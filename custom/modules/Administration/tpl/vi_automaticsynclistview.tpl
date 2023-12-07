{*
 
*}

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="custom/modules/Administration/css/VIIntegrationCss.css">
    </head>
    <div class="moduleTitle">
        <h2 class="module-title-text">{$MOD.LBL_CAP_AUTO_SYNCHRONIZE}</h2>
        <div class="clear"></div>
    </div>
    {if $NUMBEROFROWS neq 0}
    <div>
        <table style = "margin-top:20px; width: 100%">
            <tr>
                <td style ="float: left;"><input type="button" name="add_new" value="{$MOD.LBL_ADD_NEW}" class="button" onclick="location.href = '{$EDITVIEWURL}';" ></td>
                <td style="float: right;"><input type="button" name="btn_back" value="{$MOD.LBL_BACK}" class="button" onclick="location.href = '{$WIDGETURL}';" ></td>
            </tr>
        </table>
    </div>
    <div class="list-view-rounded-corners">
        <table class="list view table-responsive">
            <thead>
                <th class="td_alt"><input type="checkbox" id="select_all"></th>
                <th class="td_alt quick_view_links"></th>
                <th>{$MOD.LBL_SUITECRM_TO_EMS_STATUS} {$MOD.LBL_STATUS}</th>
                <th>{$MOD.LBL_EMS_TO_SUITECRM_STATUS} {$MOD.LBL_STATUS}</th>
                <th class="" scope="col" data-toggle="true">
                    <div>
                        <a class="listViewThLinkS1" href="#">{$MOD.LBL_SELECT_SOFTWARE}</a>
                    </div>
                </th>
                <th class="" scope="col" data-toggle="true">
                    <div>
                        <a class="listViewThLinkS1" href="#">{$MOD.LBL_MAPPING_MODULE}</a>
                    </div>
                </th>
            </thead>
            <tbody>
                {foreach from=$FINALAUTOMATICSYNCDATA key=key item=value}
                    <tr class="oddListRowS1" height="20" data-id="{$value.vi_automatic_sync_id}" data-software="{$value.sync_software}">
                        <td><input title="Select this row" class="listview-checkbox" name="mass[]" id="mass[]" value="{$value.vi_automatic_sync_id}" type="checkbox"></td>

                        <td><a class="edit-link" title="Edit" href="{$EDITVIEWURL}&records={$value.vi_automatic_sync_id}"><img src="themes/suite8/images/edit_inline.png"> </a></td>

                        <td field="status">
                          <label class="switch marginLeft">
                            <input type="checkbox" id="statusAction" value="{$value.status}" {if $value.status eq 1} checked{/if}>
                            <span class="slider round"></span>
                          </label>
                        </td>

                        <td field="emsToSuiteStatus">
                          <label class="switch marginLeft">
                            <input type="checkbox" id="emsToSuiteStatusAction" value="{$value.autoSyncEMSToSuite}" {if $value.autoSyncEMSToSuite eq 1} checked{/if}>
                            <span class="slider round"></span>
                          </label>
                        </td>

                        <td id="syncSoftware">{$value.sync_software}</td>

                        <td>
                            {foreach from=$value.moduleMappingVal key=k item=v}
                                {$v}<br />
                            {/foreach}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <div>
        <table style = "margin-top:20px;">
            <tr>
                <td><input type="button" name="btn_delete" value="{$MOD.LBL_DELETE}" id="btn_delete" class="button"></td>
            </tr>
        </table>
    {else}
        <div class="list view listViewEmpty">
            <br>
            <p class="msg">{$MOD.LBL_CREATE_MESSAGE}<a href="index.php?module=Administration&action=vi_automaticsynceditview">{$MOD.LBL_CREATE}</a>{$MOD.LBL_CREATE_MESSAGE_ONE}</p>
        </div>
    {/if}
    </div>
    
{literal}
    <script type="text/javascript" src="custom/modules/Administration/js/VIEmailSoftwareIntegration.js"></script> 
{/literal}
</html>