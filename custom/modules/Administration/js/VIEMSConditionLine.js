 
var allCondln = allCondlnCount = anyCondln = anyCondlnCount = 0;
var flowFields =  new Array();
var flowModule ='';
var conditionOperators = ['is_null', 'is_not_null', 'today', 'tomorrow', 'yesterday', 'is_in_last_7_days', 'is_in_last_30_days', 'is_in_last_60_days', 'is_in_last_90_days', 'is_in_last_120_days', 'is_in_this_week', 'is_in_the_last_week', 'is_in_this_month', 'is_in_the_last_month'];

$('#suitecrm_module').on('change',function(){
    flowModule = $('#suitecrm_module').text();
    showEMSModuleFields();
});//end of function

//Coniditions Function 
function loadEMSConditionLine(condition, conditionVal, conditionType){
    var ln = 0;
    ln = insertEMSConditionLine(conditionType);
    
    if(conditionType == 'All'){
        conditionFieldId = 'aowAllConditionsField';
        conditionFieldLabel = 'aowAllConditionsFieldLabel';
        conditionModulePath = 'aowAllConditionsModulePath';
        conditionModulePathLabel = 'aowAllConditionsModulePathLabel';
    }else{
        conditionFieldId = 'aowAnyConditionsField';
        conditionFieldLabel = 'aowAnyConditionsFieldLabel';
        conditionModulePath = 'aowAnyConditionsModulePath';
        conditionModulePathLabel = 'aowAnyConditionsModulePathLabel';
    }//end of else
    var select_field = document.getElementById(conditionFieldId+ln);
    document.getElementById(conditionFieldLabel+ln).innerHTML = select_field.options[select_field.selectedIndex].text;

    document.getElementById(conditionModulePath+ln).disabled = true;
    var select_field2 = document.getElementById(conditionModulePath+ln);
    document.getElementById(conditionModulePathLabel+ln).innerHTML = select_field2.options[select_field2.selectedIndex].text;

    if (conditionVal instanceof Array) {
        conditionVal = JSON.stringify(conditionVal)
    }//end of if
   
    document.getElementById(conditionFieldId+ln).value = condition['field'];
    showEMSModuleField(conditionType, ln, condition['operator'], condition['value_type'], conditionVal);
}//end of function

