/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.countdownEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#countdown-settings-dialog .active').removeClass('active');
    $g('#countdown-settings-dialog a[href="#countdown-general-options"]').parent().addClass('active');
    $g('#countdown-general-options').addClass('active');
    var value = '';
    setPresetsList($g('#countdown-settings-dialog'));
    $g('#countdown-settings-dialog').find('.ba-settings-group, .ba-settings-item').css('display', '');
    if ((app.edit.type == 'overlay-button' || app.edit.type == 'button' || app.edit.type == 'icon') && !('embed' in app.edit)) {
        app.edit.embed = '';
    }
    if (app.edit.type == 'overlay-button') {
        if (!app.edit.trigger) {
            app.edit.trigger = 'button';
            app.edit.desktop.style = {
                "width" : "650",
                "align" : "center"
            }
            app.edit.image = "components/com_gridbox/assets/images/default-theme.png";
            app.edit.alt = "";
            app.edit.sides = {
                image: {
                    "desktop":{
                        "border" : {
                            "color" : "@border",
                            "radius" : "9",
                            "style" : "solid",
                            "width" : "0"
                        },
                        "margin" : {
                            "bottom" : "25",
                            "top" : "25"
                        },
                        "shadow" : {
                            "value" : "0",
                            "color" : "@shadow"
                        }
                    },
                    "tablet":{},
                    "phone":{},
                    "tablet-portrait":{},
                    "phone-portrait":{}
                },
                button: {
                    "desktop":{},
                    "tablet":{},
                    "phone":{},
                    "tablet-portrait":{},
                    "phone-portrait":{}
                }
            }
            app.edit.caption = {
                title: '',
                description: '',
            }
            app.edit.desktop.overlay = {
                type: 'none',
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
            var array = new Array('border', 'margin', 'shadow');
            for (var i = 0; i < array.length; i++) {
                app.edit.sides.button.desktop[array[i]] = $g.extend(true, {}, app.edit.desktop[array[i]]);
            }
            for (var ind in app.editor.breakpoints) {
                if (app.edit[ind]) {
                    for (var i = 0; i < array.length; i++) {
                        if (app.edit[ind] && app.edit[ind][array[i]]) {
                            app.edit.sides.button[ind][array[i]] = $g.extend(true, {}, app.edit[ind][array[i]]);
                        }
                    }
                }
            }
        }
        if (!app.edit.tag) {
            app.edit.tag = 'h3';
        }
        app.editor.setOverlaySectionTrigger(app.edit, app.edit.trigger);
        $g('.overlay-button-trigger-select input[type="hidden"]').val(app.edit.trigger);
        $g('.overlay-button-trigger-select input[type="text"]').val(gridboxLanguage[app.edit.trigger.toUpperCase()]);
        var src = app.edit.image,
            array = src.split('/'),
            str = '<div class="sorting-item"><div class="sorting-image">';                
        if (src.indexOf('balbooa.com') == -1) {
            src = JUri+src;
        }
        str += '<img src="'+src+'"></div><div class="sorting-title">'+array[array.length - 1]+
            '</div><div class="sorting-icons"><span><i class="zmdi zmdi-edit"></i></span></div></div>';
        $g('#countdown-settings-dialog .sorting-container').html(str);
        value = app.getValue('overlay', 'effect', 'gradient');
        $g('#countdown-settings-dialog .overlay-linear-gradient').hide();
        $g('#countdown-settings-dialog .overlay-'+value+'-gradient').css('display', '');
        $g('#countdown-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="hidden"]').val(value);
        value = $g('#countdown-settings-dialog .overlay-gradient-options .gradient-effect-select li[data-value="'+value+'"]').text().trim();
        $g('#countdown-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="text"]').val(value);
        value = app.getValue('overlay', 'type');
        $g('#countdown-settings-dialog .overlay-color-options, .overlay-gradient-options').hide();
        $g('#countdown-settings-dialog .overlay-'+value+'-options').css('display', '');
        $g('#countdown-settings-dialog .background-overlay-select input[type="hidden"]').val(value);
        value = $g('#countdown-settings-dialog .background-overlay-select li[data-value="'+value+'"]').text().trim();
        $g('#countdown-settings-dialog .background-overlay-select input[type="text"]').val(value);
        $g('#countdown-settings-dialog .slideshow-style-custom-select input[type="hidden"]').val('title');
        $g('#countdown-settings-dialog .slideshow-style-custom-select input[readonly]').val(gridboxLanguage['TITLE']);
        $g('#countdown-settings-dialog .select-title-html-tag input[type="hidden"]').val(app.edit.tag);
        $g('#countdown-settings-dialog .select-title-html-tag input[readonly]').val(app.edit.tag.toUpperCase());
        showSlideshowDesign('title', $g('#countdown-settings-dialog .slideshow-style-custom-select'));
        $g('.overlay-button-options').find('[data-group="style"], [data-group="animation"], [data-group="overlay"]').each(function(){
            value = app.getValue(this.dataset.group, this.dataset.option, this.dataset.subgroup);
            if (this.dataset.type == 'color') {
                updateInput($g(this), value);
            } if (this.type == 'number') {
                var range = $g(this).val(value).prev().val(value);
                if (range.length > 0) {
                    setLinearWidth(range);
                }
            } else if (this.type == 'hidden') {
                this.value = value;
                value = this.parentNode.querySelector('li[data-value="'+value+'"]').textContent.trim();
                this.previousElementSibling.value = value;
            } else if (this.dataset.type == 'color') {
                updateInput($g(this), value);
            } else if (this.dataset.value == value) {
                this.classList.add('active');
            } else {
                this.classList.remove('active');
            }
        });
        if (app.edit.trigger == 'button') {
            $g('.button-label').closest('.button-options').css('display', '');
            $g('#countdown-settings-dialog .padding-options').css('display', '');
            $g('#countdown-design-options .ba-settings-group').css('display', '');
            $g('.overlay-image-options').hide();
            app.editor.$g(app.selector+' > .ba-image-wrapper').remove();
        } else {
            $g('.button-label').closest('.button-options').hide();
            $g('#countdown-settings-dialog .padding-options').hide();
            $g('.overlay-image-options').css('display', '');
        }
        $g('#countdown-settings-dialog .button-embed-code').val(app.edit.embed);
    } else {
        $g('.overlay-button-options').hide();
    }
    switch (app.edit.type) {
        case 'countdown' :
            app.setTypography($g('#countdown-settings-dialog .countdown-options .typography-options'), 'counter');
            $g('#countdown-settings-dialog .typography-select input[type="hidden"]').val('counter');
            value = $g('#countdown-settings-dialog .typography-select li[data-value="counter"]').text();
            $g('#countdown-settings-dialog .typography-select input[readonly]').val($g.trim(value));
            value = app.getValue('background', 'color');
            updateInput($g('#countdown-settings-dialog .background input[data-option="color"]'), value);
            $g('#countdown-input').val(app.edit.date);
            $g('.countdown-display-select input[type="hidden"]').val(app.edit.display);
            value = $g('.countdown-display-select li[data-value="'+app.edit.display+'"]').text();
            $g('.countdown-display-select input[readonly]').val(value);
            $g('input[data-option="hide-after"]').prop('checked', app.edit['hide-after']);
            $g('.constants.countdown-options [data-option]').each(function(){
                this.value = app.edit[this.dataset.option];
            });
            break;
        case 'icon' :
            value = app.getValue('icon', 'text-align');
            $g('#countdown-settings-dialog [data-option="text-align"][data-value="'+value+'"]').addClass('active');
            value = app.getValue('icon', 'size');
            $g('#countdown-settings-dialog .icon-options [data-option="size"]').val(value);
            var range = $g('#countdown-settings-dialog .icon-options [data-option="size"]').prev();
            range.val(value);
            setLinearWidth(range);
            value = app.editor.document.getElementById(app.editor.app.edit);
            value = value.querySelector('.ba-icon-wrapper i');
            value = value.dataset.icon.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
            $g('#countdown-settings-dialog .reselect-icon').val(value);
        case 'button' :
            $g('#countdown-settings-dialog [data-option="link"][data-group="link"]').val(app.edit.link.link);
            $g('#countdown-settings-dialog .link-target-select input[type="hidden"]').val(app.edit.link.target);
            value = $g('#countdown-settings-dialog .link-target-select li[data-value="'+app.edit.link.target+'"]').text();
            $g('#countdown-settings-dialog .link-target-select input[readonly]').val($g.trim(value));
            if (!app.edit.link.type) {
                app.edit.link.type = '';
            }
            $g('#countdown-settings-dialog .link-type-select input[type="hidden"]').val(app.edit.link.type);
            value = $g('#countdown-settings-dialog .link-type-select li[data-value="'+app.edit.link.type+'"]').text();
            $g('#countdown-settings-dialog .link-type-select input[readonly]').val($g.trim(value));
            $g('#countdown-settings-dialog .button-embed-code').val(app.edit.embed);
        case 'overlay-button' :
        case 'tags' :
        case 'post-tags' :
        case 'scroll-to-top' :
        case 'scroll-to' :
        case 'cart' :
            value = app.getValue('padding', 'top');
            $g('#countdown-settings-dialog [data-group="padding"][data-option="top"]').val(value);
            value = app.getValue('padding', 'right');
            $g('#countdown-settings-dialog [data-group="padding"][data-option="right"]').val(value);
            value = app.getValue('padding', 'bottom');
            $g('#countdown-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
            value = app.getValue('padding', 'left');
            $g('#countdown-settings-dialog [data-group="padding"][data-option="left"]').val(value);
            updateInput($g('#countdown-settings-dialog [data-option="color"][data-group="hover"]'), app.edit.hover.color);
            value = app.edit.hover['background-color'];
            updateInput($g('#countdown-settings-dialog [data-option="background-color"][data-group="hover"]'), value);
            value = app.getValue('normal', 'color');
            updateInput($g('#countdown-settings-dialog [data-option="color"][data-group="normal"]'), value);
            value = app.getValue('normal', 'background-color');
            updateInput($g('#countdown-settings-dialog [data-option="background-color"][data-group="normal"]'), value);
        case 'counter' :
            value = app.getValue('shadow', 'value');
            value = $g('#countdown-settings-dialog input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
            setLinearWidth(value);
            value = app.getValue('shadow', 'color');
            updateInput($g('#countdown-settings-dialog input[data-option="color"][data-group="shadow"]'), value);
    }
    if (app.editor.document.getElementById(app.editor.app.edit).dataset.cookie == 'accept') {
        $g('.button-link-options').hide();
    } else {
        $g('.button-link-options').css('display', '');
    }
    if (app.edit.type == 'button' || app.edit.type == 'overlay-button') {
        value = app.editor.document.querySelector(app.selector+' .ba-button-wrapper a span').textContent;
        $g('#countdown-settings-dialog input.button-label').val(value);
        value = app.getValue('icons', 'size');
        $g('#countdown-settings-dialog [data-option="size"][data-group="icons"]').val(value);
        var range = $g('#countdown-settings-dialog [data-option="size"][data-group="icons"]').prev();
        range.val(value);
        setLinearWidth(range);
        value = app.editor.document.getElementById(app.editor.app.edit);
        value = value.querySelector('.ba-button-wrapper a i');
        if (value) {
            value = value.className.replace('zmdi zmdi-', '').replace('fa fa-', '');
        } else {
            value = '';
        }
        $g('#countdown-settings-dialog [data-option="icon"][data-group="icon"]').val(value);
        $g('#countdown-settings-dialog .button-icon-position input[type="hidden"]').val(app.edit.icon.position);
        value = $g('#countdown-settings-dialog .button-icon-position li[data-value="'+app.edit.icon.position+'"]').text();
        $g('#countdown-settings-dialog .button-icon-position input[readonly]').val(value.trim());
        app.setTypography($g('#countdown-settings-dialog .button-options .typography-options'), 'typography');
    } else if (app.edit.type == 'cart') {
        value = app.getValue('icons', 'size');
        $g('#countdown-settings-dialog [data-option="size"][data-group="icons"]').val(value);
        var range = $g('#countdown-settings-dialog [data-option="size"][data-group="icons"]').prev();
        range.val(value);
        setLinearWidth(range);
        value = app.editor.document.getElementById(app.editor.app.edit);
        value = value.querySelector('.ba-button-wrapper a i');
        if (value) {
            value = value.className.replace('zmdi zmdi-', '').replace('fa fa-', '');
        } else {
            value = '';
        }
        $g('#countdown-settings-dialog [data-option="icon"][data-group="icon"]').val(value);
        $g('#countdown-settings-dialog .button-icon-position input[type="hidden"]').val(app.edit.icon.position);
        value = $g('#countdown-settings-dialog .button-icon-position li[data-value="'+app.edit.icon.position+'"]').text();
        $g('#countdown-settings-dialog .button-icon-position input[readonly]').val(value.trim());
        app.setTypography($g('#countdown-settings-dialog .button-options .typography-options'), 'typography');
    } else if (app.edit.type == 'post-tags' || app.edit.type == 'tags') {
        app.setTypography($g('#countdown-settings-dialog .typography-options'), 'typography');
    } else if (app.edit.type == 'counter') {
        app.setTypography($g('#countdown-settings-dialog .typography-options'), 'counter');
        value = app.getValue('background', 'color');
        updateInput($g('#countdown-settings-dialog .background input[data-option="color"]'), value);
        $g('#countdown-settings-dialog .counter-general input[data-option="number"]').val(app.edit.counter.number);
        $g('#countdown-settings-dialog .counter-general input[data-option="speed"]').val(app.edit.counter.speed);
    } else if (app.edit.type == 'scroll-to-top' || app.edit.type == 'scroll-to') {
        if (app.edit.type == 'scroll-to-top') {
            value = app.edit.text.align;
            $g('#countdown-settings-dialog [data-option="align"][data-group="text"][data-value="'+value+'"]').addClass('active');
            value = app.getValue('icons', 'size');
            $g('#countdown-settings-dialog .scrolltop-options [data-option="size"]').val(value);
            var range = $g('#countdown-settings-dialog .scrolltop-options [data-option="size"]').prev().val(value);
            setLinearWidth(range);
            value = app.edit.icon.replace('zmdi zmdi-', '').replace('fa fa-', '');
            $g('#countdown-settings-dialog .scrolltop-icon').val(value);
        } else {
            value = app.editor.document.querySelector(app.selector+' .ba-button-wrapper a span').textContent;
            $g('#countdown-settings-dialog input.button-label').val(value);
            value = app.getValue('icons', 'size');
            $g('#countdown-settings-dialog [data-option="size"][data-group="icons"]').val(value);
            var range = $g('#countdown-settings-dialog [data-option="size"][data-group="icons"]').prev().val(value);
            setLinearWidth(range);
            if (app.edit.type != 'scroll-to') {
                value = app.editor.document.getElementById(app.editor.app.edit);
                value = value.querySelector('.ba-button-wrapper a i');
                if (value) {
                    value = value.className.replace('zmdi zmdi-', '').replace('fa fa-', '');
                } else {
                    value = '';
                }
                $g('#countdown-settings-dialog [data-option="icon"][data-group="icon"]').val(value);
            }
            value = app.getValue('icons', 'position');
            $g('#countdown-settings-dialog .scroll-to-icon-position input[type="hidden"]').val(value);
            value = $g('#countdown-settings-dialog .scroll-to-icon-position li[data-value="'+value+'"]').text().trim();
            $g('#countdown-settings-dialog .scroll-to-icon-position input[readonly]').val(value);
            app.setTypography($g('#countdown-settings-dialog .typography-options'), 'typography');
            value = app.edit.icon.replace('zmdi zmdi-', '').replace('fa fa-', '');
            $g('#countdown-settings-dialog .scroll-to-icon').val(value);
        }
        $g('#countdown-settings-dialog .scrolltop-general input[data-option]').each(function(){
            this.value = app.edit.init[this.dataset.option];
        });
        $g('#countdown-settings-dialog .select-end-point').val(app.edit.init.target);
    }
    if (app.edit.type == 'tags') {
        app.recentPostsCallback = 'getBlogTags';
        $g('#countdown-settings-dialog .tags-app-select input[type="hidden"]').val(app.edit.app);
        value = $g('#countdown-settings-dialog .tags-app-select li[data-value="'+app.edit.app+'"]').text();
        $g('#countdown-settings-dialog .tags-app-select input[readonly]').val($g.trim(value));
        if (value) {
            $g('#countdown-settings-dialog .tags-categories-list').css('display', '');
        } else {
            $g('#countdown-settings-dialog .tags-categories-list').hide();
        }
        $g('#countdown-settings-dialog input[data-option="count"]').val(app.edit.count);
        $g('.selected-categories li:not(.search-category)').remove();
        $g('.all-categories-list .selected-category').removeClass('selected-category');
        for (var key in app.edit.categories) {
            var str = getCategoryHtml(key, app.edit.categories[key].title);
            $g('#countdown-settings-dialog .selected-categories li.search-category').before(str);
            $g('#countdown-settings-dialog .all-categories-list [data-id="'+key+'"]').addClass('selected-category');
        }
        if ($g('.selected-categories li:not(.search-category)').length > 0) {
            $g('.ba-settings-item.tags-categories-list').addClass('not-empty-list');
        } else {
            $g('.ba-settings-item.tags-categories-list').removeClass('not-empty-list');
        }
        $g('.tags-categories .all-categories-list li').hide();
    }
    $g('#countdown-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#countdown-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#countdown-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#countdown-settings-dialog .class-suffix').val(app.edit.suffix);
    if (app.edit.type != 'scroll-to-top') {
        value = app.getValue('margin', 'top');
        $g('#countdown-settings-dialog [data-group="margin"][data-option="top"]').val(value);
        value = app.getValue('margin', 'bottom');
        $g('#countdown-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    }
    for (var key in app.edit.desktop.border) {
        var input = $g('#countdown-settings-dialog input[data-option="'+key+'"][data-group="border"]');
        value = app.getValue('border', key);
        switch (key) {
            case 'color' :
                updateInput(input, value);
                break;
            case 'style' :
                input.val(value);
                var select = input.closest('.ba-custom-select');
                value = select.find('li[data-value="'+value+'"]').text();
                select.find('input[readonly]').val($g.trim(value));
                break;
            default:
                input.val(value);
                var range = input.prev();
                range.val(value);
                setLinearWidth(range);
        }
    }
    setDisableState('#countdown-settings-dialog');
    $g('#countdown-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#countdown-settings-dialog').modal();
    }, 150);
}

$g('.overlay-button-trigger-select').on('customAction', function(){
    app.edit.trigger = this.querySelector('input[type="hidden"]').value;
    app.editor.setOverlaySectionTrigger(app.edit, app.edit.trigger);
    $g('#countdown-design-options .ba-settings-group').css('display', '');
    app.editor.$g(app.selector+' > .ba-image-wrapper').remove();
    if (app.edit.trigger == 'button') {
        $g('.button-label').closest('.button-options').css('display', '');
        $g('#countdown-settings-dialog .padding-options').css('display', '');
        $g('.overlay-image-options').hide();
    } else {
        $g('.button-label').closest('.button-options').hide();
        $g('#countdown-settings-dialog .padding-options').hide();
        $g('#countdown-design-options .slideshow-margin-options.overlay-button-options').nextAll().hide();
        $g('.overlay-image-options').css('display', '');
        var wrapper = document.createElement('div'),
            img = document.createElement('img');
        wrapper.className = 'ba-image-wrapper '+app.edit.desktop.animation.effect;
        img.src = JUri+app.edit.image;
        img.alt = JUri+app.edit.alt;
        wrapper.appendChild(img);
        var str = '<div class="ba-image-item-caption"><div class="ba-caption-overlay"></div>'+
            '<'+app.edit.tag+' class="ba-image-item-title"></'+app.edit.tag+
            '><div class="ba-image-item-description"></div></div>';
        $g(wrapper).append(str);
        wrapper.querySelector('.ba-image-item-title').textContent = app.edit.caption.title;
        wrapper.querySelector('.ba-image-item-description').innerHTML = app.edit.caption.description;
        app.editor.$g(app.selector+' > .ba-button-wrapper').before(wrapper);
    }
    app.sectionRules();
    app.addHistory();
});

$g('.overlay-image-alt').on('input', function(){
    app.edit.image.alt = this.value;
    app.editor.$g(app.selector+' > .ba-image-wrapper img').attr('alt', app.edit.image.alt);
});

$g('#countdown-settings-dialog input[data-option="count"]').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.count = $this.value;
        getBlogTags();
    });
});

