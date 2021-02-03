/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.setLibraryItem = function(item, obj){
    var str = '<span>'+top.gridboxLanguage['LOADING'];
    str +='</span><img src="'+window.parent.JUri+'components/com_gridbox/assets/images/reload.svg"></img>';
    top.app.showNotice(str);
    $g.ajax({
        type : "POST",
        dataType : 'text',
        url : "index.php?option=com_gridbox&task=editor.getLibrary",
        data : {
            id : obj.id,
            type : obj.type
        },
        complete: function(msg){
            if (obj.globalItem) {
                globalItemCallback(msg, item, obj);
            } else {
                if (obj.type == 'blocks') {
                    obj.type = 'section';
                }
                itemCallback(msg, item, obj);
            }
        }
    });
}

function insertItemFromLibrary(item, target, next, id, items, type)
{
    app.setNewFont = true;
    app.fonts = {};
    app.customFonts = {};
    app.edit = id;
    app.checkModule('sectionRules');
    if (target.classList.contains('ba-edit-section') || target.classList.contains('ba-grid-column')) {
        if ((next && target.classList.contains('ba-grid-column')) || (!next && target.classList.contains('ba-grid-column'))) {
            $g(target).find(' > .empty-item').before(item[0]);
        } else {
            $g(target).prepend(item[0]);
        }
    } else {
        if (next) {
            $g(target).after(item[0]);
        } else {
            $g(target).before(item[0]);
        }
    }
    editItem(id);
    for (var i = 0; i < items.length; i++) {
        var obj = {
                data : app.items[items[i]],
                selector : items[i]
            };
        itemsInit.push(obj);
    }
    if (itemsInit.length > 0) {
        app.checkModule('initItems', itemsInit.pop());
    }
    app.buttonsPrevent();
    item.columnResizer({
        change : function(right, left){
            right.find('.ba-item').each(function(){
                if (app.items[this.id]) {
                    initMapTypes(app.items[this.id].type, this.id);
                }
            });
            left.find('.ba-item').each(function(){
                if (app.items[this.id]) {
                    initMapTypes(app.items[this.id].type, this.id);
                }
            });
        }
    });
    makeRowSortable($g(item).find(' > .ba-section > .ba-section-items'), 'row');
    var str = '.tabs-content-wrapper > .ba-section > .ba-section-items';
    makeRowSortable($g(item).find(str), 'tabs-row');
    str = ' > .ba-section > .ba-section-items > .ba-row-wrapper';
    str += ' > .ba-row > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column';
    makeColumnSortable($g(item).find(str), 'column');
    str = ' > .ba-section > .ba-section-items';
    makeRowSortable(item.find('.ba-lightbox, .ba-overlay-section').find(str), 'lightbox-row');
    str += ' > .ba-row-wrapper > .ba-row > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column';
    makeColumnSortable(item.find('.ba-lightbox, .ba-overlay-section, .ba-wrapper[data-megamenu]').find(str), 'lightbox-column');
    app.checkModule('checkOverlay');
    app.checkVideoBackground();
    app.checkModule('loadParallax');
    $g('.visible-backdrop').removeClass('visible-backdrop');
    if (window.parent.$g('#megamenu-library-dialog').hasClass('in')) {
        window.parent.$g('#megamenu-library-dialog').modal('hide');
    }
    window.parent.app.checkUserLevel();
    window.parent.app.addHistory();
}

function itemCallback(msg, target, obj)
{
    var data = JSON.parse(msg.responseText),
        div = document.createElement('div'),
        item = null,
        items = new Array(),
        child,
        id = new Date().getTime() * 10;
    div.innerHTML = data.html;
    $g(div).find('> style').appendTo(document.head);
    $g(div).find('> link').appendTo(document.head);
    $g(div).find('> script').each(function(){
        var src = this.src;
        if (src.indexOf('/jquery.min.js') != -1) {
            this.parentNode.removeChild(this);
        } else {
            document.head.appendChild(this);
        }
    });
    item = $g(div.firstElementChild);
    item.removeAttr('data-global');
    item.find('[data-global]').removeAttr('data-global');
    if (obj.type == 'section') {
        child = $g(item[0].firstElementChild);
        app.items['item-'+id] = duplicateObject(data.items[child[0].id]);
    } else {
        child = item;
        app.items['item-'+id] = duplicateObject(data.items[item[0].id]);
    }
    id = copyItem(child, items, data.items, id);
    insertItemFromLibrary(item, target, obj.next, id, items, obj.type);
}

function globalItemCallback(msg, target, obj)
{
    var data = JSON.parse(msg.responseText),
        div = document.createElement('div'),
        item = null,
        items = new Array(),
        child,
        id = new Date().getTime() * 10;
    div.innerHTML = data.html;
    $g(div).find('> style').appendTo(document.head);
    $g(div).find('> link').appendTo(document.head);
    $g(div).find('> script').each(function(){
        var src = this.src;
        if (src.indexOf('/jquery.min.js') != -1) {
            this.parentNode.removeChild(this);
        } else {
            document.head.appendChild(this);
        }
    });
    item = $g(div.firstElementChild);
    for (var key in data.items) {
        app.items[key] = data.items[key];
    }
    if (obj.type == 'section') {
        child = $g(item[0].firstElementChild);
        id = item[0].firstElementChild.id;
    } else {
        child = item;
        id = item[0].id;
    }
    if (child.hasClass('ba-item')) {
        items.push(child[0].id);
    }
    child.find('.ba-item').each(function(){
        items.push(this.id);
    });
    insertItemFromLibrary(item, target, obj.next, id, items);
}

app.setLibraryItem(app.modules.setLibraryItem.data, app.modules.setLibraryItem.selector);