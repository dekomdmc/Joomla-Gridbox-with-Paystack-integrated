/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

var libHandle = document.getElementById('library-item-handle');
app.buffer = null;

app.showEditorRbtnContext = function(){
    $g('.ba-context-menu').hide();
    var rect = document.querySelector('.editor-iframe').getBoundingClientRect(),
        deltaX = document.documentElement.clientWidth - app.context.event.clientX + rect.left,
        deltaY = document.documentElement.clientHeight - app.context.event.clientY + rect.top,
        content,
        top = app.context.event.clientY + rect.top,
        left = app.context.event.clientX + rect.left,
        context = document.querySelector('.'+app.context.context);
    context.style.display = 'block';
    context.dataset.type = app.context.itemType;
    if (deltaX - context.offsetWidth < 0) {
        context.classList.add('ba-left');
    } else {
        context.classList.remove('ba-left');
    }
    if (deltaY - context.offsetHeight < 0) {
        context.classList.add('ba-top');
        if (top < context.offsetHeight) {
            top = context.offsetHeight + 10;
        }
    } else {
        context.classList.remove('ba-top');
        if (top + context.offsetHeight > document.documentElement.clientHeight) {
            top = top - 10 - (top + context.offsetHeight - document.documentElement.clientHeight);
        }
    }
    context.style.top = top+'px';
    context.style.left = left+'px';
    var buffer = localStorage.getItem('gridboxBuffer'),
        type = app.context.item.type,
        introStr = app.getIntroStr();
    if (buffer) {
        app.buffer = JSON.parse(buffer);
    }
    if (app.context.itemType != 'column') {
        content = $g(app.context.target).find('> .ba-section-items > .ba-row-wrapper > .ba-row');
    } else {
        content = $g(app.context.target).find('> .ba-item, > .ba-row-wrapper > .ba-row');
    }
    if (content.length > 0) {
        $g('span.context-copy-content, span.context-delete-content').removeClass('disable-button');
    } else {
        $g('span.context-copy-content, span.context-delete-content').addClass('disable-button');
    }
    if (type == 'lightbox' || type == 'cookies') {
        $g('span.context-add-to-library, span.context-copy-item').addClass('disable-button');
    } else if (type == 'footer' || type == 'header' || type == 'overlay-section' || type == 'mega-menu-section'
        || app.context.target.dataset.cookie == 'accept' || type == 'category-intro' || type == 'error-message'
        || type == 'blog-posts' || type == 'post-intro' || type == 'search-result' || type == 'store-search-result'
        || type == 'sticky-header'
        || type == 'preloader' || app.context.target.classList.contains('row-with-intro-items')
        || app.editor.$g(app.context.target).find('.row-with-intro-items').length > 0) {
        $g('span.context-add-to-library, span.context-copy-item, span.context-delete-item').addClass('disable-button');
    } else {
        $g('span.context-add-to-library, span.context-copy-item, span.context-delete-item').removeClass('disable-button');
    }
    if (app.editor.themeData.edit_type == 'post-layout' && app.editor.themeData.app_type != 'blog') {
        $g('span.context-delete-item').removeClass('disable-button');
    }
    if (type == 'overlay-section' || type == 'lightbox' || type == 'cookies'
        || app.context.target.classList.contains('row-with-intro-items')
        || app.editor.$g(app.context.target).find('.row-with-intro-items').length > 0
        || app.editor.$g(app.context.target).find(introStr).length > 0) {
        $g('.context-copy-content').addClass('disable-button');
    }
    if (app.editor.themeData.edit_type == 'post-layout' && app.editor.themeData.app_type != 'blog') {
        $g('.context-delete-content').removeClass('disable-button');
    } else if (type == 'cookies'|| app.context.target.classList.contains('row-with-intro-items')
        || app.editor.$g(app.context.target).find('.row-with-intro-items').length > 0
        || app.editor.$g(app.context.target).find(introStr).length > 0) {
        $g('.context-delete-content').addClass('disable-button');
    }
    if (app.buffer && (app.buffer.store == 'item' || app.buffer.store == 'content') && app.editor.themeData.app_type != 'single'
        && (app.buffer.data.html.indexOf('ba-item-related-posts') != -1 || app.buffer.data.html.indexOf('ba-item-post-tags') != -1
            || app.buffer.data.html.indexOf('ba-item-post-navigation') != -1)) {
        $g('span.context-paste-buffer').addClass('disable-button');
    } else if (app.buffer && app.buffer.store == 'item' && app.context.context == 'plugin-context-menu' &&
        app.buffer.type != 'section' && app.buffer.type != 'row' && app.buffer.type != 'column') {
        $g('span.context-paste-buffer').removeClass('disable-button');
    } else if ((type == 'overlay-section' || type == 'lightbox' || type == 'cookies' || type == 'mega-menu-section')
        && app.buffer && (app.buffer.store == 'item' || app.buffer.store == 'content')) {
        $g('span.context-paste-buffer').addClass('disable-button');
    } else if (app.buffer && app.buffer.type == app.context.itemType &&
        (app.buffer.store != 'item' || (app.buffer.store == 'item' && type != 'footer' && type != 'header'))) {
        $g('span.context-paste-buffer').removeClass('disable-button');
    } else {
        $g('span.context-paste-buffer').addClass('disable-button');
    }
    if (app.context.itemType == 'column' && app.editor.$g(app.context.target).parent().closest('.ba-grid-column').length > 0) {
        $g('span.context-add-nested-row').addClass('disable-button');
    } else {
        $g('span.context-add-nested-row').removeClass('disable-button');
    }
    app.editor.$g(app.context.target).closest('div[class*="-wrapper"]').addClass('active-context-item')
        .parent().parents('.ba-grid-column-wrapper').addClass('active-context-item');
    app.editor.$g(app.context.target).addClass('active-context-item-editing')
        .parents('div[class*="-wrapper"]').addClass('active-context-item-editing');
    app.editor.$g(app.context.target).closest('li.megamenu-item').addClass('megamenu-editing')
        .closest('.ba-row-wrapper').addClass('row-with-megamenu')
        .closest('.ba-wrapper').addClass('section-with-megamenu')
        .closest('body').addClass('body-megamenu-editing');
}

