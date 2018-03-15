<?php

namespace App\Controller;

use App\Core\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class FactController extends Controller
{
    
    public function inicio(Request $request, Response $response, $args){
        
        
        if(isset($_SESSION["user"])) {
    
        switch($_SESSION["user"]["CUENV_TIPO"]) {
            case 0: FactController::vistaadmin($request, $response, $args); break;
            case 1: FactController::vistaseller($request, $response, $args); break;
            case 2: return $this->renderer->render($response, 'inicio.phtml', $args); break;
            case 3: FactController::corriente($request, $response, $args); break;
            case 4: FactController::allrutas($request, $response, $args); break;
            case 5: FactController::promocioneslista($request, $response, $args);
                
        }
        } else {
            return $this->renderer->render($response, 'login.phtml', $args);
        }
        
        
    }
    
    public function menu(Request $request, Response $response, $args){
   
        $bus = $this->db->prepare("SELECT `MODU` FROM `tmodcta` WHERE `TCUENTA` = :iduser  and ESTATUS='1'");
            $bus->bindParam("iduser",$_SESSION["user"]['CUENV_USUARIO']);
            $bus->execute();
            $mod = $bus->fetchAll();
            $data  = $mod;
            $_SESSION["modulos"] = $data;
$html='';
$html.='<li ><a href="#!" onclick=redirect("./") >Inicio</a></li>';      
         foreach ($data as $value) {
        if($value['MODU']=='2'){    
               $html.='<li><a href="#!" onclick=redirect("pedidos")>Pedidos</a></li>';  
        }   
        if($value['MODU']=='3'){                     
             $html.=' <li class="dropdown">';
             $html.='<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
             $html.='Cuentas por pagar<b class="caret"></b>';
             $html.='   </a>';
             $html.='   <ul class="dropdown-menu">';
             $html.='     <li><a href="#" onclick=redirect("cuentas")>Estado de cuentas</a></li>';
             $html.='     <li class="divider"></li>';
             $html.='     <li><a href="#"  onclick=redirect("facturaxpagar")>Facturas por pagar</a></li>';
         $html.='      </ul>';
          $html.='   </li> ';
          }   
          if($value['MODU']=='4'){                    
          $html.='<li class="dropdown">';
           $html.='<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
          $html.='Articulos<b class="caret"></b>';
          $html.='</a>';
         $html.='<ul class="dropdown-menu">';
           $html.='<li><a href="#"  onclick=redirect("descontinuados")>Descontinuados</a>';
         $html.='</li>';
           $html.='</ul>';
          $html.='</li>';
             } 
             if($value['MODU']=='5'){    
            $html.='<li><a href="#" onclick=redirect("fpagosday") >Pagos del día</a></li>';
             } 
            }

            return $response->withJson(array('content'=>$html));
    }
    
    
       public function menutotal(Request $request, Response $response, $args){
        $desc=$_SESSION["user"]['DEPARTAMENTO'];
        $carg=$_SESSION["user"]['CARGO'];
        $query="SELECT
                    cargos.codecarg,
                    departamentos.imagdept
                FROM
                departamentos
                INNER JOIN cargos ON departamentos.codedept = cargos.codedpt
                WHERE
                    departamentos.descripcion LIKE '$desc'
                AND cargos.descripcion LIKE '$carg'";
           
        $stmt = $this->principal->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch();
           
           $bus = $this->db->prepare("SELECT
                                            modulos.iconmodu,
                                            modulos.descmodu,
                                            modulos.linkmodu,
                                            permisos.escrpermi,
                                            permisos.editpermi,
                                            submodulo.codesmod,
                                            submodulo.descsmod,
                                            submodulo.linksmod,
                                            submodulo.iconsmod
                                        FROM
                                            permisos
                                        INNER JOIN modulos ON modulos.codemodu = permisos.codemodu
                                        LEFT JOIN submodulo ON permisos.codemodu = permisos.codemodu
                                        AND submodulo.codemodu = modulos.codemodu
                                        WHERE
                                            codetype = :iduser ");
            $bus->bindParam("iduser",$data['codecarg']);
            $bus->execute();
            $mod = $bus->fetchAll();
           $htmml='';
           foreach($mod as $m){
               $html.='<li ><a href="'.$m['linkmodu'].'"  >'.$m['descmodu'].'</a></li>';     
           }
        

            return $response->withJson(array('content'=>$html));
    }
    
    public function facturas(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT tfacpeda.NUMEPEDI,   tfacpeda.CODICLIE,  tfacpeda.FECHA, tfacpeda.TOTAPEDI FROM `tfacpeda` WHERE CODICLIE = :idcuenta  ORDER BY NUMEPEDI DESC limit 8");  //ORDER BY NUMEPEDI DESC  limit 20 and numepedi='711020813'
        $stmt->bindParam("idcuenta", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);
        $stmt->execute();
        $data = $stmt->fetchAll();
        /* return $response->withJson(array(
            "id" => $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]
        )); 
        foreach($data as $d){
            
            $stmt = $this->guia->prepare("");
            $stmt->bindParam("idpedi", $d["NUMEPEDI"]);
            $stmt->execute();
            $dt = $stmt->fetch();
            
            //$data["detalle"]=$dt[""];
        }
        */
        return $response->withJson($data);
    }
  
    public function factura(Request $request, Response $response, $args){
        $data = array();
        $data2 = array();
        $data3 = array();
        $stmt = $this->ventor->prepare("SELECT tfacpeda.NUMEPEDI,   tfacpeda.CODICLIE,tfacpeda.MOTIRECH,    tfacpeda.FECHA, tfacpeda.TOTAPEDI FROM `tfacpeda` WHERE CODICLIE = :idcuenta ORDER BY NUMEPEDI DESC");
        $stmt->bindParam("idcuenta", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);
        $stmt->execute();
        $data = $stmt->fetch();
        
        $stmt = $this->ventor->prepare("SELECT numedocu,numeguia FROM tfachisa WHERE numepedi= :idpedido ");
        $stmt->bindParam("idpedido", $data['NUMEPEDI']);
        $stmt->execute();
        $data2 = $stmt->fetch();
        if(!empty($data2))
        $data=array_merge($data,$data2);
        
        $stmt = $this->ventor->prepare("SELECT TIPODOCU, NUMEDOCU, NUMEGUIA, DATE_FORMAT(FENTREGA, '%Y-%m-%d') as FENTREGA FROM tcpcc WHERE TIPODOCU = 'FA' and NUMEDOCU = :idfactura");
        $stmt->bindParam("idfactura", $data2['numedocu']);
        $stmt->execute();
        $data3 = $stmt->fetch();
        if(!empty($data3))
        $data=array_merge($data,$data3);
        /* return $response->withJson(array(
            "id" => $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]
          )); */
        return $response->withJson($data);
    }
    public function detalle(Request $request, Response $response, $args){
         $data = array();
        $data2 = array();
        $data3 = array();
        $data4 = array();
        
        $stmt = $this->ventor->prepare("SELECT ped.NUMEPEDI, ped.FECHA, tcpca.NOMBCLIE, ped.CODIPROD,ped.CAJAS,ped.UNIDADES,ped.UNIDDESP,ped.PRECIO,prod.DESCPROD,prod.UNIDCAJA, tfacpeda.TOTAPEDI, ped.IMPU1 FROM tfacpedb AS ped INNER JOIN tinva AS prod INNER JOIN tfacpeda ON ped.NUMEPEDI = tfacpeda.NUMEPEDI INNER JOIN tcpca ON tfacpeda.CODICLIE = tcpca.CODICLIE WHERE ped.codiprod = prod.codiprod AND ped.numepedi = :idpedido");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        $stmt = $this->ventor->prepare("SELECT tfacpeda.MOTIRECH FROM `tfacpeda` WHERE NUMEPEDI = :idpedido ORDER BY NUMEPEDI DESC");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data2 = $stmt->fetch();
        
        $stmt = $this->ventor->prepare("SELECT numedocu,numeguia FROM tfachisa WHERE numepedi= :idpedido ");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data3 = $stmt->fetch();
        $data2=array_merge($data2,$data3);
        
        $stmt = $this->ventor->prepare("SELECT TIPODOCU, NUMEDOCU, NUMEGUIA, FENTREGA FROM tcpcc WHERE TIPODOCU = 'FA' and NUMEDOCU = :idfactura");
        $stmt->bindParam("idfactura", $data3['numedocu']);
        $stmt->execute();
        $data4 = $stmt->fetch();
        $data2=array_merge($data2,$data4);
        
        $resp=array($data,$data2);
        return $this->renderer->render($response, 'detalle.phtml',$resp );
        
        return $this->renderer->render($response, 'detalle.phtml', $data);
    }
    public function detalle_factura(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT fac.numedocu, fac.pedido, fac.codiprod, fac.cajas, fac.unidades, fac.fecha,  prod.descunid, fac.precvent, fac.descuento, fac.descuent2, fac.totaprod, fac.codiclie, REPLACE(DATE(fac.fecha),'-',''), prod.descprod, prod.unidcaja, prod.precsuge, fac.impu1, prod.miliunid
                         FROM  tfachisb fac INNER JOIN tinva prod 
                         WHERE fac.codiprod=prod.codiprod AND fac.tipodocu='FA' AND fac.pedido = :idpedido");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        $data2 = array();
        $qdata2 = $this->ventor->prepare("SELECT nc.numedocu, nc.NUMEAFEC from tfachisa fac INNER JOIN tcpcc nc on fac.NUMEDOCU=nc.NUMEAFEC WHERE fac.NUMEPEDI= :idpedido and nc.TIPODOCU='NC'");
        $qdata2->bindParam("idpedido", $args['pedido']);
        $qdata2->execute();
        $data2=$qdata2->fetch();
        $data["nc"]=$data2;

        $dist = array();
        $dt = $this->ventor->prepare("SELECT NOMBALMA, NOMBGERE, DIRECCION1, DIRECCION2, CIUDAD, ESTADO, TELEFONO1, RIF FROM `tdisb`");
        $dt->execute();
        $dist = $dt->fetch();
        $data["distribuidor"]=$dist;

        $clie = array();
        $clieq = $this->ventor->prepare("SELECT cli.NOMBCLIE,  cli.RAZOSOCI,  cli.DIRECCION1,  cli.CODIPOST,  cli.TELEFONO1,  cli.RIF,  cli.CODICLIE, est.nombesta, ciu.nombciud 
from `tcpca` cli INNER JOIN testa est on cli.codiesta=est.codiesta INNER JOIN tciua ciu on ciu.codiciud=cli.codiciud where CODICLIE = :idcliente");
        $clieq->bindParam("idcliente", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);
        $clieq->execute();
        $clie = $clieq->fetch();
        $data["cliente"]=$clie;
        return $this->renderer->render($response, 'factura.phtml', $data);
    }


    public function empresa(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT NOMBALMA, NOMBGERE, DIRECCION1, DIRECCION2, CIUDAD, ESTADO, TELEFONO1, RIF FROM `tdisb`");
        $stmt->execute();
        $data = $stmt->fetch();
        return $data;
    }
    public function devoluciones(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT cab.numedocu, cab.fecha, cab.numepedi, cab.codiclie, det.codiprod, det.cajas, det.unidades, det.precvent, det.descuento, det.descuent2, det.totaprod, det.impu1, inv.descprod, inv.unidcaja, inv.miliunid FROM tfachisa cab inner join  tfachisb det on cab.numedocu = det.numedocu  inner join tinva inv on det.codiprod = inv.codiprod WHERE cab.tipodocu='DV' AND cab.numepedi= :idpedido");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data = $stmt->fetchAll();

        $fac = array();
        $fact = $this->ventor->prepare("SELECT numedocu, fecha, totadocu  from tfachisa where numepedi = :idpedido and tipodocu='DV'");
        $fact->bindParam("idpedido", $args['pedido']);
        $fact->execute();
        $fac = $fact->fetch();
        $data["factura"]=$fac;

        $nc = array();
        $ncs= $this->ventor->prepare("SELECT numedocu from tcpcc where numedocu= :ifactura ");
        $ncs->bindParam("ifactura", $fac['numedocu']);
        $ncs->execute();
        $nc= $ncs->fetch();
        $data["ncredito"]=$nc;

        $dist = array();
        $dt = $this->ventor->prepare("SELECT NOMBALMA, NOMBGERE, DIRECCION1, DIRECCION2, CIUDAD, ESTADO, TELEFONO1, RIF FROM `tdisb`");
        $dt->execute();
        $dist = $dt->fetch();
        $data["distribuidor"]=$dist;

        $clie = array();
        $clieq = $this->ventor->prepare("SELECT cli.NOMBCLIE,  cli.RAZOSOCI,  cli.DIRECCION1,  cli.CODIPOST,  cli.TELEFONO1,  cli.RIF,  cli.CODICLIE, est.nombesta, ciu.nombciud 
        from `tcpca` cli INNER JOIN testa est on cli.codiesta=est.codiesta INNER JOIN tciua ciu on ciu.codiciud=cli.codiciud where CODICLIE = :idcliente");
        $clieq->bindParam("idcliente", $data[0]["codiclie"]);
        $clieq->execute();
        $clie = $clieq->fetch();
        $data["cliente"]=$clie; 
           
        return $this->renderer->render($response, 'devoluciones.phtml', $data);
    }
    
    public function cuentas(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT tcpcc.TIPODOCU, tcpcc.NUMEDOCU, DATE(FECHA) as fecha,
                DATE(FECHVENC) as fechvenc, tcpcc.SIGNO, tcpcc.MONTO, tcpcc.ABONOS,tcpcc.DESCRI,
                tcpcc.FCANCEL, DATE(tcpcc.FENTREGA) fentrega, tcpcc.FDIASCRE, tcpcc.FVENCMTO,
                tcpcc.TIPOAFEC, tcpcc.NUMEAFEC, tcpcc.FECHACGS, tcpcc.LINENUME,
                tcpcc.CODICLIE FROM tcpcc where CODICLIE = :idcliente 
                and  TIPODOCU IN ('CD','DV','FA') 
                AND MONTH (fecha) >= MONTH (CURDATE())-3 
                and year(fecha)=year(CURDATE()) order by fecha desc");
        $stmt->bindParam("idcliente", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $this->renderer->render($response, 'pagar.phtml', $data);
    }



    public function facturaxpagar(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT a.TIPODOCU, a.NUMEDOCU, DATE(a.FECHA) as fecha,  DATE(a.FECHVENC) as fechvenc, a.MONTO, a.MONTO-a.ABONOS as saldov,  a.FVENCMTO, a.CODICLIE, case  when SUM(b.MONTO) is null then '0' else SUM(b.MONTO)   end as saldo FROM ventoradm001.tcpcc a LEFT OUTER JOIN  webs4gcom.tcfactpagar b on a.NUMEDOCU= b.NUMEDOCU where a.CODICLIE = :idcliente and  a.TIPODOCU ='FA' and a.MONTO-a.ABONOS > 0 GROUP BY a.TIPODOCU, a.NUMEDOCU, a.FECHVENC, a.MONTO, a.MONTO-a.ABONOS,  a.FVENCMTO, a.CODICLIE  order by a.fecha desc");
        $stmt->bindParam("idcliente", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);
        $stmt->execute();
        $data = $stmt->fetchAll();


        return $this->renderer->render($response, 'facturaxpagar.phtml', $data);
    }

    public function abono(Request $request, Response $response, $args){                
        
        $data = array();
      
        $ban = $this->ventor->prepare("select CODIBANC, NOMBBANC FROM tbana");
        $ban->execute();
        $data = $ban->fetchAll();  
        $montoPag=$this->db->prepare("select NUMEDOCU,MONTO from tcfactpagar WHERE  NUMEDOCU = :nume");
        $montoPag->bindParam("nume", $args['factura']);
        $montoPag->execute();
        $fetch= $montoPag->fetchAll();

        
        return $this->renderer->render($response, 'abono.phtml', array('banco'=>$data, 'factmon'=>$fetch, 'factura'=>$args['factura'],'monto'=>$args['monto']));
    }

    public function pagosfactura(Request $request, Response $response, $args){

        $data = array();
        $stmt = $this->db->prepare("SELECT a.id, a.NUMEDOCU, a.DESCRI, DATE(a.FECHA) as FECHA, a.MONTO, b.NOMBBANC, a.IMAGEN, c.nombretpago FROM tcfactpagar a inner join tbana b on a.BANCO = b.CODIBANC inner join tpago c on a.TPAGO = c.id where a.numedocu= :nume  and a.codiclie= :idcliente");
        $stmt->bindParam("nume", $args['factura']);
        $stmt->bindParam("idcliente", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $data['factura']= $args['factura'];
        return $this->renderer->render($response, 'pagos.phtml', $data);
    }

    public function cpantalla(Request $request, Response $response, $args){

        $data = array();
        $stmt = $this->db->prepare("SELECT a.id,  a.IMAGEN FROM tcfactpagar a where a.id= :pagos");
        $stmt->bindParam("pagos", $args['pagos']);
        //$stmt->bindParam("idcliente", $_SESSION["user"]["info"]["CUENV_IDCUENTA"]);
        $stmt->execute();
        $data = $stmt->fetch();      
        return $this->renderer->render($response, 'cpantalla.phtml', $data);
    }
    

    public function pagosday(Request $request, Response $response, $args){

        $data = array();
        $stmt = $this->ventor->prepare("SELECT a.id, a.NUMEDOCU, a.DESCRI, DATE(a.FECHA) as FECHA, a.MONTO, b.NOMBBANC, a.IMAGEN, c.NOMBCLIE, c.CODICLIE, e.nombretpago FROM webs4gcom.tcfactpagar a 
        inner join webs4gcom.tbana b on a.BANCO = b.CODIBANC INNER JOIN ventoradm001.tcpca c on a.CODICLIE = c.CODICLIE 
        INNER JOIN ventoradm001.tcpcc d on a.NUMEDOCU = d.NUMEDOCU INNER JOIN webs4gcom.tpago e on e.id = a.TPAGO 
        where trim(d.CODIRUTA) = :ruta  and DATE(a.FECHA) BETWEEN :fechaini and :fechafin ORDER BY a.FECHA desc");
        $stmt->bindParam("ruta", $args['rutas']);
        $stmt->bindParam("fechaini", $args['fechaini']);        
        $stmt->bindParam("fechafin", $args['fechafin']);        
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
        
        //return $this->renderer->render($response, 'lispagos.phtml', $data);
    }

    public function fpagosday(Request $request, Response $response, $args){

        $data = array();
        $stmt = $this->ventor->prepare("SELECT CODIRUTA FROM truta");
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $this->renderer->render($response, 'fpagos.phtml', $data);
    }

    public function create(Request $request, Response $response, $args)
    {
        $logo=str_replace(' ','_',$_FILES['imagen']["name"]);
        
        $direccion = './imagen/';
        mkdir($direccion, 0777, true);
        $archivo_temporal= $_FILES['imagen']["tmp_name"];
        $user=$_SESSION["user"]["info"]["CLIEV_IDCLIENTE"];
        $nombre= $direccion.$user.$logo;
        
        if (is_uploaded_file($archivo_temporal))
        {
            if(copy($archivo_temporal, $nombre))
                $archivo=$logo;
            else
                $archivo='';
         }
         //return var_dump($archivo);   
         $input = $request->getParsedBody();                       
      
         $sql = "INSERT INTO `tcfactpagar`(`NUMEDOCU`, `FECHA`, `MONTO`,`DESCRI`, `CODICLIE`, `BANCO`, `TPAGO`, `IMAGEN`) VALUES (:numedocu, :fecha, :monto, :descri, :codiclie, :banco, :tpago, :imagen)";
                     

            $nombre="../".$nombre;
            if($logo==""){
               $nombre="";
            }
            $stmt = $this->db->prepare($sql);
            $fechahoy =date("Y")."/".date("m")."/".date("d");
            $stmt->bindParam("numedocu", $input['nume']);        
            $stmt->bindParam("fecha", $fechahoy);
            $stmt->bindParam("monto", $input['monto']);
            $stmt->bindParam("descri", $input['descri']);
            $stmt->bindParam("banco", $input['banco']);        
            $stmt->bindParam("tpago", $input['tpago']);                    
            $stmt->bindParam("imagen", $nombre);
            $stmt->bindParam("codiclie", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);

            $stmt->execute();       
           
    }

    public function createFact(Request $request, Response $response, $args)
    {    
           $factura = array();
           $monto = array();
           $factura = explode(',', $args['factura'] );
           $monto = explode(',', $args['montos'] );
            $data = array();
            $ban = $this->ventor->prepare("select CODIBANC, NOMBBANC FROM tbana");
            $ban->execute();
            $data = $ban->fetchAll();
       
            $fac=str_replace(',', "','", $args['factura']);
                 $montoPag=$this->db->prepare("select NUMEDOCU,MONTO from tcfactpagar WHERE  NUMEDOCU IN ('$fac')");
                 $montoPag->execute();
                 $fetch= $montoPag->fetchAll();
           
           return $this->renderer->render($response, 'abonos.phtml', array("facturas"=>$factura,"bancos"=>$data, "montos"=>$monto, "abono"=>$fetch));
    }  

     public function creates(Request $request, Response $response, $args)
    {    
        $logo=str_replace(' ','_',$_FILES['imagen']["name"]);
        
        $direccion = './imagen/';
        mkdir($direccion, 0777, true);
        $archivo_temporal= $_FILES['imagen']["tmp_name"];
        $user=$_SESSION["user"]["info"]["CLIEV_IDCLIENTE"];
        $nombre= $direccion.$user.$logo;
        
        if (is_uploaded_file($archivo_temporal))
        {
            if(copy($archivo_temporal, $nombre))
                $archivo=$logo;
            else
                $archivo='';
         }
         
        $input = $request->getParsedBody();    
        //$rmontos= array();                   
        //$rmontos= rsort($input['nmontos']);
        //return var_dump($input);   
        $num= (int) count($input['nmontos']);
        $i=0;
        $saldo=0;
        $ban=0;
       // return var_dump($input);  
        
        foreach ($input['nume'] as $valor) {
            
            $sql = "INSERT INTO `tcfactpagar`(`NUMEDOCU`, `FECHA`, `MONTO`,`DESCRI`, `CODICLIE`, `BANCO`, `TPAGO`, `IMAGEN`) VALUES (:numedocu, :fecha, :monto, :descri, :codiclie, :banco, :tpago, :imagen)";
                             
            $nombre="../".$nombre;
            if($logo==""){
               $nombre="";
            }   

            $dat = array(); 
            $query= "SELECT SUM(MONTO) AS MONTO FROM tcfactpagar WHERE numedocu=:nume AND CODICLIE=:codiclie";  
            $abonoc=$this->db->prepare($query);
            $abonoc->bindParam("nume", $valor);
            $abonoc->bindParam("codiclie", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);
            $abonoc->execute();
            $dat = $abonoc->fetch();

            //
            $abono= (double) $dat['MONTO'];
            
                                //3,207,661.97 - 1000,000 > 2600000
               if (($input['nmontos'][$i]-$abono)>= $input['monto']){                        
                            // $abono=$abono+$input['monto'];                             
                          //return var_dump($input['nmontos'][$i]);
                              $stmt = $this->db->prepare($sql);
                                $fechahoy =date("Y")."/".date("m")."/".date("d");
                                $stmt->bindParam("numedocu", $valor);          
                                $stmt->bindParam("fecha", $fechahoy);
                                $stmt->bindParam("monto", $input['monto']);
                                $stmt->bindParam("descri", $input['descri']);
                                $stmt->bindParam("banco", $input['banco']);        
                                $stmt->bindParam("tpago", $input['tpago']);                    
                                $stmt->bindParam("imagen", $nombre);
                                $stmt->bindParam("codiclie", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);               
                                $stmt->execute();    
                                $i++;
                                $input['monto']=0;    
                             break;
                            
                }

                else{       //3,207,661.97 - 1000,000 > 2600000
                            $abono=$input['nmontos'][$i]-$abono;                           
                            $input['monto']  = $input['monto']-$abono; 
                            // return var_dump($input['monto']);
                            $stmt = $this->db->prepare($sql);
                            $fechahoy =date("Y")."/".date("m")."/".date("d");
                            $stmt->bindParam("numedocu", $valor);          
                            $stmt->bindParam("fecha", $fechahoy);
                            $stmt->bindParam("monto", $abono);
                            $stmt->bindParam("descri", $input['descri']);
                            $stmt->bindParam("banco", $input['banco']);        
                            $stmt->bindParam("tpago", $input['tpago']);                    
                            $stmt->bindParam("imagen", $nombre);
                            $stmt->bindParam("codiclie", $_SESSION["user"]["info"]["CLIEV_IDCLIENTE"]);               
                            $stmt->execute();    
                            $i++;     
                                                          
                            
                }
        }   
    }
    
    public function artdesct(Request $request, Response $response, $args)
    {
        $ini=0;
        $limit=27;
        $pag=1;
        
        
        $data = array();
        $stmt = $this->ventor->prepare("SELECT tinva.CODIPROD, tinva.CODIPROV,  tinva.DESCPROD FROM `tinva` WHERE DESACTIV = 1 ");
        $stmt->execute();
        $data = $stmt->fetchAll();
        $size=count($data);
        
        if(!empty($args['pag'])){
            $pag=$args['pag'];
            $ini=($pag * $limit);
        }
        
        
        $fecha=date("Y-m-d");
        $data = array();
        $stmt = $this->ventor->prepare("SELECT tinva.CODIPROD, tinva.CODIPROV,  tinva.DESCPROD FROM `tinva` WHERE DESACTIV = 1 limit :init , :limit");
        $stmt->bindParam("init", $ini);
        $stmt->bindParam("limit",$limit);
        $stmt->execute();
        $data['desc'] = $stmt->fetchAll();
        
        for($i=0;$i< count($data['desc']);$i++){
            $img=array("IMGV_IMAGEN1"=>'', "IMGV_IMAGEN2"=>'', "IMGV_IMAGEN3"=>'', "IMGV_IMAGEN4"=>'', "IMGV_IMAGEN5"=>'');
            $stmt = $this->db->prepare("SELECT tart_imagenes.IMGV_IMAGEN1, tart_imagenes.IMGV_IMAGEN2, tart_imagenes.IMGV_IMAGEN3, tart_imagenes.IMGV_IMAGEN4, tart_imagenes.IMGV_IMAGEN5 FROM `tart_imagenes` where IMGV_IDARTICULO = :idimage");
            $stmt->bindParam("idimage", $data['desc'][$i]['CODIPROD']);
            $stmt->execute();
            if( $stmt->fetch() ){
                $img = $stmt->fetch();
            }
            $data['desc'][$i]=array_merge($data['desc'][$i],$img);
        }
        
        $data['size']=$size;
        $data['pag']=$pag;
        return $this->renderer->render($response, 'artdesct.phtml', $data);
    }
    function detail_fact(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT CODICLIE,CODIRUTA,DIAVISI,TIPOPREC,FORMPAGO,FORMPAGO1,FORMPAGO2,DIASCRED,LIMICRED FROM tcpcarut where CODICLIE = :idcliente ");
        $stmt->bindParam("idcliente",$_SESSION["user"]["CUENV_USUARIO"]);
        $stmt->execute();
        $data = $stmt->fetch();
        return $response->withJson($data);
    }
   public function vencidos(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT tcpcc.NUMEDOCU, DATE_FORMAT(FECHA, '%Y-%m-%d') AS FECHA, DATE_FORMAT(FECHVENC, '%Y-%m-%d') AS FECHVENC, DATE_FORMAT(FENTREGA, '%Y-%m-%d') AS FENTREGA, DATE_FORMAT(FVENCMTO, '%Y-%m-%d') AS FVENCMTO, FDIASCRE, MONTO, CODICLIE FROM `tcpcc` WHERE   FVENCMTO < NOW() AND FENTREGA > '0000-00-00' AND CODICLIE = :idclient and (MONTO - ABONOS)  >0");
        $stmt->bindParam("idclient",$_SESSION["user"]["CUENV_USUARIO"]);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $this->renderer->render($response, 'vencidos.phtml', $data);
    }
    public function limites(Request $request, Response $response, $args){
        $data = array();
        if($_SESSION["user"]["CUENV_TIPO"] == 2){
            $stmt = $this->ventor->prepare("SELECT tcpcarut.CODICLIE, tcpcarut.DIAVISI, tcpcarut.TIPOPREC, tcpcarut.FORMPAGO, tcpcarut.DIASCRED, tcpcarut.LIMICRED, tcpcc.NUMEDOCU, DATE_FORMAT(tcpcc.FECHA, '%Y-%m-%d') AS FECHA, DATE_FORMAT(tcpcc.FECHVENC, '%Y-%m-%d') AS FECHVENC, DATE_FORMAT(tcpcc.FENTREGA, '%Y-%m-%d') AS FENTREGA, DATE_FORMAT(tcpcc.FVENCMTO, '%Y-%m-%d') AS FVENCMTO, tcpcc.FDIASCRE, tcpcc.MONTO, tcpcc.CODICLIE, tcpcc.ABONOS, DATE_FORMAT(ADDDATE(tcpcc.FENTREGA, INTERVAL tcpcarut.DIASCRED DAY),'%Y-%m-%d') as vencido, SUM(tcpcc.ABONOS) AS totalbonos, (tcpcarut.LIMICRED - SUM(tcpcc.MONTO)) as credito FROM tcpcarut INNER JOIN tcpcc ON tcpcarut.CODICLIE = tcpcc.CODICLIE WHERE tcpcc.CODICLIE = :idclient AND FENTREGA > '0000-00-00'  GROUP BY tcpcc.CODICLIE");  
            $stmt->bindParam("idclient",$_SESSION["user"]["CUENV_USUARIO"]);
            $stmt->execute();
            $data = $stmt->fetchAll();
        }else{
            $data[0]['DIAVISI']='a';
        }
        
        return $response->withJson($data);
    }


   public function asignarmod(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT CODICLIE,NOMBCLIE FROM	tcpca");
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $this->renderer->render($response, 'asignarmod.phtml', $data);
    }

    public function asignarmodpro(Request $request, Response $response, $args){
        $data = array();        
        $stmt = $this->db->prepare("select a.ID, a.NOMBRE, b.ESTATUS from tmodulos a INNER JOIN  tmodcta b on a.ID=b.MODU where b.TCUENTA = :idcliente");
        $stmt->bindParam('idcliente', $args['cliente']);
        $stmt->execute();
        $data = $stmt->fetchAll();
        if(empty($data)){
            $stmt = $this->db->prepare("SELECT ID, NOMBRE, 0 as ESTATUS FROM tmodulos");
            $stmt->execute();
            $data = $stmt->fetchAll();
        }
        return $this->renderer->render($response, 'asignarmodpro.phtml', $data);
 

    }
    public function promociones(Request $request, Response $response, $args){
      $data = array();
        return $this->renderer->render($response, 'promociones.phtml', $data);
    }
    
    public function admlimit(Request $request, Response $response, $args){
      $data = array();
        $query="SELECT
                     trim(tclientes.CLIEV_IDCLIENTE) as CLIEV_IDCLIENTE,
                    trim(tclientes.CLIEV_RIF) as CLIEV_RIF,
                    trim(tclientes.CLIEV_NOMBRE) as CLIEV_NOMBRE,
                    trim(tplanrutas.PLANV_TIPOPRECIO) as PLANV_TIPOPRECIO,
                    trim(tclientes.CLIEV_IDGRUPO) as CLIEV_IDGRUPO,
                    trim(limites.montlimi) as montlimi
                FROM
                    tclientes
                INNER JOIN tplanrutas ON tclientes.CLIEV_CODIEMPR = tplanrutas.PLANV_CODIEMPR
                AND tclientes.CLIEV_IDCLIENTE = tplanrutas.PLANV_IDCLIENTE
                INNER JOIN tcuentas_ven ON tplanrutas.PLANV_CODIEMPR = tcuentas_ven.CRVV_CODIEMPR
                AND tplanrutas.PLANV_IDVENDEDOR = tcuentas_ven.CRVV_IDVENDEDOR
                LEFT JOIN limites ON limites.codeclie = tclientes.CLIEV_IDCLIENTE GROUP BY tclientes.CLIEV_IDCLIENTE";
         $stmt = $this->db->prepare($query);
         $stmt->execute();
         $data = $stmt->fetchAll();
        return $this->renderer->render($response, 'admlimit.phtml', $data);
    }
    
    
    function addminimo(Request $request, Response $response, $args){
         $fecha=date("Y-m-d");
        $usr=lcfirst(str_replace(' ','.',$user['CUENV_USUARIO']));
        $data = array();
        $input = $request->getParsedBody();
        $query="SELECT tclientes.CLIEV_IDCLIENTE, tclientes.CLIEV_RIF, tclientes.CLIEV_NOMBRE, tplanrutas.PLANV_TIPOPRECIO FROM tclientes
                        INNER JOIN tplanrutas ON tclientes.CLIEV_CODIEMPR = tplanrutas.PLANV_CODIEMPR AND tclientes.CLIEV_IDCLIENTE = tplanrutas.PLANV_IDCLIENTE
                        INNER JOIN tcuentas_ven ON tplanrutas.PLANV_CODIEMPR = tcuentas_ven.CRVV_CODIEMPR AND tplanrutas.PLANV_IDVENDEDOR = tcuentas_ven.CRVV_IDVENDEDOR where 1";
       
        if($input['clientes'] == 'all'){
         $stmt = $this->db->prepare($query);
         $stmt->execute();
         $data = $stmt->fetchAll();
            
        }elseif($input['clientes'] == 'canal'){
             
            $grupo=$input['canal'];
            $query.=" and tclientes.CLIEV_IDGRUPO = :idgrupo ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam("idgrupo", $grupo);
            $stmt->execute();
            $data = $stmt->fetchAll();
        }elseif($input['clientes'] == 'clientes'){
            $lista=$input['lista'];
            $lista=explode(",",$lista);
            $i=0;
            foreach($lista as $d){
                $data[$i]['CLIEV_IDCLIENTE']=$d;
                    $i++;
            }
        }
        
        foreach($data as $values){
            
            $stmt = $this->db->prepare("DELETE FROM limites WHERE codeclie = :idclie ");
            $stmt->bindParam("idclie", $values['CLIEV_IDCLIENTE']);
            $stmt->execute();
            
            
            $stmt = $this->db->prepare("INSERT INTO limites(codeclie, montlimi, codeuser, fechlimi) VALUES (:idclie, :limit, :iduser, now() )");
            $stmt->bindParam("idclie", $values['CLIEV_IDCLIENTE']);          
            $stmt->bindParam("limit", $input['monto']);
            $stmt->bindParam("iduser",$usr );
            $stmt->execute();
            
        }
        return $response->withJson($input);
    }
    function canales(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT trim(CODIGCLI) as CODIGCLI ,trim(GRUPCLIE) as GRUPCLIE  FROM tclib");
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    }

     function savemod(Request $request, Response $response, $args){

        $idmod = array();
        $idmod = explode(',', $args['id'] );
       
   
        $query = "select ID from tmodulos where ID not in (".$args['id'].")";
        $nmod = $this->db->prepare($query); 
        $nmod->execute();
        $n =  $nmod->fetchall();        

        foreach ($n as  $value) {
           //echo($value["ID"]);
           $sql = "UPDATE  `tmodcta` SET  ESTATUS ='0' WHERE MODU= '".$value["ID"]."' and TCUENTA= '".$_SESSION['idcliente']."'";            
            $cu = $this->db->prepare($sql);
            $cu->execute();
        }

        foreach ($idmod as  $val) {
           //echo($val["ID"]);
           $sql1 = "UPDATE  `tmodcta` SET  ESTATUS ='1' WHERE MODU= '".$val["ID"]."' and TCUENTA= '".$_SESSION['idcliente']."'";
            //$cu->bindParam('idcliente', $_SESSION['idcliente']);
            //$cu->bindParam('mod', $value["ID"]);
            $cu = $this->db->prepare($sql1);
            $cu->execute();
        }       
    }
    
    function limit(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->db->prepare("SELECT codeclie, montlimi FROM `limites` WHERE codeclie = :idcliente ");
        $stmt->bindParam('idcliente', $_SESSION['user']['details']['CRCV_IDCLIENTE']);
        $stmt->execute();
        $data = $stmt->fetch();
        return $response->withJson($data);
    }
    function pedidos(Request $request, Response $response, $args){
        $data0 = array();
        $stmt = $this->db->prepare("SELECT codeclie, montlimi FROM `limites` WHERE codeclie = :idcliente ");
        $stmt->bindParam('idcliente', $_SESSION['user']['details']['CRCV_IDCLIENTE']);
        $stmt->execute();
        $data0 = $stmt->fetch();
        if(empty($data0)){
            $data0=array("montlimi"=>0,"codeclie"=>$_SESSION['user']['details']['CRCV_IDCLIENTE']);
        }
        $data1 = array();
        $stmt = $this->ventor->prepare("SELECT tcpcarut.CODICLIE, tcpcarut.DIAVISI, tcpcarut.TIPOPREC, tcpcarut.FORMPAGO, tcpcarut.DIASCRED, tcpcarut.LIMICRED, tcpcc.NUMEDOCU, DATE_FORMAT(tcpcc.FECHA, '%Y-%m-%d') AS FECHA, DATE_FORMAT(tcpcc.FECHVENC, '%Y-%m-%d') AS FECHVENC, DATE_FORMAT(tcpcc.FENTREGA, '%Y-%m-%d') AS FENTREGA, DATE_FORMAT(tcpcc.FVENCMTO, '%Y-%m-%d') AS FVENCMTO, tcpcc.FDIASCRE, tcpcc.MONTO, tcpcc.CODICLIE, tcpcc.ABONOS, DATE_FORMAT(ADDDATE(tcpcc.FENTREGA, INTERVAL tcpcarut.DIASCRED DAY),'%Y-%m-%d') as vencido, SUM(tcpcc.ABONOS) AS totalbonos, (tcpcarut.LIMICRED - SUM(tcpcc.MONTO)) as credito FROM tcpcarut INNER JOIN tcpcc ON tcpcarut.CODICLIE = tcpcc.CODICLIE WHERE tcpcc.CODICLIE = :idclient AND FENTREGA > '0000-00-00'  GROUP BY tcpcc.CODICLIE");  
        $stmt->bindParam("idclient",$_SESSION["user"]["CUENV_USUARIO"]);
        $stmt->execute();
        $data1 = $stmt->fetch();
        
        $data=array_merge($data0,$data1);
        
        return $this->renderer->render($response, 'main.phtml', $data);
    }
    
    function admproduct(Request $request, Response $response, $args){
         $data = array();
        $query="SELECT
                     trim(tclientes.CLIEV_IDCLIENTE) as CLIEV_IDCLIENTE,
                    trim(tclientes.CLIEV_RIF) as CLIEV_RIF,
                    trim(tclientes.CLIEV_NOMBRE) as CLIEV_NOMBRE,
                    trim(tplanrutas.PLANV_TIPOPRECIO) as PLANV_TIPOPRECIO,
                    trim(tclientes.CLIEV_IDGRUPO) as CLIEV_IDGRUPO,
                    trim(limites.montlimi) as montlimi
                FROM
                    tclientes
                INNER JOIN tplanrutas ON tclientes.CLIEV_CODIEMPR = tplanrutas.PLANV_CODIEMPR
                AND tclientes.CLIEV_IDCLIENTE = tplanrutas.PLANV_IDCLIENTE
                INNER JOIN tcuentas_ven ON tplanrutas.PLANV_CODIEMPR = tcuentas_ven.CRVV_CODIEMPR
                AND tplanrutas.PLANV_IDVENDEDOR = tcuentas_ven.CRVV_IDVENDEDOR
                LEFT JOIN limites ON limites.codeclie = tclientes.CLIEV_IDCLIENTE GROUP BY tclientes.CLIEV_IDCLIENTE";
         $stmt = $this->db->prepare($query);
         $stmt->execute();
         $data['CLIENTES'] = $stmt->fetchAll();
        
        $stmt = $this->db->prepare("SELECT GRUCV_IDAGRUPAA value, GRUCV_NOMBRE descrip  FROM tagrupaa WHERE 1 order by GRUCV_NOMBRE");        
        $stmt->execute();      
        $data["GROUPAA"] = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT SUBGPV_IDAGRUPAA grupo, SUBGPV_IDAGRUPAB value, SUBGPV_NOMBRE descrip FROM tagrupab WHERE SUBGPV_IDAGRUPAA != '001' AND SUBGPV_IDAGRUPAA != '208' order by SUBGPV_NOMBRE");
        $stmt->execute();       
        $data["GROUPAB"] = $stmt->fetchAll();
        return $this->renderer->render($response, 'admproduct.phtml', $data);
    }
    function loadimage(Request $request, Response $response, $args){
        
        $img=str_replace(' ','_',$_FILES['file']["name"]);
        $archivo_temporal= $_FILES['file']["tmp_name"];
        $resp=array("imagen"=>$archivo_temporal);
        return $response->withJson($resp);
    }
    function desctvAll(Request $request, Response $response, $args){
        $input = $request->getParsedBody();
        if($input['tipo']=='all'){
            $query="update tagrupaa set GRUCV_STATUS='0' ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        
        if($input['tipo']=='provee'){
            $lista=$input['list'];
            $lista=explode(",",$lista);
            $n=count($lista);
            $i=0;
            foreach($lista as $l){
             $where.=" GRUCV_IDAGRUPAA = '$l' ";
                $i++;
                if($n>$i){
                    $where.=" or "; 
                }
            }
            
            $query="update tagrupaa set GRUCV_STATUS='0' where ".$where;
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        
        if($input['tipo']=='canal'){
            $lista=$input['list'];
            $canal=$input['canal'];
            $lista=explode(",",$lista);
            $query="SELECT tclientes.CLIEV_IDCLIENTE FROM tclientes where tclientes.CLIEV_IDGRUPO = :idgrupo";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam("idgrupo", $canal);
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach($data as $values){
            
                $stmt = $this->db->prepare("DELETE FROM limit_prov WHERE codeclie = :idclie and codeprov= :idprov");
                $stmt->bindParam("idclie", $values['CLIEV_IDCLIENTE']);
                $stmt->bindParam("idprov", $l);
                $stmt->execute();

                foreach($lista as $l){
                    $stmt = $this->db->prepare("INSERT INTO limit_prov(codeclie, codeprov) VALUES (:idclie, :provee )");
                    $stmt->bindParam("idclie", $values['CLIEV_IDCLIENTE']);          
                    $stmt->bindParam("provee", $l);
                    $stmt->execute();
                }
                

            }
            
        }
        if($input['tipo']=='clients'){
            $lista=$input['list'];
            $clients=$input['clients'];
            $lista=explode(",",$lista);
            $clients=explode(",",$clients);
            
            foreach($clients as $values){
            
                $stmt = $this->db->prepare("DELETE FROM limit_prov WHERE codeclie = :idclie and codeprov= :idprov ");
                $stmt->bindParam("idclie", $values);
                $stmt->bindParam("idprov", $l);
                $stmt->execute();

                foreach($lista as $l){
                    $stmt = $this->db->prepare("INSERT INTO limit_prov(codeclie, codeprov) VALUES (:idclie, :provee )");
                    $stmt->bindParam("idclie", $values);          
                    $stmt->bindParam("provee", $l);
                    $stmt->execute();
                }
                

            }
        }
        
      
        return $response->withJson($query);
    }
    function savepromo(Request $request, Response $response, $args){
        $input = $request->getParsedBody();
        $imagen=str_replace(' ','+',$input['imagen']);
        $titulo=$input['titulo'];
        $content=addslashes($input['content']);
        $fechas=$input['fechas'];
        $fechas=explode('-',$fechas);
        $stmt = $this->db->prepare("INSERT INTO promociones(tituprom,headprom,descrprom,desdeprom,hastaprom) VALUES (:titulo,:imagen,:descri,:desde,:hasta)");
        $stmt->bindParam("titulo", $titulo);
        $stmt->bindParam("imagen", $imagen);
        $stmt->bindParam("descri", $content);
        $stmt->bindParam("desde", str_replace('/','-',$fechas[0]));
        $stmt->bindParam("hasta", str_replace('/','-',$fechas[1]));
        $stmt->execute();

        
       return $response->withJson($input);
    }
    function promocioneslista(Request $request, Response $response, $args){
      $data = array();
        $query="SELECT * FROM `promociones` where statprom ='1'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
        
        return $this->renderer->render($response, 'promocioneslista.phtml', $data);
    }
    function artnuevs(Request $request, Response $response, $args){
      $data = array();
        $query="SELECT
                    a.ARTV_IDARTICULO,
                    a.ARTV_DESCART,
                    a.ARTN_PORCIVA,
                    a.ARTN_UNIXCAJA,
                    tprecios.PREDV_TIPO,
                    tprecios.PREDN_PRECIOCAJ AS ARTN_PRECIOCAJ,
                    tprecios.PREDN_PREIVACAJ AS ARTN_PREIVACAJ,
                    a.ARTN_UNDMIN
                FROM
                    tarticulos a
                INNER JOIN tprecios ON a.ARTV_CODIEMPR = tprecios.PREDV_CODIEMPR
                AND a.ARTV_IDARTICULO = tprecios.PREDV_IDARTICULO
                INNER JOIN tagrupaa ON a.ARTV_IDAGRUPAA = tagrupaa.GRUCV_IDAGRUPAA
                AND a.ARTV_CODIEMPR = tagrupaa.GRUCV_CODIEMPR
                INNER JOIN tagrupab ON a.ARTV_IDAGRUPAA = tagrupab.SUBGPV_IDAGRUPAA
                AND a.ARTV_IDAGRUPAB = tagrupab.SUBGPV_IDAGRUPAB
                AND a.ARTV_CODIEMPR = tagrupab.SUBGPV_CODIEMPR
                INNER JOIN tagrupac ON a.ARTV_IDAGRUPAA = tagrupac.TLINV_IDAGRUPAA
                AND a.ARTV_IDAGRUPAB = tagrupac.TLINV_IDAGRUPAB
                AND a.ARTV_IDAGRUPAC = tagrupac.TLINV_IDAGRUPAC
                AND a.ARTV_CODIEMPR = tagrupac.TLINV_CODIEMPR
                INNER JOIN tcuentas_cli c ON a.ARTV_CODIEMPR = c.CRCV_CODIEMPR
                INNER JOIN texisdepo ON a.ARTV_CODIEMPR = texisdepo.EXDEV_CODIEMPR
                AND a.ARTV_IDARTICULO = texisdepo.EXDEV_IDARTICULO
                WHERE
                     tprecios.PREDV_TIPO = 'A'
                AND texisdepo.EXDEV_UNIDADES >= a.ARTN_UNDMIN AND texisdepo.EXDEV_UNIDADES >= a.ARTN_UNDMIN and DATE_ADD(a.REGISTER, INTERVAL 7 DAY) >= NOW()
                ORDER BY a.REGISTER DESC limit 33";
        
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
        
        return $this->renderer->render($response, 'nuevos.phtml', $data);
}
    
    function corriente(Request $request, Response $response, $args){
    
       foreach($_SESSION['user']['rutas'] as $r){
          $n.="'".$r['CODIRUTA']."',";
        }
        $n = substr($n, 0, -1);
        $data = array();
        $query="SELECT tcpcb.codiclie, tcpcb.tipodocu, tcpcb.numedocu, tcpcb.fecha, tcpcb.fechvenc, tcpcb.monto, tcpcb.saldo, trim(tcpcb.codiruta) codiruta,
                 tcpcc.fentrega, tcpcb.fdiascre, tcpca.nombclie, tciua.nombciud, tcpcarut.diavisi,
                IF(tcpcc.fentrega > 0 ,'D','ED') as DESPACHADO,
                 DATEDIFF(NOW(),DATE_ADD(tcpcc.fentrega,INTERVAL tcpcb.fdiascre day)) as RETRASO
                FROM tcpcb tcpcb, tcpca tcpca, tciua tciua, tcpcc tcpcc, tcpcarut tcpcarut
                WHERE tciua.codiciud=tcpca.codiciud and tciua.codiesta = tcpca.codiesta
                AND tcpca.codiclie=tcpcb.codiclie
                AND tcpcb.numedocu = tcpcc.numedocu AND tcpcb.tipodocu = tcpcc.tipodocu AND tcpcb.codiclie = tcpcc.codiclie
                AND tcpcarut.codiclie = tcpca.codiclie AND trim(tcpcb.codiruta) IN ($n)
                AND tcpcb.tipodocu='FA'
                 group by tcpcb.numedocu
                ORDER BY tcpcb.codiruta, RETRASO";
        
            $stmt = $this->ventor->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
        
        $resp = array();
        $suma2 = array();
        $suma7 = array();
        $suma15 = array();
        $suma21 = array();
        $i=0;
        foreach($data as $d){
            
            if($d['RETRASO']<= 2){
                $suma_d2[$d['codiruta']]['total'][$d['DESPACHADO']]+=$d['saldo'];
            }elseif($d['RETRASO']<= 7){
                $suma_d7[$d['codiruta']]['total'][$d['DESPACHADO']]+=$d['saldo'];
            }elseif($d['RETRASO']<= 15){
                $suma_d15[$d['codiruta']]['total'][$d['DESPACHADO']]+=$d['saldo'];
            }elseif($d['RETRASO'] > 15){
                $suma_d21[$d['codiruta']]['total'][$d['DESPACHADO']]+=$d['saldo'];
            }
        }
        
        $resp['2d']=$suma_d2;
        $resp['7d']=$suma_d7;
        $resp['15d']=$suma_d15;
        $resp['21d']=$suma_d21;  
          return $this->renderer->render($response, 'manager.phtml', $resp);
    }
    public function allrutas(Request $request, Response $response, $args){
        $data = array();
                $stmt = $this->ventor->prepare("SELECT
                                                    truta.CODIRUTA,
                                                    truta.CODISUPE,
                                                    truta.NOMBVEND,
                                                    truta.CEDUVEND,
                                                    truta.TELEVEND
                                                FROM
                                                    truta
                                                WHERE CODIRUTA  <= 80");
                $stmt->execute();
                $data = $stmt->fetchAll();
            return $this->renderer->render($response, 'logistica.phtml', $data); 
    }
    public function pedidosruta(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT
                                        tfacpeda.NUMEPEDI,
                                        TRIM(tfacpeda.CODICLIE) AS CODICLIE,
                                        DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') AS FECHA,
                                        tfachisa.TOTABRUT as TOTAPEDI,
                                        TRIM(tcpca.NOMBCLIE) AS NOMBCLIE,
                                        tfacpeda.CODIRUTA,
                                    IF ( tfachisa.numeguia > 0, 'D', 'SD') AS DESPACHO,
                                     tfachisa.NUMEDOCU,
                                     tcpcc.FENTREGA,
                                     tcpcc.NUMEGUIA,
                                     tcpcc.NUMEDOCU,
                                     tcpcc.TIPODOCU,
                                        COUNT(tfacpeda.NUMEPEDI) as FAC,
                                    GROUP_CONCAT(tfachisa.NUMEDOCU) AS DOC,
                                    DATEDIFF(NOW(),tfacpeda.FECHA) AS TIEMPO
                                    FROM
                                        tfacpeda
                                    INNER JOIN tcpca ON tfacpeda.CODICLIE = tcpca.CODICLIE
                                    INNER JOIN tfachisa ON tfacpeda.NUMEPEDI = tfachisa.NUMEPEDI
                                    AND tcpca.CODICLIE = tfachisa.CODICLIE
                                    LEFT JOIN tcpcc ON tfachisa.NUMEDOCU = tcpcc.NUMEDOCU
                                    WHERE tfachisa.TOTABRUT > 0
                                    AND tfacpeda.CODIRUTA = :idruta
                                    AND DATEDIFF(NOW(),tfacpeda.FECHA) <= 7
                                    GROUP BY tfacpeda.NUMEPEDI
                                    ORDER BY tfacpeda.FECHA DESC");
        
        $stmt->bindParam("idruta", $args['ruta']);
        $stmt->execute();
        $data = $stmt->fetchAll();
       
        return $this->renderer->render($response, 'pedidos_ruta.phtml', $data);
    }
    public function pedidosrango(Request $request, Response $response, $args){
        $data = array();
        $input = $request->getParsedBody();
        $fechas=explode('-',$input['fecha']);
        $stmt = $this->ventor->prepare("SELECT tfacpeda.NUMEPEDI,
                                        TRIM(tfacpeda.CODICLIE) AS CODICLIE,
                                        DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') AS FECHA,
                                        tfachisa.TOTABRUT as TOTAPEDI,
                                        TRIM(tcpca.NOMBCLIE) AS NOMBCLIE,
                                        tfacpeda.CODIRUTA,
                                    IF ( tfachisa.numeguia > 0, 'D', 'SD') AS DESPACHO,
                                     tfachisa.NUMEDOCU,
                                     tcpcc.FENTREGA,
                                     tcpcc.NUMEGUIA,
                                     tcpcc.NUMEDOCU,
                                     tcpcc.TIPODOCU,
                                        COUNT(tfacpeda.NUMEPEDI) as FAC,
                                    GROUP_CONCAT(tfachisa.NUMEDOCU) AS DOC,
                                    DATEDIFF(NOW(),tfacpeda.FECHA) AS TIEMPO
                                    FROM
                                        tfacpeda
                                    INNER JOIN tcpca ON tfacpeda.CODICLIE = tcpca.CODICLIE
                                    INNER JOIN tfachisa ON tfacpeda.NUMEPEDI = tfachisa.NUMEPEDI
                                    AND tcpca.CODICLIE = tfachisa.CODICLIE
                                    LEFT JOIN tcpcc ON tfachisa.NUMEDOCU = tcpcc.NUMEDOCU
                                    WHERE tfachisa.TOTABRUT > 0
                                    AND tfacpeda.CODIRUTA = :idruta
                                    AND DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') >= :desde
                                    AND DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') <= :hasta
                                    AND tcpcc.TIPODOCU = 'FA'
                                    GROUP BY tfacpeda.NUMEPEDI
                                    ORDER BY tfacpeda.FECHA DESC");
        
        $stmt->bindParam("idruta", $input['ruta']);
        $stmt->bindParam("desde", trim(str_replace('/','-',$fechas[0])));
        $stmt->bindParam("hasta", trim(str_replace('/','-',$fechas[1])));
        $stmt->execute();
        $data = $stmt->fetchAll();
        for($i=0;$i< count($data);$i++){
           $data[$i]['TOTAPEDI'] = number_format($data[$i]['TOTAPEDI'], 2, ',', '.');
        }
       return $response->withJson($data);
        
    }
   public function pedidodetail(Request $request, Response $response, $args){
         $data = array();
        $data2 = array();
        $data3 = array();
        $data4 = array();
        
        $stmt = $this->ventor->prepare("SELECT ped.NUMEPEDI, ped.FECHA, tcpca.NOMBCLIE, ped.CODIPROD,ped.CAJAS,ped.UNIDADES,ped.UNIDDESP,ped.PRECIO,prod.DESCPROD,prod.UNIDCAJA, tfacpeda.TOTAPEDI, ped.IMPU1 FROM tfacpedb AS ped INNER JOIN tinva AS prod INNER JOIN tfacpeda ON ped.NUMEPEDI = tfacpeda.NUMEPEDI INNER JOIN tcpca ON tfacpeda.CODICLIE = tcpca.CODICLIE WHERE ped.codiprod = prod.codiprod AND ped.numepedi = :idpedido");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        $stmt = $this->ventor->prepare("SELECT tfacpeda.MOTIRECH FROM `tfacpeda` WHERE NUMEPEDI = :idpedido ORDER BY NUMEPEDI DESC");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data2 = $stmt->fetch();
        
        $stmt = $this->ventor->prepare("SELECT
                                            tfachisa.NUMEGUIA,
                                            tfachisa.NUMEDOCU,
                                            tcpcc.TIPODOCU,
                                            tcpcc.NUMEDOCU,
                                            tcpcc.NUMEGUIA,
                                            tfachisa.NUMEPEDI,
                                            tcpcc.FENTREGA
                                        FROM
                                            tfachisa
                                        INNER JOIN tcpcc ON tfachisa.NUMEDOCU = tcpcc.NUMEDOCU
                                        WHERE true
                                        AND numepedi= :idpedido ");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data3['DOC'] = $stmt->fetchAll();
        $data2=array_merge($data2,$data3);
        
        $resp=array($data,$data2);
        return $this->renderer->render($response, 'detalle_lider.phtml', $resp);
    }
    
    
    public function pedidodevolucion(Request $request, Response $response, $args){
         $data = array();
        $stmt = $this->ventor->prepare("SELECT cab.numedocu, cab.fecha, cab.numepedi, cab.codiclie, det.codiprod, det.cajas, det.unidades, det.precvent, det.descuento, det.descuent2, det.totaprod, det.impu1, inv.descprod, inv.unidcaja, inv.miliunid FROM tfachisa cab inner join  tfachisb det on cab.numedocu = det.numedocu  inner join tinva inv on det.codiprod = inv.codiprod WHERE cab.tipodocu='DV' AND cab.numedocu= :idpedido");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data = $stmt->fetchAll();

        $fac = array();
        $fact = $this->ventor->prepare("SELECT numedocu, fecha, totadocu  from tfachisa where numedocu = :idpedido and tipodocu='DV'");
        $fact->bindParam("idpedido", $args['pedido']);
        $fact->execute();
        $fac = $fact->fetch();
        $data["factura"]=$fac;

        $nc = array();
        $ncs= $this->ventor->prepare("SELECT numedocu from tcpcc where numedocu= :ifactura ");
        $ncs->bindParam("ifactura", $args['pedido']);
        $ncs->execute();
        $nc= $ncs->fetch();
        $data["ncredito"]=$nc;

        $dist = array();
        $dt = $this->ventor->prepare("SELECT NOMBALMA, NOMBGERE, DIRECCION1, DIRECCION2, CIUDAD, ESTADO, TELEFONO1, RIF FROM `tdisb`");
        $dt->execute();
        $dist = $dt->fetch();
        $data["distribuidor"]=$dist;

        $clie = array();
        $clieq = $this->ventor->prepare("SELECT cli.NOMBCLIE,  cli.RAZOSOCI,  cli.DIRECCION1,  cli.CODIPOST,  cli.TELEFONO1,  cli.RIF,  cli.CODICLIE, est.nombesta, ciu.nombciud 
        from `tcpca` cli INNER JOIN testa est on cli.codiesta=est.codiesta INNER JOIN tciua ciu on ciu.codiciud=cli.codiciud where CODICLIE = :idcliente");
        $clieq->bindParam("idcliente", $data[0]["codiclie"]);
        $clieq->execute();
        $clie = $clieq->fetch();
        $data["cliente"]=$clie; 
          //return $response->withJson($data);
        return $this->renderer->render($response, 'devoluciones.phtml', $data);
    }
    
     
    
     public function pedidofactura(Request $request, Response $response, $args){
        $data = array();
        $stmt = $this->ventor->prepare("SELECT fac.numedocu, fac.pedido, fac.codiprod, fac.cajas, fac.unidades, fac.fecha,  prod.descunid, fac.precvent, fac.descuento, fac.descuent2, fac.totaprod, fac.codiclie, REPLACE(DATE(fac.fecha),'-',''), prod.descprod, prod.unidcaja, prod.precsuge, fac.impu1, prod.miliunid
                         FROM  tfachisb fac INNER JOIN tinva prod 
                         WHERE fac.codiprod=prod.codiprod AND fac.tipodocu='FA' AND fac.numedocu = :idpedido");
        $stmt->bindParam("idpedido", $args['pedido']);
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        $data2 = array();
        $qdata2 = $this->ventor->prepare("SELECT nc.numedocu, nc.NUMEAFEC from tfachisa fac INNER JOIN tcpcc nc on fac.NUMEDOCU=nc.NUMEAFEC WHERE fac.NUMEPEDI= nc.numedocu and nc.TIPODOCU='NC'");
        $qdata2->bindParam("idpedido", $args['pedido']);
        $qdata2->execute();
        $data2=$qdata2->fetch();
        $data["nc"]=$data2;

        $dist = array();
        $dt = $this->ventor->prepare("SELECT NOMBALMA, NOMBGERE, DIRECCION1, DIRECCION2, CIUDAD, ESTADO, TELEFONO1, RIF FROM `tdisb`");
        $dt->execute();
        $dist = $dt->fetch();
        $data["distribuidor"]=$dist;

        $clie = array();
        $clieq = $this->ventor->prepare("SELECT cli.NOMBCLIE,  cli.RAZOSOCI,  cli.DIRECCION1,  cli.CODIPOST,  cli.TELEFONO1,  cli.RIF,  cli.CODICLIE, est.nombesta, ciu.nombciud 
        from `tcpca` cli INNER JOIN testa est on cli.codiesta=est.codiesta INNER JOIN tciua ciu on ciu.codiciud=cli.codiciud where CODICLIE = :idcliente");
        $clieq->bindParam("idcliente", $data[0]["codiclie"]);
        $clieq->execute();
        $clie = $clieq->fetch();
        $data["cliente"]=$clie;
         
        return $this->renderer->render($response, 'factura.phtml', $data);
    }
    
    public function pedioload(Request $request, Response $response, $args){
             
    $query="SELECT
            DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') AS FECHA,
            DATE_FORMAT(tfacpeda.FECHA, '%Y') AS ANIO,
            DATE_FORMAT(tfacpeda.FECHA, '%m') AS MES,
            DATE_FORMAT(tfacpeda.FECHA, '%d') AS DIA,
            ROUND(
                SUM(
                    tfacpedb.UNIDADES / tinva.unidcaja
                ),
                2
            ) AS CAJAS,
            ROUND(Sum(
                tfacpedb.PRECIO * (
                    tfacpedb.UNIDADES / tinva.unidcaja
                )
            ),2) AS PRECVENT,
            tfacpeda.CODIRUTA,
            DATE_ADD(
            DATE_FORMAT(NOW(), '%Y-%m-%d'),
            INTERVAL - 15 DAY
        ) as desde
        FROM
            tfacpedb
        INNER JOIN tinva ON tfacpedb.CODIPROD = tinva.CODIPROD
        AND LEFT (tinva.codiprod, 1) <> 'Z'
        INNER JOIN tproa ON tproa.CODIGRUP = tinva.CODIGRUP
        INNER JOIN tfacpeda ON tfacpedb.NUMEPEDI = tfacpeda.NUMEPEDI
        WHERE
            tfacpeda.CODIRUTA= ".$_SESSION['user']['details']['CRVV_IDVENDEDOR']."
            AND DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') >= DATE_ADD(
            DATE_FORMAT(NOW(), '%Y-%m-%d'),
            INTERVAL - 15 DAY
        )
        GROUP BY
            DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d')
        ORDER BY
            tfacpeda.FECHA ASC";    
            $stmt = $this->ventor->prepare($query);
            $stmt->execute();
            $data['range'] = $stmt->fetchAll();
        
        $p=count($data['range'])-1;
        $fech=$data['range'][$p]['FECHA'];
        $query="SELECT
                DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') AS FECHA,
                DATE_FORMAT(tfacpeda.FECHA, '%Y') AS ANIO,
                DATE_FORMAT(tfacpeda.FECHA, '%m') AS MES,
                DATE_FORMAT(tfacpeda.FECHA, '%d') AS DIA,
                ROUND(SUM( tfacpedb.UNIDADES / tinva.unidcaja),2) AS cajas,
                ROUND(Sum(tfacpedb.PRECIO * (
                            tfacpedb.UNIDADES / tinva.unidcaja
                        )),2) AS totalVenta,
                tfacpeda.CODIRUTA,
                tproa.CODIGRUP,
                tproa.DESCGRUP
                FROM
                    tfacpedb
                INNER JOIN tinva ON tfacpedb.CODIPROD = tinva.CODIPROD
                AND LEFT (tinva.codiprod, 1) <> 'Z'
                INNER JOIN tproa ON tproa.CODIGRUP = tinva.CODIGRUP
                INNER JOIN tfacpeda ON tfacpedb.NUMEPEDI = tfacpeda.NUMEPEDI
                WHERE
                tfacpeda.CODIRUTA= ".$_SESSION['user']['details']['CRVV_IDVENDEDOR']."
                AND DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') = '".$fech."'
                GROUP BY
                    DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d'),tproa.CODIGRUP
                ORDER BY
                    tfacpeda.FECHA ASC, cajas DESC limit 10";
        $stmt = $this->ventor->prepare($query);
        $stmt->execute();
        $data['grupo'] = $stmt->fetchAll();
        
        
          return $this->renderer->render($response, 'seller.phtml', $data);
     }
    public function listclientes(Request $request, Response $response, $args){
         
          return $this->renderer->render($response, 'clientes.phtml', $args);
     }
    public function chqdevueltos(Request $request, Response $response, $args){
        $data = array();
         $query="SELECT
                    tcpcd.NUMECHEQ,
                    DATE_FORMAT(tcpcd.fecha, '%Y-%m-%d') as FECHA,
                    tcpcd.NOMBBANC,
                    tcpcd.MONTCHEQ,
                    tcpca.NOMBCLIE,
                    tcpcd.CODICLIE
                FROM
                    tcpcd tcpcd,
                    tcpca tcpca,
                    tcpcarut tcpcarut
                WHERE
                    tcpca.codiclie = tcpcd.codiclie
                AND tcpca.codiclie = tcpcarut.codiclie
                AND tcpcd.tipodocu = 'CD'
                AND tcpcarut.codiruta = '02'
                AND tcpcd.fecha <= NOW()
                AND tcpcd.MONTCHEQ > 0 ";
        if($args['ruta']){
            $query.=" AND tcpcarut.CODIRUTA = '".$args['ruta']."'";
        }else{
            $query.=" AND tcpcarut.CODIRUTA = '".$_SESSION['user']['details']['CRVV_IDVENDEDOR']."'";
        }
        $query.="ORDER BY
                    tcpcd.codiclie,
                    tcpcd.fecha ";
        
        
            $stmt = $this->ventor->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
          return $this->renderer->render($response, 'cheques.phtml', $data);
     }
    public function listpagos(Request $request, Response $response, $args){
        $query="SELECT
                    fac.numedocu,
                    DATE_FORMAT(fac.fecha, '%Y-%m-%d') AS fecha,
                    DATE_FORMAT(fac.fechvenc, '%Y-%m-%d') AS fechvenc,
                    DATE_FORMAT(fac.fcancel, '%Y-%m-%d') AS fcancel,
                    fac.monto,
                    fac.codiclie,
                    fac.codiruta,
                    fac.base,
                    fac.monto - fac.base iva,
                    CASE fac.fdiascre
                        WHEN '0' THEN
                            'Contado'
                        ELSE
                            'Crédito'
                        END AS fdiascre,
                         CASE ret.tipocobr
                        WHEN '01' THEN
                            'Efectivo'
                        WHEN '02' THEN
                            'Cheque'
                        WHEN 'RT' THEN
                            ret.tipocobr
                        END AS tipocobr,
                         CASE ret.tipocobr
                        WHEN '01' THEN
                            'Efectivo'
                        WHEN '02' THEN
                            ret.cheque
                        END AS cheque,
                         CASE ret.tipocobr
                        WHEN '01' THEN
                            'Efectivo'
                        WHEN '02' THEN
                            ret.banco
                        END AS banco,
                         clie.limicred,
                         ret.monto montret,
                         CASE ret.tipocobr
                        WHEN 'RT' THEN
                            ret.cheque
                        ELSE
                            ''
                        END AS rete_num,
                         CASE ret.tipocobr
                        WHEN 'RT' THEN
                            ret.monto
                        ELSE
                            ''
                        END AS rete_mont,
                 DATEDIFF(fac.fcancel, fac.fechvenc) ret_dia
                    FROM tcpcc fac INNER JOIN trechisb ret INNER JOIN tcpcarut clie
                    WHERE fac.codiclie = ret.codiclie AND fac.numedocu=ret.numeafec AND fac.codiruta = ret.codiruta
                    AND fac.codiclie = clie.codiclie AND fac.codiruta=clie.codiruta AND fac.tipodocu = 'FA' AND clie.desactiv='0' ";
        if($args['ruta']){
            $query.=" AND clie.CODIRUTA = '".$args['ruta']."'";
        }else{
            $query.=" AND clie.CODIRUTA = '".$_SESSION['user']['details']['CRVV_IDVENDEDOR']."'";
        }
               $query.=" ORDER BY fac.fecha DESC limit 20";
        
        $stmt = $this->ventor->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
          return $this->renderer->render($response, 'listpagos.phtml', $data);
    }
    public function vistaseller(Request $request, Response $response, $args){
        
        $query="SELECT
            DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') AS FECHA,
            DATE_FORMAT(tfacpeda.FECHA, '%Y') AS ANIO,
            DATE_FORMAT(tfacpeda.FECHA, '%m') AS MES,
            DATE_FORMAT(tfacpeda.FECHA, '%d') AS DIA,
            ROUND(
                SUM(
                    tfacpedb.UNIDADES / tinva.unidcaja
                ),
                2
            ) AS CAJAS,
            ROUND(Sum(
                tfacpedb.PRECIO * (
                    tfacpedb.UNIDADES / tinva.unidcaja
                )
            ),2) AS PRECVENT,
            tfacpeda.CODIRUTA,
            DATE_ADD(
            DATE_FORMAT(NOW(), '%Y-%m-%d'),
            INTERVAL - 15 DAY
        ) as desde
        FROM
            tfacpedb
        INNER JOIN tinva ON tfacpedb.CODIPROD = tinva.CODIPROD
        AND LEFT (tinva.codiprod, 1) <> 'Z'
        INNER JOIN tproa ON tproa.CODIGRUP = tinva.CODIGRUP
        INNER JOIN tfacpeda ON tfacpedb.NUMEPEDI = tfacpeda.NUMEPEDI
        WHERE
            tfacpeda.CODIRUTA= ".$_SESSION['user']['details']['CRVV_IDVENDEDOR']."
            AND DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') >= DATE_ADD(
            DATE_FORMAT(NOW(), '%Y-%m-%d'),
            INTERVAL - 15 DAY
        )
        GROUP BY
            DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d')
        ORDER BY
            tfacpeda.FECHA ASC";    
            $stmt = $this->ventor->prepare($query);
            $stmt->execute();
            $data['range'] = $stmt->fetchAll();
        $p=count($data['range'])-1;
        $fech=$data['range'][$p]['FECHA'];
        
    $query="SELECT
                DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') AS FECHA,
                DATE_FORMAT(tfacpeda.FECHA, '%Y') AS ANIO,
                DATE_FORMAT(tfacpeda.FECHA, '%m') AS MES,
                DATE_FORMAT(tfacpeda.FECHA, '%d') AS DIA,
                ROUND(
                        SUM(
                            tfacpedb.UNIDADES / tinva.unidcaja
                        ),
                        2
                    ) AS cajas,
                ROUND(Sum(tfacpedb.PRECIO * (
                            tfacpedb.UNIDADES / tinva.unidcaja
                        )),2) AS totalVenta,
                tfacpeda.CODIRUTA,
                tproa.CODIGRUP,
                tproa.DESCGRUP
                FROM
                    tfacpedb
                INNER JOIN tinva ON tfacpedb.CODIPROD = tinva.CODIPROD
                AND LEFT (tinva.codiprod, 1) <> 'Z'
                INNER JOIN tproa ON tproa.CODIGRUP = tinva.CODIGRUP
                INNER JOIN tfacpeda ON tfacpedb.NUMEPEDI = tfacpeda.NUMEPEDI
                WHERE
                tfacpeda.CODIRUTA= ".$_SESSION['user']['details']['CRVV_IDVENDEDOR']."
                AND DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') = '".$fech."'
                GROUP BY
                    DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d'),tproa.CODIGRUP
                ORDER BY
                    tfacpeda.FECHA ASC, cajas DESC limit 10";
        $stmt = $this->ventor->prepare($query);
        $stmt->execute();
        $data['grupo'] = $stmt->fetchAll();
    
        return $this->renderer->render($response, 'seller.phtml', $data);
     }
    public function cargadepedidos(Request $request, Response $response, $args){
             
    $query="SELECT
            DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') AS FECHA,
            DATE_FORMAT(tfacpeda.FECHA, '%Y') AS ANIO,
            DATE_FORMAT(tfacpeda.FECHA, '%m') AS MES,
            DATE_FORMAT(tfacpeda.FECHA, '%d') AS DIA,
            ROUND(
                SUM(
                    tfacpedb.UNIDADES / tinva.unidcaja
                ),
                2
            ) AS CAJAS,
            ROUND(Sum(
                tfacpedb.PRECIO * (
                    tfacpedb.UNIDADES / tinva.unidcaja
                )
            ),2) AS PRECVENT,
            tfacpeda.CODIRUTA,
            DATE_ADD(
            DATE_FORMAT(NOW(), '%Y-%m-%d'),
            INTERVAL - 15 DAY
        ) as desde
        FROM
            tfacpedb
        INNER JOIN tinva ON tfacpedb.CODIPROD = tinva.CODIPROD
        AND LEFT (tinva.codiprod, 1) <> 'Z'
        INNER JOIN tproa ON tproa.CODIGRUP = tinva.CODIGRUP
        INNER JOIN tfacpeda ON tfacpedb.NUMEPEDI = tfacpeda.NUMEPEDI
        WHERE
            tfacpeda.CODIRUTA= ".$_SESSION['user']['details']['CRVV_IDVENDEDOR']."
            AND DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') >= DATE_ADD(
            DATE_FORMAT(NOW(), '%Y-%m-%d'),
            INTERVAL - 15 DAY
        )
        GROUP BY
            DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d')
        ORDER BY
            tfacpeda.FECHA ASC";    
            $stmt = $this->ventor->prepare($query);
            $stmt->execute();
            $data['range'] = $stmt->fetchAll();
        
        $p=count($data['range'])-1;
        $fech=$data['range'][$p]['FECHA'];
        $query="SELECT
                DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') AS FECHA,
                DATE_FORMAT(tfacpeda.FECHA, '%Y') AS ANIO,
                DATE_FORMAT(tfacpeda.FECHA, '%m') AS MES,
                DATE_FORMAT(tfacpeda.FECHA, '%d') AS DIA,
                ROUND(SUM( tfacpedb.UNIDADES / tinva.unidcaja),2) AS cajas,
                ROUND(Sum(tfacpedb.PRECIO * (
                            tfacpedb.UNIDADES / tinva.unidcaja
                        )),2) AS totalVenta,
                tfacpeda.CODIRUTA,
                tproa.CODIGRUP,
                tproa.DESCGRUP
                FROM
                    tfacpedb
                INNER JOIN tinva ON tfacpedb.CODIPROD = tinva.CODIPROD
                AND LEFT (tinva.codiprod, 1) <> 'Z'
                INNER JOIN tproa ON tproa.CODIGRUP = tinva.CODIGRUP
                INNER JOIN tfacpeda ON tfacpedb.NUMEPEDI = tfacpeda.NUMEPEDI
                WHERE
                tfacpeda.CODIRUTA= ".$_SESSION['user']['details']['CRVV_IDVENDEDOR']."
                AND DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d') = '".$fech."'
                GROUP BY
                    DATE_FORMAT(tfacpeda.FECHA, '%Y-%m-%d'),tproa.CODIGRUP
                ORDER BY
                    tfacpeda.FECHA ASC, cajas DESC limit 10";
        $stmt = $this->ventor->prepare($query);
        $stmt->execute();
        $data['grupo'] = $stmt->fetchAll();
        $data['ruta'] = $args['ruta'];
        return $this->renderer->render($response, 'cargar.phtml', $data);
     }
    function vistaadmin(Request $request, Response $response, $args){
        return $this->renderer->render($response, 'admin.phtml', $args);
    }
    
    function addpermiso(Request $request, Response $response, $args){
        $query="SELECT codemodu, iconmodu, descmodu, linkmodu FROM modulos";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $data['modulos'] = $stmt->fetchAll();
        $data['user'] = $args['user'];
        
        foreach($data['modulos'] as $key => $m){
            $Pquery="SELECT codetype, codemodu, codesmod, escrpermi, editpermi FROM permisos where codemodu = ".$data['modulos'][$key]['codemodu']." and codetype =".$args['user']."  ";
            $stmt = $this->db->prepare($Pquery);
            $stmt->execute();
            $resp=$stmt->fetchAll();
            if( !empty($resp) )
                $data['modulos'][$key]['asignado']=1;
            else
                $data['modulos'][$key]['asignado']=0;
            
            $Squery="SELECT codesmod,descsmod,linksmod,codemodu,iconsmod FROM submodulo where codemodu = ".$data['modulos'][$key]['codemodu'];
            $stmt = $this->db->prepare($Squery);
            $stmt->execute();
            $data['modulos'][$key]['submodulos'] = $stmt->fetchAll();
            foreach( $data['modulos'][$key]['submodulos'] as $k => $sb){
                
                $SBquery="SELECT codetype, codemodu, codesmod, escrpermi, editpermi FROM permisos 
                where codemodu = ".$data['modulos'][$key]['codemodu']."
                and codesmod=".$sb[$k]['codesmod']." 
                and codetype =".$args['user'];
                $stmt = $this->db->prepare($SBquery);
                $stmt->execute();
                $resp=$stmt->fetchAll();
                
                if( !empty($resp) )
                    $data['modulos'][$key]['submodulos'][$k]['asignado']=1;
                else
                    $data['modulos'][$key]['submodulos'][$k]['asignado']=0;
            }
        }
        
                
        
        return $this->renderer->render($response, 'permiso.phtml', $data);
    }
    function asignaremp(Request $request, Response $response, $args){
        $query="SELECT
                    cargos.descripcion AS cargo,
                    departamentos.descripcion AS departamento,
                    cargos.codecarg,
                    departamentos.codedept
                FROM
                    departamentos
                INNER JOIN cargos ON departamentos.codedept = cargos.codedpt";
        $stmt = $this->principal->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $this->renderer->render($response, 'asignaremp.phtml', $data);
    }
    
    function savepermisos(Request $request, Response $response, $args){
        
        $input = $request->getParsedBody();
        $user=$input['user'];
        $query="DELETE FROM permisos WHERE codetype='$user' ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        foreach($input['modulo'] as $m){
           $stmt = $this->db->prepare("INSERT INTO permisos (codetype, codemodu, codesmod)VALUES(".$user.", ".$m.", 0)");
           $stmt->execute();
        }
        return $response->withJson($input);
    }
}

