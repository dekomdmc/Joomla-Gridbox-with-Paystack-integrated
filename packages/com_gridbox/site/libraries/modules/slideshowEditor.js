/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.slideshowEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    let modal = $g('#slideshow-settings-dialog')
    modal.find('.active').removeClass('active');
    modal.find('a[href="#slideshow-general-options"]').parent().addClass('active');
    $g('#slideshow-general-options').addClass('active');
    var li = app.editor.document.querySelectorAll(app.selector+' ul li.item'),
        value;
    sortingList = [];
    modal.find('.sorting-container').html('');
    value = app.getValue('slides');
    setPresetsList(modal);
    if (!app.edit.tag) {
        app.edit.tag = 'h3';
    }
    modal.find('.tab-content [style*="display"]').css('display', '');
    if (app.edit.type != 'recent-posts-slider' && app.edit.type != 'related-posts-slider'
        && app.edit.type != 'recently-viewed-products') {
        if (!app.edit.lightbox) {
            app.edit.popup = false;
            app.edit.lightbox = {
                color: '@overlay'
            }
        }
        modal.find('input[data-option="popup"]').prop('checked', app.edit.popup);
        updateInput(modal.find('[data-option="color"][data-group="lightbox"]'), app.edit.lightbox.color);
        modal.find('input[data-option="popup"], input[data-group="lightbox"]').closest('.ba-settings-group').css('display', '');
        modal.find('.ba-settings-group.items-list, li[data-value="description"]').css('display', '');
        modal.find('li[data-value="info"], li[data-value="intro"], li[data-value="reviews"], li[data-value="postFields"]').hide();
        modal.find('.slideshow-options:not(.carousel-options)').css('display', '');
        modal.find('.slideset-options:not(.carousel-options)').css('display', '');
        modal.find('.recent-posts-slider-options').hide();
    }
    if (app.edit.type != 'recent-posts-slider' && app.edit.type != 'related-posts-slider' && app.edit.type != 'recently-viewed-products'
        && app.edit.type != 'field-slideshow' && app.edit.type != 'product-slideshow') {
        for (var i = 0; i < li.length; i++) {
            if (!value[i + 1]) {
                value[i + 1] = {
                    image: "",
                    type: "image",
                    link: "",
                    embed: "",
                    video: null
                }
            }
            var slide = value[i + 1],
                img = li[i].querySelector('.ba-slideshow-img'),
                title = li[i].querySelector('.ba-slideshow-title').textContent,
                description = li[i].querySelector('.ba-slideshow-description').innerHTML,
                button = li[i].querySelector('.ba-btn-transition'),
                obj = {
                    index : i + 1,
                    title : title,
                    description : description,
                    image : slide.image,
                    type : slide.type,
                    video : slide.video,
                    button : {
                        href : $g(button).attr('href'),
                        embed: '',
                        type : button.className,
                        title : button.textContent,
                        download: button.getAttribute('download'),
                        target : button.target
                    }
                }
            if (typeof(value[i + 1].link) != 'undefined') {
                obj.button.href = value[i + 1].link;
            }
            if (typeof(value[i + 1].embed) != 'undefined') {
                obj.button.embed = value[i + 1].embed;
            }
            if (app.view != 'desktop') {
                obj.type = 'image';
            }
            sortingList.push(obj);
            modal.find('.sorting-container').append(addSlideSortingList(obj, i));
            modal.find('.slideshow-size-select').parent().css('display', '');
        }
    } else if (app.edit.type != 'field-slideshow' && app.edit.type != 'product-slideshow') {
        if (app.edit.type == 'recent-posts-slider' && !('featured' in app.edit)) {
            app.edit.featured = false;
        }
        if (!app.edit.info) {
            app.edit.info = new Array('author', 'date', 'category', 'comments');
        }
        if (!app.edit.desktop.postFields) {
            app.edit.desktop.postFields = {
                "margin":{
                    "bottom":"25",
                    "top":"0"
                },
                "typography":{
                    "color":"@text",
                    "font-family":"@default",
                    "font-size":"16",
                    "font-style":"normal",
                    "font-weight":"400",
                    "letter-spacing":"0",
                    "line-height":"26",
                    "text-decoration":"none",
                    "text-align":"left",
                    "text-transform":"uppercase"
                }
            }
        }
        let subQuery = '.recent-posts-slider-sorting, .recent-posts-slider-featured, '+
            '.tags-categories-list, .recent-posts-slider-app-select';
        if (app.edit.type == 'recent-posts-slider') {
            checkRecentPostsAppType(app.edit.app, modal);
            if (!app.edit.categories) {
                app.edit.categories = {};
            }
            modal.find(subQuery).css('display', '');
            modal.find('.related-posts-slider-options').hide();
            modal.find('input[data-option="featured"]').prop('checked', app.edit.featured);
            modal.find('.recent-posts-app-select input[type="hidden"]').val(app.edit.app);
            value = modal.find('.recent-posts-app-select li[data-value="'+app.edit.app+'"]').text().trim();
            modal.find('.recent-posts-app-select input[readonly]').val(value);
            modal.find('.recent-posts-display-select input[type="hidden"]').val(app.edit.sorting);
            value = modal.find('.recent-posts-display-select li[data-value="'+app.edit.sorting+'"]').text().trim();
            modal.find('.recent-posts-display-select input[readonly]').val(value);
            modal.find('.selected-categories li:not(.search-category)').remove();
            modal.find('.all-categories-list .selected-category').removeClass('selected-category');
            for (var key in app.edit.categories) {
                var str = getCategoryHtml(key, app.edit.categories[key].title);
                modal.find('.selected-categories li.search-category').before(str);
                modal.find('.all-categories-list [data-id="'+key+'"]').addClass('selected-category');
            }
            if (modal.find('.selected-categories li:not(.search-category)').length > 0) {
                modal.find('.ba-settings-item.tags-categories-list').addClass('not-empty-list');
            } else {
                modal.find('.ba-settings-item.tags-categories-list').removeClass('not-empty-list');
            }
            modal.find('.tags-categories .all-categories-list li').hide();
            app.recentPostsCallback = 'getRecentPostsSlider';
        } else if (app.edit.type == 'related-posts-slider') {
            modal.find(subQuery).hide();
            modal.find('.related-posts-slider-options').css('display', '');
            modal.find('.related-posts-display-select input[type="hidden"]').val(app.edit.related);
            value = modal.find('.related-posts-display-select li[data-value="'+app.edit.related+'"]').text().trim();
            modal.find('.related-posts-display-select input[readonly]').val(value);
            app.recentPostsCallback = 'getRelatedPostsSlider';
        } else {
            modal.find(subQuery+', .related-posts-slider-options').hide();
            app.recentPostsCallback = 'getRecentlyViewedProducts';
        }
        modal.find('input[data-option="popup"], input[data-group="lightbox"]').closest('.ba-settings-group').hide();
        modal.find('.recent-posts-layout-select input[type="hidden"]').val(app.edit.layout);
        value = modal.find('.recent-posts-layout-select li[data-value="'+app.edit.layout+'"]').text().trim();
        modal.find('.recent-posts-layout-select input[type="text"]').val(value);
        modal.find('.ba-settings-group.items-list, li[data-value="description"]').hide();
        modal.find('li[data-value="info"], li[data-value="intro"], li[data-value="reviews"], li[data-value="postFields"]')
            .css('display', '');
        modal.find('.slideshow-options:not(.carousel-options)').hide();
        modal.find('.slideset-options:not(.carousel-options)').hide();
        modal.find('.recent-posts-slider-options').css('display', '');
        modal.find('.recent-posts-slider-options input[data-group="view"]').each(function(){
            value = app.getValue('view', this.dataset.option);
            this.checked = value;
        });
        modal.find('input[data-option="limit"]').val(app.edit.limit);
        modal.find('input[data-option="maximum"]').val(app.edit.maximum);
        modal.find('.slideshow-size-select').parent().hide();
        value = app.getValue('view', 'fullscreen');
        modal.find('[data-option="fullscreen"]')[0].checked = value;
        modal.find('.slideshow-animation-select input[type="hidden"]').val(app.edit.animation);
        value = modal.find('.slideshow-animation-select li[data-value="'+app.edit.animation+'"]').text().trim();
        modal.find('.slideshow-animation-select input[readonly]').val(value);
        setRecentPostsSliderLayout();
        checkAppFields(modal);
    }    
    if (!app.edit.desktop.overlay.gradient) {
        app.edit.desktop.overlay.type = 'color';
        app.edit.desktop.overlay.gradient = {
            "effect": "linear",
            "angle": 45,
            "color1": "@bg-dark",
            "position1": 25,
            "color2": "@bg-dark-accent",
            "position2": 75
        }
    }
    value = app.getValue('overlay', 'effect', 'gradient');
    modal.find('.overlay-linear-gradient').hide();
    modal.find('.overlay-'+value+'-gradient').css('display', '');
    modal.find('.overlay-gradient-options .gradient-effect-select input[type="hidden"]').val(value);
    value = modal.find('.overlay-gradient-options .gradient-effect-select li[data-value="'+value+'"]').text().trim();
    modal.find('.overlay-gradient-options .gradient-effect-select input[type="text"]').val(value);
    value = app.getValue('overlay', 'type');
    modal.find('.overlay-color-options, .overlay-gradient-options').hide();
    modal.find('.overlay-'+value+'-options').css('display', '');
    modal.find('.background-overlay-select input[type="hidden"]').val(value);
    value = modal.find('.background-overlay-select li[data-value="'+value+'"]').text().trim();
    modal.find('.background-overlay-select input[type="text"]').val(value);
    modal.find('input[data-subgroup="gradient"][data-group="overlay"]').each(function(){
        value = app.getValue('overlay', this.dataset.option, 'gradient');
        if (this.type == 'number') {
            var range = $g(this).val(value).prev().val(value);
            setLinearWidth(range);
        } else {
            updateInput($g(this), value);
        }
    });
    value = app.getValue('overlay', 'color');
    updateInput(modal.find('[data-option="color"][data-group="overlay"]'), value);
    if (app.edit.type == 'slideshow' || app.edit.type == 'field-slideshow' || app.edit.type == 'product-slideshow') {
        modal.find('[data-group="slideshow"]').each(function(){
            if (this.type == 'checkbox') {
                this.checked = app.edit.slideshow[this.dataset.option];
            } else {
                this.value = app.edit.slideshow[this.dataset.option];
            }
        });
        value = app.getValue('view', 'fullscreen');
        modal.find('[data-option="fullscreen"]')[0].checked = value;
        modal.find('.slideshow-animation-select input[type="hidden"]').val(app.edit.animation);
        value = modal.find('.slideshow-animation-select li[data-value="'+app.edit.animation+'"]').text().trim();
        modal.find('.slideshow-animation-select input[readonly]').val(value);
        if (!app.edit.dots) {
            app.edit.dots = {
                layout: app.edit.desktop.view.dots ? 'enabled-dots' : 'disabled-dots',
                outside: false
            };
            app.edit.desktop.thumbnails = {
                count: 9,
                align: '',
                height: 75
            }
        }
        if (!app.edit.dots.position) {
            app.edit.dots.position = '';
            app.edit.desktop.thumbnails.width = 75;
        }
        if (app.edit.dots.layout != 'thumbnails-dots') {
            modal.find('.thumbnails-navigation-options').hide();
        } else {
            modal.find('.thumbnails-navigation-options').css('display', '');
            if (app.edit.dots.position != '') {
                modal.find('[data-option="height"][data-group="thumbnails"], label[data-group="thumbnails"]')
                    .closest('.ba-settings-item').hide();
                modal.find('[data-option="width"][data-group="thumbnails"]').closest('.ba-settings-item').css('display', '');
            } else {
                modal.find('[data-option="height"][data-group="thumbnails"], label[data-group="thumbnails"]')
                    .closest('.ba-settings-item').css('display', '');
                modal.find('[data-option="width"][data-group="thumbnails"]').closest('.ba-settings-item').hide();
            }
        }
        $g('.slideshow-navigation-select input[type="hidden"]').val(app.edit.dots.layout);
        value = $g('.slideshow-navigation-select li[data-value="'+app.edit.dots.layout+'"]').text().trim();
        $g('.slideshow-navigation-select input[readonly]').val(value);
        $g('.slideshow-navigation-layout-select input[type="hidden"]').val(app.edit.dots.position);
        value = $g('.slideshow-navigation-layout-select li[data-value="'+app.edit.dots.position+'"]').text().trim();
        $g('.slideshow-navigation-layout-select input[readonly]').val(value);
        modal.find('[data-option="outside"]')[0].checked = app.edit.dots.outside;
        value = app.getValue('thumbnails', 'count');
        modal.find('[data-group="thumbnails"][data-option="count"]').val(value);
        value = app.getValue('thumbnails', 'height');
        var range = modal.find('[data-group="thumbnails"][data-option="height"]').val(value).prev().val(value);
        setLinearWidth(range);
        value = app.getValue('thumbnails', 'width');
        var range = modal.find('[data-group="thumbnails"][data-option="width"]').val(value).prev().val(value);
        setLinearWidth(range);
        value = app.getValue('thumbnails', 'align');
        modal.find('label[data-group="thumbnails"]').each(function(){
            if (this.dataset.value == value) {
                this.classList.add('active');
            } else {
                this.classList.remove('active');
            }
        });
    } else {
        if (typeof(app.edit.desktop.slideset.pause) == 'undefined') {
            app.edit.desktop.slideset.pause = false;
        }
        modal.find('[data-group="slideset"]').each(function(){
            value = app.getValue('slideset', this.dataset.option);
            if (this.type == 'checkbox') {
                this.checked = value;
            } else {
                this.value = value;
            }
        });
        value = app.getValue('gutter');
        modal.find('[data-option="gutter"]').prop('checked', value);
        $g('.slideset-caption-select input[type="hidden"]').val(app.edit.desktop.caption.position);
        value = $g('.slideset-caption-select li[data-value="'+app.edit.desktop.caption.position+'"]').text().trim();
        $g('.slideset-caption-select input[readonly]').val(value);
        if (app.edit.desktop.caption.hover == 'caption-hover') {
            value = true;
        } else {
            value = false;
        }
        modal.find('[data-option="hover"][data-group="caption"]').prop('checked', value);
        if (app.edit.desktop.caption.position == '') {
            modal.find('[data-option="hover"][data-group="caption"]').closest('.ba-settings-item').hide();
        } else {
            modal.find('[data-option="hover"][data-group="caption"]').closest('.ba-settings-item').css({
                display: ''
            });
        }
        value = app.getValue('overflow');
        modal.find('[data-option="overflow"]').prop('checked', value);
    }
    value = app.getValue('view', 'dots');
    modal.find('[data-group="view"][data-option="dots"]')[0].checked = value;
    modal.find('.section-access-select input[type="hidden"]').val(app.edit.access);
    value = modal.find('.section-access-select li[data-value="'+app.edit.access+'"]').text().trim();
    modal.find('.section-access-select input[readonly]').val(value);
    value = app.getValue('view', 'height');
    var range = modal.find('[data-group="view"][data-option="height"]').val(value).prev().val(value);
    setLinearWidth(range);
    value = app.getValue('view', 'size');
    $g('.slideshow-size-select input[type="hidden"]').val(value);
    value = $g('.slideshow-size-select li[data-value="'+value+'"]').text().trim();
    $g('.slideshow-size-select input[readonly]').val(value);
    value = app.getValue('view', 'arrows');
    modal.find('[data-group="view"][data-option="arrows"]')[0].checked = value;
    modal.find('.class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    modal.find('[data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    modal.find('[data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#slideshow-settings-dialog');
    if (app.edit.type == 'field-slideshow' || app.edit.type == 'product-slideshow') {
        modal.find('.slideshow-style-custom-select input[type="hidden"]').val('arrows');
        modal.find('.slideshow-style-custom-select input[readonly]').val(gridboxLanguage['ARROWS']);
        showSlideshowDesign('arrows', modal.find('.slideshow-style-custom-select'));
        modal.find('.field-slideshow-options').css('display', '');
        modal.find('input[data-option="label"]').val(app.edit.label);
        modal.find('input[data-option="description"][data-group="options"]').val(app.edit.options.description);
        modal.find('input[data-option="required"]').prop('checked', app.edit.required);
        modal.find('.select-field-upload-source input[type="hidden"]').val(app.edit.options.source);
        value = app.edit.options.source == 'desktop' ? gridboxLanguage['DESKTOP'] : gridboxLanguage['MEDIA_MANAGER'];
        modal.find('.select-field-upload-source input[type="text"]').val(value);
        modal.find('.desktop-source-filesize input').val(app.edit.options.size);
        if (app.edit.options.source == 'desktop') {
            modal.find('.desktop-source-filesize').css('display', '');
        } else {
            modal.find('.desktop-source-filesize').hide();
        }
    } else {
        modal.find('.field-slideshow-options').hide();
        modal.find('.slideshow-style-custom-select input[type="hidden"]').val('title');
        modal.find('.slideshow-style-custom-select input[readonly]').val(gridboxLanguage['TITLE']);
        modal.find('.select-title-html-tag input[type="hidden"]').val(app.edit.tag);
        modal.find('.select-title-html-tag input[readonly]').val(app.edit.tag.toUpperCase());
        showSlideshowDesign('title', modal.find('.slideshow-style-custom-select'));
    }
    modal.attr('data-edit', app.edit.type);
    setTimeout(function(){
        modal.modal();
    }, 150);
}

function getRelatedPostsSlider()
{
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getRelatedPosts",
        data: {
            id : app.editor.themeData.id,
            edit_type: app.editor.themeData.edit_type,
            limit : app.edit.limit,
            related : app.edit.related,
            maximum : app.edit.maximum,
            type: 'slider'
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .slideshow-content').innerHTML = msg.responseText;
            app.editor.app.buttonsPrevent();
            var object = {
                data : app.edit,
                selector : app.editor.app.edit
            };
            app.editor.app.checkModule('initItems', object);
            app.editor.$g(app.selector).find('.ba-blog-post-button-wrapper a')
                .text(app.edit.buttonLabel ? app.edit.buttonLabel : 'Read More');
            checkAppFields($g('#slideshow-settings-dialog'))
            app.addHistory();
        }
    });
}

