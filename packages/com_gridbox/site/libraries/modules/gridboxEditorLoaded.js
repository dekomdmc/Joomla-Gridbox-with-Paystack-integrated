/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.buttonsPrevent = function(){
    $g('a, input[type="submit"], button').on('click', function(event){
        event.preventDefault();
    });
}

app.checkAnimation = function(){
    app.viewportItems = new Array();
    $g('.ba-section, .ba-row, .ba-grid-column').each(function(){
        if (app.items[this.id] && $g(this).closest('.ba-item-content-slider').length == 0) {
            var object = $g.extend(true, {}, app.items[this.id].desktop.animation);
            if (app.view != 'desktop') {
                for (var ind in breakpoints) {
                    if (!app.items[this.id][ind]) {
                        app.items[this.id][ind] = {
                            animation : {}
                        };
                    }
                    object = $g.extend(true, {}, object, app.items[this.id][ind].animation);
                    if (ind == app.view) {
                        break;
                    }
                }
            }
            if (object.effect && app.items[this.id].type != 'sticky-header' && !this.classList.contains('visible')) {
                var obj = {
                    effect : object.effect,
                    item : $g(this)
                }
                app.viewportItems.push(obj);
            } else if (object.effect) {
                $g(this).addClass('visible');
            }
        }
    });
    if (app.viewportItems.length > 0) {
        app.checkModule('loadAnimations');
    }
}

app.checkOverlay = function(obj, key){
    $g('.ba-item-overlay-section').each(function(){
        var overlay = $g(this).find('.ba-overlay-section-backdrop');
        if (overlay.length > 0) {
            document.body.appendChild(overlay[0]);
        }
    });
}

app.setMediaRules = function(obj, key, callback){
    var desktop =  $g.extend(true, {}, obj.desktop),
        str = '';
    if (disableResponsive) {
        return str;
    }
    for (var ind in breakpoints) {
        app.breakpoint = ind;
        if (!obj[ind]) {
            obj[ind] = {};
        }
        var object = $g.extend(true, {}, desktop, obj[ind]);
        str += "@media (max-width: "+breakpoints[ind]+"px) {"
        str += window[callback](object, key, obj.type);
        str += "}";
        desktop =  $g.extend(true, {}, object);
    }
    
    return str;
}

app.checkVideoBackground = function(){
    var flag = false;
    $g('.ba-section, .ba-row, .ba-grid-column').each(function(){
        if (app.items[this.id] && app.items[this.id].desktop.background.type == 'video') {
            flag = true;
            return false;
        }
    });
    $g('.ba-item-flipbox').each(function(){
        if (app.items[this.id] && app.items[this.id].sides.frontside.desktop.background.type == 'video') {
            flag = true;
            return false;
        }
        if (app.items[this.id] && app.items[this.id].sides.backside.desktop.background.type == 'video') {
            flag = true;
            return false;
        }
    });
    if (app.theme.desktop.background.type == 'video') {
        flag = true;
    }
    if (flag) {
        app.checkModule('createVideo', {});
    }
}

app.listenMessage = function(obj){
    app.checkModule(obj.callback, obj);
}

app.checkView = function(){
    var width = $g(window).width();
    app.view = 'desktop';
    for (var ind in breakpoints) {
        if (width <= breakpoints[ind]) {
            app.view = ind;
        }
    }
}

app.resize = function(){
    clearTimeout(delay);
    app.checkView();
    delay = setTimeout(function(){
        if ($g('.ba-item-map').length > 0) {
            $g('.ba-item-map').each(function(){
                app.initmap(app.items[this.id], this.id);
            });
        }
        if ('setPostMasonryHeight' in window) {
            $g('.ba-blog-posts-wrapper.ba-masonry-layout').each(function(){
                var key = $g(this).closest('.ba-item').attr('id');
                setPostMasonryHeight(key);
            });
        }
        if ('setGalleryMasonryHeight' in window) {
            $g('.instagram-wrapper.simple-gallery-masonry-layout').each(function(){
                setGalleryMasonryHeight(this.closest('.ba-item').id);
            });
        }
    }, 300);
}

var lightboxVideo = {};

function lightboxVideoClose(item)
{
    var iframes = item.querySelectorAll('.ba-item-custom-html iframe, .ba-item-video iframe');
    for (var i = 0; i < iframes.length; i++) {
        var src = iframes[i].src,
            videoId = iframes[i].id;
        if (!lightboxVideo[videoId]) {
            continue;
        }
        if (src && src.indexOf('youtube.com') !== -1 && 'pauseVideo' in lightboxVideo[videoId]) {
            lightboxVideo[videoId].pauseVideo();
        } else if (src && src.indexOf('vimeo.com') !== -1 && 'pause' in lightboxVideo[videoId]) {
            lightboxVideo[videoId].pause();
        }
    }
    iframes = item.querySelectorAll('.ba-item-video video, .ba-item-custom-html video');
    for (var i = 0; i < iframes.length; i++) {
        var videoId = iframes[i].id;
        if (!lightboxVideo[videoId]) {
            continue;
        }
        lightboxVideo[videoId].pause();
    }
}

function lightboxVideoOpen(item)
{
    var iframes = item.querySelectorAll('.ba-item-custom-html iframe, .ba-item-video iframe'),
        youtube = false,
        vimeo = false,
        id = +new Date();
    for (var i = 0; i < iframes.length; i++) {
        var src = iframes[i].src,
            videoId;
        if (src && src.indexOf('youtube.com') !== -1) {
            if (!app.youtube) {
                youtube = true;
            } else {
                if (src.indexOf('enablejsapi=1') === -1) {
                    if (src.indexOf('?') === -1) {
                        src += '?';
                    } else {
                        src += '&'
                    }
                    src += 'enablejsapi=1';
                    iframes[i].src = src;
                }
                if (!iframes[i].id) {
                    iframes[i].id = id++;
                }
                videoId = iframes[i].id;
                if (!lightboxVideo[videoId] || !('playVideo' in lightboxVideo[videoId])) {
                    lightboxVideo[videoId] = new YT.Player(videoId, {
                        events: {
                            onReady: function(event){
                                lightboxVideo[videoId].playVideo();
                            }
                        }
                    });
                } else {
                    lightboxVideo[videoId].playVideo();
                }
            }
        } else if (src && src.indexOf('vimeo.com') !== -1) {
            if (!app.vimeo) {
                vimeo = true;
            } else {
                if (!iframes[i].id) {
                    iframes[i].id = id++;
                }
                videoId = iframes[i].id;
                if (!lightboxVideo[videoId] || !('play' in lightboxVideo[videoId])) {
                    src = src.split('/');
                    src = src.slice(-1);
                    src = src[0].split('?');
                    src = src[0];
                    var options = {
                        id: src * 1,
                        loop: true,
                    };
                    lightboxVideo[videoId] = new Vimeo.Player(videoId, options);
                }
                lightboxVideo[videoId].play();
            }
        }
    }
    iframes = item.querySelectorAll('.ba-item-video video, .ba-item-custom-html video');
    for (var i = 0; i < iframes.length; i++) {
        if (!iframes[i].id) {
            iframes[i].id = id++;
        }
        videoId = iframes[i].id;
        if (!lightboxVideo[videoId]) {
            lightboxVideo[videoId] = iframes[i];
        }
        lightboxVideo[videoId].play();
    }
    if (youtube || vimeo) {
        var object = {
            data : {}
        };
        if (youtube && !vimeo) {
            object.data.type = 'youtube';
        } else if (vimeo && !youtube) {
            object.data.type = 'vimeo';
        } else {
            object.data.type = 'youtube+vimeo';
        }
        app.checkModule('loadVideoApi', object);
    }
    if (youtube) {
        lightboxVideo.overlay = item;
    } else if (vimeo) {
        lightboxVideo.overlay = item;
    }

    return !youtube && !vimeo;
}