$g('.tags-app-select').on('customAction', function(){
    var id = this.querySelector('input[type="hidden"]').value;
    if (id != app.edit.app) {
        app.edit.categories = {};
        app.edit.app = id;
        $g('.selected-categories li:not(.search-category)').remove();
        $g('.all-categories-list .selected-category').removeClass('selected-category');
        $g('.ba-settings-item.tags-categories-list').removeClass('not-empty-list');
        $g('#countdown-settings-dialog .tags-categories-list').css('display', '');
        getBlogTags();
    }
});

function getBlogTags()
{
    var category = new Array();
    for (var key in app.edit.categories) {
        category.push(key);
    }
    category = category.join(',');
    app.editor.$g(app.selector).attr('data-app', app.edit.app).attr('data-category', category)
        .attr('data-limit', app.edit.count);
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getBlogTags&tmpl=component",
        data: {
            category : category,
            limit : app.edit.count,
            id : app.edit.app
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .ba-button-wrapper').innerHTML = msg.responseText;
            app.editor.app.buttonsPrevent();
            app.addHistory();
        }
    });
}

$g('#countdown-settings-dialog .select-end-point').on('click', function(){
    app.editor.app.checkModule('setEndPoint');
    fontBtn = this;
});

$g('#countdown-settings-dialog .icon-options [data-option="inline"]').on('change', function(){
    app.setValue(this.checked, 'inline');
    app.sectionRules();
    app.addHistory();
});

