/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.socialIconsEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#social-icons-settings-dialog .active').removeClass('active');
    $g('#social-icons-settings-dialog a[href="#social-icons-general-options"]').parent().addClass('active');
    $g('#social-icons-general-options').addClass('active');
    var value = '';
    setPresetsList($g('#social-icons-settings-dialog'));
    $g('#social-icons-settings-dialog .sorting-container').html('');
    sortingList = [];
    for (var key in app.edit.icons) {
        sortingList.push(app.edit.icons[key]);
        $g('#social-icons-settings-dialog .sorting-container').append(addSortingList(app.edit.icons[key], key));
    }
    value = app.getValue('icon', 'text-align');
    $g('#social-icons-settings-dialog [data-option="text-align"][data-value="'+value+'"]').addClass('active');
    value = app.getValue('icon', 'size');
    $g('#social-icons-settings-dialog [data-option="size"]').val(value);
    var range = $g('#social-icons-settings-dialog [data-option="size"]').prev();
    range.val(value);
    setLinearWidth(range);
    value = app.getValue('padding', 'top');
    $g('#social-icons-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#social-icons-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#social-icons-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#social-icons-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    value = app.edit.hover.color;
    updateInput($g('#social-icons-settings-dialog [data-option="color"][data-group="hover"]'), value);
    value = app.edit.hover['background-color'];
    updateInput($g('#social-icons-settings-dialog [data-option="background-color"][data-group="hover"]'), value);
    value = app.getValue('normal', 'color');
    updateInput($g('#social-icons-settings-dialog [data-option="color"][data-group="normal"]'), value);
    value = app.getValue('normal', 'background-color');
    updateInput($g('#social-icons-settings-dialog [data-option="background-color"][data-group="normal"]'), value);
    $g('#social-icons-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#social-icons-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#social-icons-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#social-icons-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#social-icons-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#social-icons-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    for (var key in app.edit.desktop.border) {
        var input = $g('#social-icons-settings-dialog input[data-option="'+key+'"][data-group="border"]');
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
    setDisableState('#social-icons-settings-dialog');
    $g('#social-icons-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#social-icons-settings-dialog').modal();
    }, 150);
}

$g('#social-icons-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    app.checkModule('deleteItem');
});

$g('#social-icons-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-edit', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key');
    $g('#edit-social-icon-dialog input[data-property]').each(function(){
        var prop = this.dataset.property,
            value = sortingList[key].link[prop];
        if (typeof(value) == 'undefined') {
            this.value = sortingList[key].title;
            this.dataset.icon = sortingList[key].icon;
        } else {
            this.value = value;
        }
        if (this.type == 'hidden') {
            value = $g(this).closest('.ba-custom-select').find('li[data-value="'+value+'"]').text().trim();
            $g(this).closest('.ba-custom-select').find('input[type="text"]').val(value);
        }
    });
    $g('#social-icon-apply').removeClass('active-button').addClass('disable-button');
    $g('#social-icon-apply').attr('data-key', key);
    $g('#edit-social-icon-dialog').modal();
});

$g('#social-icons-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-copy', function(){
    var ind = $g(this).closest('.sorting-item').attr('data-key') * 1,
        container = $g('#social-icons-settings-dialog .sorting-container').empty(),
        clone = $g.extend(true, {}, sortingList[ind]),
        obj = {};
    sortingList.splice(ind, 0, clone);
    for (var i = 0; i < sortingList.length; i++) {
        obj[i] = sortingList[i];
        container.append(addSortingList(obj[i], i));
    }
    getSocialIconsHtml(obj);
    app.edit.icons = obj;
    app.addHistory();
});

$g('#edit-social-icon-dialog input[data-property="icon"]').on('click', function(){
    uploadMode = 'reselectSocialIcon';
    checkIframe($g('#icon-upload-dialog'), 'icons');
    fontBtn = this;
    return false;
});

$g('#edit-social-icon-dialog input[data-property]').on('change input', function(){
    var link = $g('#edit-social-icon-dialog input[data-property="link"]').val().trim();
    if (link) {
        $g('#social-icon-apply').addClass('active-button').removeClass('disable-button');
    } else {
        $g('#social-icon-apply').removeClass('active-button').addClass('disable-button');
    }
});

$g('#social-icon-apply').on('click', function(event){
    event.preventDefault();
    if (this.classList.contains('active-button')) {
        var key = this.dataset.key;
        $g('#edit-social-icon-dialog input[data-property]').each(function(){
            var prop = this.dataset.property;
            if (prop == 'icon') {
                sortingList[key].title = this.value;
                sortingList[key].icon = this.dataset.icon;
            } else {
                sortingList[key].link[prop] = this.value;
            }
        });
        $g('#social-icons-settings-dialog .sorting-container').html('');
        for (var ind in app.edit.icons) {
            $g('#social-icons-settings-dialog .sorting-container').append(addSortingList(app.edit.icons[ind], ind));
        }
        getSocialIconsHtml(app.edit.icons);
        $g('#edit-social-icon-dialog').modal('hide');
    }
});

$g('#social-icons-settings-dialog .add-new-item i').on('click', function(){
    uploadMode = 'addSocialIcon';
    checkIframe($g('#icon-upload-dialog'), 'icons');
    return false;
});

app.modules.socialIconsEditor = true;
app.socialIconsEditor();