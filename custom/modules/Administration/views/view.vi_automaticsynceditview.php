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
require_once('include/MVC/View/SugarView.php');
class viewvi_automaticsynceditview extends SugarView {
    public function __construct() {
        parent::init();
    }
    public function display() {
        global $theme,$mod_strings;
        if(isset($_REQUEST['records'])){
            $recordId = $_REQUEST['records'];   
        }else{
            $recordId = "";
        }
        $smarty = new Sugar_Smarty();
        if($recordId != ""){
            $selData = "SELECT * FROM vi_automatic_sync WHERE vi_automatic_sync_id = '$recordId' and deleted = 0";
            $row = $GLOBALS['db']->fetchOne($selData,false,'',false);

            if(!empty($row)){
                $syncSoftware = $row['sync_software'];
                $selMappingModuleList = explode(",",$row['sel_mapping_module_list']);
                $syncToES = $row['sync_to_es'];
                $autoSync = $row['auto_sync_ems'];
                $syncEMSToSuite = $row['sync_ems_to_suite'];

                $smarty->assign("SYNCSOFTWARE",$syncSoftware);
                $smarty->assign("SYNCTOES",$syncToES);
                $smarty->assign("AUTO_SYNC_EMS",$autoSync);
                $smarty->assign("SYNC_EMS_TO_SUITE",$syncEMSToSuite);
                $smarty->assign("SELMAPPINGMODULELIST",$selMappingModuleList);
            }//end of if
        }

        $smarty->assign("theme",$theme);
        $smarty->assign("MOD",$mod_strings);
        $smarty->assign("RECORDID",$recordId);
        $smarty->display('custom/modules/Administration/tpl/vi_automaticsynceditview.tpl');
        parent::display();
    }//end of display
}//end of class
?>