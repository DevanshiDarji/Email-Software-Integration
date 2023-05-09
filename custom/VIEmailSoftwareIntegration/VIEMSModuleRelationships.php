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