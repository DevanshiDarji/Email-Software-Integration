<?php
 
require_once('include/MVC/View/SugarView.php');
class Viewvi_modulemappinglistview extends SugarView {
    public function __construct() {
        parent::init();
    }
    public function display() {
        global $theme,$mod_strings;
        $smarty = new Sugar_Smarty();
        $editViewUrl = "index.php?module=Administration&action=vi_modulemappingeditview";
        $widgetUrl = "index.php?module=Administration&action=vi_integrationwidget";
        $listViewUrl = "index.php?module=Administration&action=vi_modulemappinglistview";
        $finalModuleMappingData = array();
        $selModuleMapping = "SELECT * FROM vi_module_mapping where deleted = 0";
        $selModuleMappingResult = $GLOBALS['db']->query($selModuleMapping);
        $selRecordFetchRow = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($selModuleMapping));
        $recordCount = 0;
        if(!empty($selRecordFetchRow)){
            while($row=$GLOBALS['db']->fetchByAssoc($selModuleMappingResult)){
                $finalModuleMappingData[] = array("module_mapping_id"=>$row['module_mapping_id'],"title"=>$row['title'],"email_software"=>$row['email_software'],"status"=>$row['status']);
                $recordCount++;
            }
        }
        
        $smarty->assign("EDITVIEWURL",$editViewUrl);
        $smarty->assign("WIDGETURL",$widgetUrl);
        $smarty->assign("LISTVIEWURL",$listViewUrl);
        $smarty->assign("MOD",$mod_strings);
        $smarty->assign('NUMBEROFROWS',$recordCount);
        $smarty->assign('FINALMODULEMAPPINGDATA',$finalModuleMappingData);
        $smarty->assign("THEME",$theme);
        $smarty->display('custom/modules/Administration/tpl/vi_modulemappinglistview.tpl');
        parent::display();
    }//end of display
}//end of class
?>