function showLightbox($this)
{
    if (!lightboxVideoOpen($this)) {
        return false;
    }
    $this.classList.add('visible-lightbox');
    document.body.classList.add('ba-lightbox-open');
    if (app.items[app.edit].position == 'lightbox-center') {
        var width = window.innerWidth - document.documentElement.clientWidth;
        document.body.classList.add('lightbox-open');
        document.body.style.width = 'calc(100% - '+width+'px)';
    }
}

app.initStickyHeaderPanel = function($this){
    var div = window.parent.document.createElement('div'),
        panel = '<p>Sticky Header</p>',
        panels = window.parent.document.getElementById('lightbox-panels');
    panel += '<span><i class="zmdi zmdi-edit"></i><span class="ba-tooltip settings-tooltip ba-top">'+
        'Edit</span></span><span><i class="zmdi zmdi-close"></i><span class="ba-tooltip'+
        ' settings-tooltip ba-top">Close</span></span><span><i class="zmdi '+
        'zmdi-delete"></i><span class="ba-tooltip settings-tooltip ba-top">Delete</span></span>';
    div.dataset.id = $this.id;
    div.className = 'lightbox-options-panel';
    div.innerHTML = panel;
    panels.appendChild(div);
    $g(div).find('i.zmdi-delete').off('click').on('click', function(){
        $g('#'+this.parentNode.parentNode.dataset.id).find(' > .ba-edit-item .delete-item').trigger('mousedown');
    });
    $g(div).find('i.zmdi-close').off('click').on('click', function(){
        $g('#'+this.parentNode.parentNode.dataset.id).parent().removeClass('visible-sticky-header');
        document.body.classList.remove('sticky-header-opened');
    });
    $g(div).find('i.zmdi-edit').off('click').on('click', function(){
        var section = $g('#'+this.parentNode.parentNode.dataset.id),
            animation = app.items[this.parentNode.parentNode.dataset.id].desktop.animation,
            top = window.pageYOffset;
        section.addClass(animation.effect);
        document.body.classList.add('sticky-header-opened');
        setTimeout(function(){
            section.removeClass(animation.effect);
        }, animation.delay * 1 + animation.duration * 1000);
        section.parent().addClass('visible-sticky-header').css('top', 40 - top);
        section.find(' > .ba-edit-item .edit-item').trigger('mousedown');
    });
}

app.initLightboxPanel = function($this){
    if ($g($this).closest('.ba-item-blog-content').length > 0) {
        return false;
    }
    var div = window.parent.document.createElement('div'),
        panel = '<p>Lightbox</p>',
        panels = window.parent.document.getElementById('lightbox-panels');
    if (app.items[$this.dataset.id] && app.items[$this.dataset.id].type == 'cookies') {
        panel = '<p>Cookies</p>'
    }
    panel += '<span><i class="zmdi zmdi-edit"></i><span class="ba-tooltip';
    panel += ' settings-tooltip ba-top">Edit</span></span>';
    if (app.items[$this.dataset.id] && app.items[$this.dataset.id].type == 'cookies') {
        panel += '<span><i class="zmdi zmdi-close"></i><span class="ba-tooltip';
        panel += ' settings-tooltip ba-top">Close</span></span>';
    }
    panel += '<span><i class="zmdi ';
    panel += 'zmdi-delete"></i><span class="ba-tooltip settings-tooltip ba-top">Delete</span></span>';
    div.dataset.id = $this.dataset.id;
    div.className = 'lightbox-options-panel';
    div.innerHTML = panel;
    panels.appendChild(div);
    $g(div).find('i.zmdi-delete').off('click').on('click', function(){
        $g('#'+this.parentNode.parentNode.dataset.id).find(' > .ba-edit-item .delete-item').trigger('mousedown');
    });
    $g(div).find('i.zmdi-close').off('click').on('click', function(){
        $g('.ba-lightbox-backdrop[data-id="'+this.parentNode.parentNode.dataset.id+'"]').removeClass('visible-lightbox');
        lightboxVideoClose($g('.ba-lightbox-backdrop[data-id="'+this.parentNode.parentNode.dataset.id+'"]')[0]);
        document.body.style.width = '';
        $g('body').removeClass('lightbox-open ba-lightbox-open');
    });
    $g(div).find('i.zmdi-edit').off('click').on('click', function(){
        $g('div.ba-lightbox-close').trigger('click');
        $g(panels).find('i.zmdi-close').trigger('click');
        app.edit = this.parentNode.parentNode.dataset.id;
        var item = document.querySelector('.ba-lightbox-backdrop[data-id="'+app.edit+'"]'),
            width = window.innerWidth - document.documentElement.clientWidth;
        if (app.items[app.edit][app.view].disable == 1 && !document.body.classList.contains('show-hidden-elements')) {
            item.classList.remove('visible-lightbox');
            document.body.classList.remove('lightbox-open');
            document.body.classList.remove('ba-lightbox-open');
            document.body.style.width = '';
        } else {
            showLightbox(item);
        }
        if (app.items[app.edit].type == 'cookies') {
            $g('#'+this.parentNode.parentNode.dataset.id).find(' > .ba-edit-item .edit-item').trigger('mousedown');
        } else {
            window.parent.app.edit = app.items[app.edit];
            window.parent.app.checkModule('lightboxEditor');
        }
    });
}

