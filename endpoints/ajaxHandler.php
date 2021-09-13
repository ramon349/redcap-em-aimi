<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
    $path = filter_var($_POST['path'], FILTER_SANITIZE_STRING);

    switch($type) {
        case 'fetchVersions':
            $ret = $module->fetchVersions($path);
            echo json_encode($ret);
            break;
        case 'fetchModelConfig':
            $ret = $module->fetchModelConfig($path);
            echo json_encode($ret);
            break;
        case 'saveConfig':
            $alias = filter_var($_POST['alias'], FILTER_SANITIZE_ENCODED);
//            $config = filter_var(json_encode($_POST['config']), FILTER_SANITIZE_STRING);
            $ret = $module->saveConfig($alias, $_POST['config']);
            echo json_encode($ret);
            break;
        case 'getExistingModelConfig':
            $alias = filter_var($_POST['alias'], FILTER_SANITIZE_ENCODED);
            $ret = $module->getExistingModuleConfig($alias);
            echo json_encode($ret);
            break;
        case 'applyConfig':
            $alias          = filter_var($_POST['alias'], FILTER_SANITIZE_STRING);
            $aliases        = !empty($module->getProjectSetting("aliases")) ? $module->getProjectSetting("aliases") : array();

            if(!empty($aliases) && isset($aliases[$alias]) ){
                $current    = $aliases[$alias];
                $uri        = $current["url"];
                $info       = $current["info"];
                $ret        = $module->applyConfig($uri, $info, $alias);

            }
            
            $redirect_url   = $module->getUrl("pages/aimi.php");
            echo json_encode($redirect_url);
            break;
        case 'clearTempFiles':
            $ret = $module->clearTempFiles();
            echo json_encode($ret);
            break;
        case 'deleteConfig':
            $alias = filter_var($_POST['alias'], FILTER_SANITIZE_ENCODED);
            $ret = $module->removeConfig($alias);
            echo json_encode($ret);
            break;

        case "saveRecord":
            $fields = $_POST["fields"];
            $result = $module->saveData($fields);
            echo json_encode($result);
            break;
        default:
            $module->emError('Error in ajax handler');
    }

}

