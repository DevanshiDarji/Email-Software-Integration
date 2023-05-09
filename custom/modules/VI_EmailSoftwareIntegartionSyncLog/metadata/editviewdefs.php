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
