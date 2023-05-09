<?php
/*********************************************************************************
 * This file is part of package Email Software ntegartion.
 * 
 * Author : Variance InfoTech PVT LTD (http://www.varianceinfotech.com)
 * All rights (c) 2022 by Variance InfoTech PVT LTD
 *
 * This Version of Email Software Integartion is licensed software and may only be used in 
 * alignment with the License Agreement received with this Software.
 * This Software is copyrighted and may not be further distributed without
 * written consent of Variance InfoTech PVT LTD
 * 
 * You can contact via email at info@varianceinfotech.com
 * 
 ********************************************************************************/
function getPlanType($software){
    $selectData = "SELECT plan_type FROM vi_api_configuration WHERE email_software = '$software ' and deleted = 0";
    $row = $GLOBALS['db']->fetchOne($selectData);
    if(!empty($row)){
        $planType = $row['plan_type'];
        return $planType;
    }//end of if
}

function checkRecordExist($fromId,$syncSoftware,$moduleName){
    if($moduleName == "AOS_Products"){
        $moduleName = "Products";   
    }elseif($moduleName == "ProspectLists"){
        $moduleName = "Targets - Lists";    
    }
    $selectSyncData = "SELECT * FROM vi_emailsoftwareintegrationsynclog WHERE from_record = '$fromId' AND name = '$moduleName' AND email_software = '$syncSoftware' AND action_type = 'Insert' AND deleted = 0";
    $result = $GLOBALS['db']->fetchOne($selectSyncData);
    
    if(!empty($result)){
        $toRecordId = $result['viem_to_record'];
        return $toRecordId;
    }//end of if
}//end of function

function getEsModuleName($mappingId){
    $getEsModuleNameQuery = "SELECT * FROM vi_module_mapping where module_mapping_id = '$mappingId' AND deleted = 0";
    $result = $GLOBALS['db']->fetchOne($getEsModuleNameQuery);
    $esModuleName = $result['es_module'];
    return $esModuleName;
}

function syncESData($endPointUrl,$method,$syncSoftware,$data){
    $selectData = "SELECT * FROM vi_api_configuration WHERE email_software = '$syncSoftware' and deleted = 0";
    $selectResult = $GLOBALS['db']->fetchOne($selectData,false,'',false);
    if(!empty($selectResult['api_key'])){
        if($selectResult['email_software'] == "SendGrid"){
            $url = SENDGRIDAPIURL.$endPointUrl;
            $apiKey = 'Authorization: Bearer '.$selectResult['api_key'];                   
        }elseif ($selectResult['email_software'] == "Mautic") {
            $allKeys = (array)json_decode(html_entity_decode($selectResult['api_key']));
            $url = trim($allKeys['mauticUrl']).trim($endPointUrl);
            $finalKey = base64_encode ($allKeys['mauticUsername'].":".$allKeys['mauticPassword']);
            $apiKey = 'Authorization: Basic '.$finalKey;
        }elseif ($selectResult['email_software'] == "ConstantContact") {
            $allKeys = (array)json_decode(html_entity_decode($selectResult['api_key']));
            $url = CONSTANTCONTACTAPIURL.$endPointUrl.$allKeys['constantContactApiKey'];
            $finalKey = $allKeys['accessToken'];
            $apiKey = 'Authorization: Bearer '.$finalKey;
        }elseif ($selectResult['email_software'] == "ActiveCampaigns") {
            $allKeys = (array)json_decode(html_entity_decode($selectResult['api_key']));
            $url = $allKeys['activeCampaignsUrl'].$endPointUrl;
            $finalKey = $allKeys['activeCampaignsApiToken'];
            $apiKey = 'Api-Token: '.$finalKey;
        }elseif($selectResult['email_software'] == "SendInBlue"){
            $url = SENDINBLUEAPIURL.$endPointUrl;
            $apiKey = 'api-key:'.$selectResult['api_key'];
        }
    }

    $headers = array(
                    $apiKey,
                    "Content-type: application/json",
                );
    if($data != ""){
        $data = json_encode($data);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if($method != 'GET'){
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);   
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // converting
    $response = curl_exec($ch);
    return $response;
    curl_close($ch);
}

//7 params
function processESData($url,$method,$finalSuiteArray,$actionType,$recordId,$suitecrmModule,$syncSoftware){
    $flag = 0;
    if($syncSoftware == 'SendInBlue' && $suitecrmModule == 'ProspectLists' && $actionType == 'Update'){
        $fetchListId = "SELECT * FROM vi_segments_es WHERE vi_suitecrm_segments_id = '$recordId' and vi_es_name = 'SendInBlue' AND deleted = 0";
        $fetchListIDResult = $GLOBALS['db']->fetchOne($fetchListId,false,'',false);
        $esSegmentsId = $fetchListIDResult['vi_es_segments_id'];

        $res = syncESData("contacts/lists","GET",$syncSoftware,"");
        $checkResponse = (array)json_decode($res);
        
        foreach($checkResponse['lists'] as $ckey => $cvalue){
            if($cvalue->id == $esSegmentsId){
                if(in_array($cvalue->name,$finalSuiteArray)){
                    syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,"",$actionType,$esSegmentsId);
                    $flag = 1;
                    return $esSegmentsId;
                }
            }
        }
        if($flag == 0){
            unset($finalSuiteArray['folderId']);
            $resultAddNew = syncESData($url,$method,$syncSoftware,$finalSuiteArray);
            $result = json_decode($resultAddNew);                
        }
    }else{
        $resultAddNew = syncESData($url,$method,$syncSoftware,$finalSuiteArray);
        $result = json_decode($resultAddNew);
    }
    
    if($syncSoftware == "ActiveCampaigns" && $suitecrmModule == "ProspectLists"){
        //first List all groups, fetch group_id (admin) and then Create a list group permission
        if(!empty($result)){
            $listId = $result->list->id;
            $resultAllGroups = syncESData("/api/3/groups","GET",$syncSoftware,$data=array());
            $resultAllGroups = json_decode($resultAllGroups);
            
            $groupId = $resultAllGroups->groups[0]->id;
            $data = array (
                      'listGroup' => 
                      array (
                        'listid' => $listId,
                        'groupid' => $groupId,
                      ),
                    );
            $resultAddToGroup = syncESData("/api/3/listGroups","POST",$syncSoftware,$data);
            $resultAddToGroup = json_decode($resultAddToGroup);
        }
    }
    
    if($flag == 0){
        if((!empty($result->errors) && isset($result->errors)) || (isset($result->message) && $result->message == 'Key not found')){
            if(isset($result->errors)){
               foreach ($result->errors as $keyError => $valueError) {
                    if($syncSoftware == "SendGrid"){
                        if(isset($result->id) && $result->id != ''){
                            $esListId = $result->id;
                        }else{
                            $esListId = '';
                        }//end of else
                        syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,$valueError->message,$actionType,$esListId);
                    }else if($syncSoftware == "Mautic"){
                        syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,$valueError->message,$actionType,$result->contact->id);
                    }else if($syncSoftware == "ActiveCampaigns"){
                        if(isset($result->contact->id) && $result->contact->id != ''){
                            $esContactId = $result->contact->id;
                        }else{
                            $esContactId = '';
                        }//end of else
                        syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,$valueError->title,$actionType,$esContactId);
                    }
                    return "failure";
                } 
            }elseif(isset($result[0]->error_key)){
                syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,$result[0]->error_key,$actionType,$result->contact->id);
                return "failure";
            }elseif(isset($result->message)){
                syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,$result->message,$actionType,$result->contact->id);
                return "failure";
            }
            
            $errorMessage = $result[0]->error_message;
            if($errorMessage != ""){
                syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,$errorMessage,$actionType,$result->id);
                return "failure";
            }
        }else if($syncSoftware == 'ConstantContact' && is_array($result) && (isset($result[0]->error_key))){
            syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,$result[0]->error_key,$actionType,'');
            return "failure";
        }else{
            $result = (array)$result;
            $errorMessage = "";
            if(isset($result[0]->error_message) && !empty($result[0]->error_message) ){
                $errorMessage = $result[0]->error_message;
            }elseif(isset($result['message']) && !empty($result['message'])){
                if($result['message'] == "No Result found for _List with id 1"){
                    $result['message'] = "Active Campaign did not provide API for Update Contact List";
                }
                $errorMessage = $result['message'];
            }elseif (isset($result['error']) && !empty($result['error'])) {
                $errorMessage = $result['message'];
            }else if(isset($result->code) && $result->code != ''){
                $errorMessage = $result->message;
            }//end of else if

            $esresultId = "";
            if($syncSoftware == "SendGrid"){
                $esresultId = $result['id'];
            }else if($syncSoftware == "Mautic" || $syncSoftware == "ActiveCampaigns"){
                if($suitecrmModule == "Contacts" || $suitecrmModule == "Leads"){
                    $esresultId = $result['contact']->id;
                }elseif($suitecrmModule == "Accounts"){
                    if($syncSoftware == "Mautic"){
                        $esresultId = $result['company']->id;    
                    }else{
                        $esresultId = $result['account']->id;
                    }                        
                }elseif($suitecrmModule == "Campaigns"){
                    $esresultId = $result['campaign']->id;    
                }elseif($suitecrmModule == "ProspectLists"){
                    if($syncSoftware == "ActiveCampaigns" && $actionType == 'Update' && empty($result)){
                        $listIdResult = explode('/', $url);
                        $esresultId = end($listIdResult);
                    }else{
                        $esresultId = $result['list']->id;    
                    }//end of else
                }elseif($suitecrmModule == "AOS_Products"){
                    $esresultId = $result['asset']->id;
                }
            }else if($syncSoftware == "ConstantContact" || $syncSoftware == "SendInBlue"){
                if(isset($result['id']) && !empty($result['id'])){
                    $esresultId = $result['id'];
                }else{
                    if($suitecrmModule == "Campaigns"){
                        $esresultId = str_replace("emailCampaigns/", "", $url);
                    }elseif($suitecrmModule == "ProspectLists"){
                        $esresultId = str_replace("contacts/lists/", "", $url);    
                    }
                }
            }
            if($actionType == "Update" && $esresultId == ""){
                if($suitecrmModule == "Contacts"){
                    $fetchEsIdFromtbl = "SELECT * FROM vi_contacts_es WHERE vi_suitecrm_contact_id = '$recordId' and vi_es_name = '$syncSoftware' AND deleted = 0";
                    $res = $GLOBALS['db']->fetchOne($fetchEsIdFromtbl,false,'',false);
                    $esresultId = $res['vi_es_contact_id'];
                }elseif($suitecrmModule == "Leads"){
                    $fetchEsIdFromtbl = "SELECT * FROM vi_contacts_es WHERE vi_suitecrm_lead_id = '$recordId' and vi_es_name = '$syncSoftware' AND deleted = 0";
                    $res = $GLOBALS['db']->fetchOne($fetchEsIdFromtbl,false,'',false);
                    $esresultId = $res['vi_es_lead_id'];
                }                
            }

            if($actionType == 'Update' && empty($result)){
                if($syncSoftware == "ActiveCampaigns" && $suitecrmModule == "ProspectLists"){
                    $errorMessage = "Active Campaigns didn't provide the API for Update List(Segments). So List name doesn't update during Update Record from SuiteCRM but If Lead/Contacts found during the Update Target List Record from SuiteCRM then it's Add/Update the Contact against List in Active Campaigns.";
                }//end of if
            }//end of if
            
            $id = syncEsLog('VI_EmailSoftwareIntegartionSyncLog',$syncSoftware,$recordId,$suitecrmModule,$errorMessage,$actionType,$esresultId);
            if($errorMessage != ''){
                return "failure";
            }//end of if

            $rendomRecordId = create_guid();
            if($actionType == "Insert"){
                if($suitecrmModule == "Contacts"){
                    $tableName = "vi_contacts_es";
                    $data = array('id' => $rendomRecordId,'vi_suitecrm_contact_id' => $recordId,'vi_es_contact_id' => $esresultId,'vi_suitecrm_lead_id' => '','vi_es_name' => $syncSoftware,'vi_es_list_id' => '','vi_suitecrm_module' => $suitecrmModule,'vi_es_lead_id' => '', 'deleted' => 0);
                }elseif($suitecrmModule == "Leads"){
                    $tableName = "vi_contacts_es";
                    $data = array('id' => $rendomRecordId,'vi_suitecrm_contact_id' => '','vi_es_contact_id' => '','vi_suitecrm_lead_id' => $recordId,'vi_es_name' => $syncSoftware,'vi_es_list_id' => '','vi_suitecrm_module' => $suitecrmModule,'vi_es_lead_id' => $esresultId, 'deleted' => 0);
                }elseif($suitecrmModule == "Accounts"){
                    $tableName = "vi_accounts_es";
                    $data = array('id' => $rendomRecordId,'vi_suitecrm_account_id' => $recordId,'vi_es_account_id' => $esresultId,'vi_es_name' => $syncSoftware, 'deleted' => 0);
                    
                }elseif ($suitecrmModule == "Campaigns") {
                    $tableName = "vi_campaigns_es";
                    $data = array('id' => $rendomRecordId,'vi_suitecrm_campaigns_id' => $recordId,'vi_es_campaign_id' => $esresultId,'vi_es_name' => $syncSoftware, 'deleted' => 0);
                }elseif ($suitecrmModule == "ProspectLists") {
                    $tableName = "vi_segments_es";
                    $data = array('id' => $rendomRecordId,'vi_suitecrm_segments_id' => $recordId,'vi_es_segments_id' => $esresultId,'vi_es_name' => $syncSoftware, 'deleted' => 0);
                }elseif ($suitecrmModule == "AOS_Products") {
                    $tableName = "vi_assets_es";
                    $data = array('id' => $rendomRecordId,'vi_suitecrm_assets_id' => $recordId,'vi_es_assets_id' => $esresultId,'vi_es_name' => $syncSoftware, 'deleted' => 0);
                } 
                insertESRecord($tableName,$data);           
            }        
            return $esresultId;
        } 
    }
}

//insert record
function insertESRecord($tableName,$data){
    //data key
    $key = array_keys($data);
    $fieldName = implode(",", $key);
    
    //data val
    $val = array_values($data);
    $fieldVal = implode("','",$val);
    
    //insert
    $insertEMSData = "INSERT INTO $tableName ($fieldName) VALUES('$fieldVal')";
    $insertEMSDataResult = $GLOBALS['db']->query($insertEMSData);
    return $insertEMSDataResult;
}//end of function