function showEMSModuleField(conditionType, ln, operatorValue, typeValue, fieldValue){
    if (typeof operatorValue === 'undefined') { operatorValue = ''; }
    if (typeof typeValue === 'undefined') { typeValue = ''; }
    if (typeof fieldValue === 'undefined') { fieldValue = ''; }

    if(conditionType == 'All'){
        var conditionModulePathId = 'aowAllConditionsModulePath';
        var conditionFieldId = 'aowAllConditionsField';
        var conditionOperatorInputId = 'aowAllConditionsOperatorInput';
        var conditionFieldTypeId = 'aowAllConditionsFieldTypeInput';
        var conditionsFieldInputId = 'aowAllConditionsFieldInput';

        var aowOperatorName = "aowAllConditionsOperator["+ln+"]";
        var aowFieldTypeName = "aowAllConditionsValueType["+ln+"]";
        var aowFieldName = "aowAllConditionsValue["+ln+"]";
    }else if(conditionType == 'Any'){
        var conditionModulePathId = 'aowAnyConditionsModulePath';
        var conditionFieldId = 'aowAnyConditionsField';
        var conditionOperatorInputId = 'aowAnyConditionsOperatorInput';
        var conditionFieldTypeId = 'aowAnyConditionsFieldTypeInput';
        var conditionsFieldInputId = 'aowAnyConditionsFieldInput';

        var aowOperatorName = "aowAnyConditionsOperator["+ln+"]";
        var aowFieldTypeName = "aowAnyConditionsValueType["+ln+"]";
        var aowFieldName = "aowAnyConditionsValue["+ln+"]";
    }//end of else

    var relField = document.getElementById(conditionModulePathId+ln).value;
    var aowField = document.getElementById(conditionFieldId+ln).value;

    if(aowField != ''){
        var callback = {
            success: function(result) {
                document.getElementById(conditionOperatorInputId+ln).innerHTML = result.responseText;
                SUGAR.util.evalScript(result.responseText);
                document.getElementById(conditionOperatorInputId+ln).onchange = function(){changeEMSOperator(conditionType, ln);};

            },//end of success
            failure: function(result) {
                document.getElementById(conditionOperatorInputId+ln).innerHTML = '';
            }//end of failure
        }//end of callback

        var callback2 = {
            success: function(result) {
                document.getElementById(conditionFieldTypeId+ln).innerHTML = result.responseText;
                SUGAR.util.evalScript(result.responseText);
                document.getElementById(conditionFieldTypeId+ln).onchange = function(){showEMSModuleFieldType(conditionType, ln);};
            },//end of success
            failure: function(result) {
                document.getElementById(conditionFieldTypeId+ln).innerHTML = '';
            }//end of failure
        }//end of callback2

        var callback3 = {
            success: function(result) {
                document.getElementById(conditionsFieldInputId+ln).innerHTML = result.responseText;
                SUGAR.util.evalScript(result.responseText);
                enableQS(true);
            },//end of success
            failure: function(result) {
                document.getElementById(conditionsFieldInputId+ln).innerHTML = '';
            }//end of failure
        }//end of callback3
        
        YAHOO.util.Connect.asyncRequest ("GET", "index.php?entryPoint=VIEMSModuleOperatorField&view="+action_sugar_grp1+"&aow_module="+flowModule+"&aow_fieldname="+aowField+"&aow_newfieldname="+aowOperatorName+"&aow_value="+operatorValue+"&rel_field="+relField, callback);

        YAHOO.util.Connect.asyncRequest ("GET", "index.php?entryPoint=VIEMSFieldTypeOptions&view="+action_sugar_grp1+"&aow_module="+flowModule+"&aow_fieldname="+aowField+"&aow_newfieldname="+aowFieldTypeName+"&aow_value="+typeValue+"&rel_field="+relField, callback2);       
        
        YAHOO.util.Connect.asyncRequest ("GET", "index.php?entryPoint=VIEMSModuleFieldType&view="+action_sugar_grp1+"&aow_module="+flowModule+"&aow_fieldname="+aowField+"&aow_newfieldname="+aowFieldName+"&aow_value="+fieldValue+"&aow_type="+typeValue+"&rel_field="+relField+"&filename=2", callback3);
    } else {
        document.getElementById(conditionOperatorInputId+ln).innerHTML = ''
        document.getElementById(conditionFieldTypeId+ln).innerHTML = '';
        document.getElementById(conditionsFieldInputId+ln).innerHTML = '';
    }//end of else

    if(jQuery.inArray(operatorValue,conditionOperators) != -1){
        hideEMSElem(conditionFieldTypeId + ln);
        hideEMSElem(conditionsFieldInputId + ln);
    }else{
        showEMSElem(conditionFieldTypeId + ln);
        showEMSElem(conditionsFieldInputId + ln);
    }//end of else
}//end of function

function showEMSModuleFields(){
    clearEMSConditionLines();

    flowModule = $('#suitecrm_module').val();
    if(flowModule != ''){
        var callback = {
            success: function(result) {
                flowRelModules = result.responseText;
            }//end of success
        }//end of callback
        var callback2 = {
            success: function(result) {
                flowFields = result.responseText;
                document.getElementById('btnAllConditionLine').disabled = '';
                document.getElementById('btnAnyConditionLine').disabled = '';
            }//end of success
        }//end of callback2
        
        YAHOO.util.Connect.asyncRequest ("GET", "index.php?entryPoint=VIEMSModuleRelationships&aow_module="+flowModule, callback);
        YAHOO.util.Connect.asyncRequest ("GET", "index.php?entryPoint=VISuiteCRMModuleFields&view=EditView&moduleName="+flowModule+"&stepName=stepThree", callback2);
    }//end of if 
}//end of function

