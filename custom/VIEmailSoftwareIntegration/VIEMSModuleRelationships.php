<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
require_once("modules/AOW_WorkFlow/aow_utils.php");
class VIEMSModuleRelationships{
	public function __construct(){
		$this->getModuleRelationships();
	}

    //get Related Module
	public function getModuleRelationships(){
        if (isset($_REQUEST['aow_module']) && !empty($_REQUEST['aow_module'])) {
            $module = array($_REQUEST['aow_module'] => translate($_REQUEST['aow_module'])); //module
            $val = '';
            echo get_select_options_with_id($module, $val);
        }//end of if
        die;
    }//end of function
}//end of class
new VIEMSModuleRelationships();
?>