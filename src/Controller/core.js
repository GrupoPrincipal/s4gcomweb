var server = "./api/",
    token  = localStorage.getItem("_s4gcomweb-token"),
    _data  = {},
    _tmp = {},  
    _pageType;


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
    e.preventDefault();
    $.ajax({
      url: server + "auth/logout",
      type: "POST"
    }).done(function(e){
      window.location.replace("http://www.gdp.com.ve/");
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