/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.loadPlugin = function(layout, data){
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.loadPlugin&tmpl=component",
        data: {
            layout : layout,
            data : data,
            edit_type : themeData.edit_type,
            id : document.getElementById('grid_id').value
        },
        complete: function(msg){
            if (!msg.responseText) {
                return false;
            }
            var cookies = sticky = tabs = null;
            msg = JSON.parse(msg.responseText);
            for (var key in msg.items) {
                var type = msg.items[key].type
                if (app.theme.defaultPresets[type] && app.theme.presets[type] && app.theme.presets[type][app.theme.defaultPresets[type]]) {
                    msg.items[key] = $g.extend(true, msg.items[key], app.theme.presets[type][app.theme.defaultPresets[type]].data);
                }
            }
            for (var key in msg.items) {
                if (msg.items[key].type == 'cookies') {
                    cookies = key;
                } else if (msg.items[key].type == 'sticky-header') {
                    sticky = key;
                } else if (msg.items[key].type == 'tabs' || msg.items[key].type == 'accordion'
                    || msg.items[key].type == 'lightbox' || msg.items[key].type == 'overlay-section'
                    || msg.items[key].type == 'content-slider') {
                    tabs = key;
                }
                if (tabs && msg.items[tabs].type != 'content-slider' && msg.items[key].type == 'row') {
                    msg.items[key].desktop.margin = {
                        top: 0,
                        bottom: 0
                    }
                }
                app.items[key] = msg.items[key];
            }
            if (cookies) {
                key = cookies;
            } else if (sticky) {
                key = sticky;
            } else if (tabs) {
                key = tabs;
            }
            if (app.items[key].type == 'slideshow' || app.items[key].type == 'slideset' || app.items[key].type == 'carousel') {
                for (var i = 0; i < data.length; i++) {
                    app.items[key]['desktop'].slides[i + 1] = {
                        image : data[i],
                        type : 'image',
                        link : "",
                        video : null
                    }
                }
            }
            app.setNewFont = true;
            app.fonts = {};
            app.customFonts = {};
            var obj = {
                data : msg.items[key],
                selector : key
            };
            if (type == 'post-navigation') {
                var div = document.createElement('div');
                div.innerHTML = msg.html;
                var navigationTitles = $g(div).find('.ba-post-navigation-info'),
                    blogPosts = $g(div).find('.ba-blog-post');
                blogPosts.each(function(ind){
                    var navigationTitle = navigationTitles.get(ind);
                    navigationTitle.querySelector('a').href = $g(this).find('.ba-blog-post-title a').attr('href');
                    $g(this).find('.ba-blog-post-title-wrapper').before(navigationTitle);
                });
                if (blogPosts.length == 0) {
                    navigationTitles.remove();
                } else if (blogPosts.length == 1) {
                    navigationTitles.last().remove();
                }
                msg.html = div.innerHTML;
            }
            if (sticky) {
                $g('header.header').prepend(msg.html);
                makeRowSortable($g('#'+key+' .ba-section-items'), 'lightbox-row');
                makeColumnSortable($g('#'+key+' .ba-grid-column'), 'lightbox-column');
                setColumnResizer('#'+key);
                app.initStickyHeaderPanel(document.getElementById(key));
            } else if (msg.items[key].type == 'lightbox' || msg.items[key].type == 'cookies') {
                $g('#'+app.edit).closest('.ba-wrapper').parent().append(msg.html);
                makeRowSortable($g('#'+key+' .ba-section-items'), 'lightbox-row');
                makeColumnSortable($g('#'+key+' .ba-grid-column'), 'lightbox-column');
                setColumnResizer('#'+key);
                $g('#'+key).closest('.ba-lightbox-backdrop').find('.ba-lightbox-close').on('click', function(){
                    $g(this).closest('.ba-lightbox-backdrop').removeClass('visible-lightbox');
                    document.body.style.width = '';
                    $g('body').removeClass('lightbox-open ba-lightbox-open');
                });
                app.initLightboxPanel(document.getElementById(key).parentNode);
            } else if (msg.items[key].type == 'overlay-section') {
                console.info(111)
                var div = document.createElement('div');
                div.innerHTML = msg.html;
                var item = div.firstElementChild,
                    overlay = div.lastElementChild;
                if (app.items[app.edit] && (app.items[app.edit].type == 'menu' || app.items[app.edit].type == 'one-page')) {
                    $g('#'+app.edit+' > .ba-menu-wrapper > .main-menu > .add-new-item').before(item);
                } else if (app.copyAction == 'blogPostsText') {
                    $g(app.blogEditor.start).closest('.ba-item-text').after(item);
                } else {
                    $g('#'+app.edit+' > .empty-item').before(item);
                }
                $g('body').append(overlay);
                makeRowSortable($g('#'+overlay.dataset.id+' .ba-section-items'), 'lightbox-row');
                makeColumnSortable($g('#'+overlay.dataset.id+' .ba-grid-column'), 'lightbox-column');
                $g('#'+key).closest('.ba-overlay-section-backdrop').find('.ba-overlay-section-close').on('click', function(){
                    $g(this).closest('.ba-overlay-section-backdrop').removeClass('visible-section');
                });
                editItem(item.id);
                obj = {
                    data : msg.items[item.id],
                    selector : item.id
                };
            } else if (app.items[app.edit] && (app.items[app.edit].type == 'menu' || app.items[app.edit].type == 'one-page')) {
                $g('#'+app.edit+' > .ba-menu-wrapper > .main-menu > .add-new-item').before(msg.html);
            } else if (app.copyAction == 'blogPostsText') {
                $g(app.blogEditor.start).closest('.ba-item-text').after(msg.html);
            } else {
                $g('#'+app.edit+' > .empty-item').before(msg.html);
            }
            if (msg.items[key].type == 'flipbox') {
                makeColumnSortable($g('#'+key+' .ba-grid-column'), 'column');
            }
            for (var ind in msg.items) {
                if (msg.items[ind].type == 'lightbox' || msg.items[ind].type == 'overlay-section' || msg.items[ind].type == 'cookies') {
                    document.getElementById(ind).classList.add('visible');
                    window.parent.setShapeDividers(app.items[ind], ind);
                }
            }
            app.edit = null;
            app.sectionRules();
            switch (msg.items[key].type) {
                case 'slideshow':
                case 'slideset':
                case 'carousel':
                    window.parent.app.edit = app.items[key];
                    for (var i = 0; i < data.length; i++) {
                        var object = {
                                image : data[i],
                                index : (i + 1),
                                type : 'image',
                                video : null,
                                title : '',
                                description :'',
                                button : {
                                    href : '#',
                                    type : 'ba-btn-transition',
                                    title : '',
                                    target : '_blank'
                                }
                            },
                            li = window.parent.getSlideHtml(object);
                        $g('#'+key+' .slideshow-content').append(li);
                    }
                    break;
                case 'menu':
                case 'one-page':
                    makeResponsiveMenuSortable($g('#'+key));
                    break;
                case 'tabs' : 
                case 'accordion' : 
                    makeRowSortable($g('#'+key+' .ba-section-items'), 'tabs-row');
                    makeColumnSortable($g('#'+key+' .ba-grid-column'), 'column');
                    break;
                case 'content-slider':
                    makeColumnSortable($g('#'+key+' .ba-grid-column'), 'column');
                    break;
                case 'image' :
                case 'logo' :
                    var src = $g('#'+key+' img').attr('src'),
                        pos = src.indexOf('/'+IMAGE_PATH+'/');
                    src = src.substr(pos + 1);
                    app.items[key].image = src;
                    break;
                case 'countdown' :
                    var date = new Date(),
                        month = date.getMonth(),
                        year = date.getFullYear(),
                        day = date.getDate(),
                        time = date.getHours()+':'+date.getMinutes()+':';
                        sec = date.getSeconds();
                    month++;
                    if (month < 10) {
                        month = '0'+month;
                    }
                    if (day < 10 ) {
                        day = '0'+day;
                    }
                    if (sec < 10) {
                        sec = '0'+sec;
                    }
                    app.items[key].date = year+'-'+month+'-'+day+' '+time+sec;
                    break;
            }
            $g('#'+key+' .ba-edit-item').css('display', '');
            editItem(key);
            app.checkModule('initItems', obj);
            if (top.$g('#add-plugin-dialog').hasClass('in')) {
                top.$g('#add-plugin-dialog').modal('hide');
            }
            if (layout == 'bagallery') {
                initGallery();
            } else if (layout == 'tags' || layout == 'categories') {
                document.getElementById(key).dataset.app = msg.items[key].app;
            } else if (layout == 'recent-posts') {
                document.getElementById(key).dataset.app = msg.items[key].app;
                document.getElementById(key).dataset.count = msg.items[key].limit;
                document.getElementById(key).dataset.sorting = msg.items[key].sorting;
                document.getElementById(key).dataset.maximum = msg.items[key].maximum;
            } else if (layout == 'related-posts') {
                document.getElementById(key).dataset.app = msg.items[key].app;
                document.getElementById(key).dataset.count = msg.items[key].limit;
                document.getElementById(key).dataset.related = msg.items[key].related;
                document.getElementById(key).dataset.maximum = msg.items[key].maximum;
            } else if (layout == 'post-navigation') {
                document.getElementById(key).dataset.maximum = msg.items[key].maximum;
            }
            $g('a, input[type="submit"], button').on('click', function(event){
                event.preventDefault();
            });
            if (app.copyAction == 'blogPostsText') {
                app.blogEditor.copyPastText();
            } else {
                switch (msg.items[key].type) {
                    case 'text':
                    case 'lightbox':
                    case 'sticky-header':
                    case 'cookies':
                    case 'overlay-section':
                        break;
                    default:
                        $g('#'+key+' > .ba-edit-item .edit-item').trigger('mousedown');
                }
                window.top.app.setRowWithIntro();
                window.parent.app.addHistory();
            }
        }
    });
}

app.loadPlugin(app.modules.loadPlugin.data, app.modules.loadPlugin.selector)