/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

function editSortingList(obj, key)
{
    var div = $g('#tabs-settings-dialog .sorting-container .sorting-item[data-key="'+key+'"] .sorting-title');
    if (obj.title) {
        div[0].innerText = obj.title;
    } else {
        div[0].innerText = obj.icon.replace('zmdi zmdi-', '').replace('fa fa-', '');
    }
}

function addNewTab(obj)
{
    if (app.edit.type == 'tabs') {
        var div = document.createElement('div'),
            li = document.createElement('li'),
            a = document.createElement('a'),
            pspan = document.createElement('span'), 
            span = document.createElement('span'),
            i = document.createElement('i'),
            tabContent = app.editor.document.querySelector('#'+app.editor.app.edit+' > .ba-tabs-wrapper > .tab-content'),
            ul = app.editor.document.querySelector('#'+app.editor.app.edit+' > .ba-tabs-wrapper > ul');
        div.className = 'tab-pane';
        div.id = obj.href.replace('#', '');
        tabContent.appendChild(div);
        a.href = obj.href;
        a.dataset.toggle = 'tab';
        span.innerText = obj.title;
        span.className = 'tabs-title';
        if (!obj.title) {
            span.classList.add('empty-textnode');
        }
        li.appendChild(a);
        pspan.appendChild(span);
        a.appendChild(pspan);
        if (obj.icon) {
            i.className = obj.icon;
            pspan.appendChild(i);
        }
        ul.appendChild(li);
    } else {
        var accordion = app.editor.document.querySelector('#'+app.editor.app.edit+' > .accordion'),
            group = document.createElement('div'),
            heading = document.createElement('div'),
            body = document.createElement('div'),
            div = document.createElement('div'),
            a = document.createElement('a'),
            pspan = document.createElement('span'), 
            span = document.createElement('span'),
            i = document.createElement('i'),
            i2 = document.createElement('i'),
            tabContent = app.editor.document.querySelector('#'+app.editor.app.edit+' > .ba-tabs-wrapper > .tab-content'),
            ul = app.editor.document.querySelector('#'+app.editor.app.edit+' > .ba-tabs-wrapper > ul');
        i2.className = 'zmdi zmdi-chevron-right accordion-icon';
        group.className = 'accordion-group';
        heading.className = 'accordion-heading';
        group.appendChild(heading);
        a.href = obj.href;
        a.className = 'accordion-toggle';
        a.dataset.toggle = 'collapse';
        a.dataset.parent = '#'+accordion.id;
        span.innerText = obj.title;
        span.className = 'accordion-title';
        if (!obj.title) {
            span.classList.add('empty-textnode');
        }
        heading.appendChild(a);
        pspan.appendChild(span);
        a.appendChild(pspan);
        a.appendChild(i2);
        if (obj.icon) {
            i.className = obj.icon;
            pspan.appendChild(i);
        }
        group.appendChild(body);
        body.className = 'accordion-body collapse';
        body.style.height = 0;
        body.id = obj.href.replace('#', '');
        div.className = 'accordion-inner';
        body.appendChild(div);
        accordion.appendChild(group);
    }
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.loadLayout",
        data: {
            layout : 'sectionTabs',
            count : 12
        },
        complete: function(msg){
            msg = JSON.parse(msg.responseText);
            for (var key in msg.items) {
                break;
            }
            div.innerHTML = msg.html;
            app.editor.editItem(key);
            var item = app.editor.document.getElementById(key);
            app.editor.makeRowSortable($g(item).find('.ba-section-items'), 'tabs-row');
            app.editor.makeColumnSortable($g(item).find('.ba-grid-column'), 'column');
            app.editor.setColumnResizer(app.editor.document.getElementById(key));
        }
    });
}

