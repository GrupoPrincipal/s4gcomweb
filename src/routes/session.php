<?php

$app->post('/login', function ($request, $response, $args) {
    /*return $this->response->withJson(array(
      "err" => "Usuario/Clave incorrecta"
    ));*/
    return $this->response->withJson(array(
      "name" => "test",
      "email" => "test@mail.com"
    ));
    $input = $request->getParsedBody();
    $stmt = $this->db->prepare("SELECT * FROM tasks WHERE UPPER(task) LIKE :query ORDER BY task");
    $query = "%".$args['query']."%";
    $stmt->bindParam("query", $query);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $this->response->withJson($data);
});

$app->post('/logout', function ($request, $response, $args) {
    /* TODO */
    return $this->response->withJson(array(
      "SessionHandler" => false
    ));
});
