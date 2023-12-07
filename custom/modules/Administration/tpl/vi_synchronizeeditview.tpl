{*
 
*}
<html>
<head>
  <link rel="stylesheet" type="text/css" href="custom/modules/Administration/css/VIIntegrationCss.css">
</head>
<div class="moduleTitle">
  <h2 class="module-title-text">{$MOD.LBL_CAP_SYNCHRONIZE}</h2>
  <div class="clear"></div>
</div>
<button class = "button" style="float: right; margin-top: -37px; margin-right: 10px;" onclick = "backToSync()">{$MOD.LBL_BACK}</button>

<table cellspacing="1" style="margin-top: 22px; width: 100%"> 
    <tr>
        <td class='edit view' rowspan='2' width='100%'>
            <div id="wiz_message"></div>
            <div id=wizard class="wizard-unique-elem" style="width:100%;">
                <div id="step1" style="display:block;">
                    <div class="template-panel">
                        <div class="template-panel-container panel">
                            <div class="template-container-full">
                                <form name="sync_to_suite">
                                    <table width="100%" border="0" cellspacing="10" cellpadding="0" style=" margin-top: 10px;">
                                        <tbody>
                                            <tr rowspan="4">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_SELECT_SOFTWARE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SELECT_EMAIL_SOFTWARE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td>
                                                    <select name="sync_software" id="sync_software">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        <option value="SendGrid">{$MOD.LBL_SENDGRID}</option>
                                                        <option value="Mautic">{$MOD.LBL_MAUTIC}</option>
                                                        <option value="ConstantContact">{$MOD.LBL_CONSTANT_CONTACT}</option>
                                                        <option value="ActiveCampaigns">{$MOD.LBL_ACTIVE_CAMPAIGNS}</option>
                                                        <option value="SendInBlue">{$MOD.LBL_SEND_IN_BLUE}</option>
                                                    </select><br><br>
                                                </td>
                                                <br>
                                            </tr>
                                            <tr id="mapping_module_list" rowspan="4" style="display: none">
                                                <td>
                                                    <b><span class="required">*</span>{$MOD.LBL_LIST_MAPPING_MODULE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_LIST_MAPPING_MODULE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b>
                                                </td>
                                                <td>
                                                    <select name="mapping_module_list" id="sel_mapping_module_list"></select><br><br>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="syncBtn">
                                        <button type="button" class="button dynamic_sync_btn" name= "" id="" onclick = "save_sync_sc2em()" style="display: none"></button>
                                        <button type="button" class="button dynamic_sync_btn_em" name= "" id="" onclick = "save_sync_em2sc()" style="display: none"></button>
                                    </div>
                                </form>
                                <br><br>
                                <br><br>
                                <div id="div_summary" class="col-md-6 text-center" style="width: auto; display: none">
                                    <table id="qboSummary" class="table  listview-table  floatThead-table">
                                        <thead></thead>
                                        <tbody id="sendgrid_summary"></tbody>
                                    </table>
                                </div>
                                <div id="div_summary_suite" class="row form-group" style="margin-left: 10px; display: none">
                                    <div class="col-md-6 text-center" style="width: auto;">
                                        <table id="vtigerSummary" class="table  listview-table  floatThead-table">
                                            <thead>
                                                <tr>
                                                    <th colspan="5" style="text-align: center;">{$MOD.LBL_SUITECRM}</th>
                                                </tr>
                                                <tr>
                                                    <th>{$MOD.LBL_DATE}</th>
                                                    <th>{$MOD.LBL_MODULE_MAPPING}</th>
                                                    <th>{$MOD.LBL_CREATED}</th>
                                                    <th>{$MOD.LBL_UPDATED}</th>
                                                    <th>{$MOD.LBL_FAILED}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="suitecrm_summary"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>
{literal}
<script type="text/javascript" src="custom/modules/Administration/js/VIEmailSoftwareIntegration.js"></script>
<script type="text/javascript">
    var alertRequiredFields = SUGAR.language.get("app_strings","LBL_REQUIRED_FIELD");
    function save_sync_em2sc(){
        var formData = $('form');
        formData = formData.serialize();
        var syncSoftware = $('#sync_software').val();
        var selMappingModuleList = $('#sel_mapping_module_list').val();
        if(syncSoftware == "" || selMappingModuleList == ""){
            alert(alertRequiredFields);
        }else{
            var mb = messageBox();
            mb.setBody('<div class="email-in-progress"><img src="themes/' + SUGAR.themes.theme_name + '/images/loading.gif"></div>');
            mb.show();
            mb.hideHeader();
            mb.hideFooter();
            $.ajax({
                url: "index.php?entryPoint=VIAddSynchronizeData",
                type: "post",
                data: { val : formData},
                success: function (response) {
                    mb.remove();
                    $('#div_report').show();
                    $('#div_summary').show();
                    $('#div_summary_suite').show();
                    $('#suitecrm_summary').append(response);
                }
            });
        }
    }//end of function

    function save_sync_sc2em(){
        var formData = $('form');
        var disabled = formData.find(':disabled').removeAttr('disabled');
        formData = formData.serialize();
        var syncSoftware = $('#sync_software').val();
        var selMappingModuleList = $('#sel_mapping_module_list').val();

        $('#qboSummary > thead').empty();
        $('#sendgrid_summary').empty();

        if(syncSoftware == "" || selMappingModuleList == ""){
            alert(alertRequiredFields);
        }else{
            var mb = messageBox();
            mb.setBody('<div class="email-in-progress"><img src="themes/' + SUGAR.themes.theme_name + '/images/loading.gif"></div>');
            mb.show();
            mb.hideHeader();
            mb.hideFooter();
            var date = SUGAR.language.get("app_strings","LBL_DATE");
            var moduleMapping = SUGAR.language.get("app_strings","LBL_MODULE_MAPPING");
            var created = SUGAR.language.get("app_strings","LBL_CREATED");
            var updated = SUGAR.language.get("app_strings","LBL_UPDATED");
            var failed = SUGAR.language.get("app_strings","LBL_FAILED");
            $.ajax({
                url: "index.php?entryPoint=VICheckBatchManagmentStatus",
                type: "post",
                data: { val : formData},
                success: function (response) {
                    flag = 0;
                    if(response != ''){
                        response = response.replace(/<br>/gi, "\n");
                        var confirmMsg = confirm(response);
                        if(confirmMsg == true){
                            flag = 1;
                        }else{
                            window.location.href = "index.php?module=Administration&action=vi_modulemappingeditview&records="+selMappingModuleList;
                        }
                    }else{
                        flag = 1;
                    }
                    if(flag == 1){
                        $.ajax({
                            url: "index.php?entryPoint=VIAddSynchronizeDataES",
                            type: "post",
                            data: { val : formData},
                            dataType: "JSON",
                            success: function (response) {
                                mb.remove();
                                if(response['backgroundProcessmsg'] != ''){
                                    alert(response['backgroundProcessmsg']);
                                }else{
                                    $('#div_report').show();
                                    $('#div_summary').show();
                                    $('#div_summary_suite').show();
                                    $('#qboSummary > thead').append('<tr><th colspan="5" style="text-align: center;">'+syncSoftware+'</th></tr><tr><th>'+date+'</th><th>'+moduleMapping+'</th><th>'+created+'</th><th>'+updated+'</th><th>'+failed+'</th></tr>');
                                    $('#sendgrid_summary').append(response['content']);
                                }
                            }
                        }); 
                    }
                }
            });
               
        }
    }//end of function
</script>
{/literal}