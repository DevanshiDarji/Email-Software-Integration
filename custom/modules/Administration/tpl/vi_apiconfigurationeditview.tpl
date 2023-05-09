{*
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
*}

<html>
<head>
  <link rel="stylesheet" type="text/css" href="custom/modules/Administration/css/VIIntegrationCss.css">
</head>
<div class="clear"></div>
<div style="width: 100%">
    <h4 class="module-title-text">{$MOD.LBL_CAP_API_CONFIGURATION}<button class = "button" style="float: right;" onclick = "back()">{$MOD.LBL_BACK}</button></h4>
</div>
<table cellspacing="1" style="margin-top: 22px; width: 100%">
        <tr>
            <td class='edit view' rowspan='2' width='100%'>
                <div id="wiz_message"></div>
                <div id=wizard class="wizard-unique-elem" style="width:100%">
                    <div id="step1" style="display:block;">
                        <div class="template-panel">
                            <div class="template-panel-container panel">
                                <div class="template-container-full">
                                    <form name="EditView">
                                    <table width="100%" border="0" cellspacing="10" cellpadding="0" style="margin-top: 10px;">
                                        <tbody>
                                            <tr rowspan="4">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_TITLE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_TITLE_API_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <input type="text" name="title" id="title" value="{$TITLE}">
                                                </td>
                                            </tr>
                                            <tr rowspan="4">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_SELECT_SOFTWARE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SELECT_EMAIL_SOFTWARE}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <select name="software" id="software" {if $ID neq ''} disabled="true" {/if}>
                                                        <option value = "">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        <option value = "SendGrid" {if $EMAILSOFTWARE eq 'SendGrid'} selected = "selected" {/if}>{$MOD.LBL_SENDGRID}</option>
                                                        <option value = "Mautic" {if $EMAILSOFTWARE eq 'Mautic'} selected = "selected" {/if}>{$MOD.LBL_MAUTIC}</option>
                                                        <option value = "ConstantContact" {if $EMAILSOFTWARE eq 'ConstantContact'} selected = "selected" {/if}>{$MOD.LBL_CONSTANT_CONTACT}</option>
                                                        <option value = "ActiveCampaigns" {if $EMAILSOFTWARE eq 'ActiveCampaigns'} selected = "selected" {/if}>{$MOD.LBL_ACTIVE_CAMPAIGNS}</option>
                                                        <option value = "SendInBlue" {if $EMAILSOFTWARE eq 'SendInBlue'} selected = "selected" {/if}>{$MOD.LBL_SEND_IN_BLUE}</option>
                                                    </select>
                                                </td>
                                            </tr>

                                             <tr rowspan="4" style="display:none;" id="planType">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_PLAN_TYPE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SELECT_EMAIL_SOFTWARE}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <select name="planType" id="planType">
                                                        <option value = "">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        <option value = "1" {if $PLANTYPE eq 'New Marketing Campaigns'} selected = "selected" {/if}>{$MOD.LBL_NEW_MARKETING_CAMPAIGNS}</option>
                                                        <option value = "2" {if $PLANTYPE eq 'Legacy Marketing Campaigns'} selected = "selected" {/if}>{$MOD.LBL_LEGACY_MARKETING_CAMPAIGNS}</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table width="100%" border="0" cellspacing="10" cellpadding="0" id="tblapikey" style="display: none; margin-top: 50px">
                                        <tbody>
                                            <tr><th colspan="4"><h4 class="header-4">{$MOD.LBL_WHAT_IS_YOUR_SENDGRID_API_KEY}</h4></th></tr> 
                                            <tr>
                                                <td><b><span class="required">*</span>{$MOD.LBL_SENDGRID_API_KEY}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SENDGRID_API_KEY_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <input type="text" name="api_key" id="api_key" size="30" value="{$APIKEY}" style="width: 100%;">
                                                </td>
                                                <td>
                                                    <button type="button" class="button" name= "btn_save"  onclick = "save_apiconfiguration()" style="margin-left: 20px;">{$MOD.LBL_CONNECT_TO_SENDGRID}</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="text-align: center; background-color: lightgrey; width: 28%;">{$MOD.LBL_DONT_HAVE_A_SENDGRID_ACCOUNT_YET}<br><a href="https://sendgrid.com/pricing" target="_blank">{$MOD.LBL_CREATE_ONE_NOW}</a><br>{$MOD.LBL_NEED_HELP_LOCATING_YOUR_API_KEY}<br>{$MOD.LBL_CHECK_OUT} <a href="https://app.sendgrid.com/settings/api_keys" target="_blank">{$MOD.LBL_WHERE_I_FIND_MY_API_KEY}</a> {$MOD.LBL_ON_SENDGRID}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table width="100%" border="0" cellspacing="10" cellpadding="0" style="margin-top: 50px; display: none;" id="tblMauticApiKey">
                                        <tbody>
                                            <tr><th colspan="4"><h4 class="header-4">{$MOD.LBL_MAUTIC_CREDENTIALS}</h4></th></tr>
                                            <tr rowspan="4">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_MAUTIC_URL}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_MAUTIC_URL_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <input type="text" name="mautic_url" id="mautic_url" size="30" value="{if isset($APIKEY.mauticUrl)}{$APIKEY.mauticUrl}{/if}" style="width: 31%;">
                                                </td>
                                            </tr>
                                            <tr rowspan="4">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_MAUTIC_USERNAME}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_MAUTIC_USERNAME_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <input type="text" name="mautic_username" id="mautic_username" size="30" value="{if isset($APIKEY.mauticUsername)}{$APIKEY.mauticUsername}{/if}" style="width: 31%;">
                                                </td>
                                            </tr>
                                            <tr rowspan="4">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_MAUTIC_PASSWORD}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_MAUTIC_PASSWORD_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <input type="password" name="mautic_password" id="mautic_password" size="30" value="{if isset($APIKEY.mauticPassword)}{$APIKEY.mauticPassword}{/if}" style="width: 31%;">
                                                </td>
                                            </tr>
                                            <tr rowspan="4">
                                                <td></td>
                                                <td class="setvisibilityclass"><button type="button" class="button" name="btn_save" id="btn_save" onclick="save_apiconfiguration()">{$MOD.LBL_CONNECT_TO_MAUTIC}</button></td>
                                            </tr>

                                            <tr rowspan="4">
                                                <td></td>
                                                <td class="setvisibilityclass">
                                                    <label style="background-color: lightgrey; width: 31%;">{$MOD.LBL_DONT_HAVE_A_MAUTIC_ACCOUNT_YET}<br><a href="https://mautic.com/choose-your-path/" target="_blank"><center>{$MOD.LBL_CREATE_ONE_NOW}</center></a></label>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table width="100%" border="0" cellspacing="10" cellpadding="0" id="tblConstantContactsApiKey" style="display: none; margin-top: 50px">
                                        <tbody>
                                            <tr><th colspan="4"><h4 class="header-4">{$MOD.LBL_WHAT_IS_YOUR_CONSTANT_CONTACTS_API_KEY}</h4></th></tr> 
                                            <tr>
                                                <td><b><span class="required">*</span>{$MOD.LBL_CONSTANT_CONTACTS_API_KEY}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_CONSTANT_CONTACT_API_KEY}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                {if $RECORDID eq ''}
                                                    <input type="text" name="constant_contact_api_key" id="constant_contact_api_key" value="" style="width: 100%;">
                                                {else}
                                                    <input type="text" name="constant_contact_api_key" id="constant_contact_api_key" value="{if isset($APIKEY.constantContactApiKey)}{$APIKEY.constantContactApiKey}{/if}" style="width: 100%;">
                                                {/if}
                                                </td>
                                                <td>
                                                    <button type="button" class="button" name= "btn_save" id= "btn_save" onclick = "save_apiconfiguration()" style="margin-left: 20px;">{$MOD.LBL_CONNECT_TO_CONSTANT_CONTACTS}</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b><span class="required">*</span>{$MOD.LBL_CONSTANT_CONTACTS_ACCESS_TOKEN}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_CONSTANT_CONTACTS_ACCESS_TOKEN_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                {if $RECORDID eq ''}
                                                    <input type="text" name="constant_contact_access_token" id="constant_contact_access_token" value="" style="width: 100%;">
                                                {else}
                                                    <input type="text" name="constant_contact_access_token" id="constant_contact_access_token" value="{if isset($APIKEY.accessToken)}{$APIKEY.accessToken}{/if}" style="width: 100%;">
                                                {/if}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="text-align: center; background-color: lightgrey; width: 28%;">{$MOD.LBL_DONT_HAVE_A_CONSTANT_CONTACTS_ACCOUNT_YET}<br><a href="https://constantcontact.mashery.com/member/register" target="_blank">{$MOD.LBL_CREATE_ONE_NOW}</a><br></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table width="100%" border="0" cellspacing="10" cellpadding="0" style="margin-top: 50px; display: none;" id="tblActiveCampaignApiKey">
                                        <tbody>
                                            <tr><th colspan="4"><h4 class="header-4">{$MOD.LBL_ACTIVE_CAMPAIGNS_CREDENTIALS}</h4></th></tr> 
                                            <tr rowspan="4">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_ACTIVE_CAMPAIGNS_URL}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_ACTIVE_CAMPAIGNS_URL_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <input type="text" name="active_campaigns_url" id="active_campaigns_url" size="30" value="{if isset($APIKEY.activeCampaignsUrl)}{$APIKEY.activeCampaignsUrl}{/if}" style="width: 41%;">
                                                </td>
                                            </tr>
                                            <tr rowspan="4">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_ACTIVE_CAMPAIGNS_API_TOKEN}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_ACTIVE_CAMPAIGNS_API_TOKEN_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <input type="text" name="active_campaigns_api_token" id="active_campaigns_api_token" size="30" value="{if isset($APIKEY.activeCampaignsApiToken)}{$APIKEY.activeCampaignsApiToken}{/if}" style="width: 41%;">
                                                </td>
                                            </tr>
                                            <tr rowspan="4">
                                                <td></td>
                                                <td class="setvisibilityclass"><button type="button" class="button" name="btn_save" id="btn_save" onclick="save_apiconfiguration()">{$MOD.LBL_CONNECT_TO_ACTIVE_CAMPAIGNS}</button></td>
                                            </tr>

                                            <tr rowspan="4">
                                                <td></td>
                                                <td class="setvisibilityclass">
                                                    <label style="background-color: lightgrey; width: 41%;">{$MOD.LBL_DONT_HAVE_A_ACTIVE_CAMPAIGNS_ACCOUNT_YET}<br><a href="https://www.activecampaign.com" target="_blank"><center>{$MOD.LBL_CREATE_ONE_NOW}</center></a></label>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                     
                                    <table width="100%" border="0" cellspacing="10" cellpadding="0" id="tblSendInBlueApiKey" style="display: none; margin-top: 50px">
                                        <tbody>
                                            <tr><th colspan="4"><h4 class="header-4">{$MOD.LBL_WHAT_IS_YOUR_SEND_IN_BLUE_API_KEY}</h4></th></tr> 
                                            <tr>
                                                <td><b><span class="required">*</span>{$MOD.LBL_SEND_IN_BLUE_API_KEY}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SENDINBLUE_API_KEY}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <input type="text" name="send_in_blue_api_key" id="send_in_blue_api_key" size="30" value="{$APIKEY}" style="width: 100%;">
                                                </td>
                                                <td>
                                                    <button type="button" class="button" name= "btn_save" id= "btn_save" onclick = "save_apiconfiguration()" style="margin-left: 20px;">{$MOD.LBL_CONNECT_TO_SEND_IN_BLUE}</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="text-align: center; background-color: lightgrey; width: 28%;">{$MOD.LBL_DONT_HAVE_A_SEND_IN_BLUE_ACCOUNT_YET}<br><a href="https://app.sendinblue.com/account/register/" target="_blank">{$MOD.LBL_CREATE_ONE_NOW}</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</html> 
{literal}
<script type="text/javascript" src="custom/modules/Administration/js/VIEmailSoftwareIntegration.js"></script> 
<script type="text/javascript">
    $(document).ready(function (){
        var software = "{/literal}{$EMAILSOFTWARE}{literal}";
        var planType = "{/literal}{$PLANTYPE}{literal}";
        if(software == "SendGrid"){
            $('#planType').show();
            $('#planType').find("option[value='"+planType+"']").attr("selected",true);
        }else{
            $('#planType').hide();
        }
    });
    function save_apiconfiguration(){
        var MendtoryFieldsAlert = SUGAR.language.get("app_strings",'LBL_MENDTORY_FIELDS');
        var selectEsAlert = SUGAR.language.get("app_strings","LBL_SELECT_ES");
        var software = $("#software").val();
        var title = $('#title').val();
        var formData = $('form');
        var disabled = formData.find(':disabled').removeAttr('disabled');
        var mod = {/literal}{$MOD|@json_encode}{literal};   
        var mb = messageBox();
        mb.setBody('<div class="email-in-progress"><img src="themes/' + SUGAR.themes.theme_name + '/images/loading.gif"></div>');
        mb.show();
        mb.hideHeader();
        mb.hideFooter();
        formData = formData.serialize();
        var id = "{/literal}{$RECORDID}{literal}";
        if(software == ""){
            alert(selectEsAlert);
        }else{
            flag = 0;
            if(software == "SendGrid"){
                api_key = $('#api_key').val();
                planType = $('select[id="planType"]').val();
                if(api_key != '' && title != '' && planType != ''){
                    flag = 1;
                }
            }else if(software == "ConstantContact"){
                api_key = $('#constant_contact_api_key').val();
                token = $('#constant_contact_access_token').val();
                if(api_key != '' && token != '' && title != ''){
                    flag = 1;
                }
            }else if(software == "Mautic"){
                mauticURL = $('#mautic_url').val();
                mauticUsername = $('#mautic_username').val();
                mauticPassword = $('#mautic_password').val();
                if(mauticURL != '' && mauticUsername != '' && mauticPassword != '' && title != ''){
                    flag = 1;
                }
            }else if(software == "SendInBlue"){
                api_key = $('#send_in_blue_api_key').val();

                if(api_key != '' && title != ''){
                    flag = 1;
                }
            }else if(software == "ActiveCampaigns"){
                activeCampaignURL = $('#active_campaigns_url').val();
                activeCampaignToken = $('#active_campaigns_api_token').val();

                if(activeCampaignURL != '' && activeCampaignToken != '' && title != ''){
                    flag = 1;
                }
            }
            if(flag == 1){
                var editView = "{/literal}{$EDITVIEW}{literal}";
                $.ajax({
                    url: "index.php?entryPoint=VIAddApiConfiguration",
                    type: "post",
                    data: { val : formData,
                            id :  id,
                            mod : mod},
                    success: function (response) {
                        mb.remove();
                        var obj = jQuery.parseJSON (response);
                        if(obj != "" && obj.code == 1){
                            alert(mod.LBL_API_ADD_MESSAGE);
                            window.location.href = editView;
                        }else if(obj != "" && obj.code == 2){
                            alert(mod.LBL_API_NOT_ADD_MESSAGE);
                            window.location.href = editView;
                        }else if(obj != "" && obj.code == 3){
                            alert(mod.LBL_API_UPDATE_MESSAGE);
                            window.location.href = editView;
                        }else if(obj != "" && obj.code == 4){
                            alert(mod.LBL_API_NOT_UPDATE_MESSAGE);
                            window.location.href = editView;
                        }else{
                            alert(mod.LBL_API_VALID);
                            return false;
                        }
                    }
                });
            }else{
                mb.remove();
                alert(MendtoryFieldsAlert);
            }
        }
    }
</script>
{/literal}