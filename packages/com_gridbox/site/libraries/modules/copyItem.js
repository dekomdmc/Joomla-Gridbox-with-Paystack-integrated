/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

function duplicateObject(obj, id)
{
    var object = JSON.stringify(obj);
    object = JSON.parse(object);

    return object;
}

function checkSlideshow($this, id)
{
    if (app.items[$this.id].type == 'slideshow') {
        $g($this).find('.ba-slideshow-img').each(function(){
            this.firstElementChild.id = id;
            id++
        });
    }

    return id;
}

function checkAccordions($this, id)
{
    if (app.items[$this.id].type == 'accordion') {
        var accordion = $g($this).find('> .accordion'),
            parent = 'accordion-'+id;
        accordion[0].id = parent;
        id++;
        accordion.find('> .accordion-group > .accordion-heading a').each(function(){
            var old = this.hash;
            this.dataset.parent = '#'+parent;
            this.href = '#collapse-'+id;
            $g($this).find(old)[0].id = 'collapse-'+id;
            id++;
        });
    }

    return id;
}

function checkTabs($this, id)
{
    if (app.items[$this.id].type == 'tabs') {
        var tabs = $g($this);
        tabs.find('> .ba-tabs-wrapper > ul.nav.nav-tabs a').each(function(){
            var old = this.hash;
            this.href = '#tab-'+id;
            $g($this).find(old)[0].id = 'tab-'+id;
            id++;
        });
    }

    return id;
}

function copyItem(child, items, data, id)
{
    child[0].id = 'item-'+id;
    id++;
    if (child.hasClass('ba-item')) {
        items.push(child[0].id);
        id = checkSlideshow(child[0], id);
        id = checkTabs(child[0], id);
        id = checkAccordions(child[0], id);
    }
    if (app.items[child[0].id] && app.items[child[0].id].type == 'overlay-button') {
        if (!child[0].querySelector('.ba-overlay-section-backdrop')) {
            var overlay =  document.querySelector('.ba-overlay-section-backdrop[data-id="'+child[0].dataset.overlay+'"]');
            if (overlay) {
                overlay = overlay.cloneNode(true);
                child[0].appendChild(overlay);
            }
        }
    }
    $g('.ba-overlay-section-backdrop').each(function(){
        var button = child[0].querySelector('.ba-item-overlay-section[data-overlay="'+this.dataset.id+'"]');
        if (button) {
            button.appendChild(this.cloneNode(true));
        }
    });
    child.find('.ba-item').each(function(){
        var ind = this.id;
        this.id = 'item-'+(id++);
        if (data[ind]) {
            app.items[this.id] = duplicateObject(data[ind], ind);
            items.push(this.id);
            id = checkTabs(this, id);
            id = checkAccordions(this, id);
            id = checkSlideshow(this, id);
        }
    });
    child.find('.ba-row, .ba-grid-column, .ba-section').each(function(){
        var ind = this.id;
        if (data[ind]) {
            app.items['item-'+id] = duplicateObject(data[ind], ind);
            if (app.items['item-'+id].type == 'overlay-section') {
                var overlay = child[0].querySelector('.ba-overlay-section-backdrop[data-id="'+this.id+'"]');
                overlay.dataset.id = 'item-'+id;
                overlay.parentNode.dataset.overlay = 'item-'+id;
            } else if (app.items['item-'+id].type == 'mega-menu-section') {
                $g(this).closest('.tabs-content-wrapper').attr('data-id', 'item-'+id);
            }
        }
        this.id = 'item-'+(id++);
    });
    child.find('.star-ratings-wrapper').each(function(){
        var ratings = $g(this)
        ratings.find('i').addClass('active');
        ratings.find('.rating-value').text('0.00');
        ratings.find('.votes-count').text('0');
        ratings.find('.info-wrapper').attr('id', 'star-ratings-'+ratings.closest('.ba-item-star-ratings').attr('id'))
    });
    child.find('.ba-field-group-wrapper').each(function(){
        let key = this.closest('.ba-item-field-group').id,
            object = app.items[key].items;
        $g(this).find('> .ba-field-wrapper').each(function(ind){
            this.dataset.id = 'item-'+id;
            object[ind].field_key = 'item-'+(id++);
        });
    });
    id = child[0].id;

    return id;
}