function getRecentlyViewedProducts()
{
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getRecentlyViewedProducts",
        data: {
            id : app.editor.themeData.id,
            edit_type: app.editor.themeData.edit_type,
            limit : app.edit.limit,
            maximum : app.edit.maximum
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .slideshow-content').innerHTML = msg.responseText;
            app.editor.app.buttonsPrevent();
            var object = {
                data : app.edit,
                selector : app.editor.app.edit
            };
            app.editor.app.checkModule('initItems', object);
            app.editor.$g(app.selector).find('.ba-blog-post-button-wrapper a')
                .text(app.edit.buttonLabel ? app.edit.buttonLabel : 'Read More');
            checkAppFields($g('#slideshow-settings-dialog'))
            app.addHistory();
        }
    });
}

function setRecentPostsSliderLayout()
{
    if (app.edit.layout == 'carousel') {
        $g('#slideshow-settings-dialog')
            .find('input[data-option="count"][data-group="slideset"], input[data-option="gutter"], input[data-option="overflow"]')
            .closest('.ba-settings-item').css('display', '');
        $g('#slideshow-settings-dialog').find('input[data-option="fullscreen"], .slideshow-animation-select')
            .closest('.ba-settings-item').hide();
        $g('#slideshow-settings-dialog .slideset-caption-select').closest('.ba-settings-group').each(function(){
            $g(this).find('.carousel-options').css('display', '');
            $g(this).find('.slideshow-options').hide();
        });
    } else {
        $g('#slideshow-settings-dialog')
            .find('input[data-option="count"][data-group="slideset"], input[data-option="gutter"], input[data-option="overflow"]')
            .closest('.ba-settings-item').hide();
        $g('#slideshow-settings-dialog').find('input[data-option="fullscreen"], .slideshow-animation-select')
            .closest('.ba-settings-item').css('display', '');
        $g('#slideshow-settings-dialog .slideset-caption-select').closest('.ba-settings-group').each(function(){
            $g(this).find('.carousel-options').hide();
            $g(this).find('.slideshow-options').css('display', '');
        });
    }
}

