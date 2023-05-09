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
        <h2 class="module-title-text">{$MOD.LBL_CAP_MODULE_MAPPING}</h2>
        <div class="clear"></div>
    </div>
    {if $NUMBEROFROWS neq 0}
    <div>
        <table style = "margin-top:20px; width: 100%">
            <tr>
                <td style ="float: left;"><input type="button" name="add_new" value="{$MOD.LBL_ADD_NEW}" class="button" onclick="location.href = '{$EDITVIEWURL}';"></td>
                <td style="float: right;"><input type="button" name="btn_back" value="{$MOD.LBL_BACK}" class="button" onclick="location.href = '{$WIDGETURL}';"></td>
            </tr>
        </table>
    </div>
    <div class="list-view-rounded-corners">
        <table class="list view table-responsive">
            <thead>
                <th class="td_alt"><input type="checkbox" id="select_all"></th>
                <th class="td_alt quick_view_links"></th>
                <th scope="col" data-toggle="true">
                    <div>
                        <a class="listViewThLinkS1" href="#">{$MOD.LBL_TITLE}</a>
                    </div>
                </th>
                <th scope="col" data-toggle="true">
                    <div>
                        <a class="listViewThLinkS1" href="#">{$MOD.LBL_SOFTWARE}</a>
                    </div>
                </th>
                <th scope="col" data-toggle="true">
                    <div>
                        <a class="listViewThLinkS1" href="#">{$MOD.LBL_STATUS}</a>
                    </div>
                </th>
            </thead>
            <tbody>
                {foreach from=$FINALMODULEMAPPINGDATA key=key item=value}
                <tr class="oddListRowS1" data-id="" height="20">
                    <td><input title="Select this row" class="listview-checkbox" name="mass[]" id="mass[]" value="{$value.module_mapping_id}" type="checkbox"></td>
                    <td><a class="edit-link" title="Edit" id="" href="{$EDITVIEWURL}&records={$value.module_mapping_id}"><img src="themes/suite8/images/edit_inline.png"> </a></td>
                    <td>{$value.title}</td>
                    <td>{$value.email_software}</td>
                    <td>{$value.status}</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <table style = "margin-top:20px;">
        <tr>
            <td><input type="button" name="btn_delete" value="{$MOD.LBL_DELETE}" id="btn_delete" class="button"></td>
        </tr>
    </table>
    {else}
    <div class="list view listViewEmpty">
        <br>
        <p class="msg">{$MOD.LBL_CREATE_MESSAGE}<a href="index.php?module=Administration&action=vi_modulemappingeditview">{$MOD.LBL_CREATE}</a>{$MOD.LBL_CREATE_MESSAGE_ONE}</p>
    </div>
    {/if}
    </div>

<script type="text/javascript">
{literal}
//delete 
$('#btn_delete').on('click', function(e) {
  var id = [];
  $(".listview-checkbox:checked").each(function() {
    id.push($(this).val());
  });
  if(id.length <=0) { 
    var selectRecordAlert = SUGAR.language.get("app_strings",'LBL_SELECT_RECORDS');
    alert(selectRecordAlert); 
  }else {
    var sureToDeleteAlert = SUGAR.language.get("app_strings",'LBL_SURE_TO_DELETE');
    var rowAlert = SUGAR.language.get("app_strings",'LBL_ROW');
    var thisLable = SUGAR.language.get("Administration",'LBL_THIS');
    var theseLable = SUGAR.language.get('Administration','LBL_THESE');
    var msg = sureToDeleteAlert+" "+(id.length>1?theseLable:thisLable)+" "+rowAlert;
    var checked = confirm(msg);
    var listViewUrl = "{/literal}{$LISTVIEWURL}{literal}";
    if(checked == true){
      var selected_values = id.join(",");
      $.ajax({
          type: "POST",
          url: "index.php?entryPoint=VIDeleteModuleMapping",
          data: 'del_id='+selected_values,
          success: function(response) {
            window.location.href = listViewUrl;
          }
      });
    }
  }
});

//select all checkbox
$('#select_all').click(function(event) {   
  if(this.checked) {
      // Iterate each checkbox
      $(':checkbox').each(function() {
          this.checked = true;                        
      });
  } else {
      $(':checkbox').each(function() {
          this.checked = false;                       
      });
  }
});
{/literal}
</script>