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
class VIAddModuleMapping{
	public function __construct(){
    	$this->addModuleMappingData();
    }

    public function addModuleMappingData(){
        parse_str($_POST['val'], $formData);
        $title = $formData['title'];
        $suitecrmModule = $formData['suitecrm_module'];
        $targetListSubpanelModule = isset($formData['suitecrm_target_list_module'])?$formData['suitecrm_target_list_module']:'';
        $status = $formData['status'];
        $batchRecord = $formData['batch_record'];
        if($batchRecord == ''){
            $batchRecord = 0;
        }
        $moduleMappingSoftware = $formData['module_mapping_software'];
        if($moduleMappingSoftware == "SendGrid"){
            $esModule = $formData['sendgrid_module'];
        }elseif($moduleMappingSoftware == "Mautic"){
            $esModule = $formData['mautic_module'];
        }elseif($moduleMappingSoftware == "ConstantContact"){
            $esModule = $formData['constant_contact_module'];
        }elseif($moduleMappingSoftware == "ActiveCampaigns"){
            $esModule = $formData['active_campaigns_module'];
        }elseif($moduleMappingSoftware == "SendInBlue"){
            $esModule = $formData['sendinblue_module'];
        }
        if(isset($formData['batch_management_status'])){
            $batchManagementStatus = $formData['batch_management_status'];
        }else{
            $batchManagementStatus = 0;
        }
        $recordID = $_POST['id'];

        $conditionalOperator = $formData['conditionalOperator'];
        $dateCreatedModified = date("Y-m-d H:i:s");
        $moduleMappingIds = create_guid();
        if($recordID == ''){
            $moduleMappingId = $moduleMappingIds;     
        }else{
            $moduleMappingId = $recordID;
        }//end of else

        //All Condition block field value
        $allConditionFieldName = isset($formData['aowAllConditionsField'])?$formData['aowAllConditionsField']:'';
        $allContionOperatorName = isset($formData['aowAllConditionsOperator'])?$formData['aowAllConditionsOperator']:'';
        $allConditionValueType = isset($formData['aowAllConditionsValueType'])?$formData['aowAllConditionsValueType']:'';
        $allConditionFieldValue = isset($formData['aowAllConditionsValue'])?$formData['aowAllConditionsValue']:'';

        //Any Condition block field value
        $anyConditionFieldName = isset($formData['aowAnyConditionsField'])?$formData['aowAnyConditionsField']:'';
        $anyConditionOperatorName = isset($formData['aowAnyConditionsOperator'])?$formData['aowAnyConditionsOperator']:'';
        $anyConditionValueType = isset($formData['aowAnyConditionsValueType'])?$formData['aowAnyConditionsValueType']:'';
        $anyConditionFieldValue = isset($formData['aowAnyConditionsValue'])?$formData['aowAnyConditionsValue']:'';

        if($recordID == ""){
            $insData = "INSERT INTO vi_module_mapping(module_mapping_id,title,suitecrm_module,target_list_subpanel_module,es_module,email_software,status,batch_record,batch_management_status,conditional_operator) 
                            values('$moduleMappingId','$title','$suitecrmModule','$targetListSubpanelModule','$esModule','$moduleMappingSoftware','$status',$batchRecord,$batchManagementStatus,'$conditionalOperator')";
            $insResult = $GLOBALS['db']->query($insData);
            //insert record creator Field Mapping data
            $row = $formData['row'];
            for($i=1;$i<=$row;$i++){
                $sourceString = "suitecrm_fields".$i;
                if($moduleMappingSoftware == "SendGrid"){
                    $targetString = "sendgrid_fields".$i;
                }elseif($moduleMappingSoftware == "Mautic"){
                    $targetString = "mautic_fields".$i;
                }elseif($moduleMappingSoftware == "ConstantContact"){
                    $targetString = "constant_contact_fields".$i;
                }elseif($moduleMappingSoftware == "ActiveCampaigns"){
                    $targetString = "active_campaigns_fields".$i;
                }elseif($moduleMappingSoftware == "SendInBlue"){
                    $targetString = "sendinblue_fields".$i;
                }
                
                if(array_key_exists($sourceString, $formData)){
                    $fieldMappingId = create_guid();    
                    if($formData[$sourceString] != "" && $formData[$targetString] != ""){
                        $insModuleFields = "INSERT INTO vi_integration_field_mapping(module_mapping_id,field_mapping_id,suitecrm_module_fields,es_module_fields,deleted)values('$moduleMappingId','$fieldMappingId','$formData[$sourceString]','$formData[$targetString]',0)";
                        $GLOBALS['db']->query($insModuleFields);
                    }
                }
            }//end of for loop

            $rowContacts = $formData['row_contacts'];
            for($i=1;$i<=$rowContacts;$i++){
                if($formData['row_contacts'] > 0){
                    $sourceStringForContacts = "suitecrm_contacts_fields".$i;
                    $targetStringForContacts = "";
                    if(!empty($formData['sendgrid_module'])){
                        $targetStringForContacts = "sendgrid_contacts_fields".$i;
                    }elseif (!empty($formData['mautic_module'])) {
                        $targetStringForContacts = "mautic_contacts_fields".$i;
                    }elseif (!empty($formData['constant_contact_module'])) {
                        $targetStringForContacts = "constant_contact_contacts_fields".$i;
                    }elseif (!empty($formData['active_campaigns_module'])) {
                        $targetStringForContacts = "active_campaigns_contacts_fields".$i;
                    }elseif (!empty($formData['sendinblue_module'])) {
                        $targetStringForContacts = "sendinblue_contacts_fields".$i;
                    }                   
                    if(array_key_exists($sourceStringForContacts, $formData)){
                        $contactsFieldMappingId = create_guid();    
                        if($formData[$sourceStringForContacts] != "" && $formData[$targetStringForContacts] != ""){
                            $insContactsModuleFields = "INSERT INTO vi_integration_contacts_field_mapping(module_mapping_id,contacts_field_mapping_id,suitecrm_contacts_module_fields,sendgrid_contacts_module_fields,deleted)values('$moduleMappingId','$contactsFieldMappingId','$formData[$sourceStringForContacts]','$formData[$targetStringForContacts]',0)";
                            $GLOBALS['db']->query($insContactsModuleFields);
                        }
                    }  
                }
            }
            $selTemplateData = "SELECT id FROM vi_email_fields WHERE module='$esModule' and email_software='$moduleMappingSoftware'";
            $selTemplateRow=$GLOBALS['db']->fetchOne($selTemplateData);
            $id = $selTemplateRow['id'];
            $updateData = "UPDATE vi_email_fields SET module_map_id='$moduleMappingId' WHERE id = '$id'";
            $updateResult = $GLOBALS['db']->query($updateData);
        }else{
            $updateData = "UPDATE vi_module_mapping 
                            SET title = '$title', suitecrm_module = '$suitecrmModule', target_list_subpanel_module = '$targetListSubpanelModule', es_module = '$esModule',status = '$status',email_software = '$moduleMappingSoftware',
                                batch_record = $batchRecord,
                                batch_management_status = $batchManagementStatus,
                                conditional_operator = '$conditionalOperator'
                            WHERE module_mapping_id = '$recordID'";
            $updateResult = $GLOBALS['db']->query($updateData);
            //update record creator Feild Mapping data
            $rowWhileUpdate = $formData['row'];
            $deleteModuleFields = "DELETE FROM vi_integration_field_mapping WHERE module_mapping_id = '$recordID'";
            $deleteResultModuleFields = $GLOBALS['db']->query($deleteModuleFields);
            for($i=1;$i<=$rowWhileUpdate;$i++){
                $sourceStringUpdate = "suitecrm_fields".$i;
                if($moduleMappingSoftware == "SendGrid"){
                    $targetStringUpdate = "sendgrid_fields".$i;    
                }else if($moduleMappingSoftware == "Mautic"){
                    $targetStringUpdate = "mautic_fields".$i;
                }else if($moduleMappingSoftware == "ConstantContact"){
                    $targetStringUpdate = "constant_contact_fields".$i;
                }else if($moduleMappingSoftware == "ActiveCampaigns"){
                    $targetStringUpdate = "active_campaigns_fields".$i;
                }else if($moduleMappingSoftware == "SendInBlue"){
                    $targetStringUpdate = "sendinblue_fields".$i;
                }
                if(array_key_exists($sourceStringUpdate, $formData)){                               
                    if($formData[$sourceStringUpdate] != "" && $formData[$targetStringUpdate] != ""){   
                        $fieldMappingId = create_guid();
                        $updateModuleFields = "INSERT INTO vi_integration_field_mapping(module_mapping_id,field_mapping_id,suitecrm_module_fields,es_module_fields,deleted)values('$recordID','$fieldMappingId','$formData[$sourceStringUpdate]','$formData[$targetStringUpdate]',0)";
                        $GLOBALS['db']->query($updateModuleFields);
                    }
                }
            }//end of for loop 

            $rowContacts = $formData['row_contacts'];
            $selData = "SELECT * FROM vi_integration_contacts_field_mapping WHERE module_mapping_id = '$recordID' and deleted = 0";
            $res = $GLOBALS['db']->query($selData);
            $selectResultData = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($selData));
            if(!empty($selectResultData)){
                $deleteModuleFields = "DELETE FROM vi_integration_contacts_field_mapping WHERE module_mapping_id = '$recordID'";
                $deleteResultModuleFields = $GLOBALS['db']->query($deleteModuleFields);
            }

            for($i=1;$i<=$rowContacts;$i++){
                if($formData['row_contacts'] > 0){
                    $sourceStringForContactsUpdate = "suitecrm_contacts_fields".$i;
                    $targetStringForContactsUpdate = "";
                    if($formData['module_mapping_software'] == "ConstantContact"){
                        $targetStringForContactsUpdate = "constant_contact_contacts_fields".$i;
                    }elseif ($formData['module_mapping_software'] == "SendGrid") {
                        $targetStringForContactsUpdate = "sendgrid_contacts_fields".$i;
                    }elseif ($formData['module_mapping_software'] == "Mautic") {
                        $targetStringForContactsUpdate = "mautic_contacts_fields".$i;
                    }elseif ($formData['module_mapping_software'] == "ActiveCampaigns") {
                        $targetStringForContactsUpdate = "active_campaigns_contacts_fields".$i;
                    }elseif ($formData['module_mapping_software'] == "SendInBlue") {
                        $targetStringForContactsUpdate = "sendinblue_contacts_fields".$i;
                    }

                    if(array_key_exists($sourceStringForContactsUpdate, $formData)){
                        $contactsFieldMappingUpdateId = create_guid();  
                        if($formData[$sourceStringForContactsUpdate] != "" && $formData[$targetStringForContactsUpdate] != ""){
                            $insContactsModuleFieldsUpdate = "";
                            $insContactsModuleFieldsUpdate = "INSERT INTO vi_integration_contacts_field_mapping(module_mapping_id,contacts_field_mapping_id,suitecrm_contacts_module_fields,sendgrid_contacts_module_fields,deleted)values('$recordID','$contactsFieldMappingUpdateId','$formData[$sourceStringForContactsUpdate]','$formData[$targetStringForContactsUpdate]',0)";
                            $GLOBALS['db']->query($insContactsModuleFieldsUpdate);
                        }
                    }  
                }
            }
            $selTemplateData = "SELECT id FROM vi_email_fields WHERE module='$esModule' and email_software='$moduleMappingSoftware'";
            $selTemplateRow=$GLOBALS['db']->fetchOne($selTemplateData);
            $id = $selTemplateRow['id'];
     
            $updateData = "UPDATE vi_email_fields SET module_map_id='$recordID' WHERE id = '$id'";
            $updateResult = $GLOBALS['db']->query($updateData);

            $whereCondition = array('module_mapping_id' => "'".$moduleMappingId."'");
            //Delete Email Software Integration Conditions Data
            $deleteEMSDataResult = deleteEMSData('vi_ems_conditions', $whereCondition);
        }//end of else

