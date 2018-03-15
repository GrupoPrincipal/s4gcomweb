<?php
session_start();
error_reporting(0);
ini_set(display_errors, 0);
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
define ( 'DOMAIN_ROOT', 'https://' . $_SERVER ['SERVER_NAME'] . '/s4gcomweb/public/' );
if(!isset($_GET['ruta'])){
$user=$_GET['user'];
$user=base64_decode($user);
$user=json_decode($user);
$user=get_object_vars($user);
$user=$user['info'];
$user=base64_decode($user);
$user=json_decode($user);
$user=get_object_vars($user);
$user['details']=get_object_vars($user['details']);
$user['info']=get_object_vars($user['info']);

// Conectando, seleccionando la base de datos

// Realizar una consulta MySQL
if($user["CUENV_TIPO"] == 1){
    $link = mysql_connect('192.168.64.2:3307', 'estaciones', '123') or die('No se pudo conectar: ' . mysql_error());
    mysql_select_db('webs4gcom') or die('No se pudo seleccionar la base de datos');
    $usr=$user['userlogin'];
    //consultar id del vendedor;
    $query = "SELECT CUENV_IDCUENTA FROM tcuentas WHERE CUENV_USUARIO like '$usr' LIMIT 1";
    $result = mysql_query($query,$link) or die('Consulta fallida: ' . mysql_error());
    $result=mysql_fetch_array($result);
    $id=$result['CUENV_IDCUENTA'];
    $user['CUENV_IDCUENTA']=$id;
    $user['details']['CRVN_IDCUENTA']=$id;
    
    
    //consultar rutas asignadas al vendedor
    $cedu=$user['CEDULA'];
    $rutas=array();
    if($cedu != ''){
        
        
        
    
        $ventor = mysql_connect('192.168.64.2:3307', 'estaciones', '123') or die('No se pudo conectar: ' . mysql_error());
        mysql_select_db('ventoradm001') or die('No se pudo seleccionar la base de datos');

        $q_rutas = "SELECT CODIRUTA,NOMBVEND,CEDUVEND,TELEVEND FROM `truta` WHERE CEDUVEND like '%$cedu%'";
        $resp = mysql_query($q_rutas) or die('Consulta fallida: ' . mysql_error());
        $i=0;

       while($fila = mysql_fetch_array($resp)){
           $rutas[$i]['CODIRUTA']=$fila['CODIRUTA'];
           $rutas[$i]['NOMBVEND']=$fila['NOMBVEND'];
           $rutas[$i]['CEDUVEND']=$fila['CEDUVEND'];
           $rutas[$i]['TELEVEND']=$fila['TELEVEND'];
           $i++;
       }
        
    }
    $user['rutas']=$rutas;
    mysql_close($link);
}

if(strtolower($user["DEPARTAMENTO"]) == 'logistica' || (strtolower($user["DEPARTAMENTO"]) == 'ventas' &&  strtolower($user["CARGO"]) == 'analista')){
     $user['CUENV_TIPO']=4;
}
if(strtolower($user["DEPARTAMENTO"]) == 'mercadeo'){
     $user['CUENV_TIPO']=5;
}
    
if($user["CUENV_TIPO"] == 0 && strtolower($user["DEPARTAMENTO"]) == 'ventas' && strtolower($user["CARGO"]) == 'lider'){
  $cedu=$user['CEDULA'];
  $user['CUENV_TIPO']=3;
    $ventor = mysql_connect('192.168.64.2:3307', 'estaciones', '123') or die('No se pudo conectar: ' . mysql_error());
    mysql_select_db('ventoradm001') or die('No se pudo seleccionar la base de datos');
    
    $q_rutas = "SELECT tsupa.CODISUPE, tsupa.CODIVENA, truta.CODIRUTA, truta.NOMBVEND, truta.TELEVEND, tsupa.NOMBSUPE, tsupa.CEDUSUPE FROM tsupa INNER JOIN truta ON truta.CODISUPE = tsupa.CODISUPE WHERE tsupa.CEDUSUPE like '%$cedu'";
    $resp = mysql_query($q_rutas) or die('Consulta fallida: ' . mysql_error());
    $i=0;
    
   while($fila = mysql_fetch_array($resp)){
       $rutas[$i]['CODISUPE']=$fila['CODISUPE'];
       $rutas[$i]['CODIVENA']=$fila['CODIVENA'];
       $rutas[$i]['CODIRUTA']=$fila['CODIRUTA'];
       $rutas[$i]['NOMBVEND']=$fila['NOMBVEND'];
       $rutas[$i]['TELEVEND']=$fila['TELEVEND'];
       $rutas[$i]['NOMBSUPE']=$fila['NOMBSUPE'];
       $rutas[$i]['CEDUSUPE']=$fila['CEDUSUPE'];
       $i++;
   }
    $user['rutas']=$rutas;
}
if($user["CUENV_TIPO"] == 0){
  $user['CUENV_IDCUENTA'] = '0';
  $user['details'] = array( 
      'CRVV_CODIEMPR' => 'E0000053' ,
      'CRVV_IDVENDEDOR' => '01' ,
      'CRVN_IDCUENTA' => '0');
  $user['info'] =  array (
      'VENDV_CODIEMPR' => 'E0000053',
      'VENDV_IDVENDEDOR' => '01',
      'VENDV_NOMBRE' => $user['CUENV_USUARIO']);
    
    $ventor = mysql_connect('192.168.64.2:3307', 'estaciones', '123') or die('No se pudo conectar: ' . mysql_error());
    mysql_select_db('ventoradm001') or die('No se pudo seleccionar la base de datos');
    
    $q_rutas = "SELECT tsupa.CODISUPE, tsupa.CODIVENA, truta.CODIRUTA, truta.NOMBVEND, truta.TELEVEND, tsupa.NOMBSUPE, tsupa.CEDUSUPE FROM tsupa INNER JOIN truta ON truta.CODISUPE = tsupa.CODISUPE ";
    $resp = mysql_query($q_rutas) or die('Consulta fallida: ' . mysql_error());
    $i=0;
    
   while($fila = mysql_fetch_array($resp)){
       $rutas[$i]['CODISUPE']=$fila['CODISUPE'];
       $rutas[$i]['CODIVENA']=$fila['CODIVENA'];
       $rutas[$i]['CODIRUTA']=$fila['CODIRUTA'];
       $rutas[$i]['NOMBVEND']=$fila['NOMBVEND'];
       $rutas[$i]['TELEVEND']=$fila['TELEVEND'];
       $rutas[$i]['NOMBSUPE']=$fila['NOMBSUPE'];
       $rutas[$i]['CEDUSUPE']=$fila['CEDUSUPE'];
       $i++;
   }
    $user['rutas']=$rutas;
}

$_SESSION['user']=$user;
// die(var_dump($user));
//var_dump($user);

if(count($user['rutas'])>1 && $user["CUENV_TIPO"] != 0 && $user["CUENV_TIPO"] != 3){
    $_SESSION["user"]["CUENV_TIPO"]=1;
  //  die(var_dump($_SESSION["user"]));
?>
<html>
<head>
     <meta name="content"  content="text/html;" http-equiv="content-type" charset="utf-8">
  <?php include("templates/enlaces.phtml"); ?>
</head>
<body>
    <div data-role="page" style="    display: table;  background-color: rgba(0, 0, 0, 0.52);">
        <div style="    display: table-cell;    height: 100%;    width: 100%;vertical-align: middle;">
           <center>   
            <div  id="popupDialog" data-overlay-theme="b" data-theme="b" data-dismissible="false" style="max-width:400px;     border: solid;border-width: 1px;background: #fff;">
                <div data-role="header" data-theme="a">
                <h1 style="color:#000;">Seleccionar ruta</h1>
                </div>
                <div role="main" class="ui-content">
                    <h3 class="ui-title">¿Con cual ruta desea iniciar?</h3>


                    <div data-role="controlgroup" data-type="horizontal" >
                     <?php foreach($rutas as $r){ ?>
                        <a href="#" onclick="cargarruta(<?php echo $r['CODIRUTA'] ?>)" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b">Ruta <?php echo $r['CODIRUTA']; ?></a>
                    <?php } ?>
                    </div>
                </div>
            </div>
           </center>
        </div>
    </div> 
<script>
    function cargarruta(ruta){
        $.post('dologin.php?ruta='+ruta,'ruta='+ruta,function(response){
               window.location.assign("./");
        });
    }
</script>
</body>
</html>

<?php
}else{
  header("location: public");  
}
    
}else{
    $ruta=str_pad($_POST['ruta'],2,0, STR_PAD_LEFT); 
    $_SESSION['user']['details']['CRVV_IDVENDEDOR']=$ruta;
    $_SESSION['user']['info']['VENDV_IDVENDEDOR']=$ruta;
    print_r($_SESSION['user']);
}
//die(var_dump($user));
?>