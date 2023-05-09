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
class VISyncFetchModuleMappingList{
    public function __construct(){
        $this->getModuleMapping();
    }
    public function getModuleMapping(){
        $syncSoftware = $_POST['syncSoftware'];
        $sql = "SELECT * FROM vi_module_mapping Where email_software = '$syncSoftware' and status='Active' and deleted = 0";
        $result = $GLOBALS['db']->query($sql);
        $moduleMappings = array();
        while($row = $GLOBALS['db']->fetchByAssoc($result)){
            $moduleMappings[] = array('title'=>$row['title'],'id'=>$row['module_mapping_id']);
        }
        if(!empty($moduleMappings)){
            foreach ($moduleMappings as $key => $value) {
                $title = $value['title'];
                $id = $value['id'];
                echo "<option value=".$id." >".$title."</option>";
            }
        }
    }//end of function
}//end of class
new VISyncFetchModuleMappingList();
?>