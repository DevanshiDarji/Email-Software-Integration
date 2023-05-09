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
class VICheckESAPIConfiguration{
	public function __construct(){
    	$this->checkESAPIConfiguration();
    }
     
    public function checkESAPIConfiguration(){
        $moduleMappingSoftware = $_REQUEST['moduleMappingSoftware'];

        $selectData = "SELECT * FROM vi_api_configuration WHERE email_software = '$moduleMappingSoftware' AND deleted = 0";
        $selectDataResult = $GLOBALS['db']->fetchOne($selectData);
        
        if(!empty($selectDataResult)){
            echo 1;
        }else{
            echo 0;
        }
	}//end of method
}//end of class
new VICheckESAPIConfiguration();
?>