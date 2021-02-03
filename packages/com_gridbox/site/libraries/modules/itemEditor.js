/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.itemEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#item-settings-dialog .active').removeClass('active');
    $g('#item-settings-dialog a[href="#item-general-options"]').parent().addClass('active');
    $g('#item-general-options').addClass('active');
    $g('#item-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    var value = $g('#item-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#item-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#item-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#item-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#item-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    if (app.edit.type == 'logo') {
        $g('#item-settings-dialog .logo-options').css('display', '');
    }
    if (app.edit.type == 'logo' || app.edit.type == 'simple-gallery' || app.edit.type == 'field-simple-gallery'
        || app.edit.type == 'product-gallery') {
        $g('a[href="#item-design-options"]').parent().css('display', '');
    } else {
        $g('a[href="#item-design-options"]').parent().hide();
    }
    if (app.edit.type == 'modules') {
        $g('#item-settings-dialog .modules-options').css('display', '');
        $g('#item-settings-dialog .reselect-module').val('module ID='+app.edit.integration);
    } else if (app.edit.type == 'forms') {
        $g('#item-settings-dialog .modules-options').css('display', '');
        $g('#item-settings-dialog .reselect-module').val('forms ID='+app.edit.integration);
    } else if (app.edit.type == 'gallery') {
        $g('#item-settings-dialog .modules-options').css('display', '');
        $g('#item-settings-dialog .reselect-module').val('gallery ID='+app.edit.integration);
    } else {
        $g('#item-settings-dialog .modules-options').hide();
    }
    if (app.edit.type == 'simple-gallery') {
        if (!app.edit.desktop.overlay) {
            app.edit.desktop.overlay = {
                type: 'color',
                color: '@overlay',
                gradient: {
                    "effect": "linear",
                    "angle": 45,
                    "color1": "@bg-dark",
                    "position1": 25,
                    "color2": "@bg-dark-accent",
                    "position2": 75
                }
            }
            app.edit.desktop.title = {
                "typography" : {
                    "color" : "@title-inverse",
                    "font-family" : "@default",
                    "font-size" : 32,
                    "font-style" : "normal",
                    "font-weight" : "900",
                    "letter-spacing" : 0,
                    "line-height" : 42,
                    "text-decoration" : "none",
                    "text-align" : "center",
                    "text-transform" : "none"
                },
                "margin" : {
                    "bottom" : "0",
                    "top" : "0"
                }
            };
            app.edit.desktop.description = {
                "typography" : {
                    "color" : "@title-inverse",
                    "font-family" : "@default",
                    "font-size" : 21,
                    "font-style" : "normal",
                    "font-weight" : "300",
                    "letter-spacing" : 0,
                    "line-height" : 36,
                    "text-decoration" : "none",
                    "text-align" : "center",
                    "text-transform" : "none"
                },
                "margin" : {
                    "bottom" : "0",
                    "top" : "0"
                }
            };
            app.edit.desktop.animation = {
                "effect": "ba-fade",
                "duration": 0.3
            }
            app.sectionRules();
        }
        if (!app.edit.tag) {
            app.edit.tag = 'h3';
        }
        value = app.getValue('overlay', 'effect', 'gradient');
        $g('#item-settings-dialog .overlay-linear-gradient').hide();
        $g('#item-settings-dialog .overlay-'+value+'-gradient').css('display', '');
        $g('#item-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="hidden"]').val(value);
        value = $g('#item-settings-dialog .overlay-gradient-options .gradient-effect-select li[data-value="'+value+'"]').text().trim();
        $g('#item-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="text"]').val(value);
        value = app.getValue('overlay', 'type');
        $g('#item-settings-dialog .overlay-color-options, .overlay-gradient-options').hide();
        $g('#item-settings-dialog .overlay-'+value+'-options').css('display', '');
        $g('#item-settings-dialog .background-overlay-select input[type="hidden"]').val(value);
        value = $g('#item-settings-dialog .background-overlay-select li[data-value="'+value+'"]').text().trim();
        $g('#item-settings-dialog .background-overlay-select input[type="text"]').val(value);
        $g('#item-settings-dialog .slideshow-style-custom-select input[type="hidden"]').val('title');
        $g('#item-settings-dialog .slideshow-style-custom-select input[readonly]').val(gridboxLanguage['TITLE']);
        $g('#item-settings-dialog .select-title-html-tag input[type="hidden"]').val(app.edit.tag);
        $g('#item-settings-dialog .select-title-html-tag input[readonly]').val(app.edit.tag.toUpperCase());
        showSlideshowDesign('title', $g('#item-settings-dialog .slideshow-style-custom-select'));
        var images = app.editor.document.querySelectorAll('#'+app.editor.app.edit+' .ba-instagram-image');
        sortingList = [];
        $g('#item-settings-dialog .sorting-container').html('');
        for (var i = 0; i < images.length; i++) {
            var obj = {
                src: images[i].querySelector('img').dataset.src,
                alt: images[i].querySelector('img').alt,
                title: '',
                description: ''
            }
            if (images[i].querySelector('.ba-simple-gallery-title')) {
                obj.title = images[i].querySelector('.ba-simple-gallery-title').textContent.trim();
                obj.description = images[i].querySelector('.ba-simple-gallery-description').innerHTML.trim();
            }
            sortingList.push(obj);
            $g('#item-settings-dialog .sorting-container').append(addSimpleSortingList(sortingList[i], i));
        }
    }
    if (app.edit.type == 'logo') {
        $g('#item-settings-dialog [data-option="image"]').val(app.edit.image);
        $g('#item-settings-dialog [data-option="alt"]').val(app.edit.alt);
        $g('#item-settings-dialog [data-option="align"].active').removeClass('active');
        $g('#item-settings-dialog [data-option="align"][data-value="'+app.edit.align+'"]').addClass('active');
        value = app.getValue('width');
        value = $g('#item-settings-dialog .image-width input[data-option="width"]').val(value).prev().val(value);
        setLinearWidth(value);
        $g('#item-settings-dialog [data-option="text-align"].active').removeClass('active');
        value = app.getValue('text-align');
        $g('#item-settings-dialog [data-option="text-align"][data-value="'+value+'"]').addClass('active');
        $g('#item-settings-dialog [data-option="link"]').val(app.edit.link.link);
    } else if (app.edit.type == 'disqus') {
        $g('#item-settings-dialog .disqus-subdomen').val(app.edit.subdomen);
    } else if (app.edit.type == 'simple-gallery' || app.edit.type == 'field-simple-gallery' || app.edit.type == 'product-gallery') {
        setPresetsList($g('#item-settings-dialog'));
        if (!app.edit.desktop.border) {
            app.edit.desktop.border = {
                "color" : "@border",
                "radius" : "0",
                "style" : "solid",
                "width" : "0"
            }
        }
        let options = app.edit.type == 'product-gallery' ? 'field-simple-gallery' : app.edit.type;
        $g('.'+options+'-options [data-option]:not([data-subgroup="typography"])').each(function(){
            if (app.edit[this.dataset.group]) {
                value = app.edit[this.dataset.group][this.dataset.option];
            } else if (this.dataset.subgroup) {
                value = app.getValue(this.dataset.group, this.dataset.option, this.dataset.subgroup);
            } else if (this.dataset.group) {
                value = app.getValue(this.dataset.group, this.dataset.option);
            } else {
                value = app.getValue(this.dataset.option);
            }
            if (this.dataset.group == '') {

            } else  if (this.type == 'checkbox') {
                this.checked = value;
            } else if (this.dataset.type == 'color') {
                updateInput($g(this), value);
            } else if (this.type == 'hidden') {
                this.value = value;
                value = this.parentNode.querySelector('li[data-value="'+value+'"]').textContent.trim();
                this.previousElementSibling.value = value;
            } else {
                this.value = value;
                if (this.type == 'number') {
                    var range = $g(this).prev();
                    range.val(value);
                    setLinearWidth(range);
                }
            }
        });
    }
    if (app.edit.type == 'field-simple-gallery' || app.edit.type == 'product-gallery' || app.edit.type == 'simple-gallery') {
        if (!('layout' in app.edit)) {
            app.edit.layout = '';
        }
        if (!app.edit.desktop.border) {
            app.edit.desktop.border = {
                "color" : "@border",
                "radius" : "0",
                "style" : "solid",
                "width" : "0"
            }
        }
        value = app.getValue('border', 'radius');
        value = $g('#item-settings-dialog input[data-option="radius"][data-group="border"]').val(value).prev().val(value);
        setLinearWidth(value);
        value = app.getValue('border', 'width');
        value = $g('#item-settings-dialog input[data-option="width"][data-group="border"]').val(value).prev().val(value);
        setLinearWidth(value);
        value = app.getValue('border', 'color');
        updateInput($g('#item-settings-dialog input[data-option="color"][data-group="border"]'), value);
        value = app.getValue('border', 'style');
        $g('#item-settings-dialog .border-style-select input[type="hidden"]').val(value);
        value = $g('#item-settings-dialog .border-style-select li[data-value="'+value+'"]').text();
        $g('#item-settings-dialog .border-style-select input[readonly]').val($g.trim(value));
        $g('#item-settings-dialog .simple-gallery-layout-select input[type="hidden"]').val(app.edit.layout);
        value = $g('#item-settings-dialog .simple-gallery-layout-select li[data-value="'+app.edit.layout+'"]').text().trim();
        $g('#item-settings-dialog .simple-gallery-layout-select input[type="text"]').val(value);
        if (app.edit.layout) {
            $g('#item-settings-dialog .simple-gallery-layout-select').closest('.ba-settings-item').nextAll().hide();
        } else {
            $g('#item-settings-dialog .simple-gallery-layout-select').closest('.ba-settings-item').nextAll().css('display', '');
        }
    }
    if (app.edit.type == 'field-simple-gallery' || app.edit.type == 'product-gallery') {
        $g('#item-settings-dialog input[data-option="label"]').val(app.edit.label);
        $g('#item-settings-dialog input[data-option="description"][data-group="options"]').val(app.edit.options.description);
        $g('#item-settings-dialog input[data-option="required"]').prop('checked', app.edit.required);
        $g('#item-settings-dialog .select-field-upload-source input[type="hidden"]').val(app.edit.options.source);
        value = app.edit.options.source == 'desktop' ? gridboxLanguage['DESKTOP'] : gridboxLanguage['MEDIA_MANAGER'];
        $g('#item-settings-dialog .select-field-upload-source input[type="text"]').val(value);
        $g('#item-settings-dialog .desktop-source-filesize input').val(app.edit.options.size);
        if (app.edit.options.source == 'desktop') {
            $g('#item-settings-dialog .desktop-source-filesize').css('display', '');
        } else {
            $g('#item-settings-dialog .desktop-source-filesize').hide();
        }
    }
    setDisableState('#item-settings-dialog');
    $g('#item-settings-dialog').attr('data-edit', app.edit.type);
    if (app.edit.type == 'product-gallery') {
        $g('#item-settings-dialog').attr('data-edit', 'field-simple-gallery');
    }
    if (app.edit.type == 'vk-comments') {
        var attach = app.edit.options.attach.split(',');
        $g('.vk-comments-attach').each(function(){
            if (attach[0] == '*' || attach.indexOf(this.dataset.option)) {
                this.checked = true;
            } else {
                this.checked = false;
            }
        });
        $g('.vk-comments-autopublish').prop('checked', Boolean(app.edit.options.autoPublish));
        $g('.vk-comments-app-id').val(app.edit.app_id);
        var range = $g('.vk-comments-limit').val(app.edit.options.limit).prev().val(app.edit.options.limit);
        setLinearWidth(range);
        $g('.vk-comments-options').css('display', '');
    } else {
        $g('.vk-comments-options').hide();
    }
    if (app.edit.type == 'facebook-comments') {
        $g('.facebook-comments-app-id').val(app.edit.app_id);
        var range = $g('.facebook-comments-limit').val(app.edit.options.limit).prev().val(app.edit.options.limit);
        setLinearWidth(range);
        $g('.facebook-comments-options').css('display', '');
    } else {
        $g('.facebook-comments-options').hide();
    }
    if (app.edit.type == 'hypercomments') {
        $g('.hypercomments-widget-id').val(app.edit.app_id);
        $g('.hypercomments-options').css('display', '');
    } else {
        $g('.hypercomments-options').hide();
    }
    setTimeout(function(){
        $g('#item-settings-dialog').modal();
    }, 150);
}