$g('#slideshow-settings-dialog label[data-option][data-group="thumbnails"]').on('mousedown', function(){
    var position = app.getValue('thumbnails', 'align');
    app.editor.$g(app.selector+' .ba-slideshow-dots').removeClass(position);
    app.edit.desktop.thumbnails.align = this.dataset.value;
    app.editor.$g(app.selector+' .ba-slideshow-dots').addClass(this.dataset.value);
});

$g('#slideshow-settings-dialog input[data-option="popup"]').on('change', function(){
    app.edit.popup = this.checked;
    var method = this.checked ? 'addClass' : 'removeClass';
    app.editor.$g(app.selector+' .slideshow-content')[method]('lightbox-enabled');
    app.addHistory();
});

$g('#slideshow-settings-dialog .recent-posts-layout-select').on('customAction', function(){
    var ul = app.editor.$g(app.selector).find('ul');
    app.edit.layout = this.querySelector('input[type="hidden"]').value;
    setRecentPostsSliderLayout();
    if (app.edit.layout == 'carousel') {
        app.editor.$g(app.selector).find('ul.ba-slideset')[0].className = 'ba-slideset carousel-type '+
            app.edit.desktop.caption.hover+' '+app.edit.desktop.caption.position;
        app.edit.desktop.caption.position = '';
        app.edit.desktop.caption.hover = '';
        app.edit.overflow = 'overflow';
    } else {
        app.editor.$g(app.selector).find('ul.ba-slideset')[0].className = 'ba-slideset slideshow-type '+app.edit.animation;
        app.edit.desktop.view.fullscreen = false;
        app.edit.desktop.view.height = 650;
        app.edit.desktop.caption.position = '';
        app.edit.desktop.caption.hover = '';
    }
    if (!app.edit.preset && !app.editor.app.theme.defaultPresets[app.edit.type]) {
        var type = app.edit.type,
            patern = $g.extend(true, {}, presetsPatern[type]),
            is_object = null,
            object = defaultElementsStyle[type];
        for (var ind in patern) {
            if (ind == 'desktop') {
                for (var key in patern[ind]) {
                    is_object = typeof(app.edit[ind][key]) == 'object';
                    app.edit[ind][key] = is_object ? $g.extend(true, {}, object[ind][key]) : object[ind][key];
                }
                for (var ind in app.editor.breakpoints) {
                    if (app.edit[ind]) {
                        for (var key in patern.desktop) {
                            is_object = typeof(app.edit[ind][key]) == 'object';
                            if (is_object && object[ind] && object[ind][key]) {
                                app.edit[ind][key] = $g.extend(true, {}, object[ind][key]);
                            } else if (!is_object && object[ind] && object[ind][key]) {
                                app.edit[ind][key] = object[ind][key];
                            } else if (is_object) {
                                app.edit[ind][key] = {};
                            } else {
                                delete(app.edit[ind][key]);
                            }
                        }
                    }
                }
            } else {
                is_object = typeof(app.edit[ind]) == 'object';
                app.edit[ind] = is_object ? $g.extend(true, {}, object[ind]) : object[ind];
            }
        }
        if (app.edit.layout != 'carousel') {
            app.edit.desktop.title.typography.color = '@title-inverse';
            app.edit.desktop.title.typography['font-size'] = 56;
            app.edit.desktop.title.typography['line-height'] = 65;
            app.edit.desktop.title.typography['font-weight'] = '700';
            app.edit.desktop.title.typography['text-align'] = 'center';
            app.edit.desktop.info.typography.color = '@title-inverse';
            app.edit.desktop.info.typography['text-align'] = 'center';
            app.edit.desktop.intro.typography.color = '@title-inverse';
            app.edit.desktop.intro.typography['font-size'] = 24;
            app.edit.desktop.intro.typography['text-align'] = 'center';
            app.edit.desktop.button.typography['text-align'] = 'center';
            app.edit.desktop.overlay.color = '@overlay';
        }
        app.editor.app.checkModule('editItem');
        app.editor.app.setNewFont = true;
        app.editor.app.fonts = {};
        app.editor.app.customFonts = {};
    }
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    };
    app.sectionRules();
    app.editor.app.checkModule('initItems', object);
    app.addHistory();
});

