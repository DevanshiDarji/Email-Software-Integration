<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
require_once("custom/include/VIEsIntegrationConfig.php");
require_once("custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php");
class viIntegration_hook {
    function viafter_save_method($bean, $event, $arguments) {
        $sel = "SELECT * FROM vi_automatic_sync WHERE sync_to_es = 1 and deleted = 0";
        $result = $GLOBALS['db']->query($sel);
        $automaticSyncArray = $suitecrmModule = array();
        while($row = $GLOBALS['db']->fetchByAssoc($result)){
            $automaticSyncArray[$row['sync_software']][] = explode(",", $row['sel_mapping_module_list']);   
        }
        
        foreach ($automaticSyncArray as $key => $value) {
            foreach ($value as $k => $v) {
                foreach ($v as $keyMap => $val) {
                    $getSuiteCRMModuleQuery = "SELECT * FROM vi_module_mapping WHERE module_mapping_id = '".$val."' and status='Active' and deleted = 0";
                    $resultSuiteCRMModule = $GLOBALS['db']->fetchOne($getSuiteCRMModuleQuery);
                    if(!empty($resultSuiteCRMModule)){                            
                        $suitecrmModule[$key][$val] = $resultSuiteCRMModule;
                    }//end of if
                }
            }
        }//end of foreach
           
        if(!empty($suitecrmModule)){
            foreach ($suitecrmModule as $syncSoftware => $valueMappingData) {
                foreach ($valueMappingData as $mappingId => $valueData) {
                    if($bean->module_dir == $valueData['suitecrm_module']){
                        $emsConditionsData = getEMSConditionsData($valueData);

                        if(isset($bean->related_id) && $bean->related_id != ""){
                            $currentSavedRecordId = $bean->related_id;
                        }else if(isset($bean->record_id) && $bean->record_id != ""){
                            $currentSavedRecordId = $bean->record_id;
                        }else{
                            $currentSavedRecordId = $bean->id;
                        }//end of else
                    
                        $conditionMatchedRecords = matchEMSConditionsData($emsConditionsData, $valueData, $currentSavedRecordId);
                        
                        $suitecrmFields = $suitecrmContactsFields = array();
                        $selMapping = "SELECT * FROM vi_integration_field_mapping WHERE module_mapping_id = '$mappingId'";
                        $mappingResult = $GLOBALS['db']->query($selMapping);                    
                        while($rowMapping = $GLOBALS['db']->fetchByAssoc($mappingResult)){
                            if($syncSoftware == "ActiveCampaigns"){
                                $sendGridFields = $rowMapping['es_module_fields'];  
                            }else{
                                $sendGridFields = strtolower($rowMapping['es_module_fields']);  
                            }                   
                            $suitecrmFields[$sendGridFields] = $rowMapping['suitecrm_module_fields'];
                        }
                        
                        if($bean->module_dir == "ProspectLists"){
                            $selContactsMapping = "SELECT * FROM vi_integration_contacts_field_mapping WHERE module_mapping_id = '$mappingId' and deleted = 0";
                            $mappinContactsResult = $GLOBALS['db']->query($selContactsMapping);  
                            while($rowContactsMapping = $GLOBALS['db']->fetchByAssoc($mappinContactsResult)){
                                if($syncSoftware == "ActiveCampaigns"){
                                    $sendGridContactsFields = $rowContactsMapping['sendgrid_contacts_module_fields'];
                                }else{
                                    $sendGridContactsFields = strtolower($rowContactsMapping['sendgrid_contacts_module_fields']);
                                }//end of else
                                $suitecrmContactsFields[$sendGridContactsFields] = $rowContactsMapping['suitecrm_contacts_module_fields'];
                            }
                        }

                        if($bean->module_dir == "ProspectLists"){
                            $this->vi_process($bean->module_dir,$suitecrmFields,$syncSoftware,$bean,$suitecrmContactsFields, $conditionMatchedRecords, $valueData);
                        }else{
                            $this->vi_process($bean->module_dir,$suitecrmFields,$syncSoftware,$bean,"", $conditionMatchedRecords, $valueData);
                        }
                    }   
                }
            }//end of foreach
        }//end of if
    }//end of function
    
    public function vi_process($currentModuleName,$suitecrmFields,$esActive,$bean,$suitecrmContactsFields, $conditionMatchedRecords, $mappingValueData){
        global $current_user;
        $currentLoggedInUserID = $current_user->id;
        $fields = $mappingFields = $updatedRecords = $insertedRecords = $failure = array();
        $targetListSubpanelModule = $mappingValueData['target_list_subpanel_module'];
        
        if(isset($bean->related_id) && $bean->related_id != ""){
            $currentSavedRecordIds = (array)$bean->related_id;
        }else if(isset($bean->record_id) && $bean->record_id != ""){
            $currentSavedRecordIds = (array)$bean->record_id;
        }else{
            $currentSavedRecordIds = (array)$bean->id;
        }//end of else

        foreach ($currentSavedRecordIds as $index => $saveRecordId) {
            if(array_key_exists($saveRecordId, $conditionMatchedRecords)){
                $moduleBean = BeanFactory::getBean($currentModuleName, $saveRecordId);

                $planType = getPlanType($esActive);
                $syncId = checkRecordExist($saveRecordId, $esActive, $currentModuleName);

                if($syncId == '') {
                    $action = 'Insert';
                }else {
                    $action = 'Update';
                    if($planType == 1){
                        if($currentModuleName == "Leads" || $currentModuleName == "Contacts"){
                            $fields['id'] = $syncId;
                        }
                    }               
                }

                $fields = getModuleFieldsValue($suitecrmFields, $moduleBean, $currentModuleName, $flag=false);
                if($currentModuleName == "AOS_Products"){
                    $fields['storageLocation'] = "remote";  
                }               
                $mappingFields[$saveRecordId] = $fields;
            }//end of if
        }//end of foreach

        //current record from suitecrm
        $currentRecordId = getModuleCurrentRecordIds($bean, $currentModuleName, $conditionMatchedRecords);
        $matchedRecordCount = count($currentRecordId);

        foreach ($currentRecordId as $index => $recordId) {
            $currentModuleBean = BeanFactory::getBean($currentModuleName, $recordId);
            $moduleList['list'][] = $currentModuleBean;
        }//end of foreach
        
        if($currentModuleName == "ProspectLists"){
            //make array of request data
            foreach ($currentRecordId as $index => $recordId) {
                if($esActive == "ConstantContact"){
                   $mappingFields[$recordId]['status'] = "ACTIVE"; 
                }//end of if
                
                if($esActive == "SendInBlue"){
                    $fetchAllFolders = syncESData("contacts/folders","GET",$esActive,$data=array());
                    $fetchAllFoldersResponse = (array)json_decode($fetchAllFolders);
                    if($fetchAllFoldersResponse['count'] > 0){
                        $folderId = $fetchAllFoldersResponse['folders'][0]->id;
                    }//end of if
                    $mappingFields[$recordId]['folderId'] = $folderId;
                }//end of if
            }//end of foreach
        }//end of if

        if($esActive == "ConstantContact" && $currentModuleName == "ProspectLists"){
            //Fetch All SuiteCRM List, add/update them into Constant Contact And then store them into an array
            $matchedListIds = array();
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;
                            $sql = "SELECT * FROM vi_segments_es WHERE vi_es_name = '$esActive' AND vi_suitecrm_segments_id = '$suitecrmRecordId' AND deleted = 0";
                            $rowCheckResult = $GLOBALS['db']->fetchOne($sql);  
                            $viEsSegmentsId = $segmentId = "";
                            if(!empty($rowCheckResult)){
                                $viEsSegmentsId = $rowCheckResult['vi_es_segments_id'];
                                $segmentId = $rowCheckResult['id'];
                            }//ens of if
                            
                            if($viEsSegmentsId == ""){
                                //add new list
                                $insertResult = processESData("/lists?api_key=","POST",$mappingFields[$suitecrmRecordId],"Insert",$suitecrmRecordId,$currentModuleName,$esActive);
                                if($insertResult == "failure"){
                                    $failure[] = $insertResult;
                                }else{
                                    $sql = "SELECT viem_to_record FROM vi_emailsoftwareintegrationsynclog WHERE viem_to_record = '$insertResult'";
                                    $selectResult = $GLOBALS['db']->fetchOne($sql,false,'',false); 
                                    if(!empty($selectResult['viem_to_record'])){
                                        $segmentsId = $selectResult['viem_to_record']; 
                                    }
                                    $insertedRecords[] = $insertResult;
                                }                       
                            }else{
                                $result = syncESData("/lists/".$viEsSegmentsId."?api_key=","GET",$esActive,"");
                                $response = (array)json_decode($result);
                                
                                if(isset($response['id'])){
                                    $updateResult = processESData("/lists/".$viEsSegmentsId."?api_key=","PUT",$mappingFields[$suitecrmRecordId],"Update",$suitecrmRecordId,$currentModuleName,$esActive);
                                    if($updateResult == "failure"){
                                        $failure[] = $updateResult;
                                    }else{
                                        $updatedRecords[] = $updateResult;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$segmentId'";
                                    $updateResult = $GLOBALS['db']->query($updateData);

                                    //add new list
                                    $insertResult = processESData("/lists?api_key=","POST",$mappingFields[$suitecrmRecordId],"Insert",$suitecrmRecordId,$currentModuleName,$esActive); 
                                    if($insertResult == "failure"){
                                        $failure[] = $insertResult;
                                    }else{
                                        $sql = "SELECT viem_to_record FROM vi_emailsoftwareintegrationsynclog WHERE viem_to_record = '$insertResult'";
                                        $selectResult = $GLOBALS['db']->fetchOne($sql,false,'',false);
                                        if(!empty($selectResult['viem_to_record'])){
                                            $segmentsId = $selectResult['viem_to_record']; 
                                        }
                                        $insertedRecords[] = $insertResult;
                                    }
                                    $viEsSegmentsId = '';   
                                }
                            }
                            if($viEsSegmentsId == ""){
                                $matchedListIds[$value->id] = $segmentsId;
                            }else{
                                $matchedListIds[$value->id] = $viEsSegmentsId;
                            }
                        }
                    } 
                }//end of foreach

