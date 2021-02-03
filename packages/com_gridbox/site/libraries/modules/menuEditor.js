/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.menuEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#menu-settings-dialog .active').removeClass('active');
    $g('#menu-settings-dialog a[href="#menu-general-options"]').parent().addClass('active');
    $g('#menu-general-options').addClass('active');
    setPresetsList($g('#menu-settings-dialog'));
    $g('#menu-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    var value = $g('#menu-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#menu-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#menu-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#menu-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#menu-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#menu-settings-dialog');
    if (app.edit.type == 'menu' || app.edit.type == 'one-page') {
        $g('#menu-settings-dialog .menu-position-select input[type="hidden"]').val(app.edit.hamburger.position);
        value = $g('#menu-settings-dialog .menu-position-select li[data-value="'+app.edit.hamburger.position+'"]').text();
        $g('#menu-settings-dialog .menu-position-select input[readonly]').val($g.trim(value));
        $g('#menu-settings-dialog .menu-layout-custom-select input[type="hidden"]').val(app.edit.layout.layout);
        value = $g('#menu-settings-dialog .menu-layout-custom-select li[data-value="'+app.edit.layout.layout+'"]').text();
        $g('#menu-settings-dialog .menu-layout-custom-select input[readonly]').val($g.trim(value));
        $g('#menu-settings-dialog [data-option="enable"][data-group="hamburger"]').prop('checked', app.edit.hamburger.enable);
        value = app.edit.hamburger.open;
        updateInput($g('#menu-settings-dialog input[data-option="open"][data-group="hamburger"]'), value);
        $g('#menu-settings-dialog [data-option="open-align"][data-value="'+app.edit.hamburger['open-align']+'"]').addClass('active');
        value = app.edit.hamburger.close;
        updateInput($g('#menu-settings-dialog input[data-option="close"][data-group="hamburger"]'), value);
        $g('#menu-settings-dialog [data-option="close-align"][data-value="'+app.edit.hamburger['close-align']+'"]').addClass('active');
        value = app.edit.hamburger.background;
        updateInput($g('#menu-settings-dialog input[data-option="background"][data-group="hamburger"]'), value);
        $g('#menu-settings-dialog .menu-style-custom-select input[type="hidden"]').val('nav-menu');
        value = $g('#menu-settings-dialog .menu-style-custom-select li[data-value="nav-menu"]').text();
        $g('#menu-settings-dialog .menu-style-custom-select input[readonly]').val($g.trim(value));
    }
    setMenuStyle('nav-menu');
    $g('#menu-settings-dialog').find('.menu-options, .one-page-options').hide();
    if (app.edit.type == 'menu') {
        $g('#menu-settings-dialog .menu-options').removeAttr('style');
        $g('#menu-settings-dialog [data-option="collapse"][data-group="hamburger"]').prop('checked', app.edit.hamburger.collapse);
        $g('.select-mainmenu').val('module ID='+app.edit.integration);
        value = app.getValue('background', 'color');
        updateInput($g('#menu-settings-dialog [data-group="background"][data-option="color"]'), value);
        value = app.getValue('shadow', 'value');
        value = $g('#menu-settings-dialog input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
        setLinearWidth(value);
        value = app.getValue('dropdown', 'width');
        value = $g('#menu-settings-dialog input[data-option="width"][data-group="dropdown"]').val(value).prev().val(value);
        setLinearWidth(value);
        value = app.getValue('shadow', 'color');
        updateInput($g('#menu-settings-dialog input[data-option="color"][data-group="shadow"]'), value);
        value = app.getValue('dropdown', 'effect', 'animation');
        $g('#menu-settings-dialog .dropdown-menu-animation input[type="hidden"]').val(value);
        value = $g('#menu-settings-dialog .dropdown-menu-animation li[data-value="'+value+'"]').text();
        $g('#menu-settings-dialog .dropdown-menu-animation input[readonly]').val($g.trim(value));
        value = app.getValue('dropdown', 'duration', 'animation');
        value = $g('#menu-settings-dialog input[data-option="duration"][data-group="dropdown"]').val(value).prev().val(value);
        setLinearWidth(value);
        sortingList = [];
        value = app.editor.$g(app.selector+' > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul');
        value = getMenuTree(value);
        $g('#menu-settings-dialog .menu-options > .sorting-container').html(value);
        $g('#menu-settings-dialog .menu-options .deeper-sorting-container').each(function(ind){
            $g(this).sortable({
                handle : '> .sorting-item-wrapper > .sorting-item > .sorting-handle i',
                selector : '> .sorting-item-wrapper',
                change : function(dragEl){
                    sortMenuItems(dragEl.parentNode);
                },
                group : 'menu-items'
            });
        });
        $g('.menu-layout-option').css('display', '');
    } else if (app.edit.type == 'one-page') {
        $g('#menu-settings-dialog .one-page-options').removeAttr('style');
        var query = '#'+app.editor.app.edit+' > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul li a';
        value = app.editor.document.querySelectorAll(query);
        $g('#menu-settings-dialog .one-page-options .sorting-container').html('');
        sortingList = [];
        for (var i = 0; i < value.length; i++) {
            var obj = {
                    title : value[i].textContent,
                    href : value[i].hash,
                    alias : value[i].dataset.alias
                },
                icon = value[i].querySelector('a > i.ba-menu-item-icon');
            if (icon) {
                obj.icon = icon.dataset.value;
            } else {
                obj.icon = '';
            }
            sortingList.push(obj);
            $g('#menu-settings-dialog .one-page-options .sorting-container').append(addSortingList(obj, i));
        }
        if (!app.edit.autoscroll) {
            app.edit.autoscroll = {
                "enable": false,
                "speed": 1000,
                "animation": "easeInSine"
            }
        }
        if (!app.edit.layout.type) {
            $g('.menu-layout-option').css('display', '');
        } else {
            $g('.menu-layout-option').hide();
        }
        $g('#menu-settings-dialog [data-group="autoscroll"]').each(function(){
            if (this.type == 'checkbox') {
                this.checked = app.edit.autoscroll[this.dataset.option];
            } else {
                this.value = app.edit.autoscroll[this.dataset.option];
                if (this.type == 'hidden') {
                    $g(this).prev().val(app.edit.autoscroll[this.dataset.option]);
                }
            }
        });
        $g('#menu-settings-dialog .select-one-page-type input[type="hidden"]').val(app.edit.layout.type);
        value = $g('#menu-settings-dialog .select-one-page-type li[data-value="'+app.edit.layout.type+'"]').text().trim();
        $g('#menu-settings-dialog .select-one-page-type input[readonly]').val(value);
    }
    $g('#menu-settings-dialog a[href="#menu-mobile-options"]').parent().css('display', '');
    $g('.menu-layout-custom-select').closest('.ba-settings-group').css('display', '');
    setTimeout(function(){
        $g('#menu-settings-dialog').modal();
    }, 50);
}

$g('#menu-settings-dialog input[data-group="autoscroll"][data-option="enable"]').on('change', function(){
    app.edit.autoscroll.enable = this.checked;
    app.addHistory();
});

function getMenuTree(parent)
{
    var value = parent.find('> li'),
        str = '';
    for (var i = 0; i < value.length; i++) {
        var classList = value[i].classList,
            obj = {
                "title" : $g(value[i]).find('> a, > span').text().trim(),
                "id" : null
            };
        for (var j = 0; j < classList.length; j++) {
            if (classList[j].indexOf('item-') != -1) {
                obj.id = classList[j].replace('item-', '') * 1;
                break;
            }
        }
        if (!app.edit.items) {
            app.edit.items = {};
        }
        if (!app.edit.items[obj.id]) {
            app.edit.items[obj.id] = {
                "icon" : "",
                "megamenu" : false
            }
        }
        obj.item = app.edit.items[obj.id];
        sortingList.push(obj);
        var div = document.createElement('div');
        div.innerHTML = addSortingList(obj, sortingList.length - 1);
        div.className = 'sorting-item-wrapper';
        if ($g(value[i]).find('ul.nav-child').length > 0) {
            var substr = '<div class="deeper-sorting-container" data-parent="'+obj.id+'">';
            substr += getMenuTree($g(value[i]).find('> ul.nav-child'))
            substr += '</div>';
            $g(div).append(substr);
        }
        str += div.outerHTML;
    }

    return str;
}

function sortMenuItems(parent)
{
    var idArray = new Array();
    $g('#menu-settings-dialog .sorting-container > .sorting-item-wrapper > .sorting-item').each(function(){
        var obj = sortingList[this.dataset.key * 1];
        app.editor.$g('li.item-'+obj.id).each(function(){
            $g(this).closest('.integration-wrapper').find('> ul').append(this);
        });
    });
    $g('#menu-settings-dialog .deeper-sorting-container > .sorting-item-wrapper > .sorting-item').each(function(){
        var obj = sortingList[this.dataset.key * 1],
            parent = $g(this).closest('.deeper-sorting-container').attr('data-parent');
        app.editor.$g('li.item-'+obj.id).each(function(){
            $g(this).closest('.integration-wrapper').find('li.item-'+parent+' > ul').append(this);
        });
    });
    $g('#menu-settings-dialog .deeper-sorting-container').each(function(){
        if ($g(this).find('.sorting-item-wrapper').length == 0) {
            $g(this).remove();
        }
    });
    app.editor.$g(app.selector+' ul.nav-child').each(function(){
        if ($g(this).find('li').length == 0) {
            $g(this).remove();
        }
    });
    $g(parent).find('> .sorting-item-wrapper > .sorting-item').each(function(ind){
        var obj = sortingList[this.dataset.key * 1];
        app.editor.$g('li.item-'+obj.id).each(function(){
            $g(this).parent().append(this);
        });
    });
    $g('#menu-settings-dialog .menu-options .sorting-item').each(function(ind){
        var obj = sortingList[this.dataset.key * 1],
            object = {
                id : obj.id,
                parent_id : 1
            },
            parent = $g(this).closest('.deeper-sorting-container');
        if (parent.length > 0) {
            object.parent_id = parent.attr('data-parent') * 1;
        }
        idArray.push(object);
    });
    $g.ajax({
        type:"POST",
        dataType:'text',
        url:"index.php?option=com_gridbox&task=editor.sortMenuItems",
        data:{
            idArray : idArray
        },
        complete: function(msg){
            
        }
    });
}

function addNewOnePageMenuItem(obj)
{
    var li = document.createElement('li'),
        a = document.createElement('a'),
        ul = app.editor.document.querySelector('#'+app.editor.app.edit+' ul');
    a.href = obj.href;
    a.dataset.alias = obj.alias;
    a.textContent = obj.title;
    if (obj.icon) {
        var i = document.createElement('i');
        i.className = 'ba-menu-item-icon '+obj.icon;
        i.dataset.value = obj.icon;
        $g(a).prepend(i);
    }
    li.appendChild(a);
    ul.appendChild(li);
}

$g('#menu-settings-dialog .menu-options > .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    var obj = sortingList[app.itemDelete];
    if (app.editor.$g(app.selector).find('li.item-'+obj.id).hasClass('default')) {
        app.showNotice(gridboxLanguage['DEFAULT_ITEMS_NOTICE']);
        return false;
    }
    app.checkModule('deleteItem');
});

