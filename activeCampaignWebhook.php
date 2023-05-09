<?php
if (!defined('sugarEntry')) {
     define('sugarEntry', true);
}
require_once("custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php");
require_once('include/entryPoint.php');
require_once("data/BeanFactory.php");
$body = @file_get_contents('php://input');

global $timedate;
//Get Module Mapping Data
$getFieldsNames = array("*");
$whereCondition = array('email_software' => array('operator' => '=', 'value' => "'ActiveCampaigns'"), 'deleted' => array('operator' => '=', 'value' => 0));
$getModuleMappingACData = getEMSData('vi_module_mapping', $getFieldsNames, $whereCondition, $orderBy=array());
$getModuleMappingACDataResult = $GLOBALS['db']->query($getModuleMappingACData);

while($getModuleMappingACDataRow = $GLOBALS['db']->fetchByAssoc($getModuleMappingACDataResult)){
	$mappingId = $getModuleMappingACDataRow['module_mapping_id'];
	$suiteCRMModule = $getModuleMappingACDataRow['suitecrm_module'];
	$esModule = $getModuleMappingACDataRow['es_module'];

	$whereCondition = array('sync_software' => array('operator' => '=', 'value' => "'ActiveCampaigns'"), 'auto_sync_ems' => array('operator' => '=', 'value' => 1), 'deleted' => array('operator' => '=', 'value' => 0));
	$getAutoSyncData = getEMSData('vi_automatic_sync', $getFieldsNames, $whereCondition, $orderBy=array());
	$getAutoSyncDataRow = $GLOBALS['db']->fetchOne($getAutoSyncData);
	$flag = 0;

	if(!empty($getAutoSyncDataRow)){
		$mappingModuleList = explode(',', $getAutoSyncDataRow['sel_mapping_module_list']);
		if(in_array($mappingId, $mappingModuleList)){
			$flag = 1;
		}//end of if
	}//end of if

	if($flag == 1){
		//Get Fields
		$where = array('module_mapping_id' => array('operator' => '=', 'value' => "'".$mappingId."'"), 'deleted' => array('operator' => '=', 'value' => 0));
		$getModuleFieldMapping = getEMSData('vi_integration_field_mapping', $getFieldsNames, $where, $orderBy=array());
		$getModuleFieldMappingResult = $GLOBALS['db']->query($getModuleFieldMapping);
		
		$suiteCRMFields = $suiteCRMContactsFields = array();
		while($getModuleFieldMappingRow = $GLOBALS['db']->fetchByAssoc($getModuleFieldMappingResult)){
			if($suiteCRMModule == "Contacts" || $suiteCRMModule == 'Leads'){
				$result = preg_replace('/\B([A-Z])/', '_$1', $getModuleFieldMappingRow['es_module_fields']);
				$suiteCRMFields[$getModuleFieldMappingRow['suitecrm_module_fields']] = $result;
				if (preg_match('/_/', $result)) {
					$suiteCRMFields = array_map('strtolower', $suiteCRMFields);
				}//end of if
			}else{
				$suiteCRMFields[$getModuleFieldMappingRow['suitecrm_module_fields']] = $getModuleFieldMappingRow['es_module_fields'];
			}//end of else
		}//end of while

		if(!empty($suiteCRMFields)){
			parse_str($body, $formData);
			if($esModule == 'Contacts' || $esModule == 'Leads'){
				if(isset($formData['type']) && $formData['type'] == 'subscribe' || $formData['type'] == 'update'){
					if(isset($formData['contact']) && !empty($formData['contact'])){
						$activeCampaignContactId = $formData['contact']['id']; //Active Campaigns Contact Id
					
						$fieldNameValueArray  = array();
						foreach ($formData['contact'] as $fieldName => $fieldValue) {
							if(in_array($fieldName, $suiteCRMFields)){
								$suiteCRMFieldName = array_search($fieldName, $suiteCRMFields);
								$fieldNameValueArray[$suiteCRMFieldName] = $fieldValue;
							}//end of if
						}//end of foreach

						if(isset($formData['contact']['fields']) && !empty($formData['contact']['fields'])){
							$contactCustomFields = array();
							foreach ($formData['contact']['fields'] as $fieldId => $customFieldValue) {
								$customFieldsData = syncESData("/api/3/fields/".$fieldId, "GET", "ActiveCampaigns", $data=array());
								$fieldList = (array)json_decode($customFieldsData);

			                        	if(!empty($fieldList)){
			                            	if(!empty($fieldList['field'])){
		                                   	$contactCustomFields[$fieldList['field']->id] = $fieldList['field']->title;
			                            	}//end of if
			                        	}//end of if
							}//end of foreach

							foreach ($contactCustomFields as $id => $fieldsName) {
								if(in_array($fieldsName, $suiteCRMFields)){
									$suiteCRMFieldName = array_search($fieldsName, $suiteCRMFields);
									$fieldNameValueArray[$suiteCRMFieldName] = $formData['contact']['fields'][$id];
								}//end of if
							}//end of foreach
						}//end of if

						$selContactData = "SELECT * FROM vi_contacts_es WHERE deleted = 0 AND vi_es_name = 'ActiveCampaigns' AND ";
						if($suiteCRMModule == 'Contacts'){
							$selContactData .= "vi_es_contact_id = '$activeCampaignContactId'";
						}else if($suiteCRMModule == 'Leads'){
							$selContactData .= "vi_es_lead_id = '$activeCampaignContactId'";
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
								$contactBean = BeanFactory::getBean($suiteCRMModule, $suiteCRMRecordId);
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
							if($actionType == 'Insert'){
								$contactsId = create_guid(); 
								$suiteContactId = $suiteLeadId = $esContactId = $esLeadId = '';
								if($suiteCRMModule == 'Contacts'){
									$suiteContactId = $contactBean->id;
									$esContactId = $activeCampaignContactId;
								}else if($suiteCRMModule == 'Leads'){
									$suiteLeadId = $contactBean->id;
									$esLeadId = $activeCampaignContactId;
								}//end of else if
								$addData = array('id' => $contactsId,'vi_suitecrm_contact_id' => $suiteContactId, 'vi_es_contact_id' => $esContactId, 'vi_suitecrm_lead_id' => $suiteLeadId, 'vi_es_name' => 'ActiveCampaigns', 'vi_es_list_id' => '', 'vi_suitecrm_module' => $suiteCRMModule, 'vi_es_lead_id' => $esLeadId, 'deleted' => 0);

	                           		insertESRecord('vi_contacts_es', $addData);
							}//end of if
							addRecordInSyncLog($contactBean->id, $activeCampaignContactId, $actionType, $suiteCRMModule, $esModule); //insert record in sync log
						}//end of if
					}//end of if
				}//end of if
			}else if($esModule == 'Contacts_List'){
				if(isset($formData['type']) && $formData['type'] == 'list_add'){
					if(isset($formData['list']) && !empty($formData['list'])){
						$activeCampaignListId = $formData['list']['id']; //Active Campaigns List Id

						$fieldNameValueArray  = array();
						foreach ($formData['list'] as $fieldName => $fieldValue) {
							if(in_array($fieldName, $suiteCRMFields)){
								$suiteCRMFieldName = array_search($fieldName, $suiteCRMFields);
								$fieldNameValueArray[$suiteCRMFieldName] = $fieldValue;
							}//end of if
						}//end of foreach

						$selListData = "SELECT * FROM vi_segments_es WHERE vi_es_segments_id = '$activeCampaignListId' AND vi_es_name = 'ActiveCampaigns' AND deleted = 0";
						$selectListDataRow = $GLOBALS['db']->fetchOne($selListData);

						if(!empty($selectListDataRow)){
							$suiteCRMRecordId = $selectListDataRow['vi_suitecrm_segments_id'];
							$bean = BeanFactory::getBean($suiteCRMModule, $suiteCRMRecordId);

							if(!empty($bean) && $bean->deleted == 0){
								$actionType = 'Update';
								$listBean = BeanFactory::getBean($suiteCRMModule, $suiteCRMRecordId);
							}else{
								$id = $selectListDataRow['id'];

								$updateListData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$id'";
	               				$updateListDataResult = $GLOBALS['db']->query($updateListData);

								$actionType = 'Insert';
								$listBean = BeanFactory::newBean($suiteCRMModule);
							}//end of else
						}else{
							$actionType = 'Insert';
							$listBean = BeanFactory::newBean($suiteCRMModule);
						}//end of else

						foreach ($fieldNameValueArray as $fieldName => $fieldValue) {
							$listBean->$fieldName = $fieldValue;
						}//end of foreach

						if($listBean->save()){
							if($actionType == 'Insert'){
								$listId = create_guid(); 
								$suiteListId = $listBean->id;
								
								$addData = array('id' => $listId,'vi_suitecrm_segments_id' => $suiteListId, 'vi_es_segments_id' => $activeCampaignListId, 'vi_es_name' => 'ActiveCampaigns', 'deleted' => 0);

	                           		insertESRecord('vi_segments_es', $addData);
							}//end of if
							addRecordInSyncLog($listBean->id, $activeCampaignListId, $actionType, $suiteCRMModule, $esModule); //insert record in sync log
						}//end of if
					}//end of if
				}//end of if
			}else if($esModule == 'Organizations'){
				if(isset($formData['type']) && $formData['type'] == 'account_add' || $formData['type'] == 'account_update'){
					if(isset($formData['account']) && !empty($formData['account'])){
						$activeCampaignAccountId = $formData['account']['id']; //Active Campaigns Account Id

						$fieldNameValueArray  = array();
						foreach ($formData['account'] as $fieldName => $fieldValue) {
							if(in_array($fieldName, $suiteCRMFields)){
								$suiteCRMFieldName = array_search($fieldName, $suiteCRMFields);
								$fieldNameValueArray[$suiteCRMFieldName] = $fieldValue;
							}//end of if
						}//end of foreach

						if(isset($formData['account']['fields']) && !empty($formData['account']['fields'])){
							$accountCustomFields = array();
							foreach ($formData['account']['fields'] as $index => $customFieldData) {
								$fieldName = $customFieldData['key'];
								if(isset($customFieldData['value']) && !empty($customFieldData['value'])){
									$fieldValue = $customFieldData['value'];
									if(in_array($fieldName, $suiteCRMFields)){
										$suiteCRMFieldName = array_search($fieldName, $suiteCRMFields);
										$fieldNameValueArray[$suiteCRMFieldName] = $fieldValue;
									}//end of if
								} else {
									if(in_array($fieldName, $suiteCRMFields)){
										$customFieldsData = syncESData("/api/3/accountCustomFieldMeta", "GET", "ActiveCampaigns", $data=array());
										$customFieldsList = (array)json_decode($customFieldsData);
									
										if(isset($customFieldsList['accountCustomFieldMeta'])){
											foreach ($customFieldsList['accountCustomFieldMeta'] as $key => $value) {
												$fieldLabel = $value->fieldLabel;
												if($fieldLabel == $fieldName){
													$accountCustomFields[$value->id] = array('field' => $fieldName, 'type' => $value->fieldType);
												}//end of if
											}//end of foreach
										}//end of if
									}//end of if
								}//end of else
							}//end of foreach

							if(!empty($accountCustomFields)){
								$customFieldValueData = syncESData("/api/3/accountCustomFieldData", "GET", "ActiveCampaigns", $data=array());
								$customFieldValuesResult = (array)json_decode($customFieldValueData);

								if(isset($customFieldValuesResult['accountCustomFieldData'])){
									foreach ($customFieldValuesResult['accountCustomFieldData'] as $k => $valData) {
		                                        foreach ($accountCustomFields as $fieldId => $fieldData) {
		                                        	if($activeCampaignAccountId == $valData->accountId && ($valData->customFieldId == $fieldId)){
		                                        		if(in_array($fieldData['field'], $suiteCRMFields)){
													$suiteCRMFieldName = array_search($fieldData['field'], $suiteCRMFields);
		                                                  	if($fieldData['type'] == 'date'){
		                                                       	$fieldNameValueArray[$suiteCRMFieldName] = date('Y-m-d', strtotime($valData->fieldValue));
		                                                  	}else if($fieldData['type'] == 'datetime' || $fieldData['type'] == 'datetimecombo'){
		                                                       	$fieldNameValueArray[$suiteCRMFieldName] = $timedate->to_db(date('m/d/Y H:i', strtotime($valData->fieldValue)));
		                                                  	}else{
		                                                       	$fieldNameValueArray[$suiteCRMFieldName] = $valData->fieldValue;
		                                                  	}//end of else
		                                                	}//end of if
		                                            	}//end of if
		                                        }//end of foreach
		                                   }//end of foreach
								}//end of if
							}//end of if
						}//end of if

						$selAccountData = "SELECT * FROM vi_accounts_es WHERE vi_es_account_id = '$activeCampaignAccountId' AND vi_es_name = 'ActiveCampaigns' AND deleted = 0";
						$selectAccountDataRow = $GLOBALS['db']->fetchOne($selAccountData);

						if(!empty($selectAccountDataRow)){
							$suiteCRMRecordId = $selectAccountDataRow['vi_suitecrm_account_id'];
							$bean = BeanFactory::getBean($suiteCRMModule, $suiteCRMRecordId);

							if(!empty($bean) && $bean->deleted == 0){
								$actionType = 'Update';
								$accountBean = BeanFactory::getBean($suiteCRMModule, $suiteCRMRecordId);
							}else{
								$id = $selectAccountDataRow['id'];

								$updateAccountData = "UPDATE vi_accounts_es SET deleted = 1 WHERE id = '$id'";
	               				$updateAccountDataResult = $GLOBALS['db']->query($updateAccountData);

								$actionType = 'Insert';
								$accountBean = BeanFactory::newBean($suiteCRMModule);
							}//end of else
						}else{
							$actionType = 'Insert';
							$accountBean = BeanFactory::newBean($suiteCRMModule);
						}//end of else

						foreach ($fieldNameValueArray as $fieldName => $fieldValue) {
							$accountBean->$fieldName = $fieldValue;
						}//end of foreach

						if($accountBean->save()){
							if($actionType == 'Insert'){
								$accountId = create_guid(); 
								$suiteAccountId = $accountBean->id;
								
								$addData = array('id' => $accountId,'vi_suitecrm_account_id' => $suiteAccountId, 'vi_es_account_id' => $activeCampaignAccountId, 'vi_es_name' => 'ActiveCampaigns', 'deleted' => 0);

	                           		insertESRecord('vi_accounts_es', $addData);
							}//end of if

							addRecordInSyncLog($accountBean->id, $activeCampaignAccountId, $actionType, $suiteCRMModule, $esModule);//insert record in sync log
						}//end of if
					}//end of if
				}//end of if
			}//end of else if
		}//end of if		
	}//end of if
}//end of while

function addRecordInSyncLog($suiteCRMRecordId, $activeCampaignContactId, $actionType, $suiteCRMModule, $esModule){
	global $app_list_strings;
	$eslBean = BeanFactory::newBean('VI_EmailSoftwareIntegartionSyncLog');

     if($esModule == 'Contacts_List'){
          $esModule = 'Contact List';
     }else if($esModule == "Accounts"){
          $esModule = 'Organizations';
     }//end of else if
	
	$eslBean->name = $esModule;
	$eslBean->to_module = $app_list_strings['moduleList'][$suiteCRMModule];
	$eslBean->email_software = "ActiveCampaigns";
	$eslBean->sync_type = "AC2SC";
	$eslBean->action_type = $actionType; 
	$eslBean->status = "Successfull";
	$eslBean->from_record = $suiteCRMRecordId;
	$eslBean->viem_to_record = $activeCampaignContactId;
	$eslBean->save();
}//end of function