app.showContext = function(){
    if (!app.context) {
        return false;
    }
    if (app.context.type && app.context.type == 'contextEvent') {
        setTimeout(function(){
            app.showEditorRbtnContext();
        }, 50);
        return false;
    } if (app.context.dataset.context == 'responsive-context-menu' && app.context.classList.contains('disable-button')) {
        return false;
    }
    var rect = app.context.getBoundingClientRect(),
        target = app.context.dataset.context,
        context = document.getElementsByClassName(target)[0];
    context.style.top = rect.bottom+'px';
    context.style.left = rect.left+'px';
    if (app.context.dataset.context == 'page-context-menu') {
        context.style.left = rect.right+'px';
    }
    setTimeout(function(){
        if (app.context.dataset.context == 'section-library-list') {
            if (app.context.classList.contains('system-type-preloader')) {
                return false;
            }
            $g.ajax({
                type: "POST",
                dataType: 'text',
                url: "index.php?option=com_gridbox&task=editor.getLibraryItems",
                complete: function(msg){
                    var obj = JSON.parse(msg.responseText),
                        str = returnLibraryHtml(obj.sections, 'section', obj.delete, obj.global);
                    $g('.section-library-list .ba-library-item').parent().remove();
                    $g('#section-library-cell').prepend(str);
                    str = returnLibraryHtml(obj.plugins, 'plugin', obj.delete, obj.global);
                    $g('#plugins-library-cell').prepend(str);
                    $g('.editor-iframe').addClass('push-left-body');
                    if (app.editor) {
                        app.editor.document.getElementById('library-backdrop').classList.add('visible-backdrop');
                    }
                    $g(context).addClass('ba-sidebar-panel');
                }
            });
        } else if (app.context.dataset.context == 'section-page-blocks-list') {
            if (($g('body').hasClass('blog-post-editor-parent') && !$g('body').hasClass('advanced-blog-editor'))
                || app.context.classList.contains('system-type-preloader')) {
                return false;
            }
            $g('.editor-iframe').addClass('push-left-body');
            if (app.editor) {
                app.editor.document.getElementById('library-backdrop').classList.add('visible-backdrop');
            }
            $g(context).addClass('ba-sidebar-panel');
        }
        context.style.display = 'block';
    }, 15);
};

