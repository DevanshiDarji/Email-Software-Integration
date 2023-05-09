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
class VIDeleteAutomaticSync{
	public function __construct(){
    	$this->deleteAutomaticSyncData();
    }//end of constructor
    
    public function deleteAutomaticSyncData(){
        if(isset($_POST['del_id'])){
            $delId = explode(',',$_POST['del_id']);
            foreach($delId as $id){
                //softdelete data
                $deleteData = "UPDATE vi_automatic_sync
                                SET deleted = '1'
                                WHERE vi_automatic_sync_id = '$id'";
                $deleteResult = $GLOBALS['db']->query($deleteData);
            }//end of foreach
        }//end of if
	}//end of method
}//end of class
new VIDeleteAutomaticSync();
?>