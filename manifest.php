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
$manifest = array (
  0 => 
  array (
    'acceptable_sugar_versions' => 
    array (
      0 => '',
    ),
  ),
  1 => 
  array (
    'acceptable_sugar_flavors' => 
    array (
      0 => 'CE',
      1 => 'PRO',
      2 => 'ENT',
    ),
  ),
  'readme' => '',
  'key' => '',
  'author' => 'Variance Infotech PVT. LTD',
  'description' => 'Email Software Integration Plugin',
  'icon' => '',
  'is_uninstallable' => true,
  'name' => 'VIEmailSoftwareIntegration',
  'published_date' => '2022-11-10 13:30:44',
  'type' => 'module',
  'version' => 'v12.0',
  'remove_tables' => 'prompt',
);

$installdefs = array (
  'id' => 'VIEmailIntegration',
  'beans' => 
        array (
            array (
              'module' => 'VI_EmailSoftwareIntegartionSyncLog',
              'class' => 'VI_EmailSoftwareIntegartionSyncLog',
              'path' => 'modules/VI_EmailSoftwareIntegartionSyncLog/VI_EmailSoftwareIntegartionSyncLog.php',
              'tab' => true,
            ),
            array (
              'module' => 'VIEmailSoftwareIntegrationLicenseAddon',
              'class' => 'VIEmailSoftwareIntegrationLicenseAddon',
              'path' => 'modules/VIEmailSoftwareIntegrationLicenseAddon/VIEmailSoftwareIntegrationLicenseAddon.php',
              'tab' => false,
            ),
        ), 
  'post_execute' => array(  0 =>  '<basepath>/scripts/post_execute.php',),
  'post_install' => array(  0 =>  '<basepath>/scripts/post_install.php',),
  'post_uninstall' => array(  0 =>  '<basepath>/scripts/post_uninstall.php',),
  'pre_execute' => array( 0 =>  '<basepath>/scripts/pre_execute.php',),
  'copy' => 
    array (
      0 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/EntryPointRegistry/IntegrationEntryPoint.php',
        'to' => 'custom/Extension/application/Ext/EntryPointRegistry/IntegrationEntryPoint.php',
      ),
      1 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Include/VI_EmailSoftwareIntegartionSyncLog.php',
        'to' => 'custom/Extension/application/Ext/Include/VI_EmailSoftwareIntegartionSyncLog.php',
      ),
      2 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/en_us.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/en_us.VIIntegration.php',
      ),
      3 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/de_DE.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/de_DE.VIIntegration.php',
      ),
      4 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/es_ES.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/es_ES.VIIntegration.php',
      ),
      5 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/fr_FR.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/fr_FR.VIIntegration.php',
      ),
      6 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/hu_HU.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/hu_HU.VIIntegration.php',
      ),
      7 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/it_IT.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/it_IT.VIIntegration.php',
      ),
      8 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/nl_NL.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/nl_NL.VIIntegration.php',
      ),
      9 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/pt_BR.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/pt_BR.VIIntegration.php',
      ),
      10 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/ru_RU.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/ru_RU.VIIntegration.php',
      ),
      11 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/Language/ua_UA.VIIntegration.php',
        'to' => 'custom/Extension/application/Ext/Language/ua_UA.VIIntegration.php',
      ),
      12 => 
      array (
        'from' => '<basepath>/custom/Extension/application/Ext/LogicHooks/VIAutomaticSyncLogicHook.php',
        'to' => 'custom/Extension/application/Ext/LogicHooks/VIAutomaticSyncLogicHook.php',
      ),
      13 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/ActionViewMap/VIIntegrationAction_View_Map.ext.php',
        'to' => 'custom/Extension/modules/Administration/Ext/ActionViewMap/VIIntegrationAction_View_Map.ext.php',
      ),
      14 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Administration/VIIntegrationAdministration.ext.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Administration/VIIntegrationAdministration.ext.php',
      ),
      15 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.en_us.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.en_us.lang.php',
      ),
      16 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.de_DE.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.de_DE.lang.php',
      ),
      17 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.es_ES.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.es_ES.lang.php',
      ),
      18 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.fr_FR.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.fr_FR.lang.php',
      ),
      19 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.hu_HU.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.hu_HU.lang.php',
      ),
      20 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.it_IT.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.it_IT.lang.php',
      ),
      21 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.nl_NL.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.nl_NL.lang.php',
      ),
      22 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.pt_BR.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.pt_BR.lang.php',
      ),
      23 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.ru_RU.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.ru_RU.lang.php',
      ),
      24 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/Administration/Ext/Language/VIIntegration.ua_UA.lang.php',
        'to' => 'custom/Extension/modules/Administration/Ext/Language/VIIntegration.ua_UA.lang.php',
      ),
      25 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/VI_EmailSoftwareIntegartionSyncLog/Ext/Vardefs/sugarfield_viem_message_c.php',
        'to' => 'custom/Extension/modules/VI_EmailSoftwareIntegartionSyncLog/Ext/Vardefs/sugarfield_viem_message_c.php',
      ),
      26 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/VI_EmailSoftwareIntegartionSyncLog/Ext/Vardefs/sugarfield_viem_to_record.php',
        'to' => 'custom/Extension/modules/VI_EmailSoftwareIntegartionSyncLog/Ext/Vardefs/sugarfield_viem_to_record.php',
      ),
      27 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/VI_EmailSoftwareIntegartionSyncLog/Ext/Language/VIIntegrationLog.en_us.lang.php',
        'to' => 'custom/Extension/modules/VI_EmailSoftwareIntegartionSyncLog/Ext/Language/VIIntegrationLog.en_us.lang.php',
      ),
      28 => 
      array (
        'from' => '<basepath>/custom/Extension/modules/VIEmailSoftwareIntegrationLicenseAddon/',
        'to' => 'custom/Extension/modules/VIEmailSoftwareIntegrationLicenseAddon/',
      ),
      29 =>
      array (
        'from' => '<basepath>/custom/Extension/modules/Schedulers/Ext/Language/en_us.ems.php',
        'to' => 'custom/Extension/modules/Schedulers/Ext/Language/en_us.ems.php',
      ),
      30 =>
      array (
        'from' => '<basepath>/custom/Extension/modules/Schedulers/Ext/ScheduledTasks/EMS.php',
        'to' => 'custom/Extension/modules/Schedulers/Ext/ScheduledTasks/EMS.php',
      ),
      31 =>
      array (
        'from' => '<basepath>/custom/Extension/modules/Schedulers/Ext/Language/en_us.emsToSuiteCRM.php',
        'to' => 'custom/Extension/modules/Schedulers/Ext/Language/en_us.emsToSuiteCRM.php',
      ),
      32 =>
      array (
        'from' => '<basepath>/custom/Extension/modules/Schedulers/Ext/ScheduledTasks/EmsToSuiteCRM.php',
        'to' => 'custom/Extension/modules/Schedulers/Ext/ScheduledTasks/EmsToSuiteCRM.php',
      ),
      33 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/css/VIIntegrationCss.css',
        'to' => 'custom/modules/Administration/css/VIIntegrationCss.css',
      ),
      34 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/css/VIIntegrationWidget.css',
        'to' => 'custom/modules/Administration/css/VIIntegrationWidget.css',
      ),
      35 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/images/api_configuration_icon.png',
        'to' => 'custom/modules/Administration/images/api_configuration_icon.png',
      ),
      36 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/images/automatic_syncronize.png',
        'to' => 'custom/modules/Administration/images/automatic_syncronize.png',
      ),
      37 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/images/modueling_icon.png',
        'to' => 'custom/modules/Administration/images/modueling_icon.png',
      ),
      38 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/images/synchronize_icon.png',
        'to' => 'custom/modules/Administration/images/synchronize_icon.png',
      ),
      39 =>
      array (
        'from' => '<basepath>/custom/modules/Administration/images/preview_icon.png',
        'to' => 'custom/modules/Administration/images/preview_icon.png',
      ),
      40 =>
      array (
        'from' => '<basepath>/custom/modules/Administration/images/mautic_webhook.png',
        'to' => 'custom/modules/Administration/images/mautic_webhook.png',
      ),
      41 =>
      array (
        'from' => '<basepath>/custom/modules/Administration/images/activeCampaignWebhook.jpg',
        'to' => 'custom/modules/Administration/images/activeCampaignWebhook.jpg',
      ),
      42 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/js/VIEmailSoftwareIntegration.js',
        'to' => 'custom/modules/Administration/js/VIEmailSoftwareIntegration.js',
      ),
      43 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/js/VIEMSConditionLine.js',
        'to' => 'custom/modules/Administration/js/VIEMSConditionLine.js',
      ),
      44 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/tpl/vi_apiconfigurationeditview.tpl',
        'to' => 'custom/modules/Administration/tpl/vi_apiconfigurationeditview.tpl',
      ),
      45 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/tpl/vi_apiconfigurationlistview.tpl',
        'to' => 'custom/modules/Administration/tpl/vi_apiconfigurationlistview.tpl',
      ),
      46 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/tpl/vi_automaticsynceditview.tpl',
        'to' => 'custom/modules/Administration/tpl/vi_automaticsynceditview.tpl',
      ),
      47 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/tpl/vi_automaticsynclistview.tpl',
        'to' => 'custom/modules/Administration/tpl/vi_automaticsynclistview.tpl',
      ),
      48 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/tpl/vi_integrationwidget.tpl',
        'to' => 'custom/modules/Administration/tpl/vi_integrationwidget.tpl',
      ),
      49 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/tpl/vi_modulemappingeditview.tpl',
        'to' => 'custom/modules/Administration/tpl/vi_modulemappingeditview.tpl',
      ),
      50 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/tpl/vi_modulemappinglistview.tpl',
        'to' => 'custom/modules/Administration/tpl/vi_modulemappinglistview.tpl',
      ),
      51 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/tpl/vi_synchronizeeditview.tpl',
        'to' => 'custom/modules/Administration/tpl/vi_synchronizeeditview.tpl',
      ),
      52 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/views/view.vi_apiconfigurationeditview.php',
        'to' => 'custom/modules/Administration/views/view.vi_apiconfigurationeditview.php',
      ),
      53 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/views/view.vi_apiconfigurationlistview.php',
        'to' => 'custom/modules/Administration/views/view.vi_apiconfigurationlistview.php',
      ),
      54 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/views/view.vi_automaticsynceditview.php',
        'to' => 'custom/modules/Administration/views/view.vi_automaticsynceditview.php',
      ),
      55 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/views/view.vi_automaticsynclistview.php',
        'to' => 'custom/modules/Administration/views/view.vi_automaticsynclistview.php',
      ),
      56 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/views/view.vi_integrationwidget.php',
        'to' => 'custom/modules/Administration/views/view.vi_integrationwidget.php',
      ),
      57 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/views/view.vi_modulemappingeditview.php',
        'to' => 'custom/modules/Administration/views/view.vi_modulemappingeditview.php',
      ),
      58 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/views/view.vi_modulemappinglistview.php',
        'to' => 'custom/modules/Administration/views/view.vi_modulemappinglistview.php',
      ),
      59 => 
      array (
        'from' => '<basepath>/custom/modules/Administration/views/view.vi_synchronizeeditview.php',
        'to' => 'custom/modules/Administration/views/view.vi_synchronizeeditview.php',
      ),
      60 => 
      array (
        'from' => '<basepath>/custom/modules/VI_EmailSoftwareIntegartionSyncLog/metadata/detailviewdefs.php',
        'to' => 'custom/modules/VI_EmailSoftwareIntegartionSyncLog/metadata/detailviewdefs.php',
      ),
      61 => 
      array (
        'from' => '<basepath>/custom/modules/VI_EmailSoftwareIntegartionSyncLog/metadata/editviewdefs.php',
        'to' => 'custom/modules/VI_EmailSoftwareIntegartionSyncLog/metadata/editviewdefs.php',
      ),
      62 => 
      array (
        'from' => '<basepath>/custom/modules/VI_EmailSoftwareIntegartionSyncLog/metadata/listviewdefs.php',
        'to' => 'custom/modules/VI_EmailSoftwareIntegartionSyncLog/metadata/listviewdefs.php',
      ),
      63 => 
      array (
        'from' => '<basepath>/custom/modules/VI_EmailSoftwareIntegartionSyncLog/logic_hooks.php',
        'to' => 'custom/modules/VI_EmailSoftwareIntegartionSyncLog/logic_hooks.php',
      ),
      64 => 
      array (
        'from' => '<basepath>/custom/modules/VI_EmailSoftwareIntegartionSyncLog/logic_hooks_class.php',
        'to' => 'custom/modules/VI_EmailSoftwareIntegartionSyncLog/logic_hooks_class.php',
      ),
      65 => 
      array (
        'from' => '<basepath>/custom/modules/VIEmailSoftwareIntegrationLicenseAddon/',
        'to' => 'custom/modules/VIEmailSoftwareIntegrationLicenseAddon/',
      ),
      66 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIAddApiConfiguration.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIAddApiConfiguration.php',
      ),
      67 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIAddAutomaticSync.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIAddAutomaticSync.php',
      ),
      68 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIAddModuleMapping.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIAddModuleMapping.php',
      ),
      69 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIAddSynchronizeData.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIAddSynchronizeData.php',
      ),
      70 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIAddSynchronizeDataES.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIAddSynchronizeDataES.php',
      ),
      71 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIAutomaticSyncSource.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIAutomaticSyncSource.php',
      ),
      72 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VICheckApiConfiguration.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VICheckApiConfiguration.php',
      ),
      73 =>
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VICheckBatchManagmentStatus.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VICheckBatchManagmentStatus.php',
      ),
      74 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VICheckESAPIConfiguration.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VICheckESAPIConfiguration.php',
      ),
      75 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VICheckSelectedModules.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VICheckSelectedModules.php',
      ),
      76 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIDeleteApiConfiguration.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIDeleteApiConfiguration.php',
      ),
      77 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIDeleteAutomaticSync.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIDeleteAutomaticSync.php',
      ),
      78 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIDeleteModuleMapping.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIDeleteModuleMapping.php',
      ),
      79 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIEmailMarketingFunction.php',
      ),
      80 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIEMSFieldTypeOptions.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIEMSFieldTypeOptions.php',
      ),
      81 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIEMSModuleFieldType.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIEMSModuleFieldType.php',
      ),
      82 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIEMSModuleOperatorField.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIEMSModuleOperatorField.php',
      ),
      83 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIEMSModuleRelationships.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIEMSModuleRelationships.php',
      ),
      84 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIESModuleFields.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIESModuleFields.php',
      ),
      85 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VIIntegrationHook.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VIIntegrationHook.php',
      ),
      86 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VISuiteCRMModuleFields.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VISuiteCRMModuleFields.php',
      ),
      87 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VISyncESFields.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VISyncESFields.php',
      ),
      88 => 
      array (
        'from' => '<basepath>/custom/VIEmailSoftwareIntegration/VISyncFetchModuleMappingList.php',
        'to' => 'custom/VIEmailSoftwareIntegration/VISyncFetchModuleMappingList.php',
      ),
      89 => 
      array (
        'from' => '<basepath>/custom/include/VIEsIntegrationConfig.php',
        'to' => 'custom/include/VIEsIntegrationConfig.php',
      ),
      90 => 
      array (
        'from' => '<basepath>/custom/include/VIIntegration/VIIntegraionLogicHook.php',
        'to' => 'custom/include/VIIntegration/VIIntegraionLogicHook.php',
      ),
      91 => 
      array (
        'from' => '<basepath>/custom/include/VIIntegration/VIIntegrationIcon.css',
        'to' => 'custom/include/VIIntegration/VIIntegrationIcon.css',
      ),
      92 => 
      array (
        'from' => '<basepath>/modules/VI_EmailSoftwareIntegartionSyncLog/Dashlets/VI_EmailSoftwareIntegartionSyncLogDashlet/VI_EmailSoftwareIntegartionSyncLogDashlet.meta.php',
        'to' => 'modules/VI_EmailSoftwareIntegartionSyncLog/Dashlets/VI_EmailSoftwareIntegartionSyncLogDashlet/VI_EmailSoftwareIntegartionSyncLogDashlet.meta.php',
      ),
      93 => 
      array (
        'from' => '<basepath>/modules/VI_EmailSoftwareIntegartionSyncLog/language/en_us.lang.php',
        'to' => 'modules/VI_EmailSoftwareIntegartionSyncLog/language/en_us.lang.php',
      ),
      94 => 
      array (
        'from' => '<basepath>/modules/VI_EmailSoftwareIntegartionSyncLog/metadata',
        'to' => 'modules/VI_EmailSoftwareIntegartionSyncLog/metadata',
      ),
      95 => 
      array (
        'from' => '<basepath>/modules/VI_EmailSoftwareIntegartionSyncLog/Menu.php',
        'to' => 'modules/VI_EmailSoftwareIntegartionSyncLog/Menu.php',
      ),
      96 => 
      array (
        'from' => '<basepath>/modules/VI_EmailSoftwareIntegartionSyncLog/vardefs.php',
        'to' => 'modules/VI_EmailSoftwareIntegartionSyncLog/vardefs.php',
      ),
      97 => 
      array (
        'from' => '<basepath>/modules/VI_EmailSoftwareIntegartionSyncLog/VI_EmailSoftwareIntegartionSyncLog.php',
        'to' => 'modules/VI_EmailSoftwareIntegartionSyncLog/VI_EmailSoftwareIntegartionSyncLog.php',
      ),
      98 => 
      array (
        'from' => '<basepath>/modules/VIEmailSoftwareIntegrationLicenseAddon/',
        'to' => 'modules/VIEmailSoftwareIntegrationLicenseAddon/',
      ),
      99 => 
      array (
        'from' => '<basepath>/custom/application/Ext/Include/modules.ext.php',
        'to' => 'custom/application/Ext/Include/modules.ext.php',
      ),
      100 =>
      array (
        'from' => '<basepath>/custom/application/Ext/Language/en_us.lang.ext.php',
        'to' => 'custom/application/Ext/Language/en_us.lang.ext.php',
      ),      
      101 =>
      array (
        'from' => '<basepath>/MauticWebhook.php',
        'to' => 'MauticWebhook.php',
      ),
      102 =>
      array (
        'from' => '<basepath>/activeCampaignWebhook.php',
        'to' => 'activeCampaignWebhook.php',
      ),
      103 => 
      array (
        'from' => '<basepath>/themes/suite8/images/software_integration.png',
        'to' => 'themes/suite8/images/software_integration.png',
      ),
      104 => 
      array (
        'from' => '<basepath>/themes/suite8/images/software_integration.svg',
        'to' => 'themes/suite8/images/software_integration.svg',
      ),
      105 => 
      array (
        'from' => '<basepath>/themes/suite8/images/software_integration.png',
        'to' => 'themes/default/images/software_integration.png',
      ),
      106 => 
      array (
        'from' => '<basepath>/themes/suite8/images/software_integration.svg',
        'to' => 'themes/default/images/software_integration.svg',
      ),
    ),
);
?>