function reloadModules(obj)
{
    app.edit.integration = obj.selector;
    if (app.edit.type == 'modules') {
        $g('#item-settings-dialog .reselect-module').val('module ID='+app.edit.integration);
    } else {
        $g('#item-settings-dialog .reselect-module').val(app.edit.type+' ID='+app.edit.integration);
    }
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.reloadModules",
        data: {
            type : app.edit.type,
            id : obj.selector
        },
        complete: function(msg){
            app.editor.$g(app.selector).find('.integration-wrapper').html(msg.responseText);
            if ('initGalleries' in app.editor) {
                app.editor.initGalleries();
            }
        }
    });
}

$g('#item-settings-dialog .reselect-module').on('click', function(){
    if (app.edit.type == 'modules') {
        checkIframe($g('#modules-list-modal'), 'modules');
    } else {
        checkIframe($g('#'+app.edit.type+'-list-modal'), 'ba'+app.edit.type);
    }
});

$g('#item-settings-dialog .disqus-subdomen').on('input', function(){
    clearTimeout(delay);
    var $this = this;
    delay = setTimeout(function(){
        app.edit.subdomen = $this.value;
        app.editor.app.initdisqus(app.edit);
    }, 300);
});

$g('.vk-comments-attach').on('change', function(){
    var attach = new Array(),
        str = '';
    $g('.vk-comments-attach').each(function(){
        if (this.checked) {
            attach.push(this.dataset.option);
        }
    });
    str = attach.join(',');
    if (str == 'graffiti,photo,audio,video,link') {
        str = '*';
    }
    app.edit.options.attach = str;
    app.editor.app.initvkcomments(app.edit);
    app.addHistory();
});