app.init = function(){
    var str = '> .ba-wrapper:not(.ba-lightbox):not(.ba-overlay-section):not(.ba-sticky-header)';
    str += ':not(.tabs-content-wrapper) > .ba-section > .ba-section-items';
    if (themeData.edit_type) {
        document.body.classList.add('ba-'+themeData.edit_type+'-editing');
    }
    makeRowSortable($g('header.header, footer.footer, #ba-edit-section').find(str), 'row');
    str = '.tabs-content-wrapper > .ba-section > .ba-section-items'
    makeRowSortable($g(str), 'row');
    str = '.ba-wrapper:not(.ba-lightbox):not(.ba-overlay-section):not(.ba-sticky-header)';
    str += ' > .ba-section > .ba-section-items';
    str += ' > .ba-row-wrapper > .ba-row > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column';
    str += ', .ba-item-flipbox > .ba-flipbox-wrapper > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column';
    str += ', .ba-item-content-slider > .slideshow-wrapper > ul > .slideshow-content > li > .ba-grid-column';
    makeColumnSortable($g('header.header, footer.footer, #ba-edit-section').find(str), 'column');
    str = ' > .ba-section > .ba-section-items';
    makeRowSortable($g('.ba-lightbox, .ba-overlay-section, .ba-sticky-header').find(str), 'lightbox-row');
    str += ' > .ba-row-wrapper > .ba-row > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column';
    makeColumnSortable($g('.ba-lightbox,.ba-overlay-section,.ba-wrapper[data-megamenu],.ba-sticky-header').find(str), 'lightbox-column');
    app.buttonsPrevent();
    $g('.ba-section').each(function(){
        if ($g(this).closest('#ba-edit-section').length != 0 || $g('body').hasClass('blog-post-editor')) {
            $g(this).find('> .ba-edit-item .ba-buttons-wrapper').each(function(){
                if ($g(this).find('.ba-edit-wrapper').length != 5) {
                    this.innerHTML = '<span class="ba-edit-wrapper"><i class="zmdi zmdi-plus-circle add-columns"></i>'+
                        '<span class="ba-tooltip tooltip-delay settings-tooltip">'+top.gridboxLanguage['ADD_NEW_ROW']+'</span></span>'+
                        '<span class="ba-edit-wrapper"><i class="zmdi zmdi-edit edit-item"></i>'+
                        '<span class="ba-tooltip tooltip-delay settings-tooltip">'+top.gridboxLanguage['EDIT']+'</span></span>'+
                        '<span class="ba-edit-wrapper"><i class="zmdi zmdi-copy copy-item"></i>'+
                        '<span class="ba-tooltip tooltip-delay settings-tooltip">'+top.gridboxLanguage['COPY_ITEM']+'</span></span>'+
                        '<span class="ba-edit-wrapper"><i class="zmdi zmdi-globe add-library"></i>'+
                        '<span class="ba-tooltip tooltip-delay settings-tooltip">'+top.gridboxLanguage['ADD_TO_LIBRARY']+'</span></span>'+
                        '<span class="ba-edit-wrapper"><i class="zmdi zmdi-delete delete-item"></i>'+
                        '<span class="ba-tooltip tooltip-delay settings-tooltip">'+top.gridboxLanguage['DELETE_ITEM']+'</span></span>'+
                        '<span class="ba-edit-text">'+top.gridboxLanguage['SECTION']+'</span>';
                }
            });
        }
        editItem(this.id);
        setColumnResizer(this);
    });
    $g('.ba-item-preloader').each(function(){
        editItem(this.id);
    })
    $g('.ba-item').each(function(){
        if (app.items[this.id]) {
            var obj = {
                data : app.items[this.id],
                selector : this.id
            };
            itemsInit.push(obj);
        }
        if (this.classList.contains('ba-item-blog-content')) {
            if (this.querySelector('.ba-item')) {
                this.classList.remove('empty-blog-content');
            } else {
                this.classList.add('empty-blog-content');
            }
        }
    });
    if (itemsInit.length > 0) {
        app.checkModule('initItems', itemsInit.pop());
    }
    app.checkVideoBackground();
    $g('.ba-lightbox-backdrop').find('.ba-lightbox-close').off('click').on('click', function(){
        $g(this).closest('.ba-lightbox-backdrop').removeClass('visible-lightbox');
        document.body.style.width = '';
        $g('body').removeClass('lightbox-open');
        document.body.classList.remove('ba-lightbox-open');
        lightboxVideoClose($g(this).closest('.ba-lightbox-backdrop')[0]);
    });
    window.parent.document.getElementById('lightbox-panels').innerHTML = '';
    $g('.ba-lightbox').each(function(){
        app.initLightboxPanel(this);
    });
    $g('.ba-sticky-header > .ba-section').each(function(){
        app.initStickyHeaderPanel(this);
    });
    app.checkModule('loadParallax');
}

function restoreTabs(id)
{
    if (!app.items[id]) {
        var item = $g('#'+id),
            obj = null;
        if (item.hasClass('ba-section')) {
            obj = $g.extend(true, {}, top.defaultElementsStyle.section);
            obj.desktop.padding = {
                top: 0,
                right: 0,
                bottom: 0,
                left: 0
            }
        } else if (item.hasClass('ba-row')) {
            obj = $g.extend(true, {}, top.defaultElementsStyle.row);
        } else if (item.hasClass('ba-grid-column')) {
            obj = $g.extend(true, {}, top.defaultElementsStyle.column);
        } else if (item.hasClass('ba-item')) {
            var match = item[0].className.match(/ba-item-[-\w]+/);
            if (match) {
                var type = match[0].replace('ba-item-', '');
                if (top.defaultElementsStyle[type]) {
                    obj = $g.extend(true, {}, top.defaultElementsStyle[type]);
                }
            }
        }
        if (obj) {
            if (obj.desktop.margin) {
                obj.desktop.margin = {
                    top: 0,
                    bottom: 0
                }
            }
            app.items[id] = obj;
        }
    }
}

function getCKECSSrulesString()
{
    var str = 'body.cke_editable {font-family: Arial, Helevtica, sans-serif;}';
    str += ' body.cke_editable img {max-width: 100%;}';
    str += 'a { text-decoration: none; } :focus { outline: none; }';
    str += 'html {';
    for (let ind in app.theme.colorVariables) {
        str += ind.replace('@', '--')+': '+app.theme.colorVariables[ind].color+';';
    }
    str += '}';
    str += 'a[name] {border: 1px dotted #1da6f4;; padding: 0 5px 0 0;} a[name]:before ';
    str += '{content: "\\2693"; font-size: inherit; color: #1da6f4;padding: 0px 5px;}';

    return str;
}