function returnLibraryHtml(array, type, delete_item, global_item)
{
    var str = '';
    for (var i = 0; i < array.length; i++) {
        str += '<span class="library-item-wrapper">';
        if (array[i].image) {
            str += '<span class="library-image" style="background-image:url('+array[i].image+');"><img src="';
            str += 'components/com_gridbox/assets/images/default-theme.png">';
            str += '<div class="camera-container" data-id="'+array[i].id;
            str += '"><i class="zmdi zmdi-camera"></i></div></span>';
        }
        str += '<span class="ba-library-item" data-id="'+array[i].id+'">';
        str += '<span class="library-handle" data-type="'+type+'" data-id="'+array[i].id+'">';
        str += '<i class="zmdi zmdi-apps"></i></span><span class="library-title">';
        str += array[i].title+'</span>';
        if (array[i].global_item) {
            str += '<span class="library-global-item" data-id="'+array[i].global_item+'">';
            str += '<i class="zmdi zmdi-star"></i><span class="ba-tooltip ba-top">'+global_item+'</span></span>';
        }
        str += '<span class="delete-from-library" data-id="'+array[i].id+'">';
        str += '<i class="zmdi zmdi-delete"></i><span class="ba-tooltip ba-top">'+delete_item;
        str += '</span></span></span></span>';
    }

    return str;
}

function returnPointLibraryItem(event, type, offset)
{
    var pageY = event.clientY,
        pageX = event.clientX,
        item = null,
        rect = null,
        editSection = app.editor.document.getElementById('ba-edit-section'),
        str = '.ba-wrapper:not(.ba-lightbox):not(.ba-overlay-section):not(.tabs-content-wrapper)';
    if (type == 'section' || type == 'blocks') {
        $g(editSection).find(str).each(function(){
            rect = this.getBoundingClientRect();
            if (rect.top + offset < event.clientY && rect.bottom + offset > event.clientY &&
                rect.left < event.clientX && event.clientX < rect.right) {
                item = this;
                return false;
            }
        });
        if (!item) {
            item = editSection;
        }
    } else {
        editSection = app.editor.document.body;
        str = '.ba-grid-column-wrapper > .ba-grid-column';
        if (app.editor.document.querySelector('.ba-menu-wrapper.ba-hamburger-menu > .main-menu.visible-menu') &&
            app.editor.document.documentElement.offsetWidth <= app.editor.menuBreakpoint) {
            str = '.ba-menu-wrapper.ba-hamburger-menu > .main-menu.visible-menu';
        }
        var columns = [].slice.call(app.editor.document.querySelectorAll(str));
        columns = columns.reverse();
        for (var i = 0; i < columns.length; i ++) {
            $g(columns[i]).find(' > .ba-item, > .ba-row-wrapper, > .integration-wrapper').each(function(){
                rect = this.getBoundingClientRect();
                if (rect.top + offset < event.clientY && rect.bottom + offset > event.clientY &&
                    rect.left < event.clientX && event.clientX < rect.right) {
                    item = this;
                    return false;
                }
            });
            if (!item) {
                rect = columns[i].getBoundingClientRect();
                if (rect.top + offset < event.clientY && rect.bottom + offset > event.clientY &&
                    rect.left < event.clientX && event.clientX < rect.right) {
                    item = columns[i];
                    break;
                }
            } else {
                break;
            }
        }
    }
    
    return item;
}

