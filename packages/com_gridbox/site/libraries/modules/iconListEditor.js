/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.iconListEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#icon-list-settings-dialog .active').removeClass('active');
    $g('#icon-list-settings-dialog a[href="#icon-list-general-options"]').parent().addClass('active');
    $g('#icon-list-general-options').addClass('active');
    setPresetsList($g('#icon-list-settings-dialog'));
    drawIconListSortingList();
    $g('.icons-list-layout-select input[type="hidden"]').val(app.edit.layout);
    value = $g('.icons-list-layout-select li[data-value="'+app.edit.layout+'"]').text().trim();
    $g('.icons-list-layout-select input[type="text"]').val(value);
    if (!('listType' in app.edit)) {
        app.edit.listType = '';
    }
    $g('.icons-list-type-select input[type="hidden"]').val(app.edit.listType);
    value = $g('.icons-list-type-select li[data-value="'+app.edit.listType+'"]').text().trim();
    $g('.icons-list-type-select input[type="text"]').val(value);
    value = app.getValue('icons', 'size');
    var range = $g('#icon-list-settings-dialog [data-option="size"]').val(value).prev().val(value);
    setLinearWidth(range);
    app.setTypography($g('#icon-list-settings-dialog .typography-options'), 'body');
    if (!app.edit.desktop.background) {
        app.edit.desktop.background = {
            "color": "rgba(255, 255, 255, 0)"
        }
    }
    value = app.getValue('background', 'color');
    updateInput($g('#icon-list-settings-dialog input[data-option="color"][data-group="background"]'), value);
    $g('.icons-list-select-position input[type="hidden"]').val(app.edit.icon.position);
    value = $g('.icons-list-select-position li[data-value="'+app.edit.icon.position+'"]').text().trim();
    $g('.icons-list-select-position input[type="text"]').val(value);
    value = app.getValue('icons', 'color');
    updateInput($g('#icon-list-settings-dialog input[data-option="color"][data-group="icons"]'), value);
    if (!app.edit.desktop.icons.background) {
        app.edit.desktop.icons.background = 'rgba(255,255,255,0)';
        app.edit.desktop.icons.padding = 0;
        app.edit.desktop.icons.radius = 0;
    }
    value = app.getValue('icons', 'background');
    updateInput($g('#icon-list-settings-dialog input[data-option="background"][data-group="icons"]'), value);
    value = app.getValue('icons', 'padding');
    range = $g('#icon-list-settings-dialog [data-option="padding"]').val(value).prev().val(value);
    setLinearWidth(range);
    value = app.getValue('icons', 'radius');
    range = $g('#icon-list-settings-dialog [data-option="radius"]').val(value).prev().val(value);
    setLinearWidth(range);
    $g('#icon-list-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#icon-list-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#icon-list-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#icon-list-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#icon-list-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#icon-list-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#icon-list-settings-dialog');
    if (!app.edit.desktop.padding) {
        app.edit.desktop.padding = {
            "bottom" : "0",
            "left" : "0",
            "right" : "0",
            "top" : "0"
        };
        app.edit.desktop.border = {
            "color" : "@border",
            "style" : "solid",
            "radius" : 0,
            "top" : "0",
            "width" : "0"
        };
        app.edit.desktop.shadow = {
            "value" : 0,
            "color" : "@shadow"
        };
        app.edit.desktop.background = {
            "color": "rgba(255, 255, 255, 0)"
        }
    }
    value = app.getValue('padding', 'top');
    $g('#icon-list-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#icon-list-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#icon-list-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#icon-list-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    for (var key in app.edit.desktop.border) {
        var input = $g('#icon-list-settings-dialog input[data-option="'+key+'"][data-group="border"]');
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
    value = $g('#icon-list-settings-dialog input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('shadow', 'color');
    updateInput($g('#icon-list-settings-dialog input[data-option="color"][data-group="shadow"]'), value);



    $g('#icon-list-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#icon-list-settings-dialog').modal();
    }, 150);
}

function drawIconListSortingList()
{
    var container = $g('#icon-list-settings-dialog .sorting-container').empty();
    sortingList = [];
    for (var ind in app.edit.list) {
        var obj = $g.extend(true, {}, app.edit.list[ind]);
        sortingList.push(obj);
        container.append(addIconListSortingList(obj, sortingList.length - 1));
    }
}

function addIconListSortingList(obj, key)
{
    var str = '<div class="sorting-item" data-key="'+key;
    str += '"><div class="sorting-handle"><i class="zmdi zmdi-apps"></i></div>';
    str += '<div class="sorting-title">'+obj.title+'</div><div class="sorting-icons">';
    str += '<span><i class="zmdi zmdi-edit"></i></span>';
    str += '<span><i class="zmdi zmdi-copy"></i></span>';
    str += '<span><i class="zmdi zmdi-delete"></i></span></div></div>';

    return str;
}

