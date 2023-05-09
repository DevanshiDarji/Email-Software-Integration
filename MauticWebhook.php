<?php
if (!defined('sugarEntry')) {
     define('sugarEntry', true);
}
require_once("custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php");
require_once('include/entryPoint.php');
require_once("data/BeanFactory.php");
$body = @file_get_contents('php://input');

//get module mapping data
$selData = "SELECT * FROM vi_module_mapping WHERE email_software = 'Mautic' AND es_module = 'Contacts'";
$selDataResult = $GLOBALS['db']->query($selData);

while($selDataRow = $GLOBALS['db']->fetchByAssoc($selDataResult)){
	$mappingId = $selDataRow['module_mapping_id'];
	$suiteCRMModule = $selDataRow['suitecrm_module'];

	$selAutoSyncData = "SELECT * FROM vi_automatic_sync WHERE sync_software = 'Mautic' AND auto_sync_ems = 1";
	$selAutoSyncDataRow = $GLOBALS['db']->fetchOne($selAutoSyncData);
	$flag = 0;
	if(!empty($selAutoSyncDataRow)){
		$mappingModuleList = explode(',',$selAutoSyncDataRow['sel_mapping_module_list']);
		if(in_array($mappingId, $mappingModuleList)){
			$flag = 1;
		}//end of if
	}//end of if

	if($flag == 1){
		//get fields
		$selMoudleMapping  = "SELECT * FROM vi_integration_field_mapping WHERE module_mapping_id = '$mappingId' AND deleted = 0";
		$selMoudleMappingResult = $GLOBALS['db']->query($selMoudleMapping);
		
		$fieldsArray = array();
		while($selModuleMappingRow = $GLOBALS['db']->fetchByAssoc($selMoudleMappingResult)){
			$fieldsArray[$selModuleMappingRow['suitecrm_module_fields']] = $selModuleMappingRow['es_module_fields'];
		}//end of file
		
		if(!empty($fieldsArray)){
			$data = (array)json_decode($body);
			$addUpdateData = array();
			foreach ($data as $key => $value) {
				foreach((array)$value as $k => $v){
					if($key == 'mautic.lead_post_save_update' || $key == 'mautic.lead_post_save_new'){
						
						$fieldNameValueArray  = array();
						foreach ($v->contact->fields as $groupName => $allFields) {
							foreach ($allFields as $fieldName => $fieldData) {
								if(in_array($fieldName,$fieldsArray)){
									$suiteCRMFieldName = array_search($fieldName,$fieldsArray);
									$fieldNameValueArray[$suiteCRMFieldName] = $fieldData->value;
	 							}//end of if
							}//end of foreach
						}//end of foreach
						$mauticContactId = $v->contact->id; //mautic contact id

						$selContactData = "SELECT * FROM vi_contacts_es WHERE deleted = 0 AND ";
						if($suiteCRMModule == 'Contacts'){
							$selContactData .= "vi_es_contact_id = '$mauticContactId'";
						}else if($suiteCRMModule == 'Leads'){
							$selContactData .= "vi_es_lead_id = '$mauticContactId'";
						}//end of else if

						$selectContactDataRow = $GLOBALS['db']->fetchOne($selContactData);
						
						if(!empty($selectContactDataRow)){
							if($suiteCRMModule == 'Contacts'){
								$suiteCRMRecordId = $selectContactDataRow['vi_suitecrm_contact_id'];
							}else if($suiteCRMModule == 'Leads'){
								$suiteCRMRecordId = $selectContactDataRow['vi_suitecrm_lead_id'];
							}//end of else if

							$bean = BeanFactory::getBean($suiteCRMModule, $suiteCRMRecordId);

							if(!empty($bean) && $bean->deleted == 0){
								$actionType = 'Update';
								$contactBean = BeanFactory::getBean($suiteCRMModule,$suiteCRMRecordId);
							}else{
								$id = $selectContactDataRow['id'];

								$updateContactData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$id'";
                    			$updateContactDataResult = $GLOBALS['db']->query($updateContactData);

								$actionType = 'Insert';
								$contactBean = BeanFactory::newBean($suiteCRMModule);
							}//end of else
						}else{
							$actionType = 'Insert';
							$contactBean = BeanFactory::newBean($suiteCRMModule);
						}//end of else

						foreach ($fieldNameValueArray as $fieldName => $fieldValue) {
							$contactBean->$fieldName = $fieldValue;
						}//end of foreach

						if($contactBean->save()){
							$tableName = "vi_contacts_es";
							
							if($actionType == 'Insert'){
								$contactsId = create_guid(); 
								if($suiteCRMModule == 'Contacts'){
									$addData = array('id' => $contactsId,'vi_suitecrm_contact_id' => $contactBean->id,'vi_es_contact_id' => $mauticContactId,'vi_suitecrm_lead_id' => '','vi_es_name' => 'Mautic','vi_es_list_id' => '','vi_suitecrm_module' => $suiteCRMModule,'vi_es_lead_id' => '', 'deleted' => 0);
								}else if($suiteCRMModule == 'Leads'){
									$addData = array('id' => $contactsId,'vi_suitecrm_contact_id' => '', 'vi_es_contact_id' => '','vi_suitecrm_lead_id' => $contactBean->id,'vi_es_name' => 'Mautic','vi_es_list_id' => '','vi_suitecrm_module' => $suiteCRMModule,'vi_es_lead_id' => $mauticContactId, 'deleted' => 0);
								}
                                		insertESRecord($tableName,$addData);
							}//end of if
							addRecordInSyncLog($contactBean->id,$mauticContactId,$actionType,$suiteCRMModule); //insert record in sync log
						}//end of if

					}else if($key == 'mautic.lead_post_delete'){
						$deleteRecordId = $v->id; //mautic contact id

						$selContactData = "SELECT * FROM vi_contacts_es WHERE deleted = 0 AND ";
						if($suiteCRMModule == 'Contacts'){
							$selContactData .= "vi_es_contact_id = '$deleteRecordId'";
						}else if($suiteCRMModule == 'Leads'){
							$selContactData .= "vi_es_lead_id = '$deleteRecordId'";
						}//end of else if

						$selectContactDataRow = $GLOBALS['db']->fetchOne($selContactData);

						if(!empty($selectContactDataRow)){
							if($suiteCRMModule == 'Contacts'){
								$suiteCRMRecordId = $selectContactDataRow['vi_suitecrm_contact_id'];
							}else if($suiteCRMModule == 'Leads'){
								$suiteCRMRecordId = $selectContactDataRow['vi_suitecrm_lead_id'];
							}
							
						    $contactBean = BeanFactory::getBean($suiteCRMModule,$suiteCRMRecordId);
						    $contactBean->deleted = 1;
						    if($contactBean->save()){
						    	$id = $selectContactDataRow['id'];
						    	$updateContactData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$id'";
                    			$updateContactDataResult = $GLOBALS['db']->query($updateContactData);
						    	addRecordInSyncLog($suiteCRMRecordId,$deleteRecordId,'Delete',$suiteCRMModule); //insert record in sync log
						    }//end of if
						}
					}//end of else if
				}//end of foreach
			}//end of foreach
		}//end of if		
	}//end of if
}//end of while

function addRecordInSyncLog($suiteCRMRecordId,$mauticContactId,$actionType, $suiteCRMModule){
	global $app_list_strings;
	$eslBean = BeanFactory::newBean('VI_EmailSoftwareIntegartionSyncLog');
	$eslBean->name = "Contacts";
	$eslBean->to_module = $app_list_strings['moduleList'][$suiteCRMModule];
	$eslBean->email_software = "Mautic";
	$eslBean->sync_type = "MA2SC";
	$eslBean->action_type = $actionType; 
	$eslBean->status = "Successfull";
	$eslBean->from_record = $suiteCRMRecordId;
	$eslBean->viem_to_record = $mauticContactId;
	$eslBean->save();
}//end of function