function getRecentPostsSlider()
{
    var category = new Array();
    for (var key in app.edit.categories) {
        category.push(key);
    }
    category = category.join(',');
    checkRecentPostsAppType(app.edit.app, $g('#slideshow-settings-dialog'));
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getRecentPostsSlider&tmpl=component",
        data: {
            id : app.edit.app,
            limit : app.edit.limit,
            sorting : app.edit.sorting,
            category : category,
            maximum : app.edit.maximum,
            featured: Number(app.edit.featured)
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .slideshow-content').innerHTML = msg.responseText;
            app.editor.app.buttonsPrevent();
            var object = {
                data : app.edit,
                selector : app.editor.app.edit
            };
            app.editor.app.checkModule('initItems', object);
            app.editor.$g(app.selector).find('.ba-blog-post-button-wrapper a')
                .text(app.edit.buttonLabel ? app.edit.buttonLabel : 'Read More');
            checkAppFields($g('#slideshow-settings-dialog'))
            app.addHistory();
        }
    });
}

$g('.slideset-caption-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val();
    app.edit.desktop.caption.position = value;
    if (app.edit.desktop.caption.position == '') {
        $g('#slideshow-settings-dialog [data-option="hover"][data-group="caption"]').closest('.ba-settings-item').hide();
    } else {
        $g('#slideshow-settings-dialog [data-option="hover"][data-group="caption"]').closest('.ba-settings-item').css({
            display: ''
        });
    }
    if (!app.edit.preset && !app.editor.app.theme.defaultPresets[app.edit.type]) {
        var type = app.edit.type,
            patern = $g.extend(true, {}, presetsPatern[type]),
            is_object = null,
            theme = app.editor.app.theme,
            object = defaultElementsStyle[type];
        for (var ind in patern) {
            if (ind == 'desktop') {
                for (var key in patern[ind]) {
                    is_object = typeof(app.edit[ind][key]) == 'object';
                    app.edit[ind][key] = is_object ? $g.extend(true, {}, object[ind][key]) : object[ind][key];
                }
                for (var ind in app.editor.breakpoints) {
                    if (app.edit[ind]) {
                        for (var key in patern.desktop) {
                            is_object = typeof(app.edit[ind][key]) == 'object';
                            if (is_object && object[ind] && object[ind][key]) {
                                app.edit[ind][key] = $g.extend(true, {}, object[ind][key]);
                            } else if (!is_object && object[ind] && object[ind][key]) {
                                app.edit[ind][key] = object[ind][key];
                            } else if (is_object) {
                                app.edit[ind][key] = {};
                            } else {
                                delete(app.edit[ind][key]);
                            }
                        }
                    }
                }
            } else {
                is_object = typeof(app.edit[ind]) == 'object';
                app.edit[ind] = is_object ? $g.extend(true, {}, object[ind]) : object[ind];
            }
        }
        if (app.edit.type != 'recent-posts-slider' && app.edit.type != 'related-posts-slider'
            && app.edit.type != 'recently-viewed-products' && app.edit.desktop.caption.position == '') {
            app.edit.desktop.overlay.color = '@bg-secondary';
            app.edit.desktop.description.typography.color = '@text';
            app.edit.desktop.title.typography.color = '@text';
            app.edit.desktop.title.typography['font-size'] = 20;
            app.edit.desktop.title.typography['line-height'] = 30;
        } else if (app.edit.type == 'recent-posts-slider' || app.edit.type == 'related-posts-slider'
            || app.edit.type == 'recently-viewed-products') {
            var view = $g.extend(true, {}, object.desktop.view);
            if (app.edit.desktop.caption.position != '') {
                view.height = 400;
                app.edit.desktop.overlay.color = '@overlay';
                app.edit.desktop.title.typography.color = '@title-inverse';
                app.edit.desktop.title.typography['font-size'] = 24;
                app.edit.desktop.title.typography['line-height'] = 34;
                app.edit.desktop.title.margin.top = 200;
                app.edit.desktop.title.margin.bottom = 25;
                app.edit.desktop.info.typography.color = '@title-inverse';
                app.edit.desktop.intro.typography.color = '@title-inverse';
                app.edit.desktop.postFields.typography.color = '@title-inverse';
                app.edit.desktop.reviews.typography.color = '@title-inverse';
            }
            app.edit.desktop.view = $g.extend(true, app.edit.desktop.view, view);
        }
        app.editor.app.checkModule('editItem');
        app.editor.app.setNewFont = true;
        app.editor.app.fonts = {};
        app.editor.app.customFonts = {};
    }
    app.sectionRules();
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    };
    app.editor.app.checkModule('initItems', object);
    app.addHistory();
});