function showEMSModuleFieldType(conditionType, ln, value){
    if (typeof value === 'undefined') { value = ''; }
    if(conditionType == 'All'){
        conditionFieldInputId = 'aowAllConditionsFieldInput';
        conditionModulePathId = 'aowAllConditionsModulePath';
        conditionFieldId = 'aowAllConditionsField';
        conditionValueTypeId = 'aowAllConditionsValueType';
        var aowFieldName = "aowAllConditionsValue["+ln+"]";
    }else{
        conditionFieldInputId = 'aowAnyConditionsFieldInput';
        conditionModulePathId = 'aowAnyConditionsModulePath';
        conditionFieldId = 'aowAnyConditionsField';
        conditionValueTypeId = 'aowAnyConditionsValueType';
        var aowFieldName = "aowAnyConditionsValue["+ln+"]";
    }//end of else
    var callback = {
        success: function(result) {
            document.getElementById(conditionFieldInputId+ln).innerHTML = result.responseText;
            SUGAR.util.evalScript(result.responseText);
            enableQS(false);
        },//end of success
        failure: function(result) {
            document.getElementById(conditionFieldInputId+ln).innerHTML = '';
        }//end of failure
    }//end of function

    var relField = document.getElementById(conditionModulePathId+ln).value;
    var aowField = document.getElementById(conditionFieldId+ln).value;
    var typeValue = document.getElementById(conditionValueTypeId+"["+ln+"]").value;

    YAHOO.util.Connect.asyncRequest ("GET", "index.php?entryPoint=VIEMSModuleFieldType&view="+action_sugar_grp1+"&aow_module="+flowModule+"&aow_fieldname="+aowField+"&aow_newfieldname="+aowFieldName+"&aow_value="+value+"&aow_type="+typeValue+"&rel_field="+relField+"&filename=2", callback);
}//end of function

function insertEMSConditionHeader(conditionType){
    tablehead = document.createElement("thead");

    if(conditionType == 'All'){
        tableHeadId = "allConditionLinesHead";
        tableId = "aowAllConditionLines";
    }else{
        tableHeadId = "anyConditionLinesHead";
        tableId = "aowAnyConditionLines";
    }//end of else

    tablehead.id = tableHeadId;
    document.getElementById(tableId).appendChild(tablehead);

    var x = tablehead.insertRow(-1);
    var a = x.insertCell(0);
    
    var b = x.insertCell(1);
    b.style.color = "rgb(0,0,0)";
    b.innerHTML = SUGAR.language.get('Administration', 'LBL_MODULE_PATH');

    var c = x.insertCell(2);
    c.style.color = "rgb(0,0,0)";
    c.innerHTML = SUGAR.language.get('Administration', 'LBL_FIELD');

    var d = x.insertCell(3);
    d.style.color = "rgb(0,0,0)";
    d.innerHTML = SUGAR.language.get('Administration', 'LBL_OPERATOR');

    var e = x.insertCell(4);
    e.style.color = "rgb(0,0,0)";
    e.innerHTML = SUGAR.language.get('Administration', 'LBL_VALUE_TYPE');

    var f = x.insertCell(5);
    f.style.color = "rgb(0,0,0)";
    f.innerHTML = SUGAR.language.get('Administration', 'LBL_VALUE');
}//end of function

