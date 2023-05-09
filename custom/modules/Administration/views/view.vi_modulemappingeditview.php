<?php
/*********************************************************************************
 * This file is part of package Email Software Integration.
 * 
 * Author : Variance InfoTech PVT LTD (http://www.varianceinfotech.com)
 * All rights (c) 2022 by Variance InfoTech PVT LTD
 *
 * This Version of Email Software Integration is licensed software and may only be used in 
 * alignment with the License Agreement received with this Software.
 * This Software is copyrighted and may not be further distributed without
 * written consent of Variance InfoTech PVT LTD
 * 
 * You can contact via email at info@varianceinfotech.com
 * 
 ********************************************************************************/
include("custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php");
require_once('include/MVC/View/SugarView.php');
class Viewvi_modulemappingeditview extends SugarView {
    public function __construct() {
        parent::init();
    }
    //get all fields
    public function getAllFields($source_module){
        require_once('modules/ModuleBuilder/parsers/ParserFactory.php');
        global $mod_strings, $app_strings;
        $view_array = ParserFactory::getParser('editview',$source_module);
        $panelArray = $view_array->_viewdefs['panels'];
        $bean = BeanFactory::newBean($source_module);
        $field = $bean->getFieldDefinitions();
        $addressFieldData = array();
        foreach($field as $value){
            if($value['type'] == 'varchar'){
                if(isset($value['group']) && $value['group'] != ""){
                    $fieldName = $value['name'];
                    $fieldLabel = translate($value['vname'],$source_module);
                    $addressFieldData[$fieldName] =  $fieldLabel;
                    if(strpos($fieldLabel, ':')){
                        $fieldLabel = str_replace(":", "", $fieldLabel);
                        $addressFieldData[$fieldName] =  $fieldLabel;
                    }
                }        
            }
        }
        $editViewFieldArray = array();
        foreach ($panelArray as $key => $value) {
            foreach ($value as $keys => $values) {
                $editViewFieldArray[] = $values;
            }
        }
        $data = array();
        foreach($editViewFieldArray as $key => $value) {
            foreach($value as $k => $v) {
                if(array_key_exists($v, $field)) {
                    if(isset($mod_strings[$field[$v]['vname']])){       
                        $fieldLabel = $mod_strings[$field[$v]['vname']];
                    }else if(isset($app_strings[$field[$v]['vname']])){
                        $fieldLabel= $app_strings[$field[$v]['vname']];
                        $fieldName = $v;
                        $bean = BeanFactory::newBean($source_module);
                        $fieldData = $bean->field_defs[$fieldName];
                        if($fieldData['type'] != "enum"){
                            if(strpos($fieldLabel, ':')){
                                $fieldLabel = str_replace(":", "", $fieldLabel);
                            }
                            $data[$fieldName] = $fieldLabel;
                        }
                    }else{
                        require_once('include/utils.php');
                        $fieldLabel = translate($field[$v]['vname'], $source_module);
                        $fieldName = $v;
                        $bean = BeanFactory::newBean($source_module);
                        $fieldData = $bean->field_defs[$fieldName];
                            if(strpos($fieldLabel, ':')){
                                $fieldLabel = str_replace(":", "", $fieldLabel);
                            }
                            $data[$fieldName] = $fieldLabel;
                    }
                }    
            }
        }

        $unsetFieldNames = array('survey_questions_display','configurationGUI','line_items','insert_fields','email2','action_lines','condition_lines','reminders','pdffooter','pdfheader','duration','duration_hours','currency_id');
        foreach ($unsetFieldNames as $key => $value) {
            unset($data[$value]);
        }
        unset($addressFieldData['email2']);
        $arrayMerge = array_merge($data,$addressFieldData);
        $uniqueArray = array_unique($arrayMerge,SORT_REGULAR);
        asort($uniqueArray);
        return $uniqueArray;
    }//end of function

    public function getESFields($sendgridModule,$moduleMappingSoftware){
        $sql = "SELECT * FROM vi_email_fields where email_software = '$moduleMappingSoftware' and module = '$sendgridModule'";
        $result = $GLOBALS['db']->query($sql);
        $fields = "";
        while($row = $GLOBALS['db']->fetchByAssoc($result) ){
            $fields = html_entity_decode($row['fields']);
        }
        $combinedarray = json_decode($fields);
        $allfields = array();
        foreach ((array)$combinedarray as $key => $value) {
            if($moduleMappingSoftware == "SendGrid"){
                $allfields[] = $value;
            }elseif ($moduleMappingSoftware == "ActiveCampaigns" || $moduleMappingSoftware == "SendInBlue" || $moduleMappingSoftware == "ConstantContact" || $moduleMappingSoftware == "Mautic") {
                $allfields[] = $key;
            }
        }
        return $allfields;
    }//end of function

