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
class VICheckBatchManagmentStatus{
    public function __construct(){
        $this->checkBatchManagmentStatus();
    }

    public function checkBatchManagmentStatus(){
    	parse_str($_POST['val'], $formData);
        $syncSoftware = $formData['sync_software'];
        $mappingModuleList = $formData['mapping_module_list'];
        $selData = "SELECT * FROM vi_module_mapping WHERE module_mapping_id = '$mappingModuleList' and email_software = '$syncSoftware' and deleted = 0";
        $selResult = $GLOBALS['db']->fetchOne($selData,false,'',false);

        $batchManagementStatus = $selResult['batch_management_status'];
        if($batchManagementStatus == '0'){
        	echo translate('LBL_BATCH_MANAGEMENT_STATUS_MSG','Administration');
        }
    }
}
new VICheckBatchManagmentStatus();
?>