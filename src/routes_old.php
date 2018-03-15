<?php

/*
 * Las rutas estan preparadas para usar controladores, al final del archivo
 * esta otra forma de manejar estas acciones que es de forma directa. Tambien
 * existe el otro nivel que es manejar la persistencia de la data con ORM,
 * el framework ya esta preparado y seteado para manejar DOCTRINE, sin embargo
 * no se utiliza porque no esta la BD definida completamente y no existen
 * modelos o entidades que generar para el modelo MVC
 *
 * La carpeta "routes" no es utilizada y puede ser eliminada, sin embargo existe
 * para que se vea el ejemplo de como trabajar SLIM FRAMEWORK utilizando rutas
 * sencillas sin el MVC.
 *
 * AuthController
 *    login: valida el inicio de sesion
 *    Logout: destruye la sesion actual
 *
 * UserController
 *    fetch: obtiene un ususario en especifico
 *    fetchAll: obtiene todos los usuarios de la empresa actual
 *    create: crea un nuevo usuario en la empresa actual
 *    update: actualiza el usuario X que pertenece a la empresa actual
 *    delete: elimina el usuario X de la empresa actual
 *    fetchRelations: obtiene las relaciones CLIENTE-VENDEDOR
 *    updateRelation: actualiza las relaciones CLIENTE-VENDEDOR
 *
 * ListController
 *    fetchClient: obtiene un cliente especifico de la empresa actual
 *    fetchClients: obtiene todos los clientes de la empresa actual
 *    fetchSeller: obtiene un vendedor especifico de la empresa actual
 *    fetchSellers: obtiene todos los vendedores de la empresa actual
 *    fetchCompany: obtiene una empresa en especifico
 *    fetchCompanies: obtiene todas las empresas registradas
 *    fetchProduct: obtiene un producto especifico de la empresa actual
 *    fetchProducts: obtiene todos los productos de la empresa actual
 *    fetchClientProducts: obtiene todos los productos de todas las empresas del cliente actual
 *    fetchProductImage: obtiene las imagenes del producto especifico
 *
 * OrderController
 *    fetchOrder: obtiene la orden especifica
 *    fetchOrders: obtiene todas las ordenes del vendedor actual
 *    fetchClientOrders: obtiene todas las ordenes temporales del cliente actual
 *    generateOrder: genera un pedido a partir de la orden del vendedor actual
 *    generateClientOrder: genera una orden a partir de la orden temporal del cliente actual
 *    cancelOrder: cambiar el status de la orden a cancelada seteando la respectiva fecha
 *    approveOrder: pasar la orden a pedido si el cliente lo aprueba
 *    fetchRequest: obtiene un pedido en especifica
 *    fetchRequests: obtiene todos los pedidos del vendedor actual
 *    fetchClientRequests: obtiene todos los pedidos del cliente actual
 *    generateRequest: crea un pedido sin orden relacionada
 *
 * La tabla de se estructura de la siguiente manera:
 *    tordenes
 *      ID
 *      CODIEMPR
 *      CLIERIF
 *      GEN_TYPE
 *        1: CREA VENDEDOR
 *        2: CREA CLIENTE
 *      STATUS
 *        0: GENERADA
 *        1: CANCELADA POR VENDEDOR
 *        2: CANCELADA POR CLIENTE
 *        3: PROCESADA (A PEDIDO)
 *      FECHA_GEN (fecha de creacion)
 *      FECHA_ACTU (cuando se actualiza el status)
 *      TOTAL
 *
 *    tordenes_art
 *      IDORDEN
 *      IDART
 *      CANT
 *      MONT
*/
$app->get('/', function ($request, $response, $args) {
    if(isset($_SESSION["user"])) {
        switch($_SESSION["user"]["CUENV_TIPO"]) {
            case 0: return $this->renderer->render($response, 'admin.phtml', $args);
            case 1: return $this->renderer->render($response, 'seller.phtml', $args);
            case 2: return $this->renderer->render($response, 'main.phtml', $args);
        }
    } else {
        return $this->renderer->render($response, 'login.phtml', $args);
    }
});

$app->group('/api', function() {
    $this->group('/auth', function() {
        $this->post('/login', 'AuthController:login')->setName('auth.login');
        $this->post('/logout', 'AuthController:logout')->setName('auth.logout');
    });

    $this->group('', function() {
        $this->group('/user', function() {
            $this->get('[/]', 'UserController:fetchAll');
            $this->post('[/]', 'UserController:create');
            $this->group('/relation', function() {
                $this->get('[/]', 'UserController:fetchRelations');
                $this->post('[/]', 'UserController:updateRelation');
            });
            $this->group('/{id}', function() {
                $this->get('[/]', 'UserController:fetch');
                $this->put('[/]', 'UserController:update');
                $this->delete('[/]', 'UserController:delete');
            });
        });

        $this->group('/client', function() {
            $this->get('[/]', 'ListController:fetchClients');
            $this->get('/{id}[/]', 'ListController:fetchClient');
        });

        $this->group('/seller', function() {
            $this->get('[/]', 'ListController:fetchSellers');
            $this->get('/{id}[/]', 'ListController:fetchSeller');
        });

        $this->group('/company', function() {
            $this->get('[/]', 'ListController:fetchCompanies');
            $this->get('/{id}[/]', 'ListController:fetchCompany');
        });

        $this->group('/product', function() {
            $this->get('[/]', 'ListController:fetchProducts');
            $this->get('/client[/]', 'ListController:fetchClientProducts');
            $this->group('/{id}', function() {
                $this->get('[/]', 'ListController:fetchProduct');
                $this->get('/image[/]', 'ListController:fetchProductImage');
            });
        });

        $this->group('/order', function() {
            $this->get('[/]', 'OrderController:fetchOrders');
            $this->post('[/]', 'OrderController:generateOrder');
            $this->get('/client[/]', 'OrderController:fetchClientOrders');
            $this->post('/client[/]', 'OrderController:generateClientOrder');
            $this->group('/{id}', function() {
                $this->get('[/]', 'OrderController:fetchOrder');
                $this->put('/cancel[/]', 'OrderController:cancelOrder');
                $this->put('/approve[/]', 'OrderController:approveOrder');
            });
        });

        $this->group('/request', function() {
            $this->get('[/]', 'OrderController:fetchRequests');
            $this->post('[/]', 'OrderController:generateRequest');
            $this->get('/client[/]', 'OrderController:fetchClientRequests');
            $this->group('/{id}', function() {
                $this->get('[/]', 'OrderController:fetchRequest');
            });
        });
    })->add(function ($request, $response, $next) {
        if(!isset($_SESSION["user"]))
            return $response->withJson(array(
                "error" => "401 Unauthorized Access"
            ));
        $response = $next($request, $response);
        return $response;
    });
});

/**
 * LogIn /login: POST
 * LogOut /logout: POST
 */
//require __DIR__ . '/routes/session.php';

/**
 * Get Produt Info /proudct/{id}: GET
 * Get Produts List /proudcts/{id}: GET
 *
 * var {id}
 *  default: filter by any company
 */
//require __DIR__ . '/routes/products.php';

/**
 * Create New User /user: POST
 * Get Users List /users: GET
 * Get User Info /user/{id}: GET
 * Update User /user/{id}: PUT
 * Delete User /user/{id}: DELETE
 */
//require __DIR__ . '/routes/users.php';
