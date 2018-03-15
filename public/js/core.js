var server = "./api/",
    token  = localStorage.getItem("_s4gcomweb-token"),
    _data  = {},
    _tmp = {},  
    _pageType;

$('#imagen').val('');
$(function(){

  var changePage = function(page, trans, rev, hash){
      $.mobile.changePage(page, {
      transition: trans | "slide",
      reverse: rev | true,
      changeHash: hash | true
    });
  }

  $.ajaxSetup({
    //crossDomain: true,
    beforeSend: function(xhr) {
      //xhr.withCredentials = true;
      $.mobile.loading("show", {
        text: "Cargando...",
        textVisible: true
      });
    },
    complete: function(){
      $.mobile.loading("hide");
    }
  });

  $(this).bind("mobileinit", function(){
    //$.support.cors = true;
    //$.mobile.ajaxEnabled = false;
    $.mobile.allowCrossDomainPages = true;
    $.mobile.defaultPageTransition = 'none';
    $.mobile.buttonMarkup.hoverDelay = true;
    $.mobile.hoverDelay = true; //1.4.0+
    $.mobile.selectmenu.prototype.options.nativeMenu = true;
  }).on("click", ".capture-metadata", function(){
    _data = $(this).data();    
  }).on("click", ".logout-action", function(e){
      
      //redireccionamiento logout
    e.preventDefault();
    $.ajax({
      url: server + "auth/logout",
      type: "POST"
    }).done(function(e){
      window.location.assign("https://www.gdp.com.ve/wp-content/themes/Impreza/logaut.php");
    });
      
      
  }); 
});

function getLevel(val) {
  switch(parseInt(val)) {
    case 0: val = "ADMINISTRADOR"; break;
    case 1: val = "VENDEDOR"; break;
    case 2: val = "CLIENTE"; break;
  }
  return val;
}

function buildQuery(obj) {
  var Result= '';
  if(typeof(obj)== 'object') {
    jQuery.each(obj, function(key, value) {
      Result+= (Result) ? '&' : '';
      if(typeof(value)== 'object' && value.length) {
        for(var i=0; i<value.length; i++) {
          Result+= [key+'[]', encodeURIComponent(value[i])].join('=');
        }
      } else {
        Result+= [key, encodeURIComponent(value)].join('=');
      }
    });
  }
  return Result;
}

Number.prototype.formatMoney = function(c, d, t){
  var n = this, 
      c = isNaN(c = Math.abs(c)) ? 2 : c, 
      d = d == undefined ? "." : d, 
      t = t == undefined ? "," : t, 
      s = n < 0 ? "-" : "", 
      i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
      j = (j = i.length) > 3 ? j % 3 : 0;
     return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };

  function soloNumeros(e){
    var key = window.Event ? e.which : e.keyCode
    return (key >= 48 && key <= 57)
  }