function syncEsLog($moduleName,$emailSoftware,$value,$esModule,$valErrorMessage,$actionType,$fromModuleRecordId){
    
    $eslBean = BeanFactory::newBean($moduleName);
    $eslBean->email_software = $emailSoftware;
    $eslBean->from_record = $value;
    $eslBean->viem_to_record = $fromModuleRecordId;

    if($esModule == "ProspectLists"){
        $eslBean->name = "Target List";
        if($emailSoftware == "SendGrid" || $emailSoftware == "ConstantContact"
            || $emailSoftware == "ActiveCampaigns" || $emailSoftware == "SendInBlue"){
            $eslBean->to_module = "Contact List";    
        }elseif($emailSoftware == "Mautic"){
            $eslBean->to_module = "segments";    
        }            
    }elseif ($esModule == "Leads") {
        $eslBean->to_module = "Contacts";
        $eslBean->name = $esModule;   
    }elseif ($esModule == "Accounts") {
        if($emailSoftware == "ActiveCampaigns"){
            $eslBean->to_module = "Organizations";    
        }else{
            $eslBean->to_module = "Companies";    
        }            
        $eslBean->name = $esModule;
    }elseif ($esModule == "AOS_Products") {
        $eslBean->to_module = "Assets";
        $eslBean->name = 'Products';
    }else{
        $eslBean->to_module = $esModule;   
        $eslBean->name = $esModule;
    }       
    if($emailSoftware == "SendGrid"){
        $eslBean->sync_type = "SC2SG";
    }elseif ($emailSoftware == "Mautic") {
        $eslBean->sync_type = "SC2MA";
    }elseif ($emailSoftware == "ConstantContact") {
        $eslBean->sync_type = "SC2CC";
    }elseif ($emailSoftware == "ActiveCampaigns") {
        $eslBean->sync_type = "SC2AC";
    }elseif ($emailSoftware == "SendInBlue") {
        $eslBean->sync_type = "SC2SB";
    }
    
    if($valErrorMessage == ""){
        $eslBean->status = "Successfull";
    }else{
        if($emailSoftware == "ActiveCampaigns" && $actionType == 'Update' && $esModule == "ProspectLists"){
            $eslBean->status = "Successfull";
        }else{
            $eslBean->status = "Failed";
        }//end of else
    }
    
    $eslBean->action_type = $actionType;    
    $eslBean->viem_message_c = $valErrorMessage;
    $eslBean->save();
    return $eslBean->id;
}

function relatedContactsData($recipients,$recipientsFromSendGrid,$syncSoftware,$listID,$es, $targetListSubpanelModule, $esListId, $insertContactLeadId, $updateContactLeadId){
    if(is_array($listID)){
        $listIdString = implode(',',$listID);
    }else{
        $listIdString = $listID;
    }
   
    if(!empty($recipients)){
        foreach ($recipients as $keyRecipent => $valRecipent) {
            $contactLeadId = getEMSToolContactsData($esListId, 'SendGrid');

            $fromRecordId = $contactId = $valRecipent['cid'];
            unset($valRecipent['cid']);
            $contactData = array();
            $contactData[] = $valRecipent;
            $planType = getPlanType($syncSoftware);
            if($planType == 1){
                $url = NMADDUPDATECONTACTS;
                $data = array('list_ids'=>array($listIdString),'contacts'=> $contactData);
                $method = "PUT";
            }else{
                $url = LMADDUPDATECONTACTS;
                $data = $contactData;
                if(array_key_exists($valRecipent['email'], $recipientsFromSendGrid)){
                    $method = "PATCH";
                }else{
                    $method = "POST";
                }
            }
            
            $response = syncESData($url,$method,$syncSoftware,$data);
            $result = json_decode($response);
            if($planType == 2) {
                $persistedRecipients = $result->persisted_recipients;
                $fromRecordId = $persistedRecipients[0];
            }
            if(!empty($result->errors) && isset($result->errors)){
                foreach ($result->errors as $keyError => $valueError) {
                    $failure[] = $valueError->message;
                    $updateEmail = BeanFactory::newBean("VI_EmailSoftwareIntegartionSyncLog");
                    $updateEmail->viem_message_c = $valueError->message;
                    $updateEmail->save();
                }
            }else{
                if($planType == 1){
                    if($targetListSubpanelModule == 'Contacts'){
                        $relatedContactLeadId = "vi_suitecrm_contact_id = '$fromRecordId'";
                    }else{
                        $relatedContactLeadId = "vi_suitecrm_lead_id = '$fromRecordId'";
                    }//end of else

                    $selData = "SELECT * FROM vi_contacts_es WHERE ".$relatedContactLeadId." and vi_es_name = 'SendGrid' AND deleted = 0";
                    $selDataRow = $GLOBALS['db']->fetchOne($selData);
                    $listIdContactId = $listContactId = '';
                    if(!empty($selDataRow)){
                        $listIdContactId = $selDataRow['id'];
                        $listContactId = $selDataRow['vi_es_contact_id'];
                    }//end of if
                    $suiteCRMLeadId = $suiteCRMContactId = '';
                    if($targetListSubpanelModule == 'Leads'){
                        $suiteCRMLeadId = $fromRecordId;
                    }else{
                        $suiteCRMContactId = $fromRecordId;
                    }//end of else
                    
                    if($listContactId != ''){
                        $bean = BeanFactory::getBean($targetListSubpanelModule, $fromRecordId);

                        if($bean->email1 != ''){
                            $jsonDecodeData = getRelatedSendGridContactsId($bean, $syncSoftware);;
                            $syncId = $jsonDecodeData->result[0]->id;
                            
                            if($jsonDecodeData->contact_count != 0){
                                $updateListIdContacts = "UPDATE vi_contacts_es SET vi_es_list_id = '$listIdString' WHERE ".$relatedContactLeadId." and vi_es_name = 'SendGrid' AND deleted = 0";
                                $updateRes = $GLOBALS['db']->query($updateListIdContacts);

                                removeContactsLeadFromListForAllEMSTool($contactLeadId, $listContactId, $insertContactLeadId, $updateContactLeadId, $syncSoftware, $esListId, $fromRecordId, $targetListSubpanelModule, $planType);
                            }else{
                                $updateContactLeadId = addContactsLeadsFromList($contactLeadId, $targetListSubpanelModule, $fromRecordId, $esListId, $syncSoftware, $emailData=array(), $data, $insertContactLeadId, $updateContactLeadId);

                                $autoGenerateId = create_guid();
                                $jobId = $result->job_id;
                                $insertContactLeadId[] = $jobId;
                                $insertRecords = "INSERT INTO vi_contacts_es(id,vi_suitecrm_contact_id,vi_es_contact_id,vi_suitecrm_lead_id,vi_es_name,vi_es_list_id,vi_suitecrm_module,deleted)values('$autoGenerateId','$suiteCRMContactId','$jobId','$suiteCRMLeadId','SendGrid','$listIdString','$targetListSubpanelModule',0)";
                                $insertRecordsResult = $GLOBALS['db']->query($insertRecords);
                            }//end of else
                        }else{
                            $updateContactLeadId = addContactsLeadsFromList($contactLeadId, $targetListSubpanelModule, $fromRecordId, $esListId, $syncSoftware, $emailData=array(), $data, $insertContactLeadId, $updateContactLeadId);

                            $autoGenerateId = create_guid();
                            $jobId = $result->job_id;
                            $insertContactLeadId[] = $jobId;
                            $insertRecords = "INSERT INTO vi_contacts_es(id,vi_suitecrm_contact_id,vi_es_contact_id,vi_suitecrm_lead_id,vi_es_name,vi_es_list_id,vi_suitecrm_module,deleted)values('$autoGenerateId','$suiteCRMContactId','$jobId','$suiteCRMLeadId','SendGrid','$listIdString','$targetListSubpanelModule',0)";
                            $insertRecordsResult = $GLOBALS['db']->query($insertRecords);
                        }
                    }else{
                        $updateContactLeadId = addContactsLeadsFromList($contactLeadId, $targetListSubpanelModule, $fromRecordId, $esListId, $syncSoftware, $emailData=array(), $data, $insertContactLeadId, $updateContactLeadId);

                        $autoGenerateId = create_guid();
                        $jobId = $result->job_id;
                        $insertContactLeadId[] = $jobId;
                        $insertRecords = "INSERT INTO vi_contacts_es(id,vi_suitecrm_contact_id,vi_es_contact_id,vi_suitecrm_lead_id,vi_es_name,vi_es_list_id,vi_suitecrm_module,deleted)values('$autoGenerateId','$suiteCRMContactId','$jobId','$suiteCRMLeadId','SendGrid','$listIdString','$targetListSubpanelModule',0)";
                        $insertRecordsResult = $GLOBALS['db']->query($insertRecords);
                    }
                }else{
                    $url = "contactdb/lists/".$listIdString."/recipients/".$fromRecordId;
                    $method = "POST";
                    $responseinfo = syncESData($url,$method,$syncSoftware,"");
                    $updateListIdContacts = "UPDATE vi_contacts_es SET vi_es_list_id = '$listIdString' WHERE vi_es_contact_id = '$fromRecordId' and vi_es_name = 'SendGrid' AND deleted = 0";
                    $updateRes = $GLOBALS['db']->query($updateListIdContacts);

                    removeContactsLeadFromListForAllEMSTool($contactLeadId, $fromRecordId, $insertContactLeadId, $updateContactLeadId, $syncSoftware, $esListId, $contactId, $targetListSubpanelModule, $planType);
                }
            }
        }
    }
}

function insertScheduleSyncData($syncSoftware,$id,$limit,$batchRecord,$suitecrmModule){
    $startDateTime = date('Y-m-d H:i:s');
    $insertBatchRecord = "INSERT INTO vi_ems_schedule_sync(ems_software,mapping_id,batch_record,status,start_date_time)VALUES('$syncSoftware','$id','$limit',0,'$startDateTime')";
    $insertBatchRecordResult = $GLOBALS['db']->query($insertBatchRecord);

    $tot = $limit+$batchRecord;
    $moduleBean = BeanFactory::getBean($suitecrmModule);
    $moduleList = $moduleBean->get_list('date_entered','',$limit,$tot,$batchRecord,0);
    $allRecords = array();

    if(isset($moduleList) && !empty($moduleList)){
        foreach ($moduleList as $key => $obj) {
            if(!empty($obj) && is_array($obj)){
                foreach ($obj as $k => $value) {
                    $allRecords[] = $value->id;
                }//end of foreach
            }//end of if
        }//end of foreach
    }//end of if

    if(empty($allRecords)){
        $selData = "SELECT * FROM vi_ems_schedule_sync WHERE ems_software = '$syncSoftware' AND status = 0 AND mapping_id = '$id'";
        $selDataRow = $GLOBALS['db']->fetchOne($selData);
        $scheduleSyncId = $selDataRow['id'];
        updateScheduleSyncData($scheduleSyncId,0,1);
    }
}

function updateScheduleSyncData($id,$limit,$status){
    if($status == 1){
        $endDateTime = date('Y-m-d H:i:s');
        $updateStatus = "UPDATE vi_ems_schedule_sync SET status = 1, end_date_time = '$endDateTime' WHERE id = '$id'";
        $updateStatusResult = $GLOBALS['db']->query($updateStatus);
    }else{
        $updateBatchRecord = "UPDATE vi_ems_schedule_sync SET batch_record = '$limit' WHERE id = '$id'";
        $updateBatchRecordResult = $GLOBALS['db']->query($updateBatchRecord);
    }
}

function updateData($allRecordId,$scheduleSyncId,$limit,$batchRecord,$suitecrmModule, $whereQuery){
    if(!empty($allRecordId)){
        updateScheduleSyncData($scheduleSyncId,$limit,0);
        $tot = $limit+$batchRecord;
        $moduleBean = BeanFactory::getBean($suitecrmModule);
        $moduleList = $moduleBean->get_list('date_entered',$whereQuery,$limit,$tot,$batchRecord,0);

        $allRecords = array();
        if(isset($moduleList) && !empty($moduleList)){
            foreach ($moduleList as $key => $obj) {
                if(!empty($obj) && is_array($obj)){
                    foreach ($obj as $k => $value) {
                        $allRecords[] = $value->id;
                    }//end of foreach
                }//end of if
            }//end of foreach
        }//end of if

        if(empty($allRecords)){
            updateScheduleSyncData($scheduleSyncId,0,1);
        }
    }else{
        updateScheduleSyncData($scheduleSyncId,0,1);
    }
}

function getFieldValue($vfield, $moduleBean, $value, $suitecrmModule, $esSoftware){
    $fieldValue = '';
    if(isset($moduleBean->field_defs[$vfield])){
        $fieldDef = $moduleBean->field_defs[$vfield];
        if($fieldDef['type'] == 'relate'){
            $relateTableName = $fieldDef['table'];
            if($moduleBean->load_relationship($relateTableName)){
                $relationship = $moduleBean->$relateTableName;
                if($relationship->relationship->type == "one-to-many"){
                    $fieldValue = $value->$vfield;
                }else if($relationship->relationship->type == "many-to-many"){
                    $relatedTableName = $relationship->relationship->def['table'];
                    $relationName = $relationship->relationship->def['name'];

                    if(isset($relationship->relationship->def['relationships'])){
                        $relateData = $relationship->relationship->def['relationships'][$relationName];
                        $lhsModule = $relationship->relationship->def['relationships'][$relationName]['lhs_module'];
                        $rhsModule = $relationship->relationship->def['relationships'][$relationName]['rhs_module'];

                        if($rhsModule == $suitecrmModule){
                            $lhsModuleFieldName = $relationship->relationship->def['relationships'][$relationName]['join_key_lhs'];
                        }//end of if
                        if($lhsModule == $fieldDef['module']){
                            $rhsModuleFieldName = $relationship->relationship->def['relationships'][$relationName]['join_key_rhs'];
                        }//end of 

                        if($lhsModuleFieldName != '' && $rhsModuleFieldName != ''){
                            $getRelationModuleData = "SELECT * FROM $relatedTableName WHERE deleted = 0";
                            $getRelationModuleDataResult = $GLOBALS['db']->query($getRelationModuleData);

                            while($getRelationModuleDataRow = $GLOBALS['db']->fetchByAssoc($getRelationModuleDataResult)){
                                if(isset($getRelationModuleDataRow[$lhsModuleFieldName]) && isset($getRelationModuleDataRow[$rhsModuleFieldName])){
                                    if($getRelationModuleDataRow[$rhsModuleFieldName] == $value->id){
                                        $relateId = $getRelationModuleDataRow[$lhsModuleFieldName];

                                        if($relateTableName == 'accounts' || $relateTableName == 'campaigns'){
                                            $fieldName = 'name';
                                        }else{
                                            $fieldName = 'last_name';
                                        }//end of else

                                        $getRelatedFieldName = "SELECT $fieldName FROM $relateTableName WHERE id='$relateId' AND deleted = 0";
                                        $getRelatedFieldNameData = $GLOBALS['db']->fetchOne($getRelatedFieldName);
                                        $fieldValue = $getRelatedFieldNameData['name'];
                                    }//end of if
                                }//end of if
                            }//end of while
                        }//end of if
                    }//end of if
                }//end of else if
            }else{
                $fieldValue = $value->$vfield;  
            }//end of else
        }else if($fieldDef['type'] == 'multienum' && $esSoftware == 'ActiveCampaigns'){
            $fieldValue = unencodeMultienum($value->$vfield);
        }else if(($fieldDef['type'] == 'date' || $fieldDef['type'] == 'datetimecombo' || $fieldDef['type'] == 'datetime') && $esSoftware == 'SendInBlue'){
            $fieldValue = date('Y-m-d', strtotime($value->$vfield));
        }else{
            $fieldValue = $value->$vfield;
        }//end of else
    }else{
        $fieldValue = $value->$vfield;
    }//end of else
    return $fieldValue;
}//end of function

//Get Active Campaigns Accounts Custom Field List
function getActiveCampaignsAccountsCustomFields($syncSoftware){
    $fields = array();
    $activeCampaignAccountsCustomFieldList = syncESData("/api/3/accountCustomFieldMeta","GET",$syncSoftware, $data=array());
    $activeCampaignAccountsCustomFieldListResult = (array)json_decode($activeCampaignAccountsCustomFieldList);
    $customFieldList = $activeCampaignAccountsCustomFieldListResult['accountCustomFieldMeta'];
    foreach ($customFieldList as $index => $fieldsData) {
        $fieldName = $fieldsData->fieldLabel;
        $fields[$fieldsData->id] = array('field' => $fieldName, 'type' => $fieldsData->fieldType);
    }//end of foreach
    return $fields;
}//end of function

