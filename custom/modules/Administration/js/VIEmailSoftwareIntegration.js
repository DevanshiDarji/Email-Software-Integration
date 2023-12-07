 
$('#no_of_records').closest('tr').hide();
$('#batch_record').closest('tr').hide();
if($('#batch_management_status').val() == '1'){
	$('#no_of_records').closest('tr').show();
	$('#batch_record').closest('tr').show();
}
var decodeUrl = decodeURIComponent(window.location.href);
var selectOptionAlert = SUGAR.language.get("app_strings",'LBL_SELECT_AN_OPTION');
var selectOtherPairAlert = SUGAR.language.get("app_strings",'LBL_SELECT_OTHER_PAIR');
var configSetAlert = SUGAR.language.get("app_strings",'LBL_CONFIG_SET');
var reqFieldsAlert = SUGAR.language.get("app_strings",'LBL_REQUIRED_FIELD');
var selOtherFieldAlert = SUGAR.language.get("app_strings",'LBL_SELECT_OTHER_FIELD');
var synchronizeAlert = SUGAR.language.get("app_strings",'LBL_SYNCHRONIZE');
var fieldsFirstAlert = SUGAR.language.get("app_strings",'LBL_FIELDS_FIRST');
var fieldMapping = SUGAR.language.get("app_strings",'LBL_FIELD_MAPPING');
var suiteCRM = SUGAR.language.get("app_strings","LBL_SUITECRM");
var fields = SUGAR.language.get("app_strings","LBL_FIELDS");
var sendGrid = SUGAR.language.get("app_strings","LBL_SENDGRID");
var mautic = SUGAR.language.get("app_strings","LBL_MAUTIC");
var constantContacts = SUGAR.language.get("app_strings","LBL_CONSTANT_CONTACT");
var activeCampaigns = SUGAR.language.get("app_strings","LBL_ACTIVE_CAMPAIGNS");
var deleteAlert = SUGAR.language.get("app_strings",'LBL_DELETE_ALERT');
var rowAlert = SUGAR.language.get("app_strings",'LBL_ROW');
var selectRecordAlert = SUGAR.language.get("app_strings",'LBL_SELECT_RECORDS');
var sendInBlue = SUGAR.language.get("app_strings","LBL_SENDINBLUE");
var apiConfigurationMsg = SUGAR.language.get('Administration','LBL_API_CONFIGURATION_VALIDATION');
var thisLable = SUGAR.language.get("Administration",'LBL_THIS');
var theseLable = SUGAR.language.get('Administration','LBL_THESE');

function getParam( name ){
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec(decodeUrl);
	if( results == null )
	return "";
	else
	return results[1];
}
var recordId = getParam('records');
$('#sync_software').on('change',function(){	
	var syncSoftware = $('#sync_software').val();
	$.ajax({
    	url: "index.php?entryPoint=VICheckESAPIConfiguration",
        type: "post",
        data: {moduleMappingSoftware : syncSoftware},
        success: function (result) {
        	if(result == 0){
        		if(syncSoftware != ''){
	        		alert(apiConfigurationMsg);
        		}//end of if
        		$('.dynamic_sync_btn, .dynamic_sync_btn_em, #mapping_module_list').hide();
				$('#switch_tr, #autoSyncAction').empty();				
				$('#auto_sync_ems').closest('tr').hide();
				$('#auto_sync_ems, #sync_ems_to_suite').removeAttr('checked');
				$('#auto_sync_ems, #sync_ems_to_suite').val('0');
				$('#sync_software').val('');
				$('#mapping_modules').empty();
        	}else{
        		$('#div_summary_suite').hide();
				$('#qboSummary > thead, #sendgrid_summary, #suitecrm_summary').empty();
				
				var syncToSendGrid = SUGAR.language.get("app_strings",'LBL_SYNC_TO_SENDGRID');
				var syncToMautic = SUGAR.language.get("app_strings",'LBL_SYNC_SUITECRM_TO_MAUTIC');
				var syncToActiveCamp = SUGAR.language.get("app_strings",'LBL_SYNC_SUITECRM_TO_ACTIVE_CAMPAIGNS');
				var syncToConstantContact = SUGAR.language.get("app_strings",'LBL_SYNC_SUITECRM_TO_CONSTANT_CONTACT');
				var syncToSendInBlue = SUGAR.language.get("app_strings",'LBL_SYNC_SUITECRM_TO_SENDINBLUE');
				var syncConfig = SUGAR.language.get("app_strings",'LBL_SYNC_CONFIG');
				var infoIcon = "return SUGAR.util.showHelpTips(this,"+"'"+syncConfig+"'"+")";
				var emsToSuiteInfoIconMessage = "return SUGAR.util.showHelpTips(this,"+"'"+SUGAR.language.get("Administration",'LBL_EMS_TO_SUITECRM_INFO_MESSAGE')+"'"+")";

				var autoMaticSyncSliderLabel = '</b><img onclick="'+infoIcon+'" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></td><td class="setvisibilityclass"><label class="switch"><input type="checkbox" name="sync_to_es" id="sync_to_es" size="30" maxlength="150" value=""><span class="slider round"></span></label><input type="hidden" value="" id="sync_to_es_switch" name="sync_to_es_switch"><td>';

				if(syncSoftware == "Mautic"){
					var infoIconMessage = "return SUGAR.util.showHelpTips(this,"+"'"+SUGAR.language.get("Administration",'LBL_AUTO_SYNC_MAUTIC_INFO')+"'"+")";
				}else if(syncSoftware == "ActiveCampaigns"){
					var infoIconMessage = "return SUGAR.util.showHelpTips(this,"+"'"+SUGAR.language.get("Administration",'LBL_AUTO_SYNC_ACTIVE_CAMPAIGNS_INFO')+"'"+")";
				}//end of else if

				var autoSyncEMSToSuiteCRMLabel = '</b><img onclick="'+emsToSuiteInfoIconMessage+'" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></td><td class="setvisibilityclass"><label class="switch"><input type="checkbox" name="sync_ems_to_suite" id="sync_ems_to_suite" size="30" maxlength="150" value=""><span class="slider round"></span></label></td>';

				if(syncSoftware != "Mautic" || syncSoftware != "ActiveCampaigns"){
					$('#auto_sync_ems').closest('tr').hide();
					$('#auto_sync_ems').removeAttr('checked');
					$('#auto_sync_ems').val('0');
				}//end of if

				if(syncSoftware == "SendGrid"){
					$('.dynamic_sync_btn').attr('name','btn_save_sync');
					$('.dynamic_sync_btn').attr('id','btn_sync_to_sendgrid');
					$('.dynamic_sync_btn').text("SYNC SUITECRM TO "+sendGrid);
					$('.dynamic_sync_btn, .dynamic_sync_btn_em, #mapping_module_list').show();
					$('.dynamic_sync_btn_em').attr('name','btn_save_suite');
					$('.dynamic_sync_btn_em').attr('id','btn_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').text("SYNC "+sendGrid+" To SuiteCRM");
					
					//for last step
					$('#switch_tr, #autoSyncAction').html('<td><b>'+syncToSendGrid+autoMaticSyncSliderLabel);
					$('#syncEMSToSuiteCRM, #autoSyncEMSToSuiteCRM').html('<td><b>'+SUGAR.language.get("Administration", 'LBL_SYNC_SENDGRID_TO_SUITECRM')+autoSyncEMSToSuiteCRMLabel);
					$('#btn_mautic_sync_to_suitecrm').hide();
				}else if(syncSoftware == "Mautic"){
					$('.dynamic_sync_btn').attr('name','btn_sync_to_mautic');
					$('.dynamic_sync_btn').attr('id','btn_sync_to_mautic');
					$('.dynamic_sync_btn, .dynamic_sync_btn_em, #mapping_module_list').show();
					$('.dynamic_sync_btn').text("SYNC SUITECRM TO "+mautic);
					$('.dynamic_sync_btn_em').attr('name','btn_mautic_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').attr('id','btn_mautic_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').text("SYNC "+mautic+" To SuiteCRM");
					
					//for last step
					$('#switch_tr, #autoSyncAction').html('<td><b>'+syncToMautic+autoMaticSyncSliderLabel);
					$('#syncEMSToSuiteCRM, #autoSyncEMSToSuiteCRM').html('<td><b>'+SUGAR.language.get("Administration", 'LBL_AUTO_SYNC_MAUTIC')+autoSyncEMSToSuiteCRMLabel);
					$('#auto_sync_ems').closest('tr').show();
					$('#WebhookSyncLabel').html('<b>'+SUGAR.language.get('Administration', 'LBL_AUTO_SYNC_MAUTIC')+'</b><img onclick="'+infoIconMessage+';" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"><br><span style="color:#ff0000;">'+SUGAR.language.get('Administration', 'LBL_WEBHOOK_NOTE')+'</span>');
					$('#WebhookSyncPreview').html('<img src="custom/modules/Administration/images/preview_icon.png" id="btn_preview" class="btn_preview" style="height: 40px;width: 40px;margin-top: -40px;"><img id="btn_preview" class="btn_preview" src="custom/modules/Administration/images/mautic_webhook.png" alt="Mautic Webhooks" style="width:10%;max-width:30px;display: none;">');

				}else if(syncSoftware == "ConstantContact"){
					$('.dynamic_sync_btn').attr('name','btn_sync_to_constant_contact');
					$('.dynamic_sync_btn').attr('id','btn_sync_to_constant_contact');
					$('.dynamic_sync_btn, .dynamic_sync_btn_em, #mapping_module_list').show();
					$('.dynamic_sync_btn').text("SYNC SUITECRM TO "+constantContacts);
					$('.dynamic_sync_btn_em').attr('name','btn_constant_contact_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').attr('id','btn_constant_contact_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').text("SYNC "+constantContacts+" To SuiteCRM");

					//for last step
					$('#switch_tr, #autoSyncAction').html('<td><b>'+syncToConstantContact+autoMaticSyncSliderLabel);
					$('#syncEMSToSuiteCRM, #autoSyncEMSToSuiteCRM').html('<td><b>'+SUGAR.language.get("Administration", 'LBL_SYNC_CONSTANT_CONTACT_TO_SUITECRM')+autoSyncEMSToSuiteCRMLabel);
				}else if(syncSoftware == "ActiveCampaigns"){
					$('.dynamic_sync_btn').attr('name','btn_sync_to_active_campaigns');
					$('.dynamic_sync_btn').attr('id','btn_sync_to_active_campaigns');
					$('.dynamic_sync_btn, .dynamic_sync_btn_em, #mapping_module_list').show();
					$('.dynamic_sync_btn').text("SYNC SUITECRM TO "+activeCampaigns);
					$('.dynamic_sync_btn_em').attr('name','btn_active_campaigns_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').attr('id','btn_active_campaigns_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').text("SYNC "+activeCampaigns+" To SuiteCRM");
					
					//for last step
					$('#switch_tr, #autoSyncAction').html('<td><b>'+syncToActiveCamp+autoMaticSyncSliderLabel);
					$('#syncEMSToSuiteCRM, #autoSyncEMSToSuiteCRM').html('<td><b>'+SUGAR.language.get("Administration", 'LBL_SYNC_ACTIVE_CAMPAIGNS_TO_SUITECRM')+autoSyncEMSToSuiteCRMLabel);
					$('#auto_sync_ems').closest('tr').show();
					$('#WebhookSyncLabel').html('<b>'+SUGAR.language.get('Administration', 'LBL_SYNC_ACTIVE_CAMPAIGNS_TO_SUITECRM')+'</b><img onclick="'+infoIconMessage+';" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"><br><span style="color:#ff0000;">'+SUGAR.language.get('Administration', 'LBL_WEBHOOK_NOTE')+'</span>');
					$('#WebhookSyncPreview').html('<img src="custom/modules/Administration/images/preview_icon.png" id="btnACPreview" class="btnACPreview" style="height: 40px;width: 40px;margin-top: -40px;"><img id="btnACPreview" class="btnACPreview" src="custom/modules/Administration/images/activeCampaignWebhook.jpg" alt="Active Campaigns Webhooks" style="width:10%;max-width:30px;display: none;">');

				}else if(syncSoftware == "SendInBlue"){
					$('.dynamic_sync_btn').attr('name','btn_sync_to_sendinblue');
					$('.dynamic_sync_btn').attr('id','btn_sync_to_sendinblue');
					$('.dynamic_sync_btn, .dynamic_sync_btn_em, #mapping_module_list').show();
					$('.dynamic_sync_btn').text("SYNC SUITECRM TO "+sendInBlue);
					$('.dynamic_sync_btn_em').attr('name','btn_sendinblue_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').attr('id','btn_sendinblue_sync_to_suitecrm');
					$('.dynamic_sync_btn_em').text("SYNC "+sendInBlue+" To SuiteCRM");

					//for last step
					$('#switch_tr, #autoSyncAction').html('<td><b>'+syncToSendInBlue+autoMaticSyncSliderLabel);
					$('#syncEMSToSuiteCRM, #autoSyncEMSToSuiteCRM').html('<td><b>'+SUGAR.language.get("Administration", 'LBL_SYNC_SEND_IN_BLUE_TO_SUITECRM')+autoSyncEMSToSuiteCRMLabel);
					$('#btn_constant_contact_sync_to_suitecrm').hide();
				}else{
					$('div.syncBtn, #mapping_module_list').hide();
					$('#switch_tr, #autoSyncAction').empty();
					$('#auto_sync_ems').closest('tr').hide();
					$('#auto_sync_ems').removeAttr('checked');
					$('#auto_sync_ems').val('0');
				}
				$.ajax({
			        url: "index.php?entryPoint=VISyncFetchModuleMappingList",
			        type: "post",
			        data: {syncSoftware : syncSoftware },
			        success: function (result) {
			        	$('#mapping_modules').empty();
			        	$('#mapping_modules').html(result);
			        	$('#sel_mapping_module_list').html(result);
			        	$('#sel_mapping_module_list').prepend("<option value='' selected>"+selectOptionAlert+"</option>");  
			        }
				});
        	}//end of else
        }//end of success
    });//end of ajax
});