                if($targetListSubpanelModule != ''){
                    if(isset($suitecrmContactsFields) && !empty($suitecrmContactsFields)){
                        $finalArray = $array = $keyListArray = $finalSuiteContactsLeadArray = array();
                        foreach ($matchedListIds as $keyListid => $valueListid) {
                            $prospectListBean = BeanFactory::getBean("ProspectLists",$keyListid);
                            $relatedBean = BeanFactory::newBean($targetListSubpanelModule);
                            $relatedTableName = $relatedBean->getTableName();
                            $relatedSuiteCRMContactIDs = array();
                            if ($prospectListBean->load_relationship($relatedTableName)) {
                                $relatedBeans = $prospectListBean->$relatedTableName->getBeans();
                                foreach ((array)$relatedBeans as $k => $val) {
                                    $relatedSuiteCRMContactIDs[] = $k;
                                }
                            }
                            $finalArray[$valueListid] = $relatedSuiteCRMContactIDs;
                        }

                        foreach ($finalArray as $key => $value) {
                            foreach ($value as $keyList => $valueList) {
                                if(array_key_exists($valueList, $keyListArray)) {
                                    $keyListArray[$valueList][] = $key;
                                } else {
                                    $keyListArray[$valueList][] = $key;    
                                }   
                            }
                        }

                        $sea = new SugarEmailAddress;
                        foreach ($keyListArray as $keyReq => $valueReq) {
                            $contactLeadBean = BeanFactory::getBean($targetListSubpanelModule,$keyReq);
                            $list = $array = array(); 
                            foreach ($valueReq as $keyListID => $valueListID) {
                                $valueListid = (string)$valueListID;
                                $array[] = array('id' => $valueListid);
                            }

                            $finalSuiteContactsLeadArray['lists'] = $array;
                            $primaryEmailAddress = "";
                            foreach ($suitecrmContactsFields as $keyfield => $vfield) {
                                if($vfield == "email1"){
                                    $primaryEmailAddress = $sea->getPrimaryAddress($contactLeadBean,$keyReq);
                                }else{
                                    $fieldDef = $contactLeadBean->field_defs[$vfield];
                                    if($fieldDef['type'] == 'multienum'){
                                        $optionList = unencodeMultienum($contactLeadBean->$vfield);
                                        if (empty($optionList)) {
                                            $fieldVal = '';
                                        }//end of if
                                        $fieldVal = '||' . implode('||', $optionList) . '||';
                                    }else{
                                        $fieldVal = $contactLeadBean->$vfield; 
                                    }//end of else
                                    $finalSuiteContactsLeadArray[$keyfield] = $fieldVal; 
                                }
                            }
                            $finalSuiteContactsLeadArray['email_addresses'] = array (0 => array ('email_address' => $primaryEmailAddress));
                            $finalSuiteContactsLeadArray['status'] = 'ACTIVE';
                            
                            $suiteCRMLeadId = $suiteCRMContactId = '';
                            if($targetListSubpanelModule == 'Contacts'){
                                $relatedContactLeadId = "vi_suitecrm_contact_id = '$keyReq'";
                                $suiteCRMContactId = $keyReq;
                            }else{
                                $relatedContactLeadId = "vi_suitecrm_lead_id = '$keyReq'";
                                $suiteCRMLeadId = $keyReq;
                            }//end of else

                            $sqlFetchESId = "SELECT * FROM vi_contacts_es WHERE ".$relatedContactLeadId." and vi_es_name = 'ConstantContact' AND deleted = 0";
                            $resultFetchESId = $GLOBALS['db']->fetchOne($sqlFetchESId,false,'',false);
                            $relatedContactESId = $suiteContactId = '';
                            if(!empty($resultFetchESId)){
                                $relatedContactESId = $resultFetchESId['vi_es_contact_id'];
                                $suiteContactId = $resultFetchESId['id'];
                            }//end of if
                            
                            if($relatedContactESId != ""){
                                $response = syncESData("/contacts?email=".$primaryEmailAddress."&api_key=","GET",'ConstantContact',$data = array());
                                $resultFetchRelatedContacts = (array)json_decode($response);
                                
                                if(isset($resultFetchRelatedContacts['id'])){
                                    $updateContact = syncESData("/contacts?api_key=","PUT",'ConstantContact',$finalSuiteContactsLeadArray);
                                    $updateContactData = (array)json_decode($updateContact);
                                    $relatedContactsId = $updateContactData['id'];
                                    $listIds = array();
                                    $listIds = $updateContact['lists'];  
                                    
                                    if(isset($listIds) && !empty($listIds)){
                                        foreach ($listIds as $key => $listid) {
                                            if(empty($resultFetchESId['vi_es_list_id'])){
                                                $listString = $resultFetchESId['vi_es_list_id'].",".$listid->id;
                                            }else{
                                                $listString = $listid->id;
                                            }
                                            $updateContactListId = "UPDATE vi_contacts_es SET vi_es_list_id = '$listString' WHERE vi_es_contact_id = '$relatedContactESId' and vi_es_name = 'ConstantContact' AND deleted = 0";
                                            $updateResult = $GLOBALS['db']->query($updateContactListId);
                                        }
                                    }
                                }else{
                                    if(isset($resultFetchRelatedContacts['results'][0]->id)){
                                        $relatedContactId = $resultFetchRelatedContacts['results'][0]->id;
                                        $updateContact = syncESData("/contacts/".$relatedContactId."?api_key=","PUT",'ConstantContact',$finalSuiteContactsLeadArray);
                                        $updateContactData = (array)json_decode($updateContact);
                                        $relatedContactsId = $updateContactData['id'];
                                        $listIds = array();
                                        $listIds = $updateContactData['lists']; 
                                        
                                        if(isset($listIds) && !empty($listIds)){
                                            foreach ($listIds as $key => $listid) {
                                                if(empty($resultFetchESId['vi_es_list_id'])){
                                                    $listString = $resultFetchESId['vi_es_list_id'].",".$listid->id;
                                                }else{
                                                    $listString = $listid->id;
                                                }
                                                $updateContactListId = "UPDATE vi_contacts_es SET vi_es_list_id = '$listString' WHERE vi_es_contact_id = '$relatedContactESId' and vi_es_name = 'ConstantContact' AND deleted = 0";
                                                $updateResult = $GLOBALS['db']->query($updateContactListId);
                                            }
                                        }
                                    }else{
                                        $resultFetchRelatedContacts = syncESData("/contacts?api_key=","POST",$esActive,$finalSuiteContactsLeadArray);
                                        $resultFetchRelatedContacts = (array)json_decode($resultFetchRelatedContacts); 
                                        $relatedContactsId = $resultFetchRelatedContacts['id'];                        
                                        if(isset($resultFetchRelatedContacts['lists']) && !empty($resultFetchRelatedContacts['lists'])){
                                            foreach ($resultFetchRelatedContacts['lists'] as $key => $listid) {
                                                $contactsId = create_guid(); 
                                                $tableName = "vi_contacts_es";
                                                $data = array('id' => $contactsId,'vi_suitecrm_contact_id' => $suiteCRMContactId,'vi_es_contact_id' => $relatedContactsId,'vi_suitecrm_lead_id' => $suiteCRMLeadId,'vi_es_name' => 'ConstantContact','vi_es_list_id' => $listid->id,'vi_suitecrm_module' => $targetListSubpanelModule,'vi_es_lead_id' => '', 'deleted' => 0);
                                                insertESRecord($tableName,$data);
                                            }
                                        }
                                    }
                                }
                            }else{
                                $fetchRelatedContacts = syncESData("/contacts?api_key=","POST",$esActive,$finalSuiteContactsLeadArray);
                                $resultFetchRelatedContacts = (array)json_decode($fetchRelatedContacts);

                                if(isset($resultFetchRelatedContacts['id']) && $resultFetchRelatedContacts['id'] != ''){
                                    $relatedContactsId = $resultFetchRelatedContacts['id'];

                                    if(isset($resultFetchRelatedContacts['lists']) && !empty($resultFetchRelatedContacts['lists'])){
                                        foreach ($resultFetchRelatedContacts['lists'] as $key => $listid) {
                                            $contactsId = create_guid(); 
                                            $tableName = "vi_contacts_es";
                                            $data = array('id' => $contactsId,'vi_suitecrm_contact_id' => $suiteCRMContactId,'vi_es_contact_id' => $relatedContactsId,'vi_suitecrm_lead_id' => $suiteCRMLeadId,'vi_es_name' => 'ConstantContact','vi_es_list_id' => $listid->id,'vi_suitecrm_module' => $targetListSubpanelModule,'vi_es_lead_id' => '', 'deleted' => 0);
                                            insertESRecord($tableName,$data);
                                        }
                                    }
                                }//end of if
                            }
                        }
                    }//end of if
                }//end of if
            }//end of if
        }//end of if

        if($esActive == "ActiveCampaigns" && ($currentModuleName == "Contacts" || $currentModuleName == "Leads")){
            $fieldListResponse = syncESData('/api/3/fields','GET',$esActive,'');
            $fieldList = (array)json_decode($fieldListResponse);

            $contactCustomFields = array();
            if(!empty($fieldList)){
                if(!empty($fieldList['fields'])){
                    foreach ($fieldList['fields'] as $fkey => $fvalue) {
                        $contactCustomFields[$fvalue->id] = $fvalue->title;
                    }
                }
            }

            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $activeCampaignsFieldsData = array();
                            $suitecrmRecordId = $value->id;

                            foreach ($mappingFields[$suitecrmRecordId] as $keyfield => $fieldVal) {
                                if(in_array($keyfield, $contactCustomFields)){
                                    $activeCampaignsFieldsData['fieldValues'][] = array('field' => array_search($keyfield, $contactCustomFields), 'value' => $fieldVal);
                                }else{
                                    $activeCampaignsFieldsData[$keyfield] = $fieldVal;
                                }
                            }
                            $fields = array('contact' => $activeCampaignsFieldsData);
                            
                            $getContactLeadSql = "SELECT * FROM vi_contacts_es ";
                            if($currentModuleName == "Contacts"){
                                $getContactLeadSql .= "WHERE vi_suitecrm_contact_id = '$suitecrmRecordId'";
                            }else{
                                $getContactLeadSql .= "WHERE vi_suitecrm_lead_id = '$suitecrmRecordId'";
                            }//end of else
                            $getContactLeadSql .= " AND vi_es_name = '$esActive' AND vi_suitecrm_module = '$currentModuleName' AND deleted = 0";
                            $getContactLeadResult = $GLOBALS['db']->fetchOne($getContactLeadSql,false,'',false);

                            $viEsContactId = $contactId = '';
                            if(!empty($getContactLeadResult)){
                                $contactId = $getContactLeadResult['id'];
                                if($currentModuleName == "Contacts"){
                                    $viEsContactId = $getContactLeadResult['vi_es_contact_id'];
                                }else{
                                    $viEsContactId = $getContactLeadResult['vi_es_lead_id'];    
                                }//end of else
                            }//end of if
                            
                            //fetch all records and if there is no records then add directly
                            $res = syncESData("/api/3/contacts","GET",$esActive,"");
                            $checkResponse = (array)json_decode($res);
                            
                            if(empty($checkResponse)){
                                //no records. add new contact
                                $insertResult = processESData("/api/3/contacts","POST",$fields,"Insert",$value->id,$currentModuleName,$esActive);
                                if($insertResult == "failure"){
                                    $failure[] = $insertResult;
                                }else{
                                    $insertedRecords[] = $insertResult;    
                                }
                            }else{
                                //there is records
                                if($viEsContactId != ""){
                                    $responseData = syncESData('/api/3/contacts/'.$viEsContactId, "GET", $esActive, $data = array());
                                    $jsonDecodeData = json_decode($responseData);

                                    if(isset($jsonDecodeData->message) && $jsonDecodeData->message != ''){
                                        $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$contactId'";
                                        $updateResult = $GLOBALS['db']->query($updateData);
                                        //add new
                                        $insertResult = processESData("/api/3/contacts","POST",$fields,"Insert",$value->id,$currentModuleName,$esActive);
                                        if($insertResult == "failure"){
                                            $failure[] = $insertResult;
                                        }else{
                                            $insertedRecords[] = $insertResult;    
                                        }//end of else
                                    }else{
                                        //update contact
                                        $updateResult = processESData("/api/3/contacts/".$viEsContactId,"PUT",$fields,"Update",$value->id,$currentModuleName,$esActive);
                                        if($updateResult == "failure"){
                                            $failure[] = $updateResult;
                                        }else{
                                            $updatedRecords[] = $updateResult;    
                                        }//end of else
                                    }//end of else
                                }else{
                                    //add new
                                    $insertResult = processESData("/api/3/contacts","POST",$fields,"Insert",$value->id,$currentModuleName,$esActive);
                                    if($insertResult == "failure"){
                                        $failure[] = $insertResult;
                                    }else{
                                        $insertedRecords[] = $insertResult;    
                                    }//end of else
                                }//end of else
                            }//end of else
                        }//end of foreach
                    }//end of if
                }//end of foreach
            }//end of if
        }//end of if

        if($esActive == "ActiveCampaigns" && $currentModuleName == "ProspectLists"){
            $listId = '';
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;
                            $prospectListBean = BeanFactory::getBean("ProspectLists",$value->id);
                            $fields = array('list' => $mappingFields[$suitecrmRecordId]);

                            $sql = "SELECT * FROM vi_segments_es WHERE vi_es_name = '$esActive' AND vi_suitecrm_segments_id = '$suitecrmRecordId' AND deleted = 0";
                            $rowCheckResult = $GLOBALS['db']->fetchOne($sql);  
                            $viEsContactIds = $segmentId = '';
                            if(!empty($rowCheckResult)){
                                $viEsContactIds = $rowCheckResult['vi_es_segments_id'];
                                $segmentId = $rowCheckResult['id'];
                            }//end of if

                            $res = syncESData("/api/3/lists","GET",$esActive,$data=array());
                            $checkResponse = (array)json_decode($res);
                            
                            if(empty($checkResponse['lists'])){
                                //no records. add new list
                                $insertResult = processESData("/api/3/lists","POST",$fields,"Insert",$value->id,$currentModuleName,$esActive);
                                if($insertResult == "failure"){
                                    $failure[] = $insertResult;
                                }else{
                                    $listId = $insertResult;
                                    $insertedRecords[] = $insertResult;    
                                }
                            }else{
                                if($viEsContactIds != ''){
                                    $responseData = syncESData('/api/3/lists/'.$viEsContactIds, "GET", $esActive, $data = array());
                                    $jsonDecodeData = json_decode($responseData);

                                    if(isset($jsonDecodeData->message) && $jsonDecodeData->message != ''){
                                        $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$segmentId'";
                                        $updateResult = $GLOBALS['db']->query($updateData);
                                        //add new list
                                        $insertResult = processESData("/api/3/lists","POST",$fields,"Insert",$value->id,$currentModuleName,$esActive);
                                        
                                        if($insertResult == "failure"){
                                            $failure[] = $insertResult;
                                        }else{
                                            $listId = $insertResult;
                                            $insertedRecords[] = $insertResult;    
                                        }
                                    }else{
                                        //update list
                                        $updateResult = processESData("/api/3/lists/".$viEsContactIds,"PUT",$fields,"Update",$value->id,$currentModuleName,$esActive);
                                        $listId = $viEsContactIds;
                                        if($updateResult == "failure"){
                                            $updatedRecords[] = $viEsContactIds;
                                        }else{
                                            $updatedRecords[] = $updateResult;    
                                        }
                                    }
                                }else{
                                    //add new list
                                    $insertResult = processESData("/api/3/lists","POST",$fields,"Insert",$value->id,$currentModuleName,$esActive);
                                    if($insertResult == "failure"){
                                        $failure[] = $insertResult;
                                    }else{
                                        $listId = $insertResult;
                                        $insertedRecords[] = $insertResult;    
                                    }
                                }
                            }

                            //Fetch Related Contacts From SuiteCRM
                            if($targetListSubpanelModule != ''){
                                if(isset($suitecrmContactsFields) && !empty($suitecrmContactsFields)){
                                    $relatedBean = BeanFactory::newBean($targetListSubpanelModule);
                                    $relatedTableName = $relatedBean->getTableName();

                                    if ($prospectListBean->load_relationship($relatedTableName)) {
                                        $relatedBeans = $prospectListBean->$relatedTableName->getBeans();
                                        foreach ((array)$relatedBeans as $k => $val) {
                                            if($targetListSubpanelModule == 'Contacts'){
                                                $relatedContactLeadId = "vi_suitecrm_contact_id = '$val->id'";
                                            }else{
                                                $relatedContactLeadId = "vi_suitecrm_lead_id = '$val->id'";
                                            }//end of else

                                            $fetchEsContactId = "SELECT * FROM vi_contacts_es WHERE ".$relatedContactLeadId." AND vi_es_name = 'ActiveCampaigns' AND deleted = 0";
                                            $selectResult = $GLOBALS['db']->fetchOne($fetchEsContactId);

                                            $contactCustomFields = array();
                                            $fieldListResponse = syncESData('/api/3/fields','GET',$esActive,$data=array());
                                            $fieldList = (array)json_decode($fieldListResponse);

                                            if(!empty($fieldList)){
                                                if(!empty($fieldList['fields'])){
                                                    foreach ($fieldList['fields'] as $fkey => $fvalue) {
                                                        $contactCustomFields[$fvalue->id] = $fvalue->title;
                                                    }
                                                }
                                            }

                                            $finalSuiteContactsLeadArray = getContactsLeadFieldsForActiveCampaigns($targetListSubpanelModule, $val, $suitecrmContactsFields, $contactCustomFields);
                                            $contactLeadId = getEMSToolContactsData($listId, 'ActiveCampaigns');

                                            if(!empty($selectResult)){
                                                $contactId = $selectResult['id'];
                                                $viEsContactId = $selectResult['vi_es_contact_id'];
                                                
                                                $responseData = syncESData("/api/3/contacts/".$viEsContactId,"GET",$esActive,$data = array());
                                                $jsonDecodeData = json_decode($responseData);
                                                
                                                if(isset($jsonDecodeData->message) && $jsonDecodeData->message != ''){
                                                    $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$contactId'";
                                                    $updateResult = $GLOBALS['db']->query($updateData);

                                                    $insertRelatedContact = syncESData("/api/3/contacts","POST",$esActive,$finalSuiteContactsLeadArray);
                                                    $insertRelatedContact = (array)json_decode($insertRelatedContact);
                                                    //add this to vi_contacts_es
                                                    $rendomRecordId = create_guid();
                                                    $tableName = "vi_contacts_es";
                                                    $suiteCRMLeadId = $suiteCRMContactId = '';
                                                    if($targetListSubpanelModule == 'Leads'){
                                                        $suiteCRMLeadId = $val->id;
                                                    }else{
                                                        $suiteCRMContactId = $val->id;
                                                    }//end of else

                                                    $data = array('id' => $rendomRecordId,'vi_suitecrm_contact_id' => $suiteCRMContactId,'vi_es_contact_id' => $insertRelatedContact['contact']->id,'vi_suitecrm_lead_id' => $suiteCRMLeadId,'vi_es_name' => $esActive,'vi_es_list_id' => '','vi_suitecrm_module' => $targetListSubpanelModule,'vi_es_lead_id' => '', 'deleted' => 0);
                                                    insertESRecord($tableName,$data);

                                                    if($viEsContactIds != ''){
                                                        $addContactToListReqData = array (
                                                            'contactList' => 
                                                            array (
                                                                'list' => $viEsContactIds,
                                                                'contact' => $insertRelatedContact['contact']->id,
                                                                'status' => 1,
                                                            ),
                                                        );                                                        
                                                    }else{
                                                        foreach ($insertedRecords as $keyId => $valueId) {
                                                            $addContactToListReqData = array (
                                                                'contactList' => 
                                                                array (
                                                                    'list' => $valueId,
                                                                    'contact' => $insertRelatedContact['contact']->id,
                                                                    'status' => 1,
                                                                ),
                                                            );
                                                        }
                                                    }

                                                    syncESData("/api/3/contactLists","POST",$esActive,$addContactToListReqData);
                                                    //update vi_es_list_id after adding contact into list
                                                    $listId = $addContactToListReqData["contactList"]['list'];
                                                    if($targetListSubpanelModule == 'Contacts'){
                                                        $relatedContactLeadId = "vi_suitecrm_contact_id = '$val->id'";
                                                    }else{
                                                        $relatedContactLeadId = "vi_suitecrm_lead_id = '$val->id'";
                                                    }//end of else

                                                    $updateSql = "UPDATE vi_contacts_es
                                                                    SET vi_es_list_id = '$listId'
                                                                    WHERE ".$relatedContactLeadId." and vi_es_name = 'ActiveCampaigns' AND deleted = 0";
                                                    $updateDataResult = $GLOBALS['db']->query($updateSql);
                                                }else{
                                                    syncESData("/api/3/contacts/".$viEsContactId, "PUT", $esActive,$finalSuiteContactsLeadArray);

                                                    //contact is already added just add this contact to list and update vi_es_list_id field
                                                    $addContactToListReqData = array();
                                                    if($viEsContactIds != ''){
                                                        $addContactToListReqData = array (
                                                            'contactList' => 
                                                            array (
                                                                'list' => $viEsContactIds,
                                                                'contact' => $selectResult['vi_es_contact_id'],
                                                                'status' => 1,
                                                            ),
                                                        );
                                                    }else{
                                                        foreach ($insertedRecords as $keyId => $valueId) {
                                                            $addContactToListReqData = array (
                                                                'contactList' => 
                                                                array (
                                                                    'list' => $valueId,
                                                                    'contact' => $selectResult['vi_es_contact_id'],
                                                                    'status' => 1,
                                                                ),
                                                            );
                                                        }
                                                    }
                                                    syncESData("/api/3/contactLists","POST",$esActive,$addContactToListReqData);                               
                                                    removeContactsLeadFromListForAllEMSTool($contactLeadId, $viEsContactId, $insertContactLeadId, $updateContactLeadId, $esActive, $listId, $val->id, $targetListSubpanelModule, $planType="");
                                                }
                                            }else{
                                                $updateContactLeadId = addContactsLeadsFromList($contactLeadId, $targetListSubpanelModule, $val->id, $listId, $esActive, $updateRecordData=array(), $finalSuiteContactsLeadArray, $insertContactLeadId, $updateContactLeadId);

                                                //first add this new contact then add to list
                                                $insertRelatedContact = syncESData("/api/3/contacts","POST",$esActive,$finalSuiteContactsLeadArray);
                                                $insertRelatedContact = (array)json_decode($insertRelatedContact);

                                                if(empty($insertRelatedContact['errors'])){
                                                    //add this to vi_contacts_es
                                                    $randomRecordId = create_guid();
                                                    $tableName = "vi_contacts_es";
                                                    $suiteCRMLeadId = $suiteCRMContactId = '';
                                                    if($targetListSubpanelModule == 'Leads'){
                                                        $suiteCRMLeadId = $val->id;
                                                    }else{
                                                        $suiteCRMContactId = $val->id;
                                                    }//end of else
                                                    $insertContactLeadId[] = $insertRelatedContact['contact']->id;

                                                    $data = array('id' => $randomRecordId,'vi_suitecrm_contact_id' => $suiteCRMContactId,'vi_es_contact_id' => $insertRelatedContact['contact']->id,'vi_suitecrm_lead_id' => $suiteCRMLeadId,'vi_es_name' => $esActive,'vi_es_list_id' => '','vi_suitecrm_module' => $targetListSubpanelModule,'vi_es_lead_id' => '', 'deleted' => 0);
                                                    insertESRecord($tableName,$data);
                                                    
                                                    if($viEsContactIds != ''){
                                                        $addContactToListReqData = array (
                                                            'contactList' => 
                                                            array (
                                                                'list' => $viEsContactIds,
                                                                'contact' => $insertRelatedContact['contact']->id,
                                                                'status' => 1,
                                                            ),
                                                        );
                                                    }else{
                                                        foreach ($insertedRecords as $keyId => $valueId) {
                                                            $addContactToListReqData = array (
                                                                'contactList' => 
                                                                array (
                                                                    'list' => $valueId,
                                                                    'contact' => $insertRelatedContact['contact']->id,
                                                                    'status' => 1,
                                                                ),
                                                            );
                                                        }
                                                    }
                                                    syncESData("/api/3/contactLists","POST",$esActive,$addContactToListReqData);
                                                    //update vi_es_list_id after adding contact into list
                                                    $listsId = $addContactToListReqData["contactList"]['list'];
                                                    if($targetListSubpanelModule == 'Contacts'){
                                                        $relatedContactLeadId = "vi_suitecrm_contact_id = '$val->id'";
                                                    }else{
                                                        $relatedContactLeadId = "vi_suitecrm_lead_id = '$val->id'";
                                                    }//end of else
                                                    
                                                    $updateSql = "UPDATE vi_contacts_es
                                                                    SET vi_es_list_id = '$listsId'
                                                                    WHERE ".$relatedContactLeadId." and vi_es_name = 'ActiveCampaigns' AND deleted = 0";
                                                    $updateDataResult = $GLOBALS['db']->query($updateSql);
                                                }//end of if
                                            }
                                        }
                                    }
                                }//end of if
                            }//end of if
                        }
                    }
                }//end of foreach
            }//end of if
        }//end of if

        if($esActive == "ActiveCampaigns" && $currentModuleName == "Accounts"){
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;
                            $finalSuiteArray = array('account' => $mappingFields[$suitecrmRecordId]);
                            $field = getActiveCampaignsAccountsCustomFields($esActive);
                            
                            $sql = "SELECT * FROM vi_accounts_es WHERE vi_es_name = '$esActive' AND  vi_suitecrm_account_id = '$suitecrmRecordId' AND deleted = 0";
                            $rowCheckResult = $GLOBALS['db']->fetchOne($sql);
                            $viEsAccountId = $accountId = '';
                            if(!empty($rowCheckResult)){
                                $viEsAccountId = $rowCheckResult['vi_es_account_id'];
                                $accountId = $rowCheckResult['id'];
                            }//end of if

                            //fetch all records and if there is no records then add directly
                            $res = syncESData("/api/3/accounts","GET",$esActive,"");
                            $checkResponse = (array)json_decode($res);
                            
                            if(empty($checkResponse['accounts'])){
                                //no records. add new organization
                                $insertResult = processESData("/api/3/accounts","POST",$finalSuiteArray,"Insert",$value->id,$currentModuleName,$esActive);
                                if($insertResult == "failure"){
                                    $failure[] = $insertResult;
                                }else{
                                    $insertedRecords[] = $insertResult;    
                                    $recordDetails = insertActiveCampaignsAccountsCustomFieldsValue($esActive, $insertResult, $finalSuiteArray, $field);
                                }
                            }else{
                                //there is records
                                $accountCustomFieldData = $customFieldsIdsData = array();
                                if($viEsAccountId != ""){
                                    $responseData = syncESData("/api/3/accounts/".$viEsAccountId,"GET",$esActive,$data = array());
                                    $jsonDecodeData = json_decode($responseData);
                                    
                                    if(isset($jsonDecodeData->message) && $jsonDecodeData->message != ''){
                                        $updateData = "UPDATE vi_accounts_es SET deleted = 1 WHERE id = '$accountId'";
                                        $updateResult = $GLOBALS['db']->query($updateData);
                                        //no records. add new organization
                                        $insertResult = processESData("/api/3/accounts","POST",$finalSuiteArray,"Insert",$value->id,$currentModuleName,$esActive);
                                        if($insertResult == "failure"){
                                            $failure[] = $insertResult;
                                        }else{
                                            $insertedRecords[] = $insertResult; 
                                            $recordDetails = insertActiveCampaignsAccountsCustomFieldsValue($esActive, $insertResult, $finalSuiteArray, $field);   
                                        }
                                    }else{
                                        //update organization                                            
                                        $updateResult = processESData("/api/3/accounts/".$viEsAccountId,"PUT",$finalSuiteArray,"Update",$value->id,$currentModuleName,$esActive);
                                        if($updateResult == "failure"){
                                            $failure[] = $updateResult;
                                        }else{
                                            $updatedRecords[] = $updateResult;    
                                            $resultAddNew = syncESData("/api/3/accountCustomFieldData", 'GET', $esActive, array());
                                            $result = (array)json_decode($resultAddNew);

                                            foreach ($result['accountCustomFieldData'] as $k => $customFieldDetails) {
                                                $customFieldsIdsData[] = array('customFieldsId' => $customFieldDetails->customFieldId, 'accountId' => $customFieldDetails->accountId, 'customFieldValuesId' => $customFieldDetails->id);
                                            }//end of foreach

                                            foreach ($field as $customFieldId => $fieldData) {
                                                if(array_key_exists($fieldData['field'], $finalSuiteArray['account'])){
                                                    foreach ($customFieldsIdsData as $keyVal => $fieldValData) {
                                                        if($customFieldId == $fieldValData['customFieldsId'] && $updateResult == $fieldValData['accountId']){
                                                            $customFieldValuesId = $fieldValData['customFieldValuesId'];

                                                            if($fieldData['type'] == 'date'){
                                                                $accountCustomFieldData['accountCustomFieldDatum']['fieldValue'] = date('Y-m-d H:i:s', strtotime($finalSuiteArray['account'][$fieldData['field']]));
                                                            }else if($fieldData['type'] == 'datetime' || $fieldData['type'] == 'datetimecombo'){
                                                                $accountCustomFieldData['accountCustomFieldDatum']['fieldValue'] = 
                                                                date(DATE_ISO8601, strtotime($finalSuiteArray['account'][$fieldData['field']]));
                                                            }else{
                                                                $accountCustomFieldData['accountCustomFieldDatum']['fieldValue'] = $finalSuiteArray['account'][$fieldData['field']];
                                                            }//end of else

                                                            $updateCustomFields = syncESData("/api/3/accountCustomFieldData/".$customFieldValuesId, 'PUT', $esActive, $accountCustomFieldData);
                                                            $updateCustomFieldsResult = (array)json_decode($updateCustomFields);
                                                        }//end of if
                                                    }//end of foreach
                                                }//end of if
                                            }//end of foreach
                                        }                                            
                                    }
                                }else{
                                    //add new organization
                                    $insertResult = processESData("/api/3/accounts","POST",$finalSuiteArray,"Insert",$value->id,$currentModuleName,$esActive);

                                    if($insertResult == "failure"){
                                        $failure[] = $insertResult;
                                    }else{
                                        $insertedRecords[] = $insertResult; 
                                        $recordDetails = insertActiveCampaignsAccountsCustomFieldsValue($esActive, $insertResult, $finalSuiteArray, $field);
                                    }
                                }
                            }
                        }
                    }
                }//end of foreach
            }//end of if
        }//end of if
        
        if($esActive == "SendInBlue" && ($currentModuleName == "Contacts" || $currentModuleName == "Leads")){
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;
                            $getContactLeadSql = "SELECT * FROM vi_contacts_es ";
                            if($currentModuleName == "Contacts"){
                                $getContactLeadSql .= "WHERE vi_suitecrm_contact_id = '$suitecrmRecordId'";
                            }else{
                                $getContactLeadSql .= "WHERE vi_suitecrm_lead_id = '$suitecrmRecordId'";
                            }//end of else
                            $getContactLeadSql .= " AND vi_es_name = '$esActive' AND vi_suitecrm_module = '$currentModuleName' AND deleted = 0";
                            $getContactLeadResult = $GLOBALS['db']->fetchOne($getContactLeadSql,false,'',false);

                            $viEsContactIddb = $contactId = '';
                            if(!empty($getContactLeadResult)){
                                $contactId = $getContactLeadResult['id'];
                                if($currentModuleName == "Contacts"){
                                    $viEsContactIddb = $getContactLeadResult['vi_es_contact_id'];
                                }else{
                                    $viEsContactIddb = $getContactLeadResult['vi_es_lead_id'];    
                                }//end of else
                            }//end of if

                            $attributes = array();
                            $res = syncESData("contacts","GET",$esActive,"");
                            $checkResponse = (array)json_decode($res);

                            if(!empty($checkResponse['contacts'])){
                                $finalInsertUpdateArray = array();
                                $emailAttr = "";
                                foreach ($mappingFields[$suitecrmRecordId] as $key => $value) {
                                    if($key != "email"){
                                        $attributes[strtoupper($key)] = $value;
                                    }else{
                                        $emailAttr = $value;
                                    }//end of else
                                }//end of foreach
                                if(!empty($attributes)){
                                    $finalInsertUpdateArray = array (
                                      'emailBlacklisted' => false,
                                      'smsBlacklisted' => false,
                                       'attributes' => $attributes,
                                    );
                                }else{
                                    $finalInsertUpdateArray = array (
                                      'emailBlacklisted' => false,
                                      'smsBlacklisted' => false,
                                       $attributes,
                                    );
                                }

                                if($viEsContactIddb == ""){
                                    $finalInsertUpdateArray['email'] = $emailAttr;
                                    
                                    //add new
                                    $insertResult = processESData("contacts","POST",$finalInsertUpdateArray,"Insert",$suitecrmRecordId,$currentModuleName,$esActive);
                                    if($insertResult == "failure"){
                                        $failure[] = $insertResult;
                                    }else{
                                        $insertedRecords[] = $insertResult;    
                                    }
                                }else{
                                    //update 
                                    $responseData = syncESData('contacts/'.$viEsContactIddb, "GET", $esActive, $data = array());
                                    $jsonDecodeData = json_decode($responseData);

                                    if(isset($jsonDecodeData->code) && $jsonDecodeData->code == 'document_not_found'){
                                        $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$contactId'";
                                        $updateResult = $GLOBALS['db']->query($updateData);
                                        
                                        $finalInsertUpdateArray['email'] = $emailAttr;
                                        $insertResult = processESData("contacts","POST",$finalInsertUpdateArray,"Insert",$suitecrmRecordId,$currentModuleName,$esActive);

                                        if($insertResult == "failure"){
                                            $failure[] = $insertResult;
                                        }else{
                                            $insertedRecords[] = $insertResult;    
                                        }
                                    }else{
                                        $updateListResponse = processESData("contacts/".$viEsContactIddb,"PUT",$finalInsertUpdateArray,"Update",$suitecrmRecordId,$currentModuleName,$esActive);
                                        if($updateListResponse == "failure"){
                                            $failure[] = $updateListResponse;
                                        }else{
                                            $updatedRecords[] = $updateListResponse;    
                                        }
                                    }
                                }
                            }
                        }
                    }
                }//end of foreach
            }//end of if
        }//end of if
        
        if($esActive == "SendInBlue" && $currentModuleName == "ProspectLists"){
            $addContactToListReqData = array();
            $listId = $listidString = "";
            
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;
                            $prospectListBean = BeanFactory::getBean("ProspectLists",$value->id);
                            $sql = "SELECT * FROM vi_segments_es WHERE vi_es_name = '$esActive' AND vi_suitecrm_segments_id = '$suitecrmRecordId' AND deleted = 0";
                            $checkResult = $GLOBALS['db']->fetchOne($sql);  
                            $segmentId = $esSegmentsId = "";
                            if(!empty($checkResult)){
                                $segmentId = $checkResult['id'];
                                $esSegmentsId = $checkResult['vi_es_segments_id'];
                            }//end of if

                            $res = syncESData("contacts/lists","GET",$esActive,$data=array());
                            $checkResponse = (array)json_decode($res);

                            if(empty($checkResponse)){
                                //there is no list exist, add new list directly
                                $insertResult = processESData("contacts/lists","POST",$mappingFields[$suitecrmRecordId],"Insert",$suitecrmRecordId,$currentModuleName,$esActive);
                                if($insertResult == "failure"){
                                    $failure[] = $insertResult;
                                }else{
                                    $listId = $insertResult;
                                    $insertedRecords[] = $insertResult;    
                                }
                            }else{
                                //there are list exist in sendinblue so check
                                if($esSegmentsId == ""){
                                    //add new list
                                    $insertResult = processESData("contacts/lists","POST",$mappingFields[$suitecrmRecordId],"Insert",$suitecrmRecordId,$currentModuleName,$esActive);
                                    if($insertResult == "failure"){
                                        $failure[] = $insertResult;
                                    }else{
                                        $listId = $insertResult;
                                        $insertedRecords[] = $insertResult;    
                                    }
                                }else{
                                    $responseData = syncESData('contacts/lists/'.$esSegmentsId, "GET", $esActive, $data = array());
                                    $jsonDecodeData = json_decode($responseData);
                                    if(isset($jsonDecodeData->code) && $jsonDecodeData->code == 'document_not_found'){
                                        $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$segmentId'";
                                        $updateResult = $GLOBALS['db']->query($updateData);

                                        //add new list
                                        $insertResult = processESData("contacts/lists","POST",$mappingFields[$suitecrmRecordId],"Insert",$suitecrmRecordId,$currentModuleName,$esActive);
                                        if($insertResult == "failure"){
                                            $failure[] = $insertResult;
                                        }else{
                                            $listId = $insertResult;
                                            $insertedRecords[] = $insertResult;    
                                        }
                                    }else{
                                        //update list
                                        $updateResult = processESData("contacts/lists/".$esSegmentsId,"PUT",$mappingFields[$suitecrmRecordId],"Update",$suitecrmRecordId,$currentModuleName,$esActive);

                                        if($updateResult == "failure"){
                                            $failure[] = $updateResult;
                                        }else{
                                            $listId = $esSegmentsId;
                                            $updatedRecords[] = $updateResult;    
                                        }
                                    }
                                }
                            }

                            //Fetch Related Contacts From SuiteCRM
                            if($targetListSubpanelModule != ''){
                                if(isset($suitecrmContactsFields) && !empty($suitecrmContactsFields)){
                                    $relatedBean = BeanFactory::newBean($targetListSubpanelModule);
                                    $relatedTableName = $relatedBean->getTableName();
                                    if ($prospectListBean->load_relationship($relatedTableName)) {
                                        $relatedBeans = $prospectListBean->$relatedTableName->getBeans();
                                        $sea = new SugarEmailAddress;
                                        $contactsLeadBean = BeanFactory::getBean($targetListSubpanelModule);
                                        
                                        foreach ((array)$relatedBeans as $k => $val) {
                                            $primaryEmailAddress = $sea->getPrimaryAddress($contactsLeadBean,$val->id);
                                            foreach ($suitecrmContactsFields as $keyfield => $vfield) {
                                                if($vfield == "email1"){
                                                    $finalSuiteContactsLeadArray[$keyfield] = $primaryEmailAddress;
                                                }else{
                                                    $fieldValue = getFieldValue($vfield, $relatedBeans, $val, $targetListSubpanelModule, '');
                                                    $finalSuiteContactsLeadArray[$keyfield] = $fieldValue;    
                                                }
                                            }

                                            $finalContactsArrayInsert = $attributes = array();
                                            $emailAttr = "";
                                            foreach ($finalSuiteContactsLeadArray as $key => $value) {
                                                if($key != "email"){
                                                    $attributes[strtoupper($key)] = $value;
                                                }else{
                                                    $emailAttr = $value;
                                                }
                                            }
                                            if(!empty($attributes)){
                                                $finalContactsArrayInsert = array (
                                                    'emailBlacklisted' => false,
                                                    'smsBlacklisted' => false,
                                                    'attributes' => $attributes,
                                                );
                                            }else{
                                                $finalContactsArrayInsert = array (
                                                    'emailBlacklisted' => false,
                                                    'smsBlacklisted' => false,
                                                    $attributes,
                                                );
                                            }
                                            $finalContactsArrayInsert['email'] = $emailAttr;
                                            if($targetListSubpanelModule == 'Contacts'){
                                                $relatedContactLeadId = "vi_suitecrm_contact_id = '$val->id'";
                                            }else{
                                                $relatedContactLeadId = "vi_suitecrm_lead_id = '$val->id'";
                                            }//end of else

                                            $contactLeadId = getEMSToolContactsData($listId, 'SendInBlue');

                                            //there are list exist in sendinblue so check
                                            $fetchEsContactId = "SELECT * FROM vi_contacts_es WHERE ".$relatedContactLeadId." AND vi_es_name = 'SendInBlue' AND deleted = 0";
                                            $selectResult = $GLOBALS['db']->fetchOne($fetchEsContactId,false,'',false);
                                            $contactId = $viSendInBlueContactId = '';
                                            if(!empty($selectResult)){
                                                $contactId = $selectResult['id'];
                                                $viSendInBlueContactId = $selectResult['vi_es_contact_id'];
                                            }//end of if

                                            $addContactToListReqData['emails'] = array(strtolower($val->email1));
                                            $suiteCRMLeadId = $suiteCRMContactId = '';
                                            if($targetListSubpanelModule == 'Leads'){
                                                $suiteCRMLeadId = $val->id;
                                            }else{
                                                $suiteCRMContactId = $val->id;
                                            }//end of else

                                            if(!empty($selectResult['vi_es_contact_id'])){
                                                //contact is already added just add this contact to list 
                                                if($listId != ""){
                                                    $responseData = syncESData('contacts/'.$viSendInBlueContactId, "GET", $esActive, $data = array());
                                                    $jsonDecodeData = json_decode($responseData);
                                                                                                         
                                                    if(isset($jsonDecodeData->code) && $jsonDecodeData->code == 'document_not_found'){
                                                        $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$contactId'";
                                                        $updateResult = $GLOBALS['db']->query($updateData);

                                                        $addNewContact = syncESData("contacts","POST",$esActive,$finalContactsArrayInsert);
                                                        $responseNewContact = json_decode($addNewContact);
                                                        $relatedEsContactId = $responseNewContact->id;

                                                        $rendomRecordId = create_guid();
                                                        $tableName = "vi_contacts_es";

                                                        $data = array('id' => $rendomRecordId,'vi_suitecrm_contact_id' => $suiteCRMContactId,'vi_es_contact_id' => $relatedEsContactId,'vi_suitecrm_lead_id' => $suiteCRMLeadId,'vi_es_name' => $esActive,'vi_es_list_id' => $listId,'vi_suitecrm_module' => $targetListSubpanelModule,'vi_es_lead_id' => '', 'deleted' => 0);
                                                        insertESRecord($tableName,$data);
                                                    }

                                                    syncESData("contacts/lists/".$listId."/contacts/add","POST",$esActive,$addContactToListReqData);
                                                    
                                                    if (strpos($selectResult['vi_es_list_id'], $listId) !== false) {
                                                        $listidString = $selectResult['vi_es_list_id'];
                                                    }else{
                                                        $listidString = $selectResult['vi_es_list_id'].",".$listId;
                                                    }

                                                    //update vi_es_list_id field
                                                    $updateListIdSql = "UPDATE vi_contacts_es
                                                            SET vi_es_list_id = '$listidString'
                                                            WHERE ".$relatedContactLeadId." and vi_es_name = 'SendInBlue' AND deleted = 0";
                                                    $GLOBALS['db']->query($updateListIdSql);
                                                    syncESData("contacts/".$viSendInBlueContactId, "PUT", $esActive, $finalContactsArrayInsert);
                                                    
                                                    removeContactsLeadFromListForAllEMSTool($contactLeadId, $viSendInBlueContactId, $insertContactLeadId, $updateContactLeadId, $esActive, $listId, $val->id, $targetListSubpanelModule, $planType="");
                                                }                           
                                            }else{
                                                $emailData['emails'] = array($emailAttr);
                                                $updateContactLeadId = addContactsLeadsFromList($contactLeadId, $targetListSubpanelModule, $val->id, $listId, $esActive, $emailData, $finalContactsArrayInsert, $insertContactLeadId, $updateContactLeadId);

                                                //add new contact
                                                $addNewContact = syncESData("contacts","POST",$esActive,$finalContactsArrayInsert);
                                                $responseNewContact = json_decode($addNewContact);
                                                
                                                if(empty($responseNewContact->code)){
                                                    $relatedEsContactId = $responseNewContact->id;
                                                    $insertContactLeadId[] = $relatedEsContactId;

                                                    //add this new contact to list
                                                    if($listId != ""){
                                                        syncESData("contacts/lists/".$listId."/contacts/add","POST",$esActive,$emailData);    
                                                        //enter this record in vi_contacts_es
                                                        $rendomRecordId = create_guid();
                                                        $tableName = "vi_contacts_es";
                                                        $data = array('id' => $rendomRecordId,'vi_suitecrm_contact_id' => $suiteCRMContactId,'vi_es_contact_id' => $relatedEsContactId,'vi_suitecrm_lead_id' => $suiteCRMLeadId,'vi_es_name' => $esActive,'vi_es_list_id' => $listId,'vi_suitecrm_module' => $targetListSubpanelModule,'vi_es_lead_id' => '', 'deleted' => 0);
                                                        insertESRecord($tableName,$data);
                                                    }//end of if
                                                }//end of if
                                            }
                                        }
                                    }
                                }//end of if
                            }//end of if
                        }//end of foreach
                    }//end of if
                }//end of foreach
            }//end of if
        }//end of if

        if($esActive == "SendGrid" && ($currentModuleName == "Contacts" || $currentModuleName == "Leads")){
            $planType = getPlanType($esActive);
            
            //go through contacts of suitecrm and add/update one by one in sendgrid
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $mappingfields = array();
                            $suiteContactId = $viESContactId = $errorMessage = '';

                            if($planType == 1) {
                                $suitecrmRecordId = $value->id;
                                if($currentModuleName == 'Contacts'){
                                    $contactLeadSuiteField = 'vi_suitecrm_contact_id';
                                }else{
                                    $contactLeadSuiteField = 'vi_suitecrm_lead_id';
                                }//end of else

                                $sql = "SELECT * FROM vi_contacts_es WHERE vi_es_name = '$esActive' AND $contactLeadSuiteField = '$suitecrmRecordId' AND vi_suitecrm_module = '$currentModuleName' AND deleted = 0";
                                $sqlResult = $GLOBALS['db']->fetchOne($sql);
                                
                                if(!empty($sqlResult)){
                                    $suiteContactId = $sqlResult['id'];
                                    if($currentModuleName == 'Contacts'){
                                        $viESContactId = $sqlResult['vi_es_contact_id'];
                                    }else{
                                        $viESContactId = $sqlResult['vi_es_lead_id'];
                                    }//end of else
                                }//end of if
                            
                                $syncId = '';
                                $bean = BeanFactory::getBean($currentModuleName, $suitecrmRecordId);
                                if($bean->email1 != ''){
                                    $emailId = $bean->email1;
                                    $queryData['query'] = "email LIKE '".$emailId."%'";
                                    $searchData = syncESData("marketing/contacts/search", "POST", $esActive, $queryData);
                                    $jsonDecodeData = json_decode($searchData);
                                    if($jsonDecodeData->contact_count == 0){
                                        $action = 'Insert';
                                    }else{
                                        $action = 'Update';
                                        $fields['id'] = $jsonDecodeData->result[0]->id;
                                        $syncId = $jsonDecodeData->result[0]->id;
                                    }//end of else     
                                }else{
                                    $action = 'Insert';
                                }//end of else
                            }//end of if
                            
                            $mappingfields[] = $mappingFields[$suitecrmRecordId];
                            if($planType == 1){
                                $operation = NMADDUPDATECONTACTS;
                                $data = array('list_ids'=>array(),'contacts'=> $mappingfields);
                                $method = "PUT";
                            }else{
                                $operation = LMADDUPDATECONTACTS;
                                $data = $mappingfields;
                                $method = "POST";
                            }

                            $response = syncESData($operation,$method,$esActive,$data);
                            $result = json_decode($response);
                            
                            if(empty($result->errors)){
                                if($planType == 1){
                                    if($action == 'Insert') {
                                        $fromRecordId = $result->job_id;
                                    } else {
                                        $fromRecordId = $syncId;
                                    }
                                }elseif($planType == 2) {
                                    $persistedRecipients = $result->persisted_recipients;
                                    $fromRecordId = $persistedRecipients[0];
                                    if(empty($result->errors)){
                                        if($result->new_count > 0){
                                            $action = 'Insert';
                                        }else {
                                            $action = 'Update';
                                        }
                                    }
                                }

                                if($action == 'Insert'){
                                    $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$suiteContactId'";
                                    $updateResult = $GLOBALS['db']->query($updateData);
                                    //insert
                                    $insertedRecords[] = syncEsLog('VI_EmailSoftwareIntegartionSyncLog','SendGrid',$value->id,$currentModuleName,"",$action,$fromRecordId);
                                    
                                    $randomid = create_guid(); 
                                    if($currentModuleName == "Contacts"){
                                        $data = array('id' => $randomid,'vi_suitecrm_contact_id' => $value->id,'vi_es_contact_id' => $fromRecordId,'vi_suitecrm_lead_id' => '','vi_es_name' => $esActive,'vi_es_list_id' => '','vi_suitecrm_module' => $currentModuleName,'vi_es_lead_id' => '', 'deleted' => 0);
                                    }else{
                                        $data = array('id' => $randomid,'vi_suitecrm_contact_id' => '','vi_es_contact_id' => '','vi_suitecrm_lead_id' => $value->id,'vi_es_name' => $esActive,'vi_es_list_id' => '','vi_suitecrm_module' => $currentModuleName,'vi_es_lead_id' => $fromRecordId, 'deleted' => 0);
                                    }
                                    insertESRecord("vi_contacts_es",$data);                            
                                }else{
                                    //update
                                    $updatedRecords[] = syncEsLog('VI_EmailSoftwareIntegartionSyncLog','SendGrid',$value->id,$currentModuleName,"",$action,$fromRecordId);    
                                }
                            }else{
                                foreach ($result->errors as $keyError => $valueError) {
                                    $errorMessage = $valueError->message;
                                    $failure[] = syncEsLog('VI_EmailSoftwareIntegartionSyncLog','SendGrid',$value->id,$currentModuleName,$errorMessage,"",'');
                                }
                            }
                        }//end of foreach
                    }//end of if
                }//end of foreach
            }//end of if
        }//end of if

        if($esActive == "SendGrid" && $currentModuleName == "ProspectLists"){
            $result = $finalSendGridContactList = $recipients = $mapArray = $recipientsFromSendGrid = array();
            $planType = getPlanType($esActive);

            if($planType == 1){
                $operationList = NMADDUPDATELISTS;
            }else{
                $operationList = LMADDUPDATELISTS;
            }

            //To retrieve all lists
            $result = syncESData($operationList,"GET",$esActive,$data=array());
            $sendGridResponse = (array)json_decode($result);
            
            if($planType == 1){
                $sendGridContactList = (array)$sendGridResponse['result'];
                $list = '';
                $cotactSample = '?contact_sample=true';
            }else{
                $sendGridContactList = (array)$sendGridResponse['lists'];
                $list = '/recipients';
                $cotactSample = ''; 
            }
                    
            foreach ((array)$sendGridContactList as $key => $value) {
                $finalSendGridContactList[] = (array)$value;
            }

            foreach ($finalSendGridContactList as $key => $value) {
                $relatedContactLeadId = $value['id'];
                $url = $operationList."/".$relatedContactLeadId.$list.$cotactSample;
                $method = "GET";
                $resultForRecipents = syncESData($url,$method,$esActive,$data=array());             
                $sendGridResponseForRecipents = (array)json_decode($resultForRecipents);

                if($planType == 1){
                    $recipientCount = $sendGridResponseForRecipents['contact_count'];
                    $finalSendGridContactList[$key]['contact_sample'] = (array)$sendGridResponseForRecipents['contact_sample'];
                }else{
                    $recipientCount = $sendGridResponseForRecipents['recipient_count'];
                    $finalSendGridContactList[$key]['recipients'] = (array)$sendGridResponseForRecipents['recipients'];
                }
            }

            if($planType == 1){
                $contactName = "contact_sample";
            }else{
                $contactName = "recipients";
            }

            foreach ($finalSendGridContactList as $key => $value) {
                $listId = $value['id'];
                $finalArray = array();
                foreach ($value as $fieldk => $fieldv) { 
                    if($fieldk != $contactName){
                        $finalArray[$fieldk] = $fieldv;    
                    }elseif($fieldk == $contactName){
                        foreach ((array)$fieldv as $reck => $recv) {
                            $recipientsArray = array();
                            foreach ((array)$fieldv as $kv => $vv) {
                                if($kv == "id"){
                                    $contactID = $vv;
                                    $recipientsArray["id"] = $vv;
                                }
                                if(is_array($vv)){
                                    foreach ((array)$vv as $kfields => $vfields) {
                                        if(array_key_exists(strtolower($vfields->name),$suitecrmContactsFields)){
                                            $customFields[$suitecrmContactsFields[strtolower($vfields->name)]][] = $vfields->value;
                                            $recipientsArray[$suitecrmContactsFields[strtolower($vfields->name)]] = $vfields->value;
                                        }
                                    }
                                }else{
                                    if(array_key_exists($kv, $suitecrmContactsFields)){
                                        $fields[strtolower($kv)][] = $vv;
                                        $recipientsArray[$kv]= $vv;
                                    }    
                                }
                            }
                            $recipientsArray['viem_name_c'] = $esActive;
                            $recipientsArray['viem_list_id_c'] = $value['id'];
                            $finalArray[$contactName][$recv->email] = $recipientsArray;
                            $finalArray['viem_name_c'] = $esActive;
                            $finalArray['assigned_user_id'] = $currentLoggedInUserID;
                        }
                    }    
                }
                $mapArray[$listId] = $finalArray;
            }
            
            foreach ($mapArray as $key => $value) {
                if($planType == 1){
                    if($value['contact_count'] > 0){
                        $recipientsFromSendGrid = $value['contact_sample'];
                    }
                }else{
                    if($value['recipient_count'] > 0){
                        $recipientsFromSendGrid = $value['recipients'];
                    }
                } 
            }

            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                        foreach ($obj as $k => $valueList) {
                            $mappingfields = $finalSuiteArray = $recipientsFromSendGrid = $contactList = array();
                            $prospectListBean = BeanFactory::getBean("ProspectLists",$valueList->id);
                            $listName = $valueList->name;
                            
                            $finalSuiteArray['prospectListRecordId'] = $valueList->id;
                            foreach ($suitecrmFields as $keyfield => $vfield) {
                                $fieldDef = $prospectListBean->field_defs[$vfield];
                                if($_REQUEST['action'] == 'MassUpdate' && isset($_REQUEST[$vfield]) && !empty($_REQUEST[$vfield])){
                                    $fieldVal = $_REQUEST[$vfield];
                                }else{
                                    $fieldVal = $prospectListBean->$vfield;
                                }//end of else

                                if($fieldDef['type'] == 'multienum'){
                                    $optionList = unencodeMultienum($fieldVal);
                                    if (empty($optionList)) {
                                        $fieldValue = '';
                                    }//end of if
                                    $fieldValue = '||' . implode('||', $optionList) . '||';
                                    $finalSuiteArray[$keyfield] =  $fieldValue;
                                }else{
                                  $finalSuiteArray[$keyfield] =  $fieldVal;
                                }//end of else
                            }//end of foreach

                            if(empty($sendGridContactList)){
                                //sendGrid has no list create new list
                                $arrayRequest = array('name' => $listName);
                                if($planType == 1){
                                    $operationList = NMADDUPDATELISTS;
                                }else{
                                    $operationList = LMADDUPDATELISTS;
                                }
                                
                                $insertListResponse = processESData($operationList,"POST",$arrayRequest,"Insert",$valueList->id,$currentModuleName,$esActive);
                                if($insertListResponse == "failure"){
                                    $failure[] = $insertListResponse;
                                }else{
                                    $insertedRecords[] = $insertListResponse;    
                                }
                                $finalSuiteArray['id'] = $insertListResponse;

                                if($targetListSubpanelModule != ''){
                                    if(isset($suitecrmContactsFields) && !empty($suitecrmContactsFields)){
                                        $relatedBean = BeanFactory::newBean($targetListSubpanelModule);
                                        $relatedTableName = $relatedBean->getTableName();
                                        if ($prospectListBean->load_relationship($relatedTableName)) {
                                            $relatedBeans = $prospectListBean->$relatedTableName->getBeans();
                                            $recipientsSuiteArray = array();

                                            foreach ((array)$relatedBeans as $k => $val) {
                                                if($planType == 1) {
                                                    $recipientsSuiteArray['cid'] = $k;
                                                }
                                                foreach ($suitecrmContactsFields as $kfield => $valfield) {
                                                    $fieldValue = getFieldValue($valfield, $relatedBeans, $val, $targetListSubpanelModule, '');
                                                    $recipientsSuiteArray[$kfield] = $fieldValue;
                                                }
                                                if($planType == 1){
                                                    $finalSuiteArray['contact_sample'][] = $recipientsSuiteArray;     
                                                }else{
                                                    $finalSuiteArray['recipients'][] = $recipientsSuiteArray;     
                                                }
                                            }

                                            if($planType == 1){
                                                $finalSuiteArray['contact_count'] = count($relatedBeans);
                                            }else{
                                                $finalSuiteArray['recipient_count'] = count($relatedBeans);
                                            }
                                        }
                                        $mappingfields[] = $finalSuiteArray;
                                
                                        foreach ($mapArray as $key => $value) {
                                            if($planType == 1){
                                                if($value['contact_count'] > 0){
                                                    $recipientsFromSendGrid = $value['contact_sample'];
                                                }
                                            }else{
                                                if($value['recipient_count'] > 0){
                                                    $recipientsFromSendGrid = $value['recipients'];
                                                }
                                            }
                                        }
                                        
                                        foreach ($mappingfields as $key => $value) {
                                            if($planType == 1){
                                                if(isset($value['contact_sample']) && !empty($value['contact_sample'])){
                                                    $contactList = $value['contact_sample'];
                                                }//end of if
                                            }else{
                                                if(isset($value['recipients']) && !empty($value['recipients'])){
                                                    $contactList = $value['recipients'];
                                                }//end of if
                                            }
                                            relatedContactsData($contactList,$recipientsFromSendGrid,$esActive,$value['id'],'SendGrid', $targetListSubpanelModule, $value['id'], $insertContactLeadId, $updateContactLeadId);
                                        }
                                    }//end of if
                                }//end of if
                            }else{
                                $fetchListId = "SELECT * FROM vi_segments_es WHERE vi_suitecrm_segments_id = '$valueList->id' and vi_es_name = 'SendGrid' AND deleted = 0";
                                $fetchListIDResult = $GLOBALS['db']->fetchOne($fetchListId,false,'',false);
                                $viEsSegmentsId = $segmentTableId = '';
                                if(!empty($fetchListIDResult)){
                                    $viEsSegmentsId = $fetchListIDResult['vi_es_segments_id'];
                                    $segmentTableId = $fetchListIDResult['id'];
                                }//end of if
                                $finalSuiteArray['id'] = $viEsSegmentsId;
                                $finalSuiteArray['segmentId'] = $segmentTableId;

                                $relatedBean = BeanFactory::newBean($targetListSubpanelModule);
                                $relatedTableName = $relatedBean->getTableName();
                                if ($prospectListBean->load_relationship($relatedTableName)) {
                                    $relatedBeans = $prospectListBean->$relatedTableName->getBeans();
                                    $recipientsSuiteArray = array();
                                    foreach ((array)$relatedBeans as $k => $val) {
                                        if($planType == 1) {
                                            $recipientsSuiteArray['cid'] = $k;
                                        }
                                        
                                        foreach ($suitecrmContactsFields as $kfield => $valfield) {
                                            $fieldValue = getFieldValue($valfield, $relatedBeans, $val, $targetListSubpanelModule, '');
                                            $recipientsSuiteArray[$kfield] = $fieldValue;
                                        }
                                        
                                        if($planType == 1){
                                            $finalSuiteArray['contact_sample'][] = $recipientsSuiteArray;     
                                        }else{
                                            $finalSuiteArray['recipients'][] = $recipientsSuiteArray;     
                                        }
                                    }
                                    if($planType == 1){
                                        $finalSuiteArray['contact_count'] = count($relatedBeans);
                                    }else{
                                        $finalSuiteArray['recipient_count'] = count($relatedBeans);
                                    }
                                }

                                $mappingfields[] = $finalSuiteArray;
                                foreach ($mapArray as $key => $value) {
                                    if($planType == 1){
                                        if($value['contact_count'] > 0){
                                            $recipientsFromSendGrid = $value['contact_sample'];
                                        }
                                    }else{
                                        if($value['recipient_count'] > 0){
                                            $recipientsFromSendGrid = $value['recipients'];
                                        }
                                    }   
                                }
                                
                                foreach ($mappingfields as $key => $value) {
                                    $insertedRecordsId = $contactList = array();
                                    $arrayRequest = array('name' => $listName);

                                    if($value['id'] != '' && array_key_exists($value['id'], $mapArray)){
                                        //update list
                                        if($planType == 1){
                                            $operationList = NMADDUPDATELISTS."/";
                                        }else{
                                            $operationList = LMADDUPDATELISTS."/";
                                        }
                                        $updateListResponse = processESData($operationList.$value['id'],"PATCH",$arrayRequest,"Update",$value['prospectListRecordId'],$currentModuleName,$esActive);
                                        if($updateListResponse == "failure"){
                                            $failure[] = $updateListResponse;
                                        }else{
                                            $updatedRecords[] = $updateListResponse;    
                                        }
                                    }else{
                                        if($value['id'] != ''){
                                            $segmentId = $value['segmentId'];
                                            $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$segmentId'";
                                            $updateResult = $GLOBALS['db']->query($updateData);
                                        }
                                        if($planType == 1){
                                            $operationList = NMADDUPDATELISTS;
                                        }else{
                                            $operationList = LMADDUPDATELISTS;
                                        }
                                        //add new list
                                        $insertListResponse = processESData($operationList,"POST",$arrayRequest,"Insert",$value['prospectListRecordId'],$currentModuleName,$esActive);
                                        if($insertListResponse == "failure"){
                                            $failure[] = $insertListResponse;
                                        }else{
                                            $insertedRecords[] = $insertListResponse;
                                            $insertedRecordsId[] = $insertListResponse;    
                                        }
                                    }
                                    
                                    //For Related Contacts                                        
                                    if($planType == 1){
                                        $recipientCount = $value['contact_count'];
                                        if(isset($value['contact_sample'])){
                                            $contactList = $value['contact_sample'];    
                                        }
                                    }else{
                                        $recipientCount = $value['recipient_count']; 
                                        $contactList = $value['recipients']; 
                                    }
                                    
                                    if($recipientCount > 0){
                                        if(empty($fetchListIDResult)){
                                            foreach ($insertedRecordsId as $ik => $val) {
                                                relatedContactsData($contactList,$recipientsFromSendGrid,$esActive,array($val),'SendGrid', $targetListSubpanelModule, $value['id'], $insertContactLeadId, $updateContactLeadId);
                                            }//end of foreach
                                        }else{
                                            if($planType == 1){
                                                relatedContactsData($contactList,$recipientsFromSendGrid,$esActive,array($value['id']),'SendGrid', $targetListSubpanelModule, $value['id'], $insertContactLeadId, $updateContactLeadId);
                                            }else{
                                                relatedContactsData($contactList,$recipientsFromSendGrid,$esActive,$value['id'],'SendGrid', $targetListSubpanelModule, $value['id'], $insertContactLeadId, $updateContactLeadId);
                                            }//end of else
                                        }
                                    }
                                }
                            }
                        }
                    }
                }//end of foreach
            }//end of if
        }//end of if

        if($esActive == "Mautic" && $currentModuleName == "AOS_Products"){
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;
                            $viEsAssetsId = $assetsId = "";
                            $sql = "SELECT * FROM vi_assets_es WHERE vi_es_name = '$esActive' AND vi_suitecrm_assets_id = '$suitecrmRecordId' AND deleted = 0";
                            $checkResult = $GLOBALS['db']->fetchOne($sql);  
                            if(!empty($checkResult)){
                                $viEsAssetsId = $checkResult['vi_es_assets_id'];
                                $assetsId = $checkResult['id'];
                            }//end of if

                            if($viEsAssetsId == ""){
                                //add new asset
                                $insertListResponse = processESData("/api/assets/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                if($result == "failure"){
                                    $failure[] = $insertListResponse;
                                }else{
                                    $insertedRecords[] = $insertListResponse;    
                                }
                            }else{
                                $result = syncESData("/api/assets/".$viEsAssetsId,"GET",$esActive,"");
                                $response = (array)json_decode($result);

                                if(empty($response['errors'])){
                                    //Update contact from mautic
                                    $updateResult = processESData("/api/assets/".$viEsAssetsId."/edit","PATCH",$mappingFields[$suitecrmRecordId],"Update",$value->id,$currentModuleName,$esActive);
                                    if($updateResult == "failure"){
                                        $failure[] = $updateResult;
                                    }else{
                                        $updatedRecords[] = $updateResult;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_assets_es SET deleted = 1 WHERE id = '$assetsId'";
                                    $updateResult = $GLOBALS['db']->query($updateData);

                                    //add new asset    
                                    $insertListResponse = processESData("/api/assets/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                    if($result == "failure"){
                                        $failure[] = $insertListResponse;
                                    }else{
                                        $insertedRecords[] = $insertListResponse;    
                                    }  
                                }
                            }
                        }
                    }//end of if
                }//end of foreach
            }//end of if            
        }//end of if

        if($esActive == "Mautic" && ($currentModuleName == "Contacts" || $currentModuleName == "Leads")){
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;
                            $getContactLeadSql = "SELECT * FROM vi_contacts_es ";
                            if($currentModuleName == "Contacts"){
                                $getContactLeadSql .= "WHERE vi_suitecrm_contact_id = '$suitecrmRecordId'";
                            }else{
                                $getContactLeadSql .= "WHERE vi_suitecrm_lead_id = '$suitecrmRecordId'";
                            }//end of else
                            $getContactLeadSql .= " AND vi_es_name = '$esActive' AND vi_suitecrm_module = '$currentModuleName' AND deleted = 0";
                            $getContactLeadResult = $GLOBALS['db']->fetchOne($getContactLeadSql,false,'',false);

                            $viEsContactId = $contactESId = '';
                            if(!empty($getContactLeadResult)){
                                $contactESId = $getContactLeadResult['id'];
                                if($currentModuleName == "Contacts"){
                                    $viEsContactId = $getContactLeadResult['vi_es_contact_id'];
                                }else{
                                    $viEsContactId = $getContactLeadResult['vi_es_lead_id'];    
                                }//end of else
                            }//end of if

                            if($viEsContactId == ""){
                                //first check record already exist or not
                                $res = syncESData("/api/contacts/new","POST",$esActive,$mappingFields[$suitecrmRecordId]);
                                $checkResponse = (array)json_decode($res);
                                $esIdForCheck = $checkResponse['contact']->id;
                                if($esIdForCheck != ""){
                                    $sqlFetchAllESIDs = "SELECT * FROM vi_contacts_es WHERE $viEsContactId = '$esIdForCheck' AND deleted = 0";
                                    $checkResultFetchAllESIDs = $GLOBALS['db']->query($sqlFetchAllESIDs); 
                                    $selectResultData = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($sqlFetchAllESIDs));

                                    if(!empty($selectResultData)){
                                        //update 
                                        $updateResult = processESData("/api/contacts/new","POST",$mappingFields[$suitecrmRecordId],"Update",$value->id,$currentModuleName,$esActive);
                                        if($updateResult == "failure"){
                                            $failure[] = $updateResult;
                                        }else{
                                            $updatedRecords[] = $updateResult;    
                                        }
                                    }else{
                                        //Add new contact in Mautic
                                        $insertResult = processESData("/api/contacts/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                        if($insertResult == "failure"){
                                            $failure[] = $insertResult;
                                        }else{
                                            $insertedRecords[] = $insertResult;    
                                        }
                                    }
                                }
                            }else{
                                $result = syncESData("/api/contacts/".$viEsContactId,"GET",$esActive,"");
                                $response = (array)json_decode($result);

                                if(empty($response['errors'])){
                                    //Update contact from mautic
                                    $updateResult = processESData("/api/contacts/".$viEsContactId."/edit","PATCH",$mappingFields[$suitecrmRecordId],"Update",$value->id,$currentModuleName,$esActive);
                                    if($updateResult == "failure"){
                                        $failure[] = $updateResult;
                                    }else{
                                        $updatedRecords[] = $updateResult;    
                                    }                        
                                }else{
                                    foreach ($response['errors'] as $keyError => $valueError) {
                                        if($valueError->message == "Item was not found."){
                                            $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$contactESId'";
                                            $updateResult = $GLOBALS['db']->query($updateData);
                                            //Add new contact in Mautic
                                            $insertResult = processESData("/api/contacts/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                            if($insertResult == "failure"){
                                                $failure[] = $insertResult;
                                            }else{
                                                $insertedRecords[] = $insertResult;    
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }//end of foreach
            }//end of if
        }//end of if

        if($esActive == "Mautic" && $currentModuleName == "Accounts"){
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;
                            $sqlCheck = "SELECT * FROM vi_accounts_es WHERE vi_es_name = '$esActive' AND vi_suitecrm_account_id = '$suitecrmRecordId' AND deleted = 0";
                            $selectResult = $GLOBALS['db']->fetchOne($sqlCheck,false,'',false);
                            $viEsAccountId = $accountId = '';
                            if(!empty($selectResult)){
                                $viEsAccountId = $selectResult['vi_es_account_id'];
                                $accountId = $selectResult['id'];
                            }//end of if

                            if($viEsAccountId == ""){
                                //add new account
                                $insertResult = processESData("/api/companies/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                if($insertResult == "failure"){
                                    $failure[] = $insertResult;
                                }else{
                                    $insertedRecords[] = $insertResult;    
                                }
                            }else{
                                $result = syncESData("/api/companies/".$viEsAccountId,"GET",$esActive,"");
                                $response = (array)json_decode($result);

                                if(empty($response['errors'])){
                                    //Update contact from mautic
                                    $updateResult = processESData("/api/companies/".$viEsAccountId."/edit","PATCH",$mappingFields[$suitecrmRecordId],"Update",$value->id,$currentModuleName,$esActive);
                                    if($updateResult == "failure"){
                                        $failure[] = $updateResult;
                                    }else{
                                        $updatedRecords[] = $updateResult;    
                                    }                        
                                }else{
                                    foreach ($response['errors'] as $keyError => $valueError) {
                                        if($valueError->message == "Item was not found."){
                                            $updateData = "UPDATE vi_accounts_es SET deleted = 1 WHERE id = '$accountId'";
                                            $updateResult = $GLOBALS['db']->query($updateData);
                                            //Add new contact in Mautic
                                            $insertResult = processESData("/api/companies/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                            if($insertResult == "failure"){
                                                $failure[] = $insertResult;
                                            }else{
                                                $insertedRecords[] = $insertResult;    
                                            }
                                        }
                                    }
                                } 
                            }
                        }
                    }//end of if
                }//end of foreach
            }//end of if
        }//end of if

        if($esActive == "Mautic" && $currentModuleName == "Campaigns"){
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        foreach ($obj as $k => $value) {
                            $suitecrmRecordId = $value->id;                            
                            $sql = "SELECT * FROM vi_campaigns_es WHERE vi_es_name = '$esActive' AND vi_suitecrm_campaigns_id = '$suitecrmRecordId' AND deleted = 0";
                            $checkResult = $GLOBALS['db']->fetchOne($sql);
                            $viEsCampaignId = $campaignId = '';
                            if(!empty($checkResult)){
                                $viEsCampaignId = $checkResult['vi_es_campaign_id'];
                                $campaignId = $checkResult['id'];
                            }//end of if

                            if($viEsCampaignId == ""){
                                //add new account
                                $insertResult = processESData("/api/campaigns/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                if($insertResult == "failure"){
                                    $failure[] = $insertResult;
                                }else{
                                    $insertedRecords[] = $insertResult;    
                                }
                            }else{
                                $result = syncESData("/api/campaigns/".$viEsCampaignId,"GET",$esActive,"");
                                $response = (array)json_decode($result);

                                if(empty($response['errors'])){
                                    //Update contact from mautic
                                    $updateResult = processESData("/api/campaigns/".$viEsCampaignId."/edit","PATCH",$mappingFields[$suitecrmRecordId],"Update",$value->id,$currentModuleName,$esActive);
                                    if($updateResult == "failure"){
                                        $failure[] = $updateResult;
                                    }else{
                                        $updatedRecords[] = $updateResult;    
                                    }                        
                                }else{
                                    foreach ($response['errors'] as $keyError => $valueError) {
                                        if($valueError->message == "Item was not found."){
                                            $updateData = "UPDATE vi_campaigns_es SET deleted = 1 WHERE id = '$campaignId'";
                                            $updateResult = $GLOBALS['db']->query($updateData);
                                            //Add new contact in Mautic
                                            $insertResult = processESData("/api/campaigns/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                            if($insertResult == "failure"){
                                                $failure[] = $insertResult;
                                            }else{
                                                $insertedRecords[] = $insertResult;    
                                            }
                                        }
                                    }
                                } 
                            }
                        }
                    }
                }//end of foreach
            }//end of if
        }//end of if

        if($esActive == "Mautic" && $currentModuleName == "ProspectLists"){
            $sea = new SugarEmailAddress;
            if(isset($moduleList) && !empty($moduleList)){
                foreach ($moduleList as $key => $obj) {
                    if(!empty($obj) && is_array($obj)){
                        $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                        foreach ($obj as $k => $value) {
                            $prospectListBean = BeanFactory::getBean("ProspectLists",$value->id);
                            $suitecrmRecordId = $value->id;
                            $sql = "SELECT * FROM vi_segments_es WHERE vi_es_name = '$esActive' AND vi_suitecrm_segments_id = '$suitecrmRecordId' AND deleted = 0";
                            $checkResult = $GLOBALS['db']->fetchOne($sql);
                            $viEsSegmentsId = $segmentESId = '';
                            if(!empty($checkResult)){
                                $viEsSegmentsId = $listId = $checkResult['vi_es_segments_id'];
                                $segmentESId = $checkResult['id'];
                            }//end of if
                            
                            //ADD / UPDATE SEGMENTS
                            if($viEsSegmentsId == ""){
                                //add new segment
                                $insertResult = processESData("/api/segments/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                if($insertResult == "failure"){
                                    $failure[] = $insertResult;
                                }else{
                                    $sql = "SELECT viem_to_record FROM vi_emailsoftwareintegrationsynclog WHERE viem_to_record = '$insertResult'";
                                    $selectResult = $GLOBALS['db']->fetchOne($sql,false,'',false);
                                    if(!empty($selectResult['viem_to_record'])){
                                        $segmentsId = $listId = $selectResult['viem_to_record']; 
                                    }
                                    $insertedRecords[] = $insertResult;
                                }
                            }else{
                                $result = syncESData("/api/segments/".$viEsSegmentsId,"GET",$esActive,$data=array());
                                $response = (array)json_decode($result);

                                if(empty($response['errors'])){
                                    //Update segment from mautic
                                    $updateResult = processESData("/api/segments/".$viEsSegmentsId."/edit","PATCH",$mappingFields[$suitecrmRecordId],"Update",$value->id,$currentModuleName,$esActive);
                                    if($updateResult == "failure"){
                                        $failure[] = $updateResult;
                                    }else{
                                        $updatedRecords[] = $updateResult;    
                                    }
                                }else{
                                    foreach ($response['errors'] as $keyError => $valueError) {
                                        if($valueError->message == "Item was not found."){
                                            $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$segmentESId'";
                                            $updateResult = $GLOBALS['db']->query($updateData);
                                            //add new segment in Mautic
                                            $insertResult = processESData("/api/segments/new","POST",$mappingFields[$suitecrmRecordId],"Insert",$value->id,$currentModuleName,$esActive);
                                            if($insertResult == "failure"){
                                                $failure[] = $insertResult;
                                            }else{
                                                $insertedRecords[] = $insertResult;    
                                            }
                                        }
                                    }
                                }
                            }//end of else

                            //THEN ADD RELATED CONTACTS
                            if($targetListSubpanelModule != ''){
                                if(isset($suitecrmContactsFields) && !empty($suitecrmContactsFields)){
                                    $relatedBean = BeanFactory::newBean($targetListSubpanelModule);
                                    $relatedTableName = $relatedBean->getTableName();

                                    if ($prospectListBean->load_relationship($relatedTableName)) {
                                        $relatedBeans = $prospectListBean->$relatedTableName->getBeans();
                                    
                                        foreach ((array)$relatedBeans as $k => $val) {
                                            if($targetListSubpanelModule == 'Contacts'){
                                                $relatedContactLeadId = "vi_suitecrm_contact_id = '$val->id'";
                                            }else{
                                                $relatedContactLeadId = "vi_suitecrm_lead_id = '$val->id'";
                                            }//end of else

                                            $fetchEsContactId = "SELECT * FROM vi_contacts_es WHERE ".$relatedContactLeadId." and vi_es_name = 'Mautic' AND deleted = 0";
                                            $selectResult = $GLOBALS['db']->fetchOne($fetchEsContactId,false,'',false);
                                            $contactLeadId = getEMSToolContactsData($listId, 'Mautic');

                                            if(!empty($selectResult['vi_es_contact_id'])){
                                                $esContactId = $selectResult['vi_es_contact_id'];
                                                $resultSeg = syncESData("/api/segments/".$listId."/contact/".$esContactId."/add", "POST", $esActive, $data=array());

                                                removeContactsLeadFromListForAllEMSTool($contactLeadId, $esContactId, $insertContactLeadId, $updateContactLeadId, $esActive, $listId, $val->id, $targetListSubpanelModule, $planType="");

                                            }else{
                                                //this contact is not syn, so first add this contact to mautic contacts and then add to related list
                                                $primaryEmailAddress = $sea->getPrimaryAddress($relatedBean,$val->id);
                                                foreach ($suitecrmContactsFields as $keyfield => $vfield) {
                                                    if($vfield == "email1"){
                                                        $finalSuiteContactsLeadArray[$keyfield] = $primaryEmailAddress;
                                                    }else{
                                                        $fieldValue = getFieldValue($vfield, $relatedBean, $val, $targetListSubpanelModule, '');
                                                        $finalSuiteContactsLeadArray[$keyfield] = $fieldValue;
                                                    }
                                                }

                                                $updateContactLeadId = addContactsLeadsFromList($contactLeadId, $targetListSubpanelModule, $val->id, $listId, $esActive, $updateRecordData=array(), $finalSuiteContactsLeadArray, $insertContactLeadId, $updateContactLeadId);

                                                $resultRelatedContacts = syncESData("/api/contacts/new","POST",$esActive,$finalSuiteContactsLeadArray);
                                                $responseRelatedContacts = (array)json_decode($resultRelatedContacts);
                                                $relatedContactId = $responseRelatedContacts['contact']->id;

                                                $resultSeg = syncESData("/api/segments/".$listId."/contact/".$relatedContactId."/add", "POST", $esActive, $data=array());

                                                $insertContactLeadId[] = $relatedContactId;
                                                $rendomRecordId = create_guid();
                                                $tableName = "vi_contacts_es";

                                                $suiteCRMLeadId = $suiteCRMContactId = '';
                                                if($targetListSubpanelModule == 'Leads'){
                                                    $suiteCRMLeadId = $val->id;
                                                }else{
                                                    $suiteCRMContactId = $val->id;
                                                }//end of else

                                                $data = array('id' => $rendomRecordId,'vi_suitecrm_contact_id' => $suiteCRMContactId,'vi_es_contact_id' => $relatedContactId,'vi_suitecrm_lead_id' => $suiteCRMLeadId,'vi_es_name' => $esActive,'vi_es_list_id' => $segmentsId,'vi_suitecrm_module' => $targetListSubpanelModule,'vi_es_lead_id' => '', 'deleted' => 0);
                                                insertESRecord($tableName,$data);
                                            }
                                        }
                                    }
                                }//end of if
                            }//end of if 
                        }//end of foreach
                    }//end of foreach
                }//end of foreach
            }//end of if
        }//end of if
    }//end of function
}
?>