function insertEMSConditionLine(conditionType){

    if(conditionType == 'All'){
        var rowc  = $("#aowAllConditionLines > tbody > tr").length;
        var visibleRowCnt = $("#aowAllConditionLines > tbody > tr:visible").length;
        allCondln = rowc;

        tableId = 'aowAllConditionLines';
        tableHeadId = 'allConditionLinesHead';
        tableBodyId = 'aowAllConditionsBody' + allCondln;
        productLineId = 'aowAllProductLine' + allCondln;
        deleteLineId = 'aowAllConditionsDeleteLine' + allCondln;
        deleteOnclickFunction = 'markEMSConditionLineDeleted("All", '+allCondln+')';
        deleteConditionsDeletedName = 'aowAllConditionsDeleted[' + allCondln + ']';
        deleteConditionsDeletedId = 'aowAllConditionsDeleted'+ allCondln;
        conditionsName = 'aowAllConditionsId[' + allCondln + ']';
        conditionsId = 'aowAllConditionsId'+ allCondln;
        modulePathName = 'aowAllConditionsModulePath['+ allCondln +'][0]';
        modulePathId = 'aowAllConditionsModulePath'+ allCondln;
        modulePathLabel = 'aowAllConditionsModulePathLabel'+ allCondln;
        conditionsFieldName = 'aowAllConditionsField['+allCondln+']';
        conditionsFieldId = 'aowAllConditionsField'+allCondln;
        fieldOnchangeFunction = 'showEMSModuleField("'+conditionType+'", '+allCondln+')';
        conditionsFieldLabel = 'aowAllConditionsFieldLabel'+allCondln;
        conditionsOperatorId = 'aowAllConditionsOperatorInput'+allCondln;
        conditionsFieldTypeId = 'aowAllConditionsFieldTypeInput'+allCondln;
        conditionsFieldInputId = 'aowAllConditionsFieldInput'+allCondln;

    }else{
        var rowc  = $("#aowAnyConditionLines > tbody > tr").length;
        var visibleRowCnt = $("#aowAnyConditionLines > tbody > tr:visible").length;
        anyCondln = rowc;

        tableId = 'aowAnyConditionLines';
        tableHeadId = 'anyConditionLinesHead';
        tableBodyId = 'aowAnyConditionsBody' + anyCondln;
        productLineId = 'aowAnyProductLine' + anyCondln;
        deleteLineId = 'aowAnyConditionsDeleteLine' + anyCondln;
        deleteOnclickFunction = 'markEMSConditionLineDeleted("Any", '+anyCondln+')';
        deleteConditionsDeletedName = 'aowAnyConditionsDeleted[' + anyCondln + ']';
        deleteConditionsDeletedId = 'aowAnyConditionsDeleted'+ anyCondln;
        conditionsName = 'aowAnyConditionsId[' + anyCondln + ']';
        conditionsId = 'aowAnyConditionsId'+ anyCondln;
        modulePathName = 'aowAnyConditionsModulePath['+ anyCondln +'][0]';
        modulePathId = 'aowAnyConditionsModulePath'+ anyCondln;
        modulePathLabel = 'aowAnyConditionsModulePathLabel'+ anyCondln;
        conditionsFieldName = 'aowAnyConditionsField['+anyCondln+']';
        conditionsFieldId = 'aowAnyConditionsField'+anyCondln;
        fieldOnchangeFunction = 'showEMSModuleField("'+conditionType+'", '+anyCondln+')';
        conditionsFieldLabel = 'aowAnyConditionsFieldLabel'+anyCondln;
        conditionsOperatorId = 'aowAnyConditionsOperatorInput'+anyCondln;
        conditionsFieldTypeId = 'aowAnyConditionsFieldTypeInput'+anyCondln;
        conditionsFieldInputId = 'aowAnyConditionsFieldInput'+anyCondln;
    }//end of else

    if (document.getElementById(tableHeadId) == null) {
        insertEMSConditionHeader(conditionType);
    } else {
        document.getElementById(tableHeadId).style.display = '';
    }//end of else

    tablebody = document.createElement("tbody");
    tablebody.id = tableBodyId;
    document.getElementById(tableId).appendChild(tablebody);

    var x = tablebody.insertRow(-1);
    x.id = productLineId;
    
    var a = x.insertCell(0);
    if(action_sugar_grp1 == 'vi_modulemappingeditview'){
       a.innerHTML = "<button type='button' id='"+deleteLineId+"' class='button' value='' tabindex='116' onclick='"+deleteOnclickFunction+"'><span class='suitepicon suitepicon-action-minus'></span></button><br>";
        a.innerHTML += "<input type='hidden' name='"+deleteConditionsDeletedName+"' id='"+deleteConditionsDeletedId+"' value='0'><input type='hidden' name='"+conditionsName+"' id='"+conditionsId+"' value=''>";
    }//end of if

    var b = x.insertCell(1);
    var viewStyle = 'display:none';
    if(action_sugar_grp1 == 'vi_modulemappingeditview'){viewStyle = '';}
    b.innerHTML = "<select name='"+modulePathName+"' id='"+modulePathId+"' value='' tabindex='116' disabled = 'disabled'>" + flowRelModules + "</select>";
    if(action_sugar_grp1 == 'vi_modulemappingeditview'){viewStyle = 'display:none';}else{viewStyle = '';}
    b.innerHTML += "<span id='"+modulePathLabel+"' style='display:none;'></span>";
    
    var c = x.insertCell(2);
    viewStyle = 'display:none';
    if(action_sugar_grp1 == 'vi_modulemappingeditview'){viewStyle = '';}
    c.innerHTML = "<select name='"+conditionsFieldName+"' id='"+conditionsFieldId+"' value='' tabindex='116' onchange='"+fieldOnchangeFunction+"' class='"+conditionType+"FieldClass'>" + flowFields + "</select>";
    if(action_sugar_grp1 == 'vi_modulemappingeditview'){viewStyle = 'display:none';}else{viewStyle = '';}
    c.innerHTML += "<span id='"+conditionsFieldLabel+"' style='display:none;'></span>";

    var d = x.insertCell(3);
    d.id = conditionsOperatorId;

    var e = x.insertCell(4);
    e.id = conditionsFieldTypeId;

    var f = x.insertCell(5);
    f.id = conditionsFieldInputId;

    if(conditionType == 'All'){
        allCondln++;
        allCondlnCount = visibleRowCnt
        allCondlnCount++;
    }else{
        anyCondln++;
        anyCondlnCount = visibleRowCnt
        anyCondlnCount++;
    }//end of else

    $('.edit-view-field #'+tableId).find('tbody').last().find('select').change(function () {
        $(this).find('td').last().removeAttr("style");
        $(this).find('td').height($(this).find('td').last().height() + 8);
    });//end of function

    if(conditionType == 'All'){
        return allCondln -1;
    }else{
        return anyCondln -1;
    }//end of else
}//end of function

