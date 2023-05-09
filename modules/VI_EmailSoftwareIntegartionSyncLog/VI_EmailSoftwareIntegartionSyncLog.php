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

class VI_EmailSoftwareIntegartionSyncLog extends Basic {
    public $new_schema = true;
    public $module_dir = 'VI_EmailSoftwareIntegartionSyncLog';
    public $object_name = 'VI_EmailSoftwareIntegartionSyncLog';
    public $table_name = 'vi_emailsoftwareintegrationsynclog';
    public $importable = true;
    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $SecurityGroups;
    public $from_record;
    public $sync_type;
    public $action_type;
    public $status;
    public $email_software;	
    public function bean_implements($interface){
        switch($interface){
            case 'ACL':
                return true;
        }
        return false;
    }	
}