app.initGridboxEditor = function(){
    if (document.querySelector('.blog-post-editor-header-panel')) {
        app.blogEditor = {
            setSelection: function(){
                this.selection = window.getSelection();
                if (this.selection.rangeCount > 0) {
                    this.string = this.selection.toString();
                    this.html = this.selection.toString();
                    this.range = this.selection.getRangeAt(0);
                    this.start = this.range.startContainer;
                    this.end = this.range.endContainer;
                    this.startTags = $g(this.start).parentsUntil('.content-text');
                    this.endTags = $g(this.end).parentsUntil('.content-text');
                }
            },
            checkActive: function(){
                
            },
            copyPastText: function(){
                app.blogEditor.setSelection();
                app.blogEditor.checkActive();
                app.blogEditor.range.deleteContents();
                var content = $g(app.blogEditor.start).closest('.content-text'),
                    start = content.find('> *:first-child')[0],
                    data;
                app.blogEditor.range.setStartBefore(start);
                data = app.blogEditor.range.extractContents();
                if (app.blogEditor.end && app.blogEditor.end.localName) {
                    app.blogEditor.end.parentNode.removeChild(app.blogEditor.end);
                }
                app.edit = content.closest('.ba-item-text')[0].id;
                if (!data.textContent && !data.querySelector('img') && data.querySelectorAll('p').length == 1) {
                    var target = content.closest('.ba-item-text').next();
                    content.closest('.ba-item-text').before(target);
                } else {
                    app.checkModule('copyItem');
                    var copyText = content.closest('.ba-item-text').next(),
                        target = copyText.next();
                    target.after(copyText);
                    content.closest('.content-text').html(data);
                }
                setTextPlaceholder(content[0]);
                $g('.blog-posts-add-plugins').hide();
                window.parent.app.addHistory();
            },
            insertPlugins: function(){
                app.copyAction = 'blogPostsText';
                app.edit = $g(app.blogEditor.start).closest('.ba-grid-column')[0].id;
                window.parent.app.checkModule('addPlugins');
            },
            insertImage: function(){
                app.copyAction = 'blogPostsText';
                app.edit = $g(app.blogEditor.start).closest('.ba-grid-column')[0].id;
                top.uploadMode = 'itemImage';
                top.checkIframe(top.$g('#uploader-modal').attr('data-check', 'single'), 'uploader');
            },
            insertVideo: function(){
                app.copyAction = 'blogPostsText';
                app.edit = $g(app.blogEditor.start).closest('.ba-grid-column')[0].id;
                var obj = {
                    data : 'video',
                    selector : 0,
                }
                app.checkModule('loadPlugin' , obj);
            }
        }
        app.checkModule('copyItem');
        $g('body').on('mouseup', function(event){
            if (event.target && event.target.closest('.content-text')) {
                app.blogEditor.setSelection();
                app.blogEditor.checkActive();
            }
        });
        $g(document).on('mouseenter', '.content-text[title]', function(){
            $g(this).removeAttr('title');
        });
        $g('.advanced-blog-editor-toggle').on('change', function(){
            if (this.checked) {
                document.body.classList.add('advanced-blog-editor');
                top.document.body.classList.add('advanced-blog-editor');
            } else {
                document.body.classList.remove('advanced-blog-editor');
                top.document.body.classList.remove('advanced-blog-editor');
            }
            localStorage.setItem('advanced-blog-editor', this.checked);
        });
    }
    if (typeof(top.CKEDITOR) != 'undefined') {
        top.CKEDITOR.config.contentsCss = [getCKECSSrulesString()];
    }
    $g('#ba-edit-section').sortable({
        handle : '.ba-wrapper > .ba-section > .ba-edit-item .edit-settings',
        change: function(element){
            $g(element).find('.ba-item').each(function(){
                if (app.items[this.id]) {
                    initMapTypes(app.items[this.id].type, this.id);
                }
            });
            window.parent.app.addHistory();
        },
        selector : '> .ba-wrapper:not(.ba-lightbox):not(.ba-overlay-section)',
        group : 'section'
    });
    $g('body').on('contextmenu', '.ba-item, .ba-row, .ba-section, .ba-grid-column, .ba-item [contenteditable="true"]', function(event){
        var stop = true;
        if (event.currentTarget.classList.contains('ba-grid-column') && event.currentTarget.parentNode.localName == 'li') {
            stop = false;
        }
        if (stop) {
            restoreTabs(event.currentTarget.id);
            var target = event.currentTarget,
                type = app.items[target.id] ? app.items[target.id].type : '',
                flag = false,
                obj = {
                    event: event,
                    target: target,
                    type: 'contextEvent'
                };
            type = type.replace('header', 'section').replace('footer', 'section').replace('overlay-section', 'section')
                .replace('lightbox', 'section').replace('cookies', 'section').replace('mega-menu-section', 'section')
                .replace('sticky-header', 'section');
            if (flag = (target.classList.contains('ba-section') && app.items[target.id] && type == 'section')) {
                obj.context = 'section-context-menu';
            } else if (flag = (target.classList.contains('ba-row') && app.items[target.id] && type == 'row')) {
                obj.context = 'row-context-menu';
            } else if (flag = (target.classList.contains('ba-grid-column') && app.items[target.id] && type == 'column')) {
                obj.context = 'column-context-menu';
            } else if (flag = (target.classList.contains('ba-item') && app.items[target.id] && type != 'blog-content')) {
                obj.context = 'plugin-context-menu';
            }
            if ($g(target).closest('.ba-user-level-edit-denied').length > 0) {
                flag = false;
            }
            if (flag) {
                obj.itemType = type;
                obj.item = app.items[target.id];
                top.app.context = obj;
                top.app.checkModule('showContext');
            }
            if (!target.hasAttribute('contenteditable')) {
                event.preventDefault();
            }
            event.stopPropagation();
        }
    });
    $g('body').on('mouseover', '.ba-flipbox-wrapper > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column > .ba-item',
        function(event){
        var item = this,
            $this = $g(this),
            top = left = '';
        if (!$this.hasClass('sortable-helper') && !$this.hasClass('sortable-placeholder')) {
            var rect = item.getBoundingClientRect(),
                obj = app.items[item.id],
                parent = $this.closest('.ba-grid-column')[0].getBoundingClientRect();
            if (item.classList.contains('ba-row')) {
                top = rect.top - 25;
                left = rect.right - 100;
            } else {
                top = rect.top - 25 + ((rect.bottom - rect.top) / 2);
                left = parent.left - 25 + ((parent.right - parent.left) / 2);
            }
            if (obj && (obj.type == 'accordion' || obj.type == 'tabs')) {
                if (obj.type == 'tabs' && obj.position == 'tabs-left') {
                    left = rect.left + 10;
                } else if (obj.type == 'tabs' && obj.position == 'tabs-right') {
                    left = rect.right - 60;
                } else {
                    top = rect.top + 10;
                }
            }
        }
        $this.find('> .ba-edit-item').css({
            'top': top,
            'left': left
        });
    });
    $g(window).on('scroll', function(){
        $g('.ba-flipbox-wrapper > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column > .ba-item').each(function(){
            var item = this,
                $this = $g(this),
                top = left = '';
            if (!$this.hasClass('sortable-helper') && !$this.hasClass('sortable-placeholder')) {
                var rect = item.getBoundingClientRect(),
                    obj = app.items[item.id],
                    parent = $this.closest('.ba-grid-column')[0].getBoundingClientRect();
                if (item.classList.contains('ba-row')) {
                    top = rect.top - 25;
                    left = rect.right - 100;
                } else {
                    top = rect.top - 25 + ((rect.bottom - rect.top) / 2);
                    left = parent.left - 25 + ((parent.right - parent.left) / 2);
                }
                if (obj && (obj.type == 'accordion' || obj.type == 'tabs')) {
                    if (obj.type == 'tabs' && obj.position == 'tabs-left') {
                        left = rect.left + 10;
                    } else if (obj.type == 'tabs' && obj.position == 'tabs-right') {
                        left = rect.right - 60;
                    } else {
                        top = rect.top + 10;
                    }
                }
            }
            $this.find('> .ba-edit-item').css({
                'top': top,
                'left': left
            });
        });
    });
    app.checkAnimation();
    window.parent.app.loadModule('defaultElementsStyle');
    window.addEventListener('resize', app.resize);
    $g(window).on('scroll', function(){
        var top = window.pageYOffset,
            header = app.query('header.header');
        if (header) {
            if (!('lastPageYOffset' in window)) {
                window.lastPageYOffset = top;
            }
            if (top > 40) {
                header.classList.add('fixed-header');
            } else {
                header.classList.remove('fixed-header');
            }
            if (getComputedStyle(header).position == 'fixed' && header.style.top != (40 - top)+'px' && 40 - top > 0) {
                header.style.top = (40 - top)+'px';
            } else if (header.style.top != '') {
                header.style.top = '';
            }
            $g('.ba-sticky-header').each(function(){
                var section = this.querySelector('.ba-sticky-header > .ba-section'),
                    obj = app.items[section.id],
                    offset = obj.desktop.offset;
                if (app.view != 'desktop') {
                    for (var ind in breakpoints) {
                        if (!obj[ind]) {
                            obj[ind] = {};
                        }
                        offset = obj[ind].offset ? obj[ind].offset : offset;
                        if (ind == app.view) {
                            break;
                        }
                    }
                }
                if (!this.classList.contains('visible-sticky-header')) {
                    if (top - 40 >= offset * 1 && (!obj.scrollup || (obj.scrollup && top - window.lastPageYOffset < 0))) {
                        this.classList.add('visible-sticky-header');
                        document.body.classList.add('sticky-header-opened');
                        if (obj.desktop.animation.effect) {
                            section.classList.add(obj.desktop.animation.effect);
                            setTimeout(function(){
                                section.classList.remove(obj.desktop.animation.effect);
                            }, obj.desktop.animation.delay * 1 + obj.desktop.animation.duration * 1000);
                        }
                    }
                }
                if ((top - 40 < offset * 1 && !obj.scrollup) || (obj.scrollup && (top - window.lastPageYOffset > 0
                    || top - 40 <= offset * 1))) {
                    this.classList.remove('visible-sticky-header');
                    document.body.classList.remove('sticky-header-opened');
                }
            });
            window.lastPageYOffset = top;
        }
    });
    $g('body').on('mousedown', function(event){
        top.app.closeOpenedModal(event);
        top.$g('.all-tags li').hide();
        top.$g('body').off('click.customHide');
        top.$g('.visible-select').parent().trigger('customHide');
        top.$g('.visible-select').removeClass('visible-select');
        if (top._dynarch_popupCalendar) {
            top._dynarch_popupCalendar.callCloseHandler();
        }
    });
    app.pageCss = {};
    app.style = $g('#global-css-sheets style');
    $g('#custom-css-editor').each(function(){
        if (this.dataset.enabled == 1) {
            var code = $g(this).find('.custom-css-editor-code').text();
            $g(this).find('> style').html(code);
        }
    });
    $g('body .modal').on('mousedown', function(event){
        $g(document).trigger(event);
        event.stopPropagation();
    });
    if ($g('.ba-item-overlay-section').length > 0) {
        app.checkModule('checkOverlay');
    }
    app.init();
    $g('.ba-add-section').on('mousedown', function(){
        window.parent.document.getElementById('add-section-dialog').classList.remove('add-columns');
        window.parent.app.checkModule('addSection');
    });
    window.parent.app.checkModule('windowLoaded');
}