//Insert Active Campaigns Accounts Custom Fields Value
function insertActiveCampaignsAccountsCustomFieldsValue($syncSoftware, $insertResult, $finalSuiteArray, $fields){
    $accountCustomFieldData = array();
    foreach ($fields as $customFieldId => $fieldData) {
        if(array_key_exists($fieldData['field'], $finalSuiteArray['account'])){
            $accountCustomFieldData['accountCustomFieldDatum']['customFieldId'] = $customFieldId;

            if($fieldData['type'] != 'date' || $fieldData['type'] != 'datetime' || $fieldData['type'] != 'datetimecombo'){
                $accountCustomFieldData['accountCustomFieldDatum']['fieldValue'] = $finalSuiteArray['account'][$fieldData['field']];
            }//end of if

            if($fieldData['type'] == 'text' || $fieldData['type'] == 'textarea' || $fieldData['type'] == 'hidden'){
                $accountCustomFieldData['accountCustomFieldDatum']['accountId'] = $insertResult;
            }else{
                $accountCustomFieldData['accountCustomFieldDatum']['customerAccountId'] = $insertResult;
                
                if($fieldData['type'] == 'date'){
                    $accountCustomFieldData['accountCustomFieldDatum']['fieldValue'] = date('Y-m-d', strtotime($finalSuiteArray['account'][$fieldData['field']]));
                }else if($fieldData['type'] == 'datetime' || $fieldData['type'] == 'datetimecombo'){
                    $accountCustomFieldData['accountCustomFieldDatum']['fieldValue'] = 
                    date(DATE_ISO8601, strtotime($finalSuiteArray['account'][$fieldData['field']]));
                }else{
                    $accountCustomFieldData['accountCustomFieldDatum']['fieldValue'] = $finalSuiteArray['account'][$fieldData['field']];
                }//end of else
            }//end of else
            
            $insertCustomFields = syncESData("/api/3/accountCustomFieldData", 'POST', $syncSoftware, $accountCustomFieldData);
            $insertCustomFieldsResult = (array)json_decode($insertCustomFields);
        }//end of if
    }//end of foreach
}//end of function

//Get Module Fields Value for Automatic Sync
function getModuleFieldsValue($suitecrmFields, $moduleBean, $currentModuleName, $flag){
    $fields = array();
    foreach ($suitecrmFields as $keyField => $valueField) {
        if($flag == true && $keyField == "email" && ($currentModuleName == "Leads" || $currentModuleName == "Contacts")){
            if($currentModuleName == "Leads"){
                $currentRecordEmailId = $_REQUEST['Leads0emailAddress0'];
            }elseif ($currentModuleName == "Contacts") {
                $currentRecordEmailId = $_REQUEST['Contacts0emailAddress0'];
            }
            $fields[$keyField] = $currentRecordEmailId; 
        }else{
            $fieldDef = $moduleBean->field_defs[$valueField];
            $fieldVal = $moduleBean->$valueField;

            if($keyField == "email" && ($currentModuleName == "Leads" || $currentModuleName == "Contacts")){
                if(isset($moduleBean->email1) && $moduleBean->email1 != ''){
                    $fieldVal = $moduleBean->$valueField;
                }else{
                    $fieldVal = $moduleBean->email_addresses[0]['attributes']['email_address'];
                }//end of else
            }else{
                $fieldVal = $moduleBean->$valueField;
            }//end of else

            if($fieldDef['type'] == 'multienum'){
                $optionList = unencodeMultienum($fieldVal);
                if (empty($optionList)) {
                    $fieldValue = '';
                }//end of if
                $fieldValue = '||' . implode('||', $optionList) . '||';
                $fields[$keyField] =  $fieldValue;
            }else{
              $fields[$keyField] =  $fieldVal;
            }//end of else
        }//end of else
    }//end of foreach
    return $fields;
}//end of function

//Get Current Record ids of SuiteCRM
function getModuleCurrentRecordIds($bean, $currentModuleName, $conditionMatchedRecords){
    $currentRecordIdList = array();
    if($currentModuleName == "Campaigns"){
        $currentRecordId = (array)$bean->campaign_id;
    }else{
        if(isset($bean->record_id) && $bean->record_id != ''){
            $currentRecordId = (array)$bean->record_id;
        }else if(isset($bean->related_id) && $bean->related_id != ''){
            $currentRecordId = (array)$bean->related_id;
        }else if(isset($bean->id) && $bean->id != ''){
            $currentRecordId = (array)$bean->id;
        }//end of else if
    }//end of else
        
    foreach ($currentRecordId as $key => $id) {
        if(array_key_exists($id, $conditionMatchedRecords)){
            $currentRecordIdList[] = $id;
        }//end of if
    }//end of foreach
    return $currentRecordIdList;
}//end of function

//Get Module All Fields
function getEMSModuleFields($module, $stepName) {
    require_once('include/utils.php');
    $bean = BeanFactory::newBean($module);
    $field = $bean->getFieldDefinitions();

    $addressFieldData = array();
    foreach($field as $value){
        if($value['type'] == 'varchar'){
            if(isset($value['group']) && $value['group'] != ''){
                $fieldName = $value['name'];
                $fieldLabel = translate($value['vname'], $module);
                $lastChar = substr($fieldLabel, -1);
                if($lastChar == ':'  || $lastChar == ''){
                    $fieldLabel = substr_replace($fieldLabel, "", -1);
                    $lastCharColon = substr($fieldLabel, -1);
                    if($lastCharColon == ':'){
                        $fieldLabel = substr_replace($fieldLabel, "", -1);
                    }//end of if
                }//end of if
                $addressFieldData[$fieldName] = $fieldLabel;
            }//end of if
        }//end of if
    }//end of foreach

    unset($addressFieldData['email2']);
    if($stepName == 'stepThree'){
        unset($addressFieldData['email1']);
    }//end of if

    $editViewFieldData = getEMSEditDetailViewFields('editview', $module, $stepName);
    if($stepName == 'stepTwo'){
        $editDetailViewFieldsMerge = array_merge($editViewFieldData, $addressFieldData); //merge array
    }else if($stepName == 'stepThree'){
        $detailViewFieldData = getEMSEditDetailViewFields('detailview', $module, $stepName);
        $editDetailViewFieldsMerge = array_merge($editViewFieldData, $addressFieldData, $detailViewFieldData); //merge array
    }//end of if

    $value = '';
    asort($editDetailViewFieldsMerge);
    return get_select_options_with_id($editDetailViewFieldsMerge, $value);
    die;
}//end of function

//Get Module EditView DetaiView Fields
function getEMSEditDetailViewFields($view, $module, $stepName) {
    global $mod_strings,$app_strings;
    require_once('modules/ModuleBuilder/parsers/ParserFactory.php');
    $view_array = ParserFactory::getParser($view, $module);
    $panelArray = $view_array->_viewdefs['panels'];//editview panels
    $bean = BeanFactory::newBean($module);
    $field = $bean->getFieldDefinitions();

    //editview fields
    $editViewFieldArray = array();
    foreach ($panelArray as $key => $value) {
        foreach ($value as $keys => $values) {
            $editViewFieldArray[] = $values;
        }//end of foreach
    }//end of foreach
    
    if($stepName == 'stepTwo'){
        $data = array();
    }else if($stepName == 'stepThree'){
        $data = array('' => '--None--');
    }//end of else if
    
    foreach($editViewFieldArray as $key => $value) {
        foreach($value as $k => $v) {
            if(array_key_exists($v, $field)) {
                require_once('include/utils.php');
                $fieldName = $v;
                if($field[$v]['type'] == 'enum' && isset($field[$v]['options']) && $module == 'AOW_WorkFlow'){
                    if($field[$v]['vname'] == 'LBL_RUN_ON'){
                        $fieldLabel = translate('LBL_FLOW_RUN_ON', 'AOW_WorkFlow');
                    }else{
                        $fieldLabel = translate($field[$v]['vname'], 'AOW_WorkFlow');
                    }//end of foreach
                }else{
                    $fieldLabel = translate($field[$v]['vname'], $module); 
                }//end of else

                $lastChar = substr($fieldLabel, -1);
                if($lastChar == ':'  || $lastChar == ' '){
                    $fieldLabel = substr_replace($fieldLabel, "", -1);
                    $lastCharColon = substr($fieldLabel, -1);
                    if($lastCharColon == ':'){
                        $fieldLabel = substr_replace($fieldLabel, "", -1);
                    }//end of if
                }//end of if

                $data[$fieldName] = $fieldLabel;
            }//end of if 
        }//end of foreach
    }//end of foreach

    //unset fields
    $unsetData = array('sample', 'insert_fields', 'update_text', 'case_update_form', 'aop_case_updates_threaded', 'internal', 'survey_questions_display', 'line_items', 'email2', 'suggestion_box', 'filename', 'product_image', 'configurationGUI', 'invite_templates', 'action_lines', 'condition_lines', 'survey_url_display', 'reminders', 'pdffooter', 'pdffooter');
    foreach ($unsetData as $key => $value) {
        unset($data[$value]);
    }//end of foreach

    if($stepName == 'stepThree'){
        if($module == 'jjwg_Maps' || $module == 'Meetings' || $module == 'Notes' || $module == 'Tasks' || $module == 'Calls'){
            unset($data['parent_name']);
        }//end of if

        if($module == 'AOS_PDF_Templates' || $module == 'AOK_KnowledgeBase' || $module == 'Cases'){
            unset($data['description']);
        }//end of if

        if($module == 'AOS_Invoices'){
            unset($data['number']);
        }//end of if

        if($module == 'Meetings' || $module == 'FP_events'){
            unset($data['duration']);
        }//end of if
    }//end of if
    return $data;
}//end of function

//Get Module Date Field Value Type
function getEMSModuleDateField($module, $aowField, $view, $value = null, $fieldOption = true, $fieldName){
    global $app_list_strings;
    // set $view = 'EditView' as default
    if (!$view) {
        $view = 'EditView';
    }//end of if

    $value = json_decode(html_entity_decode_utf8($value), true);

    if(!file_exists('modules/AOBH_BusinessHours/AOBH_BusinessHours.php')) unset($app_list_strings['aow_date_type_list']['business_hours']);

    $field = '';

    if($view == 'EditView'){
        $field .= "<select type='text' name='$aowField".'[0]'."' id='$aowField".'[0]'."' title='' tabindex='116'>". getEMSModuleDateFields($module, $view, $value[0], $fieldOption, $fieldName) ."</select>&nbsp;&nbsp;";
        $field .= "<select type='text' name='$aowField".'[1]'."' id='$aowField".'[1]'."' onchange='emailSoftwareIntegrationDateFieldChange(\"$aowField\")'  title='' tabindex='116'>". get_select_options_with_id($app_list_strings['aow_date_operator'], $value[1]) ."</select>&nbsp;";
        $display = 'none';
        if($value[1] == 'plus' || $value[1] == 'minus') $display = '';
        $field .= "<input  type='text' style='display:$display' name='$aowField".'[2]'."' id='$aowField".'[2]'."' title='' value='$value[2]' tabindex='116'>&nbsp;";
        $field .= "<select type='text' style='display:$display' name='$aowField".'[3]'."' id='$aowField".'[3]'."' title='' tabindex='116'>". get_select_options_with_id($app_list_strings['aow_date_type_list'], $value[3]) ."</select>";
    }//end of if
    return $field;
}//end of function

//Get Module Date Fields Data
function getEMSModuleDateFields($module, $view='EditView', $value = '', $fieldOption = true, $fieldName) {
    global $beanList, $app_list_strings;

    $fields = $app_list_strings['aow_date_options'];

    if(!$fieldOption) unset($fields['field']);
    $mod = new $beanList[$module]();
    $fieldData = $mod->field_defs[$fieldName];
    $fieldType = $fieldData['type'];
    if ($module != '') {
        if(isset($beanList[$module]) && $beanList[$module]){
            $mod = new $beanList[$module]();
            foreach($mod->field_defs as $name => $arr){
                if($fieldType == 'date' ){
                    if($arr['type'] == 'date'){
                        if(isset($arr['vname']) && $arr['vname'] != ''){
                            $fieldLabel = translate($arr['vname'], $mod->module_dir);
                            if(strpos($fieldLabel, ':')){
                                $fieldLabel = substr_replace($fieldLabel, "", -1);
                            }//end of if
                            $fields[$name] = $fieldLabel;
                        } else {
                            $fields[$name] = $name;
                        }//end of else
                    }//end of if
                }else if($fieldType == 'datetime' || $fieldType == 'datetimecombo'){
                    if($arr['type'] == 'datetime' || $arr['type'] == 'datetimecombo'){
                        if(isset($arr['vname']) && $arr['vname'] != ''){
                            $fieldLabel = translate($arr['vname'], $mod->module_dir);
                            if(strpos($fieldLabel, ':')){
                                $fieldLabel = substr_replace($fieldLabel, "", -1);
                            }//end of if
                            $fields[$name] = $fieldLabel;
                        } else {
                            $fields[$name] = $name;
                        }//end of else
                    }//end of if
                }//end of else
            }//end of foreach
        }//end of if
    }//end of if
    if($view == 'EditView'){
        return get_select_options_with_id($fields, $value);
    }//end of if
}//end of function

