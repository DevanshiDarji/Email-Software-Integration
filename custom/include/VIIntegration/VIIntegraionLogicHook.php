<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
class VIIntegraionLogicHook{
    public function VIIntegration($event,$arguments){
        if($GLOBALS['app']->controller->action == 'index' && $_REQUEST['module'] == 'Administration'){
            echo '<link rel="stylesheet" type="text/css" href="custom/include/VIIntegration/VIIntegrationIcon.css">';
        }//end of if
    }//end of function
}//end of class
?>

