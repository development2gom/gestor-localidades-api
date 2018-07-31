<?php

namespace app\models;

use Yii;
use app\models\ConstantesWeb;
use app\config\ConstantesDropbox;

class Dropbox{
    
    const AUTHORIZATION = "Authorization: Bearer ".ConstantesDropbox::AUTORIZACION;    

    public static function curlSetopt($ch, $url, $header, $fields){
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));

        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        
        return $result;
    }
    
    public static function listarFolder($path = ""){
        $url = 'https://api.dropboxapi.com/2/files/list_folder';
        $header = array(
            'Content-Type: application/json' , 
            self::AUTHORIZATION
        );
        $fields = array(
            "path" => "/". ConstantesDropbox::NOMBRE_CARPETA . $path,
            "recursive" => false,
            "include_media_info" => false,
            "include_deleted" => false,
            "include_has_explicit_shared_members" => false,
            "include_mounted_folders" => true
        );
                                   
        $ch = curl_init();
        $result = self::curlSetopt($ch, $url, $header, $fields);

        return $result;
    }

    public static function crearFolder($path = ""){
        $url = 'https://api.dropboxapi.com/2/files/create_folder_v2';
        $header = array(
            'Content-Type: application/json' , 
            self::AUTHORIZATION
        );
        $fields = array(
            "path" => "/".$path,
            "autorename" => false
        );
                                   
        $ch = curl_init();
        $result = self::curlSetopt($ch, $url, $header, $fields);
        
        return $result;
    }

    public static function descargarArchivo($path = ""){
        $url = 'https://api.dropboxapi.com/2/files/get_temporary_link';
        
        $header = array(
            self::AUTHORIZATION,            
            'Content-Type: application/json'
        );
        $fields = array(
            "path" => $path
        );

        $ch = curl_init();
        $result = self::curlSetopt($ch, $url, $header, $fields);
        
        return $result;
    } 

    public static function subirArchivo($folder, $path){
        $url = 'https://content.dropboxapi.com/2/files/upload';
        $args = array(
            'path' => '/'. ConstantesDropbox::NOMBRE_CARPETA . $folder . '/' . $path->name,
            'mode' => 'add'
        );
        $args = json_encode($args);
        $header = array(
            self::AUTHORIZATION,
            'Content-Type: application/octet-stream',
			'Dropbox-Api-Arg: '.$args
        );
        $fp = fopen($path->tempName, 'rb');
        $size = filesize($path->tempName);
        
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);

        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        fclose($fp);

        return $result;
    }

    public static function eliminarArchivo($path){
        $url = 'https://api.dropboxapi.com/2/files/delete_v2';
        $fields = array(
            'path' => $path
        );
        $header = array(
            self::AUTHORIZATION,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        $result = self::curlSetopt($ch, $url, $header, $fields);

        return $result;
    }

    public static function moverArchivo($fromPath, $toPath){
        $url = 'https://api.dropboxapi.com/2/files/move_v2';
        $fields = array(
            "from_path" => $fromPath,
            "to_path" => $toPath,
            "allow_shared_folder" => false,
            "autorename" => false,
            "allow_ownership_transfer" => false
        );
        $header = array(
            self::AUTHORIZATION,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        $result = self::curlSetopt($ch, $url, $header, $fields);

        return $result;
    }

    public static function copiarArchivo($fromPath, $toPath){
        $url = 'https://api.dropboxapi.com/2/files/copy_v2';
        $fields = array(
            "from_path" => $fromPath,
            "to_path" => $toPath,
            "allow_shared_folder" => false,
            "autorename" => false,
            "allow_ownership_transfer" => false
        );
        $header = array(
            self::AUTHORIZATION,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        $result = self::curlSetopt($ch, $url, $header, $fields);

        return $result;
    }
}