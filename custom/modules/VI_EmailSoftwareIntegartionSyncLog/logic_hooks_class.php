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
class logic_hooks_class
{
    function after_ui_frame_method($event, $arguments)
    {
    	if($GLOBALS['app']->controller->action == "listview"){
    		echo "<script type='text/javascript'>$('.suitepicon-action-edit').hide();</script>";
    	}

    	if($GLOBALS['app']->controller->action == "DetailView"){
    		echo "<script type='text/javascript'>$('#edit_button').hide();</script>";
    	}
    }
}
?>