$g('.vk-comments-autopublish').on('change', function(){
    app.edit.options.autoPublish = Number(this.checked);
    app.editor.app.initvkcomments(app.edit);
    app.addHistory();
});

$g('.vk-comments-app-id').on('input', function(){
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.app_id = $g('.vk-comments-app-id').val().trim();
        app.editor.app.initvkcomments(app.edit);
        app.addHistory();
    }, 300);
});

$g('.vk-comments-limit').on('input', function(){
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.options.limit = Number($g('.vk-comments-limit').val().trim());
        app.editor.app.initvkcomments(app.edit);
        app.addHistory();
    }, 300);
});

$g('.facebook-comments-app-id').on('input', function(){
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.app_id = $g('.facebook-comments-app-id').val().trim();
        app.editor.app.initfacebookcomments(app.edit);
        app.addHistory();
    }, 300);
});

$g('.facebook-comments-limit').on('input', function(){
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.options.limit = Number($g('.facebook-comments-limit').val().trim());
        app.editor.app.initfacebookcomments(app.edit);
        app.addHistory();
    }, 300);
});

$g('.hypercomments-widget-id').on('input', function(){
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.app_id = $g('.hypercomments-widget-id').val().trim();
        app.editor.app.inithypercomments(app.edit);
        app.addHistory();
    }, 300);
});