$g('#icon-list-settings-dialog .sorting-container').on('click', 'i.zmdi.zmdi-copy', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key') * 1,
        li = app.editor.$g(app.selector+' ul > li').get(key),
        clone = li.cloneNode(true),
        obj = $g.extend({}, app.edit.list[key + 1]),
        list = {};
    key += 1;
    $g(li).after(clone);
    app.editor.app.buttonsPrevent();
    for (var ind in app.edit.list) {
        if (ind == key) {
            list[ind] = app.edit.list[ind];
            list[key + 1] = obj;
        } else if (ind >= key + 1) {
            list[ind * 1 + 1] = app.edit.list[ind];
        } else {
            list[ind] = app.edit.list[ind];
        }
    }
    app.edit.list = list;
    drawIconListSortingList();
    app.addHistory();
});

$g('#icon-list-settings-dialog .sorting-container').on('click', 'i.zmdi.zmdi-edit', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key') * 1,
        obj = sortingList[key * 1],
        modal = $g('#icons-list-item-modal');
    $g('#apply-icons-list-item').removeClass('disable-button').addClass('active-button').attr('data-key', key);
    modal.find('.element-title').val(obj.title);
    modal.find('.select-item-icon').val(obj.icon.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', ''))
        .attr('data-value', obj.icon);
    modal.find('.element-link').val(obj.link);
    modal.find('.element-target-select input[type="hidden"]').val(obj.target);
    value = modal.find('.element-target-select li[data-value="'+obj.target+'"]').text().trim();
    modal.find('.element-target-select input[type="text"]').val(value);
    modal.modal();
});

$g('#icon-list-settings-dialog .add-new-item .zmdi-plus-circle').on('click', function(){
    var modal = $g('#icons-list-item-modal');
    $g('#apply-icons-list-item').addClass('disable-button').removeClass('active-button').attr('data-key', -1);
    modal.find('.element-title').val('');
    modal.find('.select-item-icon').val('')
        .attr('data-value', '');
    modal.find('.element-link').val('');
    modal.find('.element-target-select input[type="hidden"]').val('_blank');
    value = modal.find('.element-target-select li[data-value="_blank"]').text().trim();
    modal.find('.element-target-select input[type="text"]').val(value);
    modal.modal();
});

$g('#icons-list-item-modal .element-title').on('input', function(){
    clearTimeout(delay);
    var $this = this;
    delay = setTimeout(function(){
        if ($g.trim($this.value)) {
            $g('#apply-icons-list-item').removeClass('disable-button').addClass('active-button');
        } else {
            $g('#apply-icons-list-item').addClass('disable-button').removeClass('active-button');
        }
    });
});

$g('#apply-icons-list-item').on('click', function(event){
    event.preventDefault();
    if (this.classList.contains('active-button')) {
        var key = this.dataset.key * 1,
            modal = modal = $g('#icons-list-item-modal'),
            str = '',
            obj = {
                title: modal.find('.element-title').val().trim(),
                icon: modal.find('.select-item-icon').attr('data-value'),
                link: modal.find('.element-link').val().trim(),
                target: modal.find('.element-target-select input[type="hidden"]').val()
            }
        str += obj.link ? '<a href="'+obj.link+'" target="'+obj.target+'">' : '';
        str += (obj.icon ? '<i class="'+obj.icon+'"></i>' : '')+'<span>'+obj.title+'</span>';
        str += obj.link ? '</a>' : '';
        if (key == -1) {
            var li = document.createElement('li');
            app.editor.$g(app.selector+' ul').append(li);
            key = sortingList.length;
        } else {
            var li = app.editor.$g(app.selector+' ul li').get(key);
        }
        li.classList[obj.link ? 'remove' : 'add']('list-item-without-link');
        app.edit.list[key + 1] = obj;
        li.innerHTML = str;
        app.editor.app.buttonsPrevent();
        drawIconListSortingList();
        app.addHistory();
        modal.modal('hide');
    }
});

$g('#icon-list-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    app.checkModule('deleteItem');
});

$g('.icons-list-select-position').on('customAction', function(){
    app.edit.icon.position = this.querySelector('input[type="hidden"]').value;
    app.sectionRules();
    app.addHistory();
});

$g('.icons-list-layout-select').on('customAction', function(){
    app.editor.$g(app.selector+' ul').removeClass(app.edit.layout);
    app.edit.layout = this.querySelector('input[type="hidden"]').value;
    app.editor.$g(app.selector+' ul').addClass(app.edit.layout);
    app.addHistory();
});

$g('.icons-list-type-select').on('customAction', function(){
    app.editor.$g(app.selector+' ul li').each(function(){
        this.classList[this.querySelector('a') ? 'remove' : 'add']('list-item-without-link');
    });
    app.editor.$g(app.selector+' ul').removeClass(app.edit.listType);
    app.edit.listType = this.querySelector('input[type="hidden"]').value;
    app.editor.$g(app.selector+' ul').addClass(app.edit.listType);
    if (app.edit.listType == 'bullets-type') {
        app.edit.desktop.icons.size = 10;
    } else {
        app.edit.desktop.icons.size = 24;
    }
    value = app.getValue('icons', 'size');
    var range = $g('#icon-list-settings-dialog [data-option="size"]').val(value).prev().val(value);
    setLinearWidth(range);
    app.sectionRules();
    app.addHistory();
});

app.modules.iconListEditor = true;
app.iconListEditor();