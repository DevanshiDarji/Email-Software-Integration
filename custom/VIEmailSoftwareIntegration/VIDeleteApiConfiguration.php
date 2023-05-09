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
class VIDeleteApiConfiguration{
	public function __construct(){
    	$this->deleteApiConfigurationData();
    }//end of constructor
    
    public function deleteApiConfigurationData(){
        $deleteEMSData = array('deleted' => 1);
        if(isset($_POST['del_id'])){
            $delId = explode(',', $_POST['del_id']);
            
            foreach($delId as $id){
                //soft delete data
                $deleteData = "UPDATE vi_api_configuration 
                                SET deleted = '1'
                                WHERE id = '$id'";
                $deleteResult = $GLOBALS['db']->query($deleteData);

                $selectFieldsName = array("*");
                $whereCondition = array('id' => array('operator' => '=', 'value' => "'".$id."'"), 'deleted' => array('operator' => '=', 'value' => 1));
                $getAPIConfigData = getEMSData('vi_api_configuration', $selectFieldsName, $whereCondition, $orderBy=array());
                $getAPIConfigDataResult = $GLOBALS['db']->fetchOne($getAPIConfigData);
                
                if(!empty($getAPIConfigDataResult)){
                    $syncSoftware = $getAPIConfigDataResult['email_software'];

                    $where = array('email_software' => array('operator' => '=', 'value' => "'".$syncSoftware."'"), 'deleted' => array('operator' => '=', 'value' => 0));
                    $getModuleMappingConfigData = getEMSData('vi_module_mapping', $selectFieldsName, $where, $orderBy=array());
                    $getModuleMappingConfigDataResult = $GLOBALS['db']->query($getModuleMappingConfigData);
                    
                    while($getModuleMappingConfigDataRow = $GLOBALS['db']->fetchByAssoc($getModuleMappingConfigDataResult)){
                        if(!empty($getModuleMappingConfigDataRow)){
                            $moduleMappingId = $getModuleMappingConfigDataRow['module_mapping_id'];
                            $whereConditions = array('module_mapping_id' => "'".$moduleMappingId."'");
                            updateEMSData('vi_module_mapping', $deleteEMSData, $whereConditions);
                        }//end of if
                    }//end of while

                    $whereConditionData = array('sync_software' => array('operator' => '=', 'value' => "'".$syncSoftware."'"), 'deleted' => array('operator' => '=', 'value' => 0));
                    $getAutomaticSyncConfigData = getEMSData('vi_automatic_sync', $selectFieldsName, $whereConditionData, $orderBy=array());
                    $getAutomaticSyncConfigDataResult = $GLOBALS['db']->fetchOne($getAutomaticSyncConfigData);
                    
                    if(!empty($getAutomaticSyncConfigDataResult)){
                        $autoSyncId = $getAutomaticSyncConfigDataResult['vi_automatic_sync_id'];
                        $whereCondition = array('vi_automatic_sync_id' => "'".$autoSyncId."'");
                        updateEMSData('vi_automatic_sync', $deleteEMSData, $whereCondition);
                    }//end of if
                }//end of if
            }//end of foreach
        }//end of if
	}//end of method
}//end of class
new VIDeleteApiConfiguration();
?>