app.gridboxEditorLoaded = function(){
    if ('defaultElementsBox' in window) {
        $g('.ba-item').each(function(){
            var className = this.className,
                match = className.match(/[-\w]+/g)
            if (match[0] == 'ba-item-post-intro' || match[0] == 'ba-item-blog-content') {
                $g(this).append(defaultElementsBox[match[0]].edit);
                if (defaultElementsBox[match[0]].box) {
                    $g(this).append(defaultElementsBox[match[0]].box);
                }
                if (!themeData.app_type || themeData.app_type == 'blog') {
                    $g(this).find('> .ba-edit-item .delete-item').closest('.ba-edit-wrapper').remove();
                    if (match[0] == 'ba-item-blog-content') {
                        $g(this).find('> .ba-edit-item > .ba-buttons-wrapper').remove();
                    }
                }
            } else if (defaultElementsBox[match[0]] && $g(this).find('> .ba-edit-item').length == 0) {
                $g(this).append(defaultElementsBox[match[0]].edit);
                $g(this).append(defaultElementsBox[match[0]].box);
                if (this.dataset.cookie) {
                    $g(this).find('> .ba-edit-item .ba-buttons-wrapper .ba-edit-wrapper:not(:first-child)').remove();
                };
            }
        });
        $g('.ba-row-wrapper > .ba-row').each(function(){
            if (defaultElementsBox['ba-row'] && $g(this).find('> .ba-edit-item').length == 0) {
                $g(this).append(defaultElementsBox['ba-row'].edit);
                $g(this).append(defaultElementsBox['ba-row'].box);
            }
        });
        $g('.ba-grid-column').each(function(){
            if ($g(this).find('> .ba-edit-item').length == 0 &&
                ($g(this).closest('.ba-row-wrapper').parent().hasClass('ba-grid-column') ||
                    !$g(this).closest('.ba-wrapper').hasClass('tabs-content-wrapper'))) {
                $g(this).append(defaultElementsBox['ba-grid-column'].edit);
                $g(this).append(defaultElementsBox['ba-grid-column'].box);
            }
        });
    }
    $g('.ba-item-text .content-text a[data-link]').removeAttr('data-cke-saved-href').each(function(){
        this.href = this.dataset.link;
    });
    var POST_CONTENT = top.gridboxLanguage['POST_CONTENT'] ? top.gridboxLanguage['POST_CONTENT'] : 'Post Content';
    $g('.ba-item-blog-content .empty-list p').text(POST_CONTENT);
    $g('.open-search-results').remove();
    $g(window).on('keydown', function(event){
        window.parent.$g(window.parent).trigger(event);
    });
    $g('body').on('keydown', '.content-text[contenteditable]', function(event){
        event.stopPropagation();
    });
    for (var key in gridboxItems) {
        if (key != 'theme') {
            app.items = $g.extend(true, app.items, gridboxItems[key]);
        } else if (!gridboxItems.theme.desktop.body) {
            gridboxItems.theme.desktop.body = $g.extend(true, {}, gridboxItems.theme.desktop.p);
        }
    }
    for (var ind in app.items) {
        if (app.items[ind].type == 'footer') {
            app.footer = app.items[ind];
            break;
        }
    }
    app.theme = gridboxItems.theme;
    window.parent = window.top;
    app.preloader = window.parent.document.querySelector('.preloader');
    app.initGridboxEditor();
    /*
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getItems",
        data: themeData,
        complete: function(msg){
            var data = JSON.parse(msg.responseText);
            for (var key in data) {
                if (key != 'theme') {
                    app.items = $g.extend(true, app.items, data[key]);
                } else if (!data.theme.desktop.body) {
                    data.theme.desktop.body = $g.extend(true, {}, data.theme.desktop.p);
                }
            }
            for (var ind in app.items) {
                if (app.items[ind].type == 'footer') {
                    app.footer = app.items[ind];
                    break;
                }
            }
            app.theme = data.theme;
            window.parent = window.top;
            app.preloader = window.parent.document.querySelector('.preloader');
            app.initGridboxEditor();
        }
    });*/
}