$('#sel_mapping_module_list').on('change',function(){	
	$('#div_summary_suite').hide();	
	$('#qboSummary > thead').empty();	
	$('#sendgrid_summary').empty();	
	$('#suitecrm_summary').empty();	
});

//to fetch current record ID    
if(recordId != ""){
	$('#sendgrid_module').on('change',function(){
		var sendgridModule = $('#sendgrid_module').val();
		var moduleMappingSoftware = $('#module_mapping_software').val();
		$.ajax({
	        url: "index.php?entryPoint=VIESModuleFields",
	        type: "post",
	        data: {module : sendgridModule,
	        	moduleMappingSoftware : moduleMappingSoftware},
	        success: function (result) {
	        	var tergetFields = result;
	    		var fieldName = "";
	    		if(moduleMappingSoftware == "SendGrid"){
	    			fieldName = 'sendgrid_fields'+rownumber;
				}else if(moduleMappingSoftware == "Mautic"){
	    			fieldName = 'mautic_fields'+rownumber;
	    		}
	    		$('#'+fieldName).html(tergetFields);
	    		$('#'+fieldName).prepend("<option value = '' selected>"+selectOptionAlert+"</option>");  
	        }
    	});
	});
	$('#suitecrm_module').on('change',function(){
		var moduleMappingSoftware = $('#module_mapping_software').val();
		var suitecrmModule = $('#suitecrm_module').val();
		var suitecrmModuleTxt = $("#suitecrm_module :selected").text();

		$('body').find('table#allCondition > tbody > tr > th > h4 > span.selectedModuleName, table#anyCondition > tbody > tr > th > h4 > span.selectedModuleName').html(suitecrmModuleTxt);

		if(suitecrmModule == 'ProspectLists'){
			$('#suitecrm_target_list_module_row').show();
		}else{
			$('#suitecrm_target_list_module_row').hide();
			$('#suitecrm_target_list_module').val('');
		}//end of else

		if(moduleMappingSoftware == "SendGrid"){
			esModule = $('#sendgrid_module').val();
		}else if(moduleMappingSoftware == "Mautic"){
			esModule = $('#mautic_module').val();
		}else if(moduleMappingSoftware == "ConstantContact"){
			esModule = $("#constant_contact_module").val();
		}else if(moduleMappingSoftware == "SendInBlue"){
			esModule = $("#sendinblue_module").val();
		}else if(moduleMappingSoftware == "ActiveCampaigns"){
			esModule = $("#active_campaigns_module").val();
		}

		$('.btn_minus').remove();
    	$('.target_module_fields').remove();
    	$('.source_module_fields').remove();
		$.ajax({
	        url: "index.php?entryPoint=VICheckSelectedModules",
	        type: "post",
	        data: {moduleMappingSoftware : moduleMappingSoftware,
	        		suitecrmModule : suitecrmModule,
	        		esModule : esModule,
	        		recordId : recordId},
	        success: function (result) {
	        	if(result == "not valid"){
	    			alert(selectOtherPairAlert);
	        		$('#sendgrid_module').val('');
	        		$('#suitecrm_module').val('');
	        	}
	        }
        });	
	});
}else{
	$('#suitecrm_module').on('change',function(){
		var moduleMappingSoftware = $('#module_mapping_software').val();
		var suitecrmModule = $('#suitecrm_module').val();
		var suitecrmModuleTxt = $("#suitecrm_module :selected").text();
		
		$('body').find('table#allCondition > tbody > tr > th > h4 > span.selectedModuleName, table#anyCondition > tbody > tr > th > h4 > span.selectedModuleName').html(suitecrmModuleTxt);

		if(suitecrmModule == 'ProspectLists'){
			$('#suitecrm_target_list_module_row').show();
		}else{
			$('#suitecrm_target_list_module_row').hide();
			$('#suitecrm_target_list_module').val('');
		}//end of else
		
		var esModule = "";
		if(moduleMappingSoftware == "SendGrid"){
			esModule = $('#sendgrid_module').val();
		}else if(moduleMappingSoftware == "Mautic"){
			esModule = $('#mautic_module').val();
		}else if(moduleMappingSoftware == "ConstantContact"){
			esModule = $("#constant_contact_module").val();
		}else if(moduleMappingSoftware == "SendInBlue"){
			esModule = $("#sendinblue_module").val();
		}else if(moduleMappingSoftware == "ActiveCampaigns"){
			esModule = $("#active_campaigns_module").val();
		}

		$.ajax({
	        url: "index.php?entryPoint=VICheckSelectedModules",
	        type: "post",
	        data: {moduleMappingSoftware : moduleMappingSoftware,
	        		suitecrmModule : suitecrmModule,
	        		esModule : esModule},
	        success: function (result) {
	        	if(result == "not valid"){
	        		alert(selectOtherPairAlert);
	        		$('#sendgrid_module').val('');
	        		$('#suitecrm_module').val('');
	        	}
	        }
        });
    });
}

var software = $('#software').val();
if(software == "SendGrid"){
	$("#tblapikey").show();
}else if(software == "Mautic"){
	$('#tblMauticApiKey').show();
}else if(software == "ConstantContact"){
	$('#tblConstantContactsApiKey').show();
}else if(software == "ActiveCampaigns"){
	$('#tblActiveCampaignApiKey').show();
}else if(software == "SendInBlue"){
	$('#tblSendInBlueApiKey').show();
}

