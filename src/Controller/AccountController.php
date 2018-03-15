<?php

namespace App\Controller;

use App\Core\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AccountController extends Controller
{
    public function fetchAll(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `CUENV_IDCUENTA`, `CUENV_USUARIO`, `CUENV_PASSWORD`, `CUENV_TIPO` FROM `tcuentas` WHERE `CUENV_TIPO` != 0 ORDER BY `CUENV_USUARIO`");
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    }

    public function create(Request $request, Response $response, $args)
    {
        $input = $request->getParsedBody();

        $sql = "INSERT INTO `tcuentas`(`CUENV_USUARIO`, `CUENV_PASSWORD`, `CUENV_TIPO`) VALUES (:username, :password, :type)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("username", $input['username']);
        
        $stmt->bindParam("password", md5($input['password']));
        $stmt->bindParam("type", $input['type']);
        $stmt->execute();

        $accountID = $this->db->lastInsertId();
        $relationID = ($input['type'] == 1) ? $input['seller'] : explode(":", $input['client'])[1];
        $sIdEmpresa = "";
        //return $response->withJson($input['client']['company']);
        switch($input['type'])
        {
            case 1:
                $sIdEmpresa = $input['company'];
                $query = "INSERT INTO `tcuentas_ven`(`CRVN_IDCUENTA`, `CRVV_CODIEMPR`,  `CRVV_IDVENDEDOR`) VALUES (:id, :company, :relation)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam("id", $accountID);
                $stmt->bindParam("company", $sIdEmpresa);
                $stmt->bindParam("relation", $relationID);
                $stmt->execute();
                break;

            case 2:
                $sIdEmpresa =  explode(":", $input['client'])[2];
                $sIdVendedor = $input['seller'];
                $query = "INSERT INTO `tcuentas_cli`(`CRCN_IDCUENTA`, `CRCV_CODIEMPR`,  `CRCV_IDCLIENTE`,CRCV_IDVENDEDOR) VALUES (:id, :company, :relation, :idvendedor)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam("id", $accountID);
                $stmt->bindParam("company", $sIdEmpresa);
                $stmt->bindParam("relation", $relationID);
                $stmt->bindParam("idvendedor", $sIdVendedor);
                $stmt->execute();
                break;
        }        
        return $response->withJson($input);
    }

    public function fetch(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("SELECT `CUENV_USUARIO`, `CUENV_PASSWORD`, `CUENV_TIPO` FROM `tcuentas` WHERE `CUENV_TIPO` != 0  AND CUENV_IDCUENTA = :id ORDER BY `CUENV_USUARIO` LIMIT 1");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $data = $stmt->fetch();
        return $response->withJson($data);
    }

    public function update(Request $request, Response $response, $args)
    {
        $input = $request->getParsedBody();
        $stmt = $this->db->prepare("UPDATE `tcuentas` SET `CUENV_USUARIO`= :username, `CUENV_PASSWORD`= :password WHERE `CUENV_IDCUENTA` = :id");
        $stmt->bindParam("id", $args['id']);
        $stmt->bindParam("username", $input['username']);
        $stmt->bindParam("password", md5($input['password']));
        $stmt->execute();
        return $response->withJson(array(
            "id" => $args['id'],
            "username" => $input['username']
        ));
    }

    public function delete(Request $request, Response $response, $args)
    {
        $stmt = $this->db->prepare("DELETE FROM `tcuentas` WHERE `CUENV_IDCUENTA` = :id");
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        return $response->withJson($args);
    }
}
