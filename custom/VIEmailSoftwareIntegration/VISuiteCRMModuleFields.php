<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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