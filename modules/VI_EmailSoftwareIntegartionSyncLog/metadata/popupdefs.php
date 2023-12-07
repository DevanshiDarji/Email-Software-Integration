<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 

$module_name = 'VI_EmailSoftwareIntegartionSyncLog';
$object_name = 'VI_EmailSoftwareIntegartionSyncLog';
$_module_name = 'vi_emailsoftwareintegrationsynclog';
$popupMeta = array(
    'moduleMain' => $module_name,
    'varName' => $object_name,
    'orderBy' => $_module_name . '.name',
    'whereClauses' => array(
        'name' => $_module_name . '.name',
    ),
    'searchInputs' => array($_module_name . '_number', 'name', 'priority', 'status'),

);