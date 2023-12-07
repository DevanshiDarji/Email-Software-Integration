<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
require_once("modules/AOW_WorkFlow/aow_utils.php");
require_once("VIEmailMarketingFunction.php");
class VISuiteCRMModuleFields{
    public function __construct(){
        $this->getFields();
    } 

    public function getFields(){
        $source_module = $_REQUEST['moduleName'];
        $stepName = $_REQUEST['stepName'];

        $editDetailViewFields = getEMSModuleFields($source_module, $stepName);
        echo $editDetailViewFields;
    }//end of function
}//end of class
new VISuiteCRMModuleFields();
?>