$g('#countdown-settings-dialog .scrolltop-general input[data-option]').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.init[$this.dataset.option] = $this.value;
        app.editor.app['init'+app.edit.type](app.edit, app.editor.app.edit);
        app.addHistory();
    }, 300);
});

$g('.end-point-cover').on('mousedown', function(event){
    event.stopPropagation();
});

$g('.scrolltop-animation-select').on('customAction', function(){
    $g(this).find('input[type="hidden"]').trigger('input');
});

$g('.constants.countdown-options [data-option]').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit[$this.dataset.option] = $this.value;
        var element = app.editor.document.getElementById(app.editor.app.edit);
        element = element.querySelector('.'+$this.dataset.option+' .countdown-label');
        element.innerText = $this.value;
        app.addHistory();
    }, 300);
});

$g('#countdown-general-options [data-option="hide-after"]').on('change', function(){
    app.edit['hide-after'] = this.checked;
    app.addHistory();
});

$g('.countdown-display-select').on('customAction', function(){
    app.edit.display = $g(this).find('input[type="hidden"]').val();
    app.editor.app.initcountdown(app.edit, app.editor.app.edit);
    app.addHistory();
});

$g('#countdown-input').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.date = $this.value;
        app.editor.app.initcountdown(app.edit, app.editor.app.edit);
        app.addHistory();
    }, 300);
});

