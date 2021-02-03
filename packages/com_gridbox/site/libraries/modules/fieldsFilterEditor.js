/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.fieldsFilterEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#fields-filter-settings-dialog .active').removeClass('active');
    $g('#fields-filter-settings-dialog a[href="#fields-filter-general-options"]').parent().addClass('active');
    $g('#fields-filter-general-options').addClass('active');
    setPresetsList($g('#fields-filter-settings-dialog'));
    $g('#fields-filter-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#fields-filter-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#fields-filter-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#fields-filter-settings-dialog .class-suffix').val(app.edit.suffix);
    $g('#fields-filter-settings-dialog .fields-filter-app-select input[type="hidden"]').val(app.edit.app);
    value = $g('#fields-filter-settings-dialog .fields-filter-app-select li[data-value="'+app.edit.app+'"]').text().trim();
    $g('#fields-filter-settings-dialog .fields-filter-app-select input[readonly]').val(value);
    $g('#fields-filter-settings-dialog .items-filter-layout-select input[type="hidden"]').val(app.edit.layout);
    value = $g('#fields-filter-settings-dialog .items-filter-layout-select li[data-value="'+app.edit.layout+'"]').text().trim();
    $g('#fields-filter-settings-dialog .items-filter-layout-select input[readonly]').val(value);
    if (!('auto' in app.edit)) {
        app.edit.auto = false;
    }
    if (!('collapsible' in app.edit)) {
        app.edit.collapsible = false;
    }
    $g('#fields-filter-settings-dialog input[type="checkbox"][data-option="auto"]').prop('checked', app.edit.auto);
    $g('#fields-filter-settings-dialog input[type="checkbox"][data-option="collapsible"]').prop('checked', app.edit.collapsible);
    checkFieldsFilterLayout();
    value = app.getValue('background', 'color');
    updateInput($g('#fields-filter-settings-dialog input[data-option="color"][data-group="background"]'), value);
    value = app.getValue('margin', 'top');
    $g('#fields-filter-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#fields-filter-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'top');
    $g('#fields-filter-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#fields-filter-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#fields-filter-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#fields-filter-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    for (var key in app.edit.desktop.border) {
        var input = $g('#fields-filter-settings-dialog input[data-option="'+key+'"][data-group="border"]');
        value = app.getValue('border', key);
        switch (key) {
            case 'color' :
                updateInput(input, value);
                break;
            case 'width' :
            case 'radius' :
                input.val(value);
                var range = input.prev();
                range.val(value);
                setLinearWidth(range);
                break;
            case 'style' :
                input.val(value);
                var select = input.closest('.ba-custom-select');
                value = select.find('li[data-value="'+value+'"]').text();
                select.find('input[readonly]').val($g.trim(value));
                break;
        }
    }
    value = app.getValue('shadow', 'value');
    value = $g('#fields-filter-settings-dialog input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('shadow', 'color');
    updateInput($g('#fields-filter-settings-dialog input[data-option="color"][data-group="shadow"]'), value);
    $g('#fields-filter-settings-dialog .ba-style-custom-select input[type="hidden"]').val('title');
    $g('#fields-filter-settings-dialog .ba-style-custom-select input[readonly]').val(gridboxLanguage['LABEL']);
    showBaStyleDesign('title', document.querySelector('#fields-filter-settings-dialog .ba-style-custom-select'));
    setDisableState('#fields-filter-settings-dialog');
    $g('#fields-filter-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#fields-filter-settings-dialog').modal();
    }, 150);
}

$g('#fields-filter-settings-dialog .fields-filter-app-select').on('customAction', function(){
    let id = this.querySelector('input[type="hidden"]').value,
        data = {
            id : id,
            type : app.edit.type,
            edit_type: app.editor.themeData.edit_type
        };
    if (app.edit.app != id) {
        app.edit.app = id;
        $g.ajax({
            type: "POST",
            dataType: 'text',
            url: "index.php?option=com_gridbox&task=editor.getItemsFilter",
            data: data,
            complete: function(msg){
                app.editor.document.querySelector(app.selector+' .ba-fields-filter-wrapper').innerHTML = msg.responseText;
            }
        });
        $g.ajax({
            type:"POST",
            dataType:'text',
            url:"index.php?option=com_gridbox&task=editor.getAppFields",
            data:data,
            complete: function(msg){
                let object = JSON.parse(msg.responseText),
                    array = new Array();
                app.edit.desktop.fields = {};
                for (let ind in object) {
                    if (object[ind].label && (object[ind].field_type == 'checkbox' || object[ind].field_type == 'radio' ||
                            object[ind].field_type == 'select' || object[ind].field_type == 'price')) {
                        array.push(ind);
                        app.edit.desktop.fields[ind] = true;
                    }
                }
                array.push('rating');
                app.edit.desktop.fields['rating'] = true;
                app.edit.fields = array;
                for (let key in app.editor.breakpoints) {
                    if (app.edit[key] && app.edit[key].fields) {
                        app.edit[key].fields = {};
                    }
                }
                app.sectionRules();
                app.addHistory();
            }
        });
    }
});

function checkFieldsFilterLayout()
{
    if (app.edit.layout == '') {
        $g('#fields-filter-settings-dialog .vertical-filter-options').css('display', '');
    } else {
        $g('#fields-filter-settings-dialog .vertical-filter-options').hide();
    }
}

$g('#fields-filter-settings-dialog .items-filter-layout-select').on('customAction', function(){
    let value = this.querySelector('input[type="hidden"]').value;
    app.editor.$g(app.selector+' .ba-fields-filter-wrapper').removeClass(app.edit.layout).addClass(value);
    app.edit.layout = value;
    checkFieldsFilterLayout();
    app.addHistory();
});

$g('.set-collapsible-filter').on('change', function(){
    app.edit.collapsible = this.checked;
    let action = app.edit.collapsible ? 'addClass' : 'removeClass',
        wrapper = app.editor.$g(app.selector+' .ba-fields-filter-wrapper');
    wrapper[action]('ba-collapsible-filter');
    if (action == 'addClass') {
        let first = app.edit.fields.length > 0 ? '[data-id="'+app.edit.fields[0]+'"]' : '';
        wrapper.find('.ba-field-filter').addClass('ba-filter-collapsed ba-filter-icon-rotated');
        wrapper.find('.ba-field-filter'+first).first().removeClass('ba-filter-collapsed ba-filter-icon-rotated');
    } else {
        wrapper.find('.ba-field-filter').removeClass('ba-filter-collapsed ba-filter-icon-rotated');
    }
    app.addHistory();
});

app.modules.fieldsFilterEditor = true;
app.fieldsFilterEditor();