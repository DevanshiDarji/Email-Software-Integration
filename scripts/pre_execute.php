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
$now = date("Y-m-d");

//EntryPoint
if(is_dir('custom/VIESIntegration')) {
    $emailSoftwareIntegrationFolderName = 'VIESIntegration'.$now;
    rename("custom/VIESIntegration","custom/".$emailSoftwareIntegrationFolderName);
}

//custom/include
if(is_dir('custom/include/VIIntegration')) {
    $integrationFolderName = 'VIIntegration'.$now;
    rename("custom/include/VIIntegration","custom/include/".$integrationFolderName);
}

if(file_exists('custom/include/VIEsIntegrationConfig.php')) {
    $nowTo1checkFile = 'VIEsIntegrationConfig'.$now.'.'.'css';
    rename("custom/include/VIEsIntegrationConfig.php","custom/include/".$nowTo1checkFile);
}

//custom files:
if(file_exists('custom/Extension/application/Ext/Include/VI_EmailSoftwareIntegartionSyncLog.php')) {
    $now1checkFile = 'VI_EmailSoftwareIntegartionSyncLog'.$now.'.'.'php';
    rename("custom/Extension/application/Ext/Include/VI_EmailSoftwareIntegartionSyncLog.php","custom/Extension/application/Ext/Include/".$now1checkFile);
}
if(file_exists('custom/Extension/application/Ext/LogicHooks/viAutomaticSyncLogicHook.php')) {
    $now2checkFile = 'viAutomaticSyncLogicHook'.$now.'.'.'php';
    rename("custom/Extension/application/Ext/LogicHooks/viAutomaticSyncLogicHook.php","custom/Extension/application/Ext/LogicHooks/".$now2checkFile);
}
if(file_exists('custom/Extension/application/Ext/Language/en_us.VIIntegration.php')) {
    $now3checkFile = 'en_us.VIIntegration'.$now.'.'.'php';
    rename("custom/Extension/application/Ext/Language/en_us.VIIntegration.php","custom/Extension/application/Ext/Language/".$now3checkFile);
}

//tpl
if(file_exists('custom/modules/Administration/tpl/vi_apiconfigurationeditview.tpl')) {
    $nowApiConfigurationEditviewTpl = 'vi_apiconfigurationeditview'.$now.'.'.'tpl';
    rename("custom/modules/Administration/tpl/vi_apiconfigurationeditview.tpl","custom/modules/Administration/tpl/".$nowApiConfigurationEditviewTpl);
}
if(file_exists('custom/modules/Administration/tpl/vi_apiconfigurationlistview.tpl')) {
    $nowApiConfigurationListviewTpl = 'vi_apiconfigurationlistview'.$now.'.'.'tpl';
    rename("custom/modules/Administration/tpl/vi_apiconfigurationlistview.tpl","custom/modules/Administration/tpl/".$nowApiConfigurationListviewTpl);
}
if(file_exists('custom/modules/Administration/tpl/vi_automaticsynceditview.tpl')) {
    $nowAutomaticSyncEditviewTpl = 'vi_automaticsynceditview'.$now.'.'.'tpl';
    rename("custom/modules/Administration/tpl/vi_automaticsynceditview.tpl","custom/modules/Administration/tpl/".$nowAutomaticSyncEditviewTpl);
}
if(file_exists('custom/modules/Administration/tpl/vi_automaticsynclistview.tpl')) {
    $nowAutomaticSyncListviewTpl = 'vi_automaticsynclistview'.$now.'.'.'tpl';
    rename("custom/modules/Administration/tpl/vi_automaticsynclistview.tpl","custom/modules/Administration/tpl/".$nowAutomaticSyncListviewTpl);
}
if(file_exists('custom/modules/Administration/tpl/vi_integrationwidget.tpl')) {
    $nowInterationWidgetTpl = 'vi_integrationwidget'.$now.'.'.'tpl';
    rename("custom/modules/Administration/tpl/vi_integrationwidget.tpl","custom/modules/Administration/tpl/".$nowInterationWidgetTpl);
}
if(file_exists('custom/modules/Administration/tpl/vi_modulemappingeditview.tpl')) {
    $nowModuleMappingEditviewTpl = 'vi_modulemappingeditview'.$now.'.'.'tpl';
    rename("custom/modules/Administration/tpl/vi_modulemappingeditview.tpl","custom/modules/Administration/tpl/".$nowModuleMappingEditviewTpl);
}
if(file_exists('custom/modules/Administration/tpl/vi_modulemappinglistview.tpl')) {
    $nowModuleMappingListviewTpl = 'vi_modulemappinglistview'.$now.'.'.'tpl';
    rename("custom/modules/Administration/tpl/vi_modulemappinglistview.tpl","custom/modules/Administration/tpl/".$nowModuleMappingListviewTpl);
}
if(file_exists('custom/modules/Administration/tpl/vi_synchronizeeditview.tpl')) {
    $nowSynchronizeEditviewTpl = 'vi_synchronizeeditview'.$now.'.'.'tpl';
    rename("custom/modules/Administration/tpl/vi_synchronizeeditview.tpl","custom/modules/Administration/tpl/".$nowSynchronizeEditviewTpl);
}

