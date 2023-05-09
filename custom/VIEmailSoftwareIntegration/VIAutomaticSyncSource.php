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
class VIAutomaticSyncSource{
	public function __construct(){
    	$this->viautomatic_sync_source();
    }//end of constructor
    
    public function viautomatic_sync_source(){
        $selectedSoftware = $_POST['sync_software'];
        $reocrdID = $_POST['reocrdID'];
        
        if($reocrdID != ""){
            $selectData = "SELECT * FROM vi_automatic_sync where sync_software = '$selectedSoftware' and deleted = 0 and vi_automatic_sync_id = '$reocrdID'";
            $selectResult = $GLOBALS['db']->fetchOne($selectData,false,'',false);
            $source = $selectResult['source'];

            if($selectedSoftware == "Mautic"){  
                if($source == "SuiteCRM"){    
                    echo "<option value = 'SuiteCRM' selected>SuiteCRM</option><option value = 'Mautic'>Mautic</option>";
                }else{
                    echo "<option value = 'SuiteCRM'>SuiteCRM</option><option value = 'Mautic' selected>Mautic</option>";
                }
            }elseif($selectedSoftware == "SendGrid"){
                if($source == "SuiteCRM"){    
                    echo "<option value = 'SuiteCRM' selected>SuiteCRM</option><option value = 'SendGrid'>SendGrid</option>";
                }else{
                    echo "<option value = 'SuiteCRM'>SuiteCRM</option><option value = 'SendGrid' selected>SendGrid</option>";
                }
            }
        }
    }//end of method
}//end of class
new VIAutomaticSyncSource();
?>