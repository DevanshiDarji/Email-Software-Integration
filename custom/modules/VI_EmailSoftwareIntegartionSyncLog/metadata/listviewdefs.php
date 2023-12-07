<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
$module_name = 'VI_EmailSoftwareIntegartionSyncLog';
$listViewDefs [$module_name] = 
array (
  'EMAIL_SOFTWARE' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_EMAIL_SOFTWARE',
    'width' => '10%',
    'default' => true,
  ),
  'NAME' => 
  array (
    'width' => '15%',
    'label' => 'LBL_NAME',
    'default' => true,
    'link' => true,
  ),
  'TO_MODULE' => 
  array (
    'width' => '15%',
    'label' => 'LBL_TO_MODULE',
    'default' => true,
    'link' => true,
  ),
  'FROM_RECORD' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_FROM_RECORD',
    'width' => '10%',
    'default' => true,
  ),
  'VIEM_TO_RECORD' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_TO_RECORD',
    'width' => '10%',
    'default' => true,
  ),
  'SYNC_TYPE' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_SYNC_TYPE',
    'width' => '10%',
    'default' => true,
  ),
  'ACTION_TYPE' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_ACTION_TYPE',
    'width' => '10%',
    'default' => true,
  ),
  'STATUS' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_STATUS',
    'width' => '10%',
    'default' => true,
  ),
  'VIEM_MESSAGE_C' => 
  array (
    'type' => 'varchar',
    'label' => 'LBL_MESSAGE',
    'width' => '10%',
    'default' => true,
  ),
  'DATE_ENTERED' => 
  array (
    'type' => 'datetime',
    'label' => 'LBL_DATE_ENTERED',
    'width' => '10%',
    'default' => true,
  ),
);
;
?>