//Get Module Fields Html
function getEMSModuleFieldHtml($module, $fieldName, $aowField, $view='EditView', $value = '', $altType = '', $currencyId = '', $params= array()){
    
    global $current_language, $app_strings, $app_list_strings, $current_user, $beanFiles, $beanList;

    // use the mod_strings for this module
    $mod_strings = return_module_language($current_language,$module);

    // set the filename for this control
    $file = create_cache_directory('modules/AOW_WorkFlow/') . $module . $view . $altType . $fieldName . $aowField .'.tpl';

    $displayParams = array();

    if ( !is_file($file)
        || inDeveloperMode()
        || !empty($_SESSION['developerMode']) ) {

        if ( !isset($vardef) ) {
            require_once($beanFiles[$beanList[$module]]);
            $focus = new $beanList[$module];
            $vardef = $focus->getFieldDefinition($fieldName);
        }//end of if

        // Bug: check for AOR value SecurityGroups value missing
        if(stristr($fieldName, 'securitygroups') != false && empty($vardef)) {
            require_once($beanFiles[$beanList['SecurityGroups']]);
            $module = 'SecurityGroups';
            $focus = new $beanList[$module];
            $vardef = $focus->getFieldDefinition($fieldName);
        }//end of if

        //$displayParams['formName'] = 'EditView';

        // if this is the id relation field, then don't have a pop-up selector.
        if( $vardef['type'] == 'relate' && $vardef['id_name'] == $vardef['name']) {
            $vardef['type'] = 'varchar';
        }//end of if

        if(isset($vardef['precision'])) unset($vardef['precision']);

        //$vardef['precision'] = $locale->getPrecedentPreference('default_currency_significant_digits', $current_user);

        if( $vardef['type'] == 'datetime') {
            $vardef['type'] = 'datetimecombo';
        }//end of if
        if( $vardef['type'] == 'datetimecombo') {
            $displayParams['originalFieldName'] = $aowField;
            // Replace the square brackets by a deliberately complex alias to avoid JS conflicts
            $displayParams['idName'] = emailSoftwareIntegrationCreateBracket($aowField);
        }//end of if

        // trim down textbox display
        if( $vardef['type'] == 'text' ) {
            $vardef['rows'] = 2;
            $vardef['cols'] = 32;
        }//end of if

        // create the dropdowns for the parent type fields
        if ( $vardef['type'] == 'parent_type' ) {
            $vardef['type'] = 'enum';
        }//end of if

        if($vardef['type'] == 'link'){
            $vardef['type'] = 'relate';
            $vardef['rname'] = 'name';
            $vardef['id_name'] = $vardef['name'].'_id';
            if((!isset($vardef['module']) || $vardef['module'] == '') && $focus->load_relationship($vardef['name'])) {
                $relName = $vardef['name'];
                $vardef['module'] = $focus->$relName->getRelatedModuleName();
            }//end of if
        }//end of if

        //check for $altType
        if ( $altType != '' ) {
            $vardef['type'] = $altType;
        }//end of if

        // remove the special text entry field function 'getEmailAddressWidget'
        if ( isset($vardef['function'])
            && ( $vardef['function'] == 'getEmailAddressWidget'
                || $vardef['function']['name'] == 'getEmailAddressWidget' ) )
            unset($vardef['function']);

        if(isset($vardef['name']) && ($vardef['name'] == 'date_entered' || $vardef['name'] == 'date_modified')){
            $vardef['name'] = 'aow_temp_date';
        }//end of if

        // load SugarFieldHandler to render the field tpl file
        static $sfh;

        if(!isset($sfh)) {
            require_once('include/SugarFields/SugarFieldHandler.php');
            $sfh = new SugarFieldHandler();
        }//end of if

        $contents = $sfh->displaySmarty('fields', $vardef, $view, $displayParams);

        // Remove all the copyright comments
        $contents = preg_replace('/\{\*[^\}]*?\*\}/', '', $contents);

        if ($view == 'EditView' && ($vardef['type'] == 'relate' || $vardef['type'] == 'parent')) {
            $contents = str_replace('"' . $vardef['id_name'] . '"',
                '{/literal}"{$fields.' . $vardef['name'] . '.id_name}"{literal}', $contents);
            $contents = str_replace('"' . $vardef['name'] . '"',
                '{/literal}"{$fields.' . $vardef['name'] . '.name}"{literal}', $contents);
        }//end of if
        if ($view == 'DetailView' && $vardef['type'] == 'image') {
            $contents = str_replace('{$fields.id.value}', '{$record_id}', $contents);
        }//end of if
        // hack to disable one of the js calls in this control
        if (isset($vardef['function']) && ($vardef['function'] == 'getCurrencyDropDown' || $vardef['function']['name'] == 'getCurrencyDropDown')) {
            $contents .= "{literal}<script>function CurrencyConvertAll() { return; }</script>{/literal}";
        }//end of if

        // Save it to the cache file
        if ($fh = @sugar_fopen($file, 'w')) {
            fputs($fh, $contents);
            fclose($fh);
        }//end of if
    }//end of if

    // Now render the template we received
    $ss = new Sugar_Smarty();

    // Create Smarty variables for the Calendar picker widget
    global $timedate;
    $time_format = $timedate->get_user_time_format();
    $date_format = $timedate->get_cal_date_format();
    $ss->assign('USER_DATEFORMAT', $timedate->get_user_date_format());
    $ss->assign('TIME_FORMAT', $time_format);
    $time_separator = ":";
    $match = array();
    if(preg_match('/\d+([^\d])\d+([^\d]*)/s', $time_format, $match)) {
        $time_separator = $match[1];
    }//end of if
    $t23 = strpos($time_format, '23') !== false ? '%H' : '%I';
    if(!isset($match[2]) || $match[2] == '') {
        $ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . "%M");
    }//end of if
    else {
        $pm = $match[2] == "pm" ? "%P" : "%p";
        $ss->assign('CALENDAR_FORMAT', $date_format . ' ' . $t23 . $time_separator . "%M" . $pm);
    }//end of if

    $ss->assign('CALENDAR_FDOW', $current_user->get_first_day_of_week());

    // populate the fieldlist from the vardefs
    $fieldlist = array();
    if ( !isset($focus) || !($focus instanceof SugarBean) )
        require_once($beanFiles[$beanList[$module]]);
    $focus = new $beanList[$module];
    // create the dropdowns for the parent type fields
    $vardefFields = $focus->getFieldDefinitions();
    if (isset($vardefFields[$fieldName]['type']) && $vardefFields[$fieldName]['type'] == 'parent_type' ) {
        $focus->field_defs[$fieldName]['options'] = $focus->field_defs[$vardefFields[$fieldName]['group']]['options'];
    }//end of if
    foreach ( $vardefFields as $name => $properties ) {
        $fieldlist[$name] = $properties;
        // fill in enums
        if(isset($fieldlist[$name]['options']) && is_string($fieldlist[$name]['options']) && isset($app_list_strings[$fieldlist[$name]['options']]))
            $fieldlist[$name]['options'] = $app_list_strings[$fieldlist[$name]['options']];
        // Bug 32626: fall back on checking the mod_strings if not in the app_list_strings
        elseif(isset($fieldlist[$name]['options']) && is_string($fieldlist[$name]['options']) && isset($mod_strings[$fieldlist[$name]['options']]))
            $fieldlist[$name]['options'] = $mod_strings[$fieldlist[$name]['options']];
    }//end of foreach

    // fill in function return values
    if ( !in_array($fieldName,array('email1','email2')) )
    {
        if (!empty($fieldlist[$fieldName]['function']['returns']) && $fieldlist[$fieldName]['function']['returns'] == 'html')
        {
            $function = $fieldlist[$fieldName]['function']['name'];
            // include various functions required in the various vardefs
            if ( isset($fieldlist[$fieldName]['function']['include']) && is_file($fieldlist[$fieldName]['function']['include']))
                require_once($fieldlist[$fieldName]['function']['include']);
            $_REQUEST[$fieldName] = $value;
            $value = $function($focus, $fieldName, $value, $view);

            $value = str_ireplace($fieldName, $aowField, $value);
        }//end of if
    }//end of if

    if(isset($fieldlist[$fieldName]['type']) && $fieldlist[$fieldName]['type'] == 'link'){
        $fieldlist[$fieldName]['id_name'] = $fieldlist[$fieldName]['name'].'_id';

        if((!isset($fieldlist[$fieldName]['module']) || $fieldlist[$fieldName]['module'] == '') && $focus->load_relationship($fieldlist[$fieldName]['name'])) {
            $relName = $fieldlist[$fieldName]['name'];
            $fieldlist[$fieldName]['module'] = $focus->$relName->getRelatedModuleName();
        }//end of if
    }//end of if

    if(isset($fieldlist[$fieldName]['name']) && ($fieldlist[$fieldName]['name'] == 'date_entered' || $fieldlist[$fieldName]['name'] == 'date_modified')){
        $fieldlist[$fieldName]['name'] = 'aow_temp_date';
        $fieldlist['aow_temp_date'] = $fieldlist[$fieldName];
        $fieldName = 'aow_temp_date';
    }//end of if

    $quicksearch_js = '';
    if(isset( $fieldlist[$fieldName]['id_name'] ) && $fieldlist[$fieldName]['id_name'] != '' && $fieldlist[$fieldName]['id_name'] != $fieldlist[$fieldName]['name']){
        $rel_value = $value;

        require_once("include/TemplateHandler/TemplateHandler.php");
        $template_handler = new TemplateHandler();
        $quicksearch_js = $template_handler->createQuickSearchCode($fieldlist,$fieldlist,$view);
        $quicksearch_js = str_replace($fieldName, $aowField.'_display', $quicksearch_js);
        $quicksearch_js = str_replace($fieldlist[$fieldName]['id_name'], $aowField, $quicksearch_js);

        echo $quicksearch_js;

        if(isset($fieldlist[$fieldName]['module']) && $fieldlist[$fieldName]['module'] == 'Users'){
            $rel_value = get_assigned_user_name($value);
        } else if(isset($fieldlist[$fieldName]['module'])){
            require_once($beanFiles[$beanList[$fieldlist[$fieldName]['module']]]);
            $rel_focus = new $beanList[$fieldlist[$fieldName]['module']];
            $rel_focus->retrieve($value);
            if(isset($fieldlist[$fieldName]['rname']) && $fieldlist[$fieldName]['rname'] != ''){
                $relDisplayField = $fieldlist[$fieldName]['rname'];
            } else {
                $relDisplayField = 'name';
            }//end of else
            $rel_value = $rel_focus->$relDisplayField;
        }//end of else if

        $fieldlist[$fieldlist[$fieldName]['id_name']]['value'] = $value;
        $fieldlist[$fieldName]['value'] = $rel_value;
        $fieldlist[$fieldName]['id_name'] = $aowField;
        $fieldlist[$fieldlist[$fieldName]['id_name']]['name'] = $aowField;
        $fieldlist[$fieldName]['name'] = $aowField.'_display';
    } else if(isset( $fieldlist[$fieldName]['type'] ) && $view == 'DetailView' && ($fieldlist[$fieldName]['type'] == 'datetimecombo' || $fieldlist[$fieldName]['type'] == 'datetime' || $fieldlist[$fieldName]['type'] == 'date')){
        $value = $focus->convertField($value, $fieldlist[$fieldName]);
        if(!empty($params['date_format']) && isset($params['date_format'])){
            $convert_format = "Y-m-d H:i:s";
            if($fieldlist[$fieldName]['type'] == 'date') $convert_format = "Y-m-d";
            $fieldlist[$fieldName]['value'] = $timedate->to_display($value, $convert_format, $params['date_format']);
        }else{
            $fieldlist[$fieldName]['value'] = $timedate->to_display_date_time($value, true, true);
        }//end of else
        $fieldlist[$fieldName]['name'] = $aowField;
    } else if(isset( $fieldlist[$fieldName]['type'] ) && ($fieldlist[$fieldName]['type'] == 'datetimecombo' || $fieldlist[$fieldName]['type'] == 'datetime' || $fieldlist[$fieldName]['type'] == 'date')){
        $value = $focus->convertField($value, $fieldlist[$fieldName]);
        $displayValue = $timedate->to_display_date_time($value);
        $fieldlist[$fieldName]['value'] = $fieldlist[$aowField]['value'] = $displayValue;
        $fieldlist[$fieldName]['name'] = $aowField;
    } else {
        $fieldlist[$fieldName]['value'] = $value;
        $fieldlist[$fieldName]['name'] = $aowField;
    }//end of else

    if (isset($fieldlist[$fieldName]['type']) && $fieldlist[$fieldName]['type'] == 'datetimecombo' || $fieldlist[$fieldName]['type'] == 'datetime' ) {
        $fieldlist[$aowField]['aliasId'] = emailSoftwareIntegrationCreateBracket($aowField);
        $fieldlist[$aowField]['originalId'] = $aowField;
    }//end of if

    if(isset($fieldlist[$fieldName]['type']) && $fieldlist[$fieldName]['type'] == 'currency' && $view != 'EditView'){
        static $sfh;

        if(!isset($sfh)) {
            require_once('include/SugarFields/SugarFieldHandler.php');
            $sfh = new SugarFieldHandler();
        }//end of if

        if($currency_id != '' && !stripos($fieldName, '_USD')){
            $userCurrencyId = $current_user->getPreference('currency');
            if($currency_id != $userCurrencyId){
                $currency = new Currency();
                $currency->retrieve($currency_id);
                $value = $currency->convertToDollar($value);
                $currency->retrieve($userCurrencyId);
                $value = $currency->convertFromDollar($value);
            }//end of if
        }//end of if

        $parentfieldlist[strtoupper($fieldName)] = $value;

        return($sfh->displaySmarty($parentfieldlist, $fieldlist[$fieldName], 'ListView', $displayParams));
    }//end of if
    
    $ss->assign("QS_JS", $quicksearch_js);
    $ss->assign("fields", $fieldlist);
    $ss->assign("form_name", $view);
    $ss->assign("bean", $focus);

    // Add in any additional strings
    $ss->assign("MOD", $mod_strings);
    $ss->assign("APP", $app_strings);
    $ss->assign("module", $module);
    if (isset($params['record_id'])) {
        $ss->assign("record_id", $params['record_id']);
    }//end of if

    return $ss->fetch($file);
}//end of function

function emailSoftwareIntegrationCreateBracket($variable){
    $replaceRightBracket = str_replace(']', '', $variable);
    $replaceLeftBracket =  str_replace('[', '', $replaceRightBracket);
    return $replaceLeftBracket;
}//end of function

//Update Email Software Integration Data
function updateEMSData($tableName, $updateData, $where){
    $updateEMSData = "UPDATE $tableName SET";
    $i = 0;
    $dataCount = count($updateData);

    foreach ($updateData as $key => $value) {
        if (($i+1) != $dataCount) {
          $updateEMSData .= " $key=$value,";
        }else{
          $updateEMSData .= " $key=$value";
        }//end of else
        $i++;
    }//end of foreach
    if(!empty($where)){
        $updateEMSData .= " WHERE";
    }//end of if

    $j = 0;
    $count = count($where);
    foreach($where as $k => $val){
        if (($j+1) != $count) {
          $updateEMSData .=" $k=$val AND";
        }else{
          $updateEMSData .=" $k=$val";
        }//end of else
        $j++;
    }//end of foreach
    
    $updateEMSDataResult = $GLOBALS['db']->query($updateEMSData);
    return $updateEMSDataResult;
}//end of function

//Delete Email Software Integration Condition Data
function deleteEMSData($tableName, $where){
    $delEMSData = "DELETE FROM $tableName";
    foreach($where as $k => $val){
        $delEMSData .=" WHERE $k = $val";
    }//end of foreach

    $delEMSDataResult = $GLOBALS['db']->query($delEMSData);
    return $delEMSDataResult;
}//end of function

//Get all Email Software Integration Data
function getEMSData($tableName, $fieldName, $where, $orderBy){
    $getEMSData = '';
    $getEMSData .= "SELECT ";

    foreach ($fieldName as $key => $value) {
        if($key == 0){
          $getEMSData .= $value;
        }else{
          $getEMSData .= ",".$value;
        }//end of else
    }//end of foreach

    $getEMSData .= " FROM $tableName";
    $i = 0;
    if(!empty($where)){
        $getEMSData .= " WHERE";
        $dataCount = count($where);

        foreach ($where as $key => $fieldData) {
            $operator = $fieldData['operator'];
            $value = $fieldData['value'];

            if($dataCount > 1 && $i >= 1){
                $getEMSData .= " AND ".$key." ".$operator." ".$value;
            }else{
                $getEMSData .= " ".$key." ".$operator." ".$value;
            }//end of else
            $i++;
        }//end of foreach
    }//end of if

    if(!empty($orderBy)){
        $getEMSData .= " ORDER BY";  
        foreach($orderBy as $k => $v){
            $getEMSData .= " $k $v";
        }//end of foreach 
    }//end of if
    return $getEMSData;
}//end of function

//Update Email Software Integration Condition Data
function updateEMSConditionsData($fieldName, $delId, $conditionId, $emsConditionTableName, $operatorName, $valueType, $fieldValue, $module){
    //Condition field 
    $updateEMSConditionFieldData = updateEMSConditionField($fieldName, $delId, $conditionId, $emsConditionTableName);

    //Condition operator
    $updateEMSConditionOperatorData = updateEMSConditionOperator($operatorName, $delId, $conditionId, $emsConditionTableName);

    //Condition value type
    $updateEMSConditionValueTypeData = updateEMSConditionValueType($valueType, $delId, $conditionId, $emsConditionTableName);

    //Condition value
    $updateEMSConditionFieldValueData = updateEMSConditionFieldValue($fieldValue, $fieldName, $valueType, $module, $delId, $conditionId, $emsConditionTableName);
}//end of function

