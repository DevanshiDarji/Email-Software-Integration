<?php
 
require_once('include/MVC/View/SugarView.php');
class Viewvi_apiconfigurationlistview extends SugarView {
    public function __construct() {
        parent::init();
    }
    public function display() {
        global $theme,$mod_strings;
        $finalConfigData = array();
        $selApiConfiguration = "SELECT * FROM vi_api_configuration where deleted = 0";
        $selApiConfigurationResult = $GLOBALS['db']->query($selApiConfiguration);
        $selRecordFetchRow = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($selApiConfiguration));
        $recordCount = 0;
        if(!empty($selRecordFetchRow)){
            while($row=$GLOBALS['db']->fetchByAssoc($selApiConfigurationResult)){
                $finalConfigData[] = array("id"=>$row['id'],"title"=>$row['title'],"email_software"=>$row['email_software']);
                $recordCount++;
            }
        }
        $widgetUrl = "index.php?module=Administration&action=vi_integrationwidget";
        $editViewUrl = "index.php?module=Administration&action=vi_apiconfigurationeditview";
        $listViewUrl = "index.php?module=Administration&action=vi_apiconfigurationlistview";
        $smarty = new Sugar_Smarty();
        $smarty->assign('NUMBEROFROWS',$recordCount);
        $smarty->assign("LISTVIEWURL",$listViewUrl);
        $smarty->assign("EDITVIEWURL",$editViewUrl);
        $smarty->assign("WIDGETURL",$widgetUrl);
        $smarty->assign('FINALCONFIGDATA',$finalConfigData);
        $smarty->assign("THEME",$theme);
        $smarty->assign("MOD",$mod_strings);
        $smarty->display('custom/modules/Administration/tpl/vi_apiconfigurationlistview.tpl');
        parent::display();
    }//end of display
}//end of class
?>