$g('#menu-settings-dialog .menu-options > .sorting-container').on('click', 'i.zmdi-edit', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key'),
        obj = sortingList[key * 1];
    if ($g(this).closest('.deeper-sorting-container').length == 0) {
        $g('#menu-item-edit-modal .ba-checkbox-parent').css('display', '');
    } else {
        $g('#menu-item-edit-modal .ba-checkbox-parent').hide();
    }
    $g('#menu-item-edit-modal input[data-property]').each(function(){
        if (typeof(obj[this.dataset.property]) != 'undefined') {
            var value = obj[this.dataset.property];
        } else {
            var value = obj.item[this.dataset.property];
        }
        if (this.type == 'checkbox') {
            this.checked = value;
        } else {
            this.value = value.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
            this.dataset.value = value;
        }
    });
    $g('#apply-menu-item').addClass('disable-button').removeClass('active-button').attr('data-edit', key);
    $g('#menu-item-edit-modal').modal();
});

$g('#menu-settings-dialog .menu-options > .sorting-container').on('click', 'i.zmdi-copy', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key'),
        obj = sortingList[key * 1];
});

$g('#apply-menu-item').on('click', function(event){
    event.preventDefault();
    if (this.classList.contains('active-button') && this.dataset.click != 'true') {
        this.dataset.click = 'true';
        var key = this.dataset.edit * 1,
            obj = sortingList[key];
        $g('#menu-item-edit-modal input[data-property]').each(function(){
            if (obj[this.dataset.property]) {
                obj[this.dataset.property] = this.value.trim();
            } else if (this.type == 'checkbox') {
                obj.item[this.dataset.property] = this.checked;
            } else {
                obj.item[this.dataset.property] = this.dataset.value.trim();
            }
        });
        if (!obj.item.megamenu) {
            app.editor.$g(app.selector+' li.item-'+obj.id+' > .tabs-content-wrapper').remove();
        }
        if (obj.item.megamenu && app.editor.$g(app.selector+' li.item-'+obj.id+' > .tabs-content-wrapper').length == 0) {
            $g.ajax({
                type: "POST",
                dataType: 'text',
                url: "index.php?option=com_gridbox&task=editor.loadLayout",
                async : false,
                data: {
                    layout : 'megamenu',
                    count : '4+4+4'
                },
                complete: function(msg){
                    msg = JSON.parse(msg.responseText);
                    var key = '';
                    for (var ind in msg.items) {
                        if (msg.items[ind].type == 'mega-menu-section') {
                            key = ind;
                            msg.items[ind].desktop.background.color = app.edit.desktop.background.color;
                        } else if (msg.items[ind].type == 'row') {
                            msg.items[ind].desktop.margin = {
                                "bottom" : "0",
                                "top" : "0"
                            }
                        }
                        app.editor.app.items[ind] = msg.items[ind];
                    }
                    app.sectionRules();
                    app.editor.$g(app.selector+' > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li.item-'+obj.id)
                        .addClass('megamenu-item').prepend(msg.html);
                    var item = app.editor.document.getElementById(key);
                    item.parentNode.dataset.megamenu = 'item-'+obj.id;
                    app.editor.editItem(key);
                    app.editor.makeRowSortable($g(item).find('.ba-section-items'), 'tabs-row');
                    app.editor.makeColumnSortable($g(item).find('.ba-grid-column'), 'lightbox-column');
                    app.editor.setColumnResizer(app.editor.document.getElementById(key));
                    app.editor.$g(app.selector+' .megamenu-item .tabs-content-wrapper .ba-section')
                        .addClass(app.edit.desktop.dropdown.animation.effect);
                }
            });
        } else if (!obj.item.megamenu) {
            app.editor.$g(app.selector+' li.item-'+obj.id+' > .tabs-content-wrapper').remove();
        }
        app.edit.items[obj.id] = obj.item;
        $g.ajax({
            type:"POST",
            dataType:'text',
            url:"index.php?option=com_gridbox&task=editor.saveMenuItemTitle",
            data:{
                title : obj.title,
                id : obj.id
            },
            complete: function(msg){
                $g.ajax({
                    type: "POST",
                    dataType: 'text',
                    url: "index.php?option=com_gridbox&task=editor.checkMainMenu&tmpl=component",
                    data: {
                        main_menu : app.edit.integration,
                        id : app.editor.app.edit,
                        items : JSON.stringify(app.edit)
                    },
                    complete: function(msg){
                        $g('#menu-settings-dialog .menu-options .sorting-item[data-key="'+key+'"] .sorting-title').text(obj.title);
                        app.editor.$g(app.selector+' > .ba-menu-wrapper > .main-menu > .integration-wrapper').each(function(){
                            var div = document.createElement('div');
                            div.innerHTML = msg.responseText;
                            $g(this).find('> ul > li > .tabs-content-wrapper').each(function(){
                                var classList = $g(this).closest('li')[0].classList,
                                    id = '';
                                for (var j = 0; j < classList.length; j++) {
                                    if (classList[j].indexOf('item-') != -1) {
                                        id = classList[j].replace('item-', '') * 1;
                                        break;
                                    }
                                }
                                $g(div).find('li.item-'+id).prepend(this);
                            });
                            $g(this).empty().append($g(div).find('> ul'));
                        });
                        app.editor.app.buttonsPrevent();
                    }
                });
                $g('#apply-menu-item')[0].dataset.click = 'false';
                $g('#menu-item-edit-modal').modal('hide');
            }
        });
    }
});

