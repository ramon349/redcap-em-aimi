<?php
namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$em_setting = (isset($_GET["em_setting"])) ? filter_var($_GET["em_setting"], FILTER_SANITIZE_STRING) : null;
$file_path  = (isset($_GET["filepath"])) ? filter_var($_GET["filepath"], FILTER_SANITIZE_STRING) : null;

$selected_alias = $module->getProjectSetting("active_alias");
$aliases        = $module->getProjectSetting('aliases');
$model_config   = $aliases[$selected_alias];


if($em_setting && !$file_path){
    if($em_setting == "config_js"){
        header("content-type: application/javascript");
        echo $model_config["config_js"];
    }

    if($em_setting == "model_json"){
        header("content-type: application/json");
        echo json_encode($model_config["model_json"]);
    }
}elseif($file_path){
    $file_name  = $file_path;
    $file_path  = APP_PATH_TEMP . $file_name;

    if(file_exists($file_path)){
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

        $shard_file = file_get_contents($file_path);
        var_dump($shard_file);
    }
}
exit();
?>