app.tabsEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#tabs-settings-dialog .active').removeClass('active');
    $g('#tabs-settings-dialog a[href="#tabs-general-options"]').parent().addClass('active');
    $g('#tabs-general-options').addClass('active');
    setPresetsList($g('#tabs-settings-dialog'));
    var li = app.editor.document.querySelectorAll('#'+app.editor.app.edit+' > .ba-tabs-wrapper > ul span.tabs-title'),
        value,
        query = '#'+app.editor.app.edit+' > .accordion > .accordion-group > .accordion-heading .accordion-title';
    sortingList = [];
    if (app.edit.type == 'accordion') {
        li = app.editor.document.querySelectorAll(query);
    }
    $g('#tabs-settings-dialog .sorting-container').html('');
    for (var i = 0; i < li.length; i++) {
        var obj = {
            title : li[i].innerHTML.trim(),
            href : li[i].parentNode.parentNode.hash,
            className : li[i].parentNode.parentNode.parentNode.className
        }
        var icon = li[i].parentNode.querySelector('i');
        if (icon) {
            obj.icon = icon.className;
        } else {
            obj.icon = '';
        }
        sortingList.push(obj);
        $g('#tabs-settings-dialog .sorting-container').append(addSortingList(obj, i));
    }
    value = app.getValue('icon', 'size');
    $g('#tabs-settings-dialog [data-option="size"][data-group="icon"]').val(value);
    var range = $g('#tabs-settings-dialog [data-option="size"][data-group="icon"]').prev();
    range.val(value);
    setLinearWidth(range);
    $g('#tabs-settings-dialog .tabs-icon-position input[type="hidden"]').val(app.edit.desktop.icon.position);
    value = $g('#tabs-settings-dialog .tabs-icon-position li[data-value="'+app.edit.desktop.icon.position+'"]').text();
    $g('#tabs-settings-dialog .tabs-icon-position input[readonly]').val($g.trim(value));
    app.setTypography($g('#tabs-settings-dialog .typography-options'), 'typography');
    if (app.edit.type == 'tabs') {
        value = $g('#tabs-settings-dialog .hover-group [data-option="color"]');
        value[0].dataset.group = 'hover';
        updateInput(value, app.getValue('hover', 'color'));
        value = $g('#tabs-settings-dialog [data-option="border"][data-group="header"]');
        updateInput(value, app.getValue('header', 'border'));
        $g('#tabs-settings-dialog .tabs-position-select input[type="hidden"]').val(app.edit.position);
        value = $g('#tabs-settings-dialog .tabs-position-select li[data-value="'+app.edit.position+'"]').text();
        $g('#tabs-settings-dialog .tabs-position-select input[readonly]').val($g.trim(value));
    } else {
        value = $g('#tabs-settings-dialog [data-option="color"][data-group="border"]');
        updateInput(value, app.getValue('border', 'color'));
    }
    value = app.getValue('header', 'color');
    updateInput($g('#tabs-settings-dialog [data-option="color"][data-group="header"]'), value);
    value = app.getValue('background', 'color');
    updateInput($g('#tabs-settings-dialog [data-group="background"][data-option="color"]'), value);
    $g('#tabs-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#tabs-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#tabs-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#tabs-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#tabs-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#tabs-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'top');
    $g('#tabs-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#tabs-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#tabs-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#tabs-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    setDisableState('#tabs-settings-dialog');
    $g('#tabs-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#tabs-settings-dialog').modal();
    }, 150);
}

$g('#tabs-settings-dialog .tabs-icon-position').on('customAction', function(){
    app.edit.desktop.icon.position = this.querySelector('input[type="hidden"]').value;
    app.sectionRules()
    app.addHistory();
});

$g('#tabs-settings-dialog .tabs-position-select').on('customAction', function(){
    var item = app.editor.document.querySelector('#'+app.editor.app.edit+' .ba-tabs-wrapper');
    item.classList.remove(app.edit.position);
    app.edit.position = $g(this).find('input[type="hidden"]').val();
    item.classList.add(app.edit.position);
    app.addHistory();
});

$g('#tabs-settings-dialog .add-new-item i').on('click', function(){
    $g('#add-new-element-modal input').val('');
    $g('#add-new-element-modal .select-item-icon').attr('data-value', '');
    $g('#apply-new-element').addClass('disable-button').removeClass('active-button').attr('data-edit', 'new');
    $g('#add-new-element-modal').modal();
});

$g('#tabs-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-edit', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key');
    $g('#add-new-element-modal .element-title').val(sortingList[key].title);
    $g('#add-new-element-modal .select-item-icon')
        .attr('data-value', sortingList[key].icon)
        .val(sortingList[key].icon.replace('zmdi zmdi-', '').replace('fa fa-', ''));
    $g('#apply-new-element').removeClass('disable-button').addClass('active-button').attr('data-edit', key);
    $g('#add-new-element-modal').modal();
});

$g('#tabs-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-copy', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key') * 1;
    app.editItemId = app.editor.app.edit;
    app.editor.app.copyAction = 'copyTabPane';
    app.itemDelete = key;
    app.editor.app.checkModule('copyItem');
});

