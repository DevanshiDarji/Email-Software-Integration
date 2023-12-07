<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
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