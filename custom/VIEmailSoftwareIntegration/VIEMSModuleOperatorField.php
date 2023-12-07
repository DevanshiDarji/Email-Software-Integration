<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
require_once("modules/AOW_WorkFlow/aow_utils.php");
class VIEMSModuleOperatorField{
	public function __construct(){
		$this->getModuleOperatorField();
	}

    //Get Module Operator Fields
	public function getModuleOperatorField(){
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

        if($vardef){
            switch($vardef['type']) {
                case 'double':
                case 'decimal':
                case 'float':
                case 'currency':
                    $operators = array('Equal_To', 'Not_Equal_To', 'Greater_Than', 'Less_Than', 'Greater_Than_or_Equal_To', 'Less_Than_or_Equal_To', 'is_null', 'is_not_null');
                    break;
                case 'uint':
                case 'ulong':
                case 'long':
                case 'short':
                case 'tinyint':
                case 'int':
                    $operators = array('Equal_To', 'Not_Equal_To', 'Greater_Than', 'Less_Than', 'Greater_Than_or_Equal_To', 'Less_Than_or_Equal_To', 'is_null', 'is_not_null');
                    break;
                case 'date':
                case 'datetime':
                case 'datetimecombo':
                    $operators = array('Equal_To', 'Not_Equal_To', 'Greater_Than', 'Less_Than', 'Greater_Than_or_Equal_To', 'Less_Than_or_Equal_To', 'is_null', 'is_not_null', 'today', 'tomorrow', 'yesterday', 'is_in_last_7_days', 'is_in_last_30_days', 'is_in_last_60_days', 'is_in_last_90_days', 'is_in_last_120_days', 'is_in_this_week', 'is_in_the_last_week', 'is_in_this_month', 'is_in_the_last_month');
                    break;
                case 'enum':
                case 'multienum':
                    $operators = array('Equal_To', 'Not_Equal_To', 'is_null', 'is_not_null');
                    break;
                case 'bool':
                    $operators = array('Equal_To', 'Not_Equal_To');
                    break;
                default:
                    $operators = array('Equal_To', 'Not_Equal_To', 'Contains', 'does_not_contains', 'Starts_With', 'Ends_With', 'is_null', 'is_not_null');
                    break;
            }//end of switch
            
            $operatorsArray = array('does_not_contains' => 'LBL_DOES_NOT_CONTAINS', 'is_null' => 'LBL_IS_EMPTY', 'is_not_null' => 'LBL_IS_NOT_EMPTY', 'today' => 'LBL_TODAY', 'tomorrow' => 'LBL_TOMORROW', 'yesterday' => 'LBL_YESTERDAY', 'is_in_last_7_days' => 'LBL_IS_IN_LAST_7_DAYS', 'is_in_last_30_days' => 'LBL_IS_IN_LAST_30_DAYS', 'is_in_last_60_days' => 'LBL_IS_IN_LAST_60_DAYS', 'is_in_last_90_days' => 'LBL_IS_IN_LAST_90_DAYS', 'is_in_last_120_days' => 'LBL_IS_IN_LAST_120_DAYS', 'is_in_this_week' => 'LBL_IS_IN_THIS_WEEK', 'is_in_the_last_week' => 'LBL_IS_IN_THE_LAST_WEEK', 'is_in_this_month' => 'LBL_IS_IN_THIS_MONTH', 'is_in_the_last_month' => 'LBL_IS_IN_THE_LAST_MONTH');

            foreach ($operatorsArray as $operatorName => $operatorLabel) {
                $app_list_strings['aow_operator_list'][$operatorName] = translate($operatorLabel, 'Administration');   
            }//end of foreach

            foreach($app_list_strings['aow_operator_list'] as $key => $keyValue){
                if(!in_array($key, $operators)){
                    unset($app_list_strings['aow_operator_list'][$key]);
                }//end of if
            }//end of foreach

            $app_list_strings['aow_operator_list'];

            if($view == 'vi_modulemappingeditview'){
                echo "<select type='text' name='$aowField' id='$aowField' title='' tabindex='116'>". get_select_options_with_id($app_list_strings['aow_operator_list'], $value) ."</select>";
            }//end of if
        }//end of if
        die;
    }//end of function
}//end of class
new VIEMSModuleOperatorField();
?>