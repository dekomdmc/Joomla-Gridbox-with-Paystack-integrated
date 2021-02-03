/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.commentsBoxEditor = function(){
	app.selector = '#'+app.editor.app.edit;
    $g('#comments-box-settings-dialog .active').removeClass('active');
    $g('#comments-box-settings-dialog a[href="#comments-box-general-options"]').parent().addClass('active');
    $g('#comments-box-general-options').addClass('active');
    setPresetsList($g('#comments-box-settings-dialog'));
    $g('#comments-box-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#comments-box-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#comments-box-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#comments-box-settings-dialog .class-suffix').val(app.edit.suffix);
    app.setTypography($g('#comments-box-settings-dialog .typography-options'), 'typography');
    value = app.getValue('margin', 'top');
    $g('#comments-box-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#comments-box-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
	value = app.getValue('padding', 'top');
    $g('#comments-box-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#comments-box-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#comments-box-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#comments-box-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    $g('#comments-box-settings-dialog [data-group="view"]').each(function(){
        this.checked = app.edit.view[this.dataset.option]
    });
    if (app.edit.type == 'reviews') {
        $g('#comments-box-settings-dialog .comments-box-options').hide();
        $g('#comments-box-settings-dialog .reviews-options').css('display', '');
    } else {
        $g('#comments-box-settings-dialog .comments-box-attachments-options').css('display', '');
        $g('#comments-box-settings-dialog .reviews-options').hide();
    }
    value = app.getValue('background', 'color');
    updateInput($g('#comments-box-settings-dialog input[data-option="color"][data-group="background"]'), value);
    for (var key in app.edit.desktop.border) {
        var input = $g('#comments-box-settings-dialog input[data-option="'+key+'"][data-group="border"]');
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
            default:
                if (value == 1) {
                    input.prop('checked', true);
                } else {
                    input.prop('checked', false);
                }
        }
    }
    value = app.getValue('shadow', 'value');
    value = $g('#comments-box-settings-dialog input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('shadow', 'color');
    updateInput($g('#comments-box-settings-dialog input[data-option="color"][data-group="shadow"]'), value);
    setDisableState('#comments-box-settings-dialog');
    $g('#comments-box-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#comments-box-settings-dialog').modal();
    }, 150);
}

app.modules.commentsBoxEditor = true;
app.commentsBoxEditor();