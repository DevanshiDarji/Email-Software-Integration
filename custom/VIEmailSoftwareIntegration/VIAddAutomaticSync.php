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
require_once("custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php");
class VIAddAutomaticSync{
    public function __construct(){
        $this->VIAddAutomaticSyncData();
    }

    public function VIAddAutomaticSyncData(){
        if(isset($_POST['val'])){
            parse_str($_POST['val'], $formData);
            $syncSoftware = $formData['sync_software'];
            $mappingModules = implode(",", $formData['mapping_modules']);
            if(isset($formData['sync_to_es'])){
                $syncTo = $formData['sync_to_es'];
            }else{
                $syncTo = 0;
            }
            if(isset($formData['auto_sync_ems'])){
                $autoSyncEMS = $formData['auto_sync_ems'];
            }else{
                $autoSyncEMS = 0;
            }

            if(isset($formData['sync_ems_to_suite'])){
                $syncEMSToSuite = $formData['sync_ems_to_suite'];
            }else{
                $syncEMSToSuite = 0;
            }

            $automaticSyncId = create_guid();
            $selectData = "SELECT * FROM vi_automatic_sync WHERE sync_software = '$syncSoftware' AND deleted = 0";
            $selectResult = $GLOBALS['db']->query($selectData);
            $selRecordFetchRow = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($selectData));   
            $recordId = $_POST['id'];
            if(empty($recordId)){
                if(empty($selRecordFetchRow)){
                    $insData = "INSERT INTO vi_automatic_sync(vi_automatic_sync_id,sync_software,sel_mapping_module_list,sync_to_es,auto_sync_ems, sync_ems_to_suite) 
                                        values('$automaticSyncId','$syncSoftware','$mappingModules','$syncTo',$autoSyncEMS, $syncEMSToSuite)";
                    $insResult = $GLOBALS['db']->query($insData);
                    if($insResult){
                        $result = array('code'=>1);
                    }
                }else{
                    $result = array('code'=>3);
                }
            }else{
                $updateField = "UPDATE vi_automatic_sync SET sync_software = '$syncSoftware',sel_mapping_module_list = '$mappingModules' ,sync_to_es = '$syncTo',auto_sync_ems = $autoSyncEMS, sync_ems_to_suite = $syncEMSToSuite WHERE vi_automatic_sync_id = '$recordId'";
                $updateFieldResult = $GLOBALS['db']->query($updateField);
                if($updateFieldResult){
                    $result = array('code'=>2);
                }
            }
            echo json_encode($result);
        }else{
            if(isset($_REQUEST['id'])){
                $recordId = $_REQUEST['id'];
                $status = $_REQUEST['status'];
                $emsToSuiteStatus = $_REQUEST['emsToSuiteStatus'];

                if($status != '' && $emsToSuiteStatus == ''){
                    $updateStatus = array('sync_to_es' => "'".$status."'");
                }else if($emsToSuiteStatus != '' && $status == ''){
                    $updateStatus = array('sync_ems_to_suite' => "'".$emsToSuiteStatus."'");
                }//end of else if
                $whereCondition = array('vi_automatic_sync_id' => "'".$recordId."'");
                updateEMSData('vi_automatic_sync', $updateStatus, $whereCondition);
            }//end of if
        }//end of else
    }//end of method
}//end of class
new VIAddAutomaticSync();
?>