var moduleMappingSoftware = $('#module_mapping_software').val();
if(moduleMappingSoftware == "SendGrid"){
	$('#suitecrm_module_row').show();
	$('#sendgrid_module_row').show();
	$('#mautic_module_row').hide();
	$('#constant_contact_module_row').hide();
	$('#sendinblue_module_row').hide();
}else if(moduleMappingSoftware == "Mautic"){
	$('#mautic_module_row').show();
	$('#suitecrm_module_row').show();
	$('#sendgrid_module_row').hide();
	$('#constant_contact_module_row').hide();
	$('#sendinblue_module_row').hide();
}else if(moduleMappingSoftware == "ConstantContact"){
	$('#constant_contact_module_row').show();
	$('#mautic_module_row').hide();
	$('#suitecrm_module_row').show();
	$('#sendgrid_module_row').hide();
	$('#sendinblue_module_row').hide();
}else if(moduleMappingSoftware == "ActiveCampaigns"){
	$('#active_campaigns_module_row').show();
	$('#mautic_module_row').hide();
	$('#suitecrm_module_row').show();
	$('#sendgrid_module_row').hide();
	$('#constant_contact_module_row').hide();
	$('#sendinblue_module_row').hide();
}else if(moduleMappingSoftware == "SendInBlue"){
	$('#active_campaigns_module_row').hide();
	$('#mautic_module_row').hide();
	$('#suitecrm_module_row').show();
	$('#sendgrid_module_row').hide();
	$('#constant_contact_module_row').hide();
	$('#sendinblue_module_row').show();
}

$('#software').on('change',function(){
	var selectedSoftware = $('#software').val();
	$.ajax({
	    url: "index.php?entryPoint=VICheckApiConfiguration",
	    type: "post",
	    data: {selectedSoftware : selectedSoftware },
	    success: function (response) {
	    	if(response == "1"){
	    		alert(configSetAlert);
	    		$('#software').val('');
	    		$('#planType').hide();
	    	}else{
	    		if(selectedSoftware == 'SendGrid'){
	    			$("#tblapikey").show();
	    			$("#tblMauticApiKey").hide();
	    			$('#tblConstantContactsApiKey').hide();
	    			$('#tblActiveCampaignApiKey').hide();
	    			$('#tblSendInBlueApiKey').hide();
	    			$('#planType').show();
				}else if(selectedSoftware == "Mautic"){
					$("#tblapikey").hide();
	    			$("#tblMauticApiKey").show();
	    			$('#tblConstantContactsApiKey').hide();
	    			$('#tblActiveCampaignApiKey').hide();
	    			$('#tblSendInBlueApiKey').hide();
	    			$('#planType').hide();
				}else if(selectedSoftware == "ConstantContact"){
					$("#tblapikey").hide();
	    			$("#tblMauticApiKey").hide();
	    			$('#tblConstantContactsApiKey').show();
	    			$('#tblActiveCampaignApiKey').hide();
	    			$('#tblSendInBlueApiKey').hide();
	    			$('#planType').hide();
				}else if(selectedSoftware == "ActiveCampaigns"){
					$("#tblapikey").hide();
	    			$("#tblMauticApiKey").hide();
	    			$('#tblConstantContactsApiKey').hide();
	    			$('#tblActiveCampaignApiKey').show();
	    			$('#tblSendInBlueApiKey').hide();
	    			$('#planType').hide();
				}else if(selectedSoftware == "SendInBlue"){
					$("#tblapikey").hide();
	    			$("#tblMauticApiKey").hide();
	    			$('#tblConstantContactsApiKey').hide();
	    			$('#tblActiveCampaignApiKey').hide();
	    			$('#tblSendInBlueApiKey').show();
	    			$('#planType').hide();
				}else{
					$("#tblapikey").hide();
	    			$("#tblMauticApiKey").hide();
	    			$('#tblConstantContactsApiKey').hide();
	    			$('#tblActiveCampaignApiKey').hide();
	    			$('#tblSendInBlueApiKey').hide();
	    			$('#planType').hide();
				}		
	    	}
		},
	});
});

function back(){
	window.location.href = "index.php?module=Administration&action=vi_apiconfigurationlistview";
}

var wizardCurrentStep = $('.nav-steps.selected').attr('data-nav-step');
if(wizardCurrentStep == 1){
	$("#btn_save").hide();
	$('#btn_back').hide();
}