$g('#menu-item-edit-modal input[data-property]').on('change input', function(){
    var parent = $g(this).closest('.ba-modal-sm');
    if (parent.find('input[data-property="title"]').val().trim()) {
        parent.find('.ba-btn-primary').removeClass('disable-button').addClass('active-button');
    } else {
        parent.find('.ba-btn-primary').addClass('disable-button').removeClass('active-button');
    }
});

$g('#menu-item-edit-modal input[data-property="megamenu"]').on('change', function(){
    var key = $g('#apply-menu-item').attr('data-edit')
    if (!this.checked && app.editor.$g(app.selector+' li.item-'+sortingList[key].id+' > .tabs-content-wrapper').length > 0) {
        app.checkModule('deleteItem');
    }
});

$g('#menu-settings-dialog .select-one-page-type').on('customAction', function(){
    app.edit.layout.type = this.querySelector('input[type="hidden"]').value;
    app.sectionRules();
    app.addHistory();
});

$g('.dropdown-menu-animation').on('customAction', function(){
    var effect = this.querySelector('input[type="hidden"]').value;
    app.editor.$g(app.selector).find('ul.nav-child, .megamenu-item > .tabs-content-wrapper > .ba-section')
        .removeClass(app.edit.desktop.dropdown.animation.effect).addClass(effect);
    app.edit.desktop.dropdown.animation.effect = effect;
    app.addHistory();
});

