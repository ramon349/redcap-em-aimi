<?php
namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$startTS    = microtime(true);

$finished   = $module->syncMaster();

if(!empty($finished)){
    echo "Processed records : " . implode(", ", $finished);
}

$module->emLog("syncMaster() cron run time : " . microtime(true) - $startTS );