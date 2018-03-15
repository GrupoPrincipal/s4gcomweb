simpleCart({
    cartColumns: [
        { attr: "name", label: "Nombre"},        
        { view: "decrement", label: false},
        { attr: "cajas", label: "Cant"},
        { view: "increment", label: false},        
        { view: "currency", attr: "price", label: "Precio"},
        { view: "udecrement", label: false},
        { attr: "quantity", label: "Unidades"},
        { view: "uincrement", label: false},
        { attr: "des", label: "Desc." },
        { view: "currency", attr: "total", label: "SubTotal" },
        { view: "remove", text: "Quitar", label: false}
    ],
    cartStyle: "table",
    checkout: {
        type: "SendForm",
        url: "./checkout",
        method: "POST"
    },
    currency: "VEF",
    data: {},
    language: "english-us",
    excludeFromCheckout: [],
    shippingCustom: null,
    shippingFlatRate: 0,
    shippingQuantityRate: 0,
    shippingTotalRate: 0,
    taxRate: 0,
    taxShipping: false,
    beforeAdd: function(item){                
        item.set("code", _data.id);
        item.set("codiempr", _data.empr);
    },
    afterAdd: function(event){ 
        $("a.item_add[data-id='" + _data.id + "']").removeClass("ui-show").addClass("ui-hide");
        $("a.item_remove[data-id='" + _data.id + "']").removeClass("ui-hide").addClass("ui-show"); 
        $oCard = $(".simpleCart_shelfItem[data-id='" + _data.id + "']");        
        var nUxc = $oCard.data("uxc");
        var nPre = $oCard.data("pre");
        var nIva = $oCard.data("iva");
        var nDes = $oCard.data("des");
        var cRel = $oCard.data("rel");
        var nPor = $oCard.data("por");
        var nEsc = $oCard.data("esc");
        var nMin = $oCard.data("min");
        $controls = $(".simpleCart_shelfItem[data-id='" + _data.id + "']").find(".item_cjs_btn");
        $unidades = $(".simpleCart_shelfItem[data-id='" + _data.id + "']").find(".item_qnt_btn");
        if(_tmp.pediEDT == true){
            $controls
            .find("a.ui-btn")
            .removeClass("ui-state-disabled")
            .end()
            .find("input")
            .textinput("enable");

            $unidades
            .find("a.ui-btn")
            .removeClass("ui-state-disabled")
            .end()
            .find("input")
            .textinput("enable");

            updateCartItems();
            return;
        }                            
        $controls
            .find("a.ui-btn")
            .removeClass("ui-state-disabled")
            .end()
            .find("input")
            .val("1")
            .textinput("enable");
        var cItem = "undefined";
        simpleCart.each(function(item, x){
        if(item.get("code") == _data.id){
            cItem = item;
            return false;
        }
        });
        cItem.quantity(nUxc);
        cItem.uxc(nUxc);
        cItem.cajas(1);
        cItem.price(nPre);
        cItem.iva(nIva);
        cItem.des(nDes);
        cItem.rel(cRel);
        cItem.por(nPor);
        cItem.esc(nEsc);
        cItem.umin(nMin);
        $unidades
            .find("a.ui-btn")
            .removeClass("ui-state-disabled")
            .end()
            .find("input")
            .textinput("enable");
        updateCartItems();
    },
    update: function(event){         
        //ValidarPromocion();            
        Recalcular();        
    },
    load           : null,
    ready          : null,
    beforeSave     : null,
    afterSave      : null,
    checkoutSuccess: null,
    checkoutFail   : null,
    beforeCheckout : null,
    beforeRemove   : function(item){
        oArticulo = item.get("code");
        $("a.item_add[data-id='" + oArticulo + "']").removeClass("ui-hide").addClass("ui-show");
        $("a.item_remove[data-id='" + oArticulo + "']").removeClass("ui-show").addClass("ui-hide");
        $controls = $(".simpleCart_shelfItem[data-id='" + oArticulo + "']").find(".item_qnt_btn");
        $controls
            .find("a.ui-btn")
            .addClass("ui-state-disabled")
            .end()
            .find("input")
            .val("0")
            .textinput("disable");
        $controls = $(".simpleCart_shelfItem[data-id='" + oArticulo + "']").find(".item_cjs_btn");
        $controls
            .find("a.ui-btn")
            .addClass("ui-state-disabled")
            .end()
            .find("input")
            .val("0")
            .textinput("disable");       
    }
});
simpleCart.bind('completeCheckout' , function(id,monto,cajas,type){
    monto = redondeo(monto,2);
    switch(type){
      case "client":
        if(_tmp.numePED == 0){
            alert("Se ha generado su pedido N° "+id+" por un monto de "+monto+" BsF, totalizando "+cajas+" cajas");
        }
        else{
            alert("Se ha editado su pedido N° "+id+" por un monto de "+monto+" BsF, totalizando "+cajas+" cajas");
        }
        _tmp.numePED = 0;        
        break;
      case "seller":
         if(_tmp.numePED == 0){
            alert("Se ha registrado el pedido N° "+id+" por un monto de "+monto+" BsF, totalizando "+cajas+" cajas");
        }
        else{
            alert("Se ha editado el pedido N° "+id+" por un monto de "+monto+" BsF, totalizando "+cajas+" cajas");
            $("a.bto_volver").removeClass("ui-state-disabled");
        }
        _tmp.numePED = 0;                
        break;
    }
    simpleCart.empty();
    loadProductList(type);
});


