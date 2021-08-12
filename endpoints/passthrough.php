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
    $shard_file = file_get_contents($file_path);
    header("content-type: application/octet-stream");
    echo $shard_file;
}
?>
