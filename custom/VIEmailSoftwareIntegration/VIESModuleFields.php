<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * This file is part of package Integration.
 * 
 * Author : Variance InfoTech PVT LTD (http://www.varianceinfotech.com)
 * All rights (c) 2022 by Variance InfoTech PVT LTD
 *
 * This Version of Integration is licensed software and may only be used in 
 * alignment with the License Agreement received with this Software.
 * This Software is copyrighted and may not be further distributed without
 * written consent of Variance InfoTech PVT LTD
 * 
 * You can contact via email at info@varianceinfotech.com
 * 
 ********************************************************************************/
class VIESModuleFields{
    public function __construct(){
        $this->getModuleFields();
    }
    public function getModuleFields(){
        $es = $_POST['moduleMappingSoftware'];
        $module = $_POST['module'];
        $sql = "SELECT * FROM vi_email_fields where email_software = '$es' and module = '$module'";
        $result = $GLOBALS['db']->query($sql);
        $fields = "";
        while($row = $GLOBALS['db']->fetchByAssoc($result) ){
            $fields = html_entity_decode($row['fields']);
        }
        $combinedarray = json_decode($fields);
        if(!empty($combinedarray)){
            foreach ($combinedarray as $key => $value) {
                $name = $value;
                echo "<option>".$name."</option>";
            }
        }else{
            echo 1;    
        }
    }//end of function
}//end of class
new VIESModuleFields();
?>