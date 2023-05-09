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
$hook_version = 1;
$hook_array = Array();
$hook_array['after_ui_frame'] = Array();
$hook_array['after_ui_frame'][] = Array(
    //Processing index. For sorting the array.
    1, 

    //Label. A string value to identify the hook.
    'after_ui_frame example', 

    //The PHP file where your class is located.
    'custom/modules/VI_EmailSoftwareIntegartionSyncLog/logic_hooks_class.php', 

    //The class the method is in.
    'logic_hooks_class', 

    //The method to call.
    'after_ui_frame_method' 
);
?>