$g('#menu-settings-dialog .one-page-options .add-new-item i').on('click', function(){
    $g('#one-page-item-modal input').val('');
    $g('#apply-one-page-item').addClass('disable-button').removeClass('active-button').attr('data-edit', 'new');
    $g('#one-page-item-modal').modal();
});

$g('#menu-settings-dialog .menu-options .add-new-item i').on('click', function(){
    $g('#menu-item-add-modal input').val('');
    $g('#menu-item-add-modal .menu-items-select-parent ul li').not('.item-root').remove();
    sortingList.forEach(function(el){
        var li = '<li data-value="'+el.id+'">'+el.title+'</li>';
        $g('#menu-item-add-modal .menu-items-select-parent ul').append(li);
    });
    $g('#menu-item-add-modal .menu-items-select-parent input[type="hidden"]').val(1);
    var title = $g('#menu-item-add-modal .menu-items-select-parent li[data-value="1"]').text().trim();
    $g('#menu-item-add-modal .menu-items-select-parent input[type="text"]').val(title);
    $g('#apply-new-menu-item').addClass('disable-button').removeClass('active-button');
    $g('#menu-item-add-modal').modal();
});

$g('#menu-item-add-modal input').on('change', function(){
    var flag = true;
    $g('#menu-item-add-modal input').each(function(){
        if (!this.value.trim()) {
            flag = false;
            return false;
        }
    });

    if (!flag) {
        $g('#apply-new-menu-item').addClass('disable-button').removeClass('active-button');
    } else {
        $g('#apply-new-menu-item').removeClass('disable-button').addClass('active-button');
    }
});