$g('span.pages-list').on('mousedown', function(){
    setTimeout(function(){
        checkIframe($g('#pages-list-modal'), 'pages');
    }, 200);
    $g('body').trigger('mousedown');
    return false;
});

$g('.left-context-menu, #login-modal').on('mousedown', function(event){
    event.stopPropagation();
});

$g('.section-page-blocks-list .ba-page-block-item').on('mousedown', function(event){
    if (this.classList.contains('disabled')) {
        window.gridboxCallback = 'blocksAction';
        app.checkModule('login');
        return false;
    } else {
        var id = this.dataset.id,
            item = null,
            next;
        app.editor.app.edit = null;
        app.editor.app.checkModule('copyItem');
        $g('body').trigger('mousedown');
        libHandle.style.display = '';
        libHandle.style.top = event.clientY+'px';
        libHandle.style.left = event.clientX+'px';
        var placeholder = app.editor.document.getElementById('library-placeholder'),
            backdrop = app.editor.document.getElementById('library-backdrop');
        backdrop.dataset.id = id;
        $g(document).on('mousemove.library', function(event){
            libHandle.style.top = event.clientY+'px';
            libHandle.style.left = event.clientX+'px';
            placeholder.style.display = '';
            if (!backdrop.classList.contains('visible-backdrop')) {
                backdrop.classList.add('visible-backdrop');
            }
            item = returnPointLibraryItem(event, 'blocks', 80);
            if (item) {
                var rect = item.getBoundingClientRect(),
                    obj = {
                        "left" : rect.left + 16,
                        "width" : rect.right - rect.left - 30
                    };
                next = (event.clientY - (rect.top + 80)) / (rect.bottom - rect.top) > .5;
                if (next || item.classList.contains('ba-grid-column')) {
                    obj.top = rect.bottom;
                } else {
                    obj.top = rect.top;
                }
                $g(placeholder).css(obj);
            } else {
                placeholder.style.display = 'none';
            }
            return false;
        }).on('mouseup.library', function(event){
            libHandle.style.display = 'none';
            placeholder.style.display = 'none';
            backdrop.classList.remove('visible-backdrop');
            $g(document).off('mouseup.library mousemove.library');
            $g(app.editor.document).off('mouseup.library mousemove.library');
            var obj =  {
                "data" : item,
                "selector" : {
                    id : id,
                    type : 'blocks',
                    globalItem : null
                }
            };
            if (obj.data) {
                rect = obj.data.getBoundingClientRect();
                obj.selector.next = (event.clientY - (rect.top + 80)) / (rect.bottom - rect.top) > .5;
                app.editor.app.checkModule('setLibraryItem', obj);
            }
        });
        $g(app.editor.document).on('mousemove.library', function(event){
            libHandle.style.top = (event.clientY + 80)+'px';
            libHandle.style.left = (event.clientX + (window.innerWidth - app.editor.innerWidth) / 2)+'px';
            placeholder.style.display = '';
            item = returnPointLibraryItem(event, 'blocks', 0);
            if (item) {
                var rect = item.getBoundingClientRect(),
                    obj = {
                        "left" : rect.left + 16,
                        "width" : rect.right - rect.left - 30
                    };
                next = (event.clientY - rect.top) / (rect.bottom - rect.top) > .5;
                if (next || item.classList.contains('ba-grid-column')) {
                    obj.top = rect.bottom;
                } else {
                    obj.top = rect.top;
                }
                $g(placeholder).css(obj);
            } else {
                placeholder.style.display = 'none';
            }
            return false;
        }).on('mouseup.library', function(event){
            libHandle.style.display = 'none';
            placeholder.style.display = 'none';
            $g(document).off('mouseup.library mousemove.library');
            $g(app.editor.document).off('mouseup.library mousemove.library');
            var obj =  {
                "data" : item,
                "selector" : {
                    id : id,
                    type : 'blocks',
                    next : next,
                    globalItem : null
                }
            };
            if (obj.data) {
                rect = obj.data.getBoundingClientRect();
                obj.selector.next = (event.clientY - rect.top) / (rect.bottom - rect.top) > .5;
                app.editor.app.checkModule('setLibraryItem', obj);
            }
        });
        return false;
    }
});

