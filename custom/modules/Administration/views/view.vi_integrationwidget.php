<?php
 
require_once("custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php");
require_once('include/MVC/View/SugarView.php');
class Viewvi_integrationwidget extends SugarView {
    public function __construct() {
        parent::init();
    }
    public function display() {
        global $mod_strings;
        $configlistView = "index.php?module=Administration&action=vi_apiconfigurationlistview";
        $moduleMappinglistView = "index.php?module=Administration&action=vi_modulemappinglistview";
        $syncEditView = "index.php?module=Administration&action=vi_synchronizeeditview";
        $autoListView = "index.php?module=Administration&action=vi_automaticsynclistview";

        $url = "https://suitehelp.varianceinfotech.com";
        $helpBoxContent = getEMSHelpBoxHtml($url);

        $smarty = new Sugar_Smarty();
        $smarty->assign("MOD",$mod_strings);
        $smarty->assign("CONFIGLISTVIEW",$configlistView);
        $smarty->assign("MODULEMAPPINGLISTVIEW",$moduleMappinglistView);
        $smarty->assign("SYNCEDITVIEW",$syncEditView);
        $smarty->assign("AUTOLISTVIEW",$autoListView);
        $smarty->assign('HELP_BOX_CONTENT',$helpBoxContent);
        $smarty->display('custom/modules/Administration/tpl/vi_integrationwidget.tpl');
        parent::display();
    }//end of display
}//end of class
?>