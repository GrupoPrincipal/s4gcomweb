<?php

$app->get('/', 'FactController:inicio');

        $container = $app->getContainer();
        $container['upload_directory'] = __DIR__ . '/uploads';

  


$app->group('', function() {
    $this->get('/pedido', 'FactController:pedioload');
    $this->group('/pedidos', function() {
        $this->get('/{ruta}', 'FactController:pedidosruta');
        $this->post('/facturas', 'FactController:pedidosrango');
        $this->get('/detalle/{pedido}', 'FactController:pedidodetail');
        $this->get('/devolucion/{pedido}', 'FactController:pedidodevolucion');
        $this->get('/factura/{pedido}', 'FactController:pedidofactura');
   });
    
    
    
    $this->get('/asignaremp', 'FactController:asignaremp');
    $this->get('/permiso/{user}', 'FactController:addpermiso');
    $this->post('/savepermisos', 'FactController:savepermisos');
    
    
    $this->get('/cheques', 'FactController:chqdevueltos');
    $this->get('/cheques/{ruta}', 'FactController:chqdevueltos');
    
    $this->get('/pago', 'FactController:listpagos');
    $this->get('/pago/{ruta}', 'FactController:listpagos');
    
    $this->get('/clientes', 'FactController:listclientes');
    
    $this->post('/loadimage', 'FactController:loadimage');
    $this->post('/minimo', 'FactController:addminimo');
    $this->get('/canales', 'FactController:canales');
    
    
    
    $this->get('/menu', 'FactController:menu');
    $this->get('/menutotal', 'FactController:menutotal');
    $this->get('/limite', 'FactController:admlimit');
    
    $this->get('/proveedores', 'FactController:admproduct');
        
        $this->get('/pedidos','FactController:pedidos');

     
        $this->get('/notfound', function($request, $response, $args){
            return $this->renderer->render($response, '404.phtml', $args);
        });
    
        
    
        $this->get('/vencidos', 'FactController:vencidos');
    
        $this->get('/promociones-lista', 'FactController:promocioneslista');
        $this->get('/promociones-new', 'FactController:promociones');
        
    
        $this->get('/cuentas', 'FactController:cuentas');
        $this->get('/facturaxpagar', 'FactController:facturaxpagar');
      
        $this->get('/fpagosday', 'FactController:fpagosday');
        
        //productos nuevos y descontinnuados
        $this->get('/nuevos', 'FactController:artnuevs');
        $this->get('/descontinuados', 'FactController:artdesct');
        $this->get('/descontinuados/{pag}', 'FactController:artdesct');
    
        $this->get('/detalle/{pedido}', 'FactController:detalle');

        $this->get('/abonar/{factura}/{monto}', 'FactController:abono');
        $this->get('/pagos/{factura}', 'FactController:pagosfactura');
        $this->get('/factura/{pedido}', 'FactController:detalle_factura');
        $this->get('/devoluciones/{pedido}', 'FactController:devoluciones');
        $this->get('/cpantalla/{pagos}', 'FactController:cpantalla');  

        $this->post('/createAbono', 'FactController:create'); 
        $this->post('/createAbonos', 'FactController:creates'); 
        $this->post('/pagosday/{rutas}/{fechaini}/{fechafin}', 'FactController:pagosday');
      
        $this->get('/createList/{factura}/{montos}', 'FactController:createFact');
    
        $this->get('/asignarmod', 'FactController:asignarmod');
        $this->get('/asignarmodpro/{cliente}', 'FactController:asignarmodpro');
        $this->get('/savemod/{id}', 'FactController:savemod');

})->add(function ($request, $response, $next) {
        if(!isset($_SESSION["user"]))
            return $response->withJson(array(
                "error" => "401 Unauthorized Access"
            ));
        $response = $next($request, $response);
        return $response;

    });

