<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
 

$module_name = 'VI_EmailSoftwareIntegartionSyncLog';
$viewdefs[$module_name]['QuickCreate'] = array(
    'templateMeta' => array(
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30')
        ),
    ),

    'panels' => array(
        'default' => array(

            array(
                'name',
                'assigned_user_name',
            ),
        ),

    ),
);