app.copyItemsContent = function(item, style, key){
    var items = new Array(),
        target, clone, child,
        id = new Date().getTime() * 10;
    if (app.copyAction == 'context') {
        target = top.app.context.target;
    }
    if (app.copyAction == 'copyTabPane' || (style[key] && (style[key].type == 'section' || style[key].type == 'row'))) {
        item = item.parent();
        if (app.copyAction == 'context' && top.app.buffer.store == 'item') {
            target = target.parentNode
        }
    }
    if (style[key]) {
        app.items['item-'+id] = duplicateObject(style[key]);
    }
    clone = item.clone();
    clone.removeClass('.active-context-item-editing').find('.active-context-item-editing').removeClass('active-context-item-editing');
    clone.removeAttr('data-global');
    clone.find('[data-global]').removeAttr('data-global');
    if (app.copyAction == 'copyTabPane' || (style[key] && (style[key].type == 'section' || style[key].type == 'row'))) {
        child = clone.find('#'+key);
    } else {
        child = clone;
    }
    id = copyItem(child, items, style, id);
    if (app.copyAction == 'context' && top.app.buffer.store == 'item' && 
        (top.app.context.itemType == 'menu' || top.app.context.itemType == 'one-page') &&
        $g(target).find('> .ba-menu-wrapper > .main-menu').hasClass('visible-menu')) {
        $g(target).find('> .ba-menu-wrapper > .main-menu > .integration-wrapper').after(clone);
    } else if (app.copyAction == 'context' && top.app.buffer.store == 'item') {
        $g(target).after(clone);
    } else if (app.copyAction == 'context' && top.app.context.itemType != 'column') {
        $g(target).find('> .ba-section-items').append(clone);
    } else if (app.copyAction == 'context' && top.app.context.itemType == 'column') {
        $g(target).find('> .empty-item').before(clone);
    } else {
        item.after(clone);
    }
    editItem(id);
    for (var i = 0; i < items.length; i++) {
        var obj = {
            data : app.items[items[i]],
            selector : items[i]
        };
        itemsInit.push(obj);
    }
    clone.columnResizer({
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
    var wrapper = clone.closest('.ba-wrapper'),
        rowSort = $g('header.header, footer.footer, #ba-edit-section').find(clone).find('.tabs-content-wrapper .ba-section-items');
    makeRowSortable(rowSort, 'tabs-row');
    if (app.copyAction == 'copyContentSlide') {
        makeColumnSortable($g(clone), 'column');
        makeColumnSortable($g(clone).find('.ba-grid-column'), 'column');
    } else if (wrapper.hasClass('ba-lightbox') || wrapper.hasClass('ba-overlay-section')) {
        makeColumnSortable(clone.find('.ba-grid-column'), 'lightbox-column');
        makeRowSortable(clone.find(' > .ba-section > .ba-section-items'), 'lightbox-row');
    } else if (wrapper.attr('data-menu')) {
        makeColumnSortable($g(clone).find('.ba-grid-column'), 'lightbox-column');
        makeRowSortable($g(clone).find(' > .ba-section > .ba-section-items'), 'row');
    } else if (wrapper.hasClass('tabs-content-wrapper')) {
        makeColumnSortable($g(clone).find('.ba-grid-column'), 'column');
        makeRowSortable($g(clone).find(' > .ba-section > .ba-section-items'), 'tabs-row');
    } else {
        makeColumnSortable($g(clone).find('.ba-grid-column'), 'column');
        makeRowSortable($g(clone).find(' > .ba-section > .ba-section-items'), 'row');
    }
}

app.copyItem = function(){
    if (app.copyAction == 'context' && top.app.buffer.store == 'item') {
        var target = $g(top.app.buffer.data.html),
            type = top.app.buffer.data.items[top.app.buffer.id].type;
        if (type == 'section' || type == 'row') {
            target = target.find('#'+top.app.buffer.id);
        }
        app.copyItemsContent(target, top.app.buffer.data.items, top.app.buffer.id);
    } else if (app.copyAction == 'context') {
        var content = null;
        if (top.app.context.itemType != 'column') {
            content = $g(top.app.buffer.data.html).find('> .ba-section-items > .ba-row-wrapper > .ba-row');
        } else {
            content = $g(top.app.buffer.data.html).find('> .ba-item, > .ba-row-wrapper > .ba-row');
        }
        content.each(function(){
            app.copyItemsContent($g(this), top.app.buffer.data.items, this.id);
        });
    } else if (app.copyAction == 'copyTabPane') {
        var obj = top.sortingList[top.app.itemDelete],
            tab = $g(obj.href+' .ba-wrapper > .ba-section').first();
        app.copyItemsContent(tab, app.items, tab.attr('id'));
        top.copyTabPane();
    } else if (app.copyAction == 'copyContentSlide') {
        var key = top.app.itemDelete * 1 + 1,
            li = $g(top.app.selector+' > .slideshow-wrapper > ul > .slideshow-content > li.item:nth-child('+key+')'),
            column = li.find('> .ba-grid-column');
        app.copyItemsContent(column, app.items, column.attr('id'));
        top.copyContentSlide();
    } else {
        if (!app.edit) {
            return false;
        }
        app.copyItemsContent($g('#'+app.edit), app.items, app.edit);
    }
    app.edit = null;
    app.checkModule('sectionRules');
    if (itemsInit.length > 0) {
        app.checkModule('initItems', itemsInit.pop());
    }
    app.buttonsPrevent();
    app.checkModule('checkOverlay');
    app.checkVideoBackground();
    app.checkModule('loadParallax');
    if (app.copyAction != 'blogPostsText') {
        window.parent.app.addHistory();
        window.parent.app.showNotice(window.parent.gridboxLanguage['GRIDBOX_DUPLICATED']);
    }
    if (app.copyAction == 'copyContentSlide' || app.copyAction == 'copyTabPane') {
        app.edit = top.app.editItemId;
    }
    app.copyAction = null;
}

app.copyItem();