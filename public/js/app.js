$(function(){
    
  $(this).on("pagebeforeshow", function(event){
     
    var $page = $(event.target);    
    switch($page.attr("id")){
      case "checkout-page":
            
            
            
        if(simpleCart.quantity() < 1) {
            
            
          alert("No hay productos agregados");
          $.mobile.back();
          return false;
        }
          var process_checkout = function(event){
          var productList = [];
          var isClient = $(this).hasClass("ui-btn-checkout");
          simpleCart.each(function(item , x){
            productList.push({
              code: item.get("code"),
              price: item.get("price"),              
              quantity: item.get("quantity"),
              iva: item.get("iva"),
              des: item.get("des"),
              rel: item.get("rel"),
              company: item.get("codiempr")
            });
          });
           
            
            
            
        
          $.ajax({
            url: server + (isClient ? "order/client" : "request"),
            type: "POST",
            dataType: "json",
            data: {
              client: _tmp.clientID,
              company: _tmp.clientEmpr,
              products: productList,
              pedido: _tmp.numePED
            },
          }).done(function(response){            
            simpleCart.trigger("completeCheckout", [response.id,response.monto,response.cajas,(isClient ? "client" : "seller")]);
          });
        };

        $(document).off("click", "a.ui-btn-checkout, a.ui-btn-checkout-s")
                   .on("click", "a.ui-btn-checkout, a.ui-btn-checkout-s", process_checkout);
        
        $("div.simpleCart_items").remove();
        $page.find("div.cart-items").empty().append('<div class="simpleCart_items"></div>');
        updateCartItems();
        break;

      case "order-details-page":

        var $table = $("table.order_details tbody").empty();
        $("span.order-id").text(_data.id);
        $("span.order-title").text(_data.type === "order" ? "de la Orden" : "del Pedido");

        var cancel_order = function(event){
          $.ajax({
            url: server + "order/" + _data.id + "/cancel",
            type: "PUT",
            dataType: "json"
          }).done(function(response){
            $.mobile.back();
          });
        };

        var approve_order = function(event){
          $.ajax({
            url: server + "order/" + _data.id + "/approve",
            type: "PUT",
            dataType: "json"
          }).done(function(response){
            $.mobile.back();
          });
        };

        $(document).off("click", "a.ui-btn-cancel-order, a.ui-btn-approve-order")
                   .on("click", "a.ui-btn-cancel-order", cancel_order)
                   .on("click", "a.ui-btn-approve-order", approve_order);
        if(_data.sta != 'PENDIENTE')
          $("a.btoEditar").addClass("ui-state-disabled");
        else
          $("a.btoEditar").removeClass("ui-state-disabled");
        $.ajax({
          url: server + "request/" + _data.id,
          type: "GET",
          dataType: "json",
          data:{
            "idempresa" : _tmp.clientEmpr
          },
        }).done(function(response){          
          $("span.order-id").text(_data.id);          
          $("span.order-date").text(response.pedid_fecha);
          $("span.order-client-name").text(response.CLIEV_RAZONSOC);
          $("span.order-seller-name").text(response.VENDV_NOMBRE);
          var npCant = 0,npSub = 0,npDesc = 0,npIva = 0,npTotal = 0;
          $.each(response.products, function(i){  
            cajas = parseFloat(this.pregn_cajas);
            price = this.pregn_precio*cajas;
            xSub = price;
            xDes = xSub*(this.pregn_descuento1/100);
            xSub = xSub-xDes;
            xIva = xSub*(this.pregn_impuesto/100);
            xTot = xSub +  xIva;
            npCant += cajas;
            npSub += xSub;
            npDesc += xDes;
            npIva += xIva;
            npTotal += xTot;
            $table.append("<tr>"+
              "<td>" + this.pregv_idproducto + "</td>"+
              "<td>" + this.ARTV_DESCART + "</td>"+
              "<td>" + cajas + "</td>"+
              "<td>" + this.pregn_descuento1 + "</td>"+
              "<td>" + this.pregn_precio + "</td>"+
            "</tr>")
          })          
          $("span.order-items").text(npCant.formatMoney(2, '.', ','));
          $("span.simpleCart_desc").text(npDesc.formatMoney(2, '.', ','));
          $("span.order-subtotal").text("Bs " + npSub.formatMoney(2, '.', ','));
          $("span.order-iva").text("Bs " + npIva.formatMoney(2, '.', ','));
          $("span.order-total").text("Bs " + npTotal.formatMoney(2, '.', ','));
        });
        break;

      case "client-main-page":                
        $("div.simpleCart_items").remove();
        $page.find("div.cart-items").empty().append('<div class="simpleCart_items"></div>');
        updateCartItems();
        break;

      case "client-orders-page":                      
        $page.find("ul[data-role='listview']").each(function(){
          $(this).empty();
        });

        var cancel_order = function(event){
          $li = $(this).closest("li");

          $.ajax({
            url: server + "order/" + _data.id + "/cancel",
            type: "PUT",
            dataType: "json"
          }).done(function(response){
            $li.remove();
          });
        };

        $(document).off("click", "a.ui-btn-cancel-order")
                   .on("click", "a.ui-btn-cancel-order", cancel_order);

        $page.find("ul[data-type='PENDIENTE'], ul[data-type='RECHAZADO'], ul[data-type='DESCARGADO'], ul[data-type='PROCESADO']").empty();        
        $.ajax({          
          url: server + "order/client",
          type: "GET",
          dataType: "json"
        }).done(function(response){
          var element = "";          
          $.each(response, function(i){
            element = '<li><a class="capture-metadata" data-sesion="' + this.sesion + '" data-id="' + this.ID + '" data-rel="' + this.CODIEMPR + '" data-sta="' + this.STATUS + '" data-type="order" href="#order-details-page">'+
                      '<h2>' + this.ID + ': ' + this.FECHA_GEN + '</h2>'+
                      '<p>' + this.CODIEMPR + '</p></a>'+
                      (this.STATUS == "0" ? '<a class="ui-btn-cancel-order capture-metadata" data-id="' + this.ID + '" href="#">Anular</a>' : "")+
                      '</li>';
            $page.find("ul[data-type='" + this.STATUS + "']").append(element);
          });

          $.ajax({
            url: server + "request/client",
            type: "GET",
            dataType: "json"
          }).done(function(response){
            var element = "";

            $.each(response, function(i){
              var nTotal = redondeo(parseFloat(this.base)+parseFloat(this.iva),2);
              element = '<li><a class="capture-metadata"  data-sesion="' + this.sesion + '"  data-id="' + this.pediv_numero + '" data-rel="' + this.VENDV_NOMBRE + '" data-sta="' + this.pediv_status + '" data-type="request" href="#order-details-page">'+
                        '<h2>' + this.pediv_numero + ' / ' + this.pedid_fecha + ' / ' + this.cajas + ' / ' + this.base + ' / ' + this.iva + ' / ' + nTotal + '</h2>'+
                        '<p>' + this.VENDV_NOMBRE + ' [' + this.pediv_codivend + ']</p></a>'+
                        '</li>';
              $page.find("ul[data-type='"+this.pediv_status+"']").append(element);
            });

            $page.find("ul[data-role='listview']").each(function(){
              $(this).listview('refresh');
            });
          });
        });
        break;

      case "seller-orders-page":        
        $page.find("ul[data-role='listview']").each(function(){
          $(this).empty();
        });              
        $page.find("ul[data-type='PENDIENTE'], ul[data-type='RECHAZADO'], ul[data-type='DESCARGADO'], ul[data-type='PROCESADO']").empty();

        $.ajax({
            url: server + "request",
            type: "GET",
            dataType: "json",
            data:{
              "idempresa" : _tmp.clientEmpr,
              "tipo" : "P"
            },
          }).done(function(response){             
            var element = "";
            $.each(response, function(i){
              var nTotal = redondeo(parseFloat(this.base)+parseFloat(this.iva),2);
              element = '<li><a class="capture-metadata"  data-sesion="' + this.sesion + '" data-id="' + this.pediv_numero + '" data-rel="' + this.VENDV_NOMBRE + '" data-sta="' + this.pediv_status + '" data-type="request" href="#order-details-page">'+
                        '<h2>' + this.pediv_numero + ' / ' + this.pedid_fecha + ' / Cliente: ' +this.CLIEV_RIF + ' - ' + this.CLIEV_NOMBRE +' / ' + this.cajas + ' / ' + this.base + ' / ' + this.iva + ' / ' + nTotal + '</h2>'+
                        //'<h2>' + this.pediv_numero + ' / ' + this.pedid_fecha + ' / Cliente: ' +this.CLIEV_RIF + ' - ' + this.CLIEV_RAZONSOC + '</h2>'+
                        '</a></li>';
              $page.find("ul[data-type='"+this.pediv_status+"']").append(element);
            });

            $page.find("ul[data-role='listview']").each(function(){
              $(this).listview('refresh');
            });
          });
        break;

      case "seller-client-orders-page":        
        $page.find("ul[data-role='listview']").each(function(){
          $(this).empty();
        });              
        $page.find("ul[data-type='PENDIENTE'], ul[data-type='RECHAZADO'], ul[data-type='DESCARGADO'], ul[data-type='PROCESADO']").empty();

        $.ajax({
            url: server + "request",
            type: "GET",
            dataType: "json",
            data:{
              "idempresa" : _tmp.clientEmpr,
              "tipo" : "C"
            },
          }).done(function(response){   
            var element = "";
            $.each(response, function(i){              
              var nTotal = redondeo(parseFloat(this.base)+parseFloat(this.iva),2);
              element = '<li><a class="capture-metadata"  data-sesion="' + this.sesion + '" data-id="' + this.pediv_numero + '" data-rel="' + this.VENDV_NOMBRE + '" data-sta="' + this.pediv_status + '" data-type="request" href="#order-details-page">'+
                        '<h2>' + this.pediv_numero + ' / ' + this.pedid_fecha + ' / Cliente: ' +this.CLIEV_RIF + ' - ' + this.CLIEV_NOMBRE +' / ' + this.cajas + ' / ' + this.base + ' / ' + this.iva + ' / ' + nTotal + '</h2>'+
                        //'<h2>' + this.pediv_numero + ' / ' + this.pedid_fecha + ' / Cliente: ' +this.CLIEV_RIF + ' - ' + this.CLIEV_RAZONSOC + '</h2>'+
                        '</a></li>';
              $page.find("ul[data-type='"+this.pediv_status+"']").append(element);
            });

            $page.find("ul[data-role='listview']").each(function(){
              $(this).listview('refresh');
            });
          });
        break;

      case "client-list-page":
        simpleCart.empty();
        $.ajax({
          url: server + "client",
          type: "GET",
          dataType: "json"
        }).done(function(response){
          var $list = $page.find("ul[data-role='listview']").empty();
          $.each(response, function(i){
            $list.append("<li class='capture-metadata' data-icon='false' data-filtertext='" + this.CLIEV_NOMBRE + " " + this.CLIEV_RIF + " " + this.CLIEV_IDCLIENTE +"' data-sesion='" + this.sesion + "' data-id='" + this.CLIEV_IDCLIENTE + "' data-company='" + this.CLIEV_CODIEMPR + "' data-rif='" + this.CLIEV_RIF + "' data-name='" + this.CLIEV_RAZONSOC + "' data-vend='"+this.CRVV_IDVENDEDOR+"' data-prec='"+this.PLANV_TIPOPRECIO+"' data-tipo='"+this.CLIEV_IDTIPO+"'><a href='#new-request-page'><h2>" + this.CLIEV_IDCLIENTE + ": " + this.CLIEV_NOMBRE + "</h2><p>" + this.CLIEV_RIF + "/" + this.CLIEV_TELEFONO2 + " - "  + this.CLIEV_MOVIL + "</p></li>");
          }); $list.listview("refresh");
        });
        break;

      case "new-request-page":        
        if(_tmp.numePED === undefined || _tmp.numePED === 0){
          _tmp.clientID = _data.id;
          _tmp.clientEmpr = _data.company;
          _tmp.sellerID = _data.vend;
          _tmp.tipoPRE = _data.prec;
          _tmp.tipoCLIE = _data.tipo;
          _tmp.numePED = 0;
          _tmp.tipo = _data.sesion;
        }else{
          $("a.bto_volver").addClass("ui-state-disabled");
        }
        _tmp.pagREG = 20;
        _tmp.pagNUM = 1;  
        _tmp.pagTOT = 0;  
        _tmp.pagLOT = 0;
            console.log(_tmp);
        $.ajax({
          url: server + "product/",
          type: "GET",
          dataType: "json",
          data: {
            "tipoprec" : _tmp.tipoPRE,
            "idcliente" : _tmp.clientID,
            "idtipo" : _tmp.tipoCLIE,
            "company" : _tmp.clientEmpr,
          },
        }).done(function(response){    
          $("a.bto_primera").addClass("ui-state-disabled");
          $("a.bto_anterior").addClass("ui-state-disabled");
          _tmp.pagTOT = Math.ceil(response.id/_tmp.pagREG);  
          if(_tmp.pagTOT <= 1){
            $("a.bto_siguiente").addClass("ui-state-disabled");
            $("a.bto_final").addClass("ui-state-disabled");
          }else{
            $("a.bto_siguiente").removeClass("ui-state-disabled");
            $("a.bto_final").removeClass("ui-state-disabled");
          }
          loadProductList("");  
        });
        break;

      case "new-account-page":
        $page.find(".ui-field-contain.ui-option-b").hide();

        var user_type = function(event){
          //$page.find(".ui-field-contain.ui-option-a").css('display', $(this).val() == 1 ? 'inherit' : 'none');
          $page.find(".ui-field-contain.ui-option-b").css('display', $(this).val() == 2 ? 'inherit' : 'none');
          $page.find("input[name='client']").val("");
          $.ajax({
            url: server + "seller",
            type: "GET",
            dataType: "json"
          }).done(function(response){
            var $select = $page.find(".ui-field-contain.ui-option-a select[name='seller']").empty();
            $.each(response, function(i){
              $select.append("<option data-company='" + this.VENDV_CODIEMPR + "' value='" + this.VENDV_IDVENDEDOR + "'>" + this.VENDV_NOMBRE + "</option>");
            });
            $select.selectmenu('refresh', true);
          });
        };

        var select_client = function(event){
          $("#new-account-client-autocomplete").val($.trim(_data.name) + ":" + $.trim(_data.id) + ":" + $.trim(_data.company));
          $(this).siblings().addBack().addClass("ui-screen-hidden");
          $.ajax({
            url: server + "seller",
            type: "GET",
            dataType: "json",
            data:{
              "cliente" : _data.id
            },
          }).done(function(response){
            var $select = $page.find(".ui-field-contain.ui-option-a select[name='seller']").empty();
            $.each(response, function(i){
              $select.append("<option data-company='" + this.VENDV_CODIEMPR + "' value='" + this.VENDV_IDVENDEDOR + "'>" + this.VENDV_NOMBRE + "</option>");
            });
            $select.selectmenu('refresh', true);
          });
        }

        var filter_company = function(event){
          var companyID = $(this).val();
          $(document).find("[data-company]").show('fast').filter(function() {
            return $(this).data("company") === companyID;
          }).hide('fast');
        }

        $(document).off("change", "select[name='type'], li.select-client, select[name='company']")
                   .on("change", "select[name='type']", user_type)
                   .on("change", "select[name='company']", filter_company)
                   .on("click", "li.select-client", select_client);

        $.ajax({
          url: server + "client",
          type: "GET",
          dataType: "json"
        }).done(function(response){
          var $list = $page.find(".ui-field-contain.ui-option-b").children("ul[data-role='listview']").empty();
          $.each(response, function(i){            
            $list.append("<li class='capture-metadata  select-client' data-sesion='" + this.sesion + "' data-filtertext='" + this.CLIEV_NOMBRE + " " + this.CLIEV_RIF + "' data-id='" + this.CLIEV_IDCLIENTE + "' data-rif='" + this.CLIEV_RIF + "' data-name='" + this.CLIEV_RAZONSOC + "' data-company='" + this.CLIEV_CODIEMPR + "'><a href='#'>" + this.CLIEV_NOMBRE + "</a></li>");
          });
          $list.listview("refresh");
        });

        $.ajax({
          url: server + "seller",
          type: "GET",
          dataType: "json"
        }).done(function(response){
          var $select = $page.find(".ui-field-contain.ui-option-a select[name='seller']").empty();
          $.each(response, function(i){
            $select.append("<option data-company='" + this.VENDV_CODIEMPR + "' value='" + this.VENDV_IDVENDEDOR + "'>" + this.VENDV_NOMBRE + "</option>");
          });
          $select.selectmenu('refresh', true);
        });

        $.ajax({
          url: server + "company",
          type: "GET",
          dataType: "json"
        }).done(function(response){
          var $select = $page.find(".ui-field-contain select[name='company']").empty();
          $.each(response, function(i){
            $select.append("<option value='" + this.codiempr + "'>" + this.nombempr + "</option>");
          });
          $select.selectmenu('refresh', true);
        });
        break;

      case "update-account-page":
        var delete_user = function(event){
          $li = $(this).closest("li");

          $.ajax({
            url: server + "account/" + $li.data("id"),
            type: "DELETE",
            dataType: "json"
          }).done(function(response){
            $li.remove();
          });
        }

        var set_user = function(event){
          var userID = $(this).closest("li").data("id");
          $("#update-account-form").attr("action", "account/" + userID);

          $.ajax({
            url: server + "account/" + userID,
            type: "GET",
            dataType: "json"
          }).done(function(response){
            $("#update-account-popup")
              .find("input[name='username']").val(response.CUENV_USUARIO)
              .find("input[name='password']").val(response.CUENV_PASSWORD);
          });
        }

        $(document).off("click", "a[data-icon='delete']")
                   .on("click", "a[data-icon='delete']", delete_user)
                   .off("click", "a[data-rel='popup']")
                   .on("click", "a[data-rel='popup']", set_user);

        $.ajax({
          url: server + "account",
          type: "GET",
          dataType: "json"
        }).done(function(response){
          var $list = $page.find("ul[data-role='listview']").empty();
          $.each(response, function(i){
            $list.append("<li data-id='" + this.CUENV_IDCUENTA + "' data-username='" + this.CUENV_USUARIO + "' data-type='" + this.CUENV_TIPO + "'><a data-rel='popup' data-position-to='window' href='#update-account-popup'><h2>" + this.CUENV_USUARIO + "</h2><p>" + getLevel(this.CUENV_TIPO) + "</p></a><a data-icon='delete' href='#'>Eliminar</a></li>");
          });
          $list.listview("refresh");
        });
        break;
    };
  });

  $("form").submit(function(e){
    e.preventDefault();
    var $form = $(this);
    switch($form.attr('id')){
      case "login-form":
        sUsuario = $form.find("input[name='username']").val();
        sPassword = $form.find("input[name='password']").val();
        if(sUsuario == ""){
          alert("Indique el usuario");
          return;
        }
        if(sPassword == ""){
          alert("Indique el password");
          return;
        } 
        break;
      case "new-account-form":
        sUsuario = $form.find("input[name='username']").val();
        sPassword = $form.find("input[name='password']").val();            
        nTipo = $form.find("select[name='type']").val();
        sVendedor = $form.find("input[name='seller']").val();
        sCliente = $form.find("input[name='client']").val();            
        if(sUsuario == ""){
          alert("Indique el usuario");
          return;
        }
        if(sPassword == ""){
          alert("Indique el password");
          return;
        }    
        if(nTipo == 1){
          if(sVendedor == ""){
            alert("Indique el vendedor");
            return;
          }
        }else{
          if(sCliente == ""){
            alert("Indique el cliente");
            return;
          }
        }
      case "update-account-form":
        sUsuario = $form.find("input[name='username']").val();
        sPassword = $form.find("input[name='password']").val();
        if(sUsuario == ""){
          alert("Indique el usuario");
          return;
        }
        if(sPassword == ""){
          alert("Indique el password");
          return;
        }
        break;
    }
    $.ajax({
      url: server + $form.attr("action"),
      type: $form.attr("type") || "POST",
      dataType: "json",
      data: $form.serialize()
    }).done(function(e){
      if(!e.error){
        switch($form.attr('id')){
          case "login-form":
            location.reload();
            break;

          case "new-account-form":
            alert("Cuenta Creada!")
            $form.find(".ui-field-contain.ui-option-a").show();
            $form.find(".ui-field-contain.ui-option-b").hide();
            $form[0].reset();
            break;

          case "update-account-form":
            alert("Usuario Actualizado!")
            $(document).find("ul[data-role='listview'] li[data-id='" + e.id + "'] a h2").text(e.username);
            $("#update-account-popup").popup("close");
            break;

          //En caso de que el ID del formulario no este registrado
          default:
            alert("Error al procesar formulario");
            break;
        }
      } else {
        alert(e.error);
        //alert(e.message);
      }
    });
  });
   
});
