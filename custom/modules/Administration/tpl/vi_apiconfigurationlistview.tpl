{*
 
*}
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="custom/modules/Administration/css/VIIntegrationCss.css">
  </head>

  <div class="moduleTitle">
    <h2 class="module-title-text">{$MOD.LBL_CAP_API_CONFIGURATION}</h2>
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
          <th class="" scope="col" data-toggle="true">
            <div>
              <a class="listViewThLinkS1" href="#">{$MOD.LBL_TITLE}</a>
            </div>
          </th>
          <th class="" scope="col" data-toggle="true">
            <div>
              <a class="listViewThLinkS1" href="#">{$MOD.LBL_SOFTWARE}</a>
            </div>
          </th>
        </thead>

        <tbody>
          {foreach from=$FINALCONFIGDATA key=key item=value}
            <tr class="oddListRowS1" data-email-software="{$value.email_software}" height="20">
              <td>
                <input title="Select this row" class="listview-checkbox" name="mass[]" id="mass[]" value="{$value.id}" type="checkbox">
              </td>
              <td>
                <a class="edit-link" title="Edit" id="" href="{$EDITVIEWURL}&records={$value.id}"><img src="themes/suite8/images/edit_inline.png"></a>
              </td>
              <td>{$value.title}</td>
              <td>{$value.email_software}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <table style = "margin-top:20px;">
        <tr>
          <td><input type="button" name="btn_delete" value="{$MOD.LBL_DELETE}" id="btn_delete" class="button"></td>
        </tr>
      </table>
    </div>
  {else}
    <div class="list view listViewEmpty">
      <br>
      <p class="msg">{$MOD.LBL_CREATE_MESSAGE}<a href="index.php?module=Administration&action=vi_apiconfigurationeditview">{$MOD.LBL_CREATE}</a>{$MOD.LBL_CREATE_MESSAGE_ONE}</p>
    </div>
  {/if}
  <div class="clear"></div>

<script type="text/javascript">
  {literal}
    //delete 
    $('#btn_delete').on('click', function(e) {
      var selectRecordsAlert = SUGAR.language.get("app_strings",'LBL_SELECT_RECORDS');
      var id = [];
      var emailSoftware = [];
      $(".listview-checkbox:checked").each(function() {
        id.push($(this).val());
        var software = $(this).closest('tr').attr('data-email-software');
        emailSoftware.push(software);
      });

      if(id.length <=0) { 
        alert(selectRecordsAlert); 
      }else {
        var sureToDeleteAlert = SUGAR.language.get("app_strings",'LBL_SURE_TO_DELETE');
        var row = SUGAR.language.get('app_strings','LBL_ROW');
        var thisLable = SUGAR.language.get("Administration",'LBL_THIS');
        var theseLable = SUGAR.language.get('Administration','LBL_THESE');

        var selectedEmailSoftware = emailSoftware.join(", ");
        var msg = sureToDeleteAlert+ " " +(id.length>1?theseLable:thisLable)+" "+row+"\n\n"+SUGAR.language.get('Administration', 'LBL_DELETE_API_CONFIG')+' '+selectedEmailSoftware+' '+SUGAR.language.get("Administration", 'LBL_DELETE_API_CONFIG_MESSAGE');        
        var checked = confirm(msg);

        if(checked == true){
          var selected_values = id.join(",");
          var listViewUrl = "{/literal}{$LISTVIEWURL}{literal}";
          $.ajax({
            type: "POST",
            url: "index.php?entryPoint=VIDeleteApiConfiguration",
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