$g('#slideshow-settings-dialog [data-option="hover"][data-group="caption"]').on('change', function(){
    var item = app.editor.document.querySelector(app.selector+' ul');
    if (this.checked) {
        app.edit.desktop.caption.hover = 'caption-hover';
    } else {
        app.edit.desktop.caption.hover = '';
    }
    app.sectionRules();
    setTimeout(function(){
        var object = {
            data : app.edit,
            selector : app.editor.app.edit
        };
        app.editor.app.checkModule('initItems', object);
    }, 300);
    app.addHistory();
});

$g('.slideshow-item-effect-select').on('customAction', function(){
    var $this = $g(this).find('input[type="hidden"]')[0],
        value = $this.value,
        option = $this.dataset.option,
        group = $this.dataset.group,
        subgroup = $this.dataset.subgroup,
        items = app.editor.document.querySelectorAll(app.selector+' .ba-slideshow-'+group);
    if (group == 'button') {
        items = app.editor.document.querySelectorAll(app.selector+' .slideshow-button a');
    }
    if (app.edit.desktop[group][subgroup][option]) {
        for (var i = 0; i < items.length; i ++) {
            items[i].classList.remove(app.edit.desktop[group][subgroup][option]);
        }
    }
    app.edit.desktop[group][subgroup][option] = value;
    if (app.edit.desktop[group][subgroup][option]) {
        for (var i = 0; i < items.length; i ++) {
            items[i].classList.add(app.edit.desktop[group][subgroup][option]);
        }
    }
    app.addHistory();
});

