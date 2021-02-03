/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.searchEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    let modal = $g('#search-settings-dialog');
    modal.find('.active').removeClass('active');
    modal.find('a[href="#search-general-options"]').parent().addClass('active');
    $g('#search-general-options').addClass('active');
    if (app.edit.type == 'store-search') {
        $g('#search-settings-dialog .live-store-search').prop('checked', app.edit.live);
    }
    var value = '';
    value = app.getValue('padding', 'top');
    modal.find('[data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    modal.find('[data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    modal.find('[data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    modal.find('[data-group="padding"][data-option="left"]').val(value);
    modal.find('input.search-placeholder').val(app.edit.placeholder);
    value = app.getValue('icons', 'size');
    modal.find('[data-option="size"][data-group="icons"]').val(value);
    var range = modal.find('[data-option="size"][data-group="icons"]').prev().val(value);
    setLinearWidth(range);
    value = app.edit.icon.icon.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
    modal.find('[data-option="icon"][data-group="icon"]').val(value);
    modal.find('.search-icon-position input[type="hidden"]').val(app.edit.desktop.icons.position);
    value = modal.find('.search-icon-position li[data-value="'+app.edit.desktop.icons.position+'"]').text();
    modal.find('.search-icon-position input[readonly]').val($g.trim(value));
    app.setTypography(modal.find('.typography-options'), 'typography');
    modal.find('.section-access-select input[type="hidden"]').val(app.edit.access);
    value = modal.find('.section-access-select li[data-value="'+app.edit.access+'"]').text();
    modal.find('.section-access-select input[readonly]').val($g.trim(value));
    modal.find('.class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    modal.find('[data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    modal.find('[data-group="margin"][data-option="bottom"]').val(value);
    for (var key in app.edit.desktop.border) {
        var input = modal.find('input[data-option="'+key+'"][data-group="border"]');
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
    setDisableState('#search-settings-dialog');
    modal.attr('data-edit', app.edit.type);
    setTimeout(function(){
        modal.modal();
    }, 150);
}

$g('#search-settings-dialog .live-store-search').on('change', function(){
    app.edit.live = this.checked;
    app.addHistory();
});

$g('#search-settings-dialog .search-placeholder').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        var input = app.editor.document.querySelector(app.selector+' .ba-search-wrapper input');
        app.edit.placeholder = $this.value;
        input.placeholder = $this.value;
        app.addHistory();
    });
});

$g('#search-settings-dialog input[data-option="icon"][data-group="icon"]').on('click', function(){
    uploadMode = 'addSearchIcon';
    checkIframe($g('#icon-upload-dialog'), 'icons');
    fontBtn = this;
});

function removeSearchIcon()
{
    var i = app.editor.document.querySelector(app.editor.app.edit+' .ba-search-wrapper i');
    if (i) {
        i.remove(i);
    }
}

app.modules.searchEditor = true;
app.searchEditor();