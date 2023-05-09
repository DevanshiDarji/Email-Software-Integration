<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * This file is part of package Integration.
 * 
 * Author : Variance InfoTech PVT LTD (http://www.varianceinfotech.com)
 * All rights (c) 2022 by Variance InfoTech PVT LTD
 *
 * This Version of Integration is licensed software and may only be used in 
 * alignment with the License Agreement received with this Software.
 * This Software is copyrighted and may not be further distributed without
 * written consent of Variance InfoTech PVT LTD
 * 
 * You can contact via email at info@varianceinfotech.com
 * 
 ********************************************************************************/
class VICheckSelectedModules{
	public function __construct(){
    	$this->vicheckselectedmodules();
    }//end of constructor
    
    public function vicheckselectedmodules(){
		$moduleMappingSoftware = $_POST['moduleMappingSoftware'];
		$esModule = $_REQUEST['esModule'];
		$suitecrmModule = $_REQUEST['suitecrmModule'];

		if(isset($_POST['recordId']) && $_POST['recordId'] != ""){
			$reocrdID = $_POST['recordId'];
			$selData = "SELECT * FROM vi_module_mapping WHERE module_mapping_id != '$reocrdID' and email_software = '$moduleMappingSoftware' AND deleted = 0 AND es_module = '$esModule' AND suitecrm_module = '$suitecrmModule'";
		}else{
    		$selData = "SELECT * FROM vi_module_mapping WHERE email_software = '$moduleMappingSoftware' AND deleted = 0 AND es_module = '$esModule' AND suitecrm_module = '$suitecrmModule'";
    	}
		$selRow = $GLOBALS['db']->fetchOne($selData);
		if(!empty($selRow)){
			echo 'not valid';
		}
	}//end of the method
}//end of the class
new VICheckSelectedModules();
?>