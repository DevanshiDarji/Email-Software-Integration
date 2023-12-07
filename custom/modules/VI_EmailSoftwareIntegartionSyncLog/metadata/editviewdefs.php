<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 
$module_name = 'VI_EmailSoftwareIntegartionSyncLog';
$viewdefs [$module_name] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
      'tabDefs' => 
      array (
        'DEFAULT' => 
        array (
          'newTab' => false,
          'panelDefault' => 'expanded',
        ),
      ),
      'syncDetailEditViews' => true,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'email_software',
            'label' => 'LBL_EMAIL_SOFTWARE',
          ),
          1 => 'name',
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'from_record',
            'label' => 'LBL_FROM_RECORD',
          ),
          1 => 
          array (
            'name' => 'sync_type',
            'label' => 'LBL_SYNC_TYPE',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'action_type',
            'label' => 'LBL_ACTION_TYPE',
          ),
          1 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'date_entered',
            'comment' => 'Date record created',
            'label' => 'LBL_DATE_ENTERED',
          ),
          1 => 
          array (
            'name' => 'viem_message_c',
            'comment' => 'Text Field Comment Text',
            'label' => 'vimessage',
          ),
        ),
      ),
    ),
  ),
);
;
?>
