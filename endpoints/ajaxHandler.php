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
            $config = filter_var(json_encode($_POST['config']), FILTER_SANITIZE_STRING);
            $ret = $module->saveConfig($alias, $_POST['config']);
            echo json_encode($ret);
            break;
        default:
            $module->emError('Error in ajax handler');
    }

}

