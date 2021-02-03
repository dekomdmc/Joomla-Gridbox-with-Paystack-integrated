/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.loadModule('helper');

app.emptyCallback = function(){}

app.addNoticeText = function(message, className){
    var time = 3000;
    if (className) {
        time = 6000;
    }
    app.notification.find('p').html(message);
    app.notification.addClass(className).removeClass('animation-out').addClass('notification-in');
    setTimeout(function(){
        app.notification.removeClass('notification-in').addClass('animation-out');
        setTimeout(function(){
            app.notification.removeClass(className);
        }, 400);
    }, time);
}

app.getIntroStr = function(){
    let str = '.ba-item-category-intro, .ba-item-error-message, .ba-item-post-intro,'+
        ' .ba-item-blog-content, .ba-item-blog-posts, .ba-item-search-result-headline,'+
        ' .ba-item-search-result, .ba-item-store-search-result, .ba-item-checkout-form, .ba-item-checkout-order-form';

    return str;
}

app.setRowWithIntro = function(){
    let str = app.getIntroStr();
    app.editor.$g('.row-with-intro-items').removeClass('row-with-intro-items');
    app.editor.$g(str).closest('.ba-row').addClass('row-with-intro-items');
    app.editor.$g('.ba-item-error-message').closest('.ba-section').addClass('row-with-intro-items');
}

app.showNotice = function(message, className){
    if (!className) {
        className = '';
    }
    if (app.notification.hasClass('notification-in')) {
        setTimeout(function(){
            app.notification.removeClass('notification-in').addClass('animation-out');
            setTimeout(function(){
                app.addNoticeText(message, className);
            }, 400);
        }, 3000);
    } else {
        app.addNoticeText(message, className);
    }
}

app.getItemBlogContentStyle = function(){
    if (app.editor.themeData.edit_type == 'post-layout' && app.view == 'desktop') {
        this.post_editor_wrapper = ':root {';
        app.editor.$g('.ba-item-blog-content').each(function(){
            var comp = app.editor.getComputedStyle(this);
            app.post_editor_wrapper += '--post-wrapper-column-width: '+comp.width+';';
            $g(this).closest('.ba-grid-column').each(function(){
                var comp = app.editor.getComputedStyle(this);
                app.post_editor_wrapper += '--post-wrapper-column-color: '+comp.background+';';
            }).closest('.ba-row').each(function(){
                var comp = app.editor.getComputedStyle(this);
                app.post_editor_wrapper += '--post-wrapper-row-color: '+comp.background+';';
            }).closest('.ba-section').each(function(){
                var comp = app.editor.getComputedStyle(this);
                app.post_editor_wrapper += '--post-wrapper-section-color: '+comp.background+';';
            });
        });
        this.post_editor_wrapper += '}';
    }
};