$g('#section-library-cell, #plugins-library-cell').on('mousedown', '.library-handle', function(event){
    var id = this.dataset.id,
        type = this.dataset.type,
        item = null,
        globalItem = this.parentNode;
    globalItem = globalItem.querySelector('.library-global-item');
    if (globalItem) {
        globalItem = globalItem.dataset.id;
        var item = app.editor.document.getElementById(globalItem);
        if (item) {
            app.showNotice(gridboxLanguage['GLOBAL_ITEM_NOTICE']);
            return false;
        }
    }
    app.editor.app.edit = null;
    app.editor.app.checkModule('copyItem');
    $g('body').trigger('mousedown');
    libHandle.style.display = '';
    libHandle.style.top = event.clientY+'px';
    libHandle.style.left = event.clientX+'px';
    var placeholder = app.editor.document.getElementById('library-placeholder'),
        backdrop = app.editor.document.getElementById('library-backdrop');
    backdrop.dataset.id = id;
    $g(document).on('mousemove.library', function(event){
        libHandle.style.top = event.clientY+'px';
        libHandle.style.left = event.clientX+'px';
        placeholder.style.display = '';
        if (!backdrop.classList.contains('visible-backdrop')) {
            backdrop.classList.add('visible-backdrop');
        }
        item = returnPointLibraryItem(event, type, 80);
        if (item) {
            var rect = item.getBoundingClientRect(),
                next = (event.clientY - (rect.top + 80)) / (rect.bottom - rect.top) > .5,
                obj = {
                    "left" : rect.left + 16,
                    "width" : rect.right - rect.left - 30
                };
            if (next || item.classList.contains('ba-grid-column')) {
                obj.top = rect.bottom;
            } else {
                obj.top = rect.top;
            }
            $g(placeholder).css(obj);
        } else {
            placeholder.style.display = 'none';
        }
        return false;
    }).on('mouseup.library', function(event){
        libHandle.style.display = 'none';
        placeholder.style.display = 'none';
        backdrop.classList.remove('visible-backdrop');
        $g(document).off('mouseup.library mousemove.library');
        $g(app.editor.document).off('mouseup.library mousemove.library');
        var obj =  {
            "data" : item,
            "selector" : {
                id : id,
                type : type,
                globalItem : globalItem
            }
        };
        if (obj.data) {
            rect = obj.data.getBoundingClientRect();
            obj.selector.next = (event.clientY - (rect.top + 80)) / (rect.bottom - rect.top) > .5;
            app.editor.app.checkModule('setLibraryItem', obj);
        }
    });
    $g(app.editor.document).on('mousemove.library', function(event){
        libHandle.style.top = event.clientY+80+'px';
        libHandle.style.left = (event.clientX + (window.innerWidth - app.editor.innerWidth) / 2)+'px';
        placeholder.style.display = '';
        item = returnPointLibraryItem(event, type, 0);
        if (item) {
            var rect = item.getBoundingClientRect(),
                next = (event.clientY - (rect.top)) / (rect.bottom - rect.top) > .5,
                obj = {
                    "left" : rect.left + 16,
                    "width" : rect.right - rect.left - 30
                };
            if (next || item.classList.contains('ba-grid-column')) {
                obj.top = rect.bottom;
            } else {
                obj.top = rect.top;
            }
            $g(placeholder).css(obj);
        } else {
            placeholder.style.display = 'none';
        }
        return false;
    }).on('mouseup.library', function(event){
        libHandle.style.display = 'none';
        placeholder.style.display = 'none';
        $g(document).off('mouseup.library mousemove.library');
        $g(app.editor.document).off('mouseup.library mousemove.library');
        var obj =  {
            "data" : item,
            "selector" : {
                id : id,
                type : type,
                globalItem : globalItem
            }
        };
        if (obj.data) {
            rect = obj.data.getBoundingClientRect();
            obj.selector.next = (event.clientY - (rect.top)) / (rect.bottom - rect.top) > .5;
            app.editor.app.checkModule('setLibraryItem', obj);
        }
    });
    return false;
});