$g('#slideshow-settings-dialog .slideshow-animation-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val(),
        item = app.editor.document.querySelector(app.selector+' > div > ul');
    item.classList.remove(app.edit.animation);
    app.edit.animation = value;
    item.classList.add(app.edit.animation);
    app.addHistory();
});

$g('#slideshow-settings-dialog [data-group="slideshow"]').on('change input', function(){
    var option = this.dataset.option,
        value = this.value;
    if (this.type == 'checkbox') {
        value = this.checked;
    } else if (value == '') {
        value = app.getValue('slideset', option);
    }
    app.setValue(value, 'slideshow', option);
    app.editor.app.initslideshow(app.edit, app.editor.app.edit);
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.addHistory();
    }, 300);
});

$g('#slideshow-settings-dialog [data-group="slideset"]').on('change input', function(){
    var option = this.dataset.option,
        value = this.value;
    if (this.type == 'checkbox') {
        value = this.checked;
    } else if (value == '') {
        value = app.getValue('slideset', option);
    }
    app.setValue(value, 'slideset', option);
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    }
    app.sectionRules();
    app.editor.app.checkModule('initItems', object);
    delay = setTimeout(function(){
        app.addHistory();
    }, 300);
});

$g('#slideshow-settings-dialog [data-option="gutter"]').on('change', function(){
    app.setValue(this.checked, 'gutter');
    app.editor.$g('.ba-item-'+app.edit.type).each(function(){
        if (app.editor.app.items[this.id]) {
            var obj = {
                data : app.editor.app.items[this.id],
                selector : this.id
            };
            app.editor.itemsInit.push(obj);
        }
    });
    if (app.editor.itemsInit.length > 0) {
        app.editor.app.checkModule('initItems', app.editor.itemsInit.pop());
    }
    app.sectionRules();
    app.addHistory();
});

$g('#slideshow-settings-dialog .add-new-item i').on('click', function(){
    uploadMode = 'addNewSlides';
    checkIframe($g('#uploader-modal').attr('data-check', 'multiple'), 'uploader');
    return false;
});

$g('#slideshow-settings-dialog .sorting-container').on('click', 'i.zmdi.zmdi-edit', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key'),
        obj = $g.extend({}, sortingList[key]),
        value = 'image',
        video = {
            type : 'youtube',
            id : '',
            mute : true,
            start : 0,
            quality : 'hd720'
        },
        slides = app.getValue('slides'),
        object = slides[obj.index];
    if (object.video && app.view == 'desktop') {
        value = 'video';
        video = object.video;
    }
    if (video.type == 'source') {
        $g('#slideshow-item-dialog .video-source-select').css('display', '');
        $g('#slideshow-item-dialog .video-id').hide();
    } else {
        $g('#slideshow-item-dialog .video-source-select').hide();
        $g('#slideshow-item-dialog .video-id').css('display', '');
    }
    if (!video.source) {
        video.source == '';
    }
    $g('#slideshow-item-dialog .video-options, #slideshow-item-dialog .image-options').hide();
    $g('#slideshow-item-dialog .'+value+'-options').show();
    $g('#slideshow-item-dialog .slide-type-select input[type="hidden"]').val(value);
    value = $g('#slideshow-item-dialog .slide-type-select li[data-value="'+value+'"]').text().trim();
    $g('#slideshow-item-dialog .slide-type-select input[readonly]').val(value);
    $g('#slideshow-item-dialog .slide-image').val(object.image);
    $g('#slideshow-item-dialog .video-select input[type="hidden"]').val(video.type);
    value = $g('#slideshow-item-dialog .video-select li[data-value="'+video.type+'"]').text().trim();
    $g('#slideshow-item-dialog .video-select input[readonly]').val(value);
    $g('#slideshow-item-dialog .slide-video-id').val(video.id);
    $g('#slideshow-item-dialog .video-source-select input').val(video.source);
    $g('#slideshow-item-dialog .slide-video-start').val(video.start);
    $g('#slideshow-item-dialog .slide-video-mute').prop('checked', video.mute);
    $g('#slideshow-item-dialog .youtube-quality input[type="hidden"]').val(video.quality);
    value = $g('#slideshow-item-dialog .youtube-quality li[data-value="'+video.quality+'"]').text().trim();
    $g('#slideshow-item-dialog .youtube-quality input[readonly]').val(value);
    $g('#slideshow-item-dialog .slide-title').val(obj.title);
    $g('#slideshow-item-dialog .slide-description').val(obj.description);
    if (obj.button.type.indexOf('ba-overlay-slideshow-button') != -1) {
        value = 'link';
        $g('#slideshow-item-dialog .slideshow-button-label').hide();
    } else {
        value = 'button';
        $g('#slideshow-item-dialog .slideshow-button-label').show();
    }
    $g('#slideshow-item-dialog .slide-button-type-select input[type="hidden"]').val(value);
    value = $g('#slideshow-item-dialog .slide-button-type-select li[data-value="'+value+'"]').text().trim();
    $g('#slideshow-item-dialog .slide-button-type-select input[readonly]').val(value);
    $g('#slideshow-item-dialog').find('.slide-button-link').val(obj.button.href);
    $g('#slideshow-item-dialog').find('.slide-button-label').val(obj.button.title);
    $g('#slideshow-item-dialog').find('.slide-button-embed-code').val(obj.button.embed);
    $g('#slideshow-item-dialog .slide-button-target-select input[type="hidden"]').val(obj.button.target);
    value = $g('#slideshow-item-dialog .slide-button-target-select li[data-value="'+obj.button.target+'"]').text().trim();
    $g('#slideshow-item-dialog .slide-button-target-select input[readonly]').val(value);
    if (obj.button.download == null) {
        obj.button.download = '';
    }
    $g('#slideshow-item-dialog .slide-button-attribute-select input[type="hidden"]').val(obj.button.download);
    value = $g('#slideshow-item-dialog .slide-button-attribute-select li[data-value="'+obj.button.download+'"]').text().trim();
    $g('#slideshow-item-dialog .slide-button-attribute-select input[readonly]').val(value);
    $g('#apply-new-slide').removeClass('disable-button').addClass('active-button').attr('data-edit', key);
    if (app.edit.type != 'slideshow') {
        $g('.slideshow-slide-select').hide();
    } else {
        $g('.slideshow-slide-select')[0].style.display = '';
    }
    $g('#slideshow-item-dialog').modal();
});