app.editorLoaded = function(){
    $g('span.shortcuts-gridbox').on('mousedown', function(){
        setTimeout(function(){
            $g('#shortcuts-modal').modal();
        }, 50);
    });
    $g(window).on('keydown', function(event){
        var flag = false;
        if (flag = event.keyCode == 83 && (event.ctrlKey || event.metaKey) && !event.altKey) {
            $g('.gridbox-save').trigger('click');
        } else if (flag = event.keyCode == 90 && (event.ctrlKey || event.metaKey) && event.shiftKey) {
            $g('.ba-action-redo').trigger('mousedown');
        } else if (flag = event.keyCode == 90 && (event.ctrlKey || event.metaKey)) {
            $g('.ba-action-undo').trigger('mousedown');
        } else if (flag = event.keyCode == 83 && event.altKey) {
            $g('span.ba-page-settings').trigger('mousedown');
        } else if (flag = event.keyCode == 84 && event.altKey) {
            $g('div.ba-theme-editor').trigger('mousedown');
        } else if (flag = event.keyCode == 87 && event.altKey) {
            $g('.ba-site-settings').trigger('mousedown');
        } else if (flag = event.keyCode == 69 && event.altKey) {
            $g('.ba-code-editor').trigger('mousedown');
        } else if (flag = event.keyCode == 77 && event.altKey) {
            $g('.show-media-manager').trigger('mousedown');
        } else if (flag = event.keyCode == 70 && event.altKey) {
            $g('.show-font-library').trigger('mousedown');
        } else if (flag = event.keyCode == 88 && event.altKey) {
            $g('.modal-backdrop').last().trigger('click');
        } else if (flag = event.originalEvent.code == 'Tab' && event.shiftKey) {
            var btn = $g('.responsive-context-menu span[data-view="'+app.view+'"]').next();
            if (btn.length == 0) {
                btn = $g('.responsive-context-menu span[data-view="desktop"]');
            }
            btn.trigger('mousedown');
        }
        if (flag) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
    document.body.classList.add('desktop');
    $g('#add-plugin-dialog').on('hide', function(){
        app.editor.document.body.classList.remove('disable-tooltips');
    });
    app.notification = $g('#ba-notification');
    app.notification.find('.zmdi.zmdi-close').on('click', function(){
        app.notification.removeClass('notification-in').addClass('animation-out');
    });
    app.checkModule('shapeDividers');
    $g('[data-context]').on('mousedown', function(event){
        app.context = this;
        app.checkModule('showContext');
    });
    $g('.ba-action-undo, .ba-action-redo').on('mousedown', function(){
        app.checkModule(this.dataset.module);
    });
    $g('span.ba-page-settings').on('mousedown', function(event){
        setTimeout(function(){
            app.checkModule('openPageSettings');
        }, 10);
    });
    $g('.ba-code-editor').on('mousedown', function(event){
        app.checkModule('codemirror');
    });
    $g('.show-media-manager').on('mousedown', function(){
        setTimeout(function(){
            uploadMode = '';
            checkIframe($g('#uploader-modal').attr('data-check', 'multiple'), 'uploader', function(){
                var iframe = document.querySelector('#uploader-modal iframe').contentWindow;
                iframe.document.body.classList.add('media-manager-enabled');
            });
        }, 200);
        $g('body').trigger('mousedown');
        return false;
    });
    $g('#uploader-modal').on('hide', function(){
        $g(this).removeClass('photo-media-editor');
        var iframe = this.querySelector('iframe').contentWindow;
        iframe.document.body.classList.remove('media-manager-enabled');
        iframe.document.body.classList.remove('photo-editor-enabled');
        iframe.jQuery('#check-all').prop('checked', false);
        iframe.jQuery('.select-item').prop('checked', false);
        iframe.jQuery('.active').removeClass('active');
        iframe.jQuery('.ba-context-menu').hide();
        iframe.jQuery('.ba-context-menu').hide();
        iframe.jQuery('.modal.in').modal('hide');
        iframe.jQuery('.context-active').removeClass('context-active');
    });
    $g('.open-photo-editor').on('mousedown', function(){
        setTimeout(function(){
            var modal = $g('#photo-editor-dialog');
            modal.find('.active').removeClass('active');
            modal.find('a[href="#resize-image-options"]').parent().addClass('active');
            modal.find('input').val('');
            modal.find('input[type="range"]').val(0);
            modal.find('.ba-range-liner').width(0);

            $g('#resize-image-options').addClass('active');
            modal.addClass('disabled-photo-editor').modal();
        }, 200);
        $g('body').trigger('mousedown');
        return false;
    });
    $g('.show-photo-media-editor').on('mousedown', function(event){
        event.stopPropagation();
        event.preventDefault();
        uploadMode = '';
        checkIframe($g('#uploader-modal').attr('data-check', 'single').addClass('photo-media-editor'), 'uploader', function(){
            var iframe = document.querySelector('#uploader-modal iframe').contentWindow;
            iframe.document.body.classList.add('photo-editor-enabled');
        });
    })
    $g('.show-font-library').on('mousedown', function(){
        setTimeout(function(){
            uploadMode = '';
            checkIframe($g('#fonts-editor-dialog'), 'fonts');
        }, 200);
        $g('body').trigger('mousedown');
        return false;
    });
    $g('.responsive-context-menu > span').on('mousedown', function(){
        var $this = $g(this),
            className = $this.find('i')[0].className,
            text = $this.find('span').text().trim(),
            button = $g('div[data-context="responsive-context-menu"]');
        app.getItemBlogContentStyle();
        button.find('i').first()[0].className = className;
        button.find('span').text(text);
        $g('body').removeClass(app.view).addClass(this.dataset.view);
        app.editor.$g('body').removeClass(app.view).addClass(this.dataset.view);
        app.view = this.dataset.view;
        $g('body').trigger('mousedown');
        $g('.editor-iframe').css('width', this.dataset.width);
        return false;
    });
    $g('a, input[type="submit"], button').not('.default-action').on('click', function(event){
        event.preventDefault();
    });
    $g('.default-action').on('mousedown', function(event){
        event.stopPropagation();
    });
    $g('input, textarea').on('keydown', function(event){
        event.stopPropagation();
    });
    $g('div.ba-theme-editor').on('mousedown', function(event){
        $g('.draggable-modal-cp.in').modal('hide');
        app.checkModule('themeEditor');
    });
    $g('.ba-site-settings').on('mousedown', function(event){
        app.checkModule('siteOptions');
    });
    $g('.select-favicon').on('mousedown', function(){
        uploadMode = 'favicon';
        var modal = $g('#uploader-modal').attr('data-check', 'single');
        checkIframe(modal, 'uploader');
    });
    $g('.ba-modal-cp').not('#add-section-dialog').on('hide', function(){
        setTimeout(function(){
            app.editor.$g('.megamenu-editing').removeClass('megamenu-editing')
                .closest('.ba-row-wrapper').removeClass('row-with-megamenu')
                .closest('.ba-wrapper').removeClass('section-with-megamenu')
                .closest('body').removeClass('body-megamenu-editing');
        }, 100);
    }).on('show', function(){
        if (app.selector && app.editor.document.querySelector(app.selector).closest('li.megamenu-item')) {
            app.editor.$g(app.selector).closest('li.megamenu-item').addClass('megamenu-editing')
                .closest('.ba-row-wrapper').addClass('row-with-megamenu')
                .closest('.ba-wrapper').addClass('section-with-megamenu')
                .closest('body').addClass('body-megamenu-editing');
        }
    });
    $g('#add-section-dialog, #delete-dialog, #add-plugin-dialog, #megamenu-library-dialog').on('hide', function(){
        setTimeout(function(){
            if ($g('.ba-modal-cp.in').length == 0) {
                app.editor.$g('.megamenu-editing').removeClass('megamenu-editing')
                    .closest('.ba-row-wrapper').removeClass('row-with-megamenu')
                    .closest('.ba-wrapper').removeClass('section-with-megamenu')
                    .closest('body').removeClass('body-megamenu-editing');
            }
        }, 1000);
    });
    $g('body .modal').on('hide', function(){
        app.itemDelete = null;
        $g(this).addClass('ba-modal-close');
        if (this.id != 'photo-editor-dialog' && this.id != 'save-copy-dialog' && this.id != 'save-copy-notice-dialog') {
        	uploadMode = '';
        }
        setTimeout(function(){
            $g('.ba-modal-close').removeClass('ba-modal-close');
        }, 300);
    }).on('mousedown', function(event){
        $g(document).trigger(event);
        event.stopPropagation();
    }).on('shown', function(event){
        $g('.modal-backdrop').on('mousedown', function(event){
            if (!this.classList.contains('ba-modal-picker')) {
                $g('.modal-backdrop.ba-modal-picker').trigger('click');
            }
            event.stopPropagation();
        }).last().addClass(this.id).addClass(this.classList.contains('ba-modal-picker') ? 'ba-modal-picker' : '');
    });
    var modalsWithPickers = '.draggable-modal-cp, #edit-content-slider-item-modal, #yandex-maps-item-dialog'+
        ', #yandex-maps-editor-dialog, #map-editor-dialog, #yandex-maps-editor-dialog, #openstreetmap-editor-dialog';
    $g(modalsWithPickers).find(' > *').on('mousedown', function(event){
        if ($g('.ba-modal-picker.in').hasClass('in') && event.target != fontBtn) {
            $g('.ba-modal-picker.in').modal('hide');
        } else if ($g('.ba-context-menu[style*="display: block;"]').length > 0) {
            $g('.ba-context-menu[style*="display: block;"]').hide();
        }
    });
    app.notification.on('mousedown', function(event){
        event.stopPropagation();
    });
    window.addEventListener("message", function(event){
        if (event.origin == location.origin) {
        	if (!uploadMode) {
                $g('body').trigger('mousedown');
            } else {
                app.messageData = event.data;
                app.checkModule('messageListener');
            }
        }
    });
    $g('.ba-custom-select > i, div.ba-custom-select input').on('click', function(event){
        event.stopPropagation();
        var $this = $g(this),
            parent = $this.parent();
        $g('.visible-select').removeClass('visible-select');
        parent.find('ul').addClass('visible-select');
        parent.find('li').off('click').one('click', function(){
            var text = $g.trim($g(this).text()),
                val = $g(this).attr('data-value');
            parent.find('input[type="text"]').val(text);
            parent.find('input[type="hidden"]').val(val).trigger('change');
            parent.trigger('customAction');
        });
        parent.trigger('show');
        setTimeout(function(){
            $g('body').off('click.customHide').one('click.customHide', function(){
                $g('.visible-select').parent().trigger('customHide');
                $g('.visible-select').removeClass('visible-select');
            });
        }, 50);
    });
    $g('div.ba-custom-select').on('show', function(){
        var $this = $g(this),
            ul = $this.find('ul'),
            value = $this.find('input[type="hidden"]').val();
        ul.find('i').remove();
        ul.find('.selected').removeClass('selected');
        ul.find('li[data-value="'+value+'"]').addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
    });
    $g('.ba-lg-custom-select').each(function(){
        var parent = $g(this);
        parent.find('i.zmdi-caret-down, > input[type="text"]').on('click', function(event){
            event.stopPropagation();
            parent.find('.ba-lg-custom-select-header > span:not(:first-child)').addClass('disable-button');
            parent.find('.ba-lg-custom-select-body label input[type="radio"]').prop('checked', false);
            $g('.visible-select').removeClass('visible-select');
            parent.find('ul').addClass('visible-select');
            parent.trigger('show');
            setTimeout(function(){
                $g('body').off('click.customHide').one('click.customHide', function(){
                    $g('.visible-select').parent().trigger('customHide');
                    $g('.visible-select').removeClass('visible-select');
                });
            }, 50);
        });
        parent.find('.ba-lg-custom-select-body').on('click', 'li span', function(){
            var text = this.textContent.trim(),
                value = this.parentNode.dataset.value;
            parent.find('input[type="text"]').val(text);
            parent.find('input[type="hidden"]').val(value).trigger('change');
            parent.trigger('customAction');
            parent.trigger('click');
        });
        parent.find('.ba-lg-custom-select-body').on('click', 'label input', function(){
            if (this.checked && this.value) {
                parent.find('.ba-lg-custom-select-header > span:not(:first-child)')
                    .removeClass('disable-button').attr('data-value', this.value);
            } else {
                parent.find('.ba-lg-custom-select-header > span:not(:first-child)').addClass('disable-button');
                if (!this.value) {
                    this.checked = false;
                }
            }
        });
        parent.find('ul').on('click', function(event){
            event.stopPropagation();
        });
    });
    $g('.show-hidden-elements').on('click', function(){
        this.style.display = "none";
        $g('.hide-hidden-elements')[0].style.display = "";
        app.editor.document.body.classList.add('show-hidden-elements');
        var obj = {
            callback : 'sectionRules',
        }
        app.editor.app.listenMessage(obj);
    });
    $g('.hide-hidden-elements').on('click', function(){
        this.style.display = "none";
        $g('.show-hidden-elements')[0].style.display = "";
        app.editor.document.body.classList.remove('show-hidden-elements');
        var obj = {
            callback : 'sectionRules',
        }
        app.editor.app.listenMessage(obj);
    });
    $g('body').on('mousedown', function(event){
        app.closeOpenedModal(event);
    });
    app.closeOpenedModal = function(event){
        if (!event.target || !(event.target &&
            ((event.target.className && typeof(event.target.className) == 'string' &&
                event.target.className.match(/^mce|^cke_|CodeMirror-hint/)) ||
                $g(event.target).closest('[class^="mce"], [class^="cke_"]').length > 0))) {
            $g('.modal-backdrop').last().trigger('click');
        }
        top.$g('.ba-context-menu').hide();
        if (app.editor) {
            app.editor.$g('.active-context-item, .active-context-item-editing')
                .removeClass('active-context-item active-context-item-editing');
            app.editor.document.getElementById('library-backdrop').classList.remove('visible-backdrop');
            if (event.target && event.target.className && typeof(event.target.className) == 'string'
                && event.target.className.match(/cke_/)
                && event.target.closest('body') == app.editor.document.body) {
                $g('.modal-backdrop').last().trigger('click');
            }
        }
        $g('.push-left-body').removeClass('push-left-body');
        $g('.ba-sidebar-panel').removeClass('ba-sidebar-panel');
    }

    var script = document.createElement('script');
    script.src = 'https://www.balbooa.com/updates/gridbox/gridboxApi/site/gridboxApi.js';
    script.onload = function(){
        var interval = setInterval(function(){
            if (typeof(gridboxLanguage) != 'undefined') {
                clearInterval(interval);
                for (var key in gridboxApi.plugins) {
                    var pluginGroup = $g('.ba-plugin-group[data-type="'+key+'"]');
                    for (var ind in gridboxApi.plugins[key]) {
                        if (pluginGroup.length > 0) {
                            if (pluginGroup.find('.ba-plugin[data-plugin="'+ind+'"]').length == 0) {
                                var str = '<div class="ba-plugin disable-plugin" data-plugin="'+ind+'">';
                                str += '<i class="'+gridboxApi.plugins[key][ind].image+'"></i><span class'
                                str += '="ba-title">'+gridboxLanguage[gridboxApi.plugins[key][ind].joomla_constant]+'</span></div>';
                                pluginGroup.append(str);
                            } else {
                                delete(gridboxApi.plugins[key][ind]);
                            }
                        }
                    }
                }
                gridboxApi.blocks = gridboxApi.pblocks;
                for (var key in gridboxApi.pblocks) {
                    var blockGroup = $g('#'+key+'-page-blocks');
                    for (var ind in gridboxApi.pblocks[key]) {
                        if (blockGroup.length > 0) {
                            if (blockGroup.find('.ba-page-block-item[data-id="'+ind+'"]').length == 0) {
                                var str = '<span class="ba-page-block-item disabled" data-id="'+ind+'"><img></span>';
                                blockGroup.append(str);
                                var bscript = document.createElement('script');
                                bscript.src = 'https://www.balbooa.com/updates/gridbox/gridboxApi/site/'+ind+'.js';
                                document.head.appendChild(bscript);
                            } else {
                                delete(gridboxApi.pblocks[key][ind]);
                            }
                        }
                    }
                }
            }
        }, 100);
    }
    document.head.appendChild(script);
};

function checkIframe(modal, view, callback)
{
    var iframe = modal.find('iframe');
    if (iframe.attr('src').indexOf('view='+view) == -1) {
        iframe[0].src = 'index.php?option=com_gridbox&view='+view+'&tmpl=component';
        iframe[0].onload = function(){
            modal.modal();
            if (typeof(callback) != 'undefined') {
                callback();
            }
            this.onload = null;
        }
    } else {
        modal.modal();
        if (typeof(callback) != 'undefined') {
            callback();
        }
    }
}

app.modules.editorLoaded = true;
app.editorLoaded();