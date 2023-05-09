<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * This file is part of package Email Software Integration.
 * 
 * Author : Variance InfoTech PVT LTD (http://www.varianceinfotech.com)
 * All rights (c) 2022 by Variance InfoTech PVT LTD
 *
 * This Version of Email Software Integration is licensed software and may only be used in 
 * alignment with the License Agreement received with this Software.
 * This Software is copyrighted and may not be further distributed without
 * written consent of Variance InfoTech PVT LTD
 * 
 * You can contact via email at info@varianceinfotech.com
 * 
 ********************************************************************************/

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('VI_EmailSoftwareIntegartionSyncLog', 'list', true)){
    $module_menu[]=array('index.php?module=VI_EmailSoftwareIntegartionSyncLog&action=index&return_module=VI_EmailSoftwareIntegartionSyncLog&return_action=DetailView', $mod_strings['LNK_LIST'],'List', 'VI_EmailSoftwareIntegartionSyncLog');
}
if(ACLController::checkAccess('VI_EmailSoftwareIntegartionSyncLog', 'import', true)){
    $module_menu[]=array('index.php?module=Import&action=Step1&import_module=VI_EmailSoftwareIntegartionSyncLog&return_module=VI_EmailSoftwareIntegartionSyncLog&return_action=index', $app_strings['LBL_IMPORT'], 'Import', 'VI_EmailSoftwareIntegartionSyncLog');
}