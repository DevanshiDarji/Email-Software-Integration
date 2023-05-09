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
require_once("custom/include/VIEsIntegrationConfig.php");
require_once("custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php");
class VIAddSynchronizeData{
    public function __construct(){
        $this->addSynchronizeData();
    }

    public function addSynchronizeData(){
        global $current_user, $timedate;
        $currentLoggedInUserID = $current_user->id;

        parse_str($_POST['val'], $formData);
        $syncSoftware = $formData['sync_software'];
        $mappingModuleList = $formData['mapping_module_list'];
        $selData = "SELECT * FROM vi_module_mapping WHERE module_mapping_id = '$mappingModuleList' and email_software = '$syncSoftware' and deleted = 0";
        $selResult = $GLOBALS['db']->fetchOne($selData);

        if(!empty($selResult)){
            $suiteCRMFields = $suiteCRMContactsFields = array();
            $id = $selResult['module_mapping_id'];
            $esModule = $selResult['es_module'];
            $suitecrmModule = $selResult['suitecrm_module'];
            $targetListSubpanelModule = '';
            if($selResult['target_list_subpanel_module'] != ''){
                $targetListSubpanelModule = $selResult['target_list_subpanel_module'];
            }//end of if
            $status = $selResult['status'];
            $selMapping = "SELECT * FROM vi_integration_field_mapping WHERE module_mapping_id = '$id' and deleted = 0";
            $mappingResult = $GLOBALS['db']->query($selMapping);

            while($rowMapping = $GLOBALS['db']->fetchByAssoc($mappingResult)){
                if($syncSoftware == "ActiveCampaigns" || $syncSoftware == "SendInBlue"){
                    $sendGridFields = $rowMapping['es_module_fields'];
                }else{
                    $sendGridFields = strtolower($rowMapping['es_module_fields']);
                }            
                $suiteCRMFields[$sendGridFields] = $rowMapping['suitecrm_module_fields'];
            }
            if($suitecrmModule == "ProspectLists"){
                $selContactsMapping = "SELECT * FROM vi_integration_contacts_field_mapping WHERE module_mapping_id = '$id' and deleted = 0";
                $mappinContactsResult = $GLOBALS['db']->query($selContactsMapping);  
                while($rowContactsMapping = $GLOBALS['db']->fetchByAssoc($mappinContactsResult)){
                    if($syncSoftware == "ActiveCampaigns" || $syncSoftware == "SendInBlue"){
                        $sendGridContactsFields = $rowContactsMapping['sendgrid_contacts_module_fields'];
                    }else{
                        $sendGridContactsFields = strtolower($rowContactsMapping['sendgrid_contacts_module_fields']);
                    }
                    $suiteCRMContactsFields[$sendGridContactsFields] = $rowContactsMapping['suitecrm_contacts_module_fields'];
                }
            }

            if($status == "Active"){
                $failure = $insertedRecords = $updatedRecords = array();
            
                if($syncSoftware == "SendGrid" && ($suitecrmModule == "Contacts" || $suitecrmModule == "Leads")){
                    $planType = getPlanType($syncSoftware);
                    if($planType == 1){
                        $operation = NMADDUPDATECONTACTS;
                    }else{
                        $operation = LMADDUPDATECONTACTS;
                    }

                    $result = syncESData($operation,"GET",$syncSoftware,$data=array());
                    $sendGridResponse = json_decode($result);
                 
                    if($planType == 1){
                        $sendGridAllContactData = (array)$sendGridResponse->result;
                        $sendGridData = $sendGridAllContactData;
                    }else{
                        $sendGridRecipients = (array)$sendGridResponse->recipients;
                        $sendGridData = $sendGridRecipients;
                    }

                    //get all contacts
                    $suiteCRMAllContacts = array();
                    $contactLeadTableName = strtolower($suitecrmModule);
                    $selContactData = "SELECT * FROM $contactLeadTableName WHERE deleted = 0";
                    $selContactDataResult = $GLOBALS['db']->query($selContactData);
                    while($selContactDataRow = $GLOBALS['db']->fetchByAssoc($selContactDataResult)){
                        $contactBean = BeanFactory::getBean($suitecrmModule,$selContactDataRow['id']);
                        $suiteCRMAllContacts[$selContactDataRow['id']] = $contactBean->email1;
                    }

                    foreach ((array)$sendGridData as $key => $value) {
                        $sendGridFields = array();
                        foreach ($value as $k => $v) {
                            if($k == "id"){
                                $recordId['viem_module_id_c'] = $v;
                                $recordId['id'] = $v;
                            }
                            if($k == "custom_fields"){
                                $customFields = array();
                                foreach ($v as $kfields => $vfields) {
                                    if(array_key_exists(strtolower($vfields->name),$suiteCRMFields)){
                                        $customFields[$suiteCRMFields[strtolower($vfields->name)]] = $vfields->value;
                                    }
                                }
                            }else{
                                if(array_key_exists($k, $suiteCRMFields)){
                                    $sendGridFields[$suiteCRMFields[$k]] = $v;
                                }
                            }
                        }
                        $sendGridFields['viem_name_c'] = $syncSoftware;
                        $sendGridFields['assigned_user_id'] = $currentLoggedInUserID;
                        $sendGridAllFields[] = array_merge($sendGridFields, $customFields, $recordId);
                    }
                    
                    foreach ($sendGridAllFields as $keySend => $valueSend) {
                        $id = $valueSend['viem_module_id_c'];
                        $checkSql = "";
                        $checkSql .= "SELECT * FROM vi_contacts_es ";
                        if($planType == 1){
                            if(in_array($valueSend['email1'],$suiteCRMAllContacts)){
                                $suiteCRMContactId = array_search($valueSend['email1'], $suiteCRMAllContacts);
                                if($suitecrmModule == "Contacts"){
                                    $checkSql .= "WHERE vi_suitecrm_contact_id = '$suiteCRMContactId'";
                                }else{
                                    $checkSql .= "WHERE vi_suitecrm_lead_id = '$suiteCRMContactId'";
                                }                                
                            }else{
                                if($suitecrmModule == "Contacts"){
                                    $checkSql .= "WHERE vi_es_contact_id = '$id'";
                                }else{
                                    $checkSql .= "WHERE vi_es_lead_id = '$id'";
                                }                                
                            }
                        }else{
                            if($suitecrmModule == "Contacts"){
                                $checkSql .= "WHERE vi_es_contact_id = '$id'";
                            }else{
                                $checkSql .= "WHERE vi_es_lead_id = '$id'";
                            }                                
                        }
                        $checkSql .=  " and vi_es_name = 'SendGrid' AND deleted = 0";
                        $checkResult = $GLOBALS['db']->fetchOne($checkSql,false,"",false);

                        if(!empty($checkResult)){
                            $contactId = $checkResult['id'];
                            if($suitecrmModule == "Contacts"){
                                $recordID = $checkResult['vi_suitecrm_contact_id'];
                            }else{
                                $recordID = $checkResult['vi_suitecrm_lead_id'];
                            }
                            $bean = BeanFactory::getBean($suitecrmModule, $recordID);

                            if(!empty($bean) && $bean->deleted == 0){
                                $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueSend,$suiteCRMFields,$recordID,$esModule,$syncSoftware,$recordID, "Contacts");
                                if($updatedRecord == "failure"){
                                    $failure[] = $updatedRecord;
                                }else{
                                    $updatedRecords[] = $updatedRecord;    
                                }
                            }else{
                                $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$contactId'";
                                $updateResult = $GLOBALS['db']->query($updateData);
                                
                                $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueSend,$suiteCRMFields,"",$esModule,$syncSoftware,"","Contacts");
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                }
                            }
                        }else{
                            $emailAdrress = $valueSend['email1'];
                            $query = "SELECT id FROM email_addresses WHERE email_address = '$emailAdrress'";
                            $selectResult = $GLOBALS['db']->fetchOne($query,false,'',false);
                            
                            if(!empty($selectResult)){
                                $referID = $selectResult['id'];
                                $query2 = "SELECT bean_id FROM email_addr_bean_rel WHERE email_address_id = '$referID' and bean_module = '$suitecrmModule' and deleted = 0";
                                $selectResult2 = $GLOBALS['db']->fetchOne($query2,false,'',false);
                                $recordContactID = '';
                                if(!empty($selectResult2)){
                                    $recordContactID = $selectResult2['bean_id'];
                                }//end of if

                                if($recordContactID == ""){
                                    $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueSend,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");

                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    }
                                }else{
                                    if($planType == 1){
                                        if(in_array($emailAdrress,$suiteCRMAllContacts)){
                                            $suiteCRMContactId = array_search($emailAdrress, $suiteCRMAllContacts);
                                            if($suitecrmModule == "Contacts"){
                                                $contactsLeadIdQuery = "vi_suitecrm_contact_id = '$suiteCRMContactId'";
                                            }else{
                                                $contactsLeadIdQuery = "vi_suitecrm_lead_id = '$suiteCRMContactId'";
                                            }//end of else
                                            $sql = "SELECT *
                                                    FROM vi_contacts_es
                                                    WHERE $contactsLeadIdQuery and vi_es_name = 'SendGrid' AND deleted = 0";
                                        }else{
                                            if($suitecrmModule == "Contacts"){
                                                $esContactsLeadIdQuery = "vi_es_contact_id = '$id'";
                                            }else{
                                                $esContactsLeadIdQuery = "vi_es_lead_id = '$id'";
                                            }//end of else
                                            $sql = "SELECT *
                                                    FROM vi_contacts_es
                                                    WHERE $esContactsLeadIdQuery and vi_es_name = 'SendGrid' AND deleted = 0";
                                        }//end of else
                                    }else{
                                        if($suitecrmModule == "Contacts"){
                                            $esContactsLeadIdQuery = "vi_es_contact_id = '$id'";
                                        }else{
                                            $esContactsLeadIdQuery = "vi_es_lead_id = '$id'";
                                        }//end of else
                                        $sql = "SELECT *
                                                    FROM vi_contacts_es
                                                    WHERE $esContactsLeadIdQuery and vi_es_name = 'SendGrid' AND deleted = 0";
                                    }//end of else
                                    $checkResult = $GLOBALS['db']->fetchOne($sql,false,'',false);

                                    if(!empty($checkResult)){
                                        $viContactId = $checkResult['id'];
                                        if($suitecrmModule == 'Contacts'){
                                            $scId = $checkResult['vi_suitecrm_contact_id'];
                                        }else{
                                            $scId = $checkResult['vi_suitecrm_lead_id'];
                                        }
                                        $bean = BeanFactory::getBean($suitecrmModule, $scId);

                                        if(!empty($bean) && $bean->deleted == 0){
                                            $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueSend,$suiteCRMFields,$recordContactID,$esModule,$syncSoftware,"", "Contacts");
                                            if($updatedRecord == "failure"){
                                                $failure[] = $updatedRecord;
                                            }else{
                                                $updatedRecords[] = $updatedRecord;    
                                            }
                                        }else{
                                            $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$viContactId'";
                                            $updateResult = $GLOBALS['db']->query($updateData);

                                            $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueSend,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");
                                            if($insertedRecord == "failure"){
                                                $failure[] = $insertedRecord;
                                            }else{
                                                $insertedRecords[] = $insertedRecord;    
                                            }
                                        }
                                    }else{
                                        if(in_array($emailAdrress,$suiteCRMAllContacts)){
                                            $suiteCRMContactId = array_search($emailAdrress, $suiteCRMAllContacts);
                                            $getContactData = "SELECT * FROM $contactLeadTableName WHERE id='$suiteCRMContactId' AND deleted = 0";
                                            $getContactDataResult = $GLOBALS['db']->fetchOne($getContactData);

                                            if(!empty($getContactDataResult)){
                                                $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueSend,$suiteCRMFields,$suiteCRMContactId,$esModule,$syncSoftware,"", "Contacts");
                                                if($updatedRecord == "failure"){
                                                    $failure[] = $updatedRecord;
                                                }else{
                                                    $updatedRecords[] = $updatedRecord;    
                                                }

                                                $randomId = create_guid();
                                                $suiteContactId = $suiteLeadId = '';
                                                if ($suitecrmModule == "Contacts") {
                                                    $suiteContactId = $suiteCRMContactId;
                                                }elseif ($suitecrmModule == "Leads") {
                                                    $suiteLeadId = $suiteCRMContactId;
                                                }//end of else
                                                $data = array('id' => $randomId,'vi_suitecrm_contact_id' => $suiteContactId,'vi_es_contact_id' => $id,'vi_suitecrm_lead_id' => $suiteLeadId,'vi_es_name' => $syncSoftware,'vi_es_list_id' => '','vi_suitecrm_module' => $suitecrmModule,'vi_es_lead_id' => $id, 'deleted' => 0);
                                                insertESRecord("vi_contacts_es", $data);
                                            }//end of if
                                        }else{
                                            $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueSend,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");
                                            if($insertedRecord == "failure"){
                                                $failure[] = $insertedRecord;
                                            }else{
                                                $insertedRecords[] = $insertedRecord;    
                                            }//end of else
                                        }//end of else
                                    }
                                }
                            }else{
                                $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueSend,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                }
                            }
                        }
                    }
                }

                if($syncSoftware == "SendGrid" && $suitecrmModule == "ProspectLists"){
                    $planType = getPlanType($syncSoftware);
                    if($planType == 1){
                        $operationList = NMADDUPDATELISTS;
                    }else{
                        $operationList = LMADDUPDATELISTS;
                    }
                    $result = syncESData($operationList,"GET",$syncSoftware,$data=array());
                    $sendGridResponse = (array)json_decode($result);

                    if($planType == 1){
                        $sendGridRecipients = (array)$sendGridResponse['result'];
                        $list = '';
                        $cotactSample = '?contact_sample=true';
                    }else{
                        $sendGridRecipients = (array)$sendGridResponse['lists'];
                        $list = '/recipients';
                        $cotactSample = ''; 
                    }
                    
                    $finalsendGridRecipients = $recipients = $mapArray = $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                    foreach ((array)$sendGridRecipients as $key => $value) {
                        $finalsendGridRecipients[] = (array)$value;
                    }
                    
                    foreach ($finalsendGridRecipients as $key => $value) {
                        $id = $value['id'];
                        $url = $operationList."/".$id.$list.$cotactSample;
                        $resultForRecipents = syncESData($url,"GET",$syncSoftware,$data=array());
                        $sendGridResponseForRecipents = (array)json_decode($resultForRecipents);
                        if($planType == 1){
                            $recipientCount = $sendGridResponseForRecipents['contact_count'];
                            $finalsendGridRecipients[$key]['contact_sample'] = (array)$sendGridResponseForRecipents['contact_sample'];
                        }else{
                            $recipientCount = $sendGridResponseForRecipents['recipient_count'];
                            $finalsendGridRecipients[$key]['recipients'] = (array)$sendGridResponseForRecipents['recipients'];
                        }

                    }
                    
                    if($planType == 1){
                        $contactName = "contact_sample";
                    }else{
                        $contactName = "recipients";
                    }

                    foreach ($finalsendGridRecipients as $key => $value) {
                        $finalArray = array();
                        foreach ($value as $fieldk => $fieldv) {
                            if($fieldk != $contactName){
                                $finalArray[$fieldk] = $fieldv;    
                            }elseif($fieldk == $contactName){
                                foreach ((array)$fieldv as $reck => $recv) {
                                    $recipientsArray = array();
                                    foreach ((array)$recv as $kv => $vv) {
                                        if($kv == "id"){
                                            $contactID = $vv;
                                            $recipientsArray["id"] = $vv;
                                        }
                                        if(is_array($vv)){
                                            foreach ((array)$vv as $kfields => $vfields) {
                                                if(isset($vfields->name)){
                                                    if(array_key_exists(strtolower($vfields->name),$suiteCRMContactsFields)){
                                                        $customFields[$suiteCRMContactsFields[strtolower($vfields->name)]][] = $vfields->value;
                                                        $listId = $value['id'];
                                                        $recipientsArray[$suiteCRMContactsFields[strtolower($vfields->name)]] = $vfields->value;
                                                    }    
                                                }
                                                
                                            }
                                        }else{
                                            if(array_key_exists($kv, $suiteCRMContactsFields)){
                                                $fields[$suiteCRMContactsFields[strtolower($kv)]][] = $vv;
                                                $recipientsArray[$suiteCRMContactsFields[$kv]]= $vv;
                                            }    
                                        }
                                    }

                                    $recipientsArray['viem_name_c'] = $syncSoftware;
                                    $recipientsArray['viem_list_id_c'] = $value['id'];
                                    $finalArray[$contactName][] = $recipientsArray;
                                    $finalArray['viem_name_c'] = $syncSoftware;
                                    $finalArray['assigned_user_id'] = $currentLoggedInUserID;
                                }
                            }    
                        }
                        $mapArray[] = $finalArray;
                    }
                    
                    foreach ($mapArray as $key => $value) {
                        $checkListId = $value['id'];
                        $checkSql = "SELECT *
                                        FROM vi_segments_es
                                        WHERE vi_es_segments_id = '$checkListId' and vi_es_name = '$syncSoftware' AND deleted = 0";
                        $checkResult = $GLOBALS['db']->fetchOne($checkSql,false,"",false);

                        if(!empty($checkResult)){
                            $recordID = $checkResult['vi_suitecrm_segments_id'];
                            $id = $checkResult['id'];
                            $bean = BeanFactory::getBean('ProspectLists', $recordID);

                            if(!empty($bean) && $bean->deleted == 0){
                                $updatedRecord = emsToSuiteSyncLog("ProspectLists",$value,$suiteCRMFields,$recordID,$esModule,$syncSoftware,$checkListId, $targetListSubpanelModule);
                                if($updatedRecord == "failure"){
                                    $failure[] = $updatedRecord;
                                }else{
                                    $updatedRecords[] = $updatedRecord;    
                                }
                            }else{
                                $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$id'";
                                $updateResult = $GLOBALS['db']->query($updateData);

                                $insertedRecord = emsToSuiteSyncLog("ProspectLists",$value,$suiteCRMFields,"",$esModule,$syncSoftware,'', $targetListSubpanelModule);
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                }
                            }
                        }else{
                            $insertedRecord = emsToSuiteSyncLog("ProspectLists",$value,$suiteCRMFields,"",$esModule,$syncSoftware,'', $targetListSubpanelModule);
                            if($insertedRecord == "failure"){
                                $failure[] = $insertedRecord;
                            }else{
                                $insertedRecords[] = $insertedRecord;    
                            }
                        }
                    }
                }

                if($syncSoftware == "ConstantContact" && $suitecrmModule == "ProspectLists"){

                    $result = syncESData("/lists?api_key=","GET",$syncSoftware,$data=array());
                    $response = (array)json_decode($result);
                    $listAllData = $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                    foreach ($response as $keyList => $valueList) {
                        $listData = $finalCCArray = array();
                        foreach ((array)$valueList as $key => $valList) {
                            foreach ($suiteCRMFields as $keyField => $valField) {
                                if($keyField == $key){
                                    $listData[$valField] = $valueList->$keyField; 
                                }          
                            }
                        }//end of foreach

                        $listData['id'] = $valueList->id;
                        if($valueList->contact_count > 0){
                            $totalCount = $valueList->contact_count;
                            $listRecipientData = $allContactsData = array();
                            $listId = $valueList->id;

                            $resultAllRelatedContacts = syncESData("/lists/".$listId."/contacts?api_key=","GET",$syncSoftware,$data=array());
                            $responseFetchAllRelatedContacts = (array)json_decode($resultAllRelatedContacts);
                            $allContactsData = $responseFetchAllRelatedContacts['results'];

                            if(isset($responseFetchAllRelatedContacts['meta']->pagination->next_link)){
                                $nextLink = str_replace("/v2", "", $responseFetchAllRelatedContacts['meta']->pagination->next_link);
                                $allConstantContactListContact = getConstanContactListRecords($nextLink, $syncSoftware, $totalCount, count($allContactsData), $allContactsData);
                            }else{
                                $allConstantContactListContact = $allContactsData;
                            }//end of else
                          
                            foreach ($allConstantContactListContact as $keyContacts => $valueContacts) {
                                $recipients = array();
                                foreach ($suiteCRMContactsFields as $keyField => $valField) {
                                    if(isset($valueContacts->$keyField)){
                                        $recipients[$valField] = $valueContacts->$keyField;
                                    }else{
                                        $recipients[$valField] = '';
                                    }//end of else    
                                    if($keyField == "email_address"){
                                        foreach ($valueContacts->email_addresses as $key => $value) {
                                            $email = $value->email_address;
                                            $recipients['email1'] = $email;        
                                        }
                                    }
                                    $recipients['id'] = $valueContacts->id;        
                                }
                                $listRecipientData[] = $recipients;
                            }
                            $finalCCArray['recipients'] = $listRecipientData;
                        }
                        $arrayAfterMerge = array_merge($listData,$finalCCArray);
                        $listAllData[] = $arrayAfterMerge;
                    }
                    
                    foreach ($listAllData as $keyData => $valueData) {
                        foreach ($valueData as $keyid => $valueid) {
                            if($keyid == "id"){
                                $esId = $valueid;
                                $sql = "SELECT *
                                        FROM vi_segments_es
                                        WHERE vi_es_segments_id = '$valueid' and vi_es_name = 'ConstantContact' AND deleted = 0";
                                $selectResult = $GLOBALS['db']->fetchOne($sql,false,'',false);
                                $viEsSegmentId = $segmentId = $suiteSegmentId = '';
                                if(!empty($selectResult)){
                                    $segmentId = $selectResult['id'];
                                    $viEsSegmentId = $selectResult['vi_es_segments_id'];
                                    $suiteSegmentId = $selectResult['vi_suitecrm_segments_id'];
                                }//end of if
                            }
                        }
                        
                        if($esId == $viEsSegmentId){                        
                            $bean = BeanFactory::getBean('ProspectLists', $suiteSegmentId);

                            if(!empty($bean) && $bean->deleted == 0){
                                $updatedRecord = emsToSuiteSyncLog("ProspectLists",$valueData,$suiteCRMFields,$suiteSegmentId,$esModule,$syncSoftware,$esId, $targetListSubpanelModule);
                                if($updatedRecord == "failure"){
                                    $failure[] = $updatedRecord;
                                }else{
                                    $updatedRecords[] = $updatedRecord;    
                                }
                            }else{
                                $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$segmentId'";
                                $updateResult = $GLOBALS['db']->query($updateData);
                                
                                $insertedRecord = emsToSuiteSyncLog("ProspectLists",$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", $targetListSubpanelModule);  
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                }  
                            }
                        }else{
                            $insertedRecord = emsToSuiteSyncLog("ProspectLists",$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", $targetListSubpanelModule);  
                            if($insertedRecord == "failure"){
                                $failure[] = $insertedRecord;
                            }else{
                                $insertedRecords[] = $insertedRecord;    
                            }  
                        } 
                    }
                }

                if($syncSoftware == "SendInBlue" && ($suitecrmModule == "Contacts" || $suitecrmModule == "Leads")){
                    //To fetch all contacts from SendInBlue 
                    $res = syncESData("contacts","GET",$syncSoftware,"");
                    $contactsResponse = (array)json_decode($res);
                    if($contactsResponse['count'] > 0){
                        $totalContacts = $contactsResponse['count'];
                        $cnt = $offset = 0;                    
                        $allContacts = array();
                        for($i=50;$i<=$totalContacts;$i=$i+50){
                            $limit = 50;
                            $offset = $cnt;
                            $cnt = $cnt + 50;
                            $contactUrl = "contacts?limit=".$limit."&offset=".$offset;
                            $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,"");
                            $contactData = (array)json_decode($relatedContacts); 
                            foreach($contactData['contacts'] as $k => $v){
                                $allContacts[] = $v;
                            }
                        }

                        if($cnt <= $totalContacts){
                            $contactUrl = "contacts?limit=50&offset=".$cnt;
                            $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,"");
                            $contactData = (array)json_decode($relatedContacts); 
                            foreach($contactData['contacts'] as $k => $v){
                                $allContacts[] = $v;
                            }
                        }
                    }
                    if(!empty($allContacts) && isset($allContacts)){                
                        $finalActiveCampArray = array();
                        foreach ($allContacts as $keyComp => $valueComp) {
                            $array = array();
                            foreach ($suiteCRMFields as $keyFields => $valueFields) {
                                if(isset($valueComp->$keyFields)){
                                    $array[$valueFields] = $valueComp->$keyFields;
                                }
                                if(array_key_exists($keyFields,(array)$valueComp->attributes)){
                                    $array[$valueFields] = $valueComp->attributes->$keyFields;
                                }                        
                            }
                            $array['id'] = $valueComp->id;
                            if(!empty($array)){
                                $finalActiveCampArray[] = $array;
                            }
                        }

                        foreach ($finalActiveCampArray as $keyData => $valueData) {
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;
                                    $checkSql = "SELECT * FROM vi_contacts_es";
                                    if($suitecrmModule == 'Contacts'){
                                        $checkSql .= " WHERE vi_es_contact_id = '$valueid'";
                                    }else{
                                       $checkSql .= " WHERE vi_es_lead_id = '$valueid'"; 
                                    }//end of else
                                    $checkSql .= " AND deleted = 0 AND vi_es_name = 'SendInBlue'";
                                    $selectResult = $GLOBALS['db']->fetchOne($checkSql,false,'',false);

                                    $id = $esModuleID = $suiteId = '';
                                    if(!empty($selectResult)){
                                        $id = $selectResult['id'];
                                        if($suitecrmModule == "Contacts"){
                                            $esModuleID = $selectResult['vi_es_contact_id'];    
                                            $suiteId = $selectResult['vi_suitecrm_contact_id'];
                                        }elseif ($suitecrmModule == "Leads") {
                                            $esModuleID = $selectResult['vi_es_lead_id'];    
                                            $suiteId = $selectResult['vi_suitecrm_lead_id'];
                                        }//end of else
                                    }//end of if
                                }
                            }
                            
                            if($esId == $esModuleID){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteId);
                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,$suiteId,$esModule,$syncSoftware,$esId, "Contacts");         
                                    if($updatedRecord == "failure"){
                                        $failure[] = $updatedRecord;
                                    }else{
                                        $updatedRecords[] = $updatedRecord;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$id'";
                                    $updateResult = $GLOBALS['db']->query($updateData);
                                    $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");   
                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    }
                                } 
                            }else{
                                $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");   
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                }
                            }
                        }
                    }
                }

                if($syncSoftware == "SendInBlue" && $suitecrmModule == "ProspectLists"){
                    //To fetch all list from SendInBlue 
                    $res = syncESData("contacts/lists","GET",$syncSoftware,$data=array());
                    $bean = BeanFactory::newBean($suitecrmModule);
                    $tableName = $bean->getTableName();
                   
                    $contactsListResponse = (array)json_decode($res);
                    if($contactsListResponse['count'] > 0){
                        $allContactsLists = (array)$contactsListResponse['lists'];
                    }
                    if(!empty($allContactsLists) && isset($allContactsLists)){                
                        //fetch all contacts from sendInBlue
                        $resContacts = syncESData("contacts","GET",$syncSoftware,$data=array());
                        $contactsResponse = (array)json_decode($resContacts);
                        $allContacts = $finalSendInBlueArray = array();
                        
                        if($contactsResponse['count'] > 0){
                            $totalContacts = $contactsResponse['count'];
                            $cnt = $offset = 0;
                            for($i=50;$i<=$totalContacts;$i=$i+50){
                                $limit = 50;
                                $offset = $cnt;
                                $cnt = $cnt + 50;
                                $contactUrl = "contacts?limit=".$limit."&offset=".$offset;
                                $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,$data=array());
                                $contactData = (array)json_decode($relatedContacts); 
                                foreach($contactData['contacts'] as $k => $v){
                                    $allContacts[] = $v;
                                }
                            }

                            if($cnt <= $totalContacts){
                                $contactUrl = "contacts?limit=50&offset=".$cnt;
                                $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,"");
                                $contactData = (array)json_decode($relatedContacts); 
                                foreach($contactData['contacts'] as $k => $v){
                                    $allContacts[] = $v;
                                }
                            }
                        }

                        foreach ($allContactsLists as $keyComp => $valueComp) {
                            $array = $recipientsArray = array();                        
                            foreach ($suiteCRMFields as $keyFields => $valueFields) {
                                $array[$valueFields] = $valueComp->$keyFields;
                            }

                            $array['id'] = $valueComp->id;
                            foreach ($allContacts as $key => $value) {
                                $recipients = array();
                                if(in_array($valueComp->id, $value->listIds)){
                                    foreach ($suiteCRMContactsFields as $keyContactsFields => $valueContactsFields) {
                                        if(isset($value->$keyContactsFields)){
                                            $recipients[$valueContactsFields] = $value->$keyContactsFields;
                                        }
                                        if(array_key_exists($keyContactsFields,(array)$value->attributes)){
                                            $recipients[$valueContactsFields] = $value->attributes->$keyContactsFields;
                                        }        
                                    }
                                    $recipients['id'] = $value->id;
                                    if(!empty($recipients)){
                                        $recipientsArray[] = $recipients;
                                    }
                                }
                            }

                            $array['recipients'] = $recipientsArray;
                            if(!empty($array)){
                                $finalSendInBlueArray[] = $array;
                            }
                        }

                        $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                        foreach ($finalSendInBlueArray as $keyData => $valueData) {
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;

                                    $checkSql = "SELECT * FROM vi_segments_es WHERE vi_es_segments_id = '$esId' and vi_es_name = 'SendInBlue' AND deleted = 0";
                                    $selectResult = $GLOBALS['db']->fetchOne($checkSql,false,'',false);
                                    $esModuleID = $suiteId = $segmentId = '';
                                    if(!empty($selectResult)){
                                        $esModuleID = $selectResult['vi_es_segments_id'];    
                                        $suiteId = $selectResult['vi_suitecrm_segments_id'];
                                        $segmentId = $selectResult['id'];
                                    }//end of if
                                }
                            }

                            if($esId == $esModuleID){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteId);
                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,$suiteId,$esModule,$syncSoftware,$esId, $targetListSubpanelModule);          
                                    if($updatedRecord == "failure"){
                                        $failure[] = $updatedRecord;
                                    }else{
                                        $updatedRecords[] = $updatedRecord;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$segmentId'";
                                    $updateResult = $GLOBALS['db']->query($updateData);

                                    $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", $targetListSubpanelModule);   
                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    }
                                }
                            }else{
                                $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", $targetListSubpanelModule);   
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                } 
                            } 
                        }
                    }
                }

                if($syncSoftware == "ActiveCampaigns" && ($suitecrmModule == "Contacts" || $suitecrmModule == "Leads")){
                    //To fetch all contacts from ActiveCampaigns 
                    $res = syncESData("/api/3/contacts","GET",$syncSoftware,"");
                    $activeCampResponse = (array)json_decode($res);
                    $totalContacts = $activeCampResponse['meta']->total;
                    $cnt = $offset = 0;                
                    $allContacts = $allFieldValues = array();
                    for($i=100;$i<=$totalContacts;$i=$i+100){
                        $limit = 100;
                        $offset = $cnt;
                        $cnt = $cnt + 100;
                        $contactUrl = "/api/3/contacts?limit=".$limit."&offset=".$offset;
                        $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,"");
                        $contactData = (array)json_decode($relatedContacts);
                        if(isset($contactData)){
                            foreach($contactData['contacts'] as $k => $v){
                                $allContacts[] = $v;
                            }
                            if(isset($contactData['fieldValues']) && !empty($contactData['fieldValues'])){
                                foreach($contactData['fieldValues'] as $fk => $fv){
                                    $allFieldValues[] = $fv;
                                }
                            }//end of if
                        }//end of if
                    }
                    if($cnt <= $totalContacts){
                        $contactUrl = "/api/3/contacts?limit=100&offset=".$cnt;
                        $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,"");
                        $contactData = (array)json_decode($relatedContacts); 
                        if(isset($contactData)){
                            foreach($contactData['contacts'] as $k => $v){
                                $allContacts[] = $v;
                            }
                            if(isset($contactData['fieldValues']) && !empty($contactData['fieldValues'])){
                                foreach($contactData['fieldValues'] as $fk => $fv){
                                    $allFieldValues[] = $fv;
                                }
                            }//end of if
                        }//end of if
                    }
                    
                    if(!empty($allContacts) && isset($allContacts)){
                        $finalActiveCampArray = array();
                        foreach ($allContacts as $keyComp => $valueComp) {
                            $customFieldsRecordId = $valueComp->id;
                            $customField = syncESData("/api/3/contacts/".$customFieldsRecordId."/fieldValues", "GET", $syncSoftware, "");
                            $customFieldData = (array)json_decode($customField); 

                            $array = array();
                            foreach ($suiteCRMFields as $keyFields => $valueFields) {
                                if(isset($valueComp->$keyFields)){
                                    $array[$valueFields] = $valueComp->$keyFields;
                                }else{
                                    foreach ($customFieldData as $aFKey => $aFValueData) {
                                        foreach ($aFValueData as $index => $aFValue) {
                                            $fieldId = $aFValue->field;
                                            $endPointURL = "/api/3/fields/".$fieldId;
                                            $customFieldList = syncESData($endPointURL,"GET",$syncSoftware,"");
                                            $customFieldListResult = (array)json_decode($customFieldList);
                                            $fieldName = $customFieldListResult['field']->title;
                                            if(array_key_exists($fieldName, $suiteCRMFields)){
                                                $array[$suiteCRMFields[$fieldName]] = $aFValue->value;
                                            }//end of if
                                        }//end of foreach
                                    }//end of foreach
                                }//end of else
                            }
                            $array['id'] = $valueComp->id;

                            $resultId = $valueComp->id;
                            foreach ($allFieldValues as $aFKey => $aFValue) {
                                if($aFValue->contact == $resultId){
                                    $fieldId = $aFValue->field;
                                    $endPointURL = "/api/3/fields/".$fieldId;
                                    $customFieldList = syncESData($endPointURL,"GET",$syncSoftware,"");
                                    $customFieldListResult = (array)json_decode($customFieldList);
                                    $fieldName = $customFieldListResult['field']->title;
                                    if(array_key_exists($fieldName, $suiteCRMFields)){
                                        $array[$suiteCRMFields[$fieldName]] = $aFValue->value;
                                    }
                                }
                            }

                            if(!empty($array)){
                                $finalActiveCampArray[] = $array;
                            }
                        }
                        
                        foreach ($finalActiveCampArray as $keyData => $valueData) {
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;
                                    if($suitecrmModule == 'Contacts'){
                                        $checkSql = "SELECT * FROM vi_contacts_es WHERE vi_es_contact_id = '$valueid' AND deleted = 0 AND vi_es_name = 'ActiveCampaigns'";
                                    }else{
                                        $checkSql = "SELECT * FROM vi_contacts_es WHERE vi_es_lead_id = '$valueid' AND deleted = 0 AND vi_es_name = 'ActiveCampaigns'";
                                    }
                                    
                                    $selectResult = $GLOBALS['db']->fetchOne($checkSql,false,'',false);
                                    $esModuleID = $suiteId = $id = '';
                                    if(!empty($selectResult)){
                                        if($suitecrmModule == "Contacts"){
                                            $esModuleID = $selectResult['vi_es_contact_id'];    
                                            $suiteId = $selectResult['vi_suitecrm_contact_id'];
                                        }elseif ($suitecrmModule == "Leads") {
                                            $esModuleID = $selectResult['vi_es_lead_id'];    
                                            $suiteId = $selectResult['vi_suitecrm_lead_id'];
                                        }
                                        $id = $selectResult['id'];
                                    }//end of if
                                }
                            }

                            if($esId == $esModuleID){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteId);
                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,$suiteId,$esModule,$syncSoftware,$esId, "Contacts");          
                                    if($updatedRecord == "failure"){
                                        $failure[] = $updatedRecord;
                                    }else{
                                        $updatedRecords[] = $updatedRecord;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$id'";
                                    $updateResult = $GLOBALS['db']->query($updateData);
                                    $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");   
                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    } 
                                }
                            }else{
                                $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");   
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                } 
                            }
                        }
                    }                 
                }

                if($syncSoftware == "ActiveCampaigns" && $suitecrmModule == "ProspectLists"){
                    //To fetch all ProspectLists from ActiveCampaigns 
                    $res = syncESData("/api/3/lists","GET",$syncSoftware,"");
                    $activeCampResponse = (array)json_decode($res); 

                    $relatedContacts = syncESData("/api/3/contacts","GET",$syncSoftware,$data=array());
                    $relatedTotalContactsResponse = (array)json_decode($relatedContacts);
                    $totalContacts = $relatedTotalContactsResponse['meta']->total;
                    $cnt = $offset = 0;
                    $relatedContactsResponse = $relatedContactsFieldValues = array();                
                    for($i=100;$i<=$totalContacts;$i=$i+100){                    
                        $limit = 100;
                        $offset = $cnt;
                        $cnt = $cnt + 100;
                        $contactUrl = "/api/3/contacts?limit=".$limit."&offset=".$offset;
                        $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,$data=array());
                        $contactData = (array)json_decode($relatedContacts); 
                        foreach($contactData['contacts'] as $k => $v){
                            $relatedContactsResponse['contacts'][] = $v;
                        }

                        if(isset($contactData['fieldValues']) && !empty($contactData['fieldValues'])){
                            foreach($contactData['fieldValues'] as $fk => $fv){
                                $relatedContactsFieldValues['fieldValues'][] = $fv;
                            }
                        }//end of if
                    }
                    
                    if($cnt <= $totalContacts){
                        $contactUrl = "/api/3/contacts?limit=100&offset=".$cnt;
                        $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,$data=array());
                        $contactData = (array)json_decode($relatedContacts); 
                        foreach($contactData['contacts'] as $k => $v){
                            $relatedContactsResponse['contacts'][] = $v;
                        }

                        if(isset($contactData['fieldValues']) && !empty($contactData['fieldValues'])){
                            foreach($contactData['fieldValues'] as $fk => $fv){
                                $relatedContactsFieldValues['fieldValues'][] = $fv;
                            }
                        }//end of if
                    }

                    //collect all contacts ids and then its list id
                    $relatedContactList = $allContactList = array();
                    foreach ($relatedContactsResponse['contacts'] as $key => $value) {
                        $contactId = $value->id;
                        $relatedContactsList = syncESData("/api/3/contacts/".$value->id,"GET",$syncSoftware,$data=array());
                        $relatedContactsListResponse = (array)json_decode($relatedContactsList);
                        $relatedContactInfo = array();
                        $relatedContactInfo['id'] = $value->id;

                        $customField = syncESData("/api/3/contacts/".$contactId."/fieldValues", "GET", $syncSoftware, "");
                        $customFieldData = (array)json_decode($customField); 
                        
                        foreach ($suiteCRMContactsFields as $keyFields => $valueFields) {
                            if(isset($relatedContactsListResponse['contact']->$keyFields)){
                                $relatedContactInfo[$valueFields] = $relatedContactsListResponse['contact']->$keyFields;
                            }else{
                                foreach ($customFieldData as $aFKey => $aFValueData) {
                                    foreach ($aFValueData as $index => $aFValue) {
                                        $fieldId = $aFValue->field;
                                        $endPointURL = "/api/3/fields/".$fieldId;
                                        $customFieldList = syncESData($endPointURL,"GET",$syncSoftware,"");
                                        $customFieldListResult = (array)json_decode($customFieldList);
                                        $fieldName = $customFieldListResult['field']->title;
                                        if(array_key_exists($fieldName, $suiteCRMContactsFields)){
                                            $relatedContactInfo[$suiteCRMContactsFields[$fieldName]] = $aFValue->value;
                                        }//end of if
                                    }//end of foreach
                                }//end of foreach
                            }//end of else
                        }//end of foreach
                        
                        if(isset($relatedContactsFieldValues['fieldValues']) && !empty($relatedContactsFieldValues['fieldValues'])){
                            foreach ($relatedContactsFieldValues['fieldValues'] as $aFKey => $aFValue) {
                                if($aFValue->contact == $resultId){
                                    $fieldId = $aFValue->field;
                                    $endPointURL = "/api/3/fields/".$fieldId;
                                    $customFieldList = syncESData($endPointURL,"GET",$syncSoftware,$data=array());
                                    $customFieldListResult = (array)json_decode($customFieldList);
                                    $fieldName = $customFieldListResult['field']->title;
                                    if(array_key_exists($fieldName, $suiteCRMContactsFields)){
                                        $relatedContactInfo[$suiteCRMContactsFields[$fieldName]] = $aFValue->value;
                                    }
                                }
                            }
                        }//end of if

                        foreach ($relatedContactsListResponse['contactLists'] as $keyList => $valueList) {
                            $contactStatus = $relatedContactsListResponse['contact']->contactLists[$keyList];
                            if($contactStatus == 1 || $valueList->status == 1){
                                $relatedContactList[$valueList->list][] = $relatedContactInfo; 
                            }//end of if
                        }
                    }

                    if($activeCampResponse['meta']->total > 0){
                        foreach ($activeCampResponse['lists'] as $index => $listData) {
                            if($listData->name != '' && $listData->user != ''){
                                $allContactList[$index] = $listData;
                            }//end of if
                        }//end of foreach
                    }
                    
                    if(!empty($allContactList) && isset($allContactList)){
                        $finalActiveCampArray = array();
                        foreach ($allContactList as $keyComp => $valueComp) {
                            $array = array();
                            foreach ($suiteCRMFields as $keyFields => $valueFields) {
                                $array[$valueFields] = $valueComp->$keyFields;
                            }
                            $array['id'] = $valueComp->id;
                            foreach ($relatedContactList as $key => $value) {
                                if($key == $valueComp->id){
                                    $array['recipients'] = $value;        
                                }
                            }
                            if(!empty($array)){
                                $finalActiveCampArray[] = $array;
                            }
                        }
                        
                        $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                        foreach ($finalActiveCampArray as $keyData => $valueData) {
                            $esModuleID = $suiteId = $segmentId = '';
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;
                                    $checkSql = "SELECT * FROM vi_segments_es WHERE vi_es_segments_id = '$esId' and vi_es_name = 'ActiveCampaigns' AND deleted = 0";
                                    $selectResult = $GLOBALS['db']->fetchOne($checkSql,false,'',false);
                                    if(!empty($selectResult)){
                                        $esModuleID = $selectResult['vi_es_segments_id'];    
                                        $suiteId = $selectResult['vi_suitecrm_segments_id'];
                                        $segmentId = $selectResult['id'];
                                    }//end of if
                                }
                            }
                            if($esId == $esModuleID){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteId);
                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,$suiteId,$esModule,$syncSoftware,$esId, $targetListSubpanelModule);          
                                    if($updatedRecord == "failure"){
                                        $failure[] = $updatedRecord;
                                    }else{
                                        $updatedRecords[] = $updatedRecord;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$segmentId'";
                                    $updateResult = $GLOBALS['db']->query($updateData);
                                    $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", $targetListSubpanelModule);   
                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    }
                                }
                            }else{
                                $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", $targetListSubpanelModule);   
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                } 
                            } 
                        }
                    }
                }

                if($syncSoftware == "ActiveCampaigns" && $suitecrmModule == "Accounts"){
                    //To fetch all organizations from ActiveCampaigns 
                    $res = syncESData("/api/3/accounts","GET",$syncSoftware,"");
                    $activeCampResponse = (array)json_decode($res);
                    $totalContacts = $activeCampResponse['meta']->total;
                    $cnt = $offset = 0;
                    $allAccounts = array();
                    for($i=100;$i<=$totalContacts;$i=$i+100){
                        $limit = 100;
                        $offset = $cnt;
                        $cnt = $cnt + 100;
                        $contactUrl = "/api/3/accounts?limit=".$limit."&offset=".$offset;
                        $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,"");
                        $contactData = (array)json_decode($relatedContacts); 
                        foreach($contactData['accounts'] as $k => $v){
                            $allAccounts[] = $v;
                        }
                    }
                    if($cnt <= $totalContacts){
                        $contactUrl = "/api/3/accounts?limit=100&offset=".$cnt;
                        $relatedContacts = syncESData($contactUrl,"GET",$syncSoftware,"");
                        $contactData = (array)json_decode($relatedContacts); 

                        foreach($contactData['accounts'] as $k => $v){
                            $allAccounts[] = $v;
                        }
                    }
                    
                    if(!empty($allAccounts) && isset($allAccounts)){
                        $finalActiveCampArray = array();
                        foreach ($allAccounts as $keyComp => $valueComp) {

                            $customFieldData = getActiveCampaignsAccountsCustomFields($syncSoftware);
                            $customFieldValues = syncESData("/api/3/accountCustomFieldData", 'GET', $syncSoftware, array());
                            $customFieldValuesResult = (array)json_decode($customFieldValues);

                            $array = array();
                            foreach ($suiteCRMFields as $keyFields => $valueFields) {
                                if(isset($valueComp->$keyFields)){
                                    $array[$valueFields] = $valueComp->$keyFields;
                                }else{
                                    foreach ($customFieldValuesResult['accountCustomFieldData'] as $k => $valData) {
                                        foreach ($customFieldData as $fieldId => $fieldData) {
                                            if($valueComp->id == $valData->accountId && ($valData->customFieldId == $fieldId)){
                                                if(isset($suiteCRMFields[$fieldData['field']])){
                                                    if($fieldData['type'] == 'date'){
                                                        $array[$suiteCRMFields[$fieldData['field']]] = date('Y-m-d', strtotime($valData->fieldValue));
                                                    }else if($fieldData['type'] == 'datetime' || $fieldData['type'] == 'datetimecombo'){
                                                        $array[$suiteCRMFields[$fieldData['field']]] = $timedate->to_db(date('m/d/Y H:i', strtotime($valData->fieldValue)));
                                                    }else{
                                                        $array[$suiteCRMFields[$fieldData['field']]] = $valData->fieldValue;
                                                    }//end of else
                                                }//end of if
                                            }//end of if
                                        }//end of foreach
                                    }//end of foreach
                                }//end of else
                            }//end of foreach
                            
                            $array['id'] = $valueComp->id;
                            if(!empty($array)){
                                $finalActiveCampArray[] = $array;
                            }
                        }
                        
                        foreach ($finalActiveCampArray as $keyData => $valueData) {
                            $esModuleID = $suiteId = $id = '';
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;
                                    $checkSql = "SELECT * FROM vi_accounts_es WHERE vi_es_account_id = '$esId' and vi_es_name = '$syncSoftware' AND deleted = 0";
                                    $selectResult = $GLOBALS['db']->fetchOne($checkSql,false,'',false);
                                    if(!empty($selectResult)){
                                        $esModuleID = $selectResult['vi_es_account_id'];    
                                        $suiteId = $selectResult['vi_suitecrm_account_id'];
                                        $id = $selectResult['id'];
                                    }//end of if
                                }
                            }

                            if($esId == $esModuleID){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteId);
                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,$suiteId,$esModule,$syncSoftware,$esId, "Contacts");          
                                    if($updatedRecord == "failure"){
                                        $failure[] = $updatedRecord;
                                    }else{
                                        $updatedRecords[] = $updatedRecord;    
                                    }    
                                }else{
                                    $updateData = "UPDATE vi_accounts_es SET deleted = 1 WHERE id = '$id'";
                                    $updateResult = $GLOBALS['db']->query($updateData);

                                    $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");   
                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    }
                                }
                            }else{
                                $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");   
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                } 
                            } 
                        } 
                    }                  
                }

                if($syncSoftware == "Mautic" && $suitecrmModule == "AOS_Products"){
                    //To fetch all assets from mautic
                    $result = syncESData("/api/assets","GET",$syncSoftware,$data=array());
                    $mauticResponse = (array)json_decode($result);
                    if($mauticResponse['total'] > 0){
                        $allAssets = $mauticResponse['assets'];
                    }
                    $suiteCRMFields = replaceKeys("file", "downloadUrl", $suiteCRMFields);
                    if(!empty($allAssets) && isset($allAssets)){
                        foreach ($allAssets as $keyAssets => $valueAssets) {
                            foreach ($valueAssets as $keyField => $valueField) {
                                foreach ($suiteCRMFields as $key => $value) {
                                    if($keyField == $key){
                                        $assetData[$value] = $valueAssets->$keyField;        
                                    }          
                                }
                                $assetData['id'] = $valueAssets->id;
                            }
                            $assetAllData[] = $assetData;
                        }

                        foreach ($assetAllData as $keyData => $valueData) {
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;
                                    $sql = "SELECT *
                                        FROM vi_assets_es
                                        WHERE vi_es_assets_id = '$valueid' and vi_es_name = 'Mautic' AND deleted = 0";
                                    $selectResult = $GLOBALS['db']->fetchOne($sql,false,'',false);
                                    $assetsId = $suiteAssestId = $esAssestId = '';
                                    if(!empty($selectResult)){
                                        $assetsId = $selectResult['id'];
                                        $suiteAssestId = $selectResult['vi_suitecrm_assets_id'];
                                        $esAssestId = $selectResult['vi_es_assets_id'];
                                    }//end of if
                                }
                            }

                            if($esId == $esAssestId){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteAssestId);

                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedRecord = emsToSuiteSyncLog("AOS_Products",$valueData,$suiteCRMFields,$suiteAssestId,"Assets",$syncSoftware,$esAssestId, "Contacts");
                                    if($updatedRecord == "failure"){
                                        $failure[] = $updatedRecord;
                                    }else{
                                        $updatedRecords[] = $updatedRecord;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_assets_es SET deleted = 1 WHERE id = '$assetsId'";
                                    $updateResult = $GLOBALS['db']->query($updateData);
                                    
                                    $insertedRecord = emsToSuiteSyncLog("AOS_Products",$valueData,$suiteCRMFields,"","Assets",$syncSoftware,"", "Contacts");  
                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    } 
                                }          
                            }else{
                                $insertedRecord = emsToSuiteSyncLog("AOS_Products",$valueData,$suiteCRMFields,"","Assets",$syncSoftware,"", "Contacts");  
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                }  
                            }                    
                        }
                    }
                }

                if($syncSoftware == "Mautic" && $suitecrmModule == "Accounts"){
                    //To fetch all companies from mautic
                    $result = syncESData("/api/companies","GET",$syncSoftware,$data=array());
                    $mauticResponse = (array)json_decode($result);
                    if($mauticResponse['total'] > 0){
                        $allCompanies = (array)$mauticResponse['companies'];
                    }
                    if(!empty($allCompanies) && isset($allCompanies)){
                        foreach ($allCompanies as $keyComp => $valueComp) {
                            foreach ($valueComp->fields->core as $keys => $values) {
                                foreach ($suiteCRMFields as $keyFields => $valueFields) {
                                    if($keyFields == $values->alias){
                                        $array[$valueFields] = $values->value;
                                    }
                                }
                            }
                            $array['id'] = $valueComp->id;
                            $finalMauticArray[] = $array;
                        }
                        foreach ($finalMauticArray as $keyData => $valueData) {
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;
                                    $sql = "SELECT *
                                        FROM vi_accounts_es
                                        WHERE vi_es_account_id = '$valueid' and vi_es_name = 'Mautic' AND deleted = 0";
                                    $selectResult = $GLOBALS['db']->fetchOne($sql,false,'',false);
                                    $suiteAccountId = $id = $esAccountId = '';
                                    if(!empty($selectResult)){
                                        $suiteAccountId = $selectResult['vi_suitecrm_account_id'];
                                        $id = $selectResult['id'];
                                        $esAccountId = $selectResult['vi_es_account_id'];
                                    }//end of if
                                }
                            }

                            if($esId == $esAccountId){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteAccountId);

                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedRecord = emsToSuiteSyncLog("Accounts",$valueData,$suiteCRMFields,$suiteAccountId,"Accounts",$syncSoftware,$esAccountId, "Contacts");          
                                    if($updatedRecord == "failure"){
                                        $failure[] = $updatedRecord;
                                    }else{
                                        $updatedRecords[] = $updatedRecord;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_accounts_es SET deleted = 1 WHERE id = '$id'";
                                    $updateResult = $GLOBALS['db']->query($updateData);

                                    $insertedRecord = emsToSuiteSyncLog("Accounts",$valueData,$suiteCRMFields,"","Accounts",$syncSoftware,"", "Contacts");
                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    }
                                }
                            }else{
                                $insertedRecord = emsToSuiteSyncLog("Accounts",$valueData,$suiteCRMFields,"","Accounts",$syncSoftware,"", "Contacts");    
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                }
                            }                    
                        }
                    }
                }

                if($syncSoftware == "Mautic" && ($suitecrmModule == "Contacts" || $suitecrmModule == "Leads")){
                    //To fetch all contacts from mautic
                    $result = syncESData("/api/contacts","GET",$syncSoftware,$data=array());
                    $response = (array)json_decode($result);
                    $limit = $response['total'];
                    $url = "/api/contacts?limit=".$limit;
                    $result = syncESData($url,"GET",$syncSoftware,$data=array());
                    $mauticResponse = (array)json_decode($result);
                    if($mauticResponse['total'] > 0){
                        $allContacts = (array)$mauticResponse['contacts'];
                    }
                    if(!empty($allContacts) && isset($allContacts)){                                
                        foreach ($allContacts as $keyComp => $valueComp) {
                            if($valueComp->dateIdentified != ""){
                                foreach ($valueComp->fields->core as $keys => $values) {
                                    foreach ($suiteCRMFields as $keyFields => $valueFields) {
                                        if($keyFields == $values->alias){
                                            $array[$valueFields] = $values->value;
                                        }
                                    }
                                }
                                $array['id'] = $valueComp->id;
                                if(!empty($array)){
                                    $finalMauticArray[] = $array;
                                }
                            }
                        }
                        
                        foreach ($finalMauticArray as $keyData => $valueData) {
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;
                                    $checkSql = "";
                                    $checkSql .= "SELECT * FROM vi_contacts_es ";

                                    if($suitecrmModule == "Contacts"){
                                        $checkSql .= "WHERE vi_es_contact_id = '$valueid'";
                                    }else{
                                        $checkSql .= "WHERE vi_es_lead_id = '$valueid'";
                                    }                                
                                    $checkSql .=  " and vi_es_name = 'Mautic' and deleted = 0";
                                    $selectResult = $GLOBALS['db']->fetchOne($checkSql,false,'',false);
                                    $contactESId = $esModuleID = $suiteId = '';
                                    if(!empty($selectResult)){
                                        if($suitecrmModule == "Contacts"){
                                            $esModuleID = $selectResult['vi_es_contact_id'];    
                                            $suiteId = $selectResult['vi_suitecrm_contact_id'];
                                        }elseif ($suitecrmModule == "Leads") {
                                            $esModuleID = $selectResult['vi_es_lead_id'];    
                                            $suiteId = $selectResult['vi_suitecrm_lead_id'];
                                        }
                                        $contactESId = $selectResult['id'];
                                    }//end of if
                                }
                            }

                            if($esId == $esModuleID){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteId);
                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,$suiteId,$esModule,$syncSoftware,$esId, "Contacts");          
                                    if($updatedRecord == "failure"){
                                        $failure[] = $updatedRecord;
                                    }else{
                                        $updatedRecords[] = $updatedRecord;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$contactESId'";
                                    $updateResult = $GLOBALS['db']->query($updateData);
                                    
                                    $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");   
                                    if($insertedRecord == "failure"){
                                        $failure[] = $insertedRecord;
                                    }else{
                                        $insertedRecords[] = $insertedRecord;    
                                    }
                                }
                            }else{
                                $insertedRecord = emsToSuiteSyncLog($suitecrmModule,$valueData,$suiteCRMFields,"",$esModule,$syncSoftware,"", "Contacts");   
                                if($insertedRecord == "failure"){
                                    $failure[] = $insertedRecord;
                                }else{
                                    $insertedRecords[] = $insertedRecord;    
                                } 
                            }                    
                        } 
                    }               
                }

                if($syncSoftware == "Mautic" && $suitecrmModule == "ProspectLists"){
                    //Fetch All Contacts
                    $result = syncESData("/api/contacts","GET",$syncSoftware,$data=array());
                    $response = (array)json_decode($result);
                    $limit = $response['total'];                
                    $result = syncESData("/api/contacts?limit=".$limit,"GET",$syncSoftware,$data=array());
                    $mauticResponse = (array)json_decode($result);

                    $allContacts = array();
                    if($mauticResponse['total'] > 0){
                        $allContacts = (array)$mauticResponse['contacts'];
                    }

                    if(!empty($allContacts) && isset($allContacts)){                
                        $contactsWithSegments = $finalMauticArray = $segmentInfo = $allSegmentsArray = $insertContactLeadId = $updateContactLeadId = $contactLeadId = array();
                        foreach ($allContacts as $keyComp => $valueComp) {
                            if($valueComp->dateIdentified != ""){
                                $result = syncESData("/api/contacts/".$keyComp."/segments","GET",$syncSoftware,$data=array());
                                $mauticResponse = (array)json_decode($result);                    
                                $segmentsArray = (array)$mauticResponse['lists'];

                                $finalSegmentsArray = array();
                                foreach ($valueComp->fields->core as $key => $value) {
                                    if(array_key_exists($key, $suiteCRMContactsFields)){
                                        foreach ($suiteCRMContactsFields as $keySC => $valueSC) {
                                            $array['id'] = $valueComp->id;
                                            if($keySC == $value->alias){
                                                $array[$valueSC] = $value->value;
                                            }
                                        }
                                    }
                                }                        
                                foreach ($segmentsArray as $keySeg => $valueSeg) {
                                    $valueSeg = (array)$valueSeg;
                                    foreach ($valueSeg as $key => $value) {
                                        if(array_key_exists($key, $suiteCRMFields)){
                                            $finalSegmentsArray[$key] = $value;
                                        }
                                        if($key == 'id'){
                                            $finalSegmentsArray['id'] = $value;
                                        }
                                        $finalSegmentsArray['recipients'] = $array;
                                    }
                                    $finalMauticArray[] = $finalSegmentsArray;
                                }
                            }                    
                        }

                        foreach ($finalMauticArray as $key => $value) {
                            $segments_id = $value['id'];
                            $segmentInfo[$segments_id][] = $value; 
                        }

                        foreach ($segmentInfo as $key => $value) {
                            $finalSegmentArray = array();
                            foreach ($value as $k => $v) {
                                foreach ($v as $ky => $ve) {
                                    if($ky != "recipients"){
                                        $finalSegmentArray[$ky] = $ve;
                                    }
                                    else{
                                        $finalSegmentArray[$ky][] = $ve;
                                    }   
                                }
                            }
                            $allSegmentsArray[] = $finalSegmentArray;
                        }
                        
                        foreach ($allSegmentsArray as $keyData => $valueData) {
                            foreach ($valueData as $keyid => $valueid) {
                                if($keyid == "id"){
                                    $esId = $valueid;
                                    $sql = "SELECT *
                                        FROM vi_segments_es
                                        WHERE vi_es_segments_id = '$valueid' and vi_es_name = 'Mautic' AND deleted = 0";
                                    $selectResult = $GLOBALS['db']->fetchOne($sql,false,'',false);
                                    $suiteSegmentId = $id = $esSegmentId = '';
                                    if(!empty($selectResult)){
                                        $suiteSegmentId = $selectResult['vi_suitecrm_segments_id'];
                                        $id = $selectResult['id'];
                                        $esSegmentId = $selectResult['vi_es_segments_id'];
                                    }//end of if
                                }
                            }
                            
                            if($esId == $esSegmentId){
                                $bean = BeanFactory::getBean($suitecrmModule, $suiteSegmentId);

                                if(!empty($bean) && $bean->deleted == 0){
                                    $updatedListRecord = emsToSuiteSyncLog("ProspectLists",$valueData,$suiteCRMFields,$suiteSegmentId,"Segment",$syncSoftware,$esSegmentId, $targetListSubpanelModule);          
                                    if($updatedListRecord == "failure"){
                                        $failure[] = $updatedListRecord;
                                    }else{
                                        $updatedRecords[] = $updatedListRecord;    
                                    }
                                }else{
                                    $updateData = "UPDATE vi_segments_es SET deleted = 1 WHERE id = '$id'";
                                    $updateResult = $GLOBALS['db']->query($updateData);
                                    
                                    $insertListResponse = emsToSuiteSyncLog("ProspectLists",$valueData,$suiteCRMFields,"","Segment",$syncSoftware,"", $targetListSubpanelModule);    
                                    if($insertListResponse == "failure"){
                                        $failure[] = $insertListResponse;
                                    }else{
                                        $insertedRecords[] = $insertListResponse;    
                                    }
                                }
                            }else{
                                $insertListResponse = emsToSuiteSyncLog("ProspectLists",$valueData,$suiteCRMFields,"","Segment",$syncSoftware,"", $targetListSubpanelModule);    
                                if($insertListResponse == "failure"){
                                    $failure[] = $insertListResponse;
                                }else{
                                    $insertedRecords[] = $insertListResponse;    
                                }
                            }                    
                        }
                    }
                }
                
                $failure = count($failure);
                if($suitecrmModule == "AOS_Products"){
                    $suitecrmModule = "Products";
                }
                $tdyDate = date("Y-m-d H:i:s");
                $html = "<tr><td>".$tdyDate."</td><td style='text-align:center !important;'>".$suitecrmModule."</td><td style='text-align:center !important;'>".count(array_unique($insertedRecords))."</td><td style='text-align:center !important;'>".count(array_unique($updatedRecords))."</td><td style='text-align:center !important;'>".$failure."</td></tr>";
                echo $html;  
            }//end of if status
        }//end of if
    }//end of method
}//end of class
new VIAddSynchronizeData();
?>