//view
if(file_exists('custom/modules/Administration/views/view.vi_apiconfigurationeditview.php')) {
    $nowApiConfigurationEditview = 'view.vi_apiconfigurationeditview'.$now.'.'.'php';
    rename("custom/modules/Administration/views/view.vi_apiconfigurationeditview.php","custom/modules/Administration/views/".$nowApiConfigurationEditview);
}
if(file_exists('custom/modules/Administration/views/view.vi_apiconfigurationlistview.php')) {
    $nowApiConfigurationListview = 'view.vi_apiconfigurationlistview'.$now.'.'.'php';
    rename("custom/modules/Administration/views/view.vi_apiconfigurationlistview.php","custom/modules/Administration/views/".$nowApiConfigurationListview);
}
if(file_exists('custom/modules/Administration/views/view.vi_automaticsynceditview.php')) {
    $nowAutomaticSyncEditview = 'view.vi_automaticsynceditview'.$now.'.'.'php';
    rename("custom/modules/Administration/views/view.vi_automaticsynceditview.php","custom/modules/Administration/views/".$nowAutomaticSyncEditview);
}
if(file_exists('custom/modules/Administration/views/view.vi_automaticsynclistview.php')) {
    $nowAutomaticSyncListview = 'view.vi_automaticsynclistview'.$now.'.'.'php';
    rename("custom/modules/Administration/views/view.vi_automaticsynclistview.php","custom/modules/Administration/views/".$nowAutomaticSyncListview);
}
if(file_exists('custom/modules/Administration/views/view.vi_integrationwidget.php')) {
    $nowInterationWidget = 'view.vi_integrationwidget'.$now.'.'.'php';
    rename("custom/modules/Administration/views/view.vi_integrationwidget.php","custom/modules/Administration/views/".$nowInterationWidget);
}
if(file_exists('custom/modules/Administration/views/view.vi_modulemappingeditview.php')) {
    $nowModuleMappingEditview = 'view.vi_modulemappingeditview'.$now.'.'.'php';
    rename("custom/modules/Administration/views/view.vi_modulemappingeditview.php","custom/modules/Administration/views/".$nowModuleMappingEditview);
}
if(file_exists('custom/modules/Administration/views/view.vi_modulemappinglistview.php')) {
    $nowModuleMappingListviewTpl = 'view.vi_modulemappinglistview'.$now.'.'.'php';
    rename("custom/modules/Administration/views/view.vi_modulemappinglistview.php","custom/modules/Administration/views/".$nowModuleMappingListviewTpl);
}
if(file_exists('custom/modules/Administration/views/view.vi_synchronizeeditview.php')) {
    $nowSynchronizeEditview = 'view.vi_synchronizeeditview'.$now.'.'.'php';
    rename("custom/modules/Administration/views/view.vi_synchronizeeditview.php","custom/modules/Administration/views/".$nowSynchronizeEditview);
}

//js
if(file_exists('custom/modules/Administration/js/VIEmailSoftwareIntegration.js')) {
    $nowEmailSoftwareIntegrationJs = 'VIEmailSoftwareIntegration'.$now.'.'.'js';
    rename("custom/modules/Administration/js/VIEmailSoftwareIntegration.js","custom/modules/Administration/js/".$nowEmailSoftwareIntegrationJs);
}
if(file_exists('custom/modules/Administration/js/VIEMSConditionLine.js')) {
    $nowEMSConditionLineJs = 'VIEMSConditionLine'.$now.'.'.'js';
    rename("custom/modules/Administration/js/VIEMSConditionLine.js","custom/modules/Administration/js/".$nowEMSConditionLineJs);
}

//css
if(file_exists('custom/modules/Administration/css/VIIntegrationCss.css')) {
    $nowIntegrationCss = 'VIIntegrationCss'.$now.'.'.'css';
    rename("custom/modules/Administration/css/VIIntegrationCss.css","custom/modules/Administration/css/".$nowIntegrationCss);
}
if(file_exists('custom/modules/Administration/css/VIIntegrationWidget.css')) {
    $nowIntegrationWidgetCss = 'VIIntegrationWidget'.$now.'.'.'css';
    rename("custom/modules/Administration/css/VIIntegrationWidget.css","custom/modules/Administration/css/".$nowIntegrationWidgetCss);
}

//images
if(is_dir('custom/modules/Administration/images')) {
    $imageFolderName = 'images'.$now;
    rename("custom/modules/Administration/images","custom/modules/Administration/".$imageFolderName);
}

//suite8
if(file_exists('themes/suite8/images/software_integration.png')) {
    $nowSoftwareIntegrationIconPng = 'software_integration'.$now.'.'.'png';
    rename("themes/suite8/images/software_integration.png", "themes/suite8/images/".$nowSoftwareIntegrationIconPng);
}
if(file_exists('themes/suite8/images/software_integration.svg')) {
    $nowSoftwareIntegrationIconSvg = 'software_integration'.$now.'.'.'svg';
    rename("themes/suite8/images/software_integration.svg", "themes/suite8/images/".$nowSoftwareIntegrationIconSvg);
}

//default
if(file_exists('themes/default/images/software_integration.png')) {
    $nowSoftwareIntegrationIconPng = 'software_integration'.$now.'.'.'png';
    rename("themes/default/images/software_integration.png", "themes/default/images/".$nowSoftwareIntegrationIconPng);
}
if(file_exists('themes/default/images/software_integration.svg')) {
    $nowSoftwareIntegrationIconSvg = 'software_integration'.$now.'.'.'svg';
    rename("themes/default/images/software_integration.svg", "themes/default/images/".$nowSoftwareIntegrationIconSvg);
}
?>