function addSimpleSortingList(image, key)
{
    var str = '<div class="sorting-item" data-key="'+key,
        src = image.src,
        array = src.split('/');
    if (src.indexOf('balbooa.com') == -1) {
        src = JUri+src;
    }
    str += '"><div class="sorting-handle"><i class="zmdi zmdi-apps"></i></div>';
    str += '<div class="sorting-image">';
    str += '<img src="'+src+'">';
    str += '</div><div class="sorting-title">';
    str += array[array.length - 1];
    str += '</div><div class="sorting-icons">';
    str += '<span><i class="zmdi zmdi-edit"></i></span>';
    str += '<span><i class="zmdi zmdi-copy"></i></span>';
    str += '<span><i class="zmdi zmdi-delete"></i></span></div></div>';

    return str;
}

$g('#item-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    app.checkModule('deleteItem');
});

$g('#item-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-edit', function(){
    var ind = $g(this).closest('.sorting-item').attr('data-key') * 1;
    $g('#apply-simple-gallery-item').attr('data-index', ind);
    $g('.simple-gallery-upload-image').val(sortingList[ind].src);
    $g('.simple-gallery-alt').val(sortingList[ind].alt);
    $g('.simple-gallery-title').val(sortingList[ind].title);
    $g('.simple-gallery-description').val(sortingList[ind].description);
    $g('#simple-gallery-item-edit-modal').modal();
});

$g('#item-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-copy', function(){
    var ind = $g(this).closest('.sorting-item').attr('data-key') * 1,
        image = app.editor.$g(app.selector+' .ba-instagram-image').get(ind),
        clone = image.cloneNode(true);
    $g(image).after(clone);
    app.editor.app.buttonsPrevent();
    var images = app.editor.document.querySelectorAll(app.selector+' .ba-instagram-image');
    sortingList = [];
    $g('#item-settings-dialog .sorting-container').html('');
    for (var i = 0; i < images.length; i++) {
        var obj = {
            src: images[i].querySelector('img').dataset.src,
            alt: images[i].querySelector('img').alt,
            title: '',
            description: ''
        }
        if (images[i].querySelector('.ba-simple-gallery-title')) {
            obj.title = images[i].querySelector('.ba-simple-gallery-title').textContent.trim();
            obj.description = images[i].querySelector('.ba-simple-gallery-description').innerHTML.trim();
        }
        sortingList.push(obj);
        $g('#item-settings-dialog .sorting-container').append(addSimpleSortingList(sortingList[i], i));
    }
    app.addHistory();
});

