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
class VISyncESFields{
    public function __construct(){
        $this->getModuleFields();
    }
    public function syncESData($url,$method,$syncSoftware){
        $selectData = "SELECT * FROM vi_api_configuration WHERE email_software = '$syncSoftware' and deleted = 0";
        $selectResult = $GLOBALS['db']->fetchOne($selectData,false,'',false);
        if(!empty($selectResult['api_key'])){
            if($selectResult['email_software'] == "SendGrid"){
                $url = "https://api.sendgrid.com/v3/".$url;
                $apiKey = 'Authorization: Bearer '.$selectResult['api_key'];    
            }elseif ($selectResult['email_software'] == "Mautic") {
                $allKeys = (array)json_decode(html_entity_decode($selectResult['api_key']));
                $url = $allKeys['mauticUrl'].$url;
                $finalKey = base64_encode ($allKeys['mauticUsername'].":".$allKeys['mauticPassword']);
                $apiKey = 'Authorization: Basic '.$finalKey;
            }elseif($selectResult['email_software'] == "ConstantContact"){
                $allKeys = (array)json_decode(html_entity_decode($selectResult['api_key']));
                $url = "https://api.constantcontact.com/v2".$url.$allKeys['constantContactApiKey'];
                $finalKey = $allKeys['accessToken'];
                $apiKey = 'Authorization: Bearer '.$finalKey;
            }elseif($selectResult['email_software'] == "ActiveCampaigns"){
                $allKeys = (array)json_decode(html_entity_decode($selectResult['api_key']));
                $url = $allKeys['activeCampaignsUrl'].$url;
                $finalKey = $allKeys['activeCampaignsApiToken'];
                $apiKey = 'Api-Token: '.$finalKey;
            }elseif($selectResult['email_software'] == "SendInBlue"){
                $url = "https://api.sendinblue.com/v3/".$url;
                $apiKey = 'api-key:'.$selectResult['api_key'];    
            }
        }
        $headers = array(
                        $apiKey,
                        "Content-type: application/json",
                    );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if($method != "GET"){
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // converting
        $response = curl_exec($ch);
        return $response;
        curl_close($ch);
    } 

    public function saveESFields($module,$moduleMappingSoftware,$finaljson){
        $selectData = "SELECT * FROM vi_email_fields WHERE email_software = '$moduleMappingSoftware' AND module = '$module'";
        $selectRow = $GLOBALS['db']->fetchOne($selectData);  
        
        if(empty($selectRow)){
            $emailFieldsId = create_guid();
            $insData = "INSERT INTO vi_email_fields(id,email_software,module,fields) 
                        values('$emailFieldsId','$moduleMappingSoftware','$module','$finaljson')";
            $result = $GLOBALS['db']->query($insData); 
            if(!empty($result)){
                require_once('include/utils.php');
                $fieldSynclabel = translate('LBL_FIELDS_SYNC', 'Administration');
                $moduleLabel = translate('LBL_MODULE', 'Administration');
                echo $fieldSynclabel." ".$moduleMappingSoftware." ".$module." ".$moduleLabel;
            }else{
                echo "Error in Fields Synchronization!!";
            }
        }else{
            $emailFieldsId = $selectRow['id'];
            $updateData = "UPDATE vi_email_fields SET email_software='$moduleMappingSoftware',module='$module',fields='$finaljson' WHERE id = '$emailFieldsId'";
            $result = $GLOBALS['db']->query($updateData);
            if(!empty($result)){
                require_once('include/utils.php');
                $fieldSynclabel = translate('LBL_FIELDS_SYNC', 'Administration');
                $moduleLabel = translate('LBL_MODULE', 'Administration');
                echo $fieldSynclabel." ".$moduleMappingSoftware." ".$module." ".$moduleLabel;
            }else{
                echo "Error in Fields Synchronization!!";
            }
        } 
    }
    
    public function getModuleFields(){
        $planType = getPlanType($_POST['moduleMappingSoftware']);
        if($_POST['module'] == ""){
            require_once('include/utils.php');
            $selectLabel = translate('LBL_SELECT', 'Administration');
            $moduleLabel = translate('LBL_MODULE', 'Administration');
            echo $selectLabel." ".$_POST['moduleMappingSoftware']." ".$moduleLabel;
        }else{
            $finaljson = "";
            if($_POST['moduleMappingSoftware'] == "SendGrid"){
                if($_POST['module'] == 'Contacts'){
                    if($planType == 1){
                        $operation = NMCUSTOMFIELD;
                    }else{
                        $operation = LMCUSTOMFIELD;
                    }
                    $result = $this->syncESData($operation,"GET","SendGrid");
                    $customFields = array();
                    $reservedFields = array();
                   
                    if($planType == 1) {
                        $result = json_decode($result);
                        if(isset($result->custom_fields)) {
                            $customFields[] = $result->custom_fields; 
                            $reservedFields[] = $result->reserved_fields;    
                        }else {
                            $reservedFields[] = $result->reserved_fields;    
                        }    
                    }else{
                        if(isset($result->custom_fields)) {
                            $customFields[] = $result->custom_fields; 
                        }
                        $operation = LMRESERVEDFIELD;
                        $reservedFieldsresult = $this->syncESData($operation,"GET","SendGrid");
                        $reservedFields[] = $reservedFieldsresult->reserved_fields;
                    }
                    $combinedarray = array_merge($customFields,$reservedFields);
                    $finaljson = "";
                    $combinedFieldsArray = array();
                    foreach ((array)$combinedarray as $key => $value) {
                        foreach ((array)$value as $k => $v) {
                            $combinedFieldsArray[] = (array)$v;
                        }
                    }              
                    foreach ($combinedFieldsArray as $keyT => $valueT) {
                        if($planType == 1) {
                            $type = $valueT['field_type'];
                        } else {
                            $type = $valueT['type'];
                        }
                        if($type == "date"){
                            unset($combinedFieldsArray[$keyT]);
                        }
                        if($type == "set"){
                            unset($combinedFieldsArray[$keyT]);
                        }
                    }
                    $combinedFieldsArray = array_values($combinedFieldsArray);
                    foreach ($combinedFieldsArray as $key => $value) {
                        $finalCombinedData[] = $value['name'];
                        $finaljson = json_encode($finalCombinedData);
                    }
                }

                if($_POST['module'] == 'Contacts_List'){
                    if($planType == 1){
                        $operationList = NMADDUPDATELISTS;
                    }else{
                        $operationList = LMADDUPDATELISTS;
                    }
                    $result = (array)json_decode($this->syncESData($operationList,"GET","SendGrid"));
                    $finaljson = "";
                    $final = array();
                    if($planType == 1){
                        $resultData = $result['result'];
                    }else{
                        $resultData = $result['lists'];
                    }
                    if(!empty($resultData)){
                        $listData = (array)$resultData;
                        foreach ((array)$listData[0] as $key => $value) {
                            $final[] = $key;
                        }
                        foreach ($final as $k => $v) {
                            if($v == "id"){
                                unset($final[$k]);
                            }
                             if($planType == 1){
                                if($v == "contact_count"){
                                    unset($final[$k]);
                                }
                                if($v == "_metadata"){
                                    unset($final[$k]);
                                }
                            }else{
                                if($v == "recipient_count"){
                                    unset($final[$k]);
                                }
                            }
                        }
                    }else{
                        $final = array("name");
                    }
                    foreach ($final as $key => $value) {
                        $finalCombinedData[] = $value;
                        $finaljson = json_encode($finalCombinedData);
                    }
                }

                if($_POST['module'] == 'Campaigns'){
                    $result = $this->syncESData("campaigns","GET","SendGrid");
                    $decodedarray = (array)json_decode($result); 
                    $data = (array)$decodedarray['result'][0];
                    $finaljson = "";
                    if(!empty($data)){
                        $unsetFieldNames = array('id','sender_id','list_ids','segment_ids','suppression_group_id','custom_unsubscribe_url','status','editor','ip_pool','categories','plain_content','html_content');
                        foreach ($unsetFieldNames as $key => $value) {
                            unset($data[$value]);
                        }
                        foreach ($data as $key => $value) {
                            $finalCombinedData[] = $key;
                            $finaljson = json_encode($finalCombinedData);
                        }
                    }else{
                        $finalCombinedData = array('title','subject');
                        $finaljson = json_encode($finalCombinedData);
                    }
                }
                $this->saveESFields($_POST['module'],$_POST['moduleMappingSoftware'],$finaljson);
            }elseif($_POST['moduleMappingSoftware'] == "Mautic"){
                if($_POST['module'] == "Assets"){
                    $response = $this->syncESData("/api/assets","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    if($result['total'] > 0){
                        $fields = (array)$result['assets'][0];
                        $fields["file"] = "";
                        $extraFields = array("isPublished","dateAdded","dateModified","createdBy","createdByUser","modifiedBy","modifiedByUser","id");
                        foreach ($extraFields as $keyExtraFields => $valueExtraFields) {
                            unset($fields[$valueExtraFields]);    
                        }
                        foreach ($fields as $key => $value) {
                            $fields[$key] = $key;
                        }
                        $finaljson = json_encode($fields);
                    }
                }

                if($_POST['module'] == "Contacts"){
                    $response = $this->syncESData("/api/contacts","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    $resultCompaniesData = (array)$result['contacts'];
                    foreach ($resultCompaniesData as $key => $value) {
                        foreach ($value->fields->all as $key => $value) {
                            $fields[$key] = $key;        
                        }
                    }
                    unset($fields['id']);
                    $finaljson = json_encode(array_unique($fields));
                }

                if($_POST['module'] == "Campaigns"){
                    $response = $this->syncESData("/api/campaigns","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    $finalResult = (array)$result['campaigns'];
                    $fields = array();

                    foreach ($finalResult as $keyFields => $valueFields) {
                        foreach ($valueFields as $key => $value) {
                            $fields[$key] = $key;
                        }
                    }
                    $extraFields = array("isPublished","dateAdded","dateModified","createdBy","createdByUser","modifiedBy","modifiedByUser","id");
                    foreach ($extraFields as $keyExtraFields => $valueExtraFields) {
                        unset($fields[$valueExtraFields]);    
                    }
                    $finaljson = json_encode(array_unique($fields));
                }

                if($_POST['module'] == "Companies"){
                    $response = $this->syncESData("/api/companies","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    $resultCompaniesData = (array)$result['companies'];
                    foreach ($resultCompaniesData as $key => $value) {
                        foreach ($value->fields->all as $key => $value) {
                            $fields[$key] = $key;        
                        }
                    }
                    unset($fields['id']);
                    $finaljson = json_encode(array_unique($fields));
                }

                if($_POST['module'] == "Segments"){
                    $response = $this->syncESData("/api/segments","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    $resultListData = (array)$result['lists'];
                    foreach ($resultListData as $keyList => $valueList) {
                        foreach ((array)$valueList as $keyFieldsData => $valueFieldsData) {
                            $fields[$keyFieldsData] = $keyFieldsData;    
                        }
                    }
                    $extraFields = array("isPublished","dateAdded","dateModified","createdBy","createdByUser","modifiedBy","modifiedByUser","id");
                    foreach ($extraFields as $keyExtraFields => $valueExtraFields) {
                        unset($fields[$valueExtraFields]);    
                    }
                    $finaljson = json_encode(array_unique($fields));
                }
                $this->saveESFields($_POST['module'],$_POST['moduleMappingSoftware'],$finaljson);
            }elseif($_POST['moduleMappingSoftware'] == "ConstantContact"){
                if($_POST['module'] == "Contacts_List"){
                    $response = $this->syncESData("/lists?api_key=","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    if(!empty($result)){
                        foreach ($result[0] as $key => $value) {
                            $fields[$key] = $key;    
                        }                
                        $extraFields = array("id","status","created_date","modified_date","contact_count");
                        foreach ($fields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($fields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $fields['name'] = 'name';    
                    }
                    $finaljson = json_encode(array_unique($fields));
                    $relatedModule = "";
                    $response = $this->syncESData("/contacts?api_key=","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    if(isset($result['results'])){
                        $contactResult = $result['results'][0];
                    }else{
                        $contactResult = array();
                    }
                    
                    if(!empty($contactResult)){
                        foreach ($contactResult as $key => $value) {
                            if($key == "email_addresses"){
                                foreach ($value[0] as $keyEmail => $valueEmail) {
                                    $arrayAllContactFields[$keyEmail] = $keyEmail;        
                                }
                            }
                            $arrayAllContactFields[$key] = $key;
                        }
                        $extraFields = array("id","status","addresses","notes","confirmed","lists","custom_fields","created_date","modified_date","email_addresses");
                        foreach ($arrayAllContactFields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($arrayAllContactFields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $array = array("fax","source","confirm_status","opt_in_source","opt_in_date","email_address","prefix_name","first_name","middle_name","last_name","job_title","company_name","home_phone","work_phone","cell_phone","source_details");
                        foreach ($array as $valueExtraFields) {
                            $arrayAllContactFields[$valueExtraFields] = $valueExtraFields;
                        }
                    }
                    $finaljsonForContacts = json_encode(array_unique($arrayAllContactFields));
                    $this->saveESFields("Contacts",$_POST['moduleMappingSoftware'],$finaljsonForContacts);      

                }elseif ($_POST['module'] == "Campaigns") {
                    $response = $this->syncESData("/emailmarketing/campaigns?api_key=","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    if(!empty($result['results'])){
                        $response = $result['results'][0];
                        foreach ($response as $key => $value) {
                            $fields[$key] = $key;    
                        }
                    }else{
                        //take static values
                        $fields['name'] = "name";
                    }
                    
                    $extraFields = array("id","status","modified_date");
                    foreach ($fields as $keyExtraFields => $valueExtraFields) {
                        if(in_array($valueExtraFields, $extraFields)){
                            unset($fields[$keyExtraFields]);    
                        }
                    }
                    $finaljson = json_encode(array_unique($fields));
                }else if($_POST['module'] == "Contacts"){
                    $response = $this->syncESData("/contacts?api_key=","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    
                    $contactResult = $result['results'][0];
                    if(!empty($contactResult)){
                        foreach ($contactResult as $key => $value) {
                            if($key == "email_addresses"){
                                foreach ($value[0] as $keyEmail => $valueEmail) {
                                    $arrayAllContactFields[$keyEmail] = $keyEmail;        
                                }
                            }
                            $arrayAllContactFields[$key] = $key;
                        }
                        $extraFields = array("id","status","addresses","notes","confirmed","lists","custom_fields","created_date","modified_date","email_addresses");
                        foreach ($arrayAllContactFields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($arrayAllContactFields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $array = array("fax","source","confirm_status","opt_in_source","opt_in_date","email_address","prefix_name","first_name","middle_name","last_name","job_title","company_name","home_phone","work_phone","cell_phone","source_details");
                        foreach ($array as $valueExtraFields) {
                            $arrayAllContactFields[$valueExtraFields] = $valueExtraFields;
                        }
                    }

                    $finaljson = json_encode(array_unique($arrayAllContactFields));
                }
                $this->saveESFields($_POST['module'],$_POST['moduleMappingSoftware'],$finaljson);
            }elseif($_POST['moduleMappingSoftware'] == "ActiveCampaigns"){
                if($_POST['module'] == "Contacts"){
                    $response = $this->syncESData("/api/3/contacts","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    
                    if(!empty($result['contacts'])){
                        foreach ($result['contacts'][0] as $key => $value) {
                            $fields[$key] = $key;    
                        }  
                        $extraFields = array("id","cdate","orgid","segmentio_id","bounced_hard","bounced_soft","bounced_date","ip","ua","hash","socialdata_lastcheck","email_local","email_domain","sentcnt","rating_tstamp","gravatar","deleted","anonymized","adate","udate","edate","deleted_at","created_utc_timestamp","updated_utc_timestamp","scoreValues", "fieldValues");
                        foreach ($fields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($fields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $fields = array("email" => "email","phone" => "phone","firstName" => "firstName","lastName" => "lastName","orgname" => "orgname","links" => "links","account" => "account","customerAccount" => "customerAccount","organization" => "organization");
                    }

                    if(!empty($result['fieldValues'])){
                        foreach ($result['fieldValues'] as $fkey => $fvalue) {
                            $fieldData = (array)$fvalue;
                            $fieldId = $fieldData['field'];
                            $endPointURL = "/api/3/fields/".$fieldId;
                            $customFieldList = $this->syncESData($endPointURL,"GET",$_POST['moduleMappingSoftware']);
                            $customFieldListResult = (array)json_decode($customFieldList);
                            $fieldName = $customFieldListResult['field']->title;
                            $fields[$fieldName] = $fieldName;
                        }
                    }

                    $activeCampaignCustomFieldList = $this->syncESData("/api/3/fields","GET",$_POST['moduleMappingSoftware']);
                    $activeCampaignCustomFieldListResult = (array)json_decode($activeCampaignCustomFieldList);
                    $allFields = $activeCampaignCustomFieldListResult['fields'];
                    foreach ($allFields as $index => $fieldsData) {
                        $fieldName = $fieldsData->title;
                        $fields[$fieldName] = $fieldName;
                    }//end of foreach
                }

                if($_POST['module'] == "Contacts_List"){
                    $response = $this->syncESData("/api/3/lists","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    if(!empty($result['lists'])){
                        foreach ($result['lists'][0] as $key => $value) {
                            $fields[$key] = $key;    
                        }  
                        $extraFields = array("stringid","userid","cdate","p_use_tracking","p_use_analytics_read","p_use_analytics_link","p_use_twitter","p_use_facebook","p_embed_image","p_use_captcha","send_last_broadcast","private","analytics_domains","analytics_source","analytics_ua","twitter_token","twitter_token_secret","facebook_session","carboncopy","subscription_notify","unsubscription_notify","require_name","get_unsubscribe_reason","optinoptout","optinmessageid","optoutconf","deletestamp","udate","links","id","user");
                        foreach ($fields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($fields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $fields = array("name" => "name","sender_reminder" => "sender_reminder");
                    }
                }

                if($_POST['module'] == "Organizations"){
                    $response = $this->syncESData("/api/3/organizations","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    if(!empty($result['organizations'])){
                        foreach ($result['organizations'][0] as $key => $value) {
                            $fields[$key] = $key;    
                        }  
                        $extraFields = array("created_timestamp","updated_timestamp","links","id");
                        foreach ($fields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($fields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $fields = array("name" => "name");
                    }

                    $activeCampaignAccountsCustomFieldList = $this->syncESData("/api/3/accountCustomFieldMeta","GET",$_POST['moduleMappingSoftware']);
                    $activeCampaignAccountsCustomFieldListResult = (array)json_decode($activeCampaignAccountsCustomFieldList);
                    $customFieldList = $activeCampaignAccountsCustomFieldListResult['accountCustomFieldMeta'];
                    foreach ($customFieldList as $index => $fieldsData) {
                        $fieldName = $fieldsData->fieldLabel;
                        $fields[$fieldName] = $fieldName;
                    }//end of foreach
                }
                $finaljson = json_encode(array_unique($fields));

                $this->saveESFields($_POST['module'],$_POST['moduleMappingSoftware'],$finaljson);   
            }elseif($_POST['moduleMappingSoftware'] == "SendInBlue"){
                if($_POST['module'] == "Contacts"){
                    $response = $this->syncESData("contacts","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    
                    if(!empty($result['contacts'])){
                        foreach ($result['contacts'][0] as $key => $value) {
                            $fields[$key] = $key;
                            if($key == "attributes"){
                                foreach ($value as $keyOther => $valueOther) {
                                    $fields[$keyOther] = $keyOther;
                                }
                            }    
                        }  
                        $extraFields = array("id","emailBlacklisted","smsBlacklisted","createdAt","modifiedAt","listIds","attributes");
                        foreach ($fields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($fields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $fields = array("email" => "email","LASTNAME" => "LASTNAME","FIRSTNAME" => "FIRSTNAME");
                    }
                }

                if($_POST['module'] == "Contacts_List"){
                    $response = $this->syncESData("contacts/lists","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    if(!empty($result['lists'])){
                        foreach ($result['lists'][0] as $key => $value) {
                            $fields[$key] = $key;    
                        }  
                        $extraFields = array("id","folderId","totalSubscribers","totalBlacklisted");
                        foreach ($fields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($fields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $fields = array("name" => "name");
                    }
                }      

                if($_POST['module'] == 'Campaigns'){
                    $response = $this->syncESData("emailCampaigns","GET",$_POST['moduleMappingSoftware']);
                    $result = (array)json_decode($response);
                    if(!empty($result['campaigns'])){
                        foreach ($result['campaigns'][0] as $key => $value) {
                            $fields[$key] = $key;    
                        }  
                        $extraFields = array("id","type","testSent","header","footer","sender","replyTo","toField","htmlContent","inlineImageActivation","mirrorActive","recipients","statistics","scheduledAt","createdAt","modifiedAt","shareLink","sendAtBestTime","abTesting");
                        foreach ($fields as $keyExtraFields => $valueExtraFields) {
                            if(in_array($valueExtraFields, $extraFields)){
                                unset($fields[$keyExtraFields]);    
                            }
                        }
                    }else{
                        $fields = array("name" => "name","subject" => "subject","status"=>"status");
                    }
                }
                $finaljson = json_encode(array_unique($fields));
                $this->saveESFields($_POST['module'],$_POST['moduleMappingSoftware'],$finaljson);
            }
        }
    }//end of function
}//end of class
new VISyncESFields();
?>