$g('#apply-new-menu-item').on('click', function(event){
    event.preventDefault();
    if (this.classList.contains('active-button') && this.dataset.click != 'true') {
        this.dataset.click = 'true';
        var data = {
            title : $g('#menu-item-add-modal input[data-property="title"]').val().trim(),
            link : $g('#menu-item-add-modal input[data-property="link"]').val().trim(),
            parent : $g('.menu-items-select-parent input[type="hidden"]').val().trim(),
            id : app.edit.integration
        }
        $g.ajax({
            type: "POST",
            dataType: 'text',
            url: "index.php?option=com_gridbox&task=editor.setNewMenuItem",
            data: data,
            complete: function(msg){
                $g.ajax({
                    type: "POST",
                    dataType: 'text',
                    url: "index.php?option=com_gridbox&task=editor.checkMainMenu&tmpl=component",
                    data: {
                        main_menu : app.edit.integration,
                        id : app.editor.app.edit,
                        items : JSON.stringify(app.edit)
                    },
                    complete: function(msg){
                        app.editor.$g(app.selector+' > .ba-menu-wrapper > .main-menu > .integration-wrapper').each(function(){
                            var div = document.createElement('div');
                            div.innerHTML = msg.responseText;
                            $g(this).find('> ul > li > .tabs-content-wrapper').each(function(){
                                var classList = $g(this).closest('li')[0].classList,
                                    id = '';
                                for (var j = 0; j < classList.length; j++) {
                                    if (classList[j].indexOf('item-') != -1) {
                                        id = classList[j].replace('item-', '') * 1;
                                        break;
                                    }
                                }
                                $g(div).find('li.item-'+id).prepend(this);
                            });
                            $g(this).empty().append($g(div).find('> ul'));
                        });
                        app.editor.app.buttonsPrevent();
                        sortingList = [];
                        var value = app.editor.$g(app.selector+'> .ba-menu-wrapper > .main-menu > .integration-wrapper > ul');
                        value = getMenuTree(value);
                        $g('#menu-settings-dialog .menu-options > .sorting-container').html(value);
                        $g('#menu-settings-dialog .menu-options .deeper-sorting-container').each(function(ind){
                            $g(this).sortable({
                                handle : '> .sorting-item-wrapper > .sorting-item > .sorting-handle i',
                                selector : '> .sorting-item-wrapper',
                                change : function(dragEl){
                                    sortMenuItems();
                                },
                                group : 'menu-items'
                            });
                        });
                        $g('#apply-new-menu-item')[0].dataset.click = 'false';
                        $g('#menu-item-add-modal').modal('hide');
                    }
                });
            }
        });
    }
});