        //Update Email Software Integration All Condition Data
        if(isset($formData['aowAllConditionsDeleted']) && !empty($formData['aowAllConditionsDeleted'])){
            $delId = $formData['aowAllConditionsDeleted'];
        }else{
            $delId = '';
        }//end of else

        //All Condition module_path
        $allConditionId = array();
        if(isset($formData['aowAllConditionsModulePath']) && !empty($formData['aowAllConditionsModulePath'])){
            foreach($formData['aowAllConditionsModulePath'] as $keys => $values) {
                foreach ($values as $key => $value) {
                    $id = create_guid();
                    $insConditionModuleData = array('id' => $id,
                                             'module_path' => $value,
                                             'module_mapping_id' => $moduleMappingId,
                                             'condition_type' => "All",
                                             'date_entered' => $dateCreatedModified);
                    $insConditionModuleDataResult = insertESRecord('vi_ems_conditions', $insConditionModuleData);
                    $allConditionId[] = $id;  
                }//end of if 
            }//end of foreach
        }//end of if
        
        $updateEMSConditionsData = updateEMSConditionsData($allConditionFieldName, $delId, $allConditionId, 'vi_ems_conditions', $allContionOperatorName, $allConditionValueType, $allConditionFieldValue, $suitecrmModule);

        //Update Email Software Integration Any Condition Data
        if(isset($formData['aowAnyConditionsDeleted']) && !empty($formData['aowAnyConditionsDeleted'])){
            $anyDelId = $formData['aowAnyConditionsDeleted'];
        }else{
            $anyDelId = '';
        }//end of else

        //Any Condition module_path
        $anyConditionId = array();
        if(isset($formData['aowAnyConditionsModulePath']) && !empty($formData['aowAnyConditionsModulePath'])){
            foreach($formData['aowAnyConditionsModulePath'] as $keys => $values) {
                foreach ($values as $key => $value) {
                    $id = create_guid();
                    $insConditionModuleData = array('id' => $id,
                                             'module_path' => $value,
                                             'module_mapping_id' => $moduleMappingId,
                                             'condition_type' => "Any",
                                             'date_entered' => $dateCreatedModified);
                    $insConditionModuleDataResult = insertESRecord('vi_ems_conditions', $insConditionModuleData);
                    $anyConditionId[] = $id;  
                }//end of if 
            }//end of foreach
        }//end of if

        $updateEMSConditionsData = updateEMSConditionsData($anyConditionFieldName, $anyDelId, $anyConditionId, 'vi_ems_conditions', $anyConditionOperatorName, $anyConditionValueType, $anyConditionFieldValue, $suitecrmModule);
	}//end of method
}//end of class
new VIAddModuleMapping();
?>