$g('#section-library-cell, #plugins-library-cell').on('mousedown', '.delete-from-library', function(event){
    app.itemDelete = this.dataset.id;
    if ($g(this).closest('.ba-library-item').find('.library-global-item').length > 0) {
        $g('#delete-dialog .global-library-delete').show();
        $g('#delete-dialog .can-delete').hide();
    } else {
        $g('#delete-dialog .global-library-delete').hide();
        $g('#delete-dialog .can-delete').show();
    }
    app.checkModule('deleteItem');
});

$g('#section-library-cell, #plugins-library-cell').on('mousedown', '.camera-container', function(event){
    app.itemDelete = this.dataset.id;
    uploadMode = 'reselectLibraryImage';
    checkIframe($g('#uploader-modal').attr('data-check', 'single'), 'uploader');
});

$g('span.add-to-menu').on('mousedown', function(){
    app.checkModule('addToMenu');
});

$g('span.context-edit-item').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.editor.$g(app.context.target).find('> .ba-edit-item .edit-item').trigger('mousedown');
    }
});
$g('span.context-add-new-row').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.editor.$g(app.context.target).find('> .ba-edit-item .add-columns').trigger('mousedown');
    }
});
$g('span.context-modify-columns').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.editor.$g(app.context.target).find('> .ba-edit-item .modify-columns').trigger('mousedown');
    }
});

$g('span.context-add-nested-row').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.editor.$g(app.context.target).find('> .ba-edit-item .add-columns-in-columns').trigger('mousedown');
    }
});
$g('span.context-add-new-element').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.editor.$g(app.context.target).find('> .ba-edit-item .add-item').trigger('mousedown');
    }
});

