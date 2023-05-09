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

$module_name = 'VI_EmailSoftwareIntegartionSyncLog';
$metafiles[$module_name] = array(
    'detailviewdefs' => 'modules/' . $module_name . '/metadata/detailviewdefs.php',
    'editviewdefs' => 'modules/' . $module_name . '/metadata/editviewdefs.php',
    'listviewdefs' => 'modules/' . $module_name . '/metadata/listviewdefs.php',
    'searchdefs' => 'modules/' . $module_name . '/metadata/searchdefs.php',
    'popupdefs' => 'modules/' . $module_name . '/metadata/popupdefs.php',
    'searchfields' => 'modules/' . $module_name . '/metadata/SearchFields.php',
);