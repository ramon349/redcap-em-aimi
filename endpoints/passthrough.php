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
    $temp       = explode("/", $file_path);
    $file_name  = array_pop($temp);

    if(file_exists($file_path)){
        $file_size  = filesize($file_path);
        $mime_type  = mime_content_type($file_path) ?: 'application/octet-stream';

        header("content-type: $mime_type");
        header('Content-Disposition: attachment; filename="'.$file_name.'"');
        header("Content-Length: $file_size");
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Pragma: public');

        $shard_file = file_get_contents($file_path);
        var_dump($shard_file);
        exit();

    //    header('Connection: Keep-Alive');
    //    header('Expires: 0');
    //    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    //    header("Cache-Control: post-check=0, pre-check=0", false);
    //    header("Pragma: no-cache");
    }
}
exit();
?>
<!--http://localhost/api/?type=module&prefix=redcap_aimi&page=endpoints%2Fpassthrough&filepath=/var/www/html/temp/20210814075708_group1-shard1of7.bin&pid=58&NOAUTH-->