function checkMegamenuLibrary(item)
{
    var nested = window.parent.gridboxLanguage ? window.parent.gridboxLanguage['NESTED_ROW'] : 'Nested Row';
    item.find('.ba-grid-column > .ba-row-wrapper > .ba-row > .ba-edit-item .edit-settings .ba-tooltip').text(nested);
    item.find('.ba-grid-column > .ba-edit-item').each(function(){
        var $this = $g(this),
            wrapper = $this.closest('.ba-wrapper');
        $this.find('.add-library-item').parent().remove();
        if ($this.find('.add-columns-in-columns').length == 0) {
            var str = '<span class="ba-edit-wrapper"><i class="zmdi zmdi-sort-amount-desc add-columns-in-columns"></i>',
                lib = window.parent.gridboxLanguage ? window.parent.gridboxLanguage['ADD_NESTED_ROW'] : 'Add Nested Row';
            str += '<span class="ba-tooltip tooltip-delay settings-tooltip">'+lib;
            str += '</span></span>';
            var icon = $this.find('.ba-edit-wrapper:last-child').after(str).next();
        }
        if (wrapper.attr('data-megamenu') || wrapper.hasClass('ba-overlay-section')
            || wrapper.hasClass('ba-lightbox')) {
            var str = '<span class="ba-edit-wrapper"><i class="zmdi zmdi-collection-text add-library-item"></i>',
                lib = window.parent.gridboxLanguage ? window.parent.gridboxLanguage['LIBRARY'] : 'Library';
            str += '<span class="ba-tooltip tooltip-delay settings-tooltip">'+lib;
            str += '</span></span>';
            var icon = $this.find('.ba-edit-wrapper:last-child').after(str).next();
        }
    });
    item.find('.ba-edit-item').each(function(){
        var $this = $g(this);
        if ($this.parent().hasClass('ba-row') && $this.find('.modify-columns').length == 0) {
            var str = '<span class="ba-edit-wrapper"><i class="zmdi zmdi-graphic-eq modify-columns"></i>',
                lib = window.parent.gridboxLanguage ? window.parent.gridboxLanguage['MODIFY_COLUMNS'] : 'Modify Columns';
            str += '<span class="ba-tooltip tooltip-delay settings-tooltip">'+lib;
            str += '</span></span>';
            var icon = $this.find('.ba-edit-wrapper').last().before(str).prev();
        }
    });
    item.find('.ba-section-items + .ba-edit-wrapper').each(function(){
        $g(this).parent().find('> .ba-edit-item .ba-buttons-wrapper').prepend(this);
    });
}

function setTextPlaceholder($this)
{
    var content = $this.querySelectorAll('.content-text > *');
    if (content.length == 0) {
        $this.innerHTML = '<p><br></p>';
    }
}

