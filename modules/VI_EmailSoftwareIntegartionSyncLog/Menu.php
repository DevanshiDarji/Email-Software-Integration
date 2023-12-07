<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('VI_EmailSoftwareIntegartionSyncLog', 'list', true)){
    $module_menu[]=array('index.php?module=VI_EmailSoftwareIntegartionSyncLog&action=index&return_module=VI_EmailSoftwareIntegartionSyncLog&return_action=DetailView', $mod_strings['LNK_LIST'],'List', 'VI_EmailSoftwareIntegartionSyncLog');
}
if(ACLController::checkAccess('VI_EmailSoftwareIntegartionSyncLog', 'import', true)){
    $module_menu[]=array('index.php?module=Import&action=Step1&import_module=VI_EmailSoftwareIntegartionSyncLog&return_module=VI_EmailSoftwareIntegartionSyncLog&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'VI_EmailSoftwareIntegartionSyncLog');
}