<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
class VIEmailSoftwareIntegrationLicenseAddon extends SugarBean
{
    var $module_dir = 'VIEmailSoftwareIntegrationLicenseAddon';
    var $object_name = 'VIEmailSoftwareIntegrationLicenseAddon';
    var $table_name = 'viemailsoftwareintegrationlicenseaddon';
    var $disable_var_defs = true;
    var $new_schema = true;
    var $disable_row_level_security = true;
    var $disable_custom_fields = true;
    var $relationship_fields = array();
    
    function bean_implements($interface){
        return false;
    }
}//end of class
?>