//Update Condition Field 
function updateEMSConditionField($fieldName, $delId, $conditionId, $emsConditionTableName){
    //Condition field 
    if(!empty($fieldName)){ 
        foreach($fieldName as $key => $value) {
            $updateFieldData = array('field' => "'".$value."'",
                                     'deleted' => $delId[$key]);
            $whereCondition = array('id' => "'".$conditionId[$key]."'");
            $updateEMSData = updateEMSData($emsConditionTableName, $updateFieldData, $whereCondition);
        }//end of foreach
    }//end of if
}//end of function

//Update Condition Operator
function updateEMSConditionOperator($operatorName, $delId, $conditionId, $emsConditionTableName){
    //Condition Operator
    if(!empty($operatorName)){ 
        foreach($operatorName as $key => $value) {
            $updateOperatorData = array('operator' => "'".$value."'",
                                       'deleted' => $delId[$key]);
            $whereCondition = array('id' => "'".$conditionId[$key]."'");
            $updateEMSData = updateEMSData($emsConditionTableName, $updateOperatorData, $whereCondition);
        }//end of foreach
    }//end of if
}//end of function

//Update Condition Value Type
function updateEMSConditionValueType($valueType, $delId, $conditionId, $emsConditionTableName){
    //Condition value type
    if(!empty($valueType)){ 
        foreach($valueType as $key => $value) {
            $updateValueTypeData = array('value_type' => "'".$value."'",
                                       'deleted' => $delId[$key]);
            $whereCondition = array('id' => "'".$conditionId[$key]."'");
            $updateEMSData = updateEMSData($emsConditionTableName, $updateValueTypeData, $whereCondition);
        }//end of foreach
    }//end of if
}//end of function

//Update Condition Field Value
function updateEMSConditionFieldValue($fieldValue, $fieldName, $valueType, $module, $delId, $conditionId, $emsConditionTableName){
    //Condition value
    if(!empty($fieldValue)){
        foreach($fieldValue as $key => $values) {
            $field = $fieldName[$key];
            $selValueType = $valueType[$key];
            if($selValueType == 'Value'){
                $values = getEMSFieldValues($module, $field, $values, $selValueType);
            }else if($selValueType == 'Date'){
                $values = base64_encode(serialize($values));
            }//end of else if
            
            $updateValueData = array('value' => "'".$values."'",
                                     'deleted' => $delId[$key]);
            $whereCondition = array('id' => "'".$conditionId[$key]."'");
            $updateEMSData = updateEMSData($emsConditionTableName, $updateValueData, $whereCondition);         
        }//end of foreach
    }//end of if
}//end of function

//Get Selected Module Fields Values in Condition Block
function getEMSFieldValues($module, $fieldName, $value, $selValueType){
    global $timedate;
    $bean = BeanFactory::newBean($module);
    $fieldDef = $bean->field_defs[$fieldName];
    if($fieldDef['type'] == 'datetime' || $fieldDef['type'] == 'datetimecombo'){
        $date = date('Y-m-d', strtotime($value));
        $time = date('h:i:s', strtotime($value));
        $dbtime = $timedate->to_db_time($value);
        $value = $date.' '.$dbtime;
    }else if($fieldDef['type'] == 'date'){
        $value = date('Y-m-d', strtotime($value));
    }else if($fieldDef['type'] == 'multienum'){
        $value = encodeMultienumValue($value);
    }else if($fieldDef['type'] == 'enum'){
        if($selValueType == 'Multi'){
            $value = encodeMultienumValue($value);
        }//end of if
    }else {
        $numericValue = str_replace(',', '', $value);
        if( is_numeric( $numericValue ) ) {
            $value = $numericValue;
        }//end of if
    }//end of else
    return $value;
}//end of function

//Condition Block Html
function getEMSConditionBlockHtml($moduleName, $recordId, $conditionType, $getFieldsNames){
    $randNumber = rand();
    $conditionLinesHtml = '';
    
    if($conditionType == 'All'){
        $tableId = 'aowAllConditionLines';
        $conditionButtonId = 'btnAllConditionLine';
        $conditionLinesHtml .= '<script src="custom/modules/Administration/js/VIEMSConditionLine.js?v='.$randNumber.'"></script>';
    }else{
        $tableId = 'aowAnyConditionLines';
        $conditionButtonId = 'btnAnyConditionLine';
    }//end of else

    $conditionLinesHtml .= "<table border='0' cellspacing='4' width='100%' id='".$tableId."'></table>";
    $conditionLinesHtml .= "<div style='padding-top: 10px; padding-bottom:10px;'>";
    $conditionLinesHtml .= "<input type=\"button\" tabindex=\"116\" class=\"button\" value=\"Add Condition\" id='".$conditionButtonId."' onclick=\"insertEMSConditionLine('".$conditionType."')\"/>";
    $conditionLinesHtml .= "</div>";

    if($moduleName != ''){
        $relatedModules[$moduleName] = translate($moduleName);
        $flowRelModules = get_select_options_with_id($relatedModules, $moduleName);

        require_once("modules/AOW_WorkFlow/aow_utils.php");
        $conditionLinesHtml .= "<script>";
        $conditionLinesHtml .= "flowRelModules = \"".trim(preg_replace('/\s+/', ' ', $flowRelModules))."\";";
        $conditionLinesHtml .= "flowModule = \"".$moduleName."\";";

        $whereCondition = array('module_mapping_id' => array('operator' => '=', 'value' => "'".$recordId."'"), 'condition_type' => array('operator' => '=', 'value' => "'".$conditionType."'"), 'module_path' => array('operator' => '=', 'value' => "'".$moduleName."'"), 'deleted' => array('operator' => '=', 'value' => 0));
        $orderBy = array('date_entered' => 'ASC');
        $getConditions = getEMSData('vi_ems_conditions', $getFieldsNames, $whereCondition, $orderBy);
        $getConditionsResult = $GLOBALS['db']->query($getConditions);

        $fields = '';
        $fields .= "flowFields = \"".trim(preg_replace('/\s+/', ' ', geteMSModuleFields($moduleName, 'stepThree')))."\";";
        while ($getConditionsRow = $GLOBALS['db']->fetchByAssoc($getConditionsResult)) {
            $conditionLinesHtml .= $fields;
            $fieldItem = json_encode($getConditionsRow);

            if($getConditionsRow['value_type'] == 'Date'){
                $conditionVal = json_encode(unserialize(base64_decode($getConditionsRow['value'])));    
            }else{
                $conditionVal = json_encode($getConditionsRow['value']);
            }//end of else

            $conditionLinesHtml .= "loadEMSConditionLine(".$fieldItem.", ".$conditionVal.", '".$getConditionsRow['condition_type']."');";
        }//end of while
        $conditionLinesHtml .= $fields;
        $conditionLinesHtml .= "</script>";
    }//end of if

    return $conditionLinesHtml;
}//end of function

//Get Email Software Integration Conditions Data
function getEMSConditionsData($moduleMappingData){
    $moduleMappingId = $moduleMappingData['module_mapping_id'];
    $selectFieldsName = array("*");
    $whereCondition = array('module_mapping_id' => array('operator' => '=', 'value' => "'".$moduleMappingId."'"), 'deleted' => array('operator' => '=', 'value' => 0));
    $orderBy = array('date_entered' => 'ASC');
    $getEMSConditions = getEMSData('vi_ems_conditions', $selectFieldsName, $whereCondition, $orderBy);
    $getEMSConditionsResult = $GLOBALS['db']->query($getEMSConditions);
    $rowCount = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($getEMSConditions));

    $conditionsData = array();
    if(!empty($rowCount)){
        while($getEMSConditionsResultRow = $GLOBALS['db']->fetchByAssoc($getEMSConditionsResult)){
            $modulePath = $getEMSConditionsResultRow['module_path'];
            $field = $getEMSConditionsResultRow['field']; //fields Name 
            $relatedBean = BeanFactory::newBean($modulePath);
            $fieldData = $relatedBean->field_defs[$field];

            if($fieldData['type'] == 'relate'){
                $field = $fieldData['id_name'];
            }//end of if

            $operator = $getEMSConditionsResultRow['operator']; //operator
            $valueType = $getEMSConditionsResultRow['value_type']; //value type
            $value = $getEMSConditionsResultRow['value']; //value
            $conditionType = $getEMSConditionsResultRow['condition_type']; //condition Type
            
            $conditionsData[$modulePath][] = array('modulePath' => $modulePath,
                                            'field' => $field,
                                            'operator' => $operator,
                                            'valueType' => $valueType,
                                            'value' => $value,
                                            'conditionType' => $conditionType
                                            );
        }//end of while
    }//end of if
    return $conditionsData;
}//end of function

//Get Condition Matched Records Data
function matchEMSConditionsData($emsConditionsData, $moduleMappingData, $recordId){
    $moduleMappingId = $moduleMappingData['module_mapping_id'];
    $suiteModule = $moduleMappingData['suitecrm_module'];
    $conditionalOperator = $moduleMappingData['conditional_operator'];

    $matchConditionRecords = array();
    $beanData = get_bean_select_array(false, get_singular_bean_name($suiteModule), "date_entered", "deleted=0");
    
    if($recordId != ''){
        $matchConditionRecords = getEMSMatchedConditionRecords($recordId, $emsConditionsData, $conditionalOperator);
    }else{
        if(isset($beanData) && !empty($beanData)){
            foreach ($beanData as $moduleRecordId => $value) {
                $matchedRecord = getEMSMatchedConditionRecords($moduleRecordId, $emsConditionsData, $conditionalOperator);
                if(!empty($matchedRecord)){
                    $matchConditionRecords[$moduleRecordId] =  $matchedRecord[$moduleRecordId];
                }//end of if
            }//end of foreach
        }//end of if
    }//end of else

    return $matchConditionRecords;
}//end of function