$app->group('/api', function() {
    
    
   /* $app->get('/pedidos', function ($request, $response, $args) {
     return $this->renderer->render($response, 'main.phtml', $args);
   
    });*/
    $this->post('/savepromo', 'FactController:savepromo');
    
    
    $this->get('/limit', 'FactController:limit');
    $this->get('/limites', 'FactController:limites');
    
    $this->group('/proveedor', function() {
        $this->post('[/]', 'FactController:desctvAll');       
    });
    
    
    $this->group('/auth', function() {
        $this->post('/login', 'AuthController:login')->setName('auth.login');
        $this->post('/logout', 'AuthController:logout')->setName('auth.logout');
       
    });

    $this->group('', function() {
        $this->group('/account', function() {
            $this->get('[/]', 'AccountController:fetchAll');
            $this->post('[/]', 'AccountController:create');
            $this->group('/{id}', function() {
                $this->get('[/]', 'AccountController:fetch');
                $this->put('[/]', 'AccountController:update');
                $this->delete('[/]', 'AccountController:delete');
            });
        });

        $this->group('/client', function() {
            $this->get('[/]', 'ListController:fetchClients');
            $this->get('/detail', 'FactController:detail_fact');
            $this->get('/{id}[/]', 'ListController:fetchClient');
        });
        //PARA QUE EL ADMINISTRADOR MONTE PEDIDOS
        

        $this->group('/seller', function() {
            $this->get('[/]', 'ListController:fetchSellers');
            $this->get('/{id}[/]', 'ListController:fetchSeller');
        });
        $this->group('/inicio', function() {
            $this->get('[/]', 'ListController:fetchSellers');
        });

        $this->group('/company', function() {
            $this->get('[/]', 'ListController:fetchCompanies');
            $this->get('/{id}[/]', 'ListController:fetchCompany');
        });

        $this->group('/product', function() {
            $this->get('[/]', 'ListController:fetchProducts');
            $this->get('/client[/]', 'ListController:fetchClientProducts');
            $this->get('/groups[/]', 'ListController:fetchGroups');
           $this->group('/{id}', function() {
                $this->get('[/]', 'ListController:fetchProduct');
                $this->get('/image[/]', 'ListController:fetchProductImage');
            });
            
        });

        $this->group('/order', function() {
            $this->get('[/]', 'OrderController:fetchOrders');
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
    $this->group('/facturas', function() {
        $this->get('[/]', 'FactController:facturas');
        $this->get('/ultima[/]', 'FactController:factura');
    });
    
    $this->group('/corriente', function() {
        $this->get('[/]', 'FactController:corriente');
    });
    

});
//################################################
//####PARA QUE EL ADMINISTRADOR MONTE PEDIDOS#####
//################################################

$app->group('/cargar', function() {
    $this->get('', 'FactController:cargadepedidos');
    
    $this->group('/api', function() {
    
    
   /* $app->get('/pedidos', function ($request, $response, $args) {
     return $this->renderer->render($response, 'main.phtml', $args);
   
    });*/
    $this->post('/savepromo', 'FactController:savepromo');
    
    
    $this->get('/limit', 'FactController:limit');
    $this->get('/limites', 'FactController:limites');
    
    $this->group('/proveedor', function() {
        $this->post('[/]', 'FactController:desctvAll');       
    });
    
    
    $this->group('/auth', function() {
        $this->post('/login', 'AuthController:login')->setName('auth.login');
        $this->post('/logout', 'AuthController:logout')->setName('auth.logout');
       
    });

    $this->group('', function() {
        $this->group('/account', function() {
            $this->get('[/]', 'AccountController:fetchAll');
            $this->post('[/]', 'AccountController:create');
            $this->group('/{id}', function() {
                $this->get('[/]', 'AccountController:fetch');
                $this->put('[/]', 'AccountController:update');
                $this->delete('[/]', 'AccountController:delete');
            });
        });

        $this->group('/client', function() {
            $this->get('[/]', 'ListController:fetchClients');
            $this->get('/detail', 'FactController:detail_fact');
            $this->get('/{id}[/]', 'ListController:fetchClient');
        });
        //PARA QUE EL ADMINISTRADOR MONTE PEDIDOS
        

        $this->group('/seller', function() {
            $this->get('[/]', 'ListController:fetchSellers');
            $this->get('/{id}[/]', 'ListController:fetchSeller');
        });
        $this->group('/inicio', function() {
            $this->get('[/]', 'ListController:fetchSellers');
        });

        $this->group('/company', function() {
            $this->get('[/]', 'ListController:fetchCompanies');
            $this->get('/{id}[/]', 'ListController:fetchCompany');
        });

        $this->group('/product', function() {
            $this->get('[/]', 'ListController:fetchProducts');
            $this->get('/client[/]', 'ListController:fetchClientProducts');
            $this->get('/groups[/]', 'ListController:fetchGroups');
           $this->group('/{id}', function() {
                $this->get('[/]', 'ListController:fetchProduct');
                $this->get('/image[/]', 'ListController:fetchProductImage');
            });
            
        });

        $this->group('/order', function() {
            $this->get('[/]', 'OrderController:fetchOrders');
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
    $this->group('/facturas', function() {
        $this->get('[/]', 'FactController:facturas');
        $this->get('/ultima[/]', 'FactController:factura');
    });
    
    $this->group('/corriente', function() {
        $this->get('[/]', 'FactController:corriente');
    });
    

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
