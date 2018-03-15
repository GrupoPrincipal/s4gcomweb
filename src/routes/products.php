<?php

$app->get('/product/{id}', function ($request, $response, $args) {
    $stmt = $this->db->prepare("SELECT
        `articulos`.`CODIEMPR`, `articulos`.`ARTV_IDARTICULO`, `articulos`.`ARTV_DESCART`, `articulos`.`ARTV_DESCARTDETA`, `articulos`.`ARTV_IDAGRUPAA`,
        `articulos`.`ARTV_IDAGRUPAB`, `articulos`.`ARTV_IDAGRUPAC`, `articulos`.`ARTV_IDTIPO`, `articulos`.`ARTV_IDPRESEN`, `articulos`.`ARTV_IDMARCA`,
        `articulos`.`ARTV_IDDEPAR`, `articulos`.`ARTV_IDENVASE`, `articulos`.`ARTV_IDCATEGORIA`, `articulos`.`ARTN_UNIXCAJA`, `articulos`.`ARTN_CONTXUNI`,
        `articulos`.`ARTN_COSTOANTE`, `articulos`.`ARTN_COSTOACTU`, `articulos`.`ARTN_COSTOPROM`, `articulos`.`ARTN_PORCIVA`, `articulos`.`ARTN_LISAEA`,
        `articulos`.`ARTV_GRADALCO`, `articulos`.`ARTN_NACIMPOR`, `articulos`.`ARTV_DESCUNIMIN`, `articulos`.`ARTV_DESCUNICOM`, `articulos`.`ARTN_VOLUMEN`,
        `articulos`.`ARTN_PESO`, `articulos`.`ARTN_SADA`, `articulos`.`ARTV_DESCARTCORTA`, `articulos`.`ARTV_REGISSANI`, `articulos`.`ARTV_CPE`, `articulos`.`ARTV_ESPECIE`,
        `articulos`.`ARTV_PROCEDENCIA`, `articulos`.`ARTN_PORKILOS`, `articulos`.`ARTN_STATUS`, `articulos`.`ARTN_CONBALANZA`, `articulos`.`ARTN_PORCUTIL`,
        `articulos`.`ARTN_PRECIOCAJ`, `articulos`.`ARTN_PRECIOUNI`, `articulos`.`ARTN_PREIVACAJ`, `articulos`.`ARTN_PREIVAUND`, `articulos`.`ARTN_PORCUTILM`,
        `articulos`.`ARTN_RUTAFOTO`, `articulos`.`ARTV_IDDEPOVENT`, `precios`.`CODIEMPR`, `precios`.`PREDV_TIPO`, `precios`.`PREDV_IDARTICULO`, `precios`.`PREDN_PORCUTIL`,
        `precios`.`PREDN_PRECIOCAJ`, `precios`.`PREDN_PRECIOUND`, `precios`.`PREDN_IMPUPCAJ`, `precios`.`PREDN_IMPUPUND`, `precios`.`PREDN_PREIVACAJ`, `precios`.`PREDN_PREIVAUND`
      FROM `tarticulos` AS `articulos`
      INNER JOIN `tprecios` AS `precios`
        ON `precios`.`PREDV_IDARTICULO` = `articulos`.`ARTV_IDARTICULO`
      WHERE `articulos`.`ARTV_IDARTICULO` = :id");
    $stmt->bindParam("id", $args['id']);
    $stmt->execute();
    $data = $stmt->fetchObject();
    return $this->response->withJson($data);
});

$app->get('/products[/{id}]', function ($request, $response, $args) {
    $stmt = $this->db->prepare("SELECT
        `articulos`.`CODIEMPR`, `articulos`.`ARTV_IDARTICULO`, `articulos`.`ARTV_DESCART`, `articulos`.`ARTV_DESCARTDETA`, `articulos`.`ARTV_IDAGRUPAA`,
        `articulos`.`ARTV_IDAGRUPAB`, `articulos`.`ARTV_IDAGRUPAC`, `articulos`.`ARTV_IDTIPO`, `articulos`.`ARTV_IDPRESEN`, `articulos`.`ARTV_IDMARCA`,
        `articulos`.`ARTV_IDDEPAR`, `articulos`.`ARTV_IDENVASE`, `articulos`.`ARTV_IDCATEGORIA`, `articulos`.`ARTN_UNIXCAJA`, `articulos`.`ARTN_CONTXUNI`,
        `articulos`.`ARTN_COSTOANTE`, `articulos`.`ARTN_COSTOACTU`, `articulos`.`ARTN_COSTOPROM`, `articulos`.`ARTN_PORCIVA`, `articulos`.`ARTN_LISAEA`,
        `articulos`.`ARTV_GRADALCO`, `articulos`.`ARTN_NACIMPOR`, `articulos`.`ARTV_DESCUNIMIN`, `articulos`.`ARTV_DESCUNICOM`, `articulos`.`ARTN_VOLUMEN`,
        `articulos`.`ARTN_PESO`, `articulos`.`ARTN_SADA`, `articulos`.`ARTV_DESCARTCORTA`, `articulos`.`ARTV_REGISSANI`, `articulos`.`ARTV_CPE`, `articulos`.`ARTV_ESPECIE`,
        `articulos`.`ARTV_PROCEDENCIA`, `articulos`.`ARTN_PORKILOS`, `articulos`.`ARTN_STATUS`, `articulos`.`ARTN_CONBALANZA`, `articulos`.`ARTN_PORCUTIL`,
        `articulos`.`ARTN_PRECIOCAJ`, `articulos`.`ARTN_PRECIOUNI`, `articulos`.`ARTN_PREIVACAJ`, `articulos`.`ARTN_PREIVAUND`, `articulos`.`ARTN_PORCUTILM`,
        `articulos`.`ARTN_RUTAFOTO`, `articulos`.`ARTV_IDDEPOVENT`, `precios`.`CODIEMPR`, `precios`.`PREDV_TIPO`, `precios`.`PREDV_IDARTICULO`, `precios`.`PREDN_PORCUTIL`,
        `precios`.`PREDN_PRECIOCAJ`, `precios`.`PREDN_PRECIOUND`, `precios`.`PREDN_IMPUPCAJ`, `precios`.`PREDN_IMPUPUND`, `precios`.`PREDN_PREIVACAJ`, `precios`.`PREDN_PREIVAUND`
      FROM `tarticulos` AS `articulos`
      INNER JOIN `tprecios` AS `precios`
        ON `precios`.`PREDV_IDARTICULO` = `articulos`.`ARTV_IDARTICULO`
      WHERE `articulos`.`CODIEMPR` LIKE :query");
    $query = "%".(isset($args['id']) ? $args['id'] : "")."%";
    $stmt->bindParam("query", $query);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $this->response->withJson($data);
});

$app->get('/product/image/{id}', function ($request, $response, $args) {
    $stmt = $this->db->prepare("SELECT
      `CODIEMPR`, `IMGV_IDARTICULO`, `IMGV_IMAGEN1`, `IMGV_IMAGEN2`, `IMGV_IMAGEN3`, `IMGV_IMAGEN4`, `IMGV_IMAGEN5`
      FROM `tart_imagenes`
      WHERE `IMGV_IDARTICULO` = :id");
    $stmt->bindParam("id", $args['id']);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $this->response->withJson($data);
});