$g('#slideshow-settings-dialog .sorting-container').on('click', 'i.zmdi.zmdi-copy', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key') * 1,
        image = app.editor.$g(app.selector+' .slideshow-content > li').get(key),
        clone = image.cloneNode(true),
        obj = $g.extend({}, app.edit.desktop.slides[key + 1]),
        slides = {};
    key += 1;
    $g(clone).find('.ba-slideshow-img > *').attr('id', new Date().getTime());
    $g(image).after(clone);
    app.editor.app.buttonsPrevent();
    for (var ind in app.edit.desktop.slides) {
        if (ind == key) {
            slides[ind] = app.edit.desktop.slides[ind];
            slides[key + 1] = obj;
        } else if (ind >= key + 1) {
            slides[ind * 1 + 1] = app.edit.desktop.slides[ind];
        } else {
            slides[ind] = app.edit.desktop.slides[ind];
        }
    }
    app.edit.desktop.slides = slides;
    for (var point in app.editor.breakpoints) {
        if (app.edit[point] && app.edit[point].slides) {
            slides = {};
            for (var ind in app.edit[point].slides) {
                if (ind == key) {
                    slides[ind] = app.edit[point].slides[ind];
                    slides[key + 1] = obj;
                } else if (ind >= key + 1) {
                    slides[ind * 1 + 1] = app.edit[point].slides[ind];
                } else {
                    slides[ind] = app.edit[point].slides[ind];
                }
            }
            app.edit[point].slides = slides;
        }
    }
    var li = app.editor.document.querySelectorAll('#'+app.editor.app.edit+' ul li.item'),
        value = app.getValue('slides');
    sortingList = [];
    $g('#slideshow-settings-dialog .sorting-container').html('');
    for (var i = 0; i < li.length; i++) {
        if (!value[i + 1]) {
            value[i + 1] = {
                image: "",
                type: "image",
                link: "",
                embed: "",
                video: null
            }
        }
        var slide = value[i + 1],
            img = li[i].querySelector('.ba-slideshow-img'),
            title = li[i].querySelector('.ba-slideshow-title').textContent,
            description = li[i].querySelector('.ba-slideshow-description').innerHTML,
            button = li[i].querySelector('.ba-btn-transition'),
            obj = {
                index : i + 1,
                title : title,
                description : description,
                image : slide.image,
                type : slide.type,
                video : slide.video,
                button : {
                    href : $g(button).attr('href'),
                    embed: '',
                    type : button.className,
                    title : button.textContent,
                    download: button.getAttribute('download'),
                    target : button.target
                }
            }
        if (typeof(value[i + 1].link) != 'undefined') {
            obj.button.href = value[i + 1].link;
        }
        if (typeof(value[i + 1].embed) != 'undefined') {
            obj.button.embed = value[i + 1].embed;
        }
        if (app.view != 'desktop') {
            obj.type = 'image';
        }
        sortingList.push(obj);
        $g('#slideshow-settings-dialog .sorting-container').append(addSlideSortingList(obj, i));
        $g('#slideshow-settings-dialog .slideshow-size-select').parent().css('display', '');
    }
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    }
    app.editor.app.checkModule('initItems', object);
    app.sectionRules();
    app.addHistory();
});

$g('#slideshow-item-dialog').find('.slide-image').on('click', function(){
    fontBtn = this;
    uploadMode = 'slideImage';
    checkIframe($g('#uploader-modal').attr('data-check', 'single'), 'uploader');
});

$g('#slideshow-item-dialog').find('.slide-image, .slide-video-id').on('input', function(){
    if (this.value.trim()) {
        $g('#apply-new-slide').removeClass('disable-button').addClass('active-button');
    } else {
        $g('#apply-new-slide').addClass('disable-button').removeClass('active-button');
    }
});

$g('#slideshow-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    app.checkModule('deleteItem');
});

$g('#slideshow-item-dialog .slide-button-type-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val();
    if (value == 'button') {
        $g('#slideshow-item-dialog .slideshow-button-label').show();
    } else {
        $g('#slideshow-item-dialog .slideshow-button-label').hide();
    }
});

$g('#slideshow-item-dialog .slide-type-select').on('customAction', function(){
    var target = $g(this).find('input[type="hidden"]').val(),
        parent = $g('#slideshow-item-dialog .'+target+'-options');
    $g('#slideshow-item-dialog').find('.image-options, .video-options').hide();
    parent.show();
    parent.addClass('ba-active-options');
    setTimeout(function(){
        parent.removeClass('ba-active-options');
    }, 1);
    $g('#slideshow-item-dialog .video-source-select').hide();
    $g('#slideshow-item-dialog').find('.slide-image, .slide-video-id, .video-source-select input').val('');
    $g('#apply-new-slide').addClass('disable-button').removeClass('active-button');
});