    public function display() {
        if(isset($_REQUEST['records'])){
            $recordId = $_REQUEST['records'];   
        }else{
            $recordId = "";
        }
        global $mod_strings, $sugar_config, $app_strings;
        $listviewMaxRecord = $sugar_config['list_max_entries_per_page'];
        $smarty = new Sugar_Smarty();
        $software = 'SendGrid';
        $planType = getPlanType($software);
        if($planType == '1'){
            $sendGridAllModules = array("Contacts" => "Contacts","Contacts List" => "Contacts_List"); 
        }else{
            $sendGridAllModules = array("Contacts" => "Contacts","Contacts List" => "Contacts_List","Campaigns" => "Campaigns"); 
        }
        $sendInBlueAllModules = array("Contacts" => "Contacts","Contacts List" => "Contacts_List");
        $mauticAllModules = array("Assets","Contacts","Segments","Campaigns","Companies");
        $constantContactAllModules = array("Contacts List" => "Contacts_List");
        $activeCampaignsAllModules = array("Contacts" => "Contacts","Contacts List" => "Contacts_List","Organizations" => "Organizations");
        $targetListModule = array("Contacts" => "Contacts", "Leads" => "Leads");

        $listViewUrl = "index.php?module=Administration&action=vi_modulemappinglistview";

        $fieldsarray = $fieldsContactsarray = array();
        $getFieldsNames = array("*");
        if($recordId != ""){
            $selData = "SELECT * FROM vi_module_mapping WHERE module_mapping_id = '$recordId'";
            $row = $GLOBALS['db']->fetchOne($selData,false,'',false);
            $title = $row['title'];
            $moduleMappingSoftware = $row['email_software'];
            $suitecrmModule = $row['suitecrm_module'];
            $targetListSubpanelModule = $row['target_list_subpanel_module'];
            $conditionalOperator = $row['conditional_operator'];

            $esModule = $row['es_module'];
            $status = $row['status'];
            $batchRecord = $row['batch_record'];
            $selFieldsData = "SELECT * FROM vi_integration_field_mapping WHERE module_mapping_id = '$recordId'";
            $result = $GLOBALS['db']->query($selFieldsData);
            
            while($rows = $GLOBALS['db']->fetchByAssoc($result)){
                $suitecrmModuleField = $rows['suitecrm_module_fields'];
                $esModuleField = $rows['es_module_fields'];
                $fieldMappingId = $rows['field_mapping_id'];
                $fieldsarray[] = array("suitecrmModuleField"=>$suitecrmModuleField,"esModuleField"=>$esModuleField);
            }
            $selContactsFieldsData = "SELECT * FROM vi_integration_contacts_field_mapping WHERE module_mapping_id = '$recordId'";
            $resultContactsFieldsData = $GLOBALS['db']->query($selContactsFieldsData);
            
            while($rowsContacts = $GLOBALS['db']->fetchByAssoc($resultContactsFieldsData)){
                $suitecrmModuleField = $rowsContacts['suitecrm_contacts_module_fields'];
                $sendgridModuleField = $rowsContacts['sendgrid_contacts_module_fields'];
                $fieldMappingId = $rowsContacts['contacts_field_mapping_id'];
                $fieldsContactsarray[] = array("suitecrmModuleField"=>$suitecrmModuleField,"sendgridModuleField"=>$sendgridModuleField);
            }
            
            $smarty->assign('TITLE',$title);
            $smarty->assign('SUITECRMMODULE',$suitecrmModule);
            $smarty->assign('TARGETLIST_SUBPANEL_MODULE', $targetListSubpanelModule);
            $smarty->assign('ESMODULE',$esModule);
            $smarty->assign('STATUS',$status);
            $smarty->assign("BATCH_RECORD",$batchRecord);
            $smarty->assign("BATCH_MANAGMENT_STATUS",$row['batch_management_status']);
            $smarty->assign('MODULEMAPPINGSOFTWARE',$moduleMappingSoftware);
            $smarty->assign('RECORDID',$recordId);

            $sourceModuleAllFields = $this->getAllFields($suitecrmModule);
            $smarty->assign('SOURCEMODULEALLFIELDS',$sourceModuleAllFields);
            if($targetListSubpanelModule != ''){
                $sourceModuleContactsFields = $this->getAllFields($targetListSubpanelModule);
                $smarty->assign('SOURCEMODULECONTACTSFIELDS',$sourceModuleContactsFields);
            }//end of if

            $esModuleAllFields = $this->getESFields($esModule,$moduleMappingSoftware);
            $esContactsModuleFields = $this->getESFields("Contacts",$moduleMappingSoftware);
            $smarty->assign('ESMODULEALLFIELDS',$esModuleAllFields);        
            $smarty->assign('ESMODULECONTACTSFIELDS',$esContactsModuleFields);

            //All Condition Block Html
            $emsAllConditionData = getEMSConditionBlockHtml($suitecrmModule, $recordId, $conditionType='All', $getFieldsNames);
            //Any Condition Block Html
            $emsAnyConditionData = getEMSConditionBlockHtml($suitecrmModule, $recordId, $conditionType='Any', $getFieldsNames);

            $smarty->assign('CONDITIONAL_OPERATOR',$conditionalOperator); 
            $smarty->assign('MODULE_LABEL',translate($suitecrmModule));            
            $smarty->assign("EMS_ALL_CONDITION_DATA", $emsAllConditionData); //EMS All Condition Data
            $smarty->assign("EMS_ANY_CONDITION_DATA", $emsAnyConditionData); //EMS Any Condition Data
        }
        
        $smarty->assign('FIELDSARRAY',$fieldsarray);
        $smarty->assign('FIELDSCONTACTSARRAY',$fieldsContactsarray);
        $smarty->assign('LISTVIEWURL',$listViewUrl);        
        $smarty->assign("MOD", $mod_strings);
        $smarty->assign("APP", $app_strings);
        $smarty->assign("RANDOM_NUMBER", rand());
        $smarty->assign('SENDGRIDALLMODULES',$sendGridAllModules);
        $smarty->assign('MAUTIC_ALL_MODULES',$mauticAllModules);    
        $smarty->assign('CONSTANTCONTACTALLMODULES',$constantContactAllModules);    
        $smarty->assign('ACTIVECAMPAIGNSALLMODULES',$activeCampaignsAllModules);
        $smarty->assign("SENDINBLUEALLMODULES",$sendInBlueAllModules);
        $smarty->assign("LISTVIEW_MAX_RECORD",$listviewMaxRecord);
        $smarty->assign('TARGET_LIST_MODULES', $targetListModule);    
        
        $smarty->display('custom/modules/Administration/tpl/vi_modulemappingeditview.tpl');
        parent::display();
    }//end of display function
}//end of class
?>