function copyTabPane()
{
    if (app.edit.type == 'tabs') {
        var div = document.createElement('div'),
            wrapper = app.editor.$g(sortingList[app.itemDelete].href+' > .ba-wrapper').last(),
            li = app.editor.$g(app.selector+' > .ba-tabs-wrapper li').get(app.itemDelete),
            href = 'tab-'+new Date().getTime(),
            clone = li.cloneNode(true);
        clone.classList.remove('active');
        clone.querySelector('a').href = '#'+href;
        div.className = 'tab-pane';
        div.id = href;
        li.after(clone);
        wrapper.parent().after(div);
        div.appendChild(wrapper[0]);
    } else {
        var div = app.editor.$g(app.selector+' > .accordion > .accordion-group').get(app.itemDelete),
            clone = div.cloneNode(true),
            wrapper = app.editor.$g(sortingList[app.itemDelete].href+' > .accordion-inner > .ba-wrapper').last(),
            href = 'collapse-'+new Date().getTime();
        $g(clone).find('> .accordion-heading a').removeClass('active').attr('href', '#'+href);
        $g(clone).find('> .accordion-body').removeClass('in').attr('id', href).css('height', 0).find('> .accordion-inner').html(wrapper);
        $g(div).after(clone);
    }
    var li = app.editor.document.querySelectorAll('#'+app.editor.app.edit+' > .ba-tabs-wrapper > ul span.tabs-title'),
        container = $g('#tabs-settings-dialog .sorting-container').empty(),
        query = '#'+app.editor.app.edit+' > .accordion > .accordion-group > .accordion-heading .accordion-title';
    sortingList = [];
    if (app.edit.type == 'accordion') {
        li = app.editor.document.querySelectorAll(query);
    }
    for (var i = 0; i < li.length; i++) {
        var obj = {
            title : li[i].innerHTML.trim(),
            href : li[i].parentNode.parentNode.hash,
            className : li[i].parentNode.parentNode.parentNode.className
        }
        var icon = li[i].parentNode.querySelector('i');
        if (icon) {
            obj.icon = icon.className;
        } else {
            obj.icon = '';
        }
        sortingList.push(obj);
        container.append(addSortingList(obj, i));
    }
}

$g('#tabs-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    app.checkModule('deleteItem');
});

$g('#add-new-element-modal input').on('input', function(){
    clearTimeout(delay);
    var $this = this,
        that = $g('#add-new-element-modal input').not(this)[0];
    delay = setTimeout(function(){
        if ($g.trim($this.value) || $g.trim(that.value)) {
            $g('#apply-new-element').removeClass('disable-button').addClass('active-button');
        } else {
            $g('#apply-new-element').addClass('disable-button').removeClass('active-button');
        }
    });
});

$g('#apply-new-element').on('click', function(){
    if (!this.classList.contains('active-button')) {
        return false;
    }
    if (this.dataset.edit == 'new') {
        var obj = {
            title : $g.trim($g('#add-new-element-modal .element-title').val()),
            icon : $g.trim($g('#add-new-element-modal .select-item-icon').attr('data-value')),
            href : '#tab-'+new Date().getTime(),
            className : ''
        }
        sortingList.push(obj);
        $g('#tabs-settings-dialog .sorting-container').append(addSortingList(obj, sortingList.length - 1));
        addNewTab(obj);
    } else {
        var key = this.dataset.edit,
            obj = sortingList[key],
            span = app.editor.document.querySelector('#'+app.editor.app.edit+' a[href="'+obj.href+'"] span.tabs-title'),
            icon = app.editor.document.querySelector('#'+app.editor.app.edit+' a[href="'+obj.href+'"] i');
        if (app.edit.type == 'accordion') {
            span = app.editor.document.querySelector('#'+app.editor.app.edit+' a[href="'+obj.href+'"] span.accordion-title');
            icon = app.editor.document.querySelector('#'+app.editor.app.edit+' a[href="'+obj.href+'"] span i');
        }
        obj.title = $g.trim($g('#add-new-element-modal .element-title').val());
        obj.icon = $g.trim($g('#add-new-element-modal .select-item-icon').attr('data-value'));
        span.innerText = obj.title;
        if (!obj.title) {
            span.classList.add('empty-textnode');
        } else {
            span.classList.remove('empty-textnode');
        }
        if (obj.icon) {
            if (icon) {
                icon.className = obj.icon;
            } else {
                icon = document.createElement('i');
                icon.className = obj.icon;
                span.parentNode.appendChild(icon);
            }
        } else {
            if (icon) {
                icon.parentNode.removeChild(icon);
            }
        }
        editSortingList(obj, key);
    }
    app.addHistory();
    $g('#add-new-element-modal').modal('hide');
});

app.modules.tabsEditor = true;
app.tabsEditor();