$g('#apply-new-slide').on('click', function(){
    if (!this.classList.contains('active-button')) {
        return false;
    }
    var modal = $g('#slideshow-item-dialog'),
        obj = {
            image : modal.find('.slide-image').val(),
            type : modal.find('.slide-type-select input[type="hidden"]').val(),
            video : {
                type : modal.find('.video-select input[type="hidden"]').val(),
                id : modal.find('.slide-video-id').val().trim(),
                source: modal.find('.video-source-select input').val().trim(),
                mute : modal.find('.slide-video-mute')[0].checked,
                start : modal.find('.slide-video-start').val().trim(),
                quality : modal.find('.youtube-quality input[type="hidden"]').val()
            },
            title : modal.find('.slide-title').val().trim(),
            description : modal.find('.slide-description').val().trim(),
            button : {
                href : modal.find('.slide-button-link').val().trim(),
                embed : modal.find('.slide-button-embed-code').val().trim(),
                type : modal.find('.slide-button-type-select input[type="hidden"]').val(),
                title : modal.find('.slide-button-label').val().trim(),
                target : modal.find('.slide-button-target-select input[type="hidden"]').val(),
                download : modal.find('.slide-button-attribute-select input[type="hidden"]').val()
            }
        };
    if (obj.type == 'image') {
        obj.video = null;
    }
    if (obj.button.type == 'button') {
        obj.button.type = 'ba-btn-transition';
    } else {
        obj.button.type = 'ba-btn-transition ba-overlay-slideshow-button';
    }
    var str = getSlideHtml(obj),
        key = this.dataset.edit,
        item = $g('#slideshow-settings-dialog .sorting-container .sorting-item[data-key="'+key+'"]'),
        div;
    obj.index = sortingList[key].index;
    sortingList[key] = obj;
    item.replaceWith(addSlideSortingList(obj, key));
    $g('#slideshow-settings-dialog .sorting-container .sorting-item').each(function(ind){
        if (this.dataset.key == key) {
            if (!app.edit[app.view].slides) {
                app.edit[app.view].slides = {};
            }
            app.edit.desktop.slides[ind + 1].link = obj.button.href;
            app.edit.desktop.slides[ind + 1].embed = obj.button.embed;
            app.edit[app.view].slides[ind + 1] = {
                image : obj.image,
                type : obj.type,
                link : obj.button.href,
                embed : obj.button.embed,
                video : obj.video
            }
            div = app.editor.document.querySelector('#'+app.editor.app.edit+' .slideshow-content > li:nth-child('+(ind + 1)+')');
            $g(div).replaceWith(str);
            if (obj.button.embed) {
                var a = app.editor.$g('#'+app.editor.app.edit+' .slideshow-content > li:nth-child('+(ind + 1)+') .slideshow-button a');
                replaceSlideEmbed(a, obj.button);
            }
            return false;
        }
    });
    app.editor.app.buttonsPrevent();
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    }
    app.editor.app.checkModule('initItems', object);
    app.sectionRules();
    app.addHistory();
    modal.modal('hide');
});

$g('#slideshow-settings-dialog .slideshow-navigation-select').on('customAction', function(){
    app.editor.$g(app.selector+' .ba-slideshow-dots').removeClass(app.edit.dots.layout);
    app.edit.dots.layout = this.querySelector('input[type="hidden"]').value;
    app.editor.$g(app.selector+' .ba-slideshow-dots').addClass(app.edit.dots.layout);
    if (app.edit.dots.layout != 'thumbnails-dots') {
            $g('#slideshow-settings-dialog .thumbnails-navigation-options').hide();
        } else {
            $g('#slideshow-settings-dialog .thumbnails-navigation-options').css('display', '');
            if (app.edit.dots.position != '') {
                $g('#slideshow-settings-dialog').find('[data-option="height"][data-group="thumbnails"], label[data-group="thumbnails"]')
                    .closest('.ba-settings-item').hide();
                $g('#slideshow-settings-dialog').find('[data-option="width"][data-group="thumbnails"]')
                    .closest('.ba-settings-item').css('display', '');
            } else {
                $g('#slideshow-settings-dialog').find('[data-option="height"][data-group="thumbnails"], label[data-group="thumbnails"]')
                    .closest('.ba-settings-item').css('display', '');
                $g('#slideshow-settings-dialog').find('[data-option="width"][data-group="thumbnails"]')
                    .closest('.ba-settings-item').hide();
            }
        }
    app.sectionRules();
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    };
    app.editor.app.checkModule('initslideshow', object);
    app.addHistory();
});

$g('#slideshow-settings-dialog .slideshow-navigation-layout-select').on('customAction', function(){
    app.editor.$g(app.selector+' .slideshow-wrapper').removeClass(app.edit.dots.position);
    app.edit.dots.position = this.querySelector('input[type="hidden"]').value;
    app.editor.$g(app.selector+' .slideshow-wrapper').addClass(app.edit.dots.position);
    if (app.edit.dots.position != '') {
        $g('#slideshow-settings-dialog').find('[data-option="height"][data-group="thumbnails"], label[data-group="thumbnails"]')
            .closest('.ba-settings-item').hide();
        $g('#slideshow-settings-dialog').find('[data-option="width"][data-group="thumbnails"]')
            .closest('.ba-settings-item').css('display', '');
    } else {
        $g('#slideshow-settings-dialog').find('[data-option="height"][data-group="thumbnails"], label[data-group="thumbnails"]')
            .closest('.ba-settings-item').css('display', '');
        $g('#slideshow-settings-dialog').find('[data-option="width"][data-group="thumbnails"]')
            .closest('.ba-settings-item').hide();
    }
    app.sectionRules();
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    };
    app.editor.app.checkModule('initslideshow', object);
    app.addHistory();
});

$g('#slideshow-settings-dialog input[data-option="outside"]').on('change', function(){
    app.edit.dots.outside = this.checked;
    if (this.checked) {
        app.editor.$g(app.selector+' .slideshow-wrapper').addClass('dots-position-outside');
    } else {
        app.editor.$g(app.selector+' .slideshow-wrapper').removeClass('dots-position-outside');
    }
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    };
    app.editor.app.checkModule('initslideshow', object);
    app.addHistory();
});

app.modules.slideshowEditor = true;
app.slideshowEditor();