$g('#menu-settings-dialog .one-page-options .sorting-container').on('click', '.zmdi.zmdi-edit', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key');
    $g('#one-page-item-modal .element-title').val(sortingList[key].title);
    $g('#one-page-item-modal .element-alias').val(sortingList[key].alias);
    $g('#one-page-item-modal .select-end-point').val(sortingList[key].href.replace('#', ''));
    var icon = sortingList[key].icon.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
    $g('#one-page-item-modal .select-item-icon').val(icon).attr('data-value', sortingList[key].icon);
    $g('#apply-one-page-item').removeClass('disable-button').addClass('active-button').attr('data-edit', key);
    $g('#one-page-item-modal').modal();
});

$g('#menu-settings-dialog .one-page-options .sorting-container').on('click', '.zmdi.zmdi-copy', function(){
    var ind = $g(this).closest('.sorting-item').attr('data-key') * 1,
        li = app.editor.$g(app.selector+' > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul li').get(ind),
        container = $g('#menu-settings-dialog .one-page-options .sorting-container').empty(),
        clone = li.cloneNode(true);
    clone.classList.remove('active');
    $g(li).after(clone);
    var items = app.editor.$g(app.selector+' > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul li a');
    sortingList = [];
    $g('#item-settings-dialog .sorting-container').html('');
    for (var i = 0; i < items.length; i++) {
        var obj = {
                title : items[i].textContent,
                href : items[i].hash,
                alias : items[i].dataset.alias
            },
            icon = items[i].querySelector('a > i.ba-menu-item-icon');
        if (icon) {
            obj.icon = icon.dataset.value;
        } else {
            obj.icon = '';
        }
        sortingList.push(obj);
        container.append(addSortingList(obj, i));
    }
    app.addHistory();
});

