/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/


$g('.progress-bar-label').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.editor.$g(app.selector+' .progress-bar-title').text($this.value);
        app.edit.label = $this.value;
        app.addHistory();
    }, 300);
});

$g('.progress-bar-target').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.target = $this.value;
        var obj = {
            data : app.edit,
            selector : app.editor.app.edit
        };
        app.editor.app.checkModule('initItems', obj);
        app.addHistory();
    }, 300);
});

$g('.progress-bar-duration').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.duration = $this.value * 1000;
        var obj = {
            data : app.edit,
            selector : app.editor.app.edit
        };
        app.editor.app.checkModule('initItems', obj);
        app.addHistory();
    }, 300);
});

$g('.progress-bar-effect-select').on('customAction', function(){
    app.edit.easing = this.querySelector('input[type="hidden"]').value;
    var obj = {
        data : app.edit,
        selector : app.editor.app.edit
    };
    app.editor.app.checkModule('initItems', obj);
    app.addHistory();
});

app.progressBarEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    setPresetsList($g('#progress-bar-settings-dialog'));
    $g('#progress-bar-settings-dialog .active').removeClass('active');
    $g('#progress-bar-settings-dialog a[href="#progress-bar-general-options"]').parent().addClass('active');
    $g('#progress-bar-general-options').addClass('active');
    var value = '',
        color = '';
    $g('.progress-bar-label').val(app.edit.label);
    value = $g('.progress-bar-target').val(app.edit.target).prev().val(app.edit.target);
    setLinearWidth(value);
    if (app.edit.type == 'progress-bar') {
        value = app.getValue('view', 'height');
        value = $g('#progress-bar-settings-dialog input[data-option="height"]').val(value).prev().val(value);
        setLinearWidth(value);
        value = app.getValue('display', 'label');
        $g('#progress-bar-settings-dialog input[data-option="label"][data-group="display"]').prop('checked', value);
        value = app.getValue('shadow', 'value');
        value = $g('#progress-bar-settings-dialog input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
        setLinearWidth(value);
        value = app.getValue('shadow', 'color');
        updateInput($g('#progress-bar-settings-dialog input[data-option="color"][data-group="shadow"]'), value);
        $g('.progress-pie-options').hide();
        $g('.progress-bar-options').css('display', '');
        $g('#progress-bar-design-options .progress-bar-options').prev().removeClass('last-element-child');
    } else {
        value = app.getValue('view', 'width');
        value = $g('#progress-bar-settings-dialog .ba-settings-item.progress-pie-options input[data-option="width"]')
            .val(value).prev().val(value);
        setLinearWidth(value);
        value = app.getValue('view', 'line');
        value = $g('#progress-bar-settings-dialog .ba-settings-item.progress-pie-options input[data-option="line"')
            .val(value).prev().val(value);
        setLinearWidth(value);
        $g('.progress-bar-options').hide();
        $g('.progress-pie-options').css('display', '');
        $g('#progress-bar-design-options .progress-bar-options').prev().addClass('last-element-child');
    }
    value = app.getValue('display', 'target');
    $g('#progress-bar-settings-dialog input[data-option="target"][data-group="display"]').prop('checked', value);
    $g('.progress-bar-effect-select input[type="hidden"]').val(app.edit.easing);
    value = $g('.progress-bar-effect-select li[data-value="'+app.edit.easing+'"]').text().trim();
    $g('.progress-bar-effect-select input[type="text"]').val(value);
    value = $g('.progress-bar-duration').val(app.edit.duration / 1000).prev().val(app.edit.duration / 1000);
    setLinearWidth(value);
    app.setTypography($g('#progress-bar-settings-dialog .typography-options'), 'typography');
    value = app.getValue('view', 'bar');
    updateInput($g('#progress-bar-settings-dialog input[data-option="bar"]'), value);
    value = app.getValue('view', 'background',);
    updateInput($g('#progress-bar-settings-dialog input[data-option="background"]'), value);
    value = app.getValue('padding', 'top');
    $g('#progress-bar-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#progress-bar-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#progress-bar-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#progress-bar-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    $g('#progress-bar-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#progress-bar-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#progress-bar-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#progress-bar-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#progress-bar-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#progress-bar-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    for (var key in app.edit.desktop.border) {
        var input = $g('#progress-bar-settings-dialog input[data-option="'+key+'"][data-group="border"]');
        value = app.getValue('border', key);
        switch (key) {
            case 'color' :
                updateInput(input, value);
                break;
            case 'style' :
                input.val(value);
                var select = input.closest('.ba-custom-select');
                value = select.find('li[data-value="'+value+'"]').text();
                select.find('input[readonly]').val($g.trim(value));
                break;
            default:
                input.val(value);
                var range = input.prev();
                range.val(value);
                setLinearWidth(range);
        }
    }
    setDisableState('#progress-bar-settings-dialog');
    $g('#progress-bar-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#progress-bar-settings-dialog').modal();
    }, 150);
}

app.modules.progressBarEditor = true;
app.progressBarEditor();