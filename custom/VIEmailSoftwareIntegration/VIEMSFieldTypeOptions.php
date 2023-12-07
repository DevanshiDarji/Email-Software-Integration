<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
require_once("modules/AOW_WorkFlow/aow_utils.php");
class VIEMSFieldTypeOptions{
    public function __construct(){
        $this->getFieldTypeOptions();
    }

    //Get Module Field Type Options
    public function getFieldTypeOptions(){
        global $app_list_strings, $beanFiles, $beanList;
        
        $module = $_REQUEST['aow_module'];
        $fieldName = $_REQUEST['aow_fieldname'];
        $aowField = $_REQUEST['aow_newfieldname'];
        
        if (isset($_REQUEST['view'])) {
            $view = $_REQUEST['view'];
        } else {
            $view= 'EditView';
        }//end of else

        if (isset($_REQUEST['aow_value'])) {
            $value = $_REQUEST['aow_value'];
        } else {
            $value = '';
        }//end of else

        require_once($beanFiles[$beanList[$module]]);
        $focus = new $beanList[$module];
        $vardef = $focus->getFieldDefinition($fieldName);

        if($vardef['type'] == 'date' || $vardef['type'] == 'datetime' || $vardef['type'] == 'datetimecombo'){
            $operator = array('Value', 'Date');
        }else{
            $operator = array('Value');
        }//end of else

        if(!file_exists('modules/SecurityGroups/SecurityGroup.php')){
            unset($app_list_strings['aow_condition_type_list']['SecurityGroup']);
        }//end of if

        foreach($app_list_strings['aow_condition_type_list'] as $key => $keyValue){
            if(!in_array($key, $operator)){
                unset($app_list_strings['aow_condition_type_list'][$key]);
            }//end of if
        }//end of foreach

        if($view == 'vi_modulemappingeditview'){
            echo "<select type='text'  name='$aowField' id='$aowField' title='' tabindex='116'>". get_select_options_with_id($app_list_strings['aow_condition_type_list'], $value) ."</select>";
        }//end of if
        die;
    }//end of function
}//end of class
new VIEMSFieldTypeOptions();
?>