$g('#menu-settings-dialog .one-page-options .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    app.checkModule('deleteItem');
});

$g('#one-page-item-modal .select-end-point').on('click', function(){
    app.editor.app.checkModule('setEndPoint');
    fontBtn = this;
});

$g('#one-page-item-modal .element-title').on('input', function(){
    clearTimeout(delay);
    var $this = this;
    delay = setTimeout(function(){
        if ($g.trim($this.value)) {
            $g('#apply-one-page-item').removeClass('disable-button').addClass('active-button');
        } else {
            $g('#apply-one-page-item').addClass('disable-button').removeClass('active-button');
        }
    });
});

$g('#apply-one-page-item').on('click', function(event){
    event.preventDefault();
    if (!this.classList.contains('active-button')) {
        return false;
    }
    var obj = {
        title : $g('#one-page-item-modal .element-title').val().trim(),
        alias : $g('#one-page-item-modal .element-alias').val().trim(),
        icon : $g('#one-page-item-modal .select-item-icon').attr('data-value'),
        href : '#'+$g('#one-page-item-modal .select-end-point').val()
    };
    if (!obj.alias) {
        obj.alias = obj.title;
    }
    obj.alias = obj.alias.toLowerCase().replace(/ /g, '-');
    if (this.dataset.edit == 'new') {
        sortingList.push(obj);
        $g('#menu-settings-dialog .one-page-options .sorting-container').append(addSortingList(obj, sortingList.length - 1));
        addNewOnePageMenuItem(obj);
    } else {
        var key = this.dataset.edit,
            item = $g('#menu-settings-dialog .sorting-item[data-key="'+key+'"]'),
            ul = app.editor.document.querySelector('#'+app.editor.app.edit+' ul'),
            str = '';
        sortingList[key] = obj;
        item.find('.sorting-title').text(obj.title);
        $g('#menu-settings-dialog .one-page-options .sorting-container .sorting-item').each(function(){
            var key = this.dataset.key;
            str += '<li><a href="'+sortingList[key].href;
            str += '" data-alias="'+sortingList[key].alias+'">';
            if (sortingList[key].icon) {
                str += '<i class="ba-menu-item-icon '+sortingList[key].icon;
                str += '" data-value="'+sortingList[key].icon+'"></i>';
            }
            str += sortingList[key].title+'</a></li>';
        });
        ul.innerHTML = str;
    }
    app.addHistory();
    $g('#one-page-item-modal').modal('hide');
});

$g('.menu-style-custom-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val(),
        parent = $g('#menu-settings-dialog .typography-options').addClass('ba-active-options');
    setMenuStyle(value);
    setTimeout(function(){
        parent.removeClass('ba-active-options');
    }, 1);
});