function changeEMSOperator(conditionType, ln){
    if(conditionType == 'All'){
        var aowOperator = document.getElementById("aowAllConditionsOperator["+ln+"]").value;
    }else{
        var aowOperator = document.getElementById("aowAnyConditionsOperator["+ln+"]").value;
    }//end of else
    
    if(jQuery.inArray(aowOperator, conditionOperators) != -1){
        showEMSModuleField(conditionType, ln, aowOperator);
    }else{
        if(conditionType == 'All'){
            showEMSElem('aowAllConditionsFieldTypeInput' + ln);
            showEMSElem('aowAllConditionsFieldInput' + ln);
        }else{
            showEMSElem('aowAnyConditionsFieldTypeInput' + ln);
            showEMSElem('aowAnyConditionsFieldInput' + ln);
        }//end of else
    }//end of else
}//end of function

function markEMSConditionLineDeleted(conditionType, ln) {
    // collapse line; update deleted value
    if(conditionType == 'All'){
        document.getElementById('aowAllConditionsBody' + ln).style.display = 'none';
        document.getElementById('aowAllConditionsDeleted' + ln).value = '1';
        document.getElementById('aowAllConditionsDeleteLine' + ln).onclick = '';

        allCondlnCount--;
        if(allCondlnCount == 0){
            document.getElementById('allConditionLinesHead').style.display = "none";
        }//end of if
    }else{
        document.getElementById('aowAnyConditionsBody' + ln).style.display = 'none';
        document.getElementById('aowAnyConditionsDeleted' + ln).value = '1';
        document.getElementById('aowAnyConditionsDeleteLine' + ln).onclick = '';

        anyCondlnCount--;
        if(anyCondlnCount == 0){
            document.getElementById('anyConditionLinesHead').style.display = "none";
        }//end of if 
    }//end of else
}//end of function

function clearEMSConditionLines(){

    if(document.getElementById('aowAllConditionLines') != null){
        var condRows = document.getElementById('aowAllConditionLines').getElementsByTagName('tr');
        var condRowLength = condRows.length;
        var i;
        for (i=0; i < condRowLength; i++) {
            if(document.getElementById('aowAllConditionsDeleteLine'+i) != null){
                document.getElementById('aowAllConditionsDeleteLine'+i).click();
            }//end of if
        }//end of for
    }//end of if

    if(document.getElementById('aowAnyConditionLines') != null){
        var condRows = document.getElementById('aowAnyConditionLines').getElementsByTagName('tr');
        var condRowLength = condRows.length;
        var i;
        for (i=0; i < condRowLength; i++) {
            if(document.getElementById('aowAnyConditionsDeleteLine'+i) != null){
                document.getElementById('aowAnyConditionsDeleteLine'+i).click();
            }//end of if
        }//end of for
    }//end of if
}//end of function

function hideEMSElem(id){
    if(document.getElementById(id)){
        document.getElementById(id).style.display = "none";
        document.getElementById(id).value = "";
    }//end of if
}//end of function

function showEMSElem(id){
    if(document.getElementById(id)){
        document.getElementById(id).style.display = "";
    }//end of if
}//end of function

function emailSoftwareIntegrationDateFieldChange(field){
    if(document.getElementById(field + '[1]').value == 'now'){
        hideEMSElem(field + '[2]');
        hideEMSElem(field + '[3]');
    } else {
        showEMSElem(field + '[2]');
        showEMSElem(field + '[3]');
    }//end of else
}//end of function