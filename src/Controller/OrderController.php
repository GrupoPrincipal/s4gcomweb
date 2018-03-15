<?php

namespace App\Controller;

use App\Core\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class OrderController extends Controller
{
    public function fetchOrder(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `CODIEMPR`, `CUENTAID`, `STATUS`, `FECHA_GEN`, `FECHA_ACTU`, `TOTAL` FROM `tordenes` WHERE `ID` = :id LIMIT 1");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $data = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT o.`IDART`, o.`CANT`, o.`MONTO`, a.`ARTV_DESCART` FROM `tordenes_art` o
          INNER JOIN `tarticulos` a ON o.`IDART` = a.`ARTV_IDARTICULO` AND a.`CODIEMPR` = :code WHERE `IDORDEN` = :id");
        $stmt->bindParam("id", $args['id']);
        $stmt->bindParam("code", $data['CODIEMPR']);
        $stmt->execute();
        $details = $stmt->fetchAll();

        $data["products"] = $details;
        return $response->withJson($data);

    }

    public function fetchOrders(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT o.`ID`, o.`CODIEMPR`, o.`STATUS`, o.`FECHA_GEN`, o.`FECHA_ACTU`, o.`TOTAL`,
          c.`CLIEV_RAZONSOC`, c.`CLIEV_RIF`, c.`CLIEV_IDCLIENTE` FROM `tordenes` o INNER JOIN `tcuentas_cli` t
          ON t.`CRCN_IDCUENTA` = o.`CUENTAID` INNER JOIN `tclientes` c ON c.`CLIEV_IDCLIENTE` = t.`CRCV_IDCLIENTE`
          WHERE `STATUS` = '0' ORDER BY `FECHA_GEN` DESC, `FECHA_ACTU` DESC");
        $stmt->execute();
        $data = $stmt->fetchAll();

        for ($i = 0; $i < count($data); $i++)
        {
            $stmt = $this->db->prepare("SELECT o.`IDART`, o.`CANT`, o.`MONTO`, a.`ARTV_DESCART` FROM `tordenes_art` o
              INNER JOIN `tarticulos` a ON o.`IDART` = a.`ARTV_IDARTICULO` AND a.`CODIEMPR` = :code WHERE `IDORDEN` = :id");
            $stmt->bindParam("id", $data[$i]["ID"]);
            $stmt->bindParam("code", $data[$i]["CODIEMPR"]);
            $stmt->execute();
            $details = $stmt->fetchAll();

            $data[$i]["products"] = $details;
        }

        return $response->withJson($data);
    }

    public function fetchClientOrders(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `ID`, `CODIEMPR`, `STATUS`, `FECHA_GEN`, `FECHA_ACTU`, `TOTAL`
          FROM `tordenes` WHERE `CUENTAID` = :id ORDER BY `FECHA_GEN` DESC, `FECHA_ACTU` DESC");
        $stmt->bindParam("id", $_SESSION["user"]["CUENV_IDCUENTA"]);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    }

    public function generateClientOrder(Request $request, Response $response, $args)
    {
        $products = array();
        $input = $request->getParsedBody();

        foreach ($input["products"] as $valor)
        {
            $products[$valor["code"]]["price"] = $valor["price"];
            $products[$valor["code"]]["quantity"] = $valor["quantity"];
            $products[$valor["code"]]["des"] = $valor["des"];
            $products[$valor["code"]]["rel"] = $valor["rel"];
            $products[$valor["code"]]["iva"] = $valor["iva"];
        }

        $IdEmpresa = $_SESSION["user"]["details"]["CRCV_CODIEMPR"]; 
        $IdCliente = $_SESSION["user"]["details"]["CRCV_IDCLIENTE"];        

        $stmt = $this->db->prepare("SELECT PLANV_IDVENDEDOR,PLANV_TIPOPRECIO,PLANN_DIASCREDITO FROM tplanrutas WHERE PLANV_CODIEMPR = :company AND PLANV_IDCLIENTE = :client");
        $stmt->bindParam("company", $IdEmpresa);        
        $stmt->bindParam("client", $IdCliente);
        $stmt->execute();
        $seller = $stmt->fetchObject();               

        
        $comment = "PEDIDO GENERADO POR CLIENTE DESDE MODULO WEB";        
        //$Datexx = date('Y-m-d H:i:s');
        $days = $seller->PLANN_DIASCREDITO;


        if($input["pedido"] != 0){
            $requestID = $input["pedido"];
            $stmt = $this->db->prepare("UPDATE tpedidos_enc SET pedid_fecha = NOW(),pediv_status='MODIFICADO' WHERE pediv_codiempr = :company AND pediv_numero = :pedido");
            $stmt->bindParam("company", $IdEmpresa);
            $stmt->bindParam("pedido", $requestID);
            $stmt->execute();
            
            $stmt = $this->db->prepare("DELETE FROM tpedidos_reg WHERE pregv_codiempr = :company AND pregv_numero = :pedido");
            $stmt->bindParam("company", $IdEmpresa);
            $stmt->bindParam("pedido", $requestID);
            $stmt->execute();
            
            
            //CAMBIOS EN LA BASE DE DATOS s4gcomweb 
            
            $stmt = $this->sync->prepare("UPDATE tpedidos_enc SET pedid_fecha = NOW(),pediv_status='MODIFICADO' WHERE pediv_codiempr = :company AND pediv_numero = :pedido");
            $stmt->bindParam("company", $IdEmpresa);
            $stmt->bindParam("pedido", $requestID);
            $stmt->execute();
            
            $stmt = $this->sync->prepare("DELETE FROM tpedidos_reg WHERE pregv_codiempr = :company AND pregv_numero = :pedido");
            $stmt->bindParam("company", $IdEmpresa);
            $stmt->bindParam("pedido", $requestID);
            $stmt->execute();            
        }else{
            $stmt = $this->db->prepare("INSERT INTO `tpedidos_enc`(`pediv_codiempr`, `pediv_idcliente`, `pedid_fecha`, `pedin_diascredito`, `pediv_comentario`, `pediv_codivend`, `pediv_status`) VALUES (:company, :client, NOW(), :days, :comment, :seller, 'PENDIENTE')");
            $stmt->bindParam("company", $IdEmpresa);
            $stmt->bindParam("client", $IdCliente);
            $stmt->bindParam("days", $days);
            $stmt->bindParam("comment", $comment);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRCV_IDVENDEDOR"]);
            $stmt->execute();
            $requestID = $this->db->lastInsertId();
            
            //CAMBIOS EN LA BASE DE DATOS s4gcomweb encabezado
            
           $stmt = $this->sync->prepare("INSERT INTO `tpedidos_enc`(`pediv_codiempr`,`pediv_numero`, `pediv_idcliente`, `pedid_fecha`, `pedin_diascredito`, `pediv_comentario`, `pediv_codivend`, `pediv_status`,pedid_envio,pediv_iddesglobal,pediv_voucher) VALUES (:company,:idnumero, :client, NOW(), :days, :comment, :seller, 'PENDIENTE','0000-00-00 00:00:00','','')");
            $stmt->bindParam("company",$input["company"]);
            $stmt->bindParam("idnumero",$requestID);
            $stmt->bindParam("client", $input["client"]);
            $stmt->bindParam("days", $days);
            $stmt->bindParam("comment", $comment);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
            $stmt->execute();
        }
        $npCant = 0;
        $npSub = 0;
        $npDesc = 0;
        $npIva = 0;
        $npTotal = 0;
        foreach ($products as $code => $details)
        {
            $stmt = $this->db->prepare("SELECT `ARTV_CODIEMPR`, `ARTV_DESCART`, `ARTV_DESCARTDETA`, `ARTV_IDAGRUPAA`,
              `ARTV_IDAGRUPAB`, `ARTV_IDAGRUPAC`, `ARTV_IDTIPO`, `ARTV_IDPRESEN`, `ARTV_IDMARCA`, `ARTV_IDDEPAR`, `ARTV_IDENVASE`,
              `ARTV_IDCATEGORIA`, `ARTN_UNIXCAJA`, `ARTN_CONTXUNI`, `ARTN_COSTOANTE`, `ARTN_COSTOACTU`, `ARTN_COSTOPROM`, `ARTN_PORCIVA`,
              `ARTN_LISAEA`, `ARTV_GRADALCO`, `ARTN_NACIMPOR`, `ARTV_DESCUNIMIN`, `ARTV_DESCUNICOM`, `ARTN_VOLUMEN`, `ARTN_PESO`,
              `ARTN_SADA`, `ARTV_DESCARTCORTA`, `ARTV_REGISSANI`, `ARTV_CPE`, `ARTV_ESPECIE`, `ARTV_PROCEDENCIA`, `ARTN_PORKILOS`,
              `ARTN_STATUS`, `ARTN_CONBALANZA`, `ARTN_PORCUTIL`, `ARTN_PRECIOCAJ`, `ARTN_PRECIOUNI`, `ARTN_PREIVACAJ`, `ARTN_PREIVAUND`,
              `ARTN_PORCUTILM`, `ARTN_RUTAFOTO`, `ARTV_IDDEPOVENT` FROM `tarticulos` WHERE `ARTV_IDARTICULO` = :id AND `ARTV_CODIEMPR` = :company");
            $stmt->bindParam("id", $code);
            $stmt->bindParam("company", $IdEmpresa);
            $stmt->execute();
            $product = $stmt->fetchObject();            

            //Cajas
            $cajas = $details["quantity"] / $product->ARTN_UNIXCAJA;            

            //Precio segun tipo
            $stmt = $this->db->prepare("SELECT PREDN_PRECIOCAJ FROM tprecios WHERE PREDV_CODIEMPR = :company AND PREDV_TIPO = :tipo AND PREDV_IDARTICULO = :code");
            $stmt->bindParam("company", $IdEmpresa);
            $stmt->bindParam("tipo", $seller->PLANV_TIPOPRECIO);
            $stmt->bindParam("code", $code);
            $stmt->execute();
            $precio = $stmt->fetchObject(); 
            $price = $precio->PREDN_PRECIOCAJ * $cajas;

            $xSub =  $price;
            $xDes = $xSub*($details["des"]/100);
            $xSub = $xSub-$xDes;
            $xIva = $xSub*($details["iva"]/100);
            $xTot = $xSub +  $xIva;
            $npCant += $cajas;
            $npSub += $xSub;
            $npDesc += $xDes;
            $npIva += $xIva;
            $npTotal += $xTot;

            $stmt = $this->db->prepare("INSERT INTO `tpedidos_reg`(`pregv_codiempr`, `pregv_numero`, `pregv_idproducto`, `pregv_idcliente`, `pregn_impuesto`, `pregv_tipoprecio`,`pregd_fecha`,`pregn_cajas`, `pregn_unidades`, `pregn_precio`, pregn_descuento1, `pregv_codivend`,pregv_promo) 
                VALUES (:company, :id, :code, :client, :imp, :type, NOW(), :bxs, :uni, :price, :descuento, :seller, :promo)");
            $stmt->bindParam("company", $product->ARTV_CODIEMPR);
            $stmt->bindParam("id", $requestID);
            $stmt->bindParam("code", $code);
            $stmt->bindParam("client", $IdCliente);
            $stmt->bindParam("imp", $product->ARTN_PORCIVA);
            $stmt->bindParam("type", $seller->PLANV_TIPOPRECIO);
            $stmt->bindParam("bxs", $cajas);
            $stmt->bindParam("uni", $details["quantity"]);
            $stmt->bindParam("price", $precio->PREDN_PRECIOCAJ);
            $stmt->bindParam("descuento", $details["des"]);
            $stmt->bindParam("seller", $seller->PLANV_IDVENDEDOR);
            $stmt->bindParam("promo", $details["rel"]);
            $stmt->execute();

            $stmt = $this->db->prepare("UPDATE texisdepo SET EXDEV_UNIDADES=EXDEV_UNIDADES-:uni WHERE EXDEV_CODIEMPR=:company AND EXDEV_IDARTICULO=:code");
            $stmt->bindParam("uni", $details["quantity"]);
            $stmt->bindParam("company", $product->ARTV_CODIEMPR);
            $stmt->bindParam("code", $code);
            $stmt->execute();
            
            
            //INSERT EN LA BASE DE DATOS s4gcomweb detalle
            $stmt = $this->sync->prepare("INSERT INTO `tpedidos_reg`(`pregv_codiempr`, `pregv_numero`, `pregv_idproducto`, `pregv_idcliente`, `pregn_impuesto`, `pregv_tipoprecio`,`pregd_fecha`, `pregn_cajas`, `pregn_unidades`, `pregn_precio`, pregn_descuento1, `pregv_codivend`,pregv_promo,pregn_descuento2,pregb_actulizar) VALUES (:company, :id, :code, :client, :imp, :type, NOW(), :bxs, :uni, :price, :descuento, :seller, :promo,0,0)");
            $stmt->bindParam("company", $product->ARTV_CODIEMPR);
            $stmt->bindParam("id", $requestID);
            $stmt->bindParam("code", $code);
            $stmt->bindParam("client", $input["client"]);
            $stmt->bindParam("imp", $product->ARTN_PORCIVA);
            $stmt->bindParam("type", $seller->PLANV_TIPOPRECIO);
            $stmt->bindParam("bxs", $cajas);
            $stmt->bindParam("uni", $details["quantity"]);
            $stmt->bindParam("price", $precio->PREDN_PRECIOCAJ);
            $stmt->bindParam("descuento", $details["des"]);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
            $stmt->bindParam("promo", $details["rel"]);
            $stmt->execute();
        }
        return $response->withJson(array(
            "id" => $requestID, "monto" => $npTotal, "cajas" => $npCant
        ));        
    }

    public function cancelOrder(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("UPDATE `tordenes` SET `STATUS` = :status, `FECHA_ACTU` = NOW() WHERE `ID` = :id");
        $stmt->bindParam("id", $args['id']);
        $stmt->bindParam("status", $_SESSION["user"]["CUENV_TIPO"]);
        $stmt->execute();
        return $response->withJson(array(
            "id" => $args['id']
        ));
    }

    public function approveOrder(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("UPDATE `tordenes` SET `STATUS` = '3', `FECHA_ACTU` = NOW() WHERE `ID` = :id");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();

        $stmt = $this->db->prepare("SELECT `CODIEMPR`, `CUENTAID`, `STATUS`, `FECHA_GEN`, `FECHA_ACTU`, `TOTAL`
          FROM `tordenes` WHERE `ID` = :id LIMIT 1");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $data = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT `IDART`, `CANT`, `MONTO` FROM `tordenes_art` WHERE `IDORDEN` = :id");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $details = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT `CRCV_IDCLIENTE` FROM `tcuentas_cli` WHERE `CRCV_CODIEMPR` = :company AND `CRCN_IDCUENTA` = :id");
        $stmt->bindParam("id", $data["CUENTAID"]);
        $stmt->bindParam("company", $data["CODIEMPR"]);
        $stmt->execute();
        $client = $stmt->fetchObject();

        $data["products"] = $details;
        $comment = "GENERADO A PARTIR DE LA ORDEN NRO ".$args['id'];
        $days = 7;

        $stmt = $this->db->prepare("INSERT INTO `tpedidos_enc`(`pediv_codiempr`, `pediv_idcliente`, `pedid_fecha`, `pedin_diascredito`, `pediv_comentario`, `pediv_codivend`, `pediv_status`) VALUES (:company, :client, NOW(), :days, :comment, :seller, '3')");
        $stmt->bindParam("company", $data["CODIEMPR"]);
        $stmt->bindParam("client", $client->CRCV_IDCLIENTE);
        $stmt->bindParam("days", $days);
        $stmt->bindParam("comment", $comment);
        $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
        $stmt->execute();
        //S4GCOMWEB
        $stmt = $this->sync->prepare("INSERT INTO `tpedidos_enc`(`pediv_codiempr`,`pediv_numero`, `pediv_idcliente`, `pedid_fecha`, `pedin_diascredito`, `pediv_comentario`, `pediv_codivend`, `pediv_status`,pedid_envio,pediv_iddesglobal,pediv_voucher) VALUES (:company,:idnumero, :client, NOW(), :days, :comment, :seller, 'PENDIENTE','0000-00-00 00:00:00','','')");
        $stmt->bindParam("company",$input["company"]);
        $stmt->bindParam("idnumero",$requestID);
        $stmt->bindParam("client", $client->CRCV_IDCLIENTE);
        $stmt->bindParam("days", $days);
        $stmt->bindParam("comment", $comment);
        $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
        $stmt->execute();

        $requestID = $this->db->lastInsertId();
        foreach ($data["products"] as $element)
        {
            $stmt = $this->db->prepare("SELECT `ARTV_CODIEMPR`, `ARTV_DESCART`, `ARTV_DESCARTDETA`, `ARTV_IDAGRUPAA`,
              `ARTV_IDAGRUPAB`, `ARTV_IDAGRUPAC`, `ARTV_IDTIPO`, `ARTV_IDPRESEN`, `ARTV_IDMARCA`, `ARTV_IDDEPAR`, `ARTV_IDENVASE`,
              `ARTV_IDCATEGORIA`, `ARTN_UNIXCAJA`, `ARTN_CONTXUNI`, `ARTN_COSTOANTE`, `ARTN_COSTOACTU`, `ARTN_COSTOPROM`, `ARTN_PORCIVA`,
              `ARTN_LISAEA`, `ARTV_GRADALCO`, `ARTN_NACIMPOR`, `ARTV_DESCUNIMIN`, `ARTV_DESCUNICOM`, `ARTN_VOLUMEN`, `ARTN_PESO`,
              `ARTN_SADA`, `ARTV_DESCARTCORTA`, `ARTV_REGISSANI`, `ARTV_CPE`, `ARTV_ESPECIE`, `ARTV_PROCEDENCIA`, `ARTN_PORKILOS`,
              `ARTN_STATUS`, `ARTN_CONBALANZA`, `ARTN_PORCUTIL`, `ARTN_PRECIOCAJ`, `ARTN_PRECIOUNI`, `ARTN_PREIVACAJ`, `ARTN_PREIVAUND`,
              `ARTN_PORCUTILM`, `ARTN_RUTAFOTO`, `ARTV_IDDEPOVENT` FROM `tarticulos` WHERE `ARTV_IDARTICULO` = :id");
            $stmt->bindParam("id", $element["IDART"]);
            $stmt->execute();
            $product = $stmt->fetchObject();

            $stmt = $this->db->prepare("SELECT `PLANV_TIPOPRECIO` FROM `tplanrutas` WHERE `PLANV_CODIEMPR` = :company AND `PLANV_IDVENDEDOR` = :id");
            $stmt->bindParam("id", $_SESSION["user"]["CUENV_IDCUENTA"]);
            $stmt->bindParam("company", $data["CODIEMPR"]);
            $stmt->execute();
            $seller = $stmt->fetchObject();

            //REVISAR ESTOS VALORES
            $quantity = $element["CANT"] * $product->ARTN_UNIXCAJA;
            $price = $product->ARTN_PRECIOUNI * $element["CANT"];

            $stmt = $this->db->prepare("INSERT INTO `tpedidos_reg`(`pregv_codiempr`, `pregv_numero`, `pregv_idproducto`, `pregv_idcliente`, `pregn_impuesto`, `pregv_tipoprecio`,
              `pregd_fecha`, `pregn_cajas`, `pregn_unidades`, `pregn_precio`, `pregv_codivend`) VALUES (:company, :id, :code, :client, :imp, :type, :odate, :bxs, :uni, :price, :seller)");
            $stmt->bindParam("company", $product->ARTV_CODIEMPR);
            $stmt->bindParam("id", $requestID);
            $stmt->bindParam("code", $element["IDART"]);
            $stmt->bindParam("client", $data["CUENTAID"]);
            $stmt->bindParam("imp", $product->ARTN_PORCIVA);
            $stmt->bindParam("type", $seller->PLANV_TIPOPRECIO);
            $stmt->bindParam("odate", $data["FECHA_GEN"]);
            $stmt->bindParam("bxs", $element["CANT"]);
            //VALORES QUE VIENEN DEL CARRITO
            $stmt->bindParam("uni", $element["CANT"]);
            $stmt->bindParam("price", $element["MONTO"]);
            //VALORES EN VARIABLES DEL CALCULO
            // $stmt->bindParam("uni", $quantity);
            // $stmt->bindParam("price", $price);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
            $stmt->execute();
            
            //S4GCOMWEB
            $stmt = $this->sync->prepare("INSERT INTO `tpedidos_reg`(`pregv_codiempr`, `pregv_numero`, `pregv_idproducto`, `pregv_idcliente`, `pregn_impuesto`, `pregv_tipoprecio`,
              `pregd_fecha`, `pregn_cajas`, `pregn_unidades`, `pregn_precio`, `pregv_codivend`) VALUES (:company, :id, :code, :client, :imp, :type, :odate, :bxs, :uni, :price, :seller)");
            $stmt->bindParam("company", $product->ARTV_CODIEMPR);
            $stmt->bindParam("id", $requestID);
            $stmt->bindParam("code", $element["IDART"]);
            $stmt->bindParam("client", $data["CUENTAID"]);
            $stmt->bindParam("imp", $product->ARTN_PORCIVA);
            $stmt->bindParam("type", $seller->PLANV_TIPOPRECIO);
            $stmt->bindParam("odate", $data["FECHA_GEN"]);
            $stmt->bindParam("bxs", $element["CANT"]);
            //VALORES QUE VIENEN DEL CARRITO
            $stmt->bindParam("uni", $element["CANT"]);
            $stmt->bindParam("price", $element["MONTO"]);
            //VALORES EN VARIABLES DEL CALCULO
            // $stmt->bindParam("uni", $quantity);
            // $stmt->bindParam("price", $price);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
            $stmt->execute();
        }

        return $response->withJson(array(
            "id" => $args['id']
        ));
    }

    public function fetchRequest(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `pediv_codiempr`, `pediv_idcliente`, `pedid_fecha`, `pedin_diascredito`,
          `pediv_iddesglobal`, `pediv_comentario`, `pediv_status`, `pedid_envio`, `pediv_voucher`, `pediv_codivend`, `pediv_status`,
          tclientes.CLIEV_IDCLIENTE,tclientes.CLIEV_RAZONSOC,tclientes.CLIEV_RIF,tvendedores.VENDV_NOMBRE,tplanrutas.PLANV_TIPOPRECIO,tclientes.CLIEV_IDTIPO
          FROM `tpedidos_enc` 
          INNER JOIN tclientes ON tpedidos_enc.pediv_codiempr = tclientes.CLIEV_CODIEMPR AND tpedidos_enc.pediv_idcliente = tclientes.CLIEV_IDCLIENTE
          INNER JOIN tvendedores ON tpedidos_enc.pediv_codiempr = tvendedores.VENDV_CODIEMPR AND tpedidos_enc.pediv_codivend = tvendedores.VENDV_IDVENDEDOR
          INNER JOIN tplanrutas ON tpedidos_enc.pediv_codiempr = tplanrutas.PLANV_CODIEMPR AND tpedidos_enc.pediv_idcliente = tplanrutas.PLANV_IDCLIENTE AND tpedidos_enc.pediv_codivend = tplanrutas.PLANV_IDVENDEDOR
          WHERE `pediv_numero` = :id AND  pediv_codiempr = :idempresa LIMIT 1");
        $stmt->bindParam("id", $args['id']);
        $stmt->bindParam("idempresa", $_GET["idempresa"]);
        $stmt->execute();
        $data = $stmt->fetch();            

        $stmt = $this->db->prepare("SELECT p.`pregv_codiempr`, p.`pregv_idproducto`, p.`pregv_idcliente`, p.`pregd_fecha`, a.`ARTV_IDARTICULO`,a.`ARTV_DESCART`,
          `pregn_cajas`, p.`pregn_unidades`, p.`pregn_precio`, p.`pregn_descuento1`, p.`pregn_descuento2`, p.`pregn_impuesto`,a.ARTN_UNIXCAJA,
          `pregv_tipoprecio`, p.`pregb_actulizar`, p.`pregv_codivend`, p.pregv_promo FROM `tpedidos_reg` p INNER JOIN `tarticulos` a
          ON a.`ARTV_IDARTICULO` = p.`pregv_idproducto` AND a.`ARTV_CODIEMPR` = p.`pregv_codiempr` WHERE `pregv_numero` = :id AND  pregv_codiempr = :idempresa");
        $stmt->bindParam("id", $args['id']);
        $stmt->bindParam("idempresa", $_GET["idempresa"]);
        $stmt->execute();
        $details = $stmt->fetchAll();

        $data["products"] = $details;
        return $response->withJson($data);

    }

    public function fetchRequests(Request $request, Response $response, $args)
    {
        $sSelect = "SELECT `pediv_codiempr`, pediv_numero, `pediv_idcliente`, `pedid_fecha`, `pedin_diascredito`,
            `pediv_iddesglobal`, `pediv_comentario`, `pediv_status`, `pedid_envio`, `pediv_voucher`, `pediv_codivend`,
            tclientes.CLIEV_IDCLIENTE,tclientes.CLIEV_RAZONSOC,tclientes.CLIEV_RIF,
            SUM(ROUND(tpedidos_reg.pregn_cajas,2)) as cajas,
            SUM(ROUND(((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)-
            (((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)*tpedidos_reg.pregn_descuento1)/100))-
            ((((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)-(((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)*tpedidos_reg.pregn_descuento1)/100))*tpedidos_reg.pregn_descuento2)/100),2)) as base,
            SUM(ROUND((((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)-
            (((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)*tpedidos_reg.pregn_descuento1)/100))-
            ((((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)-(((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)*tpedidos_reg.pregn_descuento1)/100))*tpedidos_reg.pregn_descuento2)/100))*(pregn_impuesto/100),2)) as iva,
            tclientes.CLIEV_NOMBRE
            FROM `tpedidos_enc` 
            INNER JOIN tclientes ON tpedidos_enc.pediv_codiempr = tclientes.CLIEV_CODIEMPR AND tpedidos_enc.pediv_idcliente = tclientes.CLIEV_IDCLIENTE
            INNER JOIN tpedidos_reg ON pediv_codiempr = tpedidos_reg.pregv_codiempr AND pediv_numero = tpedidos_reg.pregv_numero
            WHERE `pediv_codiempr` = :idempresa AND pediv_codivend = :idvendedor";
        
        if($_GET['tipo'] == 'P'){
            $sSelect .=  " AND pediv_comentario LIKE '%VENDEDOR%'";
        }else{
             $sSelect .= " AND pediv_comentario LIKE '%CLIENTE%'";
        }
        $sSelect .= " GROUP BY pediv_numero ORDER BY pedid_fecha";
        $stmt = $this->db->prepare($sSelect);
        $stmt->bindParam("idempresa",$_GET["idempresa"]);
        $stmt->bindParam("idvendedor",$_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);          
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    }

    public function fetchClientRequests(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT p.`pediv_codiempr`, p.`pediv_numero`, p.`pediv_idcliente`, p.`pedid_fecha`,
          p.`pedin_diascredito`, p.`pediv_iddesglobal`, p.`pediv_comentario`, p.`pediv_status`, p.`pedid_envio`,
          p.`pediv_voucher`, p.`pediv_codivend`, v.`VENDV_NOMBRE`,
          SUM(ROUND(tpedidos_reg.pregn_cajas,2)) as cajas,
          SUM(ROUND(((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)-
            (((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)*tpedidos_reg.pregn_descuento1)/100))-
            ((((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)-(((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)*tpedidos_reg.pregn_descuento1)/100))*tpedidos_reg.pregn_descuento2)/100),2)) as base,
          SUM(ROUND((((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)-
            (((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)*tpedidos_reg.pregn_descuento1)/100))-
            ((((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)-(((tpedidos_reg.pregn_cajas*tpedidos_reg.pregn_precio)*tpedidos_reg.pregn_descuento1)/100))*tpedidos_reg.pregn_descuento2)/100))*(pregn_impuesto/100),2)) as iva
          FROM `tpedidos_enc` p
          INNER JOIN `tcuentas_cli` t ON t.`CRCV_IDCLIENTE` = p.`pediv_idcliente` 
          INNER JOIN `tcuentas` c ON c.`CUENV_IDCUENTA` = t.`CRCN_IDCUENTA` 
          INNER JOIN `tvendedores` v ON v.`VENDV_CODIEMPR` = p.`pediv_codiempr`
          INNER JOIN tpedidos_reg ON p.pediv_codiempr = tpedidos_reg.pregv_codiempr AND p.pediv_numero = tpedidos_reg.pregv_numero
          AND v.`VENDV_IDVENDEDOR` = p.`pediv_codivend` 
          WHERE p.`pediv_idcliente` = :id 
          GROUP BY p.pediv_numero
          ORDER BY p.`pedid_fecha`");
        $stmt->bindParam("id", $_SESSION["user"]["details"]["CRCV_IDCLIENTE"]);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    }

    public function generateRequest(Request $request, Response $response, $args)
    {
        $products = array();
        $input = $request->getParsedBody();        

        foreach ($input["products"] as $valor)
        {
            $products[$valor["code"]]["price"] = $valor["price"];
            $products[$valor["code"]]["quantity"] = $valor["quantity"];
            $products[$valor["code"]]["des"] = $valor["des"];
            $products[$valor["code"]]["rel"] = $valor["rel"];
            $products[$valor["code"]]["iva"] = $valor["iva"];
        }        

        $stmt = $this->db->prepare("SELECT PLANV_TIPOPRECIO,PLANN_DIASCREDITO FROM tplanrutas WHERE PLANV_CODIEMPR = :company AND PLANV_IDVENDEDOR = :id AND PLANV_IDCLIENTE = :client");
        $stmt->bindParam("company", $input["company"]);
        $stmt->bindParam("id", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);        
        $stmt->bindParam("client", $input["client"]);
        $stmt->execute();
        $seller = $stmt->fetchObject();       

        $comment = "PEDIDO GENERADO POR VENDEDOR DESDE MODULO WEB";
        $days = $seller->PLANN_DIASCREDITO;
        //$newDate = date('Y-m-d H:i:s');

        
        if($input["pedido"] != 0){
            $requestID = $input["pedido"];
            $stmt = $this->db->prepare("UPDATE tpedidos_enc SET pedid_fecha = NOW(),pediv_status='MODIFICADO' WHERE pediv_codiempr = :company AND pediv_numero = :pedido");
            $stmt->bindParam("company", $input["company"]);
            $stmt->bindParam("pedido", $requestID);
            $stmt->execute();
            
            $stmt = $this->db->prepare("DELETE FROM tpedidos_reg WHERE pregv_codiempr = :company AND pregv_numero = :pedido");
            $stmt->bindParam("company",$input["company"]);
            $stmt->bindParam("pedido", $requestID);
            $stmt->execute(); 
            
            //CAMBIOS s4gcomweb
            $stmt = $this->sync->prepare("UPDATE tpedidos_enc SET pedid_fecha = NOW(),pediv_status='MODIFICADO' WHERE pediv_codiempr = :company AND pediv_numero = :pedido");
            $stmt->bindParam("company", $input["company"]);
            $stmt->bindParam("pedido", $requestID);
            $stmt->execute();
            
            $stmt = $this->sync->prepare("DELETE FROM tpedidos_reg WHERE pregv_codiempr = :company AND pregv_numero = :pedido");
            $stmt->bindParam("company",$input["company"]);
            $stmt->bindParam("pedido", $requestID);
            $stmt->execute();            
        }else{
            $stmt = $this->db->prepare("INSERT INTO `tpedidos_enc`(`pediv_codiempr`, `pediv_idcliente`, `pedid_fecha`, `pedin_diascredito`, `pediv_comentario`, `pediv_codivend`, `pediv_status`) VALUES (:company, :client, NOW(), :days, :comment, :seller, 'ENCURSO')");
            $stmt->bindParam("company",$input["company"]);
            $stmt->bindParam("client", $input["client"]);
            $stmt->bindParam("days", $days);
            $stmt->bindParam("comment", $comment);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
            $stmt->execute();
            $requestID = $this->db->lastInsertId();
            
            
            //INSERT EN LA BASE DE DATOS s4gcomweb encabezado
            $stmt = $this->sync->prepare("INSERT INTO `tpedidos_enc`(`pediv_codiempr`,`pediv_numero`, `pediv_idcliente`, `pedid_fecha`, `pedin_diascredito`, `pediv_comentario`, `pediv_codivend`, `pediv_status`,pedid_envio,pediv_iddesglobal,pediv_voucher) VALUES (:company,:idnumero, :client, NOW(), :days, :comment, :seller, 'PENDIENTE','0000-00-00 00:00:00','','')");
            $stmt->bindParam("company",$input["company"]);
            $stmt->bindParam("idnumero",$requestID);
            $stmt->bindParam("client", $input["client"]);
            $stmt->bindParam("days", $days);
            $stmt->bindParam("comment", $comment);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
            $stmt->execute();
        }        

        $npCant = 0;
        $npSub = 0;
        $npDesc = 0;
        $npIva = 0;
        $npTotal = 0;        
        foreach ($products as $code => $details)
        {
            $stmt = $this->db->prepare("SELECT `ARTV_CODIEMPR`, `ARTV_DESCART`, `ARTV_DESCARTDETA`, `ARTV_IDAGRUPAA`,
              `ARTV_IDAGRUPAB`, `ARTV_IDAGRUPAC`, `ARTV_IDTIPO`, `ARTV_IDPRESEN`, `ARTV_IDMARCA`, `ARTV_IDDEPAR`, `ARTV_IDENVASE`,
              `ARTV_IDCATEGORIA`, `ARTN_UNIXCAJA`, `ARTN_CONTXUNI`, `ARTN_COSTOANTE`, `ARTN_COSTOACTU`, `ARTN_COSTOPROM`, `ARTN_PORCIVA`,
              `ARTN_LISAEA`, `ARTV_GRADALCO`, `ARTN_NACIMPOR`, `ARTV_DESCUNIMIN`, `ARTV_DESCUNICOM`, `ARTN_VOLUMEN`, `ARTN_PESO`,
              `ARTN_SADA`, `ARTV_DESCARTCORTA`, `ARTV_REGISSANI`, `ARTV_CPE`, `ARTV_ESPECIE`, `ARTV_PROCEDENCIA`, `ARTN_PORKILOS`,
              `ARTN_STATUS`, `ARTN_CONBALANZA`, `ARTN_PORCUTIL`, `ARTN_PRECIOCAJ`, `ARTN_PRECIOUNI`, `ARTN_PREIVACAJ`, `ARTN_PREIVAUND`,
              `ARTN_PORCUTILM`, `ARTN_RUTAFOTO`, `ARTV_IDDEPOVENT` FROM `tarticulos` WHERE `ARTV_IDARTICULO` = :id AND `ARTV_CODIEMPR` = :company");
            $stmt->bindParam("id", $code);
            $stmt->bindParam("company", $input["company"]);
            $stmt->execute();
            $product = $stmt->fetchObject();            

            //Cajas
            $cajas = $details["quantity"] / $product->ARTN_UNIXCAJA;
            //Precio segun tipo
            $stmt = $this->db->prepare("SELECT PREDN_PRECIOCAJ  FROM tprecios WHERE PREDV_CODIEMPR = :company AND PREDV_TIPO = :tipo AND PREDV_IDARTICULO = :code");
            $stmt->bindParam("company", $input["company"]);
            $stmt->bindParam("tipo", $seller->PLANV_TIPOPRECIO);
            $stmt->bindParam("code", $code);
            $stmt->execute();
            $precio = $stmt->fetchObject(); 
            $price = $precio->PREDN_PRECIOCAJ * $cajas;

            $xSub =  $price;
            $xDes = $xSub*($details["des"]/100);
            $xSub = $xSub-$xDes;
            $xIva = $xSub*($details["iva"]/100);
            $xTot = $xSub +  $xIva;
            $npCant += $cajas;
            $npSub += $xSub;
            $npDesc += $xDes;
            $npIva += $xIva;
            $npTotal += $xTot;

            $stmt = $this->db->prepare("INSERT INTO `tpedidos_reg`(`pregv_codiempr`, `pregv_numero`, `pregv_idproducto`, `pregv_idcliente`, `pregn_impuesto`, `pregv_tipoprecio`,`pregd_fecha`, `pregn_cajas`, `pregn_unidades`, `pregn_precio`, pregn_descuento1, `pregv_codivend`,pregv_promo) VALUES (:company, :id, :code, :client, :imp, :type, NOW(), :bxs, :uni, :price, :descuento, :seller, :promo)");
            $stmt->bindParam("company", $product->ARTV_CODIEMPR);
            $stmt->bindParam("id", $requestID);
            $stmt->bindParam("code", $code);
            $stmt->bindParam("client", $input["client"]);
            $stmt->bindParam("imp", $product->ARTN_PORCIVA);
            $stmt->bindParam("type", $seller->PLANV_TIPOPRECIO);
            $stmt->bindParam("bxs", $cajas);
            $stmt->bindParam("uni", $details["quantity"]);
            $stmt->bindParam("price", $precio->PREDN_PRECIOCAJ);
            $stmt->bindParam("descuento", $details["des"]);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
            $stmt->bindParam("promo", $details["rel"]);
            $stmt->execute();

            $stmt = $this->db->prepare("UPDATE texisdepo SET EXDEV_UNIDADES=EXDEV_UNIDADES-:uni WHERE EXDEV_CODIEMPR=:company AND EXDEV_IDARTICULO=:code");
            $stmt->bindParam("uni", $details["quantity"]);
            $stmt->bindParam("company", $product->ARTV_CODIEMPR);
            $stmt->bindParam("code", $code);
            $stmt->execute();
            
            //INSERT EN LA BASE DE DATOS s4gcomweb detalle
            $stmt = $this->sync->prepare("INSERT INTO `tpedidos_reg`(`pregv_codiempr`, `pregv_numero`, `pregv_idproducto`, `pregv_idcliente`, `pregn_impuesto`, `pregv_tipoprecio`,`pregd_fecha`, `pregn_cajas`, `pregn_unidades`, `pregn_precio`, pregn_descuento1, `pregv_codivend`,pregv_promo,pregn_descuento2,pregb_actulizar) VALUES (:company, :id, :code, :client, :imp, :type, NOW(), :bxs, :uni, :price, :descuento, :seller, :promo,0,0)");
            $stmt->bindParam("company", $product->ARTV_CODIEMPR);
            $stmt->bindParam("id", $requestID);
            $stmt->bindParam("code", $code);
            $stmt->bindParam("client", $input["client"]);
            $stmt->bindParam("imp", $product->ARTN_PORCIVA);
            $stmt->bindParam("type", $seller->PLANV_TIPOPRECIO);
            $stmt->bindParam("bxs", $cajas);
            $stmt->bindParam("uni", $details["quantity"]);
            $stmt->bindParam("price", $precio->PREDN_PRECIOCAJ);
            $stmt->bindParam("descuento", $details["des"]);
            $stmt->bindParam("seller", $_SESSION["user"]["details"]["CRVV_IDVENDEDOR"]);
            $stmt->bindParam("promo", $details["rel"]);
            $stmt->execute();
        }

        return $response->withJson(array(
            "id" => $requestID, "monto" => $npTotal, "cajas" => $npCant
        )); 
    }
}
