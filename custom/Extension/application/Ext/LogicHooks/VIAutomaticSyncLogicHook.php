<?php
 
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