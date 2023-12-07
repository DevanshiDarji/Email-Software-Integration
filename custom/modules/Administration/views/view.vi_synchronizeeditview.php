<?php
 
require_once('include/MVC/View/SugarView.php');
class viewvi_synchronizeeditview extends SugarView {
    public function __construct() {
        parent::init();
    }
    public function display() {
        global $theme,$mod_strings;
        
        $smarty = new Sugar_Smarty();       
        $smarty->assign("theme",$theme);
        $smarty->assign("MOD",$mod_strings);
        $smarty->display('custom/modules/Administration/tpl/vi_synchronizeeditview.tpl');
        parent::display();
    }//end of display
}//end of class
?>