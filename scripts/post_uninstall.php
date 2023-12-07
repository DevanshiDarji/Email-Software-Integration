<?php
 
global $db;
$db->dropTableName('vi_accounts_es');
$db->dropTableName('vi_api_configuration');
$db->dropTableName('vi_assets_es');
$db->dropTableName('vi_automatic_sync');
$db->dropTableName('vi_campaigns_es');
$db->dropTableName('vi_contacts_es');
$db->dropTableName('vi_email_fields');
$db->dropTableName('vi_ems_conditions');
$db->dropTableName('vi_ems_schedule_sync');
$db->dropTableName('vi_integration_contacts_field_mapping');
$db->dropTableName('vi_integration_field_mapping');
$db->dropTableName('vi_module_mapping');
$db->dropTableName('vi_segments_es');
$db->dropTableName('vi_synchronize');

$sqlEMS = "DELETE from config where name = 'email-software-integration'";
$result = $GLOBALS['db']->query($sqlEMS);

$sqlLicenseKey = "DELETE from config where name = 'lic_email-software-integration'";
$result2 = $GLOBALS['db']->query($sqlLicenseKey);
?>