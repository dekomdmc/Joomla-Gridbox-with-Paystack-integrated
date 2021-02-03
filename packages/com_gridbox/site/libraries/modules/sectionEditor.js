/**
* @package   gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

$g('#section-settings-dialog input[data-option="max-width"]').on('change', function(){
    if (!this.checked) {
        $g('.megamenu-width').css('display', '');
    } else {
        $g('.megamenu-width').hide();
    }
    app.edit.desktop.full.fullwidth = this.checked;
    app.sectionRules();
    app.editor.$g(app.selector).closest('li').trigger('mouseenter');
    app.addHistory();
});

$g('#section-settings-dialog input[data-option="fullscreen"]').on('change', function(){
    app.setValue(this.checked, 'full', 'fullscreen');
    app.sectionRules();
    app.addHistory();
});

$g('[data-group="parallax"][type="checkbox"]').on('change', function(){
    app.edit.parallax[this.dataset.option] = this.checked;
    app.editor.app.loadParallax();
    app.addHistory();
    if (this.dataset.option == 'enable') {
        if (this.checked) {
            $g('.parallax-options').css('display', '').addClass('ba-active-options');
            setTimeout(function(){
                $g('.parallax-options').removeClass('ba-active-options');
            }, 1);
        } else {
            $g('.parallax-options').css('display', 'none');
        }
    } else if (this.dataset.option == 'invert') {
        app.sectionRules();
    }
});

$g('.parallax-type-select').on('customAction', function(){
    app.edit.parallax.type = this.querySelector('input[type="hidden"]').value;
    app.editor.app.loadParallax();
    app.addHistory();
});

$g('.effect-select').on('customAction', function(){
    var id = app.editor.app.edit,
        val = $g(this).find('input[type="hidden"]').val(),
        effect = app.getValue('animation', 'effect'),
        duration = app.getValue('animation', 'duration') * 1000,
        delay = app.getValue('animation', 'delay') * 1000,
        item = app.editor.document.getElementById(id);
    if (effect) {
        item.classList.remove(effect);
        item.classList.remove('visible');
        clearTimeout(delay);
    }
    if (val) {
        item.classList.add('visible');
        item.classList.add(val);
        delay = setTimeout(function(){
            if ($g(item).closest('.ba-item-content-slider').length == 0) {
                item.classList.remove(val);
                app.addHistory();
            }
        }, duration + delay);
    }
    app.setValue(val, 'animation', 'effect');
    app.sectionRules();
});

$g('.flipbox-select-side').on('customAction', function(){
    app.edit.side = this.querySelector('input[type="hidden"]').value;
    app.editor.setFlipboxSide(app.edit, app.edit.side);
    setSectionBackgroundOptions();
    app.editor.$g(app.selector).addClass('flipbox-animation-started');
    if (app.edit.side == 'frontside') {
        app.editor.$g(app.selector).removeClass('backside-fliped');
    } else {
        app.editor.$g(app.selector).addClass('backside-fliped');
    }
    var duration = app.getValue('animation', 'duration');
    setTimeout(function(){
        app.editor.$g(app.selector).removeClass('flipbox-animation-started');
    }, duration * 1000);
});

$g('.flipbox-effect-select').on('customAction', function(){
    var value = this.querySelector('input[type="hidden"]').value,
        item = app.editor.$g(app.selector),
        match = value.match(/\w+-flip/g),
        duration = app.getValue('animation', 'duration');
    item.addClass(match[0]);
    app.editor.$g(app.selector+' > .ba-flipbox-wrapper').removeClass(app.edit.desktop.animation.effect);
    setTimeout(function(){
        app.editor.$g(app.selector+' > .ba-flipbox-wrapper').addClass(value);
        app.edit.desktop.animation.effect = value;
        setTimeout(function(){
            item.addClass('flipbox-animation-started backside-fliped');
            setTimeout(function(){
                item.removeClass('backside-fliped');
                item.removeClass(match[0]);
                app.addHistory();
            }, duration * 1000);
            setTimeout(function(){
                item.removeClass('flipbox-animation-started');
            }, duration * 2000)
        }, 50);
    }, 50);
});

function setSectionBackgroundOptions()
{
    value = app.getValue('background', 'effect', 'gradient');
    $g('#section-settings-dialog .background-linear-gradient').hide();
    $g('#section-settings-dialog .background-'+value+'-gradient').css('display', '');
    $g('#section-settings-dialog .gradient-options .gradient-effect-select input[type="hidden"]').val(value);
    value = $g('#section-settings-dialog .gradient-options .gradient-effect-select li[data-value="'+value+'"]').text().trim();
    $g('#section-settings-dialog .gradient-options .gradient-effect-select input[type="text"]').val(value);
    value = app.getValue('overlay', 'effect', 'gradient');
    $g('#section-settings-dialog .overlay-linear-gradient').hide();
    $g('#section-settings-dialog .overlay-'+value+'-gradient').css('display', '');
    $g('#section-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="hidden"]').val(value);
    value = $g('#section-settings-dialog .overlay-gradient-options .gradient-effect-select li[data-value="'+value+'"]').text().trim();
    $g('#section-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="text"]').val(value);
    $g('#section-settings-dialog input[data-subgroup="gradient"][data-group="background"]').each(function(){
        value = app.getValue('background', this.dataset.option, 'gradient');
        if (this.type == 'number') {
            var range = $g(this).val(value).prev().val(value);
            setLinearWidth(range);
        } else {
            updateInput($g(this), value);
        }
    });
    value = app.getValue('overlay', 'type');
    $g('#section-settings-dialog .overlay-color-options, .overlay-gradient-options').hide();
    $g('#section-settings-dialog .overlay-'+value+'-options').css('display', '');
    $g('#section-settings-dialog .background-overlay-select input[type="hidden"]').val(value);
    value = $g('#section-settings-dialog .background-overlay-select li[data-value="'+value+'"]').text().trim();
    $g('#section-settings-dialog .background-overlay-select input[type="text"]').val(value);
    $g('#section-settings-dialog input[data-subgroup="gradient"][data-group="overlay"]').each(function(){
        value = app.getValue('overlay', this.dataset.option, 'gradient');
        if (this.type == 'number') {
            var range = $g(this).val(value).prev().val(value);
            setLinearWidth(range);
        } else {
            updateInput($g(this), value);
        }
    });
    value = app.getValue('background', 'color');
    updateInput($g('#section-background-options input[data-option="color"][data-group="background"]'), value);
    value = app.getValue('overlay', 'color');
    updateInput($g('#section-background-options input[data-option="color"][data-group="overlay"]'), value);
    value = app.getValue('image', 'image');
    $g('#section-background-options input[data-option="image"]').val(value);
    value = app.getValue('background', 'attachment', 'image');
    $g('#section-background-options [data-option="attachment"]').val(value);
    value = $g('#section-background-options .attachment li[data-value="'+value+'"]').text();
    $g('#section-background-options .attachment input[readonly]').val($g.trim(value));
    value = app.getValue('background', 'size', 'image');
    if (value == 'contain' || value == 'initial') {
        $g('#section-background-options .contain-size-options').show().addClass('ba-active-options');
        setTimeout(function(){
            $g('#section-background-options .contain-size-options').removeClass('ba-active-options');
        }, 1);
    } else {
        $g('#section-background-options .contain-size-options').hide();
    }
    $g('#section-background-options .backround-size input[type="hidden"]').val(value);
    value = $g('#section-background-options .backround-size li[data-value="'+value+'"]').text();
    $g('#section-background-options .backround-size input[readonly]').val($g.trim(value));
    value = app.getValue('background', 'position', 'image');
    $g('#section-background-options [data-option="position"]').val(value);
    name = $g('#section-background-options .backround-position li[data-value="'+value+'"]').text();
    $g('#section-background-options .backround-position input[readonly]').val($g.trim(name));
    value = app.getValue('background', 'repeat', 'image');
    $g('#section-background-options [data-option="repeat"]').val(value);
    name = $g('#section-background-options .backround-repeat li[data-value="'+value+'"]').text();
    $g('#section-background-options .backround-repeat input[readonly]').val($g.trim(name));
    $g('#section-settings-dialog .video-select [data-option="video-type"]').val(app.edit.desktop.video.type);
    value = $g('#section-settings-dialog .video-select li[data-value="'+app.edit.desktop.video.type+'"]').text();
    $g('#section-settings-dialog .video-select input[readonly]').val($g.trim(value));
    $g('#section-settings-dialog .video-select').trigger('customAction');
    $g('#section-background-options [data-option="id"]').val(app.edit.desktop.video.id);
    if (!app.edit.desktop.video.source) {
        app.edit.desktop.video.source = '';
    }
    $g('#section-background-options [data-option="source"]').val(app.edit.desktop.video.source);
    $g('#section-background-options [data-option="start"]').val(app.edit.desktop.video.start);
    if (app.edit.desktop.video.mute == 1) {
        $g('#section-background-options [data-option="mute"]').prop('checked', true);
    }
    $g('#section-settings-dialog .video-quality [data-option="quality"]').val(app.edit.desktop.video.quality);
    value = $g('#section-settings-dialog .video-quality li[data-value="'+app.edit.desktop.video.quality+'"]').text();
    $g('#section-settings-dialog .video-quality [readonly]').val($g.trim(value));
    value = app.getValue('background', 'type');
    $g('#section-settings-dialog .background-options').find('> div').hide();
    $g('.'+value+'-options').css('display', '');
    $g('#section-settings-dialog .background-select input[type="hidden"]').val(value);
    value = $g('#section-settings-dialog .background-select li[data-value="'+value+'"]').text().trim();
    $g('#section-settings-dialog .background-select input[readonly]').val(value);
    $g('[data-group="parallax"]').each(function(){
        if (this.type == 'checkbox') {
            this.checked = app.edit.parallax[this.dataset.option];
        } else {
            this.value = app.edit.parallax[this.dataset.option];
            var range = $g(this).prev().val(app.edit.parallax[this.dataset.option]);
            setLinearWidth(range);
        }
    });
    if (!app.edit.parallax.type) {
        app.edit.parallax.type = 'mousemove';
    }
    $g('#section-settings-dialog .parallax-type-select input[type="hidden"]').val(app.edit.parallax.type);
    value = $g('#section-settings-dialog .parallax-type-select li[data-value="'+app.edit.parallax.type+'"]').text();
    $g('#section-settings-dialog .parallax-type-select input[readonly]').val($g.trim(value));
    if (app.edit.parallax.enable) {
        $g('.parallax-options').css('display', '');
    } else {
        $g('.parallax-options').css('display', 'none');
    }
}

$g('input[data-option="effect3D"]').on('change', function(){
    app.edit.effect3D = this.checked;
    if (this.checked) {
        app.editor.$g(app.selector+' > .ba-flipbox-wrapper').addClass('flipbox-3d-effect');
    } else {
        app.editor.$g(app.selector+' > .ba-flipbox-wrapper').removeClass('flipbox-3d-effect');
    }
    app.addHistory();
});

app.sectionEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    var value = '',
        flipboxEffect = $g('.flipbox-effect-select').closest('.flipbox-options');
    $g('#section-settings-dialog .active').removeClass('active');
    $g('#section-settings-dialog a[href="#section-general-options"]').parent().addClass('active');
    $g('#section-general-options').addClass('active');
    if (app.edit.type == 'flipbox') {
        value = app.getValue('animation', 'duration');
        app.edit.side = 'frontside';
        app.editor.setFlipboxSide(app.edit, app.edit.side);
        app.editor.$g(app.selector).addClass('flipbox-animation-started').removeClass('backside-fliped');
        setTimeout(function(){
            app.editor.$g(app.selector).removeClass('flipbox-animation-started');
        }, value * 1000);
        $g('.flipbox-select-side input[type="hidden"]').val(app.edit.side);
        $g('.flipbox-select-side input[type="text"]').val(gridboxLanguage[app.edit.side.toUpperCase()]);
        $g('.flipbox-options').css('display', '');
        flipboxEffect.find('input[type="hidden"]').val(app.edit.desktop.animation.effect);
        value = flipboxEffect.find('li[data-value="'+app.edit.desktop.animation.effect+'"]').text().trim();
        flipboxEffect.find('input[type="text"]').val(value);
        flipboxEffect.next().hide().nextAll().last().hide();
        value = app.getValue('view', 'height');
        var range = $g('.flipbox-options input[data-option="height"]').val(value).prev().val(value);
        setLinearWidth(range);
        $g('#section-settings-dialog input[data-option="effect3D"]').prop('checked', app.edit.effect3D);
        $g('#section-settings-dialog .full-width').next().hide();
        $g('#section-settings-dialog input[data-option="enable"][data-group="parallax"]').closest('.ba-settings-item').hide();
    } else {
        $g('.flipbox-options').hide();
        flipboxEffect.next().css('display', '').nextAll().last().css('display', '');
        $g('#section-settings-dialog .full-width').next().css('display', '');
        $g('#section-settings-dialog input[data-option="enable"][data-group="parallax"]').closest('.ba-settings-item').css('display', '');
    }
    if (app.edit.type == 'column') {
        if (!app.edit.link) {
            app.edit.link = {
                "link" : "",
                "target" : "_self",
                "type": ""
            }
            app.edit.embed = '';
        }
        $g('#section-settings-dialog [data-option="link"]').val(app.edit.link.link);
        $g('#section-settings-dialog .link-target-select input[type="hidden"]').val(app.edit.link.target);
        value = $g('#section-settings-dialog .link-target-select li[data-value="'+app.edit.link.target+'"]').text();
        $g('#section-settings-dialog .link-target-select input[readonly]').val($g.trim(value));
        $g('#section-settings-dialog .link-type-select input[type="hidden"]').val(app.edit.link.type);
        value = $g('#section-settings-dialog .link-type-select li[data-value="'+app.edit.link.type+'"]').text().trim();
        $g('#section-settings-dialog .link-type-select input[readonly]').val(value);
        $g('#section-settings-dialog .button-embed-code').val(app.edit.embed);
        $g('#section-settings-dialog .ba-column-options').css('display', '');
    } else {
        $g('#section-settings-dialog .ba-column-options').hide();
    }
    if (!app.edit.desktop.video) {
        app.edit.desktop.video = $g.extend(true, {}, app.edit.desktop.background.video);
    }
    if (!app.edit.desktop.background.gradient) {
        app.edit.desktop.background.gradient = {
            "effect": "linear",
            "angle": 45,
            "color1": "@bg-dark",
            "position1": 25,
            "color2": "@bg-dark-accent",
            "position2": 75
        }
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
    setSectionBackgroundOptions();
    if (app.edit.type == 'section' || app.edit.type == 'row' || app.edit.type == 'column' || app.edit.type == 'flipbox') {
        setPresetsList($g('#section-settings-dialog'));
        $g('#section-settings-dialog .presets-options').css('display', '');
    } else {
        $g('#section-settings-dialog .presets-options').hide();
    }
    if (app.edit.type == 'column') {
        $g('#section-general-options .full-width').hide();
        value = app.getValue('span', 'width');
        if (!value && app.view != 'desktop' && !app.edit[app.view].span) {
            app.edit[app.view].span = {};
        }
        if (!value && app.editor.$g(app.selector).closest('header').length == 0) {
            value = 12;
        } else if (!value) {
            value = app.editor.document.querySelector(app.selector).parentNode.dataset.span;
        }
        var range = $g('.mobile-column-width input[data-option="width"][data-group="span"]').val(value).prev().val(value);
        setLinearWidth(range);
        value = app.getValue('span', 'order');
        if (!value) {
            value = 1;
        }
        range = $g('.mobile-column-width input[data-option="order"][data-group="span"]').val(value).prev().val(value);
        setLinearWidth(range);
        if (!app.edit.sticky) {
            app.edit.sticky = {
                enable: false,
                offset: 0
            }
        }
        $g('.sticky-column-option').css('display', '').each(function(){
            let $this = $g(this);
            $this.find('input[type="checkbox"]').prop('checked', app.edit.sticky.enable);
            range = $this.find('input[data-option]').val(app.edit.sticky.offset).prev().val(app.edit.sticky.offset);
            setLinearWidth(range);
            if (!app.edit.sticky.enable) {
                range.closest('.sticky-column-option').hide();
            }
        });
    } else {
        value = app.edit.desktop.full.fullwidth;
        $g('#section-general-options .full-width')[0].style.display = '';
        if (app.edit.desktop.full.fullwidth) {
            $g('.megamenu-width').hide();
        } else {
            $g('.megamenu-width').css('display', '');
        }
        $g('#section-settings-dialog input[data-option="max-width"]').prop('checked', app.edit.desktop.full.fullwidth);
        $g('.sticky-column-option').hide();
    }
    if (app.edit.type == 'overlay-section') {
        if (app.edit.lightbox.layout.match('vertical-')) {
            $g('.ba-settings-item.full-width').css('display', '').next().css('display', 'none');
        } else if (app.edit.lightbox.layout.match('horizontal-')) {
            $g('.ba-settings-item.full-width').css('display', 'none').next().css('display', '');
        } else {
            $g('.ba-settings-item.full-width').css('display', '').next().css('display', '');
        }
    }
    if (app.edit.type == 'footer') {
        value = $g('#section-settings-dialog .typography-select input[type="hidden"]').val();
        app.setTypography($g('#section-settings-dialog .typography-options'), value);
    }
    if (app.edit.type == 'header') {
        if (!app.edit.layout) {
            $g('.full-group').removeAttr('style');
        } else {
            $g('.full-group').hide();
        }
        if (app.edit.layout == "sidebar-menu" && (app.view == 'desktop' || app.view == 'laptop')) {
            $g('#section-settings-dialog .header-position').hide();
            $g('#section-settings-dialog .header-sidebar-width').css('display', '');
        } else {
            $g('#section-settings-dialog .header-position').css('display', '');
            $g('#section-settings-dialog .header-sidebar-width').hide();
        }
        if (!app.edit.desktop.width) {
            app.edit.desktop.width = 250;
        }
        value = app.getValue('width');
        var range = $g('#section-settings-dialog .header-sidebar-width input[data-option="width"]').val(value).prev().val(value);
        setLinearWidth(range);
        $g('#section-settings-dialog .header-layout-select input[type="hidden"]').val(app.edit.layout);
        value = $g('#section-settings-dialog .header-layout-select li[data-value="'+app.edit.layout+'"]').text();
        $g('#section-settings-dialog .header-layout-select input[readonly]').val($g.trim(value));
        value = app.getValue('position');
        $g('#section-settings-dialog .header-position-select input[type="hidden"]').val(value);
        value = $g('#section-settings-dialog .header-position-select li[data-value="'+value+'"]').text();
        $g('#section-settings-dialog .header-position-select input[readonly]').val($g.trim(value));
    } else {
        $g('.full-group').removeAttr('style');
    }
    if (app.edit.type == 'row') {
        value = app.getValue('view', 'gutter');
        $g('.column-gutter [data-option="gutter"]').prop('checked', value);
        $g('.column-gutter').css('display', '');
    } else {
        $g('.column-gutter').hide();
    }
    if (app.edit.type == 'mega-menu-section') {
        value = $g('#section-settings-dialog .image-width input[data-option="width"]')
            .val(app.edit.view.width).prev().val(app.edit.view.width);
        setLinearWidth(value);
        $g('#section-settings-dialog .megamenu-position-select input[type="hidden"]').val(app.edit.view.position);
        value = $g('#section-settings-dialog .megamenu-position-select li[data-value="'+app.edit.view.position+'"]').text().trim();
        $g('#section-settings-dialog .megamenu-position-select input[type="text"]').val(value);
    }
    value = app.getValue('full', 'fullscreen');
    $g('#section-settings-dialog input[data-option="fullscreen"]').prop('checked', value);
    $g('#section-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#section-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#section-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#section-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#section-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#section-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'top');
    $g('#section-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#section-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#section-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#section-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    for (var key in app.edit.desktop.border) {
        var input = $g('#section-settings-dialog input[data-option="'+key+'"][data-group="border"]');
        value = app.getValue('border', key);
        switch (key) {
            case 'color' :
                updateInput(input, value);
                break;
            case 'width' :
            case 'radius' :
                input.val(value);
                var range = input.prev();
                range.val(value);
                setLinearWidth(range);
                break;
            case 'style' :
                input.val(value);
                var select = input.closest('.ba-custom-select');
                value = select.find('li[data-value="'+value+'"]').text();
                select.find('input[readonly]').val($g.trim(value));
                break;
            default:
                if (value == 1) {
                    input.prop('checked', true);
                } else {
                    input.prop('checked', false);
                }
        }
    }
    setDisableState('#section-settings-dialog');
    if (typeof(app.edit.desktop.animation.delay) == 'undefined') {
        app.edit.desktop.animation.delay = 0;
    }
    for (var key in app.edit.desktop.animation) {
        value = app.getValue('animation', key);
        var input = $g('#section-settings-dialog input[data-option="'+key+'"][data-group="animation"]');
        switch (key) {
            case 'effect' :
                input.val(value);
                var select = input.closest('.ba-custom-select');
                value = select.find('li[data-value="'+value+'"]').text();
                select.find('input[readonly]').val($g.trim(value));
                break;
            default :
                input.val(value);
                var range = input.prev();
                range.val(value);
                setLinearWidth(range);
        }
    }
    $g('#section-settings-dialog').attr('data-edit', app.edit.type);
    if (app.edit.desktop.shadow) {
        value = app.getValue('shadow', 'value');
        value = $g('#section-settings-dialog input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
        setLinearWidth(value);
        value = app.getValue('shadow', 'color');
        updateInput($g('#section-settings-dialog input[data-option="color"][data-group="shadow"]'), value);
    }
    $g('.shape-divider-options').hide();
    if (app.edit.type != 'column' && app.edit.type != 'flipbox') {
        $g('.shape-divider-options').css('display', '');
        if (!app.edit.desktop.shape) {
            app.edit.desktop.shape = {
                top : {
                    effect : '',
                    color : '@primary',
                    value : '50'
                },
                bottom : {
                    effect : '',
                    color : '@primary',
                    value : '50'
                }
            }
        }
        $g('.shape-divider-position input[type="hidden"]').val('bottom');
        value = $g('.shape-divider-position li[data-value="bottom"]').text().trim();
        $g('.shape-divider-position input[type="text"]').val(value);
        setDividerPosition('bottom');
    }
    if (app.edit.type == 'sticky-header') {
        $g('.sticky-header-options').css('display', '');
        value = app.getValue('offset');
        $g('.sticky-header-options input[data-option="offset"]').val(value);
        $g('.sticky-header-options input[data-option="scrollup"]').prop('checked', app.edit.scrollup);
    } else {
        $g('.sticky-header-options').hide();
    }
    if (app.edit.type == 'cookies') {
        $g('#section-settings-dialog .cookies-options').css('display', '');
        $g('#section-settings-dialog').find('.full-group').hide().next().next().hide();
        $g('.cookies-layout-select input[type="hidden"]').val(app.edit.lightbox.layout);
        value = $g('.cookies-layout-select li[data-value="'+app.edit.lightbox.layout+'"]').text().trim();
        $g('.cookies-layout-select input[type="text"]').val(value);
        value = app.getValue('view', 'width');
        var input = $g('#section-settings-dialog .cookies-options .width-options input[data-option="width"]');
        input.val(value);
        var range = input.prev();
        range.val(value);
        setLinearWidth(range);
        setCookiesPosition();
    } else {
        $g('#section-settings-dialog .cookies-options').hide();
        $g('#section-settings-dialog').find('.full-group').css('display', '').next().next().next().css('display', '');
    }
    if (app.edit.type == 'column' || app.edit.type == 'flipbox') {
        if (typeof(app.edit.content_align) == 'undefined') {
            app.edit.content_align = '';
        }
        $g('#section-settings-dialog .column-content-align input[type="hidden"]').val(app.edit.content_align);
        value = $g('#section-settings-dialog .column-content-align li[data-value="'+app.edit.content_align+'"]').text().trim();
        $g('#section-settings-dialog .column-content-align input[type="text"]').val(value);
        let display = (!app.edit.sticky || !app.edit.sticky.enable) ? '' : 'none'
        $g('#section-settings-dialog .full-group .column-content-align').css('display', display);
    } else {
        $g('#section-settings-dialog .full-group .column-content-align').hide();
    }
    setTimeout(function(){
        $g('#section-settings-dialog').modal();
    }, 150);
}

function setCookiesPosition()
{
    $g('.cookies-position-select input[type="hidden"]').val(app.edit.lightbox.position);
    var value = $g('.cookies-position-select li[data-value="'+app.edit.lightbox.position+'"]').text().trim();
    $g('.cookies-position-select input[type="text"]').val(value);
    $g('.cookies-position-select li').hide();
    $g('.cookies-position-select li[data-value*="'+app.edit.lightbox.layout+'"]').css('display', '');
    if (app.edit.lightbox.layout == 'lightbox') {
        $g('#section-settings-dialog .cookies-options .width-options').css('display', '');
    } else {
        $g('#section-settings-dialog .cookies-options .width-options').hide();
    }
}

function setDividerPosition(type)
{
    var parent = $g('.shape-divider-position-options');
    parent.addClass('ba-active-options');
    setTimeout(function(){
        parent.removeClass('ba-active-options');
    }, 1);
    parent.find('input[data-group="shape"]').attr('data-subgroup', type);
    $g('.shape-divider-effect input[type="hidden"]').attr('data-subgroup', type);
    value = app.getValue('shape', 'effect', type);
    $g('.shape-divider-effect input[type="hidden"]').val(value);
    value = $g('.shape-divider-effect li[data-value="'+value+'"]').text().trim();
    $g('.shape-divider-effect input[type="text"]').val(value);
    value = app.getValue('shape', 'color', type);
    updateInput($g('#section-background-options input[data-option="color"][data-group="shape"]'), value);
    value = app.getValue('shape', 'value', type);
    var range = $g('#section-background-options input[data-option="value"][data-group="shape"]')
        .val(value).prev().val(value);
    setLinearWidth(range);
}

$g('.cookies-layout-select').on('customAction', function(){
    app.edit.lightbox.layout = this.querySelector('input[type="hidden"]').value;
    if (app.edit.lightbox.layout == 'lightbox') {
        app.edit.lightbox.position = 'lightbox-bottom-right';
    } else {
        app.edit.lightbox.position = 'notification-bar-bottom';
    }
    setCookiesPosition();
    app.sectionRules();
    app.addHistory();
});

$g('#section-settings-dialog .full-group .column-content-align .ba-custom-select').on('customAction', function(){
    var column = app.editor.$g(app.selector);
    if (app.edit.type == 'flipbox') {
        column = column.find('.ba-grid-column');
    }
    column.removeClass(app.edit.content_align);
    app.edit.content_align = this.querySelector('input[type="hidden"]').value;
    column.addClass(app.edit.content_align);
    app.addHistory();
});

$g('#section-settings-dialog .enable-sticky input').on('change', function(){
    app.edit.sticky.enable = this.checked;
    app.editor.$g(app.selector).closest('.ba-grid-column-wrapper')[this.checked ? 'addClass' : 'removeClass']('ba-sticky-column-wrapper');
    $g('#section-settings-dialog .sticky-offset').css('display', this.checked ? '' : 'none');
    $g('#section-settings-dialog .column-content-align').css('display', !this.checked ? '' : 'none');
    app.sectionRules();
    app.addHistory();
});

$g('.cookies-position-select').on('customAction', function(){
    app.edit.lightbox.position = this.querySelector('input[type="hidden"]').value;
    app.sectionRules();
    app.addHistory();
});

$g('.megamenu-position-select').on('customAction', function(){
    var value = this.querySelector('input[type="hidden"]').value,
        wrapper = app.editor.$g(app.selector).closest('.ba-wrapper');
    if (value) {
        wrapper.addClass(value);
    } else {
        wrapper.removeClass(app.edit.position);
    }
    wrapper.closest('li').trigger('mouseenter');
    app.edit.view.position = value;
    app.sectionRules();
});

$g('#section-general-options .full-group .image-width input[data-option="width"]').on('input', function(){
    app.editor.$g(app.selector).closest('li').trigger('mouseenter');
});

$g('.shape-divider-effect').on('customAction', function(){
    app.editor.$g(app.selector+' > .ba-shape-divider').remove();
    var input = this.querySelector('input[type="hidden"]'),
        value = input.value,
        type = input.dataset.subgroup;
    app.setValue(value, 'shape', 'effect', type);
    if (app.edit.preset) {
        var str = '.ba-'+app.edit.type.replace('column', 'grid-column');
        app.editor.$g(str).each(function(){
            if (app.editor.app.items[this.id] && app.editor.app.items[this.id].preset == app.edit.preset) {
                setShapeDividers(app.editor.app.items[this.id], this.id);
            }
        });
    } else {
        setShapeDividers(app.edit, app.editor.app.edit);
    }
    app.sectionRules();
    app.addHistory();
});

$g('.shape-divider-position').on('customAction', function(){
    setDividerPosition(this.querySelector('input[type="hidden"]').value);
});

$g('#section-settings-dialog .header-layout-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val(),
        item = app.editor.document.querySelector('header.header');
    if (app.edit.layout) {
        item.classList.remove(app.edit.layout);
    }
    app.edit.layout = value;
    if (app.edit.layout) {
        item.classList.add(app.edit.layout);
    }
    if (!app.edit.layout) {
        $g('.full-group').removeAttr('style');
    } else {
        $g('.full-group').hide();
    }
    if (app.edit.layout == "sidebar-menu" && (app.view == 'desktop' || app.view == 'laptop')) {
        $g('#section-settings-dialog .header-position').hide();
        $g('#section-settings-dialog .header-sidebar-width').css('display', '');
    } else {
        $g('#section-settings-dialog .header-position').css('display', '');
        $g('#section-settings-dialog .header-sidebar-width').hide();
    }
    app.addHistory();
});

$g('#section-settings-dialog .header-position-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val();
    app.setValue(value, 'position');
    app.sectionRules();
    app.addHistory();
    app.editor.$g('header.header').css('top', '');
});

app.modules.sectionEditor = true;
app.sectionEditor();