<?php
 
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['VIEmailSoftwareIntegrationLicenseAddon'] = array(
    'table' => 'viemailsoftwareintegrationlicenseaddon',
    'audited' => true,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge' => true,
    'comment' => 'Show Subpanel records Count, Min, Max and Multiplication',
    'fields' => array(

    ),
);
if (!class_exists('VardefManager')) {
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('VIEmailSoftwareIntegrationLicenseAddon', 'VIEmailSoftwareIntegrationLicenseAddon', array('basic','assignable','security_groups'));
?>