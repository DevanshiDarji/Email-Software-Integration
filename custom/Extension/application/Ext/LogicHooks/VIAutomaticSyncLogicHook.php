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
$hook_array['after_save'][] = Array(
  1, 
  'Custom Logic', 
  'custom/VIEmailSoftwareIntegration/VIIntegrationHook.php', 
  'viIntegration_hook', 
  'viafter_save_method'
);

$hook_array['after_ui_frame'][] = array(
  1, //Hook version
  'VIIntegration',  //Label
  'custom/include/VIIntegration/VIIntegraionLogicHook.php', //Include file
  'VIIntegraionLogicHook', //Class
  'VIIntegration' //Method
);
?>