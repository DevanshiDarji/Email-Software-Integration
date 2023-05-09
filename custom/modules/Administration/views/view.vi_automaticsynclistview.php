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
class viewvi_automaticsynclistview extends SugarView {
    public function __construct() {
        parent::init();
    }
    public function display() {
        global $mod_strings;
        $editViewUrl = "index.php?module=Administration&action=vi_automaticsynceditview";
        $finalAutomaticSyncData = array();
        $selAutomaticSync = "SELECT * FROM vi_automatic_sync where deleted = 0";
        $selAutomaticSyncResult = $GLOBALS['db']->query($selAutomaticSync);
        $selRecordFetchRow = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($selAutomaticSync));
        $recordCount = 0;
        if(!empty($selRecordFetchRow)){
            while($row=$GLOBALS['db']->fetchByAssoc($selAutomaticSyncResult)){
                $moduleMappings = explode(",", $row['sel_mapping_module_list']);
                $moduleMappingVal = array();
                foreach ($moduleMappings as $key => $value) {
                    $selData = "SELECT * FROM vi_module_mapping WHERE module_mapping_id = '$value' and status= 'Active' and deleted = 0";
                    $selDataRow = $GLOBALS['db']->fetchOne($selData);
                    if(!empty($selDataRow)){
                        $moduleMappingVal[] = $selDataRow['title'];
                    }//end of if
                }
                $finalAutomaticSyncData[] = array("vi_automatic_sync_id"=>$row['vi_automatic_sync_id'],"sync_software"=>$row['sync_software'],"moduleMappingVal"=>$moduleMappingVal, 'status' => $row['sync_to_es'], 'autoSyncEMSToSuite' => $row['sync_ems_to_suite']);
                $recordCount++;
            }
        }

        $widgetUrl = "index.php?module=Administration&action=vi_integrationwidget";
        $smarty = new Sugar_Smarty();
        $smarty->assign('FINALAUTOMATICSYNCDATA',$finalAutomaticSyncData);
        $smarty->assign("EDITVIEWURL",$editViewUrl);
        $smarty->assign("MOD",$mod_strings);
        $smarty->assign('NUMBEROFROWS',$recordCount);
        $smarty->assign('WIDGETURL',$widgetUrl);
        $smarty->display('custom/modules/Administration/tpl/vi_automaticsynclistview.tpl');
        parent::display();
    }//end of display
}//end of class
?>