$g('#countdown-settings-dialog .counter-general input[data-option]').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.edit.counter[$this.dataset.option] = $this.value;
        app.editor.app.initcounter(app.edit, app.editor.app.edit);
        app.addHistory();
    }, 300);
})

$g('#countdown-settings-dialog .button-label').on('input', function(){
    var $this = this;
    clearTimeout(delay);
    delay = setTimeout(function(){
        var span = app.editor.document.getElementById(app.editor.app.edit);
        span = span.querySelector('a span');
        span.innerText = $this.value;
        if (!$g.trim($this.value)) {
            span.classList.add('empty-textnode');
        } else {
            span.classList.remove('empty-textnode');
        }
        app.addHistory();
    });
});

$g('#countdown-settings-dialog input[data-option="icon"][data-group="icon"]').on('click', function(){
    uploadMode = 'addButtonIcon';
    checkIframe($g('#icon-upload-dialog'), 'icons');
    fontBtn = this;
});

$g('#countdown-settings-dialog .reselect-icon').on('click', function(){
    uploadMode = 'reselectIcon';
    checkIframe($g('#icon-upload-dialog'), 'icons');
    fontBtn = this;
});

$g('#countdown-settings-dialog .scrolltop-icon').on('click', function(){
    uploadMode = 'scrolltopIcon';
    checkIframe($g('#icon-upload-dialog'), 'icons');
    fontBtn = this;
});

$g('#countdown-settings-dialog .scroll-to-icon').on('click', function(){
    uploadMode = 'smoothScrollingIcon';
    checkIframe($g('#icon-upload-dialog'), 'icons');
    fontBtn = this;
});

function removeIcon()
{
    var i = app.editor.document.getElementById(app.editor.app.edit);
    i = i.querySelector('a i');
    if (i) {
        i.parentNode.removeChild(i);
    }
}

function updateCountdown()
{
    $g('#countdown-input').trigger('input');
}

var funct = function(){
    setupCalendar('countdown-input', 'countdown-calendar', "%Y-%m-%d %H:%M:%S", updateCountdown)
}

if (!app.modules.calendar) {
    if (!app.actionStack['calendar']) {
        app.actionStack['calendar'] = new Array();
    }
    app.actionStack['calendar'].push(funct);
    app.loadModule('calendar');
} else if (app.modules.calendar) {
    funct();
}

app.modules.countdownEditor = true;
app.countdownEditor();