$g('.select-mainmenu').on('click', function(){
    fontBtn = this;
    checkIframe($g('#menu-select-modal').attr('data-check', 'single'), 'menu');
});

function selectMenu(obj)
{
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.checkMainMenu&tmpl=component",
        data: {
            main_menu : obj.selector,
            id : app.editor.app.edit,
            items : JSON.stringify(app.edit)
        },
        complete: function(msg){
            var item = app.editor.document.getElementById(app.editor.app.edit);
            item = item.querySelector('.integration-wrapper');
            item.innerHTML = msg.responseText;
            fontBtn.value = 'module ID='+obj.selector;
            app.edit.integration = obj.selector;
            $g('a, input[type="submit"], button').on('click', function(event){
                event.preventDefault();
            });
            sortingList = [];
            var value = app.editor.$g(app.selector+'> .ba-menu-wrapper > .main-menu > .integration-wrapper > ul');
            value = getMenuTree(value);
            $g('#menu-settings-dialog .menu-options > .sorting-container').html(value);
            $g('#menu-settings-dialog .menu-options .deeper-sorting-container').each(function(ind){
                $g(this).sortable({
                    handle : '> .sorting-item-wrapper > .sorting-item > .sorting-handle i',
                    selector : '> .sorting-item-wrapper',
                    change : function(dragEl){
                        sortMenuItems(dragEl.parentNode);
                    },
                    group : 'menu-items'
                });
            });
            app.addHistory();
        }
    });
}

function setMenuStyle(type)
{
    $g('#menu-settings-dialog').attr('data-edit', type);
    type = type.replace('-menu', '');
    app.setTypography($g('#menu-settings-dialog .typography-options'), type+'-typography');
    for (var ind in app.edit.desktop[type]) {
        $g('#menu-design-options input[data-subgroup="'+ind+'"]').attr('data-group', type);
    }
    $g('#menu-design-options input[data-type="color"][data-group="'+type+'"]').each(function(){
        var option = this.dataset.option,
            subgroup = this.dataset.subgroup;
        value = app.getValue(type, option, subgroup);
        updateInput($g(this), value);
    });
    $g('#menu-design-options input[data-group="'+type+'"][data-subgroup="padding"]').each(function(){
        var option = this.dataset.option,
            subgroup = this.dataset.subgroup;
        this.value = app.getValue(type, option, subgroup);
    });
    $g('#menu-design-options input[data-group="'+type+'"][data-subgroup="margin"]').each(function(){
        var option = this.dataset.option,
            subgroup = this.dataset.subgroup;
        this.value = app.getValue(type, option, subgroup);
    });
    $g('#menu-design-options input[type="range"] + input[data-group="'+type+'"]').each(function(){
        var input = $g(this);
        value = app.getValue(type, this.dataset.option, this.dataset.subgroup);
        input.val(value);
        var range = input.prev();
        range.val(value);
        setLinearWidth(range);
    });
    for (var key in app.edit.desktop[type].border) {
        var input = $g('#menu-settings-dialog input[data-option="'+key+'"][data-subgroup="border"]');
        value = app.getValue(type, key, 'border');
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
    $g('#menu-design-options i[data-type="reset"]').attr('data-option', type);
}

$g('.menu-layout-custom-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val();
    app.edit.layout.layout = value;
    app.sectionRules();
    app.addHistory();
});

$g('.menu-position-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val();
    app.edit.hamburger.position = value;
    app.sectionRules();
    app.addHistory();
});

$g('#menu-settings-dialog [data-group="hamburger"][data-option="enable"]').on('change', function(){
    app.edit.hamburger.enable = this.checked;
    app.sectionRules();
    app.addHistory();
});

$g('#menu-settings-dialog [data-group="hamburger"][data-option="collapse"]').on('change', function(){
    app.edit.hamburger.collapse = this.checked;
    app.sectionRules();
    app.addHistory();
});

app.modules.menuEditor = true;
app.menuEditor();