//Get Matched Condition Data
function getEMSMatchedConditionRecords($recordId, $emsConditionsData, $conditionalOperator){
    global $timedate, $app_list_strings;
    $matchConditionRecords = array();
    if(!empty($emsConditionsData)){
        foreach ($emsConditionsData as $moduleName => $conditionsData) {
            $matchCondition = array();
            $matchAllCondition = $matchAnyCondition = $matchAllAnyCondition = 0;
            foreach ($conditionsData as $index => $values) {
                $recordBean = BeanFactory::getBean($moduleName, $recordId);

                $conditionFieldName = $values['field'];
                $fieldDef = $recordBean->field_defs[$conditionFieldName];
                $fieldValue = $recordBean->$conditionFieldName;
                $fieldType = $fieldDef['type'];
                $conditionValue = $values['value'];
                $valueType = $values['valueType'];
                $conditionType = $values['conditionType'];

                if($valueType == 'Date'){
                    $params = unserialize(base64_decode($conditionValue));
                    $dateType = 'datetime';
                    if($params[0] == 'now'){
                        $conditionValue = date('Y-m-d H:i');
                        $fieldValue = strtotime(date('Y-m-d H:i', strtotime($fieldValue)));
                    }else if($params[0] == 'today'){
                        $dateType = 'date';
                        $conditionValue = date('Y-m-d');
                        $fieldValue = strtotime(date('Y-m-d', strtotime($fieldValue)));
                    }else {
                        $conditionValue = date('Y-m-d');
                        $fieldValue = strtotime(date('Y-m-d H:i',strtotime($fieldValue)));
                    }//end of else

                    if($params[1] != 'now'){
                        switch($params[3]) {
                            case 'business_hours';
                                if(file_exists('modules/AOBH_BusinessHours/AOBH_BusinessHours.php')){
                                    require_once('modules/AOBH_BusinessHours/AOBH_BusinessHours.php');

                                    $businessHours = new AOBH_BusinessHours();
                                    $amount = $params[2];
                                    if($params[1] != "plus"){
                                        $amount = 0-$amount;
                                    }//end of if
                                    $conditionValue = date('Y-m-d H:i:s', strtotime($conditionValue));
                                    $conditionValue = $businessHours->addBusinessHours($amount, $timedate->fromDb($conditionValue));
                                    $conditionValue = strtotime($timedate->asDbType( $conditionValue, $dateType ));
                                    break;
                                }//end of if
                                //No business hours module found - fall through.
                                $params[3] = 'hours';
                            default:
                                $conditionValue = strtotime($conditionValue.' '.$app_list_strings['aow_date_operator'][$params[1]]." $params[2] ".$params[3]);
                                if($dateType == 'date') $conditionValue = strtotime(date('Y-m-d', $conditionValue));
                                break;
                        }//end of switch
                    }else{
                        $conditionValue = strtotime($conditionValue);
                    }//end of else
                }else if($valueType == 'Value'){
                    if(($fieldType == 'datetimecombo' || $fieldType == 'datetime') && ($fieldValue != '')){
                        $conditionValue = $timedate->to_display_date_time($conditionValue);
                        $conditionValue = date('Y-m-d H:i', strtotime($conditionValue));//configuration value
                       
                        $fieldDate = date('Y-m-d', strtotime($recordBean->$conditionFieldName));
                        $hour = date('H', strtotime($recordBean->$conditionFieldName));
                        $minute = date('i', strtotime($recordBean->$conditionFieldName));
                            
                        $fieldValue = $fieldDate.' '.$hour.':'.$minute;// record value
                    }else if($fieldType == 'date' && $fieldValue != ''){
                        $conditionValue = date('Y-m-d', strtotime($conditionValue));
                        $fieldValue = date('Y-m-d', strtotime($fieldValue));
                    }else if($fieldType == 'multienum'){
                        $fieldValue = encodeMultienumValue($fieldValue);
                    }else if($fieldType == 'enum'){
                        if($valueType == 'Multi'){
                            $fieldValue = encodeMultienumValue($fieldValue);
                        }//end of if
                    }else if($fieldType == 'relate'){
                        $relateFieldName = $fieldDef['id_name']; //id name
                        $fieldValue = $recordBean->$relateFieldName;
                    }else if($fieldType == 'id'){
                        $idName = $fieldDef['name']; //id name
                        $fieldValue = $recordBean->$idName;
                    }else{
                        $numericValue = str_replace(',', '', $fieldValue);
                        if( is_numeric( $numericValue ) ) {
                            $fieldValue = $numericValue;
                        }//end of if
                    }//end of else
                }//end of else if

                $operator = $values['operator'];
                $dateOperators = array('today', 'is_in_last_7_days', 'is_in_last_30_days', 'is_in_last_60_days', 'is_in_last_90_days', 'is_in_last_120_days', 'is_in_this_week', 'is_in_the_last_week', 'is_in_this_month', 'is_in_the_last_month');
                if(in_array($operator, $dateOperators)){
                    $conditionValue = date('Y-m-d');
                    $fieldValue = date('Y-m-d', strtotime($fieldValue));
                }else if($operator == 'tomorrow'){
                    $conditionValue = date("Y-m-d", strtotime("tomorrow"));
                    $fieldValue = date('Y-m-d', strtotime($fieldValue));
                }else if($operator == 'yesterday'){
                    $conditionValue = date("Y-m-d", strtotime("yesterday"));
                    $fieldValue = date('Y-m-d', strtotime($fieldValue));
                }//end of else if

                switch ($operator) {
                    case 'Equal_To':
                        if($fieldValue == $conditionValue && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'Not_Equal_To':
                        if($fieldValue != $conditionValue && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'Contains':
                        if(strpos($fieldValue, $conditionValue) !== false && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'Starts_With':
                        if(emailSoftwareIntegrationStartsWiths($fieldValue, $conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'Ends_With':
                        if(emailSoftwareIntegrationEndsWiths($fieldValue, $conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'Greater_Than':
                        if($fieldValue > $conditionValue && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'Less_Than':
                        if($fieldValue < $conditionValue && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1'; 
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'Greater_Than_or_Equal_To':
                        if($fieldValue >= $conditionValue && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'Less_Than_or_Equal_To':
                        if($fieldValue <= $conditionValue && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_null':
                        if(empty($fieldValue) || $fieldValue == ''){
                            $matchCondition[$conditionType][] = '1';    
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_not_null':
                        if(!empty($fieldValue) || $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';    
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'does_not_contains':
                        if(strpos($fieldValue, $conditionValue) === false && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'today':
                        if(strtotime($fieldValue) == strtotime($conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'tomorrow':
                        if(strtotime($fieldValue) == strtotime($conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                                break;
                    case 'yesterday':
                        if(strtotime($fieldValue) == strtotime($conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_last_7_days':
                        $compareDate = date('Y-m-d', strtotime('-7 days'));
                        if(strtotime($fieldValue) >= strtotime($compareDate) && strtotime($fieldValue) < strtotime($conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_last_30_days':
                        $compareDate = date('Y-m-d', strtotime('-30 days'));
                        if(strtotime($fieldValue) >= strtotime($compareDate) && strtotime($fieldValue) < strtotime($conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_last_60_days':
                        $compareDate = date('Y-m-d', strtotime('-60 days'));
                        if(strtotime($fieldValue) >= strtotime($compareDate) && strtotime($fieldValue) < strtotime($conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_last_90_days':
                        $compareDate = date('Y-m-d', strtotime('-90 days'));
                        if(strtotime($fieldValue) >= strtotime($compareDate) && strtotime($fieldValue) < strtotime($conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_last_120_days':
                        $compareDate = date('Y-m-d', strtotime('-120 days'));
                        if(strtotime($fieldValue) >= strtotime($compareDate) && strtotime($fieldValue) < strtotime($conditionValue) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_this_week':
                        $monday = strtotime("last monday");
                        $monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
                        $sunday = strtotime(date("Y-m-d", $monday)." +6 days");
                        $thisWeekSd = date("Y-m-d", $monday);
                        $thisWeekEd = date("Y-m-d", $sunday);
                        if(strtotime($fieldValue) >= strtotime($thisWeekSd) && strtotime($fieldValue) <= strtotime($thisWeekEd) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_the_last_week':
                        $monday = strtotime("last monday");
                        $monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
                        $sunday = strtotime(date("Y-m-d", $monday)." -6 days");
                        $lastWeekEd = date("Y-m-d", $monday);
                        $lastWeekSd = date("Y-m-d", $sunday);
                        if(strtotime($fieldValue) >= strtotime($lastWeekSd) && strtotime($fieldValue) <= strtotime($lastWeekEd) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_this_month':
                        $thisMonthFirstdate = date ('Y-m-d', strtotime ('first day of this month'));
                        $thisMonthLastdate =date ('Y-m-d', strtotime ('last day of this month'));
                        if(strtotime($fieldValue) >= strtotime($thisMonthFirstdate) && strtotime($fieldValue) <= strtotime($thisMonthLastdate) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    case 'is_in_the_last_month':
                        $lastMonthFirstdate = date ('Y-m-d', strtotime ('first day of last month'));
                        $lastMonthLastdate =date ('Y-m-d', strtotime ('last day of last month'));
                        if(strtotime($fieldValue) >= strtotime($lastMonthFirstdate) && strtotime($fieldValue) <= strtotime($lastMonthLastdate) && $fieldValue != ''){
                            $matchCondition[$conditionType][] = '1';
                        }else{
                            $matchCondition[$conditionType][] = '0';
                        }//end of else
                        break;
                    default:
                        echo "";
                        break;
                }//end of switch
            }//end of foreach

            if(!empty($matchCondition)){
                if(isset($matchCondition['All'])){
                    if(in_array('0', $matchCondition['All'])){
                        $matchAllCondition = '0';
                    }else{
                        $matchAllCondition = '1';
                    }//end of else
                }else{
                    $matchAllCondition = '1';
                }//end of else

                if(isset($matchCondition['Any'])){
                    if(in_array('1', $matchCondition['Any'])){
                        $matchAnyCondition = '1';
                    }else{
                        $matchAnyCondition = '0';
                    }//end of else
                }else{  
                    $matchAnyCondition = '1';
                }//end of else
            }//end of if

            if($conditionalOperator == 'AND'){
                if($matchAllCondition == '1' &&  $matchAnyCondition == '1'){
                    $matchAllAnyCondition = '1';
                    $matchConditionRecords[$recordId] = $matchAllAnyCondition;
                }//end of if
            }else{
                if($matchAllCondition == '1' ||  $matchAnyCondition == '1'){
                    $matchAllAnyCondition = '1';
                    $matchConditionRecords[$recordId] = $matchAllAnyCondition;
                }//end of if
            }//end of else
        }//end of foreach
    }else{
        $matchConditionRecords[$recordId] = '1';
    }//end of if

    return $matchConditionRecords;
}//end of function

//startsWiths
function emailSoftwareIntegrationStartsWiths($str, $substr){
    $sl = strlen($str);
    $ssl = strlen($substr);
    if ($sl >= $ssl) {
        if(strpos($str, $substr, 0) === 0){
            return true;
        }//end of if
    }//end of if
}//end of function

//endsWiths
function emailSoftwareIntegrationEndsWiths($str, $subStr) {
    $sl = strlen($str);
    $ssl = strlen($subStr);
    if ($sl >= $ssl) {
        if(substr_compare($str, $subStr, $sl - $ssl, $ssl) == 0){
            return true;
        }//end of if
    }//end of if
}//end of function

//Get Contact/Lead Field for Active Campaigns
function getContactsLeadFieldsForActiveCampaigns($targetListSubpanelModule, $contactLeadData, $suitecrmContactsFields, $contactCustomFields){
    $finalSuiteContactsLeadArray = array();
    $contactLeadBean = BeanFactory::getBean($targetListSubpanelModule);
    $sea = new SugarEmailAddress;
    $primaryEmailAddress = $sea->getPrimaryAddress($contactLeadBean,$contactLeadData->id);

    foreach ($suitecrmContactsFields as $keyfield => $vfield) {
        if($vfield == "email1"){
            $finalSuiteContactsLeadArray[$keyfield] = $primaryEmailAddress;
        }else{
            $fieldDef = $contactLeadData->field_defs[$vfield];
            if($fieldDef['type'] == 'multienum'){
                $optionList = unencodeMultienum($contactLeadData->$vfield);
                if (empty($optionList)) {
                    $fieldVal = '';
                }//end of if
                $fieldVal = '||' . implode('||', $optionList) . '||';
            }else{
                $fieldVal = getFieldValue($vfield, $contactLeadBean, $contactLeadData, $targetListSubpanelModule, '');
            }//end of else

            if(in_array($keyfield, $contactCustomFields)){
                $finalSuiteContactsLeadArray['fieldValues'][] = array('field' => array_search($keyfield, $contactCustomFields), 'value' => $fieldVal);
            }else{
                $finalSuiteContactsLeadArray[$keyfield] = $fieldVal;
            }//end of else
        }//end of else
    }//end of foreach

    $finalSuiteContactsLeadArray = array('contact' => $finalSuiteContactsLeadArray);
    return $finalSuiteContactsLeadArray;
}//end of function

//Get EMS Tool Contacts Data
function getEMSToolContactsData($listId, $syncSoftware){
    $contactLeadId = array();
    $getFieldsNames = array("*");
    $whereCondition = array('vi_es_list_id' => array('operator' => '=', 'value' => "'".$listId."'"), 'vi_es_name' => array('operator' => '=', 'value' => "'".$syncSoftware."'"));
    $orderBy = array('vi_es_contact_id' => 'ASC');
    $fetchListContactsLead = getEMSData('vi_contacts_es', $getFieldsNames, $whereCondition, $orderBy);

    $fetchListContactsLeadResult = $GLOBALS['db']->query($fetchListContactsLead,false,'',false);
    $listAllContactLeadData = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($fetchListContactsLead));
    
    if(!empty($listAllContactLeadData)){
        while($fetchListContactsLeadRow = $GLOBALS['db']->fetchByAssoc($fetchListContactsLeadResult)){
            $contactLeadId[] = array('esContactId' => $fetchListContactsLeadRow['vi_es_contact_id'], 'suiteContactId' => $fetchListContactsLeadRow['vi_suitecrm_contact_id'], 'suiteLeadId' => $fetchListContactsLeadRow['vi_suitecrm_lead_id'], 'id' => $fetchListContactsLeadRow['id'], 'esListId' => $fetchListContactsLeadRow['vi_es_list_id'], 'deleted' => $fetchListContactsLeadRow['deleted']);
        }//end of while
    }//end of if
    return $contactLeadId;
}//end of function

//Get Related SendGrid Contact Id
function getRelatedSendGridContactsId($bean, $syncSoftware){
    $emailId = $bean->email1;
    $queryData['query'] = "email LIKE '".$emailId."%'";
    $searchData = syncESData("marketing/contacts/search", "POST", $syncSoftware, $queryData);
    $jsonDecodeData = json_decode($searchData);
    return $jsonDecodeData;
}//end of function

//Remove Contacts/Leads From List For All EMS Tool
function removeContactsLeadFromListForAllEMSTool($contactLeadId, $viEsContactId, $insertContactLeadId, $updateContactLeadId, $syncSoftware, $listId, $id, $targetListSubpanelModule, $planType){
    $suiteContactLeadId = '';
    $contactsLeadRemoveArray = array();
    foreach ($contactLeadId as $index => $listContactLeadData) {
        if($targetListSubpanelModule == 'Leads'){
            $suiteContactLeadId = $listContactLeadData['suiteLeadId'];
        }else{
            $suiteContactLeadId = $listContactLeadData['suiteContactId'];
        }//end of else
        
        if($syncSoftware == 'SendGrid' && $planType == 1){
            $contactLeadBean = BeanFactory::getBean($targetListSubpanelModule, $suiteContactLeadId);
            if($contactLeadBean->email1 != ''){
                $contactsData = getRelatedSendGridContactsId($contactLeadBean, $syncSoftware);;
                $removeContactsId = $contactsData->result[0]->id;
            }//end of if            
        }//end of if
        
        if($listContactLeadData['esContactId'] != $viEsContactId && ((!empty($insertContactLeadId) && (!in_array($listContactLeadData['esContactId'], $insertContactLeadId))) || (!empty($updateContactLeadId) && (!in_array($listContactLeadData['esContactId'], $updateContactLeadId)))) && $listId != $listContactLeadData['esListId']){
            
            if($syncSoftware == 'ActiveCampaigns'){
                $contactsLeadRemoveArray['contactList'] = array('list' => $listContactLeadData['esListId'], 'contact' => $listContactLeadData['esContactId'], 'status' => 2);
                removeContactsLeadFromList("POST", $syncSoftware, "/api/3/contactLists", $contactsLeadRemoveArray, $listContactLeadData, $listId);
            }else if($syncSoftware == 'SendInBlue'){
                removeSendInBlueContactLeadFromList($listContactLeadData, $syncSoftware, $listId);
            }else if($syncSoftware == 'SendGrid'){
                if($planType == 1){
                    removeContactsLeadFromList("DELETE", $syncSoftware, NMADDUPDATELISTS."/".$listId."/contacts?contact_ids=".$removeContactsId, $contactsLeadRemoveArray, $listContactLeadData, $listId);
                }else if($planType == 2){
                    removeContactsLeadFromList("DELETE", $syncSoftware, LMADDUPDATELISTS."/".$listId."/recipients/".$viEsContactId, $contactsLeadRemoveArray, $listContactLeadData, $listId);
                }//end of else if
            }else if($syncSoftware == 'Mautic'){
                removeContactsLeadFromList("POST", $syncSoftware, "/api/segments/".$listId."/contact/".$listContactLeadData['esContactId']."/remove", $contactsLeadRemoveArray, $listContactLeadData, $listId);
            }//end of else if

        }else if($listContactLeadData['esContactId'] != $viEsContactId && $suiteContactLeadId != $id && (empty($insertContactLeadId) && empty($updateContactLeadId)) && $listId != $listContactLeadData['esListId']){

            if($syncSoftware == 'ActiveCampaigns'){
                $contactsLeadRemoveArray['contactList'] = array('list' => $listContactLeadData['esListId'], 'contact' => $listContactLeadData['esContactId'], 'status' => 2);
                removeContactsLeadFromList("POST", $syncSoftware, "/api/3/contactLists", $contactsLeadRemoveArray, $listContactLeadData, $listId);
            }else if($syncSoftware == 'SendInBlue'){
                removeSendInBlueContactLeadFromList($listContactLeadData, $syncSoftware, $listId);
            }else if($syncSoftware == 'SendGrid'){
                if($planType == 1){
                    removeContactsLeadFromList("DELETE", $syncSoftware, NMADDUPDATELISTS."/".$listId."/contacts?contact_ids=".$removeContactsId, $contactsLeadRemoveArray, $listContactLeadData, $listId);
                }else if($planType == 2){
                    removeContactsLeadFromList("DELETE", $syncSoftware, LMADDUPDATELISTS."/".$listId."/recipients/".$viEsContactId, $contactsLeadRemoveArray, $listContactLeadData, $listId);
                }//end of else if
            }else if($syncSoftware == 'Mautic'){
                removeContactsLeadFromList("POST", $syncSoftware, "/api/segments/".$listId."/contact/".$listContactLeadData['esContactId']."/remove", $contactsLeadRemoveArray, $listContactLeadData, $listId);
            }//end of else if
        }//end of else if
    }//end of foreach
}//end of function

//remove Contacts from List in SendInBlue
function removeSendInBlueContactLeadFromList($listContactLeadData, $syncSoftware, $listId){
    $responseData = syncESData('contacts/'.$listContactLeadData['esContactId'], "GET", $syncSoftware, $data = array());
    $jsonDecodeData = json_decode($responseData);
    $contactsLeadRemoveArray['emails'] = array($jsonDecodeData->email);
    removeContactsLeadFromList("POST", $syncSoftware, "contacts/lists/".$listId."/contacts/remove", $contactsLeadRemoveArray, $listContactLeadData, $listId);
}//end of function

//Remove Contacts/Leads From List
function removeContactsLeadFromList($method, $syncSoftware, $endPointUrl, $contactsLeadRemoveArray, $listContactLeadData, $listId){
    $listContactLeadId = $listContactLeadData['id'];
    $updateFieldData = array('deleted' => 1);
    $whereCondition = array('id' => "'".$listContactLeadId."'", 'vi_es_list_id' =>  "'".$listId."'");
    $updateListDataResult = updateEMSData('vi_contacts_es', $updateFieldData, $whereCondition);

    syncESData($endPointUrl, $method, $syncSoftware, $contactsLeadRemoveArray);
}//end of function

//Add Contacts/Leads Against List
function addContactsLeadsFromList($contactLeadId, $targetListSubpanelModule, $id, $listId, $syncSoftware, $updateRecordData, $finalSuiteContactsLeadArray, $insertContactLeadId, $updateContactLeadId){
    $ContactListData = array();
    foreach ($contactLeadId as $index => $contactLeadData) {
        $suiteContactLeadId = '';
        if($targetListSubpanelModule == 'Leads'){
            $suiteContactLeadId = $contactLeadData['suiteLeadId'];
        }else{
            $suiteContactLeadId = $contactLeadData['suiteContactId'];
        }//end of else

        if($suiteContactLeadId == $id && $contactLeadData['deleted'] == 1 && $listId == $contactLeadData['esListId']){
            $listContactLeadId = $contactLeadData['id'];
            $updateContactLeadId[] = $contactLeadData['esContactId'];
            $updateFieldData = array('deleted' => 0);
            $whereCondition = array('id' => "'".$listContactLeadId."'", 'vi_es_list_id' =>  "'".$listId."'");
            $updateListDataResult = updateEMSData('vi_contacts_es', $updateFieldData, $whereCondition);

            if($syncSoftware == 'ActiveCampaigns'){
                syncESData("/api/3/contacts/".$contactLeadData['esContactId'], "PUT", $syncSoftware,$finalSuiteContactsLeadArray);
                $ContactListData['contactList'] = array('list' => $listId, 'contact' => $contactLeadData['esContactId'], 'status' => 1);
                syncESData("/api/3/contactLists","POST",$syncSoftware,$ContactListData);
            }else if($syncSoftware == 'SendInBlue' && !empty($updateRecordData)){
                syncESData("contacts/".$contactLeadData['esContactId'], "PUT", $syncSoftware, $finalSuiteContactsLeadArray);
                syncESData("contacts/lists/".$listId."/contacts/add","POST",$syncSoftware,$updateRecordData);
            }else if($syncSoftware == 'Mautic'){
                syncESData("/api/contacts/new","POST",$syncSoftware,$finalSuiteContactsLeadArray);
                syncESData("/api/segments/".$listId."/contact/".$contactLeadData['esContactId']."/add","POST",$syncSoftware,$updateRecordData);
            }//end of else if
        }//end of if
    }//end of foreach
    return $updateContactLeadId;
}//end of function

function emsToSuiteSyncLog($moduleName, $values, $suitecrmFields, $recordID, $esModule, $syncSoftware, $toRecord, $targetListSubpanelModule){
    $eslBean = BeanFactory::newBean('VI_EmailSoftwareIntegartionSyncLog');
    $planType = getPlanType($syncSoftware);
    $esId = $values['id'];

    if($recordID == ""){
        $actionType = "Insert";
        if($syncSoftware == "SendInBlue" && ($moduleName == 'Leads' || $moduleName == 'Contacts')){
            $contactLeadTableName = strtolower($moduleName) ;
            $selContactData = "SELECT * FROM $contactLeadTableName WHERE deleted = 0";
            $selContactDataResult = $GLOBALS['db']->query($selContactData);
            $suiteCRMAllContacts = array();
            while($selContactDataRow = $GLOBALS['db']->fetchByAssoc($selContactDataResult)){
                $contactLeadBean = BeanFactory::getBean($moduleName, $selContactDataRow['id']);
                $suiteCRMAllContacts[$selContactDataRow['id']] = $contactLeadBean->email1;
            }//end of while

            $checkSql = "SELECT * FROM vi_contacts_es";
            if(in_array($values['email1'], $suiteCRMAllContacts)){
                $suiteCRMContactId = array_search($values['email1'], $suiteCRMAllContacts);
                $getContactData = "SELECT * FROM $contactLeadTableName WHERE id='$suiteCRMContactId' AND deleted = 0";
                $getContactDataResult = $GLOBALS['db']->fetchOne($getContactData);

                if(!empty($getContactDataResult)){
                    $moduleBean = BeanFactory::getBean($moduleName, $suiteCRMContactId);
                    $actionType = "Update";
                }else{
                    $moduleBean = BeanFactory::getBean($moduleName);
                }//end of else
            }else{
                $moduleBean = BeanFactory::newBean($moduleName);                             
            }//end of else
        }else{
            $moduleBean = BeanFactory::newBean($moduleName);
        }//end of else
    }else{
        $actionType = "Update";
        $moduleBean = BeanFactory::getBean($moduleName, $recordID);
    }//end of else

    $recipientsArray = array();
    if($planType == 1){
        $contactName = 'contact_sample';
    }else{
        $contactName = 'recipients';
    }//end of else
    
    $relationShipData = array();
    foreach ($values as $k => $val) {
        if($k != $contactName){
            if(in_array($k, $suitecrmFields)){
                if(isset($moduleBean->field_defs[$k])){
                    $fieldDef = $moduleBean->field_defs[$k];
                    if($fieldDef['type'] == 'relate'){
                        $relationShipData = getEMSToSuiteRelateFieldData($val, $moduleBean, $k, $moduleBean->id, $relationShipData, $fieldDef, $moduleName);
                    }else{
                        $moduleBean->$k = $val;    
                    }//end of else
                }//end of if
            }else{
                if($k == "id"){
                    $eslBean->from_record = $recordID;   
                    $eslBean->viem_to_record = $val;
                }else{
                    $moduleBean->$k = $val;
                }//end of else
            }//end of else
        }elseif ($k == $contactName) {
            $recipientsArray = $val;
        }//end of else if
    }//end of foreach
    
    $moduleBean->save();

    if(!empty($relationShipData)){
        addEMSToSuiteRelateFieldData($relationShipData, $moduleBean->id);
    }//end of if

    if($moduleBean->id == ""){
        return "failure";
    }//end of if

    if($moduleBean->id && !empty($recipientsArray)){
        insertEMSToSuiteRelatedRecords($moduleName,$targetListSubpanelModule,$recipientsArray,$moduleBean->id,$recordID,$syncSoftware,$esId);
    }//end of if
        
    if($esModule == "Accounts" && $syncSoftware == "Mautic"){
        $esModule = "Companies";
    }else if($esModule == "Accounts" && $syncSoftware == "ActiveCampaigns"){
        $esModule = "Organizations";
    }else if($esModule == "Leads"){
        $esModule = "Contacts";
    }else if($esModule == 'Contacts_List'){
        $esModule = 'Contact List';
    }//end of else if
    $eslBean->name = $esModule;

    //Suitecrm Module Name Changes
    global $app_list_strings;
    $moduleName = $app_list_strings['moduleList'][$moduleName];
    $eslBean->to_module = $moduleName;
    $eslBean->email_software = $syncSoftware;
    if($syncSoftware == "SendGrid"){
        $eslBean->sync_type = "SG2SC";
    }elseif($syncSoftware == "Mautic"){
        $eslBean->sync_type = "MA2SC";
    }elseif($syncSoftware == "ConstantContact"){
        $eslBean->sync_type = "CC2SC";
    }elseif($syncSoftware == "ActiveCampaigns"){
        $eslBean->sync_type = "AC2SC";
    }elseif($syncSoftware == "SendInBlue"){
        $eslBean->sync_type = "SB2SC";
    }//end of else if

    $eslBean->action_type = $actionType; 
    $eslBean->status = "Successfull";
    if($recordID == ""){
        $eslBean->from_record = $moduleBean->id;
        $eslBean->viem_to_record = $esId;
    }//end of if

    $eslBean->save();
    if($recordID == ""){
        $randomid = create_guid();
        if($moduleName == "Products"){
            $tableName = "vi_assets_es";
            $data = array('id' => $randomid, 'vi_suitecrm_assets_id' => $moduleBean->id, 'vi_es_assets_id' => $esId, 'vi_es_name' => $syncSoftware, 'deleted' => 0);
        }elseif($moduleName == "Accounts"){
            $tableName = "vi_accounts_es";
            $data = array('id' => $randomid, 'vi_suitecrm_account_id' => $moduleBean->id, 'vi_es_account_id' => $esId, 'vi_es_name' => $syncSoftware, 'deleted' => 0);
        }elseif($moduleName == "Target List" || $moduleName == "Targets - Lists" || $moduleName == "Target Lists"){
            $tableName = "vi_segments_es";
            $data = array('id' => $randomid, 'vi_suitecrm_segments_id' => $moduleBean->id, 'vi_es_segments_id' => $esId, 'vi_es_name' => $syncSoftware, 'deleted' => 0);  
        }elseif ($moduleName == "Contacts") {
            $tableName = "vi_contacts_es";
            $data = array('id' => $randomid, 'vi_suitecrm_contact_id' => $moduleBean->id, 'vi_es_contact_id' => $esId, 'vi_suitecrm_lead_id' => '', 'vi_es_name' => $syncSoftware, 'vi_es_list_id' => '', 'vi_suitecrm_module' => $moduleName, 'vi_es_lead_id' => '', 'deleted' => 0);
        }elseif ($moduleName == "Leads") {
            $tableName = "vi_contacts_es";
            $data = array('id' => $randomid, 'vi_suitecrm_contact_id' => '', 'vi_es_contact_id' => $esId, 'vi_suitecrm_lead_id' => $moduleBean->id, 'vi_es_name' => $syncSoftware, 'vi_es_list_id' => '', 'vi_suitecrm_module' => $moduleName, 'vi_es_lead_id' => $esId, 'deleted' => 0);
        }elseif ($moduleName == "Campaigns"){
            $tableName = "vi_campaigns_es";
            $data = array('id' => $randomid, 'vi_suitecrm_campaigns_id' => $moduleBean->id, 'vi_es_campaign_id' => $esId, 'vi_es_name' => $syncSoftware, 'deleted' => 0);
        }//end of else if
        insertESRecord($tableName,$data);
    }//end of if
    return $moduleBean->id;
}//end of function

//Get Related type field data
function getEMSToSuiteRelateFieldData($fieldValue, $moduleBean, $contactLeadFieldName, $contactLeadId, $relationShipData, $fieldDef, $moduleName){
    global $current_user;
    $currentLoggedInUserID = $current_user->id;

    $relateTableName = $fieldDef['table'];
    if($relateTableName == 'accounts' || $relateTableName == 'campaigns'){
        $fieldName = 'name';
    }else{
        $fieldName = 'last_name';
    }//end of else

    $nameArray = get_bean_select_array(false, get_singular_bean_name($fieldDef['module']), $fieldName, "deleted=0");
    
    if(in_array(trim($fieldValue), $nameArray)){
        $relatedModuleRecordId = array_search(trim($fieldValue), $nameArray);
    }else{
        $createRelatedRecordId = create_guid();
        $data = array('id' => $createRelatedRecordId, $fieldName => $fieldValue, 'assigned_user_id' => $currentLoggedInUserID, 'date_entered' => date('Y-m-d H:i:s'), 'date_modified' => date('Y-m-d H:i:s'), 'deleted' => 0);
        insertESRecord($relateTableName, $data);
        $relatedModuleRecordId = $createRelatedRecordId;
    }//end of else if

    if($moduleBean->load_relationship($relateTableName)){
        $relationship = $moduleBean->$relateTableName;

        if($relationship->relationship->type == "one-to-many"){
            if(isset($fieldDef['id_name'])){
                $idName = $fieldDef['id_name'];
                $moduleBean->$idName = $relatedModuleRecordId;
            }//end of if
        }else if($relationship->relationship->type == "many-to-many"){
            $moduleBean->$contactLeadFieldName = $relatedModuleRecordId;
            $relatedTableName = $relationship->relationship->def['table'];
            $relationName = $relationship->relationship->def['name'];

            if(isset($relationship->relationship->def['relationships'])){
                $relateData = $relationship->relationship->def['relationships'][$relationName];
                $lhsModule = $relationship->relationship->def['relationships'][$relationName]['lhs_module'];
                $rhsModule = $relationship->relationship->def['relationships'][$relationName]['rhs_module'];

                if($rhsModule == $moduleName){
                    $lhsModuleFieldName = $relationship->relationship->def['relationships'][$relationName]['join_key_lhs'];
                }//end of if
                if($lhsModule == $fieldDef['module']){
                    $rhsModuleFieldName = $relationship->relationship->def['relationships'][$relationName]['join_key_rhs'];
                }//end of if
                                        
                if($lhsModuleFieldName != '' && $rhsModuleFieldName != ''){
                    $getRelationModuleData = "SELECT * FROM $relatedTableName WHERE deleted = 0";
                    $getRelationModuleDataResult = $GLOBALS['db']->query($getRelationModuleData);

                    $relatedModuleRecordIdArray = $targetModuleRecordIdArray = array();
                    while($getRelationModuleDataRow = $GLOBALS['db']->fetchByAssoc($getRelationModuleDataResult)){
                        if(isset($getRelationModuleDataRow[$lhsModuleFieldName]) && isset($getRelationModuleDataRow[$rhsModuleFieldName])){
                            $relatedModuleRecordIdArray[$lhsModuleFieldName][] = $getRelationModuleDataRow[$lhsModuleFieldName];
                            $targetModuleRecordIdArray[$rhsModuleFieldName][] = $getRelationModuleDataRow[$rhsModuleFieldName];
                        }//end of if
                    }//end of while

                    if(!in_array($relatedModuleRecordId, $relatedModuleRecordIdArray[$lhsModuleFieldName]) || !in_array($contactLeadId, $targetModuleRecordIdArray[$rhsModuleFieldName])){
                        $relationId = create_guid();
                        $relationShipData[$relatedTableName] = array('id' => $relationId, 'targetModuleField' => $rhsModuleFieldName, 'relateModuleField' => $lhsModuleFieldName, 'relatedModuleRecordId' => $relatedModuleRecordId, 'contactLeadId' => $contactLeadId);
                    }//end of if
                }//end of if
            }//end of if
        }//end of else if
    }else{
        $moduleBean->$contactLeadFieldName = $fieldValue;    
    }//end of else
    return $relationShipData;
}//end of function

//Add Related Field Data in Relation Table
function addEMSToSuiteRelateFieldData($relationShipData, $moduleRecordId){
    foreach ($relationShipData as $tableName => $relationData) {
        $relatedModuleField = $relationShipData[$tableName]['relateModuleField'];
        $relatedModuleRecordId = $relationShipData[$tableName]['relatedModuleRecordId'];
        $targetModuleField = $relationShipData[$tableName]['targetModuleField'];

        if($relationData['contactLeadId'] == ''){
            $relationShipData[$tableName]['targetModuleRecordId'] = $moduleRecordId;
            if(isset($relationShipData[$tableName]['targetModuleRecordId']) && $relationShipData[$tableName]['targetModuleRecordId'] != ''){
                unset($relationShipData[$tableName]['contactLeadId']);
                $targetModuleRecordId = $relationShipData[$tableName]['targetModuleRecordId'];
                $relatData = array('id' => $relationShipData[$tableName]['id'], $relatedModuleField => $relatedModuleRecordId, $targetModuleField => $relationShipData[$tableName]['targetModuleRecordId'], 'date_modified' => date('Y-m-d H:i:s'), 'deleted' => 0);

                $getRelatedContactData = "SELECT * FROM $tableName WHERE $relatedModuleField='$relatedModuleRecordId' AND $targetModuleField='$targetModuleRecordId' AND deleted = 0";
                $getRelatedContactDataResult = $GLOBALS['db']->fetchOne($getRelatedContactData);
                if(empty($getRelatedContactDataResult)){
                    insertESRecord($tableName, $relatData);
                }//end of if                    
            }//end of if
        }else if($relationData['contactLeadId'] != ''){
            $relationShipData[$tableName]['targetModuleRecordId'] = $relationData['contactLeadId'];
            if(isset($relationShipData[$tableName]['targetModuleRecordId']) && $relationShipData[$tableName]['targetModuleRecordId'] != ''){
                unset($relationShipData[$tableName]['contactLeadId']);
                $targetModuleRecordId = $relationShipData[$tableName]['targetModuleRecordId'];

                $updateData = "UPDATE $tableName SET $relatedModuleField = '$relatedModuleRecordId' WHERE $targetModuleField = '$targetModuleRecordId'";
                $updateResult = $GLOBALS['db']->query($updateData);
            }//end of if
        }//end of else if
    }//end of foreach
}//end of function

function insertEMSToSuiteRelatedRecords($mainModule, $moduleName, $val, $relatedRecordID, $recordID, $syncSoftware, $esId){
    global $current_user;
    $currentLoggedInUserID = $current_user->id;

    $contactLeadTableName = strtolower($moduleName);
    $selContactData = "SELECT * FROM $contactLeadTableName WHERE deleted = 0";
    $selContactDataResult = $GLOBALS['db']->query($selContactData);
    $suiteCRMAllContacts = array();
    while($selContactDataRow = $GLOBALS['db']->fetchByAssoc($selContactDataResult)){
        $contactLeadBean = BeanFactory::getBean($moduleName,$selContactDataRow['id']);
        $suiteCRMAllContacts[$selContactDataRow['id']] = $contactLeadBean->email1;
    }//end of while
        
    foreach ($val as $kv => $vv) {
        $contactLeadId = getEMSToolContactsData($esId, $syncSoftware);
        $contactid = $vv['id'];
        $relationShipData = array();
        $selectData = '';

        if($syncSoftware == 'SendGrid'){
            $selectData .= "SELECT * FROM vi_contacts_es ";
            $planType = getPlanType($syncSoftware);
            if($planType == 1){
                if(in_array($vv['email1'],$suiteCRMAllContacts)){
                    $suiteCRMContactId = array_search($vv['email1'], $suiteCRMAllContacts);
                    if($moduleName == "Contacts"){
                        $selectData .= "WHERE vi_suitecrm_contact_id = '$suiteCRMContactId' and deleted = 0";
                    }else if($moduleName == "Leads"){
                        $selectData .= "WHERE vi_suitecrm_lead_id = '$suiteCRMContactId' AND deleted = 0";
                    }//end of else if                               
                }else{
                    if($moduleName == "Contacts"){
                        $selectData .= "WHERE vi_es_contact_id = '$contactid' AND deleted = 0";
                    }else if($moduleName == "Leads"){
                        $selectData .= "WHERE vi_es_lead_id = '$contactid' AND deleted = 0";
                    }//end of else if                              
                }//end of else
            }else{
                if($moduleName == "Contacts"){
                    $selectData .= "WHERE vi_es_contact_id = '$contactid' AND deleted = 0";
                }else if($moduleName == "Leads"){
                    $selectData .= "WHERE vi_es_lead_id = '$contactid' AND deleted = 0";
                }//end of else if                             
            }//end of else
        }else{
            if($contactLeadTableName == 'contacts'){
                $contactLeadRecordId = "vi_contacts_es.vi_suitecrm_contact_id = contacts.id";
            }else if($contactLeadTableName == "leads"){
                $contactLeadRecordId = "vi_contacts_es.vi_suitecrm_lead_id = leads.id";
            }//end of else
            $selectData = "SELECT * FROM vi_contacts_es
                            JOIN $contactLeadTableName
                            ON $contactLeadRecordId
                            WHERE vi_contacts_es.vi_es_contact_id = '$contactid' and $contactLeadTableName.deleted = 0 and vi_contacts_es.vi_es_name = '$syncSoftware' AND vi_contacts_es.deleted = 0";
        }//end of else
        
        $selectResult = $GLOBALS['db']->fetchOne($selectData);
        $id = $cid = '';
        if(!empty($selectResult)){
            $id = $selectResult['id'];
            $contactLeadDeleted = $selectResult['deleted'];
            if($moduleName == 'Contacts'){
                $cid = $selectResult['vi_suitecrm_contact_id'];
            }else if($moduleName == "Leads"){
                $cid = $selectResult['vi_suitecrm_lead_id'];
            }//end of else
        }else{
            if($contactLeadTableName == 'contacts'){
                $contactLeadRecordId = "vi_contacts_es.vi_suitecrm_contact_id = contacts.id";
            }else if($contactLeadTableName == "leads"){
                $contactLeadRecordId = "vi_contacts_es.vi_suitecrm_lead_id = leads.id";
            }//end of else

            $checkListContacts = "SELECT * FROM vi_contacts_es JOIN $contactLeadTableName ON $contactLeadRecordId WHERE vi_contacts_es.vi_es_name = '$syncSoftware' AND vi_contacts_es.vi_es_contact_id = '$contactid' AND vi_contacts_es.vi_es_list_id = '$esId' AND vi_contacts_es.deleted = 0 AND $contactLeadTableName.deleted = 1";
            $checkListContactsResult = $GLOBALS['db']->fetchOne($checkListContacts);
            if(!empty($checkListContactsResult)){
                if($moduleName == 'Contacts'){
                    $contactLeadId = $checkListContactsResult['vi_suitecrm_contact_id'];
                    $contactLeadIdQuery = "vi_suitecrm_contact_id = '$contactLeadId' ";
                }else if($moduleName == "Leads"){
                    $contactLeadId = $checkListContactsResult['vi_suitecrm_lead_id'];
                    $contactLeadIdQuery = "vi_suitecrm_lead_id = '$contactLeadId'";
                }//end of else
            
                $esListId = $checkListContactsResult['vi_es_list_id'];
                $esName = $checkListContactsResult['vi_es_name'];
                $updateVIESContact = "SELECT * FROM vi_contacts_es WHERE vi_es_list_id = '$esListId' AND vi_es_contact_id = '$contactid' AND vi_es_name = '$esName' AND $contactLeadIdQuery AND deleted = 0";
                
                $updateVIESContactResult = $GLOBALS['db']->fetchOne($updateVIESContact);
                if(!empty($updateVIESContactResult)){
                    $deletedContactId = $updateVIESContactResult['id'];
                    $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$deletedContactId'";
                    $updateResult = $GLOBALS['db']->query($updateData);
                    $moduleBean = BeanFactory::newBean($moduleName);
                }//end of if
            }//end of if
        }//end of else

        if($recordID == "" && $cid == ""){
            if(in_array($vv['email1'],$suiteCRMAllContacts)){
                $suiteCRMContactId = array_search($vv['email1'], $suiteCRMAllContacts);
                $getContactData = "SELECT * FROM $contactLeadTableName WHERE id='$suiteCRMContactId' AND deleted = 0";
                $getContactDataResult = $GLOBALS['db']->fetchOne($getContactData);

                if(!empty($getContactDataResult)){
                    $moduleBean = BeanFactory::getBean($moduleName,$suiteCRMContactId);
                }else{
                    $moduleBean = BeanFactory::newBean($moduleName);
                }//end of else
            }else{
                $moduleBean = BeanFactory::newBean($moduleName);    
            }//end of else
        }else if($cid == ''){
            $getFieldsNames = array("*");
            $whereCondition = array('vi_es_contact_id' => array('operator' => '=', 'value' => "'".$contactid."'"), 'vi_es_list_id' => array('operator' => '=', 'value' => "'".$esId."'"), 'deleted' => array('operator' => '=', 'value' => 1));
            $checkContactLead = getEMSData('vi_contacts_es', $getFieldsNames, $whereCondition, $orderBy=array());
            $checkContactLeadResult = $GLOBALS['db']->fetchOne($checkContactLead);

            if(!empty($checkContactLeadResult)){
                if($moduleName == 'Contacts'){
                    $suiteContactLeadId = $checkContactLeadResult['vi_suitecrm_contact_id'];
                }else if($moduleName == "Leads"){
                    $suiteContactLeadId = $checkContactLeadResult['vi_suitecrm_lead_id'];
                }//end of else
                $moduleBean = BeanFactory::getBean($moduleName,$suiteContactLeadId);
            }else{
                if(in_array($vv['email1'],$suiteCRMAllContacts)){
                    $suiteCRMContactId = array_search($vv['email1'], $suiteCRMAllContacts);
                    $getContactData = "SELECT * FROM $contactLeadTableName WHERE id='$suiteCRMContactId' AND deleted = 0";
                    $getContactDataResult = $GLOBALS['db']->fetchOne($getContactData);

                    if(!empty($getContactDataResult)){
                        $moduleBean = BeanFactory::getBean($moduleName,$suiteCRMContactId);
                    }else{
                        $moduleBean = BeanFactory::getBean($moduleName,$cid);
                    }//end of else
                }else{
                    $moduleBean = BeanFactory::getBean($moduleName,$cid);
                }//end of else
            }//end of else
        }else{
            $moduleBean = BeanFactory::getBean($moduleName,$cid);
            if(empty($moduleBean)){
                $updateData = "UPDATE vi_contacts_es SET deleted = 1 WHERE id = '$id'";
                $updateResult = $GLOBALS['db']->query($updateData);
                $moduleBean = BeanFactory::newBean($moduleName);
            }//end of if
        }//end of else
        
        $relationShipData = array();
        foreach ($vv as $keyv => $valuev) {
            $moduleBean->assigned_user_id = $currentLoggedInUserID;
            if($keyv == "id"){
                if($cid != ""){
                    $moduleBean->id = $cid;
                }//end of if                   
            }else if($keyv == "email1"){
                $moduleBean->email1 = $valuev;
            }else{
                if(isset($moduleBean->field_defs[$keyv])){
                    $fieldDef = $moduleBean->field_defs[$keyv];
                    if($fieldDef['type'] == 'relate'){
                        $relationShipData = getEMSToSuiteRelateFieldData($valuev, $moduleBean, $keyv, $cid, $relationShipData, $fieldDef, $moduleName);                            
                    }else{
                        $moduleBean->$keyv = $valuev;    
                    }//end of else
                }//end of if
            }//end of else            
        }//end of foreach
        
        $moduleBean->save();
        $targetListBean = BeanFactory::getBean($mainModule,$relatedRecordID);
        $relationshipName = strtolower($moduleName);
        $targetListBean->load_relationship($relationshipName);
        
        if($cid != ''){
            $targetListBean->$relationshipName->add($moduleBean);

            $suiteContactLeadId = '';
            foreach ($contactLeadId as $index => $listContactLeadData) {
                if($moduleName == 'Leads'){
                    $suiteContactLeadId = $listContactLeadData['suiteLeadId'];
                }else if($moduleName == "Contacts"){
                    $suiteContactLeadId = $listContactLeadData['suiteContactId'];
                }//end of else
                                    
                $listContactLeadId = $listContactLeadData['id'];
                if($suiteContactLeadId != $cid && ((!empty($insertContactLeadId) && (!in_array($suiteContactLeadId, $insertContactLeadId))) || (!empty($updateContactLeadId) && (!in_array($suiteContactLeadId, $updateContactLeadId))))){
                    $updateFieldData = array('deleted' => 1);
                    $whereCondition = array('id' => "'".$listContactLeadId."'", 'vi_es_list_id' =>  "'".$esId."'");
                    $updateListDataResult = updateEMSData('vi_contacts_es', $updateFieldData, $whereCondition);

                    $targetListBean->$relationshipName->delete($targetListBean->id, $suiteContactLeadId);
                }else if($listContactLeadData['esContactId'] != $contactid && $suiteContactLeadId != $cid && (empty($insertContactLeadId) && empty($updateContactLeadId))){
                    $updateFieldData = array('deleted' => 1);
                    $whereCondition = array('id' => "'".$listContactLeadId."'", 'vi_es_list_id' =>  "'".$esId."'");
                    $updateListDataResult = updateEMSData('vi_contacts_es', $updateFieldData, $whereCondition);

                   $targetListBean->$relationshipName->delete($targetListBean->id, $suiteContactLeadId);
                }//end of else if
            }//end of foreach
        }else if($cid == ''){
            foreach ($contactLeadId as $index => $contactLeadData) {
                $suiteContactLeadId = '';
                if($moduleName == 'Leads'){
                    $suiteContactLeadId = $contactLeadData['suiteLeadId'];
                }else if($moduleName == "Contacts"){
                    $suiteContactLeadId = $contactLeadData['suiteContactId'];
                }//end of else

                if($contactLeadData['esContactId'] == $contactid && $contactLeadData['deleted'] == 1 && $esId == $contactLeadData['esListId']){
                    $listContactLeadId = $contactLeadData['id'];
                    $updateContactLeadId[] = $suiteContactLeadId;
                    $updateFieldData = array('deleted' => 0);
                    $whereCondition = array('id' => "'".$listContactLeadId."'", 'vi_es_list_id' =>  "'".$esId."'");
                    $updateListDataResult = updateEMSData('vi_contacts_es', $updateFieldData, $whereCondition);

                    $targetListBean->$relationshipName->add($suiteContactLeadId);
                }//end of if
            }//end of foreach

            $getFieldsNames = array("*");
            $whereCondition = array('vi_es_contact_id' => array('operator' => '=', 'value' => "'".$contactid."'"), 'vi_es_list_id' => array('operator' => '=', 'value' => "'".$esId."'"), 'deleted' => array('operator' => '=', 'value' => 0));
            $checkContact = getEMSData('vi_contacts_es', $getFieldsNames, $whereCondition, $orderBy=array());
            $checkContactResult = $GLOBALS['db']->fetchOne($checkContact);

            if(empty($checkContactResult)){
                $relatedContactId = create_guid();   
                $tableName = "vi_contacts_es";
                $contactModuleRecordId = $leadModuleRecordId = '';
                if($moduleName == 'Contacts'){
                    $contactModuleRecordId = $moduleBean->id;
                }else if($moduleName == "Leads"){
                    $leadModuleRecordId = $moduleBean->id;
                }//end of else
                
                $insertContactLeadId[] = $moduleBean->id;
                $data = array('id' => $relatedContactId,'vi_suitecrm_contact_id' => $contactModuleRecordId,'vi_es_contact_id' => $contactid,'vi_suitecrm_lead_id' => $leadModuleRecordId,'vi_es_name' => $syncSoftware,'vi_es_list_id' => $esId,'vi_suitecrm_module' => $moduleName,'vi_es_lead_id' => '', 'deleted' => 0);
                insertESRecord($tableName,$data);
                $targetListBean->$relationshipName->add($moduleBean);
            }//end of if
        }//end of else if

        if(!empty($relationShipData)){
            addEMSToSuiteRelateFieldData($relationShipData, $moduleBean->id);
        }//end of if
    }//end of foreach
}//end of function

//Get All contacts of List of Constant Contact
function getConstanContactListRecords($nextLink, $syncSoftware, $totalCount, $recordCount, $allContactsData){
    $contactData = syncESData($nextLink."&api_key=","GET", $syncSoftware, $data=array());
    $contactDataRespose = (array)json_decode($contactData);
    $finalArray = array_merge($allContactsData, $contactDataRespose['results']);
    $recordCount = count($finalArray);

    $allContacts = array();
    if($totalCount != $recordCount){
        if(isset($contactDataRespose['meta']->pagination->next_link)){
            $nextLink = str_replace("/v2", "", $contactDataRespose['meta']->pagination->next_link);
            $finalArray = getConstanContactListRecords($nextLink, $syncSoftware, $totalCount, $recordCount, $finalArray);
            $recordCount = count($finalArray);
        }//end of if
    }//end of if
    return $finalArray;
}//end of function

function getEMSHelpBoxHtml($url){
    global $suitecrm_version, $theme, $current_language;
    
    $helpBoxContent = '';
    $curl = curl_init();

    $postData = json_encode(array("suiteCRMVersion" => $suitecrm_version, "themeName" => $theme, 'currentLanguage' => $current_language));
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
    
    $data = curl_exec($curl);
    
    $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    if($httpCode == 200){
        $helpBoxContent = $data;
    }//end of if
    curl_close($curl);

    return $helpBoxContent;
}//end of function
?>