$(document).on("click", "a.item_remove", function(){     
    var oArticulo = $(this).attr("data-id");    
    simpleCart.each(function(item, x){
        if(item.get("code") == oArticulo){
            $("a.item_add[data-id='" + oArticulo + "']").removeClass("ui-hide").addClass("ui-show");
            $("a.item_remove[data-id='" + oArticulo + "']").removeClass("ui-show").addClass("ui-hide");
            $controls = $(".simpleCart_shelfItem[data-id='" + oArticulo + "']").find(".item_qnt_btn");
            $controls
                .find("a.ui-btn")
                .addClass("ui-state-disabled")
                .end()
                .find("input")
                .val("0")
                .textinput("disable");
            $controls = $(".simpleCart_shelfItem[data-id='" + oArticulo + "']").find(".item_cjs_btn");
            $controls
                .find("a.ui-btn")
                .addClass("ui-state-disabled")
                .end()
                .find("input")
                .val("0")
                .textinput("disable");
            item[oArticulo] = null;
            item.remove();            
        }
    });
    updateCartItems();
}).on("change", "div.item_qnt_btn input", function(){
    var cItem = "undefined";
    scsID = $(this).closest(".simpleCart_shelfItem").data("id");
    nUxc = $(this).closest(".simpleCart_shelfItem").data("uxc");
    nMin = $(this).closest(".simpleCart_shelfItem").data("min");
    simpleCart.each(function(item, x){
        if(item.get("code") == scsID){
            cItem = item;
            return false;
        }
    });
    var nUnidades = UnidadMinima(nMin,$(this).val(),true);
    cItem.quantity(nUnidades);
    cItem.uxc(nUxc);
    cItem.cajas(redondeo(cItem.quantity()/nUxc,2));
    $cajas = $(".simpleCart_shelfItem[data-id='" + scsID + "']").find(".item_cjs_btn");
    $cajas
        .find("input")
        .val(parseFloat(cItem.quantity()/nUxc).toFixed(2));    
    updateCartItems();
}).on("click", "div.item_qnt_btn.plus a.ui-btn", function(){
    var cItem = "undefined";
    scsID = $(this).closest(".simpleCart_shelfItem").data("id");    
    nUxc = $(this).closest(".simpleCart_shelfItem").data("uxc");
    nMin = $(this).closest(".simpleCart_shelfItem").data("min");
    simpleCart.each(function(item, x){
        if(item.get("code") == scsID){
            cItem = item;
            return false;
        }
    });
    $unidades = $(this).closest(".item_qnt_controls");
    nUnd = parseFloat($unidades.find("input").val())+1;
    var nUnidades = UnidadMinima(nMin,nUnd);
    //cItem.quantity(cItem.quantity() + 1);
    cItem.quantity(nUnidades);
    cItem.uxc(nUxc);
    cItem.cajas(redondeo(cItem.quantity()/nUxc,2));
    $unidades = $(this).closest(".item_qnt_controls");
    $unidades.find("input").val(cItem.quantity());    
    updateCartItems();
}).on("click", "div.item_qnt_btn.minus a.ui-btn", function(){
    var cItem = "undefined";
    scsID = $(this).closest(".simpleCart_shelfItem").data("id");
    nUxc = $(this).closest(".simpleCart_shelfItem").data("uxc");
    nMin = $(this).closest(".simpleCart_shelfItem").data("min");
    simpleCart.each(function(item, x){
        if(item.get("code") == scsID){
            cItem = item;
            return false;
        }
    });
    $unidades = $(this).closest(".item_qnt_controls");
    nUnd = parseFloat($unidades.find("input").val()-1);
    if(nUnd > 0){
        var nUnidades = UnidadMinima(nMin*-1,nUnd);
        cItem.quantity(nUnidades);
        //cItem.quantity(cItem.quantity() - 1);
    }
    cItem.cajas(redondeo(cItem.quantity()/nUxc,2));
    cItem.uxc(nUxc);    
   
    $unidades.find("input").val(cItem.quantity());
    $cajas = $(".simpleCart_shelfItem[data-id='" + scsID + "']").find(".item_cjs_btn");
    $cajas
        .find("input")
        .val(cItem.cajas());
    updateCartItems();
}).on("change", "div.item_cjs_btn input", function(){
    var cItem = "undefined";
    scsID = $(this).closest(".simpleCart_shelfItem").data("id");
    nUxc = $(this).closest(".simpleCart_shelfItem").data("uxc");
    nMin = $(this).closest(".simpleCart_shelfItem").data("min");
    simpleCart.each(function(item, x){
        if(item.get("code") == scsID){
            cItem = item;
            return false;
        }
    });
    $cajas = $(this).closest(".item_cjs_controls");
    nCjs = parseFloat($cajas.find("input").val())
    var xUnidades = Math.ceil($(this).val()*nUxc);
    var nUnidades = UnidadMinima(nMin,xUnidades,true);    
    cItem.cajas(redondeo(nUnidades/nUxc,2));    
    cItem.uxc(nUxc);
    cItem.quantity(nUnidades);    
    $unidades = $(this).closest(".item_qnt_controls");
    $unidades.find("input").val(cItem.quantity());
    updateCartItems();
}).on("click", "div.item_cjs_btn.plus a.ui-btn", function(){
    var cItem = "undefined";
    scsID = $(this).closest(".simpleCart_shelfItem").data("id");    
    nUxc = $(this).closest(".simpleCart_shelfItem").data("uxc");
    nMin = $(this).closest(".simpleCart_shelfItem").data("min");
    simpleCart.each(function(item, x){
        if(item.get("code") == scsID){
            cItem = item;
            return false;
        }
    });    
    $cajas = $(this).closest(".item_cjs_controls");
    nCjs = parseFloat($cajas.find("input").val()) + 1;    
    
    var xUnidades = Math.ceil(nCjs*cItem.uxc());
    var nUnidades = UnidadMinima(nMin,xUnidades,true);    
    cItem.cajas(redondeo(nUnidades/nUxc,2));
    cItem.quantity(nUnidades);
    cItem.uxc(nUxc);    
    $unidades = $(this).closest(".item_qnt_controls");
    $unidades.find("input").val(cItem.quantity());
    updateCartItems();
}).on("click", "div.item_cjs_btn.minus a.ui-btn", function(){
    var cItem = "undefined";
    scsID = $(this).closest(".simpleCart_shelfItem").data("id");
    nUxc = $(this).closest(".simpleCart_shelfItem").data("uxc");
    nMin = $(this).closest(".simpleCart_shelfItem").data("min");
    simpleCart.each(function(item, x){
        if(item.get("code") == scsID){
            cItem = item;
            return false;
        }
    });
    $cajas = $(this).closest(".item_cjs_controls");
    nCjs = parseFloat($cajas.find("input").val()) - 1;
    if(nCjs > 0){
        var xUnidades = Math.ceil(nCjs*cItem.uxc());
        var nUnidades = UnidadMinima(nMin*-1,xUnidades,true);    
        cItem.cajas(redondeo(nUnidades/nUxc,2));
        cItem.quantity(nUnidades);        
        cItem.uxc(nUxc);
        $unidades = $(this).closest(".item_qnt_controls");
        $unidades.find("input").val(cItem.quantity());
        updateCartItems();
    }
}).on("change", "div.group-control ul li select, #searchField", function(e){
    var GroupFilterA = $('#group-aa'),
        GroupFilterB = $('#group-ab'),
        GroupFilterC = $('#group-ac'),
        SearchField = $('#searchField');
    i = 1;
    var cGrupo = $('#group-aa').find(':selected').attr('data-grupo');
    var cSubGrupo = $('#group-ab').find(':selected').attr('data-subgrupo');        
    
    $("#group-ab option").each(function(){     
      var opt = $('#group-ab option:eq('+i+')');      
      var xGrupo = ""+opt.data("grupo");
      if(GroupFilterA.val() === "all")
        opt.show()
      else if(xGrupo === cGrupo)
          opt.show()
      else
          opt.hide()
      i++;     
    });
    i = 1;
    $("#group-ac option").each(function(){     
      var opt = $('#group-ac option:eq('+i+')');     
      var xGrupo = ""+opt.data("grupo");
      var xSubgr = ""+opt.data("subgrupo")
      if(GroupFilterB.val() === "all")
        opt.show()
      else if(xGrupo === cGrupo && xSubgr === cSubGrupo)
          opt.show()      
      else 
          opt.hide()
      i++;     
    });    
    $.ajax({
        url: server + "product/" + (typeof _pageType !== "undefined" ? _pageType : ""),
        type: "GET",
        dataType: "json",
        data: {
            "tipoprec" : _tmp.tipoPRE,
            "idcliente" : _tmp.clientID,
            "idtipo" : _tmp.tipoCLIE,
            "company" : _tmp.clientEmpr,
            "gr" : GroupFilterA.val(),
            "sg" : GroupFilterB.val(),
            "li" : GroupFilterC.val(),
            "pr" : SearchField.val()
        },
    }).done(function(response){    
        $("a.bto_primera").addClass("ui-state-disabled");
        $("a.bto_anterior").addClass("ui-state-disabled");
        _tmp.pagTOT = Math.ceil(response.id/_tmp.pagREG);
        _tmp.pagNUM = 1;
        _tmp.pagLOT = 0;
        if(_tmp.pagTOT <= 1){
            $("a.bto_siguiente").addClass("ui-state-disabled");
            $("a.bto_final").addClass("ui-state-disabled");
        }else{
            $("a.bto_siguiente").removeClass("ui-state-disabled");
            $("a.bto_final").removeClass("ui-state-disabled");
        }        
        loadProductList(typeof _pageType !== "undefined" ? _pageType : "");        
    });
}).on("click", "a.bto_primera", function(){
    _tmp.pagNUM = 1;
    _tmp.pagLOT = 0;
    $("a.bto_primera").addClass("ui-state-disabled");
    $("a.bto_anterior").addClass("ui-state-disabled");
    $("a.bto_siguiente").removeClass("ui-state-disabled");
    $("a.bto_final").removeClass("ui-state-disabled");
    loadProductList(typeof _pageType !== "undefined" ? _pageType : "");
}).on("click", "a.bto_anterior", function(){
    _tmp.pagNUM--;
    _tmp.pagLOT -= _tmp.pagREG;
    $("a.bto_siguiente").removeClass("ui-state-disabled");
    $("a.bto_final").removeClass("ui-state-disabled");
    if(_tmp.pagNUM == 1){
        $("a.bto_primera").addClass("ui-state-disabled");
        $("a.bto_anterior").addClass("ui-state-disabled");        
    }
    loadProductList(typeof _pageType !== "undefined" ? _pageType : "");
}).on("click", "a.bto_siguiente", function(){
    _tmp.pagNUM++;
    _tmp.pagLOT += _tmp.pagREG;
    $("a.bto_primera").removeClass("ui-state-disabled");
    $("a.bto_anterior").removeClass("ui-state-disabled");
    if(_tmp.pagNUM == _tmp.pagTOT){
        
        $("a.bto_siguiente").addClass("ui-state-disabled");
        $("a.bto_final").addClass("ui-state-disabled");
    }
    loadProductList(typeof _pageType !== "undefined" ? _pageType : "");
}).on("click", "a.bto_final", function(){
    _tmp.pagNUM = _tmp.pagTOT;
    _tmp.pagLOT = ((_tmp.pagTOT-1)*_tmp.pagREG);    
    $("a.bto_primera").removeClass("ui-state-disabled");
    $("a.bto_anterior").removeClass("ui-state-disabled");
    $("a.bto_siguiente").addClass("ui-state-disabled");
    $("a.bto_final").addClass("ui-state-disabled");
    loadProductList(typeof _pageType !== "undefined" ? _pageType : "");
}).on("click", "a.simpleCart_increment, a.simpleCart_decrement", function(){
    updateCartItems();
}).on("click", "a.btoEditar", function(){
    _tmp.numePED = _data.id;
    _tmp.pagLOT = 0;
    _tmp.pagREG = 20;
    _tmp.pagNUM = 1;
    _tmp.pediEDT = true;
    simpleCart.empty();
    $.ajax({
      url: server + "request/" + _tmp.numePED,
      type: "GET",
      dataType: "json",
      data:{
        "idempresa" : _tmp.clientEmpr
      },
    }).done(function(response){     
        _tmp.clientID = response.pediv_idcliente;
        _tmp.tipoPRE = response.PLANV_TIPOPRECIO;
        _tmp.tipoCLIE = response.CLIEV_IDTIPO;
        _tmp.sellerID = response.pediv_codivend;
        $.each(response.products, function(i){    
            _data.id = this.ARTV_IDARTICULO;
            xRel = (this.pregv_promo == null ? "" : this.pregv_promo);
            simpleCart.add({ 
                code: this.ARTV_IDARTICULO,
                name: this.ARTV_DESCART ,
                price: this.pregn_precio ,
                size: "Small" ,                
                quantity: this.pregn_unidades,
                cajas: this.pregn_cajas,
                uxc: this.ARTN_UNIXCAJA,
                iva: this.pregn_impuesto,
                des: this.pregn_descuento1,
                por: this.pregn_descuento1,
                rel: xRel
            });        
        });

        $.ajax({
            url: server + "product/" + (typeof _pageType !== "undefined" ? _pageType : ""),
            type: "GET",
            dataType: "json",
            data: {
                "tipoprec" : _tmp.tipoPRE,
                "idcliente" : _tmp.clientID,
                "idtipo" : _tmp.tipoCLIE,
                "company" : _tmp.clientEmpr
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
            loadProductList(typeof _pageType !== "undefined" ? _pageType : "");
            _tmp.pediEDT = false;
        });     
    });    
}).on("DOMSubtreeModified", "#cart-panel", function(){    
    simpleCart.each(function(item, x){
        $("#cart-panel").find(".item-name").each(function(index, value){
            $("#item_qnt_" + item.get("code")).val(item.quantity());
            $("#item_cjs_" + item.get("code")).val(parseFloat(item.cajas()).toFixed(2));
            if(item.get("name") == $(this).text()) {
                $(this).parent("tr").find(".simpleCart_decrement").css('display', item.cajas() > 1 ? 'inherit' : 'none');
            }
        });
    });
});
var promoList = [];
var loadProductList = function(type){   
    Paginar();
    var CardsBlockA = $('#CardsBlockA'),
        CardsBlockB = $('#CardsBlockB'),
        CardsBlockC = $('#CardsBlockC'),
        CardsBlockD = $('#CardsBlockD'),
        GroupFilterA = $('#group-aa'),
        GroupFilterB = $('#group-ab'),
        GroupFilterC = $('#group-ac'),
        SearchField = $('#searchField'),
        i, j = 1, tag;    
    $("div.simpleCart_items").remove();
    $("div.ui-grid-solo").find("div.cart-items").empty().append('<div class="simpleCart_items"></div>');        
    $.ajax({
        url: server + "product/" + type,
        type: "GET",
        dataType: "json",
        data: {
          "tipoprec" : _tmp.tipoPRE, 
          "idcliente" : _tmp.clientID,
          "idtipo" : _tmp.tipoCLIE,   
          "company" : _tmp.clientEmpr,      
          "gr" : GroupFilterA.val(),
          "sg" : GroupFilterB.val(),
          "li" : GroupFilterC.val(),
          "pr" : SearchField.val(),
          "in" : _tmp.pagLOT
        },
    }).done(function(response){  
        if(response.id != undefined)
            alert(response.id);        
        if(response.message != undefined)
            alert("Mensaje: "+response.message);        
        CardsBlockA.empty();
        CardsBlockB.empty();
        CardsBlockC.empty();
        CardsBlockD.empty();
        var nColumnActual = 1;
        for (i = 0; i < response.length; i = i + 1) {
            var isAdded = false;
                quantity = 0;
                cajas = 0;
            simpleCart.each(function(item, x){                
                if(item.get("code") == response[i].ARTV_IDARTICULO){
                    isAdded = true;
                    quantity = item.quantity();
                    cajas = item.cajas();
                    return false;
                }
            });
            var xPro = response[i].PROV_IDPROMO;
            if(xPro){
                var xExistePromo = BuscarPromo(response[i].PROV_IDPROMO);            
                if(xExistePromo < 0)
                    promoList.push({"id": response[i].PROV_IDPROMO, "esc":response[i].PRON_DESDE1, "actual" : 0, "dar" : false});
            }
            var sDescrip = response[i].ARTV_IDARTICULO + '-' +  response[i].ARTV_DESCART;
            tag = '<div class="card simpleCart_shelfItem row" id="card' + i + '" data-id="' + response[i].ARTV_IDARTICULO + '" data-uxc="' + response[i].ARTN_UNIXCAJA + '" data-iva="' + response[i].ARTN_PORCIVA + '" data-pre="' + response[i].ARTN_PRECIOCAJ + '" data-des="' + 0 + '" data-rel="' + response[i].PROV_IDPROMO + '" data-por="' + response[i].PRON_DESC1 + '" data-esc="' + response[i].PRON_DESDE1 + '" data-min="'+response[i].ARTN_UNDMIN+'">' +
                
                  '  <div class="card-image col-md-6">'+
                  '    <img class="item_image-' + response[i].ARTV_IDARTICULO + '" src="./images/no-image.png" />' +
                  '  </div>'+
                  '  <div class="col-md-6"><h5 class="item_name">' + sDescrip.trim() + '</h5></div>' +
                  //'  <h5 class="item_name">' +  (sDescrip.substr(41,100).trim()  === "" ? "~" : sDescrip.substr(41,100)) + '</h5>' +
                  //'  <h5 class="item_name">' + response[i].ARTV_DESCART + '</h5>' +
                  '  <div class="card_controls">'+
                  '    <div class="ui-grid-b">' +
                  '     <div class="ui-block-a" style="width:40%;">' +
                  '      <h5 class = "'+ (!xPro ? "normal" : "tachado") + '">PVP. ' + parseFloat(response[i].ARTN_PRECIOCAJ).toFixed(2) + ' Bs</h5>' +
                  '      <h5 class = "'+ (!xPro ? "normal" : "tachado") + '">IVA. ' + parseFloat(response[i].ARTN_PRECIOCAJ*response[i].ARTN_PORCIVA/100).toFixed(2) + ' Bs</h5>' +
                  '      <h5 class = "'+ (!xPro ? "normal" : "tachado") + '">TOT. ' + parseFloat(response[i].ARTN_PREIVACAJ).toFixed(2) + ' Bs</h5>' +                      
                  '     </div>' +
                  '     <div class="ui-block-b" style="width:40%;">' +
                  '      <h5 class = "'+ (!xPro ? "ui-hide" : "promocion") + '">PVP. ' + parseFloat(redondeo(response[i].ARTN_PRECIOCAJ-(response[i].ARTN_PRECIOCAJ*response[i].PRON_DESC1/100),2)).toFixed(2) + ' Bs</h5>' +
                  '      <h5 class = "'+ (!xPro ? "ui-hide" : "promocion") + '">IVA. ' + parseFloat((response[i].ARTN_PRECIOCAJ-(response[i].ARTN_PRECIOCAJ*response[i].PRON_DESC1/100))*response[i].ARTN_PORCIVA/100).toFixed(2) + ' Bs</h5>' +
                  '      <h5 class = "'+ (!xPro ? "ui-hide" : "promocion") + '">TOT. ' + parseFloat((response[i].ARTN_PRECIOCAJ-(response[i].ARTN_PRECIOCAJ*response[i].PRON_DESC1/100))+((response[i].ARTN_PRECIOCAJ-(response[i].ARTN_PRECIOCAJ*response[i].PRON_DESC1/100))*response[i].ARTN_PORCIVA/100)).toFixed(2) + ' Bs</h5>' +                      
                  '     </div>' +
                  '     <div class="ui-block-c" style="width:20%;">' +
                  '      <div class="item_controls">' +
                  '       <a class="item_add capture-metadata ui-btn ui-btn-icon-notext ui-icon-check ui-btn-b ' + (isAdded ? "ui-hide" : "ui-show") + '" data-id="' + response[i].ARTV_IDARTICULO + '" data-empr="' + response[i].ARTV_CODIEMPR + '" href="#">Agregar</a>' +
                  '       <a class="item_remove capture-metadata ui-btn ui-btn-icon-notext ui-icon-minus ui-btn-b ' + (isAdded ? "ui-show" : "ui-hide") + '" data-id="' + response[i].ARTV_IDARTICULO + '" data-empr="' + response[i].ARTV_CODIEMPR + '" href="#">Quitar</a>' +
                  '      </div>' +
                  '     </div>' +
                  '    </div>' +
                  '  </div>' +
                  '  <h5 class = "'+ (!xPro ? "normal" : "promocion") + '">' + (!xPro ? "~" : 'Promocion válida a patir de ' + response[i].PRON_DESDE1 +  ' cajas' ) + '</h5>' +
                  '  <div class="item_cjs_controls" style="background-color: #004372; color: #DAE0EE">' +
                  '    <div class="item_cjs_btn minus"><a href="#" class="' + (isAdded ? "" : 'ui-state-disabled') + ' ui-shadow ui-corner-all ui-btn ui-btn-icon-notext ui-icon-minus ui-mini"></a></div>' +
                  '    <div class="item_cjs_btn"><h5 style="font-size:12px;">Cjs.</h5><input ' + (isAdded ? "" : 'disabled="disabled"') + 'class="ui-center-text" step="any" min="0" id="item_cjs_' + response[i].ARTV_IDARTICULO + '" value="' + parseFloat(cajas).toFixed(2) + '" type="number"></div>' +
                  '    <div class="item_cjs_btn plus"><a href="#" class="' + (isAdded ? "" : 'ui-state-disabled') + ' ui-shadow ui-corner-all ui-btn ui-btn-icon-notext ui-icon-plus ui-mini"></a></div></br>' +
                  '  </div>' +
                  '  <div class="item_qnt_controls">' +
                  '    <div class="item_qnt_btn minus"><a href="#" class="' + (isAdded ? "" : 'ui-state-disabled') + ' ui-shadow ui-corner-all ui-btn ui-btn-b ui-btn-icon-notext ui-icon-minus ui-mini"></a></div>' +
                  '    <div class="item_qnt_btn"><h5 class="style="font-size:12px;" >Und.</h5><input ' + (isAdded ? "" : 'disabled="disabled"') + 'class="ui-center-text" pattern="[0-9]*" min="1" id="item_qnt_' + response[i].ARTV_IDARTICULO + '" value="' + quantity + '" type="number"></div>' +
                  '    <div class="item_qnt_btn plus"><a href="#" class="' + (isAdded ? "" : 'ui-state-disabled') + ' ui-shadow ui-corner-all ui-btn ui-btn-b ui-btn-icon-notext ui-icon-plus ui-mini"></a></div>' +
                  '  </div>' +
                  '</div>';
            if(nColumnActual == 1){
                CardsBlockA.append(tag).trigger('create');
                nColumnActual++;
            }
            else if(nColumnActual == 2){
                CardsBlockB.append(tag).trigger('create');
                nColumnActual++;
            }
            else if (nColumnActual == 3) {
                CardsBlockC.append(tag).trigger('create');
                nColumnActual++;
            }
            else if (nColumnActual == 4) {
                CardsBlockD.append(tag).trigger('create');
                nColumnActual = 1;
            }
            pressEffectCard('card' + i);
        }

        $.each(response, function(i){
            var productID = this.ARTV_IDARTICULO;
            $.ajax({
                url: server + "product/" + productID + "/image",
                type: "GET",
                dataType: "json"
            }).done(function(response){
                if(response.IMGV_IMAGEN1.length > 0)
                    $("img.item_image-" + productID).attr("src", "data:image/jpeg;base64," + response.IMGV_IMAGEN1);
                else
                    $("img.item_image-" + productID).attr("src", "./images/no-image.png");
            });
        });
        Recalcular();
        updateCartItems();
    });    
}
function Paginar(){
    $(document).find(".simpleCart_paginas").text("Página " + _tmp.pagNUM + " de "+ _tmp.pagTOT + "  ");
}

function Recalcular(){
    var npCant=0, npSub=0, npDesc=0, npIva=0, npTotal=0;
    for (i = 0; i < promoList.length; i = i + 1) {
        promoList[i].actual = 0;
        promoList[i].dar = false; 
    }
    simpleCart.each(function( item , x ){
        item.des(0);
        nCjs = item.cajas();
        cRel = item.rel();    
        if(cRel){
            nPro = BuscarPromo(cRel);
            nEsc = promoList[nPro].esc;
            promoList[nPro].actual = promoList[nPro].actual + nCjs;
            nVan = promoList[nPro].actual;            
            if(nVan >= nEsc){
                promoList[nPro].dar = true;                
            }
        }
    });
    simpleCart.each(function( item , x ){
        sId = item.get("code");        
        nUxc = item.uxc();
        nPre = item.price();
        lIva = item.iva();
        nIva = (nPre*lIva/100);
        nUni = item.quantity();
        nCjs = item.cajas();
        nPor = item.por();
        nEsc = item.esc();
        cRel = item.rel();
        item.des(0.00);
        xDes = 0.00;         
        if(cRel){
            nPro = BuscarPromo(cRel);
            lDar = promoList[nPro].dar;            
            if(lDar){
                item.des(nPor);  
                nDes = item.des();    
                xDes = (nPre*nDes/100); 
                nPre =nPre - (nPre*nDes/100);
                nIva = (nPre*lIva/100);                
            }
        }
        xSub = nPre*nCjs;
        xIva = nIva*nCjs;
        xDes = xDes*nCjs;
        xTot = xSub +  xIva;
        npCant += nCjs;
        npSub += xSub;
        npDesc += xDes;
        npIva += xIva;
        npTotal += xTot;
    });
    $(document).find(".simpleCart_quantity1").text("Bs " + npCant.formatMoney(2, '.', ','));
    $(document).find(".simpleCart_desc").text("Bs " +npDesc.formatMoney(2, '.', ','));
    $(document).find(".simpleCart_grandTotal").text("Bs " +simpleCart.total().formatMoney(2, '.', ','));
    $(document).find(".simpleCart_iva").text("Bs " + npIva.formatMoney(2, '.', ','));
    $(document).find(".simpleCart_finalTotal").text("Bs " + npTotal.formatMoney(2, '.', ','));
}

function pressEffectCard(x) {
    var id = $("#" + x);
    id.off('touchstart').on('touchstart', function () {
        id.addClass("holoPressEffectDiv");
    });
    id.off('touchend').on('touchend', function () {
        id.removeClass("holoPressEffectDiv");
    });
    id.off('touchmove').on('touchmove', function () {
        id.removeClass("holoPressEffectDiv");
    });
}
function UnidadMinima(xMin,xUnd,xManual){
    var oMin = typeof xMin !== "number" ?  parseInt(xMin) : xMin;
    var oUnd = typeof xUnd !== "number" ?  parseInt(xUnd) : xUnd;
    var oManual = typeof xManual !== "undefined" ?  xManual : false;
    if(oManual==false)
        if (oMin<0) oUnd++; else oUnd--;
    var oRetornar = (oManual) ? oUnd : oUnd+oMin;
    if(oUnd<oMin){
        alert("Para este producto se permite mínimo "+oMin+" unidades o múltiplos de este");
        oRetornar = oMin;
    }
    if((oRetornar%oMin) != 0){
        alert("Para este producto se solo se permite multiplos de  "+oMin);
        oRetornar = (Math.trunc(oRetornar/oMin)*oMin)+oMin;
    }
    return oRetornar;
}
function updateCartItems() {
    simpleCart.update();
    $("#cart-panel").find("table tr").each(function(i, val){
        var i = 0;
        $(this).find("th, td").each(function(){
            i++; if(i > 5) $(this).remove();
        });
    }).trigger("updatelayout");
}
function redondeo(numero, decimales)
{
    var flotante = parseFloat(numero);
    var resultado = Math.round(flotante*Math.pow(10,decimales))/Math.pow(10,decimales);
    return resultado;
}
function BuscarPromo(cId)
{
    return promoList.map((el) => el.id).indexOf(cId);
}
$(function(){ 
    $.ajax({
        url: server + "product/groups",
        type: "GET",
        dataType: "json"
    }).done(function(response){
        $.each(response.GROUPAA, function(i){
            $("#group-aa").append('<option data-grupo="' + this.value + '" value="' + this.descrip + '">' + this.descrip + '</option>');
        });        
        $.each(response.GROUPAB, function(i){
            $("#group-ab").append('<option data-grupo="'  + this.grupo + '" data-subgrupo="' + this.value + '" value="' + this.descrip + '">' + this.descrip + '</option>');
        });
        $.each(response.GROUPAC, function(i){
            $("#group-ac").append('<option data-grupo="' + this.grupo + '" data-subgrupo="' + this.subgrupo + '" value="' + this.descrip + '">' + this.descrip + '</option>');
        });
    });
});