/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.featureBoxEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#feature-box-settings-dialog .active').removeClass('active');
    $g('#feature-box-settings-dialog a[href="#feature-box-general-options"]').parent().addClass('active');
    $g('#feature-box-general-options').addClass('active');
    setPresetsList($g('#feature-box-settings-dialog'));
    drawFeatureBoxSortingList();
    $g('.feature-box-layout-select input[type="hidden"]').val(app.edit.layout);
    value = $g('.feature-box-layout-select li[data-value="'+app.edit.layout+'"]').text().trim();
    $g('.feature-box-layout-select input[type="text"]').val(value);
    value = app.getValue('view', 'count');
    $g('#feature-box-settings-dialog input[data-option="count"][data-group="view"]').val(value);
    $g('#feature-box-settings-dialog .select-options-state').each(function(){
        this.querySelector('input[type="hidden"]').value = 'normal';
        this.querySelector('input[type="text"]').value = gridboxLanguage['DEFAULT'];
        $g(this).trigger('customAction');
    });
    $g('#feature-box-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#feature-box-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#feature-box-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#feature-box-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#feature-box-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#feature-box-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#feature-box-settings-dialog');
    for (var key in app.edit.desktop.border) {
        var input = $g('#feature-box-settings-dialog input[data-option="'+key+'"][data-group="border"]');
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
    value = app.getValue('padding', 'top');
    $g('#feature-box-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#feature-box-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#feature-box-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#feature-box-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    $g('#feature-box-settings-dialog').attr('data-edit', app.edit.type);
    $g('#feature-box-settings-dialog .ba-style-custom-select input[type="hidden"]').val('icon');
    $g('#feature-box-settings-dialog .ba-style-custom-select input[readonly]').val(gridboxLanguage['ICON']);
    $g('#feature-box-settings-dialog .select-title-html-tag input[type="hidden"]').val(app.edit.tag);
    $g('#feature-box-settings-dialog .select-title-html-tag input[readonly]').val(app.edit.tag.toUpperCase());
    showBaStyleDesign('icon', document.querySelector('#feature-box-settings-dialog .ba-style-custom-select'));
    setTimeout(function(){
        $g('#feature-box-settings-dialog').modal();
    }, 150);
}

function drawFeatureBoxSortingList()
{
    var container = $g('#feature-box-settings-dialog .sorting-container').empty();
    sortingList = [];
    for (var ind in app.edit.items) {
        var obj = $g.extend(true, {}, app.edit.items[ind]);
        sortingList.push(obj);
        container.append(addFeatureBoxSortingList(obj, sortingList.length - 1));
    }
}

function addFeatureBoxSortingList(obj, key)
{
    var str = '<div class="sorting-item" data-key="'+key;
    str += '"><div class="sorting-handle"><i class="zmdi zmdi-apps"></i></div>';
    str += '<div class="sorting-title">'+obj.title+'</div><div class="sorting-icons">';
    str += '<span><i class="zmdi zmdi-edit"></i></span>';
    str += '<span><i class="zmdi zmdi-copy"></i></span>';
    str += '<span><i class="zmdi zmdi-delete"></i></span></div></div>';

    return str;
}

$g('#feature-box-settings-dialog .sorting-container').on('click', 'i.zmdi.zmdi-copy', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key') * 1,
        child = app.editor.$g(app.selector+' .ba-feature-box').get(key),
        clone = child.cloneNode(true),
        obj = $g.extend({}, app.edit.items[key]),
        list = {};
    $g(child).after(clone);
    app.editor.app.buttonsPrevent();
    for (var ind in app.edit.items) {
        if (ind == key) {
            list[ind] = app.edit.items[ind];
            list[key + 1] = obj;
        } else if (ind >= key + 1) {
            list[ind * 1 + 1] = app.edit.items[ind];
        } else {
            list[ind] = app.edit.items[ind];
        }
    }
    app.edit.items = list;
    drawFeatureBoxSortingList();
    app.sectionRules();
    app.addHistory();
});

$g('#feature-box-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    app.checkModule('deleteItem');
});

