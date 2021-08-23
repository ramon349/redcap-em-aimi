<?php
namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$em_setting = (isset($_GET["em_setting"])) ? filter_var($_GET["em_setting"], FILTER_SANITIZE_STRING) : null;
$file_path  = (isset($_GET["filepath"])) ? filter_var($_GET["filepath"], FILTER_SANITIZE_STRING) : null;

$active_alias   = $module->getProjectSetting("active_alias");
$aliases        = $module->getProjectSetting('aliases');
$current        = $aliases[$active_alias];
$shard_paths    = array_key_exists("model_json",$current) && !empty($current["model_json"]) ? $current["model_json"]["weightsManifest"][0]["paths"] : array();

if($em_setting && !$file_path){
    if($em_setting == "config_js"){
        header("content-type: application/javascript");
        echo $current["config_js"];
    }

    if($em_setting == "model_json"){
        header("content-type: application/json");
        echo json_encode($current["model_json"]);
    }
}elseif($file_path){
    $file_name  = $file_path;
    $file_path  = APP_PATH_TEMP . $file_name;

    // Make sure the requested file is one of the desired active model's shard paths
    $model_file_check = false;
    foreach($shard_paths as $shard_path ){
        if(strpos($shard_path , $file_name) > -1){
            $model_file_check = true;
            break;
        }
    }

    // MAKE SURE THE FILE PATH EXISTS
    if($model_file_check && file_exists($file_path)){
        $file_size  = filesize($file_path);
        $mime_type  = mime_content_type($file_path) ?: 'application/octet-stream';

        header('Accept-Ranges: bytes');
        header("Content-Length: $file_size");
        header("content-type: $mime_type");

        header_remove('Access-Control-Allow-Origin');
        header_remove('cache-control');
        header_remove('Connection');
        header_remove('Expires');
        header_remove('Keep-Alive');
        header_remove('Pragma');
        header_remove('REDCap-Random-Text');
        header_remove('X-Content-Type-Options');
        header_remove('X-XSS-Protection');

        $handle = fopen($file_path, "rb");
        $contents = fread($handle, filesize($file_path));
        fclose($handle);
        echo $contents;
    }
}
exit();
?>
