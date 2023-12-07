{*
 
*}
<html>
<head>
<link rel="stylesheet" type="text/css" href="custom/modules/Administration/css/VIIntegrationCss.css">
</head>
<body>
<div class="moduleTitle">
<h2>{$MOD.LBL_CAP_MODULE_MAPPING}</h2>
</div>
<div class="clear"></div>
<form name="EditView">
    <div class="progression-container">
        <ul class="progression">
            <li id="nav_step1" class="nav-steps selected" data-nav-step="1"><div>{$MOD.LBL_SELECT_SOFTWARE}</div></li>
            <li id="nav_step2" class="nav-steps" data-nav-step="2"><div>{$MOD.LBL_FIELD_MAPPING}</div></li>
            <li id="navStep3" class="nav-steps" data-nav-step="3"><div>{$MOD.LBL_APPLY_CONDITION}</div></li>
        </ul>
    </div>
    
    <p>
        <div id ='buttons'>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                <tr> 
                    <td align="left" width='30%'>
                        <table border="0" cellspacing="0" cellpadding="0" ><tr>
                            <td><div id="back_button_div"><input id="btn_back" type='button' title="{$MOD.LBL_BACK}" class="button" name="back" value="{$MOD.LBL_BACK}" style="margin-right: 20px;"></div></td>
                            <td><div id="cancel_button_div"><button type="button" class="button" name= "btn_cancel" id= "btn_cancel" onclick = "cancel();">{$MOD.LBL_CANCEL}</button></div></td>
                            <td><div id="clear_button_div"><button type="button" class="button" name= "btn_clear" id= "btn_clear" onclick = "clearall();" style="margin-left: 20px;">{$MOD.LBL_CLEAR}</button></div></td>
                            <td><div id="next_button_div"><button type="button" class="button" name= "btn_next" id= "btn_next" style="margin-left: 20px;">{$MOD.LBL_NEXT}</button></div></td>
                            <td><div id="save_button_div"><button type="button" class="button" name= "btn_save" id= "btn_save" onclick = "savemodulemapping()" style="margin-left: 20px;">{$MOD.LBL_SAVE}</button></div></td>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </p>

    <table cellspacing="1" style="">
        <tr>
            <td class='edit view' rowspan='2' width='100%'>
                <div id="wiz_message"></div>
                <div id=wizard class="wizard-unique-elem" style="width:1000px;">
                    <div id="step1">
                        <div class="template-panel">
                            <div class="template-panel-container panel">
                                <div class="template-container-full">
                                    <table width="100%" border="0" cellspacing="10" cellpadding="0">
                                        <tbody>
                                            <tr><th colspan="4"><h4 class="header-4">{$MOD.LBL_SELECT_SOFTWARE}</h4></th></tr> 
                                            <tr>
                                                <td><b><span class="required">*</span>{$MOD.LBL_TITLE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_TITLE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <input type="text" name="title" id="title" value="{$TITLE}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b><span class="required">*</span>{$MOD.LBL_STATUS}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_STATUS_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <select name="status" id="status">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                            <option value="Active" {if $STATUS eq 'Active'} selected = "selected" {/if}>{$MOD.LBL_ACTIVE}</option>
                                                            <option value="Inactive" {if $STATUS eq 'Inactive'} selected = "selected" {/if}>{$MOD.LBL_INACTIVE}</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b><span class="required">*</span>{$MOD.LBL_SELECT_SOFTWARE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SELECT_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <select name="module_mapping_software" id="module_mapping_software">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        <option value="SendGrid" {if $MODULEMAPPINGSOFTWARE eq 'SendGrid'} selected = "selected" {/if}>{$MOD.LBL_SENDGRID}</option>
                                                        <option value="Mautic" {if $MODULEMAPPINGSOFTWARE eq 'Mautic'} selected = "selected" {/if}>{$MOD.LBL_MAUTIC}</option>
                                                        <option value="ConstantContact" {if $MODULEMAPPINGSOFTWARE eq 'ConstantContact'} selected = "selected" {/if}>{$MOD.LBL_CONSTANT_CONTACT}</option>
                                                        <option value="ActiveCampaigns" {if $MODULEMAPPINGSOFTWARE eq 'ActiveCampaigns'} selected = "selected" {/if}>{$MOD.LBL_ACTIVE_CAMPAIGNS}</option>
                                                        <option value="SendInBlue" {if $MODULEMAPPINGSOFTWARE eq 'SendInBlue'} selected = "selected" {/if}>{$MOD.LBL_SEND_IN_BLUE}</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr id="sendgrid_module_row" style="display: none">
                                                <td><b><span class="required">*</span>{$MOD.LBL_SENDGRID_MODULE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_MODULE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <select name="sendgrid_module" id="sendgrid_module">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$SENDGRIDALLMODULES item=value key = key}
                                                            <option value="{$value}" {if $ESMODULE eq $value}selected{/if}>{$key}</option>
                                                        {/foreach}
                                                    </select>
                                                    <button type="button" class="button" onclick = "syncFields();">{$MOD.LBL_SYNC_FIELDS}</button>
                                                </td>
                                            </tr>

                                            <tr id="mautic_module_row" style="display: none">
                                                <td><b><span class="required">*</span>{$MOD.LBL_MAUTIC_MODULE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_MODULE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <select name="mautic_module" id="mautic_module">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$MAUTIC_ALL_MODULES item=value}
                                                            <option value="{$value}" {if $ESMODULE eq $value}selected{/if}>{$value}</option>
                                                        {/foreach}                                         
                                                    </select>
                                                    <button type="button" class="button" onclick = "syncFields();">{$MOD.LBL_SYNC_FIELDS}</button>
                                                </td>
                                            </tr>

                                            <tr id="constant_contact_module_row" style="display: none">
                                                <td><b><span class="required">*</span>{$MOD.LBL_CONSTANT_CONTACT_MODULE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_MODULE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <select name="constant_contact_module" id="constant_contact_module">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$CONSTANTCONTACTALLMODULES item=value key = key}
                                                            <option value="{$value}" {if $ESMODULE eq $value}selected{/if}>{$key}</option>
                                                        {/foreach}
                                                    </select>
                                                    <button type="button" class="button" onclick = "syncFields();">{$MOD.LBL_SYNC_FIELDS}</button>
                                                </td>
                                            </tr>

                                            <tr id="active_campaigns_module_row" style="display: none">
                                                <td><b><span class="required">*</span>{$MOD.LBL_ACTIVE_CAMPAIGNS_MODULE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_MODULE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <select name="active_campaigns_module" id="active_campaigns_module">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$ACTIVECAMPAIGNSALLMODULES item=value key = key}
                                                            <option value="{$value}" {if $ESMODULE eq $value}selected{/if}>{$key}</option>
                                                        {/foreach}
                                                    </select>
                                                    <button type="button" class="button" onclick = "syncFields();">{$MOD.LBL_SYNC_FIELDS}</button>
                                                </td>
                                            </tr>

                                            <tr id="sendinblue_module_row" style="display: none">
                                                <td><b><span class="required">*</span>{$MOD.LBL_SEND_IN_BLUE_MODULE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_MODULE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <select name="sendinblue_module" id="sendinblue_module">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$SENDINBLUEALLMODULES item=value key = key}
                                                            <option value="{$value}" {if $ESMODULE eq $value}selected{/if}>{$key}</option>
                                                        {/foreach}
                                                    </select>
                                                    <button type="button" class="button" onclick = "syncFields();">{$MOD.LBL_SYNC_FIELDS}</button>
                                                </td>
                                            </tr> 
                                            
                                            {if $RECORDID neq ""}
                                            <tr id = "suitecrm_module_row" style="display: none">
                                                <td><b><span class="required">*</span>{$MOD.LBL_SUITECRM_MODULE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SUITECRM_MODULE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                <td class="setvisibilityclass">
                                                    <select name="suitecrm_module" id="suitecrm_module">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {if $ESMODULE eq 'Contacts'}
                                                            <option value="Contacts" {if $SUITECRMMODULE eq 'Contacts'} selected = "selected" {/if}>Contacts</option>
                                                            <option value="Leads" {if $SUITECRMMODULE eq 'Leads'} selected = "selected" {/if}>Leads</option>
                                                        {elseif $ESMODULE eq "Campaigns"}
                                                            <option value="Campaigns" {if $SUITECRMMODULE eq 'Campaigns'} selected = "selected" {/if}>Campaigns</option>
                                                        {elseif $ESMODULE eq "Assets"}
                                                            <option value="AOS_Products" {if $SUITECRMMODULE eq 'AOS_Products'} selected = "selected" {/if}>Products</option>
                                                        {elseif $ESMODULE eq "Companies"}
                                                            <option value="Accounts" {if $SUITECRMMODULE eq 'Accounts'} selected = "selected" {/if}>Accounts</option>
                                                        {elseif $ESMODULE eq "Organizations"}
                                                            <option value="Accounts" {if $SUITECRMMODULE eq 'Accounts'} selected = "selected" {/if}>Accounts</option>
                                                        {elseif $ESMODULE eq "Contacts_List" || "Segments"}
                                                            <option value="ProspectLists" {if $SUITECRMMODULE eq 'ProspectLists'} selected = "selected" {/if}>Target List</option>
                                                        {/if}
                                                    </select>
                                                </td>
                                            </tr>

                                            {else}
                                                <tr id = "suitecrm_module_row" style="display: none">
                                                    <td><b><span class="required">*</span>{$MOD.LBL_SUITECRM_MODULE}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_SUITECRM_MODULE_CONFIG}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></b></td>
                                                    <td class="setvisibilityclass">
                                                        <select name="suitecrm_module" id="suitecrm_module">
                                                            <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            {/if}

                                            <tr id="suitecrm_target_list_module_row" style="display: none">
                                                <td><b><span class="required">*</span>SuiteCRM Target List Subpanel/Related Module</b></td>

                                                <td class="setvisibilityclass">
                                                    <select name="suitecrm_target_list_module" id="suitecrm_target_list_module">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$TARGET_LIST_MODULES item=moduleValue key = moduleName}
                                                            <option value="{$moduleValue}" {if $moduleValue eq $TARGETLIST_SUBPANEL_MODULE} selected = "selected" {/if}>{$moduleName}</option>
                                                        {/foreach}
                                                    </select>
                                                </td
                                            </tr>

                                            <tr>
                                                <th colspan="4"><h4 class="header-4">{$MOD.LBL_BATCH_MANAGEMENT}<img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_BATCH_MANAGEMENT_INFO}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"><label class="switch" style="height:14px;margin-left:20px;">
                                                        <input type="checkbox" class="batch_management_status" id="batch_management_status" name="batch_management_status" size="30" maxlength="150" value="{$BATCH_MANAGMENT_STATUS}" {if $BATCH_MANAGMENT_STATUS eq '1'} checked{/if}>
                                                        <span class="slider round"></span>
                                                    </label></h4>
                                                </th>
                                            </tr>
                                            <tr>
                                                <td><b>{$MOD.LBL_NO_OF_RECORDS}</b><img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_NO_OF_RECORD_INFO}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></td>
                                                <td class="setvisibilityclass"><input type="text" name="no_of_records" value="100" disabled id="no_of_records"></td>
                                            </tr>
                                            <tr>
                                                <td><span class="required">*</span><b>{$MOD.LBL_BATCH_RECORD}</b><img onclick="return SUGAR.util.showHelpTips(this,'{$MOD.LBL_BATCH_SIZE_INFO}');" src="themes/default/images/helpInline.gif?v=wmRJeTRpwzmvnjrzcfY9qw" alt="Information" class="inlineHelpTip" border="0"></td>
                                                <td><input type="text" name="batch_record" id="batch_record" value="{$BATCH_RECORD}"></td>
                                            </tr>
                                        </tbody>
                                    </table> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <p></p>
                    <p></p>
                    
                    <div id="step2" style="display:none;">
                        <div class="template-panel">
                            <div class="template-panel-container panel">
                                <div class="template-container-full">
                                    <input type="hidden" name="row" id="row" value="{$FIELDSARRAY|@count}">
                                    
                                    {if $RECORDID eq ""}
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" id="tbl_field_mapping" style="margin-left: 15px;">
                                            <thead>
                                                <tr><th colspan='4'><h4 class='header-4'>{$APP.LBL_FIELD_MAPPING}</h4></th></tr>
                                            </thead>
                                            <tbody>
                                                <tr id='trheader'></tr>
                                            </tbody>
                                        </table>
                                    {else}
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" id="tbl_field_mapping1" style="margin-left: 15px;">
                                        <tbody>
                                            <tr id="tr_header"></tr>
                                            {foreach from=$FIELDSARRAY key=fieldValue item=fieldName}
                                            {assign var=KEYS value=$fieldValue+1}
                                            <tr id="row" class="fieldmappingrow">
                                                <td>
                                                    <button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button>
                                                </td>
                                                <td>
                                                    <select name='suitecrm_fields{$KEYS}' id='suitecrm_fields{$KEYS}' class='source_module_fields'>
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$SOURCEMODULEALLFIELDS key=key item=value}
                                                            <option value="{$key}" {if $fieldName.suitecrmModuleField eq $key}selected{/if}>{$value}</option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td>

                                                    {if $MODULEMAPPINGSOFTWARE eq 'SendGrid'}
                                                    <select name='sendgrid_fields{$KEYS}' id='sendgrid_fields{$KEYS}' class="target_module_fields">
                                                    {foreach from=$ESMODULEALLFIELDS key=k item=v}
                                                        <option value="{$v}" {if $fieldName.esModuleField eq $v}selected{/if}>{$v}</option>
                                                    {/foreach}
                                                    </select>    

                                                    {elseif $MODULEMAPPINGSOFTWARE eq 'Mautic'}
                                                    <select name='mautic_fields{$KEYS}' id='mautic_fields{$KEYS}' class="target_module_fields">
                                                        {foreach from=$ESMODULEALLFIELDS key=k item=v}
                                                            <option value="{$v}" {if $fieldName.esModuleField eq $v}selected{/if}>{$v}</option>
                                                        {/foreach}
                                                    </select>
                                                    
                                                    {elseif $MODULEMAPPINGSOFTWARE eq 'ConstantContact'}
                                                    <select name='constant_contact_fields{$KEYS}' id='constant_contact_fields{$KEYS}' class="target_module_fields">
                                                        {foreach from=$ESMODULEALLFIELDS key=k item=v}
                                                            <option value="{$v}" {if $fieldName.esModuleField eq $v}selected{/if}>{$v}</option>
                                                        {/foreach}
                                                    </select>
                                                    

                                                    {elseif $MODULEMAPPINGSOFTWARE eq 'ActiveCampaigns'}
                                                    <select name='active_campaigns_fields{$KEYS}' id='active_campaigns_fields{$KEYS}' class="target_module_fields">
                                                        {foreach from=$ESMODULEALLFIELDS key=k item=v}
                                                            <option value="{$v}" {if $fieldName.esModuleField eq $v}selected{/if}>{$v}</option>
                                                        {/foreach}
                                                    </select>

                                                    {elseif $MODULEMAPPINGSOFTWARE eq 'SendInBlue'}
                                                    <select name='sendinblue_fields{$KEYS}' id='sendinblue_fields{$KEYS}' class="target_module_fields">
                                                        {foreach from=$ESMODULEALLFIELDS key=k item=v}
                                                            <option value="{$v}" {if $fieldName.esModuleField eq $v}selected{/if}>{$v}</option>
                                                        {/foreach}
                                                    </select>
                                                    {/if}
                                                    
                                                </td>
                                        </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                    {/if}
                                    <button type="button" class="button" name= "btn_add_mapping" id= "btn_add_mapping" onclick = "add_mapping();" style="margin-top: 10px;">{$MOD.LBL_ADD_MAPPING}</button>
                                    <br>
                                    <div id="div_save_continue" style="display: none;">
                                        <button type = "button" class ="button" name = "btn_save_continue" id = "btn_save_continue" onclick = "sync_save_and_continue();" style="margin-top: 10px; margin-left: 30%;">{$MOD.LBL_SAVE_AND_CONTINUE}</button>
                                        <br>
                                        <input type="hidden" name="row_contacts" id="row_contacts" value="{$FIELDSCONTACTSARRAY|@count}">
                                        
                                        {if $RECORDID eq ""}
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" id="tbl_field_mapping_for_contacts" style="margin-left: 15px;">
                                                <tbody>
                                                <tr id="tr_header_save_continiue"></tr>
                                                </tbody>
                                            </table>
                                        {else}
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" id="tbl_field_mapping_contacts_1" style="margin-left: 15px;">
                                        <tbody>
                                            <tr id="tr_header_save_continiue"></tr>
                                            {foreach from=$FIELDSCONTACTSARRAY key=fieldValue item=fieldName}
                                            {assign var=KEYS value=$fieldValue+1}
                                            <tr id="row{$KEYS}" class="fieldmappingrow">
                                                <td>
                                                    <button type='button' class='button btn_minus' value=''><span class='suitepicon suitepicon-action-minus'></span></button>
                                                </td>
                                                <td>
                                                    <select name='suitecrm_contacts_fields{$KEYS}' id='suitecrm_contacts_fields{$KEYS}' class='source_module_fields'>
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$SOURCEMODULECONTACTSFIELDS key=key item=value}
                                                            <option value="{$key}" {if $fieldName.suitecrmModuleField eq $key}selected{/if}>{$value}</option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td>
                                                {if $MODULEMAPPINGSOFTWARE eq 'SendGrid'}
                                                    <select name='sendgrid_contacts_fields{$KEYS}' id='sendgrid_contacts_fields{$KEYS}' class="target_module_fields">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$ESMODULECONTACTSFIELDS key=key item=value}
                                                            {foreach from=$value key=k item=v}
                                                            <option value="{$v}" {if $fieldName.sendgridModuleField eq $v}selected{/if}>{$v}</option>
                                                            {/foreach}    
                                                        {/foreach}
                                                    </select>
                                                {elseif $MODULEMAPPINGSOFTWARE eq 'ConstantContact'}
                                                    <select name='constant_contact_contacts_fields{$KEYS}' id='constant_contact_contacts_fields{$KEYS}' class="target_module_fields">
                                                        <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                        {foreach from=$ESMODULECONTACTSFIELDS key=key item=value}
                                                            {foreach from=$value key=k item=v}
                                                            <option value="{$v}" {if $fieldName.sendgridModuleField eq $v}selected{/if}>{$v}</option>
                                                            {/foreach}    
                                                        {/foreach}
                                                    </select>

                                                {elseif $MODULEMAPPINGSOFTWARE eq 'ActiveCampaigns'}
                                                <select name='active_campaigns_contacts_fields{$KEYS}' id='active_campaigns_contacts_fields{$KEYS}' class="target_module_fields">
                                                    <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                    {foreach from=$ESMODULECONTACTSFIELDS key=key item=value}
                                                        {foreach from=$value key=k item=v}
                                                        <option value="{$v}" {if $fieldName.sendgridModuleField eq $v}selected{/if}>{$v}</option>
                                                        {/foreach}    
                                                    {/foreach}
                                                </select>

                                                {elseif $MODULEMAPPINGSOFTWARE eq 'Mautic'}
                                                <select name='mautic_contacts_fields{$KEYS}' id='mautic_contacts_fields{$KEYS}' class="target_module_fields">
                                                    <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                    {foreach from=$ESMODULECONTACTSFIELDS key=key item=value}
                                                        {foreach from=$value key=k item=v}
                                                        <option value="{$v}" {if $fieldName.sendgridModuleField eq $v}selected{/if}>{$v}</option>
                                                        {/foreach}    
                                                    {/foreach}
                                                </select>

                                                {elseif $MODULEMAPPINGSOFTWARE eq 'SendInBlue'}
                                                <select name='sendinblue_contacts_fields{$KEYS}' id='sendinblue_contacts_fields{$KEYS}' class="target_module_fields">
                                                    <option value="">{$MOD.LBL_SELECT_AN_OPTION}</option>
                                                    {foreach from=$ESMODULECONTACTSFIELDS key=key item=value}
                                                        {foreach from=$value key=k item=v}
                                                        <option value="{$v}" {if $fieldName.sendgridModuleField eq $v}selected{/if}>{$v}</option>
                                                        {/foreach}    
                                                    {/foreach}
                                                </select>
                                                {/if}
                                            </td>
                                        </tr>
                                        {/foreach}
                                        </tbody>
                                        </table>
                                        <button type="button" class="button" id="btn_add_mapping_for_contacts" onclick = "add_mapping_contacts();" style="margin-top: 10px;">{$MOD.LBL_ADD_MAPPING}</button>
                                        {/if}
                                        <button type="button" class="button" id="btn_add_mapping_for_contacts" onclick = "add_mapping_contacts();" style="margin-top: 10px; display: none;">{$MOD.LBL_ADD_MAPPING}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="step3" style="display:none;">
                        <div class="template-panel">
                            <div class="template-panel-container panel">
                                <div class="template-container-full">
                                    <div class="conditionBlockBorder">
                                        <span id="conditionLinesSpan">
                                            <div id="conditionBorder">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                            <th colspan="4">
                                                                <h4 class="header-4">{$MOD.LBL_APPLY_CONDITION}</h4>
                                                            </th>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <table id="allCondition" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr><th colspan="4"><h4 class="header-4">{$MOD.LBL_EMS_ALL_CONDITIONS}<span class="selectedModuleName">{$MODULE_LABEL}</span></h4></th></tr>
                                                    </tbody>
                                                </table>
                                                <span class="conditionMessage">{$MOD.LBL_EMS_ALL_CONDITIONS_MESSAGE}</span>
                                                <br/><br/>

                                                {if $EMS_ALL_CONDITION_DATA eq ''}
                                                    <script type="text/javascript" src="custom/modules/Administration/js/VIEMSConditionLine.js?v={$RANDOM_NUMBER}"></script>
                                                    
                                                    <table id="aowAllConditionLines" width="100%" cellspacing="4" border="0">
                                                    </table>
                                                    <div class="allConditionButtonDiv">
                                                        <input tabindex="116" class="button" value="{$MOD.LBL_ADD_CONDITIONS}" id="btnAllConditionLine" onclick="insertEMSConditionLine('All')" type="button">
                                                    </div>
                                                {else} 
                                                    {$EMS_ALL_CONDITION_DATA} 
                                                {/if}
                                            </div>

                                            <br>
                                            <label style="margin: 7px;">{$MOD.LBL_EMS_CONDITIONAL_OPERATOR}</label>
                                            <select name="conditionalOperator" id="conditionalOperator">
                                                <option value="AND" {if $CONDITIONAL_OPERATOR eq 'AND'} selected="selected" {/if}>{$MOD.LBL_AND}</option>
                                                <option value="OR" {if $CONDITIONAL_OPERATOR eq 'OR'} selected="selected" {/if}>{$MOD.LBL_OR}</option>
                                            </select>
                                            <br><br>

                                            <div id="conditionBorder">
                                                <table id="anyCondition" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tbody>
                                                        <tr>
                                                        <th colspan="4"><h4 class="header-4">{$MOD.LBL_EMS_ANY_CONDITIONS}<span class="selectedModuleName">{$MODULE_LABEL}</span></h4></th>
                                                        </tr> 
                                                    </tbody>
                                                </table>
                                                <span class="conditionMessage">{$MOD.LBL_EMS_ANY_CONDITIONS_MESSAGE}</span>
                                                <br/><br/>
                                                  
                                                {if $EMS_ANY_CONDITION_DATA eq ''}
                                                    <table id="aowAnyConditionLines" width="100%" cellspacing="4" border="0">
                                                    </table>
                                                    <div class="anyConditionButtonDiv">
                                                        <input tabindex="116" class="button" value="{$MOD.LBL_ADD_CONDITIONS}" id="btnAnyConditionLine" onclick="insertEMSConditionLine('Any')" type="button">
                                                    </div>
                                                {else} 
                                                    {$EMS_ANY_CONDITION_DATA} 
                                                {/if}
                                            </div>

                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </td>
        </tr>
    </table>
</form>
</body>
</html>

{literal}
    <script type="text/javascript">
        var listviewMaxRecord = "{/literal}{$LISTVIEW_MAX_RECORD}{literal}";
        var listViewUrl = "{/literal}{$LISTVIEWURL}{literal}";
        var id = "{/literal}{$RECORDID}{literal}";

        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = "custom/modules/Administration/js/VIEmailSoftwareIntegration.js?v="+Math.random();
        document.body.appendChild(script); 
    </script>
{/literal}