$('#btn_next').on('click',function(){
	var wizardCurrentStep = $('.nav-steps.selected').attr('data-nav-step');
	var suitecrmModule = $("#suitecrm_module").val();
	var sendgridModule = $("#sendgrid_module").val();
	var constantContactModule = $("#constantContactModule").val();
	var suitecrmModuleTxt = $("#suitecrm_module :selected").text();
	var sendgridModuleTxt = $("#sendgrid_module :selected").text();
	var mauticModuleTxt = $("#mautic_module :selected").text();
	var constantContactText = $('#constant_contact_module :selected').text();
	var activeCampaignsText = $('#active_campaigns_module :selected').text();
	var sendInBlueText = $('#sendinblue_module :selected').text();
	var moduleMappingSoftware = $('#module_mapping_software').val();

	if(suitecrmModule == "ProspectLists"){
		$('#div_save_continue').show();
	}

	var title = $('#title').val();
	var status = $('#status').val();
	var emailSoftware = $('#module_mapping_software').val();
	var suitecrmModule = $('#suitecrm_module').val();
	var sendgridModule = $('#sendgrid_module').val();
	var targetListSubpanelModuleModule = $('#suitecrm_target_list_module').val();

	if($('#batch_management_status').val() == '1'){
		var batchRecord = $('#batch_record').val();
	}else{
		var batchRecord = "0";
	}

	if(wizardCurrentStep == 1){
		if(title == "" || status == "" || emailSoftware == "" || suitecrmModule == "" || batchRecord == ""){
			alert(reqFieldsAlert);
		}//end of if

		if(title != "" && status != "" && emailSoftware != "" && suitecrmModule != "" && batchRecord != ""){
			if(suitecrmModule == "ProspectLists" && targetListSubpanelModuleModule == ''){
				alert(reqFieldsAlert);
			}else{
				$('#nav_step1, #navStep3').removeClass('selected');
	    		$('#nav_step2').addClass('selected');
				$('#step1, #step3').css("display", "none");
				$('#step2').css("display", "block");
				$("#btn_save").hide();
				$('#btn_back, #btn_next').show();	

				var element = $('#tbl_field_mapping tbody tr#trheader');
				$(element).empty();
				if(moduleMappingSoftware == "ConstantContact"){
					$(element).append("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ constantContacts + " " +constantContactText + " " +fields +"</b></th>");
					$('#tr_header').html("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ constantContacts + " " +constantContactText + " " +fields +"</b></th>");
				}else if(moduleMappingSoftware == "SendGrid"){
					$(element).append("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ sendGrid + " " +sendgridModuleTxt + " " +fields +"</b></th>");
					$('#tr_header').html("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ sendGrid + " " +sendgridModuleTxt + " " +fields +"</b></th>");
				}else if(moduleMappingSoftware == "ActiveCampaigns"){
					$(element).append("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ activeCampaigns + " " +activeCampaignsText + " " +fields +"</b></th>");
					$('#tr_header').html("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ activeCampaigns + " " +activeCampaignsText + " " +fields +"</b></th>");
				}else if(moduleMappingSoftware == "SendInBlue"){
					$(element).append("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ sendInBlue + " " +sendInBlueText + " " +fields +"</b></th>");
					$('#tr_header').html("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ sendInBlue + " " +sendInBlueText + " " +fields +"</b></th>");
				}else if(moduleMappingSoftware == "Mautic"){
					$(element).append("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ mautic + " " +mauticModuleTxt + " " +fields +"</b></th>");
					$('#tr_header').html("<th><b>Actions</b></th><th class='primary_module_fields_name'><b> "+ suiteCRM + " " + suitecrmModuleTxt + " " + fields +"</b></th><th class='target_module_fields_name'><b>"+ mautic + " " +mauticModuleTxt + " " +fields +"</b></th>");
				}//end of else if
			}//end of else
		}//end of if
	}else if(wizardCurrentStep == 2){
		var displayRowCount = $('#tbl_field_mapping > tbody > tr.fieldmappingrow:visible, #tbl_field_mapping1 > tbody > tr.fieldmappingrow:visible').length;

		var rowCount = $('#tbl_field_mapping > tbody > tr, #tbl_field_mapping1 > tbody > tr').length;
		var prospectListRowCount = $('#tbl_field_mapping_for_contacts > tbody > tr, #tbl_field_mapping_contacts_1 > tbody > tr').length;

		var mappingData = checkEMSFieldMappingValidation(rowCount, 'suitecrm_fields', suitecrmModule, emailSoftware, prospectListRowCount, 'suitecrm_contacts_fields');
	   	
		if(displayRowCount == 0){
			alert(SUGAR.language.get('Administration', 'LBL_EMPTY_FIELD_MAPPING_VALIDATION'));
			return false;
		}else{
			if((mappingData.obj.checkMappingValue == 1 && mappingData.obj.suiteFieldValue == '' && mappingData.obj.esFieldValue == '') || (mappingData.prospectObj != undefined && mappingData.prospectObj.checkProspectListMappingValue == 1 && mappingData.prospectObj.suiteSubPanelFieldValue == '' && mappingData.prospectObj.esProspectListFieldValue == '')){
				alert(SUGAR.language.get('Administration', 'LBL_EMPTY_FIELD_MAPPING_ALL_VALUE_VALIDATION'));
				return false;
			}else if((mappingData.obj.checkMappingValue == 1 && mappingData.obj.suiteFieldValue == '' || mappingData.obj.esFieldValue == '') || (mappingData.prospectObj != undefined && mappingData.prospectObj.checkProspectListMappingValue == 1 && mappingData.prospectObj.suiteSubPanelFieldValue == '' || mappingData.prospectObj.esProspectListFieldValue == '')){
				alert(SUGAR.language.get('Administration', 'LBL_EMPTY_FIELD_MAPPING_ANY_VALUE_VALIDATION'));
				return false;
			}else{
				$('#nav_step1, #nav_step2').removeClass('selected');
				$('#navStep3').addClass('selected');
				$('#step1, #step2').css("display", "none");
				$('#step3').css("display", "block");
				$("#btn_save, #btn_back").show();
				$('#btn_next').hide();
			}//end of else
		}//end of else

		if(recordId == ''){
	    	$('body').find('table#allCondition > tbody > tr > th > h4 > span.selectedModuleName, table#anyCondition > tbody > tr > th > h4 > span.selectedModuleName').html(suitecrmModuleTxt);
	    }//end of if
	}//end of else if
	
	if(recordId != ""){
		var module = "";
		var moduleMappingSoftware = $('#module_mapping_software').val();
		if(moduleMappingSoftware == "SendGrid"){
			module = $('#sendgrid_module').val();
		}else if(moduleMappingSoftware == "Mautic"){
			module = $('#mautic_module').val();
		}else if(moduleMappingSoftware == "ConstantContact"){
			module = $('#constant_contact_module').val();
		}else if(moduleMappingSoftware == "ActiveCampaigns"){
			module = $('#active_campaigns_module').val();
		}else if(moduleMappingSoftware == "SendInBlue"){
			module = $('#sendinblue_module').val();
		}

		var label = '';
		if(module == 'Contacts_List'){
			if($('#suitecrm_module').val() == 'ProspectLists'){
                var targetListSubpanelModule = $('#suitecrm_target_list_module').val();
				label = 'SuiteCRM '+targetListSubpanelModule+' Fields';
            }else{
                label = 'SuiteCRM Contacts Fields';
            }//end of else
		}else{
			label = 'SuiteCRM Contacts Fields';
		}//end of else

		$("#tr_header_save_continiue").html("<th><b>Actions</b></th><th><b> "+label+" </b></th><th><b>"+moduleMappingSoftware+" Contacts Fields</b></th>");

		var row = parseInt($('#row').val());
		$.ajax({
        	url: "index.php?entryPoint=VIESModuleFields",
	        type: "post",
	        data: {module : module,
	        		moduleMappingSoftware : moduleMappingSoftware},
	        success: function (result) {
	        	if(result == 1){
	        		alert(synchronizeAlert+" "+moduleMappingSoftware+" "+sendgridModule+" "+fieldsFirstAlert);
					$('#nav_step2').removeClass('selected');
				    $('#nav_step1').addClass('selected');
					$('#step1').css("display","block");
					$('#step2').css("display","none");
					$("#btn_save").hide();
					$('#btn_back').hide();
					$('#btn_next').show();
	        	}else{
	        		var tergetFields = result;
		    		var fieldName = "";
		    		if(moduleMappingSoftware == "SendGrid"){
		    			fieldName = 'sendgrid_fields'+row;
					}else if(moduleMappingSoftware == "Mautic"){
		    			fieldName = 'mautic_fields'+row;
		    		}else if(moduleMappingSoftware == "ConstantContact"){
		    			fieldName = 'constant_contact_fields'+row;
		    		}else if(moduleMappingSoftware == "ActiveCampaigns"){
		    			fieldName = 'active_campaigns_fields'+row;
		    		}else if(moduleMappingSoftware == "SendInBlue"){
		    			fieldName = 'sendinblue_fields'+row;
		    		}
		    	}
			}
		});

		$('.source_module_fields').on('change',function(){
			var selectedValue = $(this).val();
			var rowId = $(this).attr('id');
			var rownum = rowId.match(/(\d+)/); 
			for(var i=1; i<rownum[0]; i++){
    			var previusField = $('#suitecrm_fields'+i).val();

				if(previusField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}
    		}

    		var lastnum = $("#row").val();
			for(var j = lastnum; j>rownum; j--){
				var nxtField = $('#suitecrm_fields'+j).val();
				if(nxtField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}	
    		}
    	});
		$('.target_module_fields').on('change',function(){
			var selectedValue = $(this).val();
			var rowId = $(this).attr('id');
			var rownum = rowId.match(/(\d+)/); 
    		for(var i=1; i< rownum[0]; i++){
				var previusField = $('#sendgrid_fields'+i).val();	    			
				if(previusField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}
    		}

    		var lastnum = $("#row").val();
			for(var j = lastnum; j>rownum; j--){
				var nxtField = $('#sendgrid_fields'+j).val();
				if(nxtField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}	
    		}
    	});
	}
});

function checkEMSFieldMappingValidation(rowCount, suiteFields, suitecrmModule, emailSoftware, prospectListRowCount, esFieldsName){
	var checkMappingValue = checkProspectListMappingValue = 0;
	var prospectObj = obj = {};
    for(var i=0;i<rowCount+1;i++){
    	if($('tr#row'+i).is(':visible')){
    		var suiteFieldValue = $('#'+suiteFields+i).val();
    		var esFieldName = '';

    		if(emailSoftware == 'ConstantContact'){
    			esFieldName = 'constant_contact_fields';
    		}else if(emailSoftware == 'ActiveCampaigns'){
    			esFieldName = 'active_campaigns_fields';
    		}else if(emailSoftware == 'SendInBlue'){
    			esFieldName = 'sendinblue_fields';
    		}else if(emailSoftware == 'SendGrid'){
    			esFieldName = 'sendgrid_fields';
    		}else if(emailSoftware == 'Mautic'){
    			esFieldName = 'mautic_fields';
    		}//end of else if

    		var esFieldValue = $('#'+esFieldName+i).val();
			
			if(suiteFieldValue == '' || esFieldValue == ''){
    			checkMappingValue = 1;
    		}//end of if

			var obj = {
		        checkMappingValue: checkMappingValue,
		        suiteFieldValue: suiteFieldValue,		        
		        esFieldValue: esFieldValue
		    };
    	}//end of if
    }//end of for

    for(var i=0;i<prospectListRowCount+1;i++){
		if($('tr#row'+i).is(':visible')){
    		var suiteSubPanelFieldValue = $('#'+esFieldsName+i).val();
			var esProspectListFieldName = esProspectListFieldValue = '';

			if(emailSoftware == 'ConstantContact'){
    			esProspectListFieldName = 'constant_contact_contacts_fields';
    		}else if(emailSoftware == 'ActiveCampaigns'){
    			esProspectListFieldName = 'active_campaigns_contacts_fields';
    		}else if(emailSoftware == 'SendInBlue'){
    			esProspectListFieldName = 'sendinblue_fields';
    		}else if(emailSoftware == 'SendGrid'){
    			esProspectListFieldName = 'sendinblue_contacts_fields';
    		}else if(emailSoftware == 'Mautic'){
    			esProspectListFieldName = 'mautic_contacts_fields';
    		}//end of else if
    		esProspectListFieldValue = $('#'+esProspectListFieldName+i).val();

    		if(suiteSubPanelFieldValue == '' || esProspectListFieldValue == ''){
    			checkProspectListMappingValue = 1;
    		}//end of if
	    	
	    	var prospectObj = {
		        checkProspectListMappingValue: checkProspectListMappingValue,
		        suiteSubPanelFieldValue: suiteSubPanelFieldValue,		        
		        esProspectListFieldValue: esProspectListFieldValue
		    };
    	}//end of if
	}//end of for

    var data = {
    	obj: obj,
    	prospectObj: prospectObj
    };

    return data;
}//end of function

$('#btn_back').on('click',function(){
	var wizardCurrentStep = $('.nav-steps.selected').attr('data-nav-step');
	if(wizardCurrentStep == 2){
		$('#step1').css("display","block");
		$('#step2, #step3').css("display","none");
	    $('#nav_step1').addClass('selected');
		$('#nav_step2, #navStep3').removeClass('selected');
		$("#btn_save, #btn_back").hide();
		$('#btn_next').show();
	}else if(wizardCurrentStep == 3){
		$('#step2').css("display","block");
		$('#step1, #step3').css("display","none");
	    $('#nav_step2').addClass('selected');
		$('#nav_step1, #navStep3').removeClass('selected');
		$("#btn_save").hide();
		$('#btn_next, #btn_back').show();
	}//end of else if
});

$('#btn_cancel').on('click',function(){
	window.location.href = "index.php?module=Administration&action=vi_modulemappinglistview";		
});

function clearall(){
	var wizardCurrentStep = $('.nav-steps.selected').attr('data-nav-step');
    if(wizardCurrentStep == 1){
		$('#title, #status, #module_mapping_software, #batch_record, #suitecrm_module, #sendgrid_module, #constant_contact_module, #mautic_module, #active_campaigns_module, #sendinblue_module, #suitecrm_target_list_module').val('');
		$('#batch_management_status').val('0');
		$('#batch_management_status').removeAttr('checked');
		$('#no_of_records, #batch_record').closest('tr').hide();
		$('#suitecrm_module_row, #constant_contact_module_row, #sendgrid_module_row, #mautic_module_row, #active_campaigns_module_row, #sendinblue_module_row, #suitecrm_target_list_module_row').hide();
	}else  if(wizardCurrentStep == 2){
		$(".fieldmappingrow").hide(); 
	}else if(wizardCurrentStep == 3){
		$('#aowAllConditionLines, #aowAnyConditionLines').empty();
		$('#conditionalOperator').val('AND');
	}//end of else if
}

function add_mapping(){
	var moduleMappingSoftware = $('#module_mapping_software').val();
	var row = parseInt($('#row').val());
	var rowNumber = row + 1;
    var table = $('#tbl_field_mapping tbody');
    new_row = $('<tr id="row'+rowNumber+'" class="fieldmappingrow">');
    var suitecrm_field = 'suitecrm_fields'+rowNumber;

    if(moduleMappingSoftware == "SendGrid"){
	    var sendgrid_field = 'sendgrid_fields'+rowNumber;
		new_row.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field+"' id='"+suitecrm_field+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+sendgrid_field+"' id='"+sendgrid_field+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	}else if(moduleMappingSoftware == "Mautic"){
		var mautic_field = 'mautic_fields'+rowNumber;
		new_row.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field+"' id='"+suitecrm_field+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+mautic_field+"' id='"+mautic_field+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	}else if(moduleMappingSoftware == "ConstantContact"){
		var constant_contact_field = 'constant_contact_fields'+rowNumber;
		new_row.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field+"' id='"+suitecrm_field+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+constant_contact_field+"' id='"+constant_contact_field+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	}else if(moduleMappingSoftware == "ActiveCampaigns"){
		var active_campaigns_field = 'active_campaigns_fields'+rowNumber;
		new_row.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field+"' id='"+suitecrm_field+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+active_campaigns_field+"' id='"+active_campaigns_field+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	}else if(moduleMappingSoftware == "SendInBlue"){
		var sendinblue_field = 'sendinblue_fields'+rowNumber;
		new_row.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field+"' id='"+suitecrm_field+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+sendinblue_field+"' id='"+sendinblue_field+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	}

	table.append(new_row);
	var table1 = $('#tbl_field_mapping1');
	new_row1 = $('<tr id="row'+rowNumber+'" class="fieldmappingrow">');
    var suitecrm_field1 = 'suitecrm_fields'+rowNumber;
    var constant_contact_fields1 = 'constant_contact_fields'+rowNumber;
    var sendgrid_field1 = 'sendgrid_field'+rowNumber;
    var mautic_field1 = 'mautic_field'+rowNumber;
	if(moduleMappingSoftware == "SendGrid"){
	    var sendgrid_field1 = 'sendgrid_fields'+rowNumber;
		new_row1.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field1+"' id='"+suitecrm_field1+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+sendgrid_field1+"' id='"+sendgrid_field1+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	    table1.append(new_row1);
	}else if(moduleMappingSoftware == "Mautic"){
		var mautic_field1 = 'mautic_fields'+rowNumber;
		new_row1.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field1+"' id='"+suitecrm_field1+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+mautic_field1+"' id='"+mautic_field1+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	    table1.append(new_row1);
	}else if(moduleMappingSoftware == "ConstantContact"){
		var mautic_field1 = 'constant_contact_fields'+rowNumber;
		new_row1.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field1+"' id='"+suitecrm_field1+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+constant_contact_fields1+"' id='"+constant_contact_fields1+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	    table1.append(new_row1);
	}else if(moduleMappingSoftware == "ActiveCampaigns"){
		var active_campaigns_field1 = 'active_campaigns_fields'+rowNumber;
		new_row1.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field1+"' id='"+suitecrm_field1+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+active_campaigns_field1+"' id='"+active_campaigns_field1+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	    table1.append(new_row1);
	}else if(moduleMappingSoftware == "SendInBlue"){
		var sendinblue_field1 = 'sendinblue_fields'+rowNumber;
		new_row1.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field1+"' id='"+suitecrm_field1+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+sendinblue_field1+"' id='"+sendinblue_field1+"' class='target_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	    table1.append(new_row1);
	}
	row += 1;
    $("#row").val(row);
    var wizardCurrentStep = $('.nav-steps.selected').attr('data-nav-step');
    var rownumber = $("#row").val();
	var suitecrmModule = $("#suitecrm_module").val();
	$.ajax({
        url: "index.php?entryPoint=VISuiteCRMModuleFields",
        type: "post",
        data: {moduleName : suitecrmModule,
        		stepName : 'stepTwo'},
        success: function (result) {
        	var tergetFields = result;
        	$('#suitecrm_fields'+rownumber).html(tergetFields);
            $('#suitecrm_fields'+rownumber).prepend("<option value='' selected>"+selectOptionAlert+"</option>");  
        }
    });

	var esModule = "";
	if(moduleMappingSoftware == "SendGrid"){
	    esModule = $('#sendgrid_module').val();
	}else if(moduleMappingSoftware == "Mautic"){
		esModule = $('#mautic_module').val();
	}else if(moduleMappingSoftware == "ConstantContact"){
		esModule = $('#constant_contact_module').val();
	}else if(moduleMappingSoftware == "ActiveCampaigns"){
		esModule = $('#active_campaigns_module').val();
	}else if(moduleMappingSoftware == "SendInBlue"){
		esModule = $('#sendinblue_module').val();
	}

	$.ajax({
		url: "index.php?entryPoint=VIESModuleFields",
	    type: "post",
	    data: {module : esModule,
	    		moduleMappingSoftware : moduleMappingSoftware},
	    success: function (result) {
	    	if(result == 1){
	    		alert(synchronizeAlert+" "+mautic+" "+esModule+" "+fieldsFirstAlert);
				$('#nav_step2').removeClass('selected');
			    $('#nav_step1').addClass('selected');
				$('#step1').css("display","block");
				$('#step2').css("display","none");
				$("#btn_save").hide();
				$('#btn_back').hide();
				$('#btn_next').show();
	    	}else{
	    		var tergetFields = result;
	    		var fieldName = "";
	    		if(moduleMappingSoftware == "SendGrid"){
	    			fieldName = 'sendgrid_fields'+rownumber;
				}else if(moduleMappingSoftware == "Mautic"){
	    			fieldName = 'mautic_fields'+rownumber;
	    		}else if(moduleMappingSoftware == "ConstantContact"){
	    			fieldName = 'constant_contact_fields'+rownumber;
	    		}else if(moduleMappingSoftware == "ActiveCampaigns"){
	    			fieldName = 'active_campaigns_fields'+rownumber;
	    		}else if(moduleMappingSoftware == "SendInBlue"){
	    			fieldName = 'sendinblue_fields'+rownumber;
	    		}
	    		$('#'+fieldName).html(tergetFields);
	            $('#'+fieldName).prepend("<option value = '' selected>"+selectOptionAlert+"</option>");  	
	        }
		},
		error: function(result){
        	alert('fail');
    	}
	});    

    if(rownumber > 1){
    	$('.source_module_fields').on('change',function(){
    		var selectedValue = $(this).val();
			var rowId = $(this).attr('id');
			var rownum = rowId.match(/(\d+)/); 
    		for(var i=1; i< rownum[0]; i++){
				var previusField = $('#suitecrm_fields'+i).val();
				if(previusField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}
    		}

    		var lastnum = $("#row").val();
			for(var j = lastnum; j>rownum; j--){
				var nxtField = $('#suitecrm_fields'+j).val();
				if(nxtField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}	
    		}
    	});

    	$('.target_module_fields').on('change',function(){
			var selectedValue = $(this).val();
			var rowId = $(this).attr('id');
			var rownum = rowId.match(/(\d+)/); 
    		var previusField = "";
    		for(var i=1; i< rownum[0]; i++){
    			if(moduleMappingSoftware == "SendGrid"){
					previusField = $('#sendgrid_fields'+i).val();
				}else if(moduleMappingSoftware == "Mautic"){
					previusField = $('#mautic_fields'+i).val();
				}else if(moduleMappingSoftware == "ConstantContact"){
					previusField = $('#constant_contact_fields'+i).val();
				}else if(moduleMappingSoftware == "ActiveCampaigns"){
					previusField = $('#active_campaigns_fields'+i).val();
				}else if(moduleMappingSoftware == "SendInBlue"){
					previusField = $('#sendinblue_fields'+i).val();
				}
				if(previusField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}
    		}

    		var lastnum = $("#row").val();
			for(var j = lastnum; j>rownum; j--){
				if(moduleMappingSoftware == "SendGrid"){
					nxtField = $('#sendgrid_fields'+i).val();
				}else if(moduleMappingSoftware == "Mautic"){
					nxtField = $('#mautic_fields'+i).val();
				}else if(moduleMappingSoftware == "ConstantContact"){
					nxtField = $('#constant_contact_fields'+i).val();
				}else if(moduleMappingSoftware == "ActiveCampaigns"){
					nxtField = $('#active_campaigns_fields'+i).val();
				}else if(moduleMappingSoftware == "SendInBlue"){
					nxtField = $('#sendinblue_fields'+i).val();
				}
				if(nxtField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}
			}
    	});
	}

	if(suitecrmModule == 'ProspectLists'){
		if($('#row').val() > 0){
			$('#div_save_continue').show();
		}else{
			$('#div_save_continue').hide();
		}
	}else{
		$('#div_save_continue').hide();
	}
}

function add_mapping_contacts(){
	var moduleMappingSoftware = $('#module_mapping_software').val();
	var row = parseInt($('#row_contacts').val());
	var rowNumber = row + 1;
    var table = $('#tbl_field_mapping_for_contacts');
    new_row = $('<tr id="row'+rowNumber+'" class="fieldmappingrow">');
    var suitecrm_field = 'suitecrm_contacts_fields'+rowNumber;
    var es_field = "";
    if(moduleMappingSoftware == "SendGrid"){
    	es_field = 'sendgrid_contacts_fields'+rowNumber;
    }else if(moduleMappingSoftware == "Mautic"){
    	es_field = 'mautic_contacts_fields'+rowNumber;
    }else if(moduleMappingSoftware == "ConstantContact"){
    	es_field = 'constant_contact_contacts_fields'+rowNumber;
    }else if(moduleMappingSoftware == "ActiveCampaigns"){
    	es_field = 'active_campaigns_contacts_fields'+rowNumber;
    }else if(moduleMappingSoftware == "SendInBlue"){
    	es_field = 'sendinblue_contacts_fields'+rowNumber;
    }
	new_row.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field+"' id='"+suitecrm_field+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+es_field+"' id='"+es_field+"' class='target_contact_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
	table.append(new_row);
    var table1 = $('#tbl_field_mapping_contacts_1');
    new_row1 = $('<tr id="row'+rowNumber+'" class="fieldmappingrow">');
    var suitecrm_field1 = 'suitecrm_contacts_fields'+rowNumber;
    var es_field1 = "";
    if(moduleMappingSoftware == "SendGrid"){
    	es_field1 = 'sendgrid_contacts_fields'+rowNumber;
	}else if(moduleMappingSoftware == "Mautic"){
		es_field1 = 'mautic_contacts_fields'+rowNumber;
	}else if(moduleMappingSoftware == "ConstantContact"){
    	es_field1 = 'constant_contact_contacts_fields'+rowNumber;
    }else if(moduleMappingSoftware == "ActiveCampaigns"){
    	es_field1 = 'active_campaigns_contacts_fields'+rowNumber;
    }else if(moduleMappingSoftware == "SendInBlue"){
    	es_field1 = 'sendinblue_contacts_fields'+rowNumber;
    }
	new_row1.append("<td><button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button></td><td><select name='"+suitecrm_field1+"' id='"+suitecrm_field1+"' class='source_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td><td><select name='"+es_field1+"' id='"+es_field1+"' class='target_contact_module_fields'><option value = ''>"+selectOptionAlert+"</option></select></td>");
    table1.append(new_row1);
	row += 1;
    $("#row_contacts").val(row);
    var wizardCurrentStep = $('.nav-steps.selected').attr('data-nav-step');
    var rownumber = $("#row_contacts").val();
	var suitecrmModule = $("#suitecrm_target_list_module").val();;
	$.ajax({
        url: "index.php?entryPoint=VISuiteCRMModuleFields",
        type: "post",
        data: {moduleName : suitecrmModule,
        		stepName : 'stepTwo'},
        success: function (result) {
        	var tergetFields = result;
        	$('#suitecrm_contacts_fields'+rownumber).html(tergetFields);
            $('#suitecrm_contacts_fields'+rownumber).prepend("<option value='' selected>"+selectOptionAlert+"</option>");  
        }
    });
	var module = "Contacts";
	$.ajax({
    	url: "index.php?entryPoint=VIESModuleFields",
        type: "post",
        data: {module : module,
        		moduleMappingSoftware : moduleMappingSoftware},
        success: function (result) {
        	if(result == 1){
				alert("Synchronize "+moduleMappingSoftware + module +" Fields First!! Click on SYNC FIELDS Button!!");
        		$('#nav_step2').removeClass('selected');
			    $('#nav_step1').addClass('selected');
				$('#step1').css("display","block");
				$('#step2').css("display","none");
				$("#btn_save").hide();
				$('#btn_back').hide();
				$('#btn_next').show();
        	}else{
        		var tergetFields = result;
	    		var fieldName = "";
	    		var contactFieldName = "";
	    		if(moduleMappingSoftware == "SendGrid"){
	    			fieldName = 'sendgrid_fields'+rownumber;
	    			contactFieldName = "sendgrid_contacts_fields"+rownumber;
				}else if(moduleMappingSoftware == "Mautic"){
	    			fieldName = 'mautic_fields'+rownumber;
	    			contactFieldName = "mautic_contacts_fields"+rownumber;
	    		}else if(moduleMappingSoftware == "ConstantContact"){
	    			fieldName = 'constant_contact_fields'+rownumber;
	    			contactFieldName = "constant_contact_contacts_fields"+rownumber;
	    		}else if(moduleMappingSoftware == "ActiveCampaigns"){
			    	fieldName = 'active_campaigns_fields'+rownumber;
	    			contactFieldName = "active_campaigns_contacts_fields"+rownumber;
			    }else if(moduleMappingSoftware == "SendInBlue"){
			    	fieldName = 'sendinblue_fields'+rownumber;
	    			contactFieldName = "sendinblue_contacts_fields"+rownumber;
			    }
	            $('#'+contactFieldName).html(tergetFields);
	            $('#'+contactFieldName).prepend("<option value = '' selected>"+selectOptionAlert+"</option>"); 
	        }
		}
    }); 
	if(rownumber > 1){
    	$('.source_module_fields').on('change',function(){
    		var selectedValue = $(this).val();
			var rowId = $(this).attr('id');
			var rownum = rowId.match(/(\d+)/); 
    		for(var i=1; i< rownum[0]; i++){
				var previusField = $('#suitecrm_contacts_fields'+i).val();
				if(previusField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}
    		}

    		var lastnum = $("#row").val();
			for(var j = lastnum; j>rownum; j--){
				var nxtField = $('#suitecrm_contacts_fields'+j).val();
				if(nxtField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}	
    		}
    	});

    	$('.target_contact_module_fields').on('change',function(){
			var selectedValue = $(this).val();
			var rowId = $(this).attr('id');
			var rownum = rowId.match(/(\d+)/); 
    		var previusField = "";
    		for(var i=1; i< rownum[0]; i++){
    			if(moduleMappingSoftware == "SendGrid"){
					previusField = $('#sendgrid_contacts_fields'+i).val();
				}else if(moduleMappingSoftware == "Mautic"){
					previusField = $('#mautic_contacts_fields'+i).val();
				}else if(moduleMappingSoftware == "ConstantContact"){
					previusField = $('#constant_contact_contacts_fields'+i).val();
				}else if(moduleMappingSoftware == "ActiveCampaigns"){
					previusField = $('#active_campaigns_contacts_fields'+i).val();
				}else if(moduleMappingSoftware == "SendInBlue"){
					previusField = $('#sendinblue_contacts_fields'+i).val();
				}
				if(previusField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}
    		}

    		var lastnum = $("#row").val();
			for(var j = lastnum; j>rownum; j--){
				if(moduleMappingSoftware == "SendGrid"){
					nxtField = $('#sendgrid_contacts_fields'+i).val();
				}else if(moduleMappingSoftware == "Mautic"){
					nxtField = $('#mautic_contacts_fields'+i).val();
				}else if(moduleMappingSoftware == "ConstantContact"){
					nxtField = $('#constant_contact_contacts_fields'+i).val();
				}else if(moduleMappingSoftware == "ActiveCampaigns"){
					nxtField = $('#active_campaigns_contacts_fields'+i).val();
				}else if(moduleMappingSoftware == "SendInBlue"){
					nxtField = $('#sendinblue_contacts_fields'+i).val();
				}
				if(nxtField == selectedValue){
					alert(selOtherFieldAlert);
					$(this).val('');
				}
			}
    	});  
    } 
}
$("#tbl_field_mapping").on('click', '.btn_minus',function(event) {
    $(this).closest("tr").remove();
});
$("#tbl_field_mapping1").on('click', '.btn_minus',function(event) {
    $(this).closest("tr").remove();
});
$("#tbl_field_mapping_for_contacts, #tbl_field_mapping_contacts_1").on('click', '.btn_minus',function(event) {
    $(this).closest("tr").remove();
});

$('#module_mapping_software').on('change',function(){
	var moduleMappingSoftware = $('#module_mapping_software').val();
	$.ajax({
    	url: "index.php?entryPoint=VICheckESAPIConfiguration",
        type: "post",
        data: {moduleMappingSoftware : moduleMappingSoftware},
        success: function (result) {
        	if(result == 0){
        		if(moduleMappingSoftware != ''){
	        		alert(apiConfigurationMsg);
        		}//end of if
        		$('#mautic_module_row, #suitecrm_module_row, #sendgrid_module_row, #constant_contact_module_row, #active_campaigns_module_row, #sendinblue_module_row, #suitecrm_target_list_module_row').hide();
				$('#module_mapping_software, #suitecrm_target_list_module, #constant_contact_module, #sendgrid_module, #mautic_module, #active_campaigns_module, #sendinblue_module').val('');
        	}else{
        		if(moduleMappingSoftware == 'SendGrid'){
					$('#suitecrm_module_row, #sendgrid_module_row').show();
					$('#suitecrm_module, #suitecrm_target_list_module').val('');
					$('#constant_contact_module_row, #active_campaigns_module_row, #sendinblue_module_row, #mautic_module_row, #suitecrm_target_list_module_row').hide();
				}else if(moduleMappingSoftware == "Mautic"){
					$('#mautic_module_row, #suitecrm_module_row').show();
					$('#suitecrm_module, #suitecrm_target_list_module').val('');
					$('#constant_contact_module_row, #sendgrid_module_row, #active_campaigns_module_row, #sendinblue_module_row, #suitecrm_target_list_module_row').hide();
				}else if(moduleMappingSoftware == "ConstantContact"){
					$('#constant_contact_module_row, #suitecrm_module_row').show();
					$('#suitecrm_module, #suitecrm_target_list_module').val('');
					$('#mautic_module_row, #sendgrid_module_row, #active_campaigns_module_row, #sendinblue_module_row, #suitecrm_target_list_module_row').hide();
				}else if(moduleMappingSoftware == "ActiveCampaigns"){
					$('#active_campaigns_module_row, #suitecrm_module_row').show();
					$('#suitecrm_module, #suitecrm_target_list_module').val('');
					$('#mautic_module_row, #sendgrid_module_row, #constant_contact_module_row, #sendinblue_module_row, #suitecrm_target_list_module_row').hide();
				}else if(moduleMappingSoftware == "SendInBlue"){
					$('#sendinblue_module_row, #suitecrm_module_row').show();
					$('#suitecrm_module, #suitecrm_target_list_module').val('');
					$('#mautic_module_row, #sendgrid_module_row, #constant_contact_module_row, #active_campaigns_module_row, #suitecrm_target_list_module_row').hide();
				}else{
					$('#mautic_module_row, #sendgrid_module_row, #constant_contact_module_row, #active_campaigns_module_row, #suitecrm_target_list_module_row, #suitecrm_module_row, #sendinblue_module_row').hide();
				}//end of else
        	}//end of else
        }//end of success
    });//end of ajax
});

if($('#suitecrm_module').val() == 'ProspectLists'){
    $('#suitecrm_target_list_module_row').show();
}else{
    $('#suitecrm_target_list_module_row').hide();
}//end of else

var saveClickCount = 0;
function savemodulemapping() {
    var title = $('#title').val();
    var status = $('#status').val();
    var moduleMappingSoftware = $('#module_mapping_software').val();
  
    var wizardCurrentStep = $('.nav-steps.selected').attr('data-nav-step');
	//Condition Lines Validations
	if(wizardCurrentStep == 3){
		var allConditionTotalRowCount = $("#aowAllConditionLines > tbody > tr:visible").length;
		var anyConditionTotalRowCount = $("#aowAnyConditionLines > tbody > tr:visible").length;
		var checkAllConditionValue = checkAnyConditionValue = 0;
		checkAllConditionValue = checkEMSConditionValidation('aowAllConditionLines', 'aowAllProductLine', 'aowAllConditionsField', 'aowAllConditionsFieldInput', 'aowAllConditionsValue');
		checkAnyConditionValue = checkEMSConditionValidation('aowAnyConditionLines', 'aowAnyProductLine', 'aowAnyConditionsField', 'aowAnyConditionsFieldInput', 'aowAnyConditionsValue');
    }//end of if

    if(checkAllConditionValue == 1 || checkAnyConditionValue == 1){
    	alert(reqFieldsAlert);
    	return false;
    }else{
    	var formData = $('form');
	    var disabled = formData.find(':disabled').removeAttr('disabled');
	    var formData = formData.serialize();

	    if(saveClickCount == 0){
		    $.ajax({
	            url: "index.php?entryPoint=VIAddModuleMapping",
	            type: "post",
	            data: {val : formData,
	            	id :  id},
	            success: function (response) {
	            	window.location.href = listViewUrl;
	            }//end of success
	        });//end of ajax
	    	saveClickCount++;
		}//end of if
    }//end of else
}//end of function

//Check Condition Block Empty Validation
function checkEMSConditionValidation(tableName, lineId, fieldId, fieldInputId, fieldValue){
	var rowCount = $("#"+tableName+" > tbody > tr").length;
	var checkConditionValue = 0;
	var conditionValue = '';

	for(var i=0; i<rowCount; i++){
		if($('#'+lineId+i).is(':visible')){

			var field = document.getElementById(fieldId+i).value;
			if(field != ''){
                if(tableName == 'aowAllConditionLines' || tableName == 'aowAnyConditionLines'){
                	if(tableName == 'aowAllConditionLines'){
                		var valueType = document.getElementById('aowAllConditionsValueType['+i+']').value;
                		var operator = document.getElementById('aowAllConditionsOperator['+i+']').value; //operator
                	}else if(tableName == 'aowAnyConditionLines'){
                		var valueType = document.getElementById('aowAnyConditionsValueType['+i+']').value;
                		var operator = document.getElementById('aowAnyConditionsOperator['+i+']').value; //operator
                	}//end of else

                	if(valueType == 'Date'){
                		param0 = document.getElementById(fieldValue+'['+i+'][0]').value;
	                    param1 = document.getElementById(fieldValue+'['+i+'][1]').value;
	                    param2 = document.getElementById(fieldValue+'['+i+'][2]').value;
	                    param3 = document.getElementById(fieldValue+'['+i+'][3]').value;
	                    if(param1 == 'now'){
	                        param2 = '0';
	                        param3 = '0';
	                    }//end of if

	                    if(param0 == '' || param1 == '' || param2 == '' || param3 == ''){
	                        conditionValue = '0';
	                    }else{
	                        conditionValue = '1';
	                    }//end of else
                	}else{
                		var checkDateTimeComboTDType = $('td #'+fieldInputId+i).closest('tr').find("td input.datetimecombo_date").length;
						if(field == 'currency_id'){
		                    conditionValue = document.getElementById(fieldValue+'['+i+']_select').value;
		                }else if(checkDateTimeComboTDType > 0){
		                    conditionValue = $('td #'+fieldInputId+i).closest('tr').find("td input.datetimecombo_date").val();
		                }else{
		                    conditionValue = document.getElementById(fieldValue+'['+i+']').value;    
		                }//end of else				                
                	}//end of else
                	
                	if(jQuery.inArray(operator, conditionOperators) != -1){
                		checkConditionValue = '0';
				    }else{
				    	if(conditionValue == ''){
	                        checkConditionValue = 1;
	                    }//end of if
				    }//end of else
	                
                }else{
                	if(conditionValue == ''){
                    	checkConditionValue = 1;
                	}//end of if
                }//end of else
            }else{
            	checkConditionValue = 1;
            }//end of else
		}//end of if		
	}//end of for
	return checkConditionValue;
}//end of function

$("#sendgrid_module").on('change',function(){
	var sendgridModule = $("#sendgrid_module").val();
	$('#suitecrm_target_list_module_row').hide();
	$('#suitecrm_target_list_module').val('');
	if(sendgridModule == "Contacts"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Contacts'>Contacts</option>");
		$("#suitecrm_module").append("<option value='Leads'>Leads</option>");
	}else if(sendgridModule == "Contacts_List"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='ProspectLists'>Target List</option>");
	}else{
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Campaigns'>Campaigns</option>");
	}
});

$("#mautic_module").on('change',function(){
	var mauticModule = $("#mautic_module").val();
	$('#suitecrm_target_list_module_row').hide();
	$('#suitecrm_target_list_module').val('');
	if(mauticModule == "Assets"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='AOS_Products'>Products</option>");
	}else if(mauticModule == "Contacts"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Contacts'>Contacts</option>");
		$("#suitecrm_module").append("<option value='Leads'>Leads</option>");
	}else if(mauticModule == "Segments"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='ProspectLists'>Target List</option>");
	}else if(mauticModule == "Companies")	{
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Accounts'>Accounts</option>");
	}else{
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Campaigns'>Campaigns</option>");
	}
});

$("#constant_contact_module").on('change',function(){
	var constantContactsModule = $("#constant_contact_module").val();
	$('#suitecrm_target_list_module_row').hide();
	$('#suitecrm_target_list_module').val('');
	if(constantContactsModule == "Contacts_List"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='ProspectLists'>Target List</option>");
	}else if(constantContactsModule == "Campaigns"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Campaigns'>Campaigns</option>");
	}else{
		$("#suitecrm_module").empty();
	}
});

$("#active_campaigns_module").on('change',function(){
	var activeCampaignsModule = $("#active_campaigns_module").val();
	$('#suitecrm_target_list_module_row').hide();
	$('#suitecrm_target_list_module').val('');
	if(activeCampaignsModule == "Contacts"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Contacts'>Contacts</option>");
		$("#suitecrm_module").append("<option value='Leads'>Leads</option>");
	}else if(activeCampaignsModule == "Contacts_List"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='ProspectLists'>Target List</option>");
	}else if(activeCampaignsModule == "Organizations"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Accounts'>Accounts</option>");
	}else{
		$("#suitecrm_module").empty();
	}
});

$("#sendinblue_module").on('change',function(){
	var sendinblueModule = $("#sendinblue_module").val();
	$('#suitecrm_target_list_module_row').hide();
	$('#suitecrm_target_list_module').val('');
	if(sendinblueModule == "Contacts"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Contacts'>Contacts</option>");
		$("#suitecrm_module").append("<option value='Leads'>Leads</option>");
	}else if(sendinblueModule == "Contacts_List"){
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='ProspectLists'>Target List</option>");
	}else{
		$("#suitecrm_module").empty();
		$("#suitecrm_module").append("<option value=''>"+selectOptionAlert+"</option>");
		$("#suitecrm_module").append("<option value='Campaigns'>Campaigns</option>");
	}
});

$("#suitecrm_target_list_module").on('change',function(){
	$('#tbl_field_mapping_contacts_1 .fieldmappingrow, #tbl_field_mapping_for_contacts .fieldmappingrow').each(function(){
		changeSaveHeaderContinueLabel();
        $(this).closest("tr").remove();
    });//end of each
});

//Change Header Based on Selected Target List Subpanel Module
function changeSaveHeaderContinueLabel(){
	var moduleName = "";
	var moduleMappingSoftware = $('#module_mapping_software').val();
	if(moduleMappingSoftware == "SendGrid"){
		moduleName = $('#sendgrid_module').val();
	}else if(moduleMappingSoftware == "Mautic"){
		moduleName = $('#mautic_module').val();
	}else if(moduleMappingSoftware == "ConstantContact"){
		moduleName = $('#constant_contact_module').val();
	}else if(moduleMappingSoftware == "ActiveCampaigns"){
		moduleName = $('#active_campaigns_module').val();
	}else if(moduleMappingSoftware == "SendInBlue"){
		moduleName = $('#sendinblue_module').val();
	}

	var label = '';
	if(moduleName == 'Contacts_List'){
		if($('#suitecrm_module').val() == 'ProspectLists'){
            var targetListSubpanelModule = $('#suitecrm_target_list_module').val();
			label = 'SuiteCRM '+targetListSubpanelModule+' Fields';
        }else{
            label = 'SuiteCRM Contacts Fields';
        }//end of else
	}else{
		label = 'SuiteCRM Contacts Fields';
	}//end of else

	$("#tr_header_save_continiue").html("<th><b>Actions</b></th><th><b>"+label+"</b></th><th><b>"+moduleMappingSoftware+" Contacts Fields</b></th>");
	$('#btn_add_mapping_for_contacts').show();
}//end of function

function syncFields(){
	var moduleMappingSoftware = $('#module_mapping_software').val();
	var esModule = "";
	if(moduleMappingSoftware == "SendGrid"){
		esModule = $("#sendgrid_module").val();	
	}else if(moduleMappingSoftware == "Mautic"){
		esModule = $('#mautic_module').val();		
	}else if(moduleMappingSoftware == "ConstantContact"){
		esModule = $('#constant_contact_module').val();		
	}else if(moduleMappingSoftware == "ActiveCampaigns"){
		esModule = $('#active_campaigns_module').val();		
	}else if(moduleMappingSoftware == "SendInBlue"){
		esModule = $('#sendinblue_module').val();		
	}
	var mb1 = messageBox();
    mb1.setBody('<div class="email-in-progress"><img src="themes/' + SUGAR.themes.theme_name + '/images/loading.gif"></div>');
    mb1.show();
    mb1.hideHeader();
    mb1.hideFooter();
	$.ajax({
        url: "index.php?entryPoint=VISyncESFields",
        type: "post",
        data: {module : esModule,
        		moduleMappingSoftware : moduleMappingSoftware,
        		id : recordId },
        success: function (result) {
        	mb1.remove();
        	alert(result);
        	console.log(result);
        }
    });
}

function backToSync(){
	window.location.href = "index.php?module=Administration&action=vi_integrationwidget";
}
function sync_save_and_continue(){
	var moduleMappingSoftware = $('#module_mapping_software').val();
	var esModule = "Contacts";
	var mb1 = messageBox();
    mb1.setBody('<div class="email-in-progress"><img src="themes/' + SUGAR.themes.theme_name + '/images/loading.gif"></div>');
    mb1.show();
    mb1.hideHeader();
    mb1.hideFooter();
	$.ajax({
        url: "index.php?entryPoint=VISyncESFields",
        type: "post",
        data: {module : esModule,
        		moduleMappingSoftware : moduleMappingSoftware},
        success: function (result) {
        	mb1.remove();
        	alert(result);
        	changeSaveHeaderContinueLabel();
        }
    });
}

$(document).on('click', '#statusAction, #emsToSuiteStatusAction', function() {
	var actionId = $(this).attr('id');

	if($(this).is(':checked')){
		$(this).val('1');
	}else{
		$(this).val('0');
		$(this).removeAttr('checked');
	}//end of else

	var status = emsToSuiteStatus = '';
	if(actionId == 'statusAction'){
		status = $(this).val();
	}else{
		emsToSuiteStatus = $(this).val();
	}//end of else
	var automaticSyncId = $(this).closest('tr').attr('data-id');
	var syncSoftware = $(this).closest('tr').attr('data-software');

	$.ajax({
	    url: "index.php?entryPoint=VIAddAutomaticSync",
	    type: "post",
	    data: { status : status,
	    		id : automaticSyncId,
	    		emsToSuiteStatus : emsToSuiteStatus},
	    success: function (response) {
	    	var message = SUGAR.language.get('Administration', 'LBL_AUTOMATIC_SYNC')+' '+SUGAR.language.get('Administration', 'LBL_OF')+' ';
			if(actionId == 'statusAction'){
				message += SUGAR.language.get('Administration', 'LBL_SUITECRM_TO_EMS_STATUS')+' '+SUGAR.language.get('Administration', 'LBL_FOR')+' '+syncSoftware+' ';
		    	if(status == 0){
		    		message += SUGAR.language.get('Administration', 'LBL_DEACTIVATED');
	    		}else{
	    			message += SUGAR.language.get('Administration', 'LBL_ACTIVATED');
	    		}//end of else
	    		alert(message);
	    	}else{
	    		var emsToSuiteSyncMessage = message+SUGAR.language.get('Administration', 'LBL_EMS_TO_SUITECRM_STATUS')+' '+SUGAR.language.get('Administration', 'LBL_FOR')+' '+syncSoftware+' ';
	    		if(emsToSuiteStatus == 0){
		    		emsToSuiteSyncMessage += SUGAR.language.get('Administration', 'LBL_DEACTIVATED');
	    		}else{
	    			emsToSuiteSyncMessage += SUGAR.language.get('Administration', 'LBL_ACTIVATED');
	    		}//end of else
	    		alert(emsToSuiteSyncMessage);
	    	}//end of else
    		window.location.href = "index.php?module=Administration&action=vi_automaticsynclistview";
	    }//end of success
	});
});

function save_automatic_sync(){
	var syncSoftware = $('#sync_software').val();
	var mappingModule = $('#mapping_modules').val();

	if(syncSoftware == '' || mappingModule == null){
		alert(reqFieldsAlert);
		return false;
	}else{
		var formData = $('form');
	    var disabled = formData.find(':disabled').removeAttr('disabled');
	    formData = formData.serialize();
	    $.ajax({
		    url: "index.php?entryPoint=VIAddAutomaticSync",
		    type: "post",
		    data: { val : formData,
		    		id : recordId },
		    success: function (response) {
		    	var obj = jQuery.parseJSON (response);
		    	if(obj != "" && obj.code == 1){
	                alert(SUGAR.language.get('Administration','LBL_SYNC_ADD_MESSAGE'));
	            }else if(obj != "" && obj.code == 2){
	                alert(SUGAR.language.get('Administration','LBL_SYNC_UPDATE_MESSAGE'));
	            }else if(obj != "" && obj.code == 3){
	                alert(SUGAR.language.get('Administration','LBL_SYNC_CONF_EXIST'));   
	            }
	            window.location.href = "index.php?module=Administration&action=vi_automaticsynclistview";
		    }
		});
	}//end of else
}

function cancel(){
	window.location.href = "index.php?module=Administration&action=vi_automaticsynclistview";
}

if($('#sync_to_es').is(':checked')){
	$('#sync_to_es').val('1');
}else{
	$('#sync_to_es').val('0');
}

$(document).on('change', '#sync_to_es, #sync_ems_to_suite', function() {
	if($(this).is(':checked')){
		$(this).val('1');
	}else{
	   $(this).val('0');
	   $(this).removeAttr('checked');
	}
});

//delete
$('#btn_delete').on('click', function() {
	var id = [];
    $(".listview-checkbox:checked").each(function() {
      id.push($(this).val());
    });
    if(id.length <=0) { 
        alert(selectRecordAlert); 
    } else {
        var msg = deleteAlert+" "+(id.length>1?theseLable:thisLable)+" "+rowAlert;
        var checked = confirm(msg);
        if(checked == true){
          	var selected_values = id.join(",");
			$.ajax({
                type: "POST",
                url: "index.php?entryPoint=VIDeleteAutomaticSync",
                data: 'del_id='+selected_values,
                success: function(response) {
                	window.location.href = 'index.php?module=Administration&action=vi_automaticsynclistview';
                }
            });
        }
    }
});

//select all checkbox
$('#select_all').click(function(event) {   
	if(this.checked) {
		// Iterate each checkbox
		$('.listview-checkbox').each(function() {
		  this.checked = true;                        
		});
	} else {
		$('.listview-checkbox').each(function() {
		  this.checked = false;                        
		});
	}//end of else
});

Calendar.setup ({inputField : "sync_integration",form : "SyncEditView",ifFormat : "%m/%d/%Y %H:%M",daFormat : "%m/%d/%Y %H:%M",button : "sync_integration_trigger",singleClick : true,dateStr : "",startWeekday: 0,step : 1,weekNumbers:false });

$('.batch_management_status').on('change',function(){
	if($(this).is(':checked')){
		$(this).val('1');
		$('#batch_record').val('');
		$('#no_of_records').closest('tr').show();
		$('#batch_record').closest('tr').show();		
	}else{
		$(this).val('0');
		$('#no_of_records').closest('tr').hide();
		$('#batch_record').closest('tr').hide();
		$('#batch_record').val('');
	}//end of else
});//end of function

$('#batch_record').on('change',function(){
	var batchRecord = $(this).val();
	if(parseInt(batchRecord) > listviewMaxRecord){
		alert(SUGAR.language.get('Administration','LBL_BATCH_SIZE_VALIDATION')+' '+listviewMaxRecord);
		$(this).val('');
	}//end of if
});//end of function

$('#auto_sync_ems').on('change', function(){
	if($(this).is(':checked')){
		$(this).val('1');
	}else{
		$(this).val('0');
	}//end of else
});//end of function

// Get the modal
var modal = document.getElementById("myModal");

// Get the image and insert it inside the modal - use its "alt" text as a caption
var modalImg = document.getElementById("img01");
var captionText = document.getElementById("caption");
$(document).on('click', '.btn_preview, .btnACPreview', function() {
	var openImage = $(this).next("img").attr("src");
	var openImageText = $(this).next("img").attr("alt");
	modal.style.display = "block";
	modalImg.style.width = "100%";
	modalImg.style.marginLeft = "35%";
	modalImg.src = openImage;
	captionText.innerHTML = openImageText;
})//end of function

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

if(span != undefined){
	// When the user clicks on <span> (x), close the modal
	span.onclick = function() { 
  		modal.style.display = "none";
	}//end of function
}//end of if