$g('span.context-add-to-library').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.editor.$g(app.context.target).find('> .ba-edit-item .add-library').trigger('mousedown');
    }
});
$g('span.context-delete-item').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.editor.$g(app.context.target).find('> .ba-edit-item .delete-item').trigger('mousedown');
    }
});
$g('span.context-copy-item').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        var flag = app.context.itemType == 'section' || app.context.itemType == 'row',
            html = flag ? app.context.target.parentNode.cloneNode(true) : app.context.target.cloneNode(true);
        $g(html).removeClass('active-context-item active-context-item-editing');
        if (app.context.item.type == 'overlay-button' && !html.querySelector('.ba-overlay-section-backdrop')) {
            var overlay =  app.editor.document.querySelector('.ba-overlay-section-backdrop[data-id="'+html.dataset.overlay+'"]');
            if (overlay) {
                overlay = overlay.cloneNode(true);
                html.appendChild(overlay);
            }
        } else if (app.context.item.type == 'row' || app.context.item.type == 'section') {
            $g(html).find('.ba-item-overlay-section').each(function(){
                var overlay =  app.editor.document.querySelector('.ba-overlay-section-backdrop[data-id="'+this.dataset.overlay+'"]');
                if (overlay) {
                    overlay = overlay.cloneNode(true);
                    this.appendChild(overlay);
                }
            });
        }
        app.buffer = {
            type: app.context.itemType,
            id : app.context.target.id,
            store: 'item',
            data: {
                html: html.outerHTML,
                items: $g.extend(true, {}, app.editor.app.items)
            }
        }
        var buffer = JSON.stringify(app.buffer);
        localStorage.setItem('gridboxBuffer', buffer);
    }
});
$g('span.context-copy-style').on('mousedown', function(){
    if (presetsPatern[app.context.itemType] && !this.classList.contains('disable-button')) {
        app.buffer = {
            type: app.context.itemType,
            store: 'style',
            data: {}
        }
        var patern = $g.extend(true, {}, presetsPatern[app.context.itemType]),
            is_object = null;;
        if (app.context.itemType == 'section' || app.context.itemType == 'row' || app.context.itemType == 'column') {
            patern.desktop.image = '';
            patern.desktop.video = '';
        }
        for (var ind in patern) {
            if (ind == 'desktop') {
                app.buffer.data[ind] = {};
                for (var key in patern[ind]) {
                    is_object = typeof(app.context.item[ind][key]) == 'object';
                    app.buffer.data[ind][key] = is_object ? $g.extend(true, {}, app.context.item[ind][key]) : app.context.item[ind][key];
                }
                for (var ind in app.editor.breakpoints) {
                    if (app.context.item[ind]) {
                        app.buffer.data[ind] = {};
                        for (var key in patern.desktop) {
                            is_object = typeof(app.context.item[ind][key]) == 'object';
                            if (is_object && app.context.item[ind][key]) {
                                app.buffer.data[ind][key] = $g.extend(true, {}, app.context.item[ind][key]);
                            } else if (!is_object && app.context.item[ind][key]) {
                                app.buffer.data[ind][key] = app.context.item[ind][key];
                            } else if (is_object) {
                                app.buffer.data[ind][key] = {};
                            }
                        }
                    }
                }
            } else {
                is_object = typeof(app.context.item[ind]) == 'object';
                app.buffer.data[ind] = is_object ? $g.extend(true, {}, app.context.item[ind]) : app.context.item[ind];
            }
        }
        var buffer = JSON.stringify(app.buffer);
        localStorage.setItem('gridboxBuffer', buffer);
    }
});
$g('span.context-copy-content').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        var html = app.context.target.cloneNode(true);
        $g(html).removeClass('active-context-item active-context-item-editing');
        $g(html).find('.ba-item-overlay-section').each(function(){
            var overlay =  app.editor.document.querySelector('.ba-overlay-section-backdrop[data-id="'+this.dataset.overlay+'"]');
            if (overlay) {
                overlay = overlay.cloneNode(true);
                this.appendChild(overlay);
            }
        });
        app.buffer = {
            type: app.context.itemType,
            store: 'content',
            data: {
                html: html.outerHTML,
                items: $g.extend(true, {}, app.editor.app.items)
            }
        }
        var buffer = JSON.stringify(app.buffer);
        localStorage.setItem('gridboxBuffer', buffer);
    }
});
$g('span.context-paste-buffer').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.editor.app.setNewFont = true;
        app.editor.app.fonts = {};
        app.editor.app.customFonts = {};
        if (app.buffer.store == 'style') {
            var is_object = null;
            for (var ind in app.buffer.data) {
                if (ind == 'desktop') {
                    for (var key in app.buffer.data[ind]) {
                        is_object = typeof(app.context.item[ind][key]) == 'object';
                        if (is_object) {
                            app.context.item[ind][key] = $g.extend(true, {}, app.buffer.data[ind][key]);
                        } else {
                            app.context.item[ind][key] = app.buffer.data[ind][key];
                        }
                    }
                    for (var ind in app.editor.breakpoints) {
                        if (app.buffer.data[ind]) {
                            for (var key in app.buffer.data.desktop) {
                                is_object = typeof(app.context.item[ind][key]) == 'object';
                                if (is_object && app.buffer.data[ind][key]) {
                                    app.context.item[ind][key] = $g.extend(true, {}, app.buffer.data[ind][key]);
                                } else if (!is_object && app.buffer.data[ind][key]) {
                                    app.context.item[ind][key] = app.buffer.data[ind][key];
                                } else if (is_object) {
                                    app.context.item[ind][key] = {};
                                } else {
                                    delete(app.context.item[ind][key]);
                                }
                            }
                        }
                    }
                } else {
                    is_object = typeof(app.context.item[ind]) == 'object';
                    app.context.item[ind] = is_object ? $g.extend(true, {}, app.buffer.data[ind]) : app.buffer.data[ind];
                }
            }
            app.editor.app.edit = app.context.target.id;
            app.editor.app.checkModule('sectionRules');
            if (app.context.item.desktop.shape && 'setShapeDividers' in window) {
                var str = '.ba-'+app.context.item.type.replace('column', 'grid-column');
                setShapeDividers(app.context.item, app.context.target.id);
            }
            if (app.context.item.type == 'progress-pie') {
                app.drawPieLine();
            }
            app.editor.app.checkModule('checkOverlay');
            app.editor.app.checkVideoBackground();
            app.editor.app.checkModule('loadParallax');
            app.addHistory();
        } else if (app.buffer.store == 'content') {
            app.editor.app.copyAction = 'context';
            app.editor.app.checkModule('copyItem');
        } else if (app.buffer.store == 'item') {
            app.editor.app.copyAction = 'context';
            app.editor.app.checkModule('copyItem');
        }
    }
});
$g('span.context-delete-content').on('mousedown', function(){
    if (!this.classList.contains('disable-button')) {
        app.itemDelete = null;
        app.deleteAction = 'context';
        app.checkModule('deleteItem');
    }
});
$g('span.context-reset-style').on('mousedown', function(){
    if (presetsPatern[app.context.itemType] && !this.classList.contains('disable-button')) {
        var patern = $g.extend(true, {}, presetsPatern[app.context.itemType]),
            is_object = null,
            theme = app.editor.app.theme,
            type = app.context.itemType,
            object = defaultElementsStyle[app.context.item.type];
        if (type == 'section' || type == 'row' || type == 'column') {
            patern.desktop.image = '';
            patern.desktop.video = '';
        }
        if (theme.defaultPresets[type] && theme.presets[type] && theme.presets[type][theme.defaultPresets[type]]) {
            object = $g.extend(true, object, theme.presets[type][theme.defaultPresets[type]].data);
        }
        for (var ind in patern) {
            if (ind == 'desktop') {
                for (var key in patern[ind]) {
                    is_object = typeof(app.context.item[ind][key]) == 'object';
                    app.context.item[ind][key] = is_object ? $g.extend(true, {}, object[ind][key]) : object[ind][key];
                }
                for (var ind in app.editor.breakpoints) {
                    if (app.context.item[ind]) {
                        for (var key in patern.desktop) {
                            is_object = typeof(app.context.item[ind][key]) == 'object';
                            if (is_object && object[ind] && object[ind][key]) {
                                app.context.item[ind][key] = $g.extend(true, {}, object[ind][key]);
                            } else if (!is_object && object[ind] && object[ind][key]) {
                                app.context.item[ind][key] = object[ind][key];
                            } else if (is_object) {
                                app.context.item[ind][key] = {};
                            } else {
                                delete(app.context.item[ind][key]);
                            }
                        }
                    }
                }
            } else {
                is_object = typeof(app.context.item[ind]) == 'object';
                app.context.item[ind] = is_object ? $g.extend(true, {}, object[ind]) : object[ind];
            }
        }
        app.editor.app.setNewFont = true;
        app.editor.app.fonts = {};
        app.editor.app.customFonts = {};
        app.editor.app.edit = app.context.target.id;
        app.editor.app.checkModule('sectionRules');
        if (app.context.item.desktop.shape && 'setShapeDividers' in window) {
            var str = '.ba-'+app.context.item.type.replace('column', 'grid-column');
            setShapeDividers(app.context.item, app.context.target.id);
        }
        if (app.context.item.type == 'progress-pie') {
            app.drawPieLine();
        }
        app.editor.app.checkModule('checkOverlay');
        app.editor.app.checkVideoBackground();
        app.editor.app.checkModule('loadParallax');
        app.addHistory();
    }
});

app.modules.showContext = true;
app.showContext();