function limpiar(){
  $("#imagen").val('');
}


 function abonarfact(){
    $("#mensaje3").fadeOut(); 
    $("#mensaje4").fadeOut();
    var monto = $("#monto").val();
    var descri = $("#descri").val(); 
    var banco = $("#banco").val();
    var tpago = $("#tpago").val();
    var imagen = $("input[name='imagen']");
    //var datos = "monto="+monto+"&descri="+descri+"&nume="+$('#nume').val()+"&banco="+banco+"&imagen="+imagen+"&tpago="+tpago;
    var tp = Number($("#tp").val());
    
     var pattern = /^\d+(\.\d+)?$/;  
    if(!pattern.test(monto) && monto!=""){
       $("#mensaje7").fadeIn("slow");
       $("#mensaje6").fadeOut();
       $("#mensaje1").fadeOut();
       return false;
    }  
    else
       $("#mensaje7").fadeOut();
    if(monto>tp){
       $("#mensaje6").fadeIn("slow");
       $("#mensaje7").fadeOut();
       $("#mensaje1").fadeOut();
       return false;
     }
     else
        $("#mensaje6").fadeOut();

    if(monto==""){ 
       $("#mensaje1").fadeIn("slow");
       $("#mensaje6").fadeOut();
       $("#mensaje7").fadeOut();
       return false;
    }
    else
        $("#mensaje1").fadeOut();

    if(descri==""){ 
       $("#mensaje2").fadeIn("slow");
       return false;
    }
    else
        $("#mensaje2").fadeOut();  

    if(banco==0){
       $("#mensaje4").fadeIn("slow");
       return false;
    }
    else
       $("#mensaje4").fadeOut();

     if(tpago==0){
       $("#mensaje5").fadeIn("slow");
       return false;
    }
    else
       $("#mensaje5").fadeOut();

     var form=document.getElementById('varios');
     var datos=new FormData(form);

     $.ajax({
                    type:"POST",
                    url:"../../createAbono",
                    data:datos,
                    contentType:false,
                    processData:false,
                    cache:false,
                    success:function(response){
                        console.log(response);
                        $("#monto").val('');
                        $("#descri").val('');
                        $("#mensaje3").fadeIn("slow");
                }
            });
    
    return false;
  }

   function abonarfacts(){
    $("#mensaje3").fadeOut(); 
    $("#mensaje4").fadeOut();
    var monto = $("#monto").val();
    var descri = $("#descri").val(); 
    var banco = $("#banco").val();
    var tpago = $("#tpago").val();
    var imagen = $("input[name='imagen']");
    var pago = $("#nmontos").val();
    var tpendiente = $("#tpendiente").val();
    var tp = Number($("#tp").val());
    
    var pattern = /^\d+(\.\d+)?$/;  
    if(!pattern.test(monto) && monto!=""){
      $("#mensaje7").fadeIn("slow");
      $("#mensaje6").fadeOut();
      $("#mensaje1").fadeOut();
       return false;
    }  
    else
       $("#mensaje7").fadeOut();

    if(monto>tp){
       $("#mensaje6").fadeIn("slow");
       $("#mensaje7").fadeOut();
       $("#mensaje1").fadeOut();
       return false;
     }
     else
        $("#mensaje6").fadeOut();
     if(monto==""){ 
       $("#mensaje1").fadeIn("slow");
       $("#mensaje6").fadeOut();
       $("#mensaje7").fadeOut();
       return false;
    }
    else
        $("#mensaje1").fadeOut();

    if(descri==""){ 
       $("#mensaje2").fadeIn("slow");
       return false;
    }
    else
        $("#mensaje2").fadeOut();  

    if(banco==0){
       $("#mensaje4").fadeIn("slow");
       return false;
    }
    else
       $("#mensaje4").fadeOut();

     if(tpago==0){
       $("#mensaje5").fadeIn("slow");
       return false;
    }
    else
       $("#mensaje5").fadeOut();
     
   
     var form=document.getElementById('varios');
     var datos=new FormData(form);
     $.ajax({
                    type:"POST",
                    url: "../../createAbonos",
                    data:datos,
                    contentType:false,
                    processData:false,
                    cache:false,
                    success:function(response){
                        console.log(response);
                        $("#monto").val('');
                        $("#descri").val('');
                        $("#mensaje3").fadeIn("slow");
                }
            });
    
    return false;
  }

  function guardar(){
  var idmod = [];
  var i=0;
      $('.amod').each(function(){

        if($(this).is(':checked')){
          
          idmod.push($(this).data('id'));
          
          i++;
        }

      });
     $.ajax({
                    type:"GET",
                    url: "../savemod/"+idmod,                                     
                    success:function(response){
                        console.log(response);
                         $("#mensaje").fadeIn("slow");     
                         $("#mensaje").fadeOut(4000);                   
                }
            });
  }
  
  function gpagos(){

    var rutas = $("#rutas").val();
    var datos = "rutas="+rutas;
    var fechaini = $("#fechaini").val();
    var fechafin = $("#fechafin").val();

    if(rutas==0){ 
       $("#mensaje1").fadeIn("slow");  
       return false;
    }
    else
        $("#mensaje1").fadeOut();

    if(fechaini==""){ 
       $("#mensaje2").fadeIn("slow");  
       return false;
    }
    else
        $("#mensaje2").fadeOut();

    if(fechafin==""){ 
       $("#mensaje3").fadeIn("slow");  
       return false;
    }
    else
        $("#mensaje3").fadeOut();  

     
      div = document.getElementById('facturas');
          div.style.display = '';

     $.post(server + "../pagosday/"+rutas+"/"+fechaini+"/"+fechafin,'',function(response){
     // console.log(response);
          
          var content='';
          $('.listapagos').children('tr').remove();
          for(var i=0; i<response.length; i++) {

              content='<tr><td>'+response[i].NUMEDOCU+'</td><td>'+response[i].NOMBCLIE+'('+response[i].CODICLIE+')'+'</td><td>'+response[i].FECHA+'</td><td>'+response[i].DESCRI+'</td><td>'+response[i].nombretpago+'</td><td>'+response[i].NOMBBANC+'</td><td style="text-align: right">'+response[i].MONTO.formatMoney(2, '.', ',')+'</td> <td style="text-align: center;"> <a href="cpantalla/'+response[i].id+'" class="btn btn-default btn-sm" data-role="button" title="Captura de pantalla" transition="flip" data-rel="dialog" data-theme="d"> <span class="glyphicon glyphicon-picture"></span></a></td></tr>';
              $('.listapagos').append(content);
              content='';
            }
     })

    return false;
  }