function editItem(id)
{
    var item = $g('#'+id);
    item.find('.content-text').each(function(){
        if ('createInlineCKE' in window) {
            createInlineCKE();
        }
        setTextPlaceholder(this);
    }).on('input', function(){
        setTextPlaceholder(this);
    }).on('keydown', function(){
        if (app.blogEditor) {
            this.textInterval = setInterval(function(){
                app.blogEditor.setSelection();
                app.blogEditor.checkActive();
            }, 1);
        }
    }).on('keyup', function(){
        clearInterval(this.textInterval);
        setTextPlaceholder(this);
    });
    checkMegamenuLibrary(item);
    item.off('mouseenter').on('mouseenter', function(){
        $g(this).find('> .ba-edit-item').css({
            animation: 'edit-item-show .15s ease-in-out both',
            display: 'inline-flex'
        });
    }).off('mouseleave').on('mouseleave', function(){
        $g(this).find('> .ba-edit-item').css({
            animation: 'none',
            display: 'none'
        });
    }).find('.ba-section, .ba-row, .ba-grid-column, .ba-item').off('mouseenter').on('mouseenter', function(){
        $g(this).find('> .ba-edit-item').css({
            animation: 'edit-item-show .15s ease-in-out both',
            display: 'inline-flex'
        });
    }).off('mouseleave').on('mouseleave', function(){
        $g(this).find('> .ba-edit-item').css({
            animation: 'none',
            display: 'none'
        });
    });
    if (item.hasClass('ba-item-preloader')) {
        item.off('mouseenter mouseleave').off('mouseout').on('mouseout', function(event){
            if (event.toElement && (event.toElement.closest('.ba-edit-item') || event.toElement.closest('.preloader-point-wrapper')
                    || event.toElement.classList.contains('preloader-point-wrapper') || event.toElement.localName == 'img'
                    || event.toElement.classList.contains('preloader-image-wrapper'))) {
                $g(this).find('> .ba-edit-item').css({
                    animation: 'edit-item-show .15s ease-in-out both',
                    display: 'inline-flex'
                });
            } else {
                $g(this).find('> .ba-edit-item').css({
                    animation: 'none',
                    display: 'none'
                });
            }
        });
    }
    item.find('.ba-grid-column-wrapper').off('mouseenter').on('mouseenter', function(){
        if ($g(this).closest('.ba-grid-column').length > 0) {
            this.style.zIndex = 6;
        }
    }).off('mouseleave').on('mouseleave', function(){
        if ($g(this).closest('.ba-grid-column').length > 0) {
            this.style.zIndex = '';
        }
    });
    item.find('.ba-row-wrapper').off('mouseenter').on('mouseenter', function(){
        if ($g(this).closest('.ba-grid-column').length > 0) {
            this.style.zIndex = 20;
        }
    }).off('mouseleave').on('mouseleave', function(){
        if ($g(this).closest('.ba-grid-column').length > 0) {
            this.style.zIndex = '';
        }
    });
    item.find('.ba-column-resizer').off('mouseenter').on('mouseenter', function(){
        $g(this).find('> span').css({
            'z-index': 20
        });
    }).off('mouseleave').on('mouseleave', function(){
        $g(this).find('> span').css({
            'z-index': ''
        });
    });
    item.find('.open-overlay-item').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        var parent = $g(this).closest('.ba-edit-item').parent()[0],
            overlay = document.querySelector('.ba-overlay-section-backdrop[data-id="'+parent.dataset.overlay+'"]');
        app.edit = overlay.querySelector('.ba-section').id;
        openOverlaySection(parent);
        window.parent.app.edit = app.items[app.edit];
        window.parent.app.checkModule('lightboxEditor');
    });
    item.find('.flip-flipbox-item').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        if (this.fliped == 'started') {
            return false;
        }
        this.fliped = 'started';
        var $this = this,
            parent = $g(this).closest('.ba-item-flipbox'),
            id = parent.attr('id'),
            obj = app.items[id];
        parent.addClass('flipbox-animation-started');
        setTimeout(function(){
            $this.fliped = 'ended';
            parent.removeClass('flipbox-animation-started');
        }, obj.desktop.animation.duration * 1000);
        if (obj.side == 'frontside') {
            obj.side = 'backside';
            parent.addClass('backside-fliped');
        } else {
            obj.side = 'frontside';
            parent.removeClass('backside-fliped');
        }
    });
    item.find('.edit-item').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        event.stopPropagation();
        $g(this).closest('li.megamenu-item').addClass('megamenu-editing')
            .closest('.ba-row-wrapper').addClass('row-with-megamenu')
            .closest('.ba-wrapper').addClass('section-with-megamenu')
            .closest('body').addClass('body-megamenu-editing');
        app.edit = $g(this).closest('.ba-edit-item').parent()[0].id;
        $g('body').trigger('mousedown');
        app.checkModule('editItem');
    });
    item.find('.add-library-item').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        event.stopPropagation();
        $g(this).closest('li.megamenu-item').addClass('megamenu-editing')
            .closest('.ba-row-wrapper').addClass('row-with-megamenu')
            .closest('.ba-wrapper').addClass('section-with-megamenu')
            .closest('body').addClass('body-megamenu-editing');
        app.edit = $g(this).closest('.ba-grid-column')[0].id;
        window.parent.app.checkModule('addMegamenuLibrary');
    });
    item.find('.flipbox-add-item').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        var flipbox = $g(this).closest('.ba-item-flipbox'),
            id = flipbox.attr('id'),
            search = ' > .ba-flipbox-wrapper > .ba-flipbox-'+app.items[id].side;
        flipbox.find(search+' > .ba-grid-column-wrapper > .ba-grid-column > .empty-item span span').trigger('mousedown');
    });
    item.find('.add-item, .empty-item span span, .empty-item span i').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        app.copyAction = null;
        $g(this).closest('li.megamenu-item').addClass('megamenu-editing')
            .closest('.ba-row-wrapper').addClass('row-with-megamenu')
            .closest('.ba-wrapper').addClass('section-with-megamenu')
            .closest('body').addClass('body-megamenu-editing');
        app.edit = $g(this).closest('.ba-grid-column')[0].id;
        window.parent.app.checkModule('addPlugins');
    });
    item.find('.delete-item').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        $g(this).closest('li.megamenu-item').addClass('megamenu-editing')
            .closest('.ba-row-wrapper').addClass('row-with-megamenu')
            .closest('.ba-wrapper').addClass('section-with-megamenu')
            .closest('body').addClass('body-megamenu-editing');
        app.edit = $g(this).closest('.ba-edit-item').parent()[0].id;
        var item = $g('#'+app.edit);
        if (themeData.edit_type == 'post-layout' && themeData.app_type != 'blog') {
            window.parent.app.checkModule('deleteItem');
        } else if (item.hasClass('row-with-intro-items') || item.parent().hasClass('row-with-intro-items') ||
            item.find('.row-with-intro-items').length > 0) {
            window.parent.app.showNotice(window.parent.gridboxLanguage['DEFAULT_ITEMS_NOTICE'], 'ba-alert');
        } else {
            window.parent.app.checkModule('deleteItem');
        }
    });
    item.find('.copy-item').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        app.edit = $g(this).closest('.ba-edit-item').parent()[0].id;
        var item = $g('#'+app.edit);
        if (item.hasClass('row-with-intro-items') || item.parent().hasClass('row-with-intro-items') ||
            item.find('.row-with-intro-items').length > 0) {
            window.parent.app.showNotice(window.parent.gridboxLanguage['DEFAULT_ITEMS_NOTICE'], 'ba-alert');
        } else {
            app.checkModule('copyItem');
        }
    });
    item.find('.modify-columns').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        app.edit = $g(this).closest('.ba-edit-item').parent()[0].id;
        window.parent.document.getElementById('add-section-dialog').classList.add('add-columns');
        window.parent.document.getElementById('add-section-dialog').classList.remove('blog-editor');
        window.parent.app.checkModule('addSection');
    });
    item.find('.add-columns-in-columns').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        app.edit = $g(this).closest('.ba-grid-column')[0].id;
        window.parent.document.getElementById('add-section-dialog').classList.add('add-columns');
        window.parent.app.checkModule('addSection');
    });
    item.find('.add-nested-row').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        var parent = $g(this).closest('.ba-edit-item').parent(),
            key = parent[0].id,
            search = '> .ba-flipbox-wrapper > .ba-flipbox-'+app.items[key].side;
        app.edit = parent.find(search+' > .ba-grid-column-wrapper > .ba-grid-column')[0].id;
        window.parent.document.getElementById('add-section-dialog').classList.add('add-columns');
        window.parent.document.getElementById('add-section-dialog').classList.remove('blog-editor');
        window.parent.app.checkModule('addSection');
    });
    item.find('.content-slider-add-nested-row').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        var parent = $g(this).closest('.ba-edit-item').parent(),
            key = parent[0].id;
        app.edit = parent.find('> .slideshow-wrapper > ul > .slideshow-content > li.active > .ba-grid-column')[0].id;
        window.parent.document.getElementById('add-section-dialog').classList.add('add-columns');
        window.parent.document.getElementById('add-section-dialog').classList.remove('blog-editor');
        window.parent.app.checkModule('addSection');
    });
    item.find('.add-columns').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        $g(this).closest('li.megamenu-item').addClass('megamenu-editing')
            .closest('.ba-row-wrapper').addClass('row-with-megamenu')
            .closest('.ba-wrapper').addClass('section-with-megamenu')
            .closest('body').addClass('body-megamenu-editing');
        app.edit = $g(this).closest('.ba-section')[0].id;
        window.parent.document.getElementById('add-section-dialog').classList.add('add-columns');
        window.parent.app.checkModule('addSection');
    });
    item.find('.add-library').off('mousedown').on('mousedown', function(event){
        if (event.button && event.button != 0) {
            return false;
        }
        var parent = $g(this).closest('.ba-edit-item').parent()
        app.edit = parent[0].id;
        if (parent.hasClass('row-with-intro-items') || parent.parent().hasClass('row-with-intro-items') ||
            parent.find('.row-with-intro-items').length > 0) {
            window.parent.app.showNotice(window.parent.gridboxLanguage['DEFAULT_ITEMS_NOTICE'], 'ba-alert');
        } else {
            window.parent.app.checkModule('addLibrary');
        }
    });
}

