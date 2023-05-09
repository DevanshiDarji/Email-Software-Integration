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
class VIAddApiConfiguration{
    public function __construct(){
        $this->addApiConfigurationData();
    } 
    public function addApiConfigurationData(){
        parse_str($_POST['val'], $formData);
        $software = $formData['software'];
        $title = $formData['title'];
        $planType = $formData['planType'];
        $apiConfigurationId = create_guid();
        $recordID = $_POST['id'];
        $apiKey = "";
        if($software == "SendGrid"){
            $apiKey = $formData['api_key'];
        }elseif($software == "ConstantContact"){
            $constantContactApiKey = $formData['constant_contact_api_key'];
            $accessToken = $formData['constant_contact_access_token'];
            $constantContactKeys = array("constantContactApiKey"=>$constantContactApiKey,"accessToken"=>$accessToken);
            $apiKey = json_encode($constantContactKeys);
        }elseif ($software == "Mautic") {
            $mauticUsername = $formData['mautic_username'];
            $mauticPassword = $formData['mautic_password'];
            $mauticUrl = $formData['mautic_url'];
            $mauticKeys = array("mauticUsername"=>$mauticUsername,"mauticPassword"=>$mauticPassword,"mauticUrl"=>$mauticUrl);
            $apiKey = json_encode($mauticKeys);
        }elseif ($software == "ActiveCampaigns") {
            $activeCampaignsUrl = $formData['active_campaigns_url'];
            $activeCampaignsApiToken = $formData['active_campaigns_api_token'];
            $activeCampaignsKeys = array("activeCampaignsUrl"=>$activeCampaignsUrl,"activeCampaignsApiToken"=>$activeCampaignsApiToken);
            $apiKey = json_encode($activeCampaignsKeys);
        }elseif($software == "SendInBlue"){
            $apiKey = $formData['send_in_blue_api_key'];
        }
        $fieldsResult = $this->checkAPIKeyValidation($software,$apiKey,$planType);
        
        if($recordID == ""){
            if(!array_key_exists('errors',$fieldsResult) && !array_key_exists('error_key', $fieldsResult)){
                if($planType == ""){
                    $planType = 'NULL';
                }
                if(!empty($fieldsResult)){
                    $insData = "INSERT INTO vi_api_configuration(id,email_software,plan_type,api_key,title) 
                      values('$apiConfigurationId','$software',$planType,'$apiKey','$title')";   
                    $insResult = $GLOBALS['db']->query($insData);
                    if($insResult){
                        $result = array('code'=>1);
                    }else{
                        $result = array('code'=>2);
                    }
                }else{
                    $result = array('code'=>0);
                }//end of else
            }else{
                $result = array('code'=>0);
            }
        }else{
            if(!array_key_exists('errors',$fieldsResult) && !array_key_exists('error_key', $fieldsResult)){
                if($planType == ""){
                    $planType = 'NULL';
                }
                if(!empty($fieldsResult)){
                    $updateData = "UPDATE vi_api_configuration 
                                    SET email_software = '$software', api_key = '$apiKey',plan_type=$planType,title = '$title' 
                                    WHERE id = '$recordID'";
                    $updateResult = $GLOBALS['db']->query($updateData);
                    if($updateResult){
                        $result = array('code'=>3);
                    }else{
                        $result = array('code'=>4);
                    }
                }else{
                    $result = array('code'=>0);
                }//end of else
            }else{
                $result = array('code'=>0);
            }
        } 
        echo json_encode($result);
    }//end of method
    public function syncESKeyData($url,$method,$syncSoftware,$apiKey){
        if($syncSoftware == "SendGrid"){
            $url = "https://api.sendgrid.com/v3/".$url;
            $apiKey = 'Authorization: Bearer'.' '.$apiKey;
        }elseif ($syncSoftware == "Mautic") {
            $allKeys = (array)json_decode(html_entity_decode($apiKey));
            $url = $allKeys['mauticUrl'].$url;
            $finalKey = base64_encode ($allKeys['mauticUsername'].":".$allKeys['mauticPassword']);
            $apiKey = 'Authorization: Basic '.$finalKey;
        }elseif($syncSoftware == "ConstantContact"){
            $allKeys = (array)json_decode(html_entity_decode($apiKey));
            $url = "https://api.constantcontact.com/v2".$url.$allKeys['constantContactApiKey'];
            $finalKey = $allKeys['accessToken'];
            $apiKey = 'Authorization: Bearer '.$finalKey;
        }elseif($syncSoftware == "ActiveCampaigns"){
            $allKeys = (array)json_decode(html_entity_decode($apiKey));
            $url = $allKeys['activeCampaignsUrl'].$url;
            $finalKey = $allKeys['activeCampaignsApiToken'];
            $apiKey = 'Api-Token: '.$finalKey;
        }elseif($syncSoftware == "SendInBlue"){
            $url = "https://api.sendinblue.com/v3/".$url;
            $apiKey = 'api-key:'.$apiKey;    
        } 
        $headers = array(
                        $apiKey,
                        "Content-type: application/json",
                    );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 130);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if($method != 'GET'){
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // converting
        $response = curl_exec($ch);
        return $response;
        curl_close($ch);
    } 
    public function checkAPIKeyValidation($software,$apiKey,$planType){
        if($software == "SendGrid"){ 
            if($planType == 1){
                $operation = NMCUSTOMFIELD;
            }else{
                $operation = LMCUSTOMFIELD;
            }  
        }elseif($software == "Mautic"){
            $operation = "/api/contacts";
        }elseif($software == "ConstantContact"){
            $operation = "/lists?api_key=";
        }elseif($software == "ActiveCampaigns"){
            $operation = "/api/3/contacts";
        }elseif($software == "SendInBlue"){
            $operation = "contacts";
        }
        $result = $this->syncESKeyData($operation,"GET",$software,$apiKey);
        $decodedResult = (array)json_decode($result);
      
        if($software == "ConstantContact"){
            if(!empty($decodedResult)){
                foreach ($decodedResult[0] as $key => $value) {
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
            $decodedResult = json_encode(array_unique($fields));
            $decodedResults = (array)json_decode($decodedResult);
            $decodedResult = $decodedResults;
        } elseif($software == 'ActiveCampaigns') {
            if(!empty($decodedResult['contacts'])){
                foreach ($decodedResult['contacts'][0] as $key => $value) {
                        $fields[$key] = $key;
                }  
                $extraFields = array("id","cdate","orgid","segmentio_id","bounced_hard","bounced_soft","bounced_date","ip","ua","hash","socialdata_lastcheck","email_local","email_domain","sentcnt","rating_tstamp","gravatar","deleted","anonymized","adate","udate","edate","deleted_at","created_utc_timestamp","updated_utc_timestamp","scoreValues");
                foreach ($fields as $keyExtraFields => $valueExtraFields) {
                    if(in_array($valueExtraFields, $extraFields)){
                        unset($fields[$keyExtraFields]);    
                    }
                }
            }//end of if
        }elseif($software == 'SendInBlue'){
            if(!empty($decodedResult['contacts'])){
                foreach ($decodedResult['contacts'][0] as $key => $value) {
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
                if(isset($decodedResult['message']) && $decodedResult['message'] == 'Key not found'){
                    $decodedResult = array();
                }else{
                    $fields = array("email" => "email","LASTNAME" => "LASTNAME","FIRSTNAME" => "FIRSTNAME"); 
                    $decodedResult = json_encode(array_unique($fields));
                    $decodedResults = (array)json_decode($decodedResult);
                    $decodedResult = $decodedResults;
                }//end of else
            }
        }
        return $decodedResult;
    }//end of function
}//end of class
new VIAddApiConfiguration();
?>