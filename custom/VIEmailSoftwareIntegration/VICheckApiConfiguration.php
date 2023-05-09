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
class VICheckApiConfiguration{
	public function __construct(){
    	$this->checkApiConfigurationData();
    }//end of constructor
    
    public function checkApiConfigurationData(){
        $selectedSoftware = $_POST['selectedSoftware'];
        if(isset($_POST['selectedSoftware'])){
            $selectData = "SELECT * FROM vi_api_configuration where email_software = '$selectedSoftware' and deleted = 0";
            $selectResult = $GLOBALS['db']->query($selectData);
            $selRecordFetchRow = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($selectData));
            if(!empty($selRecordFetchRow)){
                echo "1";
            }
        }
    }//end of method
}//end of class
new VICheckApiConfiguration();
?>