function makeColumnSortable(parent, group)
{
    var handle = '> .ba-item:not(.ba-item-scroll-to-top):not(.ba-social-sidebar)';
    handle += ':not(.side-navigation-menu) > .ba-edit-item .edit-settings';
    handle += ', > .ba-row-wrapper > .ba-row > .ba-edit-item .edit-settings';
    parent.each(function(){
        $g(this).sortable({
            handle : handle,
            selector : '> .ba-item, > .ba-row-wrapper',
            change: function(element){
                if (element.classList.contains('ba-row-wrapper')) {
                    $g(element).find('.ba-item').each(function(){
                        if (app.items[this.id]) {
                            initMapTypes(app.items[this.id].type, this.id);
                        }
                    });
                } else if (app.items[element.id]) {
                    initMapTypes(app.items[element.id].type, element.id);
                }
                window.parent.app.addHistory();
            },
            group : group
        });
        if ($g(this).find('> .ba-row-wrapper').length > 0) {
            var str = ' > .ba-row-wrapper > .ba-row > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column';
            makeColumnSortable($g(this).find(str), group);
        }
    });
    makeResponsiveMenuSortable(parent.find('> .ba-item-main-menu, .ba-item-one-page-menu'));
}

function makeResponsiveMenuSortable(parent)
{
    var handle = '> .ba-item:not(.ba-item-scroll-to-top):not(.ba-social-sidebar)';
    handle += ':not(.side-navigation-menu) > .ba-edit-item .edit-settings';
    handle += ', > .ba-row-wrapper > .ba-row > .ba-edit-item .edit-settings';
    parent.each(function(){
        $g(this).find('> .ba-menu-wrapper > .main-menu').sortable({
            handle : handle,
            selector : '> .ba-item, .integration-wrapper',
            change: function(element){
                window.parent.app.addHistory();
            },
            group : 'responsive-menu'
        });
    });
}

function initMapTypes(type, id)
{
    let array = ['map', 'yandex-maps', 'openstreetmap', 'slideset', 'carousel', 'blog-posts', 'recent-posts',
        'recent-reviews', 'search-result', 'store-search-result', 'post-navigation', 'related-posts',
        'recent-posts-slider', 'related-posts-slider', 'recently-viewed-products', 'testimonials-slider', 'field-google-map'];
    if (array.indexOf(type) != -1) {
        setTimeout(function(){
            let obj = {
                data : app.items[id],
                selector : id
            }
            app.checkModule('initItems', obj);
        }, 300);
    }
}

function makeRowSortable(parent, group)
{
    parent.each(function(){
        $g(this).sortable({
            handle : '> .ba-row-wrapper > .ba-row > .ba-edit-item .edit-settings',
            selector : '> .ba-row-wrapper',
            change: function(element){
                $g('.prevent-default').removeClass('prevent-default');
                $g(element).find('.ba-item').each(function(){
                    if (app.items[this.id]) {
                        initMapTypes(app.items[this.id].type, this.id);
                    }
                });
                window.parent.app.addHistory();
            },
            start : function(el){
                if ($g(el).closest('.ba-item').length > 0) {
                    $g(el).closest('.ba-row').addClass('prevent-default');
                }
            },
            group : group
        });
    });
}

function setColumnResizer(item)
{
    $g(item).columnResizer({
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
            if ('setPostMasonryHeight' in window) {
                $g('.ba-blog-posts-wrapper.ba-masonry-layout').each(function(){
                    var key = $g(this).closest('.ba-item').attr('id');
                    setPostMasonryHeight(key);
                });
            }
            if ('setGalleryMasonryHeight' in window) {
                $g('.instagram-wrapper.simple-gallery-masonry-layout').each(function(){
                    setGalleryMasonryHeight(this.closest('.ba-item').id);
                });
            }
            window.parent.app.addHistory();
        }
    });
}

app.gridboxEditorLoaded();
app.modules.gridboxEditorLoaded = true;