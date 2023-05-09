{*
/*********************************************************************************
 * This file is part of package Integration.
 * 
 * Author : Variance InfoTech PVT LTD (http://www.varianceinfotech.com)
 * All rights (c) 2022 by Variance InfoTech PVT LTD
 *
 * This Version of Integration is licensed software and may only be used in 
 * alignment with the License Agreement received with this Software.
 * This Software is copyrighted and may not be further distributed without
 * written consent of Variance InfoTech PVT LTD
 * 
 * You can contact via email at info@varianceinfotech.com
 * 
 ********************************************************************************/
*}
<html>
<body>
    <!-- Suitecrm box design start -->
    {$HELP_BOX_CONTENT}
    <!-- Suitecrm box design end -->
<head>
    <link rel="stylesheet" type="text/css" href="custom/modules/Administration/css/VIIntegrationWidget.css">
</head>
<div class="main-bg-D">
    <div class="vx_xcd_td">
        <h2 class="name_title_xcd">{$MOD.LBL_CAP_INTEGRATION}<a href="index.php?module=VIEmailSoftwareIntegrationLicenseAddon&action=license" id="updateLicense"><button class="button">{$MOD.LBL_UPDATE_LICENSE}</button></a></h2>
        <div class="row">
            <div class="col-md-6 col-sm-12 col-xs-12 col-lg-3 box-marg_d">
                <div class="main_div">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="image_xs" style="text-align: center; border-right: 1px solid #fff;">
                                <img style="padding:6px;" src="custom/modules/Administration/images/api_configuration_icon.png" alt="API Configuration" height="50" width="50">
                            </div>
                        </div>
                        <div class="col-md-8 name_vx">
                            <h4 class="name_full" style="font-size: 15px !important; text-align: center;">
                            <a href="{$CONFIGLISTVIEW}" style="text-decoration-line:underline;">{$MOD.LBL_API}<br>{$MOD.LBL_CONFIG}</a></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12 col-lg-3 box-marg_d">
                <div class="main_div">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="image_xs" style="text-align: center; border-right: 1px solid #fff;">
                                <img style="padding:6px;" src="custom/modules/Administration/images/modueling_icon.png" alt="Module Mapping" height="50" width="50">
                            </div>
                        </div>
                        <div class="col-md-8 name_vx">
                            <h4 class="name_full" style="font-size: 15px !important; text-align: center;">
                            <a href="{$MODULEMAPPINGLISTVIEW}" style="text-decoration-line:underline;">{$MOD.LBL_MODULE__MAIN}<br>{$MOD.LBL_MAPPING_MAIN}</a></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12 col-lg-3 box-marg_d">
                <div class="main_div">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="image_xs" style="text-align: center; border-right: 1px solid #fff;">
                                <img style="padding:6px;" src="custom/modules/Administration/images/synchronize_icon.png" alt="Synchronize" height="50" width="50">
                            </div>
                        </div>
                        <div class="col-md-8 name_vx">
                            <h4 class="name_full" style="font-size: 15px !important;text-align: center;">
                            <a href="{$SYNCEDITVIEW}" style="text-decoration-line:underline;">{$MOD.LBL_SYNCHRONIZE}</a></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12 col-lg-3 box-marg_d">
                <div class="main_div">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="image_xs" style="text-align: center; border-right: 1px solid #fff;">
                                <img style="padding:6px;" src="custom/modules/Administration/images/automatic_syncronize.png" alt="Automatic Synchronize" height="50" width="50">
                            </div>
                        </div>
                        <div class="col-md-8 name_vx">
                            <h4 class="name_full" style="font-size: 15px !important;text-align: center;">
                            <a href="{$AUTOLISTVIEW}" style="text-decoration-line:underline;">{$MOD.LBL_AUTOMATIC}<br>{$MOD.LBL_SYNCHRONIZE}</a></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>