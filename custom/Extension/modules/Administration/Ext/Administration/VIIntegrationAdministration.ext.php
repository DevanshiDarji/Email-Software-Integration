<?php
/*********************************************************************************
 * This file is part of package Email Software Integartion.
 * 
 * Author : Variance InfoTech PVT LTD (http://www.varianceinfotech.com)
 * All rights (c) 2022 by Variance InfoTech PVT LTD
 *
 * This Version of Email Software Integartion is licensed software and may only be used in 
 * alignment with the License Agreement received with this Software.
 * This Software is copyrighted and may not be further distributed without
 * written consent of Variance InfoTech PVT LTD
 * 
 * You can contact via email at info@varianceinfotech.com
 * 
 ********************************************************************************/
require_once('modules/VIEmailSoftwareIntegrationLicenseAddon/license/VIESIntegrationOutfittersLicense.php');
require_once('include/MVC/Controller/SugarController.php');
global $sugar_config;
global $theme;
$dynamicURL = $sugar_config['site_url'];
$url = $dynamicURL."/index.php?module=VIEmailSoftwareIntegrationLicenseAddon&action=license";
$sqlLicenseCheck = "SELECT * from config where name = 'lic_email-software-integration'";
$result = $GLOBALS['db']->query($sqlLicenseCheck);
$selectResultData = $GLOBALS['db']->fetchRow($GLOBALS['db']->query($sqlLicenseCheck));
if(!empty($selectResultData)){
    $validate_license = VIESIntegrationOutfittersLicense::isValid('VIEmailSoftwareIntegrationLicenseAddon');
    if($validate_license !== true) {
        if(is_admin($current_user)) {
            SugarApplication::appendErrorMessage('VIEmailSoftwareIntegrationLicenseAddon is no longer active due to the following reason: '.$validate_license.' Users will have limited to no access until the issue has been addressed <a href='.$url.'>Click Here</a>');
        }
            echo '<h2><p class="error">VIEmailSoftwareIntegrationLicenseAddon is no longer active</p></h2><p class="error">Please renew your subscription or check your license configuration.</p><a href='.$url.'>Click Here</a>';
    }else{
        foreach ($admin_group_header as $key => $value) {
            $values[] = $value[0];
        }   
        if (in_array("Other", $values)){
                $array['VIIntegration'] = array('software_integration',
                                                          $mod_strings["LBL_INTEGRATION"],
                                                          $mod_strings["LBL_INTEGRATION_DESCRIPTION"],
                                                          './index.php?module=Administration&action=vi_integrationwidget',
                                                          'software_integration');
                $admin_group_header['Other'][3]['Administration'] = array_merge($admin_group_header['Other'][3]['Administration'],$array);
        }else{
            $admin_option_defs = array();
            $admin_option_defs['Administration']['VIIntegration'] = array(
                //Icon name. Available icons are located in ./themes/default/images
                'software_integration',

                //Link name label 
                $mod_strings["LBL_INTEGRATION"],

                //Link description label
                $mod_strings["LBL_INTEGRATION_DESCRIPTION"],

                //Link URL
                './index.php?module=Administration&action=vi_integrationwidget',
                'software_integration',
            );
            $admin_group_header['Other'] = array(
                //Section header label
                'Other',

                //$other_text parameter for get_form_header()
                '',

                //$show_help parameter for get_form_header()
                false,

                //Section links
                $admin_option_defs,

                //Section description label
                ''
            );
        }   
    }
}else{
    foreach ($admin_group_header as $key => $value) {
        $values[] = $value[0];
    }
    if (in_array("Other", $values))
    {
        $array['VIIntegration'] = array('software_integration',$mod_strings["LBL_INTEGRATION"],
                                                          $mod_strings["LBL_INTEGRATION_DESCRIPTION"],
                                                          './index.php?module=VIEmailSoftwareIntegrationLicenseAddon&action=license',
                                                          'software_integration');
        $admin_group_header['Other'][3]['Administration'] = array_merge($admin_group_header['Other'][3]['Administration'],$array);
    }else{
        $admin_option_defs = array();
        $admin_option_defs['Administration']['VIIntegration'] = array(
            //Icon name. Available icons are located in ./themes/default/images
            'software_integration',

            //Link name label 
            $mod_strings["LBL_INTEGRATION"],

            //Link description label
            $mod_strings["LBL_INTEGRATION_DESCRIPTION"],

            //Link URL
            './index.php?module=VIEmailSoftwareIntegrationLicenseAddon&action=license',
            'software_integration'
        );
        $admin_group_header['Other'] = array(
            //Section header label
            'Other',

            //$other_text parameter for get_form_header()
            '',

            //$show_help parameter for get_form_header()
            false,

            //Section links
            $admin_option_defs,

            //Section description label
            ''
        );
    }
}
?>