$g('.simple-gallery-upload-image').on('click', function(){
    uploadMode = 'reselectSimpleImage';
    fontBtn = this;
    checkIframe($g('#uploader-modal').attr('data-check', 'single'), 'uploader');
});

$g('.simple-gallery-caption-effect-select').on('customAction', function(){
    app.editor.$g('#'+app.editor.app.edit+' .instagram-wrapper').removeClass(app.edit.desktop.animation.effect);
    app.edit.desktop.animation.effect = this.querySelector('input[type="hidden"]').value;
    app.editor.$g('#'+app.editor.app.edit+' .instagram-wrapper').addClass(app.edit.desktop.animation.effect);
    app.addHistory();
});

$g('#apply-simple-gallery-item').on('click', function(event){
    event.preventDefault();
    var children = app.editor.$g('#'+app.editor.app.edit+' .instagram-wrapper').addClass(app.edit.desktop.animation.effect)[0].children,
        ind = this.dataset.index * 1,
        image = children[ind].querySelector('img'),
        title = children[ind].querySelector('.ba-simple-gallery-title'),
        description = children[ind].querySelector('.ba-simple-gallery-description'),
        obj = {
            src: $g('.simple-gallery-upload-image').val(),
            alt: $g('.simple-gallery-alt').val().trim(),
            title: $g('.simple-gallery-title').val().trim(),
            description: $g('.simple-gallery-description').val().trim()
        }
    sortingList.splice(ind, 1, obj);
    children[ind].style.backgroundImage = 'url('+JUri+obj.src+')';
    image.src = JUri+obj.src;
    image.dataset.src = obj.src;
    image.alt = obj.alt;
    if (!title) {
        var caption = '<div class="ba-simple-gallery-image"></div><div class="ba-simple-gallery-caption">'+
            '<div class="ba-caption-overlay"></div><'+app.edit.tag+' class="ba-simple-gallery-title"></'+app.edit.tag+'>'+
            '<div class="ba-simple-gallery-description"></div></div>';
        $g(children[ind]).append(caption);
        title = children[ind].querySelector('.ba-simple-gallery-title');
        description = children[ind].querySelector('.ba-simple-gallery-description');
    }
    if (!obj.title) {
        title.classList.add('empty-content');
    } else {
        title.classList.remove('empty-content');
    }
    if (!obj.description) {
        description.classList.add('empty-content');
    } else {
        description.classList.remove('empty-content');
    }
    title.textContent = obj.title;
    description.innerHTML = obj.description;
    $g('#item-settings-dialog .sorting-container').html('');
    sortingList.forEach(function(el, ind){
        $g('#item-settings-dialog .sorting-container').append(addSimpleSortingList(sortingList[ind], ind));
    });
    app.addHistory();
    $g('#simple-gallery-item-edit-modal').modal('hide');
});

$g('#item-settings-dialog .add-new-item .zmdi-plus-circle').on('click', function(){
    uploadMode = 'addSimpleImages';
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    checkIframe($g('#uploader-modal').attr('data-check', 'multiple'), 'uploader');
});

$g('#item-settings-dialog .simple-gallery-layout-select').on('customAction', function(){
    app.editor.$g(app.selector+' .instagram-wrapper').removeClass(app.edit.layout);
    app.edit.layout = this.querySelector('input[type="hidden"]').value;
    app.editor.$g(app.selector+' .instagram-wrapper').addClass(app.edit.layout);
    app.editor.setGalleryMasonryHeight(app.editor.app.edit);
    if (app.edit.layout) {
        $g('#item-settings-dialog .simple-gallery-layout-select').closest('.ba-settings-item').nextAll().hide();
    } else {
        $g('#item-settings-dialog .simple-gallery-layout-select').closest('.ba-settings-item').nextAll().css('display', '');
    }
    app.addHistory();
});

app.modules.itemEditor = true;
app.itemEditor();