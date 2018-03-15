<?php

namespace App\Controller;

use App\Core\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthController extends Controller
{
    public function login(Request $request, Response $response, $args)
    {
        
       
        $input = $request->getParsedBody();
        $stmt = $this->db->prepare("SELECT `CUENV_IDCUENTA`, `CUENV_USUARIO`, `CUENV_PASSWORD`, `CUENV_TIPO` FROM `tcuentas` WHERE `CUENV_USUARIO` = :username AND `CUENV_PASSWORD` = :password LIMIT 1");
        $stmt->bindParam("username", $input['username']);
        $stmt->bindParam("password", md5($input['password']));
        $stmt->execute();
        $account = $stmt->fetchAll();

        if(count($account) !== 1)
        {
            return $response->withJson(array(
                "error" => "Usuario o Clave Incorrecta"
            ));
        }

        $account = $account[0];
        $stmt->closeCursor();       

        if($account["CUENV_TIPO"] != 0)
        {
            $query = null;

            switch($account["CUENV_TIPO"])
            {
                case 1:
                    $query = "SELECT `CRVN_IDCUENTA`, `CRVV_CODIEMPR`, `CRVV_IDVENDEDOR` FROM `tcuentas_ven` WHERE `CRVN_IDCUENTA` = :accountID LIMIT 1";
                    break;
                case 2:
                    $query = "SELECT `CRCN_IDCUENTA`, `CRCV_CODIEMPR`, `CRCV_IDCLIENTE`,CRCV_IDVENDEDOR FROM `tcuentas_cli` WHERE `CRCN_IDCUENTA` = :accountID LIMIT 1";
                    break;
            }

            $stmt = $this->db->prepare($query);
            $stmt->bindParam("accountID", $account["CUENV_IDCUENTA"]);
            $stmt->execute();
            $join = $stmt->fetch();

            $account["details"] = $join;
            $accountID = $account["CUENV_TIPO"] == 1 ? $account["details"]["CRVV_IDVENDEDOR"] : $account["details"]["CRCV_IDCLIENTE"];
            $accountEMP = $account["CUENV_TIPO"] == 1 ? $account["details"]["CRVV_CODIEMPR"] : $account["details"]["CRCV_CODIEMPR"];

            switch($account["CUENV_TIPO"])
            {
                case 1:
                    $query = "SELECT `VENDV_CODIEMPR`, `VENDV_IDVENDEDOR`, `VENDV_NOMBRE` FROM `tvendedores` WHERE `VENDV_IDVENDEDOR` = :accountID AND VENDV_CODIEMPR = :accountEMP LIMIT 1";
                    break;
                case 2:
                    $query = "SELECT `CLIEV_CODIEMPR`, `CLIEV_IDCLIENTE`, `CLIEV_RIF`, `CLIEV_RAZONSOC`, `CLIEV_NOMBRE`, `CLIEV_PROPIETARIO`, `CLIEV_ENCARGADO`, `CLIEV_DIRECCION1`, `CLIEV_DIRECCION2`, `CLIEV_DIRECCION3`, `CLIEV_DIRECCION4`, `CLIEV_DIRECCION5`, `CLIEV_TELEFONO2`, `CLIEV_MOVIL`, `CLIEV_IDESTADO`, `CLIEV_IDCIUDAD`, `CLIEV_IDTMUNICIPIO`, `CLIEV_IDPARROQUIA`, `CLIEV_IDURBANIZACION`, `CLIEV_IDZONA`, `CLIEV_IDGRUPO`, `CLIEV_IDSUBGRUPO`, `CLIEN_STATUS`, `CLIEV_EMAIL`, `CLIEN_DIASDESPACHO`, `CLIEV_HORADESPACHO`, `CLIEV_GRUPCANAL`, `CLIEV_GRUPOFREC`, `CLIET_ULTIVENTA`, `CLIEN_LATITUD`, `CLIEN_LONGITUD`, `CLIEV_CODIGOPOSTAL`, `CLIEV_CONTRIBUYE`, `CLIEV_DIASCRED`, `CLIEV_LIMICRED` FROM `tclientes` 
                      WHERE `CLIEV_IDCLIENTE` = :accountID AND CLIEV_CODIEMPR = :accountEMP LIMIT 1";
                    break;
            }


            $stmt->closeCursor();
            $stmt = $this->db->prepare($query);
            $stmt->bindParam("accountID", $accountID);
            $stmt->bindParam("accountEMP", $accountEMP);
            $stmt->execute();
            $info = $stmt->fetch();

            $account["info"] = $info;
        }

        $_SESSION["user"] = $account;
        return $response->withJson($account);
    }

    public function logout(Request $request, Response $response, $args)
    {
      if (ini_get("session.use_cookies")) {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
          );
      }

      session_destroy();
      return $response->withJson(array(
          "status" => "200"
      ));
    }
}
