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
class Viewvi_apiconfigurationeditview extends SugarView {
    public function __construct() {
        parent::init();
    }
    public function display() {
        if(isset($_REQUEST['records'])){
            $recordId = $_REQUEST['records'];   
        }else{
            $recordId = "";
        }
        global $mod_strings;
        $smarty = new Sugar_Smarty();
        if($recordId != ""){
            $selData = "SELECT * FROM vi_api_configuration WHERE id = '$recordId'";
            $row = $GLOBALS['db']->fetchOne($selData,false,'',false);
            $emailSoftware = $row['email_software'];
            $title = $row['title'];
            $planType = $row['plan_type'];
            $smarty->assign('ID',$recordId);
            $smarty->assign('EMAILSOFTWARE',$emailSoftware);
            $smarty->assign('TITLE',$title);
            $smarty->assign('PLANTYPE',$planType);
            if($emailSoftware == "SendGrid" || $emailSoftware == "SendInBlue"){
                $apiKey = $row['api_key'];
            }elseif($emailSoftware == "Mautic" || $emailSoftware == "ConstantContact" || $emailSoftware == "ActiveCampaigns"){
                $key = $row['api_key'];
                $apiKey = (array)json_decode(html_entity_decode($key));
            }
            $smarty->assign('APIKEY',$apiKey);
        }
        $editView = "index.php?module=Administration&action=vi_apiconfigurationlistview";
        $smarty->assign("MOD",$mod_strings);
        $smarty->assign('RECORDID',$recordId);
        $smarty->assign('EDITVIEW',$editView);
        $smarty->display('custom/modules/Administration/tpl/vi_apiconfigurationeditview.tpl');
        parent::display();
    }//end of display
}//end of class
?>