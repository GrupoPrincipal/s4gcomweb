<?php

$app->post('/user', function ($request, $response) {
    $input = $request->getBody();
    return $this->response->withJson($request);
    $sql = "INSERT INTO `tcuentas`(`CUENV_USUARIO`, `CUENV_PASSWORD`, `CUENV_TIPO`) VALUES (:username, :password, :type)";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam("username", $input['username']);
    $stmt->bindParam("password", $input['password']);
    $stmt->bindParam("type", $input['type']);
    $stmt->execute();
    $input['id'] = $this->db->lastInsertId();
    return $this->response->withJson($input);
});
