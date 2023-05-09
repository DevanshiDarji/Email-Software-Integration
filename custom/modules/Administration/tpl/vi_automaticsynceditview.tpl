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
<div class="moduleTitle">
  <h2 class="module-title-text">{$MOD.LBL_CAP_AUTO_SYNCHRONIZE}</h2>
  <div class="clear"></div>
</div>
<div class="clear"></div>
<form name="SyncEditView" id="SyncEditView">
    <table cellspacing="1" style="margin-top: 22px;">
        <tr>
            <td class='edit view' rowspan='2' width='100%'>
                <div id="wiz_message"></div>
                <div id=wizard class="wizard-unique-elem" style="width:1000px;">
                    <div id="step1" style="display:block;">
                        <div class="template-panel">
                            <div class="template-panel-container panel">
                                <div class="template-container-full">
                                    <table width="100%" border="0" cellspacing="10" cellpadding="0">
                                        <tbody>
                                            <tr rowspan="4">
                                                <td>
                                                    <b>{$MOD.LBL_SELECT_SOFTWARE}<span class="required">*</span><img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SELECT_EMAIL_SOFTWARE_AUTO_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td>
                                                    <select name="sync_software" id="sync_software">
                                                        <option value="" selected>{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        <option value="SendGrid" {if $SYNCSOFTWARE eq 'SendGrid'} selected = "selected" {/if}>{$MOD.LBL_SENDGRID}</option>
                                                        <option value="Mautic" {if $SYNCSOFTWARE eq 'Mautic'} selected = "selected" {/if}>{$MOD.LBL_MAUTIC}</option>
                                                        <option value="ConstantContact" {if $SYNCSOFTWARE eq 'ConstantContact'} selected = "selected" {/if}>{$MOD.LBL_CONSTANT_CONTACT}</option>
                                                        <option value="ActiveCampaigns" {if $SYNCSOFTWARE eq 'ActiveCampaigns'} selected = "selected" {/if}>{$MOD.LBL_ACTIVE_CAMPAIGNS}</option>
                                                        <option value="SendInBlue" {if $SYNCSOFTWARE eq 'SendInBlue'} selected = "selected" {/if}>{$MOD.LBL_SEND_IN_BLUE}</option>
                                                    </select><br><br>
                                                </td>
                                                <br>
                                            </tr>
                                            <tr rowspan="4">
                                                <td>
                                                    <b>{$MOD.LBL_LIST_MAPPING_MODULE}<span class="required">*</span><img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_LIST_MAPPING_MODULE_AUTO_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td>
                                                    <select name="mapping_modules[]" id="mapping_modules" multiple="true">
                                                    </select><br><br>
                                                </td>
                                            </tr>
                                            {if $RECORDID neq ''}
                                                <tr rowspan="4" id="autoSyncAction">
                                                    <td>
                                                        {if $SYNCSOFTWARE eq 'SendGrid'}
                                                            <b>{$MOD.LBL_SYNC_TO_SENDGRID}
                                                        {elseif $SYNCSOFTWARE eq 'ConstantContact'}
                                                            <b>{$MOD.LBL_SYNC_SUITECRM_TO_CONSTANT_CONTACT}
                                                        {elseif $SYNCSOFTWARE eq 'ActiveCampaigns'}
                                                            <b>{$MOD.LBL_SYNC_SUITECRM_TO_ACTIVE_CAMPAIGNS}
                                                        {elseif $SYNCSOFTWARE eq 'SendInBlue'}
                                                            <b>{$MOD.LBL_SYNC_SUITECRM_TO_SEND_IN_BLUE}
                                                        {else}
                                                            <b>{$MOD.LBL_SYNC_SUITECRM_TO_MAUTIC}
                                                        {/if}
                                                        <img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SYNC_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                    </td>
                                                    <td class="setvisibilityclass">
                                                        <label class="switch">
                                                        {if $SYNCTOES eq 1}
                                                        <input type="checkbox" name="sync_to_es" id="sync_to_es" size="30" value="" checked="">
                                                        {else}
                                                        <input type="checkbox" name="sync_to_es" id="sync_to_es" size="30" value="">
                                                        {/if}
                                                        <span class="slider round"></span></label><input type="hidden" value="" id="sync_to_es_switch" name="sync_to_es_switch">
                                                    </td>
                                                </tr>
                                            {else}
                                                <tr rowspan="4" id="switch_tr"></tr>
                                            {/if}

                                            {if $RECORDID neq ''}
                                                <tr id="autoSyncEMSToSuiteCRM">
                                                    <td>
                                                        {if $SYNCSOFTWARE eq 'SendGrid'}
                                                            <b>{$MOD.LBL_SYNC_SENDGRID_TO_SUITECRM}</b>
                                                        {elseif $SYNCSOFTWARE eq 'ConstantContact'}
                                                            <b>{$MOD.LBL_SYNC_CONSTANT_CONTACT_TO_SUITECRM}</b>
                                                        {elseif $SYNCSOFTWARE eq 'ActiveCampaigns'}
                                                            <b>{$MOD.LBL_SYNC_ACTIVE_CAMPAIGNS_TO_SUITECRM}</b>
                                                        {elseif $SYNCSOFTWARE eq 'SendInBlue'}
                                                            <b>{$MOD.LBL_SYNC_SEND_IN_BLUE_TO_SUITECRM}</b>
                                                        {else}
                                                            <b>{$MOD.LBL_AUTO_SYNC_MAUTIC}</b>
                                                        {/if}
                                                        <img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_EMS_TO_SUITECRM_INFO_MESSAGE}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0">
                                                    </td>
                                                    <td class="setvisibilityclass">
                                                        <label class="switch">
                                                            <input type="checkbox" name="sync_ems_to_suite" id="sync_ems_to_suite" size="30" value="{$SYNC_EMS_TO_SUITE}" {if $SYNC_EMS_TO_SUITE eq 1} checked{/if}><span class="slider round"></span>
                                                        </label>
                                                    </td>
                                                </tr>
                                            {else}
                                                <tr id="syncEMSToSuiteCRM">
                                                </tr>
                                            {/if}

                                            <tr {if $SYNCSOFTWARE neq 'Mautic' && $SYNCSOFTWARE neq 'ActiveCampaigns'} style="display: none;"{/if}>
                                                <td id="WebhookSyncLabel"><b>
                                                {if $SYNCSOFTWARE eq 'Mautic'}
                                                    {$MOD.LBL_AUTO_SYNC_MAUTIC}</b><img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_AUTO_SYNC_MAUTIC_INFO}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"><br><span style="color:#ff0000;">{$MOD.LBL_WEBHOOK_NOTE}</span>
                                                {else if $SYNCSOFTWARE eq 'ActiveCampaigns'}
                                                    {$MOD.LBL_SYNC_ACTIVE_CAMPAIGNS_TO_SUITECRM}</b><img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_AUTO_SYNC_ACTIVE_CAMPAIGNS_INFO}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"><br><span style="color:#ff0000;">{$MOD.LBL_WEBHOOK_NOTE}</span>
                                                {/if}
                                                </td>
                                                <td class="setvisibilityclass">
                                                    <label class="switch">
                                                        <input type="checkbox" name="auto_sync_ems" id="auto_sync_ems" size="30" value="{$AUTO_SYNC_EMS}" {if $AUTO_SYNC_EMS eq 1} checked{/if}><span class="slider round"></span>
                                                    </label>

                                                    <span id="WebhookSyncPreview">
                                                        {if $SYNCSOFTWARE eq 'Mautic'}
                                                            <img src="custom/modules/Administration/images/preview_icon.png" id="btn_preview" class="btn_preview" style="height: 40px;width: 40px;margin-top: -40px;"><img id="btn_preview" class="btn_preview" src="custom/modules/Administration/images/mautic_webhook.png" alt="Mautic Webhooks" style="width:10%;max-width:30px;display: none;">
                                                        {else if $SYNCSOFTWARE eq 'ActiveCampaigns'}
                                                            <img src="custom/modules/Administration/images/preview_icon.png" id="btnACPreview" class="btnACPreview" style="height: 40px;width: 40px;margin-top: -40px;"><img id="btnACPreview" class="btnACPreview" src="custom/modules/Administration/images/activeCampaignWebhook.jpg" alt="Active Campaigns Webhooks" style="width:10%;max-width:30px;display: none;">
                                                        {/if}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                        <input type="button" value="{$MOD.LBL_SAVE}" onclick="save_automatic_sync()">
                                        <input type="button" value="{$MOD.LBL_CANCEL}" onclick="cancel()">
                                    <br>
                                    <br>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div id="myModal" class="modal">
        <span class="close">x</span>
        <img class="modal-content" id="img01">
        <div id="caption"></div>
    </div>
</form>
{literal}
    <script type="text/javascript" src="custom/modules/Administration/js/VIEmailSoftwareIntegration.js"></script>
    <script type="text/javascript">
        var syncSoftware = $('#sync_software').val();
        var moduleMappingList = {/literal}{$SELMAPPINGMODULELIST|@json_encode}{literal};
        var convertModuleMappingListStringData = JSON.stringify(moduleMappingList);
        var dataObject = jQuery.parseJSON(convertModuleMappingListStringData);
        
        $.ajax({
            url: "index.php?entryPoint=VISyncFetchModuleMappingList",
            type: "post",
            data: {syncSoftware : syncSoftware },
            success: function (result) {
                $('#mapping_modules').empty();
                $('#mapping_modules').html(result);
                if(dataObject != null){
                    $.each(dataObject,function(index,value){
                        $('#mapping_modules').find("option[value="+value+"]").attr("selected",true);
                    });
                }//end of if
            }
        });
    </script>  
{/literal}