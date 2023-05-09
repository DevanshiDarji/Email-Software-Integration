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
require_once("VIEmailMarketingFunction.php");
class VIDeleteModuleMapping{
	public function __construct(){
    	$this->deleteModuleMappingData();
    }//end of constructor
    
    public function deleteModuleMappingData(){
        $deleteEMSModuleMappingData = array('deleted' => 1);
        if(isset($_POST['del_id'])){
            $delId = explode(',',$_POST['del_id']);
            foreach($delId as $id){
                //soft delete data
                $deleteData = "UPDATE vi_module_mapping 
                                SET deleted = 1
                                WHERE module_mapping_id = '$id'";
                $deleteResult = $GLOBALS['db']->query($deleteData);

                $deleteDataMapping = "UPDATE vi_integration_field_mapping 
                                SET deleted = 1
                                WHERE module_mapping_id = '$id'";
                $deleteResultMapping = $GLOBALS['db']->query($deleteDataMapping);

                $whereCondition = array('module_mapping_id' => "'".$id."'");
                updateEMSData('vi_ems_conditions', $deleteEMSModuleMappingData, $whereCondition);
            }//end of foreach
        }//end of if
	}//end of method
}//end of class
new VIDeleteModuleMapping();
?>