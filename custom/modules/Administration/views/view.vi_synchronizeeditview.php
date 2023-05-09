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