$g('#feature-box-settings-dialog .sorting-container').on('click', 'i.zmdi.zmdi-edit', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key') * 1,
        obj = sortingList[key],
        modal = $g('#feature-box-item-modal');
    modal.find('.feature-box-type-select input[type="hidden"]').val(obj.type);
    value = modal.find('.feature-box-type-select li[data-value="'+obj.type+'"]').text().trim();
    modal.find('.feature-box-type-select input[type="text"]').val(value);
    modal.find('.feature-box-type-option').hide();
    modal.find('.feature-box-type-option[data-type="'+obj.type+'"]').css('display', '');
    modal.find('.slide-title').val(obj.title);
    modal.find('.slide-description').val(obj.description);
    modal.find('.select-item-icon').val(obj.icon.replace(/zmdi zmdi-|fa fa-|flaticon-/, '')).attr('data-value', obj.icon);
    modal.find('.image-item-upload-image').val(obj.image);
    if (obj.button.view == 'link') {
        modal.find('.slideshow-button-label').hide();
    } else {
        modal.find('.slideshow-button-label').show();
    }
    modal.find('.slide-button-type-select input[type="hidden"]').val(obj.button.view);
    value = modal.find('.slide-button-type-select li[data-value="'+obj.button.view+'"]').text().trim();
    modal.find('.slide-button-type-select input[readonly]').val(value);
    modal.find('.slide-button-link').val(obj.button.href);
    modal.find('.slide-button-label').val(obj.button.title);
    modal.find('.slide-button-embed-code').val(obj.button.embed);
    modal.find('.slide-button-target-select input[type="hidden"]').val(obj.button.target);
    value = modal.find('.slide-button-target-select li[data-value="'+obj.button.target+'"]').text().trim();
    modal.find('.slide-button-target-select input[readonly]').val(value);
    modal.find('.slide-button-attribute-select input[type="hidden"]').val(obj.button.type);
    value = modal.find('.slide-button-attribute-select li[data-value="'+obj.button.type+'"]').text().trim();
    modal.find('.slide-button-attribute-select input[readonly]').val(value);
    $g('#apply-feature-box-item').attr('data-key', key);
    modal.modal();
});

$g('.feature-box-type-select').on('customAction', function(){
    var type = this.querySelector('input[type="hidden"]').value;
    $g(this).closest('.modal').find('.feature-box-type-option').hide();
    $g(this).closest('.modal').find('.feature-box-type-option[data-type="'+type+'"]').css('display', '');
});

$g('#apply-feature-box-item').on('click', function(){
    var key = this.dataset.key * 1,
        div = document.createElement('div'),
        obj = {
            button: {}
        },
        modal = $g('#feature-box-item-modal');
    obj.type = modal.find('.feature-box-type-select input[type="hidden"]').val();
    obj.title = modal.find('.slide-title').val().trim();
    obj.description = modal.find('.slide-description').val().trim();
    obj.icon = modal.find('.select-item-icon').attr('data-value');
    obj.image = modal.find('.image-item-upload-image').val();
    obj.button.view = modal.find('.slide-button-type-select input[type="hidden"]').val();
    obj.button.href = modal.find('.slide-button-link').val().trim();
    obj.button.title = modal.find('.slide-button-label').val().trim();
    obj.button.embed = modal.find('.slide-button-embed-code').val().trim();
    obj.button.target = modal.find('.slide-button-target-select input[type="hidden"]').val();
    obj.button.type = modal.find('.slide-button-attribute-select input[type="hidden"]').val();
    div.className = 'ba-feature-box';
    if ((obj.icon && obj.type == 'icon') || (obj.image && obj.type == 'image')) {
        var image = '<div class="ba-feature-image-wrapper" data-type="'+obj.type+'">';
        image += obj.icon && obj.type == 'icon' ? '<i class="'+obj.icon+'"></i>' : '<div class="ba-feature-image"></div>';
        image += '</div>';
        div.innerHTML = image;
    }
    if (obj.title || obj.description || obj.button.title || (obj.button.href && obj.button.view == 'link')) {
        var caption = document.createElement('div');
        caption.className = 'ba-feature-caption';
        if (obj.title) {
            var title = document.createElement('div'),
                tag = document.createElement(app.edit.tag);
            title.className = 'ba-feature-title-wrapper';
            tag.className = 'ba-feature-title';
            tag.textContent = obj.title;
            title.appendChild(tag);
            caption.appendChild(title);
        }
        if (obj.description) {
            var description = document.createElement('div')
                desc = document.createElement('div');
            description.className = 'ba-feature-description-wrapper';
            desc.className = 'ba-feature-description';
            desc.innerHTML = obj.description;
            description.appendChild(desc);
            caption.appendChild(description);
        }
        if (obj.button.title || (obj.button.href && obj.button.view == 'link')) {
            var button = document.createElement('div'),
                a = document.createElement('a');
            button.className = 'ba-feature-button'+(obj.button.view == 'link' ? ' empty-content' : '');
            button.appendChild(a);
            caption.appendChild(button);
            var object = {
                href: obj.button.href,
                target: obj.button.target,
                type: obj.button.view == 'link' ? 'ba-overlay-slideshow-button' : '',
                download: obj.button.type,
                embed: obj.button.embed,
                title: obj.button.title
            }
            replaceSlideEmbed($g(a), object);
        }
        div.appendChild(caption);
    }
    if (app.edit.items[key]) {
        var child = app.editor.$g(app.selector+' .ba-feature-box').get(key);
        $g(child).replaceWith(div);
    } else {
        app.editor.$g(app.selector+' .ba-feature-box-wrapper').append(div);
    }
    app.edit.items[key] = obj;
    app.editor.app.buttonsPrevent();
    drawFeatureBoxSortingList();
    modal.modal('hide');
    app.sectionRules();
    app.addHistory();
});

