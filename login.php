<?php
require("core/config_var.php");
$obj=new funciones();
$user=$_POST[0];
$login=$_POST[0];
$pass=$_POST[1];
$tipo=$_POST[2];

if($tipo==3){
    
  $usuario=$obj->dologin($user,$pass);
    
    if($usuario==9){
        $records["resp"] = 1002;
        $records["error"] = "USUARIO NO AUTORIZADO";
    }elseif($usuario){
        
        $res=explode(',',$usuario['dn']);
        foreach($res as $r){
            $p=explode('=',$r);
            $ar[0][$p[0]].=$p[1].'|';
            $ar[0][$p[0]]=str_replace('Excepción Unidades Extraibles|','',$ar[0][$p[0]]);
        }
            $des=explode('|',$ar[0]['OU']);
        if($des[1]=='GDP San Cristobal'){
            $sc='SC';
        }elseif($des[1]=='GDP Merida'){
            $sc='ME';
        }elseif($des[1]=='GDP La Grita'){
            $sc='LG';
        }else{
            $sc='SC';
        }
        
        if($usuario["title"][0] > 0){
            $info=['userlogin'=> $login ,'CUENV_USUARIO' => str_replace('|','',$ar[0]['CN']),'CUENV_TIPO' => 1];
            $info['details']=['CRVV_CODIEMPR' => 'E0000053','CRVV_IDVENDEDOR' => $usuario["title"][0]];
            $info['info']=['VENDV_CODIEMPR' => 'E0000053','VENDV_IDVENDEDOR' => $usuario["title"][0],'VENDV_NOMBRE' => str_replace('|','',$ar[0]['CN'])];
        }else{
            $info=['CUENV_USUARIO' => str_replace('|','',$ar[0]['CN']),'CUENV_TIPO' => 0];
        }
        
        
        
            
        $dsc=$des[0];
        $dsc=str_replace(' SC','',$dsc);
        $dsc=str_replace(' Mda','',$dsc);
        $dsc=str_replace(' LG','',$dsc);
        $records["login"] = $usuario["samaccountname"][0]; //$usuario;
        $records["tipo"] = $tipo;
        $records["ruta"] = $usuario["title"][0];
        $records["cod"] = $usuario["department"][0];
        $records["cargo"] = $usuario["description"][0];
        $records["sucur"] = $sc;
        $records["user"] = str_replace('|','',$ar[0]['CN']);
        $records["dept"] = $dsc;
        $records['edit']=date("Y-m-d");
        $records["resp"] = "ok";
        $records["email"] = $usuario["mail"][0];
        $records["cedula"] = $usuario["info"][0];
        
        
        $info['DEPARTAMENTO']= $records["dept"];
        $info['CARGO']=$records["cargo"];
        $info['CEDULA']= $records["cedula"];
        $info=json_encode($info);
        $info=base64_encode($info);
        $records["info"] = $info;
    }else{
        $records["resp"] = 1001;
        $records["error"] = "USUARIO INVALIDO";
    }
    
}
    

if($tipo == 2 || $tipo == 1){
    
    $obj_v = new Validate();
    $pass=$obj_v->Encrypt($pass);
    $user=$obj->dologi_ini($user,$pass);
    
    if(!empty($user)){
        $records['resp']="ok";
        $records['cod']=trim($user[0]['codiuser']);
        $records['rif']=trim($user[0]['rifuser']);
        $records['user']=trim($user[0]['nameuser']);
        $records['dir']=trim($user[0]['direuser']);
        $records['tipo']=trim($user[0]['tipouser']);
        $records['login']=trim($user[0]['logiuser']);
        $records['edit']=trim($user[0]['edituser']);
        $records['foto']=trim($user[0]['fotouser']);
        $records['code']=trim($user[0]['codeuser']);
        $records["sucur"] = "SC";
    }else{
        
        if($tipo==2){ 
            $pro=$obj->get_proveed($login);
            if(!empty($pro)){
                $new=$obj->add_user($pro,'PR');
                $user=$obj->dologi_ini($login,'bGFwcmluY2lwYWw=');
                $user[0]['tipouser']="PR";
                $records['resp']="ok";
                    $records['cod']=trim($user[0]['codiuser']);
                    $records['rif']=trim($user[0]['rifuser']);
                    $records['user']=trim($user[0]['nameuser']);
                    $records['dir']=trim($user[0]['direuser']);
                    $records['tipo']=trim($user[0]['tipouser']);
                    $records['login']=trim($user[0]['logiuser']);
                    $records['edit']=trim($user[0]['edituser']);
                    $records['foto']=trim($user[0]['fotouser']);
                    $records['code']=trim($user[0]['codeuser']);
                    $records["sucur"] = "SC";
            }else{
                $records["resp"] = 1001;
                $records["error"] = "USUARIO INVALIDO";
            }
        }elseif($tipo==1){
            $clie=$obj->get_cliente($login);
            if(!empty($clie)){
                $new=$obj->add_user($clie,'CL');
                $user=$obj->dologi_ini($login,'bGFwcmluY2lwYWw=');
                $user[0]['tipouser']="CL";
                $records['resp']="ok";
                    $records['cod']=trim($user[0]['codiuser']);
                    $records['rif']=trim($user[0]['rifuser']);
                    $records['user']=trim($user[0]['nameuser']);
                    $records['dir']=trim($user[0]['direuser']);
                    $records['tipo']=trim($user[0]['tipouser']);
                    $records['login']=trim($user[0]['logiuser']);
                    $records['edit']=trim($user[0]['edituser']);
                    $records['foto']=trim($user[0]['fotouser']);
                    $records['code']=trim($user[0]['codeuser']);
                    $records["sucur"] = "SC";
                
            }else{
                $records["resp"] = 1001;
                $records["error"] = "USUARIO INVALIDO";
            }
            
            }

        
    }
    
   $info='';
            if(trim($records['tipo'])== 'CL'){
                $us=trim($user[0]['logiuser']);
                $ps=trim($user[0]['pcomuser']);
                $info=$obj->login_comerse($us,$ps);
                $info=json_encode($info);
                $info=base64_encode($info);
            }
            $records['info']=$info; 
    
}

$resp=json_encode($records,JSON_UNESCAPED_UNICODE);
echo $resp;
?>