$g('#feature-box-item-modal .slide-button-type-select').on('customAction', function(){
    if (this.querySelector('input[type="hidden"]').value == 'button') {
        $g('#feature-box-item-modal .slideshow-button-label').show();
    } else {
        $g('#feature-box-item-modal .slideshow-button-label').hide();
    }
});

$g('#feature-box-settings-dialog .add-new-item .zmdi-plus-circle').on('click', function(){
    var modal = $g('#feature-box-item-modal');
    modal.find('.feature-box-type-select input[type="hidden"]').val('icon');
    modal.find('.feature-box-type-select input[type="text"]').val(gridboxLanguage['ICON']);
    modal.find('.feature-box-type-option').hide();
    modal.find('.feature-box-type-option[data-type="icon"]').css('display', '');
    modal.find('.slide-title').val('');
    modal.find('.slide-description').val('');
    modal.find('.select-item-icon').val('').attr('data-value', '');
    modal.find('.image-item-upload-image').val('');
    modal.find('.slideshow-button-label').show();
    modal.find('.slide-button-type-select input[type="hidden"]').val('button');
    modal.find('.slide-button-type-select input[readonly]').val(gridboxLanguage['BUTTON']);
    modal.find('.slide-button-link').val('');
    modal.find('.slide-button-label').val('');
    modal.find('.slide-button-embed-code').val('');
    modal.find('.slide-button-target-select input[type="hidden"]').val('_blank');
    modal.find('.slide-button-target-select input[readonly]').val(gridboxLanguage['NEW_WINDOW']);
    modal.find('.slide-button-attribute-select input[type="hidden"]').val('');
    modal.find('.slide-button-attribute-select input[readonly]').val(gridboxLanguage['DEFAULT']);
    $g('#apply-feature-box-item').attr('data-key', sortingList.length);
    modal.modal();
});

$g('.feature-box-layout-select').on('customAction', function(){
    app.editor.$g(app.selector+' .ba-feature-box-wrapper').removeClass(app.edit.layout);
    app.edit.layout = this.querySelector('input[type="hidden"]').value;
    app.editor.$g(app.selector+' .ba-feature-box-wrapper').addClass(app.edit.layout);

    if (!app.edit.preset && !app.editor.app.theme.defaultPresets[app.edit.type]) {
        var type = app.edit.type,
            patern = $g.extend(true, {}, presetsPatern[type]),
            is_object = null,
            object = defaultElementsStyle[type];
        for (var ind in patern) {
            if (ind == 'desktop') {
                for (var key in patern[ind]) {
                    is_object = typeof(app.edit[ind][key]) == 'object';
                    app.edit[ind][key] = is_object ? $g.extend(true, {}, object[ind][key]) : object[ind][key];
                }
                for (var ind in app.editor.breakpoints) {
                    if (app.edit[ind]) {
                        for (var key in patern.desktop) {
                            is_object = typeof(app.edit[ind][key]) == 'object';
                            if (is_object && object[ind] && object[ind][key]) {
                                app.edit[ind][key] = $g.extend(true, {}, object[ind][key]);
                            } else if (!is_object && object[ind] && object[ind][key]) {
                                app.edit[ind][key] = object[ind][key];
                            } else if (is_object) {
                                app.edit[ind][key] = {};
                            } else {
                                delete(app.edit[ind][key]);
                            }
                        }
                    }
                }
            } else {
                is_object = typeof(app.edit[ind]) == 'object';
                app.edit[ind] = is_object ? $g.extend(true, {}, object[ind]) : object[ind];
            }
        }
        if (app.edit.layout == 'ba-feature-list-layout') {
            app.edit.desktop.title.margin.top = 0;
            app.edit.desktop.title.typography['text-align'] = 'left';
            app.edit.desktop.description.typography['text-align'] = 'left';
            app.edit.desktop.button.typography['text-align'] = 'left';
        }
        app.editor.app.checkModule('editItem');
        app.editor.app.setNewFont = true;
        app.editor.app.fonts = {};
        app.editor.app.customFonts = {};
    }


    app.sectionRules();
    app.addHistory();
});

app.modules.featureBoxEditor = true;
app.featureBoxEditor();