/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

function appendItemStyles(key, type, preset)
{
    if (!app.pageCss[key]) {
        app.pageCss[key] = document.createElement('style');
        app.pageCss[key].type = 'text/css';
        var str = getPageCSS(app.items[key], key);
        app.pageCss[key].innerHTML = str;
        document.head.appendChild(app.pageCss[key]);
    } else if (app.edit == 'body' || app.edit == key ||
        (preset && app.items[key].type == type && app.items[key].preset == preset)) {
        var str = getPageCSS(app.items[key], key);
        app.pageCss[key].innerHTML = str;
    }
}

app.sectionRules = function(){
    var type = preset = '';
    if (app.edit && app.edit != 'body' && app.items[app.edit]) {
        type = app.items[app.edit].type;
        preset = app.items[app.edit].preset;
    }
    for (var key in app.items) {
        appendItemStyles(key, type, preset)
    }
    if (app.preloader) {
        app.preloader.classList.add('ba-hide');
        app.preloader.classList.remove('ba-preloader-slide');
        window.top.$g('.gridbox-save').on('click', function(){
            window.top.app.checkModule('gridboxSave');
        }).addClass('gridbox-enabled-save');
        app.preloader = null;
    }
    if (app.setNewFont) {
        getFontUrl();
    }
}

function getPageCSS(obj, key)
{
    var str = '';
    app.itemType = key;
    comparePresets(obj);
    app.breakpoint = 'desktop';
    switch (obj.type) {
        case 'checkout-order-form':
            break;
        case 'checkout-form':
            str += createCheckoutFormRules(obj, key);
            break;
        case 'preloader':
            str += createPreloaderRules(obj, key);
            break;
        case 'icon-list':
            str += createIconListRules(obj, key);
            break;
        case 'search':
        case 'store-search':
            str += createSearchRules(obj, key);
            break;
        case 'logo' :
            str += createLogoRules(obj, key);
            break;
        case 'feature-box' :
            str += createFeatureBoxRules(obj, key);
            break;
        case 'slideshow' :
        case 'field-slideshow' :
        case 'product-slideshow' :
            str += createSlideshowRules(obj, key);
            break;
        case 'carousel' :
        case 'slideset' :
            str += createCarouselRules(obj, key);
            break;
        case 'testimonials-slider' :
            str += createTestimonialsRules(obj, key);
            break;
        case 'recent-posts-slider':
        case 'related-posts-slider':
        case 'recently-viewed-products':
            str += createRecentSliderRules(obj, key);
            break;
        case 'content-slider':
            str += createContentRules(obj, key);
            break;
        case 'menu' :
            str += createMenuRules(obj, key);
            break;
        case 'one-page' :
            str += createOnePageRules(obj, key);
            break;
        case 'map':
        case 'yandex-maps':
        case 'openstreetmap':
        case 'field-google-maps':
        case 'google-maps-places':
            str += createMapRules(obj, key);
            break;
        case 'weather' :
            str += createWeatherRules(obj, key);
            break;
        case 'scroll-to-top' :
            str += createScrollTopRules(obj, key);
            break;
        case 'image' :
        case 'image-field' :
            str += createImageRules(obj, key);
            break;
        case 'video':
        case 'field-video':
            str += createVideoRules(obj, key);
            break;
        case 'tabs' :
            str += createTabsRules(obj, key);
            break;
        case 'accordion' :
            str += createAccordionRules(obj, key);
            break;
        case 'icon' :
        case 'social-icons':
            str += createIconRules(obj, key);
            break;
        case 'cart':
        case 'button':
        case 'tags':
        case 'post-tags':
        case 'overlay-button':
        case 'scroll-to':
        case 'wishlist':
            str += createButtonRules(obj, key);
            break;
        case 'countdown' :
            str += createCountdownRules(obj, key);
            break;
        case 'counter' :
            str += createCounterRules(obj, key);
            break;
        case 'text':
        case 'headline':
            str += createTextRules(obj, key);
            break;
        case 'progress-bar' :
            str += createProgressBarRules(obj, key);
            break;
        case 'progress-pie' :
            str += createProgressPieRules(obj, key);
            break;
        case 'social' :
            str += createSocialRules(obj, key);
            break;
        case 'disqus':
        case 'vk-comments':
        case 'hypercomments':
        case 'facebook-comments':
        case 'modules':
        case 'custom-html':
        case 'gallery':
        case 'forms':
            str += createModulesRules(obj, key);
            break;
        case 'comments-box':
        case 'reviews':
            str += createCommentsBoxRules(obj, key);
            break;
        case 'event-calendar':
            str += createEventCalendarRules(obj, key);
            break;
        case 'field':
        case 'field-group':
            str += createFieldRules(obj, key);
            break;
        case 'fields-filter':
            str += createFieldsFilterRules(obj, key);
            break;
        case 'blog-posts' :
        case 'search-result':
        case 'store-search-result':
        case 'recent-posts' :
        case 'post-navigation' :
        case 'related-posts' :
            str += createBlogPostsRules(obj, key);
            break;
        case 'add-to-cart' :
            str += createAddToCartRules(obj, key);
            break;
        case 'categories' :
            str += createCategoriesRules(obj, key);
            break;
        case 'recent-comments':
        case 'recent-reviews':
            str += createRecentCommentsRules(obj, key);
            break;
        case 'author':
            str += createAuthorRules(obj, key);
            break;
        case 'star-ratings' :
            str += createStarRatingsRules(obj, key);
            break;
        case 'post-intro' :
        case 'category-intro' :
            str += createPostIntroRules(obj, key);
            break;
        case 'instagram':
            str += '';
            break;
        case 'simple-gallery':
        case 'field-simple-gallery':
        case 'product-gallery':
            str += createSimpleGalleryRules(obj, key);
            break;
        case 'blog-content' :
            break;
        case 'mega-menu-section' :
            str += createMegaMenuSectionRules(obj, key);
            break;
        case 'flipbox' :
            str += createFlipboxRules(obj, key);
            break;
        case 'error-message':
            str += createErrorRules(obj, key);
            break;
        case 'search-result-headline':
            str += createSearchHeadlineRules(obj, key);
            break;
        default :
            str += createSectionRules(obj, key);
    }
    
    return str;
}

function setItemsVisability(disable, display, selector)
{
    var str = 'body.show-hidden-elements '+selector+' {';
    if (disable == 1) {
        str += "opacity : 0.3;";
    } else {
        str += "opacity : 1;";
    }
    str += "display : "+display+";";
    str += '}';
    str += 'body:not(.show-hidden-elements) '+selector+' {';
    if (disable == 1) {
        str += "display : none;";
    } else {
        str += "display : "+display+";";
    }
    str += '}';

    return str;
}

function setBoxModel(obj, selector)
{
    var str = '';
    if (obj.margin) {
        if (obj.margin.top) {
            str += "#"+selector+" > .ba-box-model:before {";
            str += "height: "+obj.margin.top+"px;";
            if (obj.border && obj.border.width) {
                if ((obj.border.top && obj.border.top == 1) || !obj.border.top) {
                    str += "top: -"+obj.border.width+"px;";
                } else {
                    str += "top: 0;";
                }
            }
            str += "}";
        }
        if (obj.margin.bottom) {
            str += "#"+selector+" > .ba-box-model:after {";
            str += "height: "+obj.margin.bottom+"px;";
            if (obj.border && obj.border.width) {
                if ((obj.border.bottom && obj.border.bottom == 1) || !obj.border.bottom) {
                    str += "bottom: -"+obj.border.width+"px;";
                } else {
                    str += "bottom: 0";
                }
            }
            str += "}";
        }
    }
    for (var ind in obj.padding) {
        str += "#"+selector+" > .ba-box-model .ba-bm-"+ind+" {";
        str += "width: "+obj.padding[ind]+"px; height: "+obj.padding[ind]+"px;}";
    }

    return str;
}

function createOnePageRules(obj, key)
{
    if (!obj.desktop.nav) {
        var $nav = '{"padding":{"bottom":"15","left":"15","right":"15","top":"15"},"margin":{"left":"0","right":"0"}';
        $nav += ',"icon":{"size":24},"border":{"bottom":"0","left":"0","right":"0","top":"0","color":"#000000",';
        $nav += '"style":"solid","radius":"0","width":"0"},"normal":{"color":"color","background":"rgba(0,0,0,';
        $nav += '0)"},"hover":{"color":"color","background":"rgba(0,0,0,0)"}}';
        obj.desktop.nav = JSON.parse($nav);
        obj.desktop.nav.normal.color = obj.desktop['nav-typography'].color;
        obj.desktop.nav.hover.color = obj.desktop['nav-hover'].color;
    }
    var str = getOnePageRules(obj.desktop, key);
    str += "#"+key+" .main-menu li a:hover {";
    str += "color : "+getCorrectColor(obj.desktop.nav.hover.color)+";";
    str += "background-color : "+getCorrectColor(obj.desktop.nav.hover.background)+";";
    str += "}";
    if (!disableResponsive) {
        str += "@media (max-width: "+menuBreakpoint+"px) {"
        str += "#"+key+" .ba-hamburger-menu .main-menu {";
        str += "background-color : "+getCorrectColor(obj.hamburger.background)+";";
        str += "}"
        str += "#"+key+" .ba-hamburger-menu .open-menu {";
        str += "color : "+getCorrectColor(obj.hamburger.open)+";";
        str += "text-align : "+obj.hamburger['open-align']+";";
        str += "}";
        str += "#"+key+" .ba-hamburger-menu .close-menu {";
        str += "color : "+getCorrectColor(obj.hamburger.close)+";";
        str += "text-align : "+obj.hamburger['close-align']+";";
        str += "}";
        str += "}";
    }
    str += app.setMediaRules(obj, key, 'getOnePageRules');
    $g('#'+key).removeClass('side-navigation-menu').addClass(obj.layout.type).find('.ba-menu-wrapper').each(function(){
        $g(this).removeClass('vertical-menu ba-menu-position-left ba-hamburger-menu ba-menu-position-center');
        if (obj.hamburger.enable) {
            $g(this).addClass('ba-hamburger-menu');
        }
    }).addClass(obj.layout.layout).addClass(obj.hamburger.position);

    return str;
}

function createMenuRules(obj, key)
{
    if (!obj.desktop.nav) {
        var $nav = '{"padding":{"bottom":"15","left":"15","right":"15","top":"15"},"margin":{"left":"0","right":"0"}';
        $nav += ',"icon":{"size":24},"border":{"bottom":"0","left":"0","right":"0","top":"0","color":"#000000",';
        $nav += '"style":"solid","radius":"0","width":"0"},"normal":{"color":"color","background":"rgba(0,0,0,';
        $nav += '0)"},"hover":{"color":"color","background":"rgba(0,0,0,0)"}}';
        obj.desktop.nav = JSON.parse($nav);
        obj.desktop.nav.normal.color = obj.desktop['nav-typography'].color;
        obj.desktop.nav.hover.color = obj.desktop['nav-hover'].color;
        var sub = '{"padding":{"bottom":"10","left":"20","right":"20","top":"10"},"icon":{"size":24},"border":{';
        sub += '"bottom":"0","left":"0","right":"0","top":"0","color":"#000000","style":"solid","radius":"0",';
        sub += '"width":"0"},"normal":{"color":"color","background":"rgba(0,0,0,0)"},"hover":{"color":"color",';
        sub += '"background":"rgba(0,0,0,0)"}}';
        obj.desktop.sub = JSON.parse(sub);
        obj.desktop.sub.normal.color = obj.desktop['sub-typography'].color;
        obj.desktop.sub.hover.color = obj.desktop['sub-hover'].color;
        sub = '{"width":250,"animation":{"effect":"fadeInUp","duration":"0.2"},"padding":{"bottom":"10",';
        sub += '"left":"0","right":"0","top":"10"}}';
        obj.desktop.dropdown = JSON.parse(sub);
    }
    var str = getMenuRules(obj.desktop, key);
    str += "#"+key+" ul.nav-child {";
    str += "width: "+obj.desktop.dropdown.width+"px;";
    str += "background-color : "+getCorrectColor(obj.desktop.background.color)+";";
    str += "box-shadow: 0 "+(obj.desktop.shadow.value * 10);
    str += "px "+(obj.desktop.shadow.value * 20)+"px 0 "+getCorrectColor(obj.desktop.shadow.color)+";";
    str += "animation-duration: "+obj.desktop.dropdown.animation.duration+"s;"
    str += "}";
    str += "#"+key+" li.megamenu-item > .tabs-content-wrapper > .ba-section {";
    str += "box-shadow: 0 "+(obj.desktop.shadow.value * 10);
    str += "px "+(obj.desktop.shadow.value * 20)+"px 0 "+getCorrectColor(obj.desktop.shadow.color)+";";
    str += "animation-duration: "+obj.desktop.dropdown.animation.duration+"s;"
    str += "}";
    str += "#"+key+" .nav-child > .deeper:hover > .nav-child {";
    str += "top : -"+obj.desktop.dropdown.padding.top+"px;";
    str += "}";
    if (!disableResponsive) {
        str += "@media (max-width: "+menuBreakpoint+"px) {"
        str += "#"+key+" .ba-hamburger-menu .main-menu {";
        str += "background-color : "+getCorrectColor(obj.hamburger.background)+";";
        str += "}"
        str += "#"+key+" .ba-hamburger-menu .open-menu {";
        str += "color : "+getCorrectColor(obj.hamburger.open)+";";
        str += "text-align : "+obj.hamburger['open-align']+";";
        str += "}";
        str += "#"+key+" .ba-hamburger-menu .close-menu {";
        str += "color : "+getCorrectColor(obj.hamburger.close)+";";
        str += "text-align : "+obj.hamburger['close-align']+";";
        str += "}";
        str += "}";
    }
    $g('#'+key).find('> .ba-menu-wrapper').each(function(){
        $g(this).removeClass('vertical-menu ba-menu-position-left ba-hamburger-menu ba-collapse-submenu ba-menu-position-center');
        if (obj.hamburger.enable) {
            $g(this).addClass('ba-hamburger-menu');
        }
        if (obj.hamburger.collapse) {
            $g(this).addClass('ba-collapse-submenu');
        }
    }).addClass(obj.layout.layout).addClass(obj.hamburger.position);
    str += app.setMediaRules(obj, key, 'getMenuRules');

    return str;
}

function createLogoRules(obj, key)
{
    var str = getLogoRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getLogoRules');

    return str;
}

function createWeatherRules(obj, key)
{
    var str = getWeatherRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getWeatherRules');

    return str;
}

function createScrollTopRules(obj, key)
{
    var str = getScrollTopRules(obj.desktop, key);
    str += "#"+key+" i.ba-btn-transition:hover {";
    str += "color : "+getCorrectColor(obj.hover.color)+";";
    str += "background-color : "+getCorrectColor(obj.hover['background-color'])+";";
    str += "}";
    str += app.setMediaRules(obj, key, 'getScrollTopRules');
    if (obj.type == 'scroll-to-top') {
        $g("#"+key).removeClass('scroll-btn-left scroll-btn-right').addClass('scroll-btn-'+obj.text.align);
    }

    return str;
}

function createCarouselRules(obj, key)
{
    var str = getCarouselRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getCarouselRules');
    $g('#'+key+' ul').removeClass('caption-over caption-hover')
        .addClass(obj.desktop.caption.position).addClass(obj.desktop.caption.hover);

    return str;
}

function createTestimonialsRules(obj, key)
{
    var str = getTestimonialsRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getTestimonialsRules');
    for (var ind in obj.slides) {
        if (obj.slides[ind].image) {
            str += "#"+key+" li.item:nth-child("+ind+") .testimonials-img,";
            str += " #"+key+" ul.style-6 .ba-slideset-dots > div:nth-child("+ind+") {background-image: url(";
            if (obj.slides[ind].image.indexOf('balbooa.com') != -1) {
                str += obj.slides[ind].image+");";
            } else {
                str += JUri+encodeURI(obj.slides[ind].image)+");";
            }
            str += "}"; 
        }
    }

    return str;
}

function createRecentSliderRules(obj, key)
{
    app.blogPostsInfo = app.blogPostsFields = null;
    if (obj.info) {
        app.blogPostsInfo = obj.info;
    }
    if (obj.fields) {
        app.blogPostsFields = obj.fields;
    }
    if (!obj.desktop.store) {
        obj.desktop.store = {
            badge: true,
            wishlist: true,
            price: true,
            cart: true
        }
    }
    var str = getRecentSliderRules(obj.desktop, key);
    if (obj.fields) {
        for (let i = 0; i < obj.fields.length; i++) {
            str += '#'+key+' .ba-blog-post-field-row[data-id="'+obj.fields[i]+'"] {';
            str += "order: "+i+";";
            str += "}";
        }
    }
    if (obj.info) {
        for (let i = 0; i < obj.info.length; i++) {
            str += '#'+key+' .ba-blog-post-'+obj.info[i]+' {';
            str += "order: "+i+";";
            str += "}";
        }
    }
    str += app.setMediaRules(obj, key, 'getRecentSliderRules');
    $g('#'+key+' ul').removeClass('caption-over caption-hover')
        .addClass(obj.desktop.caption.position).addClass(obj.desktop.caption.hover);

    return str;
}

function createContentRules(obj, key)
{
    var str = getContentSliderRules(obj.desktop, key),
        slideStr = '';
    str += app.setMediaRules(obj, key, 'getContentSliderRules');
    for (var ind in obj.slides) {
        slideStr = "#"+key+" > .slideshow-wrapper > .ba-slideshow > .slideshow-content > li.item:nth-child("+ind+")";
        str += getContentSliderItemsRules(obj.slides[ind].desktop, slideStr);
        str += app.setMediaRules(obj.slides[ind], slideStr, 'getContentSliderItemsRules');
    }

    return str;
}

function createFeatureBoxRules(obj, key)
{
    var str = getFeatureBoxRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getFeatureBoxRules');
    for (var ind in obj.items) {
        if (obj.items[ind].type == 'image' && obj.items[ind].image) {
            str += "#"+key+" .ba-feature-box:nth-child("+(ind * 1 + 1)+") .ba-feature-image {background-image: url(";
            if (obj.items[ind].image.indexOf('balbooa.com') != -1) {
                str += obj.items[ind].image+");";
            } else {
                str += JUri+encodeURI(obj.items[ind].image)+");";
            }
            str += "}";
        }
    }

    return str;
}

function createSlideshowRules(obj, key)
{
    var str = getSlideshowRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getSlideshowRules');
    if (obj.type == 'field-slideshow' || obj.type == 'product-slideshow') {
        str += "body.com_gridbox.gridbox #"+key+" li.item .ba-slideshow-img,"
        str += "body.com_gridbox.gridbox #"+key+" .thumbnails-dots div {";
        str += "background-image: url("+JUri+"components/com_gridbox/assets/images/default-theme.png);"
        str += "}";
    }
    

    return str;
}

function createAccordionRules(obj, key)
{
    var str = getAccordionRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getAccordionRules');

    return str;
}

function createTabsRules(obj, key)
{
    var str = getTabsRules(obj.desktop, key);
    str += "#"+key+" ul.nav.nav-tabs li a:hover {";
    str += "color : "+getCorrectColor(obj.desktop.hover.color)+";";
    str += "}";
    if (obj.desktop.icon.position == 'icon-position-left') {
        str += '#'+key+' .ba-tabs-wrapper > ul li a > span {direction: rtl;display: inline-flex;'
        str += 'flex-direction: row;}';
        str += '#'+key+' .ba-tabs-wrapper > ul li a > span i {margin-bottom:0;}';
    } else if (obj.desktop.icon.position == 'icon-position-top') {
        str += '#'+key+' .ba-tabs-wrapper > ul li a > span {display: inline-flex;';
        str += 'flex-direction: column-reverse;}';
        str += '#'+key+' .ba-tabs-wrapper > ul li a > span i {margin-bottom:10px;}';
    } else {
        str += '#'+key+' .ba-tabs-wrapper > ul li a > span {direction: ltr;display: inline-flex;'
        str += 'flex-direction: row;}';
        str += '#'+key+' .ba-tabs-wrapper > ul li a > span i {margin-bottom:0;}';
    }
    str += app.setMediaRules(obj, key, 'getTabsRules');

    return str;
}

function createMapRules(obj, key)
{
    var str = getMapRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getMapRules');

    return str;
}

function createCounterRules(obj, key)
{
    var str = getCounterRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getCounterRules');

    return str;
}

function createCountdownRules(obj, key)
{
    var str = getCountdownRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getCountdownRules');

    return str;
}

function createSearchRules(obj, key)
{
    var str = getSearchRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getSearchRules');
    $g('#'+key).find('.ba-search-wrapper').removeClass('after').addClass(obj.desktop.icons.position);

    return str;
}

function setOverlaySectionTrigger(obj, trigger)
{
    var array = new Array('border', 'margin', 'shadow');
    for (var i = 0; i < array.length; i++) {
        obj.desktop[array[i]] = obj.sides[trigger].desktop[array[i]];
    }
    for (var ind in breakpoints) {
        if (!obj[ind]) {
            obj[ind] = {};
        }
        if (!obj.sides[trigger][ind]) {
            obj.sides[trigger][ind] = {};
        }
        for (var i = 0; i < array.length; i++) {
            if (!obj[ind][array[i]]) {
                obj[ind][array[i]] = {}
            }
            if (!obj.sides[trigger][ind][array[i]]) {
                obj.sides[trigger][ind][array[i]] = {};
            }
            obj[ind][array[i]] = obj.sides[trigger][ind][array[i]];
        }
    }
}

function createCheckoutFormRules(obj, key)
{
    var str = getCheckoutFormRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getCheckoutFormRules');

    return str;
}

function createIconListRules(obj, key)
{
    var str = getIconListRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getIconListRules');
    str += "#"+key+" .ba-icon-list-wrapper ul li a:hover span {";
    str += "color : inherit;";
    str += "}";
    str += "#"+key+" .ba-icon-list-wrapper ul li i, #"+key+" ul li a:before, #"+key+" ul li.list-item-without-link:before {";
    str += "order: "+(obj.icon.position == '' ? 0 : 2)+";";
    str += "margin-"+(obj.icon.position == '' ? 'right' : 'left')+": 20px;";
    str += "}";

    return str;
}

function createButtonRules(obj, key)
{
    if (obj.type == 'overlay-button' && obj.trigger == 'button') {
        setOverlaySectionTrigger(obj, 'button');
    }
    var str = getButtonRules(obj.desktop, key);
    str += "#"+key+" .ba-button-wrapper a:hover {";
    str += "color : "+getCorrectColor(obj.hover.color)+";";
    str += "background-color : "+getCorrectColor(obj.hover['background-color'])+";";
    str += "}";
    if (typeof(obj.icon) == 'object') {
        str += "#"+key+" .ba-button-wrapper a {";
        if (obj.icon.position == '') {
            str += 'flex-direction: row-reverse;';
        } else {
            str += 'flex-direction: row;';
        }
        str += "}";
        if (obj.icon.position == '') {
            str += "#"+key+" .ba-button-wrapper a i {";
            str += 'margin: 0 10px 0 0;';
            str += "}";
        } else {
            str += "#"+key+" .ba-button-wrapper a i {";
            str += 'margin: 0 0 0 10px;';
            str += "}";
        }
    }
    if (obj.type == 'overlay-button' && obj.trigger == 'image') {
        setOverlaySectionTrigger(obj, 'image');
        str = getImageRules(obj.desktop, key);
        str += app.setMediaRules(obj, key, 'getImageRules');
    }
    str += app.setMediaRules(obj, key, 'getButtonRules');

    return str;
}

function createRecentCommentsRules(obj, key)
{
    var str = getRecentCommentsRules(obj.desktop, key, obj.type);
    str += app.setMediaRules(obj, key, 'getRecentCommentsRules');

    return str;
}

function createCategoriesRules(obj, key)
{
    var str = getCategoriesRules(obj.desktop, key);
    str += "#"+key+" .ba-blog-post-title a:hover, #"+key+" .ba-blog-post.active .ba-blog-post-title a {";
    str += "color: "+getCorrectColor(obj.desktop.title.hover.color)+";";
    str += "}";
    str += "#"+key+" .ba-blog-post-info-wrapper a:hover, #"+key+" .ba-blog-post-info-wrapper a.active {";
    str += "color: "+getCorrectColor(obj.desktop.info.hover.color)+";";
    str += "}";
    str += app.setMediaRules(obj, key, 'getCategoriesRules');

    return str;
}

function createAddToCartRules(obj, key)
{
    var str = getAddToCartRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getAddToCartRules');

    return str;
}

function createBlogPostsRules(obj, key)
{
    app.blogPostsInfo = app.blogPostsFields = null;
    if (obj.info) {
        app.blogPostsInfo = obj.info;
    }
    if (obj.fields) {
        app.blogPostsFields = obj.fields;
    }
    if (!obj.desktop.store) {
        obj.desktop.store = {
            badge: true,
            wishlist: true,
            price: true,
            cart: true
        }
    }
    var str = getBlogPostsRules(obj.desktop, key, obj.type);
    str += "#"+key+" .ba-blog-post-title a:hover {";
    str += "color: "+getCorrectColor(obj.desktop.title.hover.color)+";";
    str += "}";
    str += "#"+key+" .ba-blog-post-info-wrapper > * a:hover, #"+key+" .ba-post-navigation-info a:hover {";
    str += "color: "+getCorrectColor(obj.desktop.info.hover.color)+";";
    str += "}";
    if (obj.fields) {
        for (let i = 0; i < obj.fields.length; i++) {
            str += '#'+key+' .ba-blog-post-field-row[data-id="'+obj.fields[i]+'"] {';
            str += "order: "+i+";";
            str += "}";
        }
    }
    if (obj.info) {
        for (let i = 0; i < obj.info.length; i++) {
            str += '#'+key+' .ba-blog-post-'+obj.info[i]+' {';
            str += "order: "+i+";";
            str += "}";
        }
    }
    str += app.setMediaRules(obj, key, 'getBlogPostsRules');

    return str;
}

function createAuthorRules(obj, key)
{
    var str = getAuthorRules(obj.desktop, key);
    str += "#"+key+" .ba-post-author-title a:hover {";
    str += "color: "+getCorrectColor(obj.desktop.title.hover.color)+";";
    str += "}";
    str += app.setMediaRules(obj, key, 'getAuthorRules');

    return str;
}

function createPostIntroRules(obj, key)
{
    app.blogPostsInfo = null;
    if (obj.info) {
        app.blogPostsInfo = obj.info;
    }
    var str = getPostIntroRules(obj.desktop, key);
    str += "#"+key+" .intro-post-wrapper .intro-post-info > * a:hover {";
    str += "color: "+getCorrectColor(obj.desktop.info.hover.color)+";";
    str += "}";
    if (obj.info) {
        for (let i = 0; i < obj.info.length; i++) {
            str += '#'+key+' .intro-post-'+obj.info[i]+' {';
            str += "order: "+i+";";
            str += "}";
        }
    }
    str += app.setMediaRules(obj, key, 'getPostIntroRules');
    $g('#'+key).find('.intro-post-wrapper').removeClass('fullscreen-post').addClass(obj.layout.layout);

    return str;
}

function createIconRules(obj, key)
{
    var str = getIconRules(obj.desktop, key);
    str += "#"+key+" .ba-icon-wrapper i:hover {";
    str += "color : "+getCorrectColor(obj.hover.color)+";";
    str += "background-color : "+getCorrectColor(obj.hover['background-color'])+";";
    str += "}";
    str += app.setMediaRules(obj, key, 'getIconRules');

    return str;
}

function createStarRatingsRules(obj, key)
{
    var str = getStarRatingsRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getStarRatingsRules');

    return str;
}

function createSimpleGalleryRules(obj, key)
{
    var str = getSimpleGalleryRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getSimpleGalleryRules');
    str += '#'+key+' .ba-instagram-image {';
    str += 'cursor: zoom-in;';
    str += '}';

    return str;
}

function createErrorRules(obj, key)
{
    var str = getErrorRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getErrorRules');

    return str;
}

function createSearchHeadlineRules(obj, key)
{
    var str = getSearchHeadlineRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getSearchHeadlineRules');

    return str;
}


function createTextRules(obj, key)
{
    var array = new Array('h1' ,'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'links');
    if (obj.global) {
        delete(obj.global);
        array.forEach(function(el){
            delete(obj.desktop[el]);
            for (var ind in breakpoints) {
                if (obj[ind]) {
                    delete(obj[ind][el]);
                }
            }
        });
    }
    if (!obj.desktop.p) {
        array.forEach(function(el){
            if (el != 'links') {
                obj.desktop[el] = {
                    "font-family" : "@default",
                    "font-weight" : "@default"
                };
                for (var ind in breakpoints) {
                    if (!obj[ind]) {
                        obj[ind] = {};
                    }
                    obj[ind][el] = {};
                }
            }
        });
    }
    if (!obj.desktop.links) {
        obj.desktop.links = {};
    }
    var str = getTextRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getTextRules');

    return str;
}

function createProgressPieRules(obj, key)
{
    var str = getProgressPieRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getProgressPieRules');

    return str;
}

function createProgressBarRules(obj, key)
{
    var str = getProgressBarRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getProgressBarRules');

    return str;
}

function createSocialRules(obj, key)
{
    var str = getModulesRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getModulesRules');
    str += '#'+key+' .social-counter {display:'+(obj.view.counters ? 'inline-block' : 'none')+'}';
    $g('#'+key).removeClass('ba-social-sidebar').each(function(){
        if (obj.view.layout == 'ba-social-sidebar') {
            if (this.parentNode.localName != 'body') {
                obj.parent = this.parentNode.id;
                document.body.appendChild(this);
            }
        } else {
            if (this.parentNode.localName == 'body') {
                var parent = document.getElementById(obj.parent);
                if (!parent) {
                    parent = document.querySelector('.ba-grid-column');
                    if (!parent) {
                        return false;
                    }
                }
                obj.parent = parent.id;
                $g(parent).find(' > .empty-item').before(this);
            }
        }
        let keys = new Array('facebook', 'linkedin', 'pinterest', 'twitter', 'vk'),
            count = 0;
        for (let i = 0; i < keys.length; i++) {
            if (obj[keys[i]]) {
                count++;
            }
        }
        this.style.setProperty('--social-count', count);
    }).addClass(obj.view.layout).attr('data-size', obj.view.size).attr('data-style', obj.view.style)
        .find('.ba-social').removeClass('ba-social-sm ba-social-md ba-social-lg')
        .addClass(obj.view.size).removeClass('ba-social-classic ba-social-flat ba-social-circle ba-social-minimal')
        .addClass(obj.view.style);

    return str;
}

function createEventCalendarRules(obj, key)
{
    var str = getEventCalendarRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getEventCalendarRules');

    return str;
}

function createCommentsBoxRules(obj, key)
{
    var str = getCommentsBoxRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getCommentsBoxRules');
    if (!obj.view.user) {
        str += "#"+key+" .ba-user-login-wrapper {";
        str += "display: none;";
        str += "}";
    }
    if (!obj.view.social) {
        str += "#"+key+" .ba-social-login-wrapper {";
        str += "display: none;";
        str += "}";
    }
    if (!obj.view.guest) {
        str += "#"+key+" .ba-guest-login-wrapper {";
        str += "display: none;";
        str += "}";
    }
    if (!obj.view.share) {
        str += "#"+key+" .comment-share-action {";
        str += "display: none;";
        str += "}";
    }
    if (!obj.view.rating) {
        str += '#'+key+' .comment-likes-action-wrapper {';
        str += "display: none;";
        str += "}";
    }
    if (!obj.view.files) {
        str += '#'+key+' .ba-comments-attachment-file-wrapper[data-type="file"] {';
        str += "display: none;";
        str += "}";
    }
    if (!obj.view.images) {
        str += '#'+key+' .ba-comments-attachment-file-wrapper[data-type="image"] {';
        str += "display: none;";
        str += "}";
    }
    if (!obj.view.report) {
        str += '#'+key+' .comment-report-user-comment {';
        str += "display: none;";
        str += "}";
    }
    if (('reply' in obj.view) && !obj.view.reply) {
        str += '#'+key+' .comment-reply-action {';
        str += "display: none;";
        str += "}";
    }

    return str;
}

function createFieldRules(obj, key)
{
    var str = getFieldRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getFieldRules');
    $g('#'+key+' .ba-field-wrapper').removeClass('ba-label-position-right ba-label-position-left').addClass(obj.layout.position);

    return str;
}

function createFieldsFilterRules(obj, key)
{
    app.blogPostsFields = obj.fields;
    var str = getFieldsFilterRules(obj.desktop, key);
    for (let i = 0; i < obj.fields.length; i++) {
        str += '#'+key+' .ba-field-filter[data-id="'+obj.fields[i]+'"] {';
        str += "order: "+i+";";
        str += "}";
    }
    if (obj.auto) {
        str += '#'+key+' .ba-items-filter-search-button {';
        str += 'display: none;';
        str += "}";
    }
    str += app.setMediaRules(obj, key, 'getFieldsFilterRules');

    return str;
}

function createModulesRules(obj, key)
{
    var str = getModulesRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getModulesRules');

    return str;
}

function createPreloaderRules(obj, key)
{
    var str = getPreloaderRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getPreloaderRules');

    return str;
}

function createImageRules(obj, key)
{
    var str = getImageRules(obj.desktop, key);
    if (obj.link && obj.link.link) {
        str += '#'+key+' .ba-image-wrapper { cursor: pointer; }';
    } else if (obj.popup) {
        str += '#'+key+' .ba-image-wrapper { cursor: zoom-in; }';
    } else {
        str += '#'+key+' .ba-image-wrapper { cursor: default; }';
    }
    str += app.setMediaRules(obj, key, 'getImageRules');

    return str;
}

function createVideoRules(obj, key)
{
    var str = getVideoRules(obj.desktop, key);
    str += app.setMediaRules(obj, key, 'getVideoRules');

    return str;
}

function createHeaderRules(obj, view)
{
    var str = "body header.header {";
    str += "position:"+obj.position+";";
    str += "}";
    if (!obj.width) {
        obj.width = 250;
    }
    str += "body {";
    str += "--sidebar-menu-width:"+obj.width+"px;";
    str += "}";
    str += "body.com_gridbox.gridbox header.header {";
    if (obj.position == 'fixed') {
        if (view == 'desktop') {
            str += "width: calc(100% - 103px);";
            str += "left: 52px;";
        } else {
            str += "width: 100%;";
            str += "left: 0;";    
        }
        str += "top: 40px;";
    } else {
        str += "width: 100%;";
        str += "left: 0;";
        str += "top: 0;";
    }
    if (obj.position == 'relative') {
        str += "z-index: auto;";
    } else {
        str += "z-index: 40;";
    }
    str += "}";
    str += "body.com_gridbox.gridbox header.header:hover, body.body-megamenu-editing.com_gridbox.gridbox header.header {";
    if (obj.position == 'relative') {
        str += "z-index: 32;";
    } else {
        str += "z-index: 40;";
    }
    str += "}";
    if (obj.position == 'fixed') {
        str += ".ba-container .header {margin-left: calc((100vw - 1280px)/2);";
        str += "max-width: 1170px;}";
    } else {
        str += ".ba-container .header {margin-left:0;max-width: none;}";
    }

    return str;
}

function createMegaMenuSectionRules(obj, key)
{
    if (!obj.desktop.full) {
        obj.desktop.full = {
            fullscreen: obj.desktop.fullscreen == '1'
        };
        if (obj['max-width']) {
            obj.desktop.full.fullwidth = obj['max-width'] == '100%';
            delete(obj['max-width']);
        }
        delete(obj.desktop.fullscreen);
        for (var ind in breakpoints) {
            if (obj[ind] && obj[ind].fullscreen) {
                obj[ind].full = {
                    fullscreen: obj[ind].fullscreen == '1'
                };
                delete(obj[ind].fullscreen);
            }
        }
        obj.view = {
            width: obj.width,
            position: obj.position
        }
        delete(obj.width);
        delete(obj.position);
    }
    var str = createMegaMenuRules(obj.desktop, key);
    if (obj.parallax) {
        var pHeight = 100 + obj.parallax.offset * 2 * 200,
            pTop = obj.parallax.offset * 2 * -100;
        str += "#"+key+" > .parallax-wrapper.scroll .parallax {";
        str += "height: "+pHeight+"%;"
        str += "top: "+pTop+"%;"
        str += "}";
    }
    str += "#"+key+" {width: "+obj.view.width+"px; }";
    str += app.setMediaRules(obj, key, 'createMegaMenuRules');
    if (obj.desktop.background && obj.desktop.background.type != 'video') {
        $g('#'+key+' > .ba-video-background').remove();
    }
    if (!obj.desktop.full.fullwidth) {
        $g('#'+key).parent().addClass('ba-container');
    } else {
        $g('#'+key).parent().removeClass('ba-container');
    }
    $g('#'+key).parent().removeClass('megamenu-center').addClass(obj.view.position);
    
    return str;
}

function setFlipboxSide(obj, side)
{
    var array = new Array('background', 'overlay', 'image', 'video');
    obj.parallax = obj.sides[side].parallax;
    for (var i = 0; i < array.length; i++) {
        obj.desktop[array[i]] = obj.sides[side].desktop[array[i]];
    }
    for (var ind in breakpoints) {
        if (!obj[ind]) {
            obj[ind] = {};
        }
        if (!obj.sides[side][ind]) {
            obj.sides[side][ind] = {};
        }
        for (var i = 0; i < array.length; i++) {
            if (!obj[ind][array[i]]) {
                obj[ind][array[i]] = {}
            }
            if (!obj.sides[side][ind][array[i]]) {
                obj.sides[side][ind][array[i]] = {}
            }
            obj[ind][array[i]] = obj.sides[side][ind][array[i]];
        }
    }
}

function createFlipboxRules(obj, key)
{
    setFlipboxSide(obj, obj.side);
    var str = getFlipboxRules(obj.desktop, key),
        object = $g.extend(true, {}, obj);
    str += app.setMediaRules(obj, key, 'getFlipboxRules');
    setFlipboxSide(object, 'frontside');
    var key1 = key+' > .ba-flipbox-wrapper > .ba-flipbox-frontside > .ba-grid-column-wrapper > .ba-grid-column';
    if (object.parallax) {
        var pHeight = 100 + object.parallax.offset * 2 * 200,
            pTop = object.parallax.offset * 2 * -100;
        str += "#"+key1+" > .parallax-wrapper.scroll .parallax {";
        str += "height: "+pHeight+"%;"
        str += "top: "+pTop+"%;"
        str += "}";
    }
    str += getFlipsidesRules(object.desktop, key1);
    str += app.setMediaRules(object, key1, 'getFlipsidesRules');
    if (object.desktop.background && object.desktop.background.type != 'video') {
        $g('#'+key1+' > .ba-video-background').remove();
    }
    setFlipboxSide(object, 'backside');
    key1 = key+' > .ba-flipbox-wrapper > .ba-flipbox-backside > .ba-grid-column-wrapper > .ba-grid-column';
    if (object.parallax) {
        var pHeight = 100 + object.parallax.offset * 2 * 200,
            pTop = object.parallax.offset * 2 * -100;
        str += "#"+key1+" > .parallax-wrapper.scroll .parallax {";
        str += "height: "+pHeight+"%;"
        str += "top: "+pTop+"%;"
        str += "}";
    }
    str += getFlipsidesRules(object.desktop, key1);
    str += app.setMediaRules(object, key1, 'getFlipsidesRules');
    if (object.desktop.background && object.desktop.background.type != 'video') {
        $g('#'+key1+' > .ba-video-background').remove();
    }
    
    return str;
}

function createSectionRules(obj, key)
{
    if (obj.type == 'row' && !obj.desktop.view) {
        obj.desktop.view = {
            gutter: obj.desktop.gutter == '1'
        }
        delete(obj.desktop.gutter);
        for (var ind in breakpoints) {
            if (obj[ind] && obj[ind].gutter) {
                obj[ind].view = {
                    gutter: obj[ind].gutter == '1'
                };
                delete(obj[ind].gutter);
            }
        }
    }
    if (!obj.desktop.full) {
        obj.desktop.full = {
            fullscreen: obj.desktop.fullscreen == '1'
        };
        if (obj['max-width']) {
            obj.desktop.full.fullwidth = obj['max-width'] == '100%';
            delete(obj['max-width']);
        }
        delete(obj.desktop.fullscreen);
        obj.desktop.image = {
            image: obj.desktop.background.image.image
        };
        for (var ind in breakpoints) {
            if (obj[ind]) {
                if (obj[ind].fullscreen) {
                    obj[ind].full = {
                        fullscreen: obj[ind].fullscreen == '1'
                    };
                    delete(obj[ind].fullscreen);
                }
                if (obj[ind].background && obj[ind].background.image && obj[ind].background.image.image) {
                    obj[ind].image = {
                        image: obj[ind].background.image.image
                    };
                }
            }
        }
        if (obj.type == 'column') {
            for (var ind in breakpoints) {
                if (obj[ind] && obj[ind]['column-width']) {
                    obj[ind].span = {
                        width: obj[ind]['column-width']
                    }
                    delete(obj[ind]['column-width']);
                }
            }
        } else if (obj.type == 'overlay-section') {
            obj.lightbox = {
                layout: obj.layout,
                background: obj['background-overlay']
            }
            delete(obj.layout);
            delete(obj['background-overlay']);
        } else if (obj.type == 'lightbox') {
            obj.lightbox = {
                layout: obj.position,
                background: obj['background-overlay']
            }
            delete(obj.position);
            delete(obj['background-overlay']);
        } else if (obj.type == 'cookies') {
            obj.lightbox = {
                layout: obj.layout,
                position: obj.position
            }
            delete(obj.layout);
            delete(obj.position);
        }
        if (obj.desktop.width) {
            obj.desktop.view = {
                width: obj.desktop.width
            };
            delete(obj.desktop.width);
            if (obj.desktop.height) {
                obj.desktop.view.height = obj.desktop.height;
                delete(obj.desktop.height);
            }
            for (var ind in breakpoints) {
                if (obj[ind]) {
                    obj[ind].view = {};
                    if (obj[ind].width) {
                        obj[ind].view.width = obj[ind].width;
                        delete(obj[ind].width);
                    }
                    if (obj[ind].height) {
                        obj[ind].view.height = obj[ind].height;
                        delete(obj[ind].height);
                    }
                }
            }
        }
    }
    app.cssRulesFlag = 'desktop';
    var str = createPageRules(obj.desktop, key, obj.type);
    if (obj.type == 'footer') {
        app.footer = obj;
    }
    if (obj.type == 'lightbox') {
        str += ".ba-lightbox-backdrop[data-id="+key+"] .close-lightbox {";
        str += "color: "+getCorrectColor(obj.close.color)+";";
        str += "text-align: "+obj.close['text-align']+";";
        str += "}";
        str += "body.gridbox .ba-lightbox-backdrop[data-id="+key+"] > .ba-lightbox-close {";
        str += "background-color: "+getCorrectColor(obj.lightbox.background)+";";
        str += "}";
        str += "body:not(.gridbox) .ba-lightbox-backdrop[data-id="+key+"] {";
        str += "background-color: "+getCorrectColor(obj.lightbox.background)+";";
        str += "}";
        $g('#'+key).closest('.ba-lightbox-backdrop')
            .removeClass('lightbox-top-left lightbox-top-right lightbox-center lightbox-bottom-left lightbox-bottom-right')
            .addClass(obj.lightbox.layout);
    } else if (obj.type == 'overlay-section') {
        str += ".ba-overlay-section-backdrop[data-id="+key+"] .close-overlay-section {";
        str += "color: "+getCorrectColor(obj.close.color)+";";
        str += "text-align: "+obj.close['text-align']+";";
        str += "}";
        str += "body.gridbox .ba-overlay-section-backdrop[data-id="+key+"] > .ba-overlay-section-close {";
        str += "background-color: "+getCorrectColor(obj.lightbox.background)+";";
        str += "}";
        str += "body:not(.gridbox) .ba-overlay-section-backdrop[data-id="+key+"] {";
        str += "background-color: "+getCorrectColor(obj.lightbox.background)+";";
        str += "}";
        $g('#'+key).closest('.ba-overlay-section-backdrop')
            .removeClass('vertical-right vertical-left horizontal-top horizontal-bottom lightbox').addClass(obj.lightbox.layout);
    } else if (obj.type == 'cookies') {
        $g('#'+key).closest('.ba-lightbox-backdrop')
            .removeClass('notification-bar-top notification-bar-bottom lightbox-top-left lightbox-top-right lightbox-bottom-left')
            .removeClass('lightbox-bottom-right').addClass(obj.lightbox.position);
    }
    if (obj.parallax) {
        var pHeight = 100 + obj.parallax.offset * 2 * 200,
            pTop = obj.parallax.offset * 2 * -100;
        str += "#"+key+" > .parallax-wrapper.scroll .parallax {";
        str += "height: "+pHeight+"%;"
        str += "top: "+pTop+"%;"
        str += "}";
    }
    if (obj.type == 'column' && obj.sticky && obj.sticky.enable) {
        str += "#"+key+" {";
        str += "top: "+obj.sticky.offset+"px;"
        str += "}";
    }




    app.cssRulesFlag = 'tablet';
    str += app.setMediaRules(obj, key, 'createPageRules');
    if (obj.desktop.background && obj.desktop.background.type != 'video') {
        $g('#'+key+' > .ba-video-background').remove();
    }
    if (obj.type != 'column' && 'fullwidth' in obj.desktop.full) {
        if (!obj.desktop.full.fullwidth) {
            $g('#'+key).parent().addClass('ba-container');
        } else {
            $g('#'+key).parent().removeClass('ba-container');
        }
    }
    if (obj.type == 'row') {
        if (obj.desktop.view.gutter) {
            $g('#'+key).removeClass('no-gutter-desktop');
        } else {
            $g('#'+key).addClass('no-gutter-desktop');
        }
    } else if (obj.type == 'column') {
        var parent = $g('#'+key).parent();
        for (var ind in breakpoints) {
            var name = ind.replace('tablet-portrait', 'ba-tb-pt-').replace('tablet', 'ba-tb-la-')
                .replace('phone-portrait', 'ba-sm-pt-').replace('phone', 'ba-sm-la-');
            if (obj[ind] && obj[ind].span && obj[ind].span.width) {
                for (var i = 1; i <= 12; i++) {
                    parent.removeClass(name+i);
                }
                parent.addClass(name+obj[ind].span.width);
            }
            name += 'order-';
            if (obj[ind] && obj[ind].span && obj[ind].span.order) {
                for (var i = 1; i <= 12; i++) {
                    parent.removeClass(name+i);
                }
                parent.addClass(name+obj[ind].span.order);
            }
        }
    }
    
    return str;
}

function createFooterStyle(obj)
{
    var str = "";
    for (var key in obj) {
        switch(key) {
            case 'links' : 
                str += "body footer a {";
                str += "color : "+getCorrectColor(obj[key].color)+";";
                str += "}";
                str += "body footer a:hover {";
                str += "color : "+getCorrectColor(obj[key]['hover-color'])+";";
                str += "}";
                break;
            case 'body':
                str += "body footer, footer ul, footer ol, footer table, footer blockquote";
                str += " {";
                str += getTypographyRule(obj[key]);
                str += "}";
                break;
            case 'p' :
            case 'h1' :
            case 'h2' :
            case 'h3' :
            case 'h4' :
            case 'h5' :
            case 'h6' :
                str += "footer "+key;
                str += " {";
                str += getTypographyRule(obj[key]);
                str += "}";
                break;
        }
    }
    return str;
}

function createMegaMenuRules(obj, selector)
{
    var str = "#"+selector+" {";
    str += "min-height: 50px;";
    for (var ind in obj.padding) {
        str += 'padding-'+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "border-bottom-width : "+(obj.border.width * obj.border.bottom)+"px;";
    str += "border-color : "+getCorrectColor(obj.border.color)+";";
    str += "border-left-width : "+(obj.border.width * obj.border.left)+"px;";
    str += "border-right-width : "+(obj.border.width * obj.border.right)+"px;";
    str += "border-style : "+obj.border.style+";";
    str += "border-top-width : "+(obj.border.width * obj.border.top)+"px;";
    str += "}";
    str += 'li.deeper > .tabs-content-wrapper[data-id="'+selector+'"] + a > i.zmdi-caret-right {';
    if (obj.disable == 1) {
        str += 'display: none;';
    } else {
        str += 'display: inline-block;';
    }
    str += "}";
    if (obj.background.image.image) {
        str += "#"+selector+" > .parallax-wrapper .parallax {";
        if (obj.background.image.image.indexOf('balbooa.com') != -1) {
            str += "background-image: url("+obj.background.image.image+");";
        } else {
            str += "background-image: url("+JUri+encodeURI(obj.background.image.image)+");";
        }
        str += "}";
    } else {
        str += "#"+selector+" > .parallax-wrapper .parallax {";
        str += "background-image: none;";
        str += "}";
    }
    str += app.backgroundRule(obj, '#'+selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    str += setBoxModel(obj, selector);

    return str;
}

function getFlipboxRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += 'margin-'+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" > .ba-flipbox-wrapper {"
    str += "height: "+obj.view.height+"px;";
    str += "}";
    str += "#"+selector+" > .ba-flipbox-wrapper > .column-wrapper > .ba-grid-column-wrapper > .ba-grid-column {"
    if (obj.full.fullscreen) {
        str += "justify-content: center;";
        str += "min-height: 100vh;";
    } else {
        str += "min-height: 50px;";
    }
    str += "}";
    str += "#"+selector+" > .ba-flipbox-wrapper > .column-wrapper {"
    str += "transition-duration: "+obj.animation.duration+"s;"
    str += "}";
    str += setItemsVisability(obj.disable, "block", "#"+selector);
    str += setBoxModel(obj, selector);

    return str;
}


function getFlipsidesRules(obj, selector)
{
    var str = '#'+selector+" {"
    str += "border-bottom-width : "+(obj.border.width * obj.border.bottom)+"px;";
    str += "border-color : "+getCorrectColor(obj.border.color)+";";
    str += "border-left-width : "+(obj.border.width * obj.border.left)+"px;";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "border-right-width : "+(obj.border.width * obj.border.right)+"px;";
    str += "border-style : "+obj.border.style+";";
    str += "border-top-width : "+(obj.border.width * obj.border.top)+"px;";
    for (var ind in obj.padding) {
        str += 'padding-'+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    str += app.backgroundRule(obj, '#'+selector);

    return str;
}

function createPageRules(obj, selector, type)
{
    var str = "#"+selector+" {";
    for (var key in obj) {
        switch (key) {
            case 'border' : 
                if (obj[key].bottom == 1 && obj[key].width) {
                    str += key+"-bottom-width : "+obj[key].width+"px;";
                } else if (obj[key].bottom == 0) {
                    str += key+"-bottom-width : 0;";
                }
                if (obj[key].color) {
                    str += key+"-color : "+getCorrectColor(obj[key].color)+";";
                }
                if (obj[key].left == 1 && obj[key].width) {
                    str += key+"-left-width : "+obj[key].width+"px;";
                } else if (obj[key].left == 0) {
                    str += key+"-left-width : 0;";
                }
                str += key+"-radius : "+obj[key].radius+"px;";
                if (obj[key].right == 1 && obj[key].width) {
                    str += key+"-right-width : "+obj[key].width+"px;";
                } else if (obj[key].right == 0) {
                    str += key+"-right-width : 0;";
                }
                if (obj[key].style) {
                    str += key+"-style : "+obj[key].style+";";
                }
                if (obj[key].top == 1 && obj[key].width) {
                    str += key+"-top-width : "+obj[key].width+"px;";
                } else if (obj[key].top == 0) {
                    str += key+"-top-width : 0;";
                }
                break;
            case 'animation':
                str += "animation-duration: "+obj.animation.duration+"s;"
                str += "animation-delay: "+obj.animation.delay+"s;"
                if (obj.animation.effect) {
                    str += "opacity: 0;";
                } else {
                    str += "opacity: 1;";
                }
                break;
            case 'full' :
                if (obj[key].fullscreen) {
                    if (type != 'column') {
                        str += "align-items: center;";
                    }
                    str += "justify-content: center;";
                    if (type != 'lightbox') {
                        str += "min-height: 100vh;";
                    } else {
                        str += "min-height: calc(100vh - 50px);";
                    }
                } else {
                    if (obj.view && obj.view.height) {
                        str += "min-height: "+obj.view.height+"px;";
                    } else {
                        str += "min-height: 50px;";
                    }
                }
                break;
            case 'view' :
                if (obj.view.width) {
                    str += "width: "+obj.view.width+"px;";
                }
                break;
            case 'margin' :
            case 'padding' :
                for (var ind in obj[key]) {
                    str += key+'-'+ind+" : "+obj[key][ind]+"px;";
                }
                break;
        }
    }
    str += "}";
    if (obj.full.fullscreen) {
        str += setItemsVisability(obj.disable, "flex", "#"+selector);
    } else {
        str += setItemsVisability(obj.disable, "block", "#"+selector);
    }
    if (obj.disable == 1) {
        str += "body.show-hidden-elements #"+selector+".visible {opacity : 0.3;}";
    } else {
        str += "#"+selector+".visible {opacity : 1;}";
    }
    if (obj.background.image.image || obj.image) {
        str += "#"+selector+" > .parallax-wrapper .parallax {";
        var image = obj.background.image.image;
        if (obj.image && obj.image.image) {
            image = obj.image.image;
        }
        if (image.indexOf('balbooa.com') != -1) {
            str += "background-image: url("+image+");";
        } else {
            str += "background-image: url("+JUri+encodeURI(image)+");";
        }
        str += "}";
    } else {
        str += "#"+selector+" > .parallax-wrapper .parallax {";
        str += "background-image: none;";
        str += "}";
    }
    if (obj.shape) {
        str += getShapeRules(selector, obj.shape.bottom, 'bottom');
        str += getShapeRules(selector, obj.shape.top, 'top');
    }
    str += app.backgroundRule(obj, '#'+selector);
    str += setBoxModel(obj, selector);
    if (type == 'header') {
        str += createHeaderRules(obj, app.cssRulesFlag);
    }
    if (type == 'footer') {
        str += createFooterStyle(obj);
    }

    return str;
}

function getShapeRules(selector, obj, type)
{
    str = "#"+selector+" > .ba-shape-divider.ba-shape-divider-"+type+" {";
    if (obj.effect == 'arrow') {
        var arrow = '';
        arrow += "clip-path: polygon(100% "+(100 - obj.value);
        arrow += "%, 100% 100%, 0 100%, 0 "+(100 - obj.value);
        arrow += "%, "+(50 - obj.value / 2)+"% "+(100 - obj.value)+"%, 50% 100%, "+(50 + obj.value / 2)+"% ";
        arrow += (100 - obj.value)+"%);";
        str += arrow;
    } else if (obj.effect == 'zigzag') {
        var pyramids = "clip-path: polygon(",
            delta = 0,
            delta2 = 100 / (obj.value * 2);
        for (var i = 0; i < obj.value; i++) {
            if (i != 0) {
                pyramids += ",";
            }
            pyramids += delta+"% 100%,";
            pyramids += delta2+"% calc(100% - 15px),";
            delta += 100 / obj.value;
            delta2 += 100 / obj.value;
            pyramids += delta+"% 100%";
        }
        pyramids += ");";
        str += pyramids;
    } else if (obj.effect == 'circle') {
        str += "clip-path: circle("+obj.value+"% at 50% 100%);";
    } else if (obj.effect == 'vertex') {
        str += "clip-path: polygon(20% calc("+(100 - obj.value)+"% + 15%), 35%  calc("+(100 - obj.value);
        str += "% + 45%), 65%  "+(100 - obj.value)+"%, 100% 100%, 100% 100%, 0% 100%, 0  calc(";
        str += (100 - obj.value)+"% + 10%), 10%  calc("+(100 - obj.value)+"% + 30%));";
    } else if (obj.effect != 'arrow' && obj.effect != 'zigzag' &&
        obj.effect != 'circle' && obj.effect != 'vertex') {
        str += "clip-path: none;";
        str += "background: none;";
        str += "color: "+getCorrectColor(obj.color)+";";
    }
    if (obj.effect == 'arrow' || obj.effect == 'zigzag' ||
        obj.effect == 'circle' || obj.effect == 'vertex') {
        str += "background-color: "+getCorrectColor(obj.color)+";";
    }
    if (!obj.effect) {
        str += 'display: none;';
    } else {
        str += 'display: block;';
    }
    str += "}";
    str += "#"+selector+" > .ba-shape-divider.ba-shape-divider-"+type+" svg:not(.shape-divider-"+obj.effect+") {";
    str += "display: none;";
    str += "}";
    str += "#"+selector+" > .ba-shape-divider.ba-shape-divider-"+type+" svg.shape-divider-"+obj.effect+" {";
    str += "display: block;";
    str += "height: "+(obj.value * 10)+"px;";
    str += "}";

    return str;
}

function getOnePageRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .integration-wrapper > ul > li {";
    for (var ind in obj.nav.margin) {
        str += "margin-"+ind+" : "+obj.nav.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" i.ba-menu-item-icon {";
    str += "font-size: "+obj.nav.icon.size+"px;";
    str += "}";
    str += "#"+selector+" .main-menu li a {";
    str += getTypographyRule(obj['nav-typography']);
    str += "color : "+getCorrectColor(obj.nav.normal.color)+";";
    str += "background-color : "+getCorrectColor(obj.nav.normal.background)+";";
    for (var ind in obj.nav.padding) {
        str += "padding-"+ind+" : "+obj.nav.padding[ind]+"px;";
    }
    str += "border-bottom-width : "+(obj.nav.border.width * obj.nav.border.bottom)+"px;";
    str += "border-color : "+getCorrectColor(obj.nav.border.color)+";";
    str += "border-left-width : "+(obj.nav.border.width * obj.nav.border.left)+"px;";
    str += "border-radius : "+obj.nav.border.radius+"px;";
    str += "border-right-width : "+(obj.nav.border.width * obj.nav.border.right)+"px;";
    str += "border-style : "+obj.nav.border.style+";";
    str += "border-top-width : "+(obj.nav.border.width * obj.nav.border.top)+"px;";
    str += "}"
    if (obj.nav.border.left == 1 && obj.nav.border.right == 1 && obj.nav.margin.left == 0 && obj.nav.margin.right == 0) {
        str += "#"+selector+" > .ba-menu-wrapper:not(.vertical-menu) > .main-menu:not(.visible-menu)";
        str += " > .integration-wrapper > ul > li:not(:last-child) > a, #"+selector+"> .ba-menu-wrapper:not(.vertical-menu)";
        str += " > .main-menu:not(.visible-menu) .integration-wrapper > ul > li:not(:last-child) > span {";
        str += "border-right: none";
        str += "}";
    }
    if (obj.nav.border.top == 1 && obj.nav.border.bottom == 1) {
        str += "#"+selector+" > .ba-menu-wrapper.vertical-menu > .main-menu";
        str += " > .integration-wrapper > ul > li:not(:last-child) > a, #"+selector+"> .ba-menu-wrapper.vertical-menu";
        str += " > .main-menu .integration-wrapper > ul > li:not(:last-child) > span, #";
        str += selector+" > .ba-menu-wrapper > .main-menu.visible-menu";
        str += " > .integration-wrapper > ul > li:not(:last-child) > a, #"+selector+"> .ba-menu-wrapper";
        str += " > .main-menu.visible-menu .integration-wrapper > ul > li:not(:last-child) > span {";
        str += "border-bottom: none";
        str += "}";
    }
    str += "#"+selector+" .main-menu li > a:hover {";
    str += "color : "+getCorrectColor(obj.nav.normal.color)+";";
    str += "background-color : "+getCorrectColor(obj.nav.normal.background)+";";
    str += "}";
    str += "#"+selector+" ul {";
    str += "text-align : "+obj['nav-typography']['text-align']+";";
    str += "}"
    str += "#"+selector+" .main-menu li.active > a {";
    str += "color : "+getCorrectColor(obj.nav.hover.color)+";";
    str += "background-color : "+getCorrectColor(obj.nav.hover.background)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getMenuRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li {";
    for (var ind in obj.nav.margin) {
        str += "margin-"+ind+" : "+obj.nav.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li > a > i.ba-menu-item-icon, #";
    str += selector+" .integration-wrapper > ul > li > span > i.ba-menu-item-icon {";
    str += "font-size: "+obj.nav.icon.size+"px;";
    str += "}";
    str += "#"+selector+" > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li > a, #";
    str += selector+" .integration-wrapper > ul > li > span {";
    str += getTypographyRule(obj['nav-typography']);
    str += "color : "+getCorrectColor(obj.nav.normal.color)+";";
    str += "background-color : "+getCorrectColor(obj.nav.normal.background)+";";
    for (var ind in obj.nav.padding) {
        str += "padding-"+ind+" : "+obj.nav.padding[ind]+"px;";
    }
    str += "border-bottom-width : "+(obj.nav.border.width * obj.nav.border.bottom)+"px;";
    str += "border-color : "+getCorrectColor(obj.nav.border.color)+";";
    str += "border-left-width : "+(obj.nav.border.width * obj.nav.border.left)+"px;";
    str += "border-radius : "+obj.nav.border.radius+"px;";
    str += "border-right-width : "+(obj.nav.border.width * obj.nav.border.right)+"px;";
    str += "border-style : "+obj.nav.border.style+";";
    str += "border-top-width : "+(obj.nav.border.width * obj.nav.border.top)+"px;";
    str += "}";
    if (obj.nav.border.left == 1 && obj.nav.border.right == 1 && obj.nav.margin.left == 0 && obj.nav.margin.right == 0) {
        str += "#"+selector+" > .ba-menu-wrapper:not(.vertical-menu) > .main-menu:not(.visible-menu)";
        str += " > .integration-wrapper > ul > li:not(:last-child) > a, #"+selector+"> .ba-menu-wrapper:not(.vertical-menu)";
        str += " > .main-menu:not(.visible-menu) .integration-wrapper > ul > li:not(:last-child) > span {";
        str += "border-right: none";
        str += "}";
    }
    if (obj.nav.border.top == 1 && obj.nav.border.bottom == 1) {
        str += "#"+selector+" > .ba-menu-wrapper.vertical-menu > .main-menu";
        str += " > .integration-wrapper > ul > li:not(:last-child) > a, #"+selector+"> .ba-menu-wrapper.vertical-menu";
        str += " > .main-menu .integration-wrapper > ul > li:not(:last-child) > span, #";
        str += selector+" > .ba-menu-wrapper > .main-menu.visible-menu";
        str += " > .integration-wrapper > ul > li:not(:last-child) > a, #"+selector+"> .ba-menu-wrapper";
        str += " > .main-menu.visible-menu .integration-wrapper > ul > li:not(:last-child) > span {";
        str += "border-bottom: none";
        str += "}";
    }
    str += "#"+selector+" .main-menu .nav-child li i.ba-menu-item-icon {";
    str += "font-size: "+obj.sub.icon.size+"px;";
    str += "}";
    str += "#"+selector+" .main-menu .nav-child li a,#"+selector+" .main-menu .nav-child li span {";
    str += getTypographyRule(obj['sub-typography']);
    str += "color : "+getCorrectColor(obj.sub.normal.color)+";";
    str += "background-color : "+getCorrectColor(obj.sub.normal.background)+";";
    for (var ind in obj.sub.padding) {
        str += "padding-"+ind+" : "+obj.sub.padding[ind]+"px;";
    }
    str += "border-bottom-width : "+(obj.sub.border.width * obj.sub.border.bottom)+"px;";
    str += "border-color : "+getCorrectColor(obj.sub.border.color)+";";
    str += "border-left-width : "+(obj.sub.border.width * obj.sub.border.left)+"px;";
    str += "border-radius : "+obj.sub.border.radius+"px;";
    str += "border-right-width : "+(obj.sub.border.width * obj.sub.border.right)+"px;";
    str += "border-style : "+obj.sub.border.style+";";
    str += "border-top-width : "+(obj.sub.border.width * obj.sub.border.top)+"px;";
    str += "}"
    if (obj.sub.border.top == 1 && obj.sub.border.bottom == 1) {
        str += "#"+selector+" .main-menu .nav-child li:not(:last-child) > a,#";
        str += selector+" .main-menu .nav-child li:not(:last-child) > span {";
        str += "border-bottom: none";
        str += "}";
    }
    let hoverColor = obj.nav.hover.color,
        hoverBackground = obj.nav.hover.background;
    if (app.breakpoint != 'desktop' && app.breakpoint != 'laptop') {
        hoverColor = obj.nav.normal.color,
        hoverBackground = obj.nav.normal.background;
    }
    str += "#"+selector+" > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li a:hover,#";
    str += selector+" .main-menu li > span:hover {";
    str += "color : "+getCorrectColor(hoverColor)+";";
    str += "background-color : "+getCorrectColor(hoverBackground)+";";
    str += "}";
    hoverColor = obj.sub.hover.color,
    hoverBackground = obj.sub.hover.background;
    if (app.breakpoint != 'desktop' || app.breakpoint != 'laptop') {
        hoverColor = obj.sub.normal.color,
        hoverBackground = obj.sub.normal.background;
    }
    str += "#"+selector+" .main-menu .nav-child li a:hover,#"+selector+" .main-menu .nav-child li span:hover {";
    str += "color : "+getCorrectColor(hoverColor)+";";
    str += "background-color : "+getCorrectColor(hoverBackground)+";";
    str += "}"
    str += "#"+selector+" > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul {";
    str += "text-align : "+obj['nav-typography']['text-align']+";";
    str += "}"
    str += "#"+selector+" > .ba-menu-wrapper > .main-menu > .integration-wrapper > ul > li.active > a,#";
    str += selector+" .main-menu li.active > span {";
    str += "color : "+getCorrectColor(obj.nav.hover.color)+";";
    str += "background-color : "+getCorrectColor(obj.nav.hover.background)+";";
    str += "}";
    str += "#"+selector+" .main-menu .nav-child li.active > a,#"+selector+" .main-menu .nav-child li.active > span {";
    str += "color : "+getCorrectColor(obj.sub.hover.color)+";";
    str += "background-color : "+getCorrectColor(obj.sub.hover.background)+";";
    str += "}";
    str += "#"+selector+" ul.nav-child {";
    for (var ind in obj.dropdown.padding) {
        str += "padding-"+ind+" : "+obj.dropdown.padding[ind]+"px;";
    }
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getWeatherRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .weather .city {";
    str += getTypographyRule(obj.city);
    str += "}";
    str += "#"+selector+" .weather .condition {";
    str += getTypographyRule(obj.condition);
    str += "}";
    str += "#"+selector+" .weather-info > div,#"+selector+" .weather .date {";
    str += getTypographyRule(obj.info);
    str += "}";
    str += "#"+selector+" .forecast > span {";
    str += getTypographyRule(obj.forecasts);
    str += "}";
    str += "#"+selector+" .weather-info .wind {";
    if (obj.view.wind) {
        str += "display : inline;";
    } else {
        str += "display : none;";
    }
    str += "}";
    str += "#"+selector+" .weather-info .humidity {";
    if (obj.view.humidity) {
        str += "display : inline-block;";
    } else {
        str += "display : none;";
    }
    str += "}";
    str += "#"+selector+" .weather-info .pressure {";
    if (obj.view.pressure) {
        str += "display : inline-block;";
    } else {
        str += "display : none;";
    }
    str += "}";
    str += "#"+selector+" .weather-info .sunrise-wrapper {";
    if (obj.view['sunrise-wrapper']) {
        str += "display : block;";
    } else {
        str += "display : none;";
    }
    str += "}";
    if (obj.view.layout == 'forecast-block') {
        str += '#'+selector+' .forecast > span {display: block;width: initial;}';
        str += '#'+selector+' .weather-info + div {text-align: center;}';
        str += '#'+selector+' .ba-weather div.forecast {margin: 0 20px 0 10px;}';
        str += '#'+selector+' .ba-weather div.forecast .day-temp,';
        str += '#'+selector+' .ba-weather div.forecast .night-temp {margin: 0 5px;}';
        str += '#'+selector+' .ba-weather div.forecast span.night-temp,';
        str += '#'+selector+' .ba-weather div.forecast span.day-temp {padding-right: 0;width: initial;}';
    } else {
        str += '#'+selector+' .forecast > span {display: inline-block;width: 33.3%;}';
        str += '#'+selector+' .weather-info + div {text-align: left;}';
        str += '#'+selector+' .ba-weather div.forecast .day-temp,';
        str += '#'+selector+' .ba-weather div.forecast .night-temp {margin: 0;}';
        str += '#'+selector+' .ba-weather div.forecast {margin: 0;}';
        str += '#'+selector+' .ba-weather div.forecast span.night-temp,';
        str += '#'+selector+' .ba-weather div.forecast span.day-temp {padding-right: 1.5%;width: 14%;}';
    }
    str += "#"+selector+" .forecast:nth-child(n) {";
    str += "display : none;";
    str += "}";
    for (var i = 0; i < obj.view.forecast; i++) {
        str += "#"+selector+" .forecast:nth-child("+(i + 1)+")";
        if (i != obj.view.forecast - 1 ){
            str += ","
        }
    }
    str += " {";
    if (obj.view.layout == 'forecast-block') {
        str += "display: inline-block;";
    } else {
        str += "display: block;";
    }
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getAccordionRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .accordion-group, #"+selector+" .accordion-inner {";
    str += "border-color: "+getCorrectColor(obj.border.color)+";"; 
    str += "}";
    str += "#"+selector+" .accordion-inner {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "background-color: "+getCorrectColor(obj.background.color)+";";
    str += "}";
    str += "#"+selector+" .accordion-heading a {";
    str += getTypographyRule(obj.typography, 'text-decoration');
    str += "}";
    if (obj.typography['text-decoration']) {
        str += "#"+selector+" .accordion-heading span.accordion-title {";
        str += "text-decoration: "+obj.typography['text-decoration']+";";
        str += "}";
    }
    str += "#"+selector+" .accordion-heading a i {";
    str += "font-size: "+obj.icon.size+"px;";
    str += "}";
    str += "#"+selector+" .accordion-heading {";
    str += "background-color: "+getCorrectColor(obj.header.color)+";";
    str += "}";
    if (obj.icon.position == 'icon-position-left') {
        str += "#"+selector+' .accordion-toggle > span {flex-direction: row-reverse;}';
    } else {
        str += "#"+selector+' .accordion-toggle > span {flex-direction: row;}';
    }
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getSimpleGalleryRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-instagram-image {"
    if (obj.border) {
        str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
        str += "border-radius : "+obj.border.radius+"px;";
    }
    str += "}";
    str += "#"+selector+" .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image {";
    if (obj.gutter) {
        str += "width: calc((100% / "+obj.count+") - "+((obj.count * 10 - 10) / obj.count)+"px);";
    } else {
        str += "width: calc(100% / "+obj.count+");";
    }
    str += "height: "+obj.view.height+"px;";
    str += "}";
    str += "#"+selector+" .simple-gallery-masonry-layout {";
    str += "grid-template-columns: repeat(auto-fill, minmax(calc((100% / "+obj.count+") - 20px),1fr));";
    str += "}";

    str += "#"+selector+" .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:nth-child(n) {";
    str += "margin-top: "+(obj.gutter ? 10 : 0)+"px;";
    str += "}";
    for (var i = 0; i < obj.count; i++) {
        str += "#"+selector+" .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:nth-child("+(i + 1)+") {";
        str += "margin-top: 0;";
        str += "}";
    }
    str += "#"+selector+" .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:not(:nth-child("+obj.count+"n)) {";
    str += "margin-right: "+(obj.gutter ? 5 : 0)+"px;";
    str += "}";
    str += "#"+selector+" .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:not(:nth-child("+obj.count+"n + 1)) {";
    str += "margin-left: "+(obj.gutter ? 5 : 0)+"px;";
    str += "}";
    str += "#"+selector+" .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:nth-child("+obj.count+"n) {";
    str += "margin-right: 0;";
    str += "}";
    str += "#"+selector+" .instagram-wrapper:not(.simple-gallery-masonry-layout) .ba-instagram-image:nth-child("+obj.count+"n + 1) {";
    str += "margin-left: 0;";
    str += "}";
    if (obj.overlay) {
        str += "#"+selector+" .ba-instagram-image > * {";
        str += "transition-duration: "+obj.animation.duration+"s;"
        str += "}";
        str += "#"+selector+" .ba-simple-gallery-caption .ba-caption-overlay {background-color :";
        if (!obj.overlay.type || obj.overlay.type == 'color') {
            str += getCorrectColor(obj.overlay.color)+";";
            str += 'background-image: none;';
        } else if (obj.overlay.type == 'none') {
            str += 'rgba(0, 0, 0, 0);';
            str += 'background-image: none;';
        } else {
            str += 'rgba(0, 0, 0, 0);';
            str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
            if (obj.overlay.gradient.effect == 'linear') {
                str += obj.overlay.gradient.angle+'deg';
            } else {
                str += 'circle';
            }
            str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
            str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
            str += ' '+obj.overlay.gradient.position2+'%);';
            str += 'background-attachment: scroll;';
        }
        str += "}";
        str += "#"+selector+" .ba-simple-gallery-title {";
        str += getTypographyRule(obj.title.typography);
        for (var ind in obj.title.margin) {
            str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
        }
        str += "}";
        str += "#"+selector+" .ba-simple-gallery-description {";
        str += getTypographyRule(obj.description.typography);
        for (var ind in obj.description.margin) {
            str += "margin-"+ind+" : "+obj.description.margin[ind]+"px;";
        }
        str += "}";
    }
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getErrorRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" h1.ba-error-code {";
    str += getTypographyRule(obj.code.typography, '');
    for (var ind in obj.code.margin) {
        str += "margin-"+ind+" : "+obj.code.margin[ind]+"px;";
    }
    str += "display: "+(obj.view.code ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" p.ba-error-message {";
    str += getTypographyRule(obj.message.typography, '');
    for (var ind in obj.message.margin) {
        str += "margin-"+ind+" : "+obj.message.margin[ind]+"px;";
    }
    str += "display: "+(obj.view.message ? "block" : "none")+";";
    str += "}";    
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getSearchHeadlineRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .search-result-headline-wrapper > * {"
    str += getTypographyRule(obj.typography);
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getTextRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    var array = new Array('p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6');
    array.forEach(function(el){
        if (obj[el]['font-style'] && obj[el]['font-style'] == '@default') {
            delete(obj[el]['font-style']);
        }
        str += "#"+selector+" "+el+" {";
        str += getTypographyRule(obj[el], '', el);
        if (obj.animation) {
            str += 'animation-duration: '+obj.animation.duration+'s;';
        }
        str += "}";
    });
    if (obj.links && obj.links.color) {
        str += "#"+selector+' a {';
        str += 'color:'+getCorrectColor(obj.links.color)+';'
        str += '}';
    }
    if (obj.links && obj.links['hover-color']) {
        str += "#"+selector+' a:hover {';
        str += 'color:'+getCorrectColor(obj.links['hover-color'])+';'
        str += '}';
    }
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getProgressPieRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-progress-pie {";
    str += 'width: '+obj.view.width+'px;';
    str += getTypographyRule(obj.typography);
    str += "}";
    str += "#"+selector+" .ba-progress-pie canvas {";
    str += 'width: '+obj.view.width+'px;';
    str += "}";
    str += "#"+selector+" .progress-pie-number {display: ";
    if (obj.display.target) {
        str += 'inline-block;';
    } else {
        str += 'none;';
    }
    str += "}";
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    str += setBoxModel(obj, selector);

    return str;
}

function getProgressBarRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-progress-bar {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += 'height: '+obj.view.height+'px;';
    str += "background-color: "+getCorrectColor(obj.view.background)+";";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "}";
    str += "#"+selector+" .ba-animated-bar {";
    str += "background-color: "+getCorrectColor(obj.view.bar)+";";
    str += getTypographyRule(obj.typography);
    str += "}";
    str += "#"+selector+" .progress-bar-title {display: ";
    if (obj.display.label) {
        str += 'inline-block;';
    } else {
        str += 'none;';
    }
    str += "}";
    str += "#"+selector+" .progress-bar-number {display: ";
    if (obj.display.target) {
        str += 'inline-block;';
    } else {
        str += 'none;';
    }
    str += "}";
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    str += setBoxModel(obj, selector);

    return str;
}

function getEventCalendarRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-event-calendar-title-wrapper {";
    str += getTypographyRule(obj.months.typography);
    str += "}";
    str += "#"+selector+" .ba-event-calendar-header * {";
    str += getTypographyRule(obj.weeks.typography);
    str += "}";
    str += "#"+selector+" .ba-event-calendar-body * {";
    str += getTypographyRule(obj.days.typography);
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getCommentsBoxRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-comment-message, #"+selector+" .user-comment-wrapper {";
    str += "background-color: "+getCorrectColor(obj.background.color)+";";
    str += "border-top-width : "+obj.border.width * obj.border.top+"px;";
    str += "border-right-width : "+obj.border.width * obj.border.right+"px;";
    str += "border-bottom-width : "+obj.border.width * obj.border.bottom+"px;";
    str += "border-left-width : "+obj.border.width * obj.border.left+"px;";
    str += "border-color : "+getCorrectColor(obj.border.color)+";";
    str += "border-style : "+obj.border.style+";";
    str += "border-radius : "+obj.border.radius+"px;";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += "}";
    str += "#"+selector+" .comment-message, #"+selector+" .ba-comment-message::placeholder, ";
    str += "#"+selector+" .ba-comments-total-count-wrapper select, #"+selector+" .ba-comment-message, ";
    str += "#"+selector+" .comment-delete-action, #"+selector+" .comment-edit-action, ";
    str += "#"+selector+" .comment-likes-action-wrapper > span > span, ";
    str += "#"+selector+" .ba-review-rate-title, ";
    str += "#"+selector+" span.ba-comment-attachment-trigger, ";
    str += "#"+selector+" .comment-likes-wrapper .comment-action-wrapper > span.comment-reply-action > span, ";
    str += "#"+selector+" .comment-likes-wrapper .comment-action-wrapper > span.comment-share-action > span, ";
    str += "#"+selector+" .comment-user-date, #"+selector+" .ba-social-login-wrapper > span, ";
    str += "#"+selector+" .ba-user-login-btn, #"+selector+" .ba-guest-login-btn, #"+selector+" .comment-logout-action, ";
    str += "#"+selector+" .comment-user-name, #"+selector+" .ba-comments-total-count {";
    str += getTypographyRule(obj.typography);
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getFieldRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-field-wrapper {";
    str += "border-top-width : "+obj.border.width * obj.border.top+"px;";
    str += "border-right-width : "+obj.border.width * obj.border.right+"px;";
    str += "border-bottom-width : "+obj.border.width * obj.border.bottom+"px;";
    str += "border-left-width : "+obj.border.width * obj.border.left+"px;";
    str += "border-color : "+getCorrectColor(obj.border.color)+";";
    str += "border-style : "+obj.border.style+";";
    str += "border-radius : "+obj.border.radius+"px;";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-field-label, #"+selector+" .ba-field-label *:not(i):not(.ba-tooltip) {";
    str += getTypographyRule(obj.title.typography);
    str += "}";
    str += "#"+selector+" .ba-field-label i {";
    str += "color : "+getCorrectColor(obj.icons.color)+";";
    str += "font-size : "+obj.icons.size+"px;";
    str += "}";
    str += "#"+selector+" .ba-field-content {";
    str += getTypographyRule(obj.value.typography);
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getFieldsFilterRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "background-color: "+getCorrectColor(obj.background.color)+";";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-field-filter-label, #"+selector+" .ba-selected-filter-values-title {";
    str += getTypographyRule(obj.title.typography);
    str += "}";
    str += "#"+selector+" .ba-field-filter-value-wrapper, #"+selector+" .ba-selected-filter-values-remove-all {";
    str += getTypographyRule(obj.value.typography);
    str += '--filter-value-line-height: '+obj.value.typography['line-height']+'px;'
    str += "}";
    let justify = obj.value.typography['text-align'].replace('right', 'flex-start').replace('left', 'flex-end');
    str += "#"+selector+" .ba-checkbox-wrapper {";
    str += "justify-content: "+justify+";";
    str += "}";
    str += "#"+selector+" .ba-field-filter {";
    str += "display: none;"
    str += "}";
    let visibleField = null;
    for (let i = 0; i < app.blogPostsFields.length; i++) {
        if (obj.fields[app.blogPostsFields[i]]) {
           visibleField = app.blogPostsFields[i];
        }
        str += '#'+selector+' .ba-field-filter[data-id="'+app.blogPostsFields[i]+'"] {';
        str += "display: "+(obj.fields[app.blogPostsFields[i]] ? 'flex' : 'none')+";";
        str += "}";
    }
    if (visibleField) {
        str += '#'+selector+' .ba-field-filter[data-id="'+visibleField+'"] {';
        str += "margin-bottom: 0;";
        str += "}";
    }
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getModulesRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}



function getTabsRules(obj, selector)
{
    var str = "#"+selector+" {",
        align = obj.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .tab-content {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "background-color: "+getCorrectColor(obj.background.color)+";";
    str += "}";
    str += "#"+selector+" ul.nav.nav-tabs li a {";
    str += getTypographyRule(obj.typography, 'text-decoration');
    str += 'align-items:'+align+';';
    str += "}";
    if (obj.typography['text-decoration']) {
        str += "#"+selector+" li span.tabs-title {";
        str += "text-decoration : "+obj.typography['text-decoration']+";";
        str += "}";
    }
    str += "#"+selector+" ul.nav.nav-tabs li a i {";
    str += "font-size: "+obj.icon.size+"px;";
    str += "}";
    str += "#"+selector+" ul.nav.nav-tabs li.active a {";
    str += "color : "+getCorrectColor(obj.hover.color)+";";
    str += "}";
    str += "#"+selector+" ul.nav.nav-tabs li.active a:before {";
    str += "background-color : "+getCorrectColor(obj.hover.color)+";";
    str += "}";
    str += "#"+selector+" ul.nav.nav-tabs {";
    str += "background-color: "+getCorrectColor(obj.header.color)+";";
    str += "border-color: "+getCorrectColor(obj.header.border)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getCounterRules(obj, selector)
{
    var str = "#"+selector+" .ba-counter span.counter-number {";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "background-color: "+getCorrectColor(obj.background.color)+";";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += getTypographyRule(obj.counter, 'text-align');
    str += "width : "+obj.counter['line-height']+"px;";
    str += "}";
    str += "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "text-align : "+obj.counter['text-align']+";"
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getCountdownRules(obj, selector)
{
    var str = "#"+selector+" .ba-countdown > span {";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "background-color: "+getCorrectColor(obj.background.color)+";";
    str += "}";
    str += "#"+selector+" {";
    for (var ind in obj.margin) {
        str += 'margin-'+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .countdown-time {";
    str += getTypographyRule(obj.counter);
    str += "}";
    str += "#"+selector+" .countdown-label {";
    str += getTypographyRule(obj.label);
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getSearchRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}"
    str += "#"+selector+" .ba-search-wrapper input::-webkit-input-placeholder {";
    str += getTypographyRule(obj.typography);
    str += "}";
    str += "#"+selector+" .ba-search-wrapper input::-moz-placeholder {";
    str += getTypographyRule(obj.typography);
    str += "}";
    str += "#"+selector+" .ba-search-wrapper input {";
    str += getTypographyRule(obj.typography);
    str += "height : "+obj.typography['line-height']+"px;";
    str += "}";
    str += "#"+selector+" .ba-search-wrapper {";
    if (obj.border.bottom == 1) {
        str += "border-bottom-width : "+obj.border.width+"px;";
    } else {
        str += "border-bottom-width : 0;";
    }
    str += "border-color : "+getCorrectColor(obj.border.color)+";";
    if (obj.border.left == 1) {
        str += "border-left-width : "+obj.border.width+"px;";
    } else {
        str += "border-left-width : 0;";
    }
    str += "border-radius : "+obj.border.radius+"px;";
    if (obj.border.right == 1) {
        str += "border-right-width : "+obj.border.width+"px;";
    } else {
        str += "border-right-width : 0;";
    }
    str += "border-style : "+obj.border.style+";";
    if (obj.border.top == 1) {
        str += "border-top-width : "+obj.border.width+"px;";
    } else {
        str += "border-top-width : 0;";
    }
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    if (obj.icons && obj.icons.size) {
        str += "#"+selector+" .ba-search-wrapper i {";
        str += "color: "+getCorrectColor(obj.typography.color)+";";
        str += "font-size : "+obj.icons.size+"px;";
        str += "}";
    }
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getCheckoutFormRules(obj, selector)
{
    var str = "#"+selector+" {"
    for (var ind in obj.margin) {
        str += "--margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-checkout-form-title-wrapper, .ba-item-checkout-order-form {"
    str += getTypographyRule(obj.title.typography, '', '', true, '--title');
    if (obj.headline) {
        str += getTypographyRule(obj.headline.typography, '', '', true, '--headline');
    }
    str += "}";
    str += "#"+selector+" .ba-checkout-form-field-wrapper, .ba-item-checkout-order-form {"
    str += "--background-color: "+getCorrectColor(obj.field.background.color)+";";
    str += "--border-bottom-width: "+(obj.field.border.width * Number(obj.field.border.bottom))+"px;";
    str += "--border-color: "+getCorrectColor(obj.field.border.color)+";";
    str += "--border-left-width: "+(obj.field.border.width * Number(obj.field.border.left))+"px;";
    str += "--border-radius: "+obj.field.border.radius+"px;";
    str += "--border-right-width: "+(obj.field.border.width * Number(obj.field.border.right))+"px;";
    str += "--border-style: "+obj.field.border.style+";";
    str += "--border-top-width: "+(obj.field.border.width * Number(obj.field.border.top))+"px;";
    str += getTypographyRule(obj.field.typography, '', '', true, '--field');
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getIconListRules(obj, selector)
{
    var str = "#"+selector+" {",
        align  = obj.body['text-align'];
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-icon-list-wrapper ul {";
    str += "align-items: "+align.replace('left', 'flex-start').replace('right', 'flex-end')+";";
    str += "justify-content: "+align.replace('left', 'flex-start').replace('right', 'flex-end')+";";
    str += "}";
    if (obj.padding) {
        str += "#"+selector+" .ba-icon-list-wrapper ul li {";
        str += "background-color:"+getCorrectColor(obj.background.color)+';';
        str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
        str += "border-radius : "+obj.border.radius+"px;";
        str += "box-shadow: 0 "+(obj.shadow.value * 10);
        str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
        for (var ind in obj.padding) {
            str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
        }
        str += "}";
    }
    str += "#"+selector+" .ba-icon-list-wrapper ul li span {";
    str += getTypographyRule(obj.body);
    str += "}";
    str += "#"+selector+" .ba-icon-list-wrapper ul li {";
    if (obj.body['line-height']) {
        str += '--icon-list-line-height: '+obj.body['line-height']+'px;';
    }
    str += "}";
    str += "#"+selector+" .ba-icon-list-wrapper ul li i, #"+selector+" ul li a:before, #";
    str += selector+" ul li.list-item-without-link:before {";
    str += "color: "+getCorrectColor(obj.icons.color)+";";
    str += "font-size: "+obj.icons.size+"px;";
    if (obj.icons.background) {
        str += "background-color: "+getCorrectColor(obj.icons.background)+";";
        str += "padding: "+obj.icons.padding+"px;";
        str += "border-radius: "+obj.icons.radius+"px;";
    }
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getButtonRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-button-wrapper {";
    str += "text-align: "+obj.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-button-wrapper a span {";
    str += getTypographyRule(obj.typography);
    str += "}";
    str += "#"+selector+" .ba-button-wrapper a {";
    str += "color : "+getCorrectColor(obj.normal.color)+";";
    str += "background-color : "+getCorrectColor(obj.normal['background-color'])+";";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    if (obj.icons && obj.icons.size) {
        str += "#"+selector+" .ba-button-wrapper a i {";
        str += "font-size : "+obj.icons.size+"px;"
        str += "}";
    }
    if (obj.icons && ('position' in obj.icons)) {
        str += "#"+selector+" .ba-button-wrapper a {";
        if (obj.icons.position == '') {
            str += 'flex-direction: row-reverse;';
        } else {
            str += 'flex-direction: row;';
        }
        str += "}";
        if (obj.icons.position == '') {
            str += "#"+selector+" .ba-button-wrapper a i {";
            str += 'margin: 0 10px 0 0;';
            str += "}";
        } else {
            str += "#"+selector+" .ba-button-wrapper a i {";
            str += 'margin: 0 0 0 10px;';
            str += "}";
        }
    }
    if (obj.view && 'subtotal' in obj.view) {
        str += "#"+selector+" .ba-button-wrapper a span.ba-cart-subtotal {";
        str += 'display: '+(obj.view.subtotal ? 'flex' : 'none')+';';
        str += "}";
    }
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getCategoriesRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-masonry-layout {";
    str += "grid-template-columns: repeat(auto-fill, minmax(calc((100% / "+obj.view.count+") - 21px),1fr));";
    str += "}";
    str += "#"+selector+" .ba-grid-layout .ba-blog-post, #"+selector+" .ba-classic-layout .ba-blog-post {";
    str += "width: calc((100% / "+obj.view.count+") - 21px);";
    str += "}";
    if (obj.view.gutter) {
        str += "#"+selector+" .ba-cover-layout .ba-blog-post {";
        str += "margin-left: 10px;margin-right: 10px;";
        str += "width: calc((100% / "+obj.view.count+") - 21px);";
        str += "}";
        str += "#"+selector+" .ba-cover-layout {margin-left: -10px;margin-right: -10px;}";
    } else {
        str += "#"+selector+" .ba-cover-layout .ba-blog-post {";
        str += "margin-left: 0;margin-right: 0;";
        str += "width: calc(100% / "+obj.view.count+");";
        str += "}";
        str += "#"+selector+" .ba-cover-layout {margin-left: 0;margin-right: 0;}";
    }
    str += "#"+selector+" .ba-grid-layout .ba-blog-post:nth-child(n) {";
    str += "margin-top: 30px;";
    str += "}";
    str += "#"+selector+" .ba-classic-layout .ba-blog-post:nth-child(n) {";
    str += "margin-top: "+(obj.view.image ? 30 : 0)+"px;";
    str += "}";
    for (var i = 0; i < obj.view.count; i++) {
        str += "#"+selector+" .ba-grid-layout .ba-blog-post:nth-child("+(i + 1)+"), #";
        str += selector+" .ba-classic-layout .ba-blog-post:nth-child("+(i + 1)+") {";
        str += "margin-top: 0;";
        str += "}";
    }
    str += "#"+selector+" .ba-cover-layout .ba-blog-post:nth-child(n) {";
    str += "margin-top: "+(obj.view.gutter ? 30 : 0)+"px;";
    str += "}";
    for (var i = 0; i < obj.view.count; i++) {
        str += "#"+selector+" .ba-cover-layout .ba-blog-post:nth-child("+(i + 1)+") {";
        str += "margin-top: 0;";
        str += "}";
    }
    str += "#"+selector+" .ba-overlay {background-color:"
    if (!obj.overlay.type || obj.overlay.type == 'color') {
        str += getCorrectColor(obj.overlay.color)+";";
        str += 'background-image: none;';
    } else if (obj.overlay.type == 'none') {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: none;';
    } else {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
        if (obj.overlay.gradient.effect == 'linear') {
            str += obj.overlay.gradient.angle+'deg';
        } else {
            str += 'circle';
        }
        str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
        str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
        str += ' '+obj.overlay.gradient.position2+'%);';
        str += 'background-attachment: scroll;';
    }
    str += '}';
    str += "#"+selector+" .ba-blog-post {";
    str += "background-color:"+getCorrectColor(obj.background.color)+';';
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-image {";
    str += "border : "+obj.image.border.width+"px "+obj.image.border.style+" "+getCorrectColor(obj.image.border.color)+";";
    str += "border-radius : "+obj.image.border.radius+"px;";
    str += "width :"+obj.image.width+"px;";
    str += "height :"+obj.image.height+"px;";
    str += "background-size: "+obj.image.size+";";
    str += "display:"+(obj.view.image ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-masonry-layout .ba-blog-post-image {";
    str += "width :100%;";
    str += "height :auto;";
    str += "}";
    str += "#"+selector+" .ba-cover-layout .ba-blog-post {";
    str += "height :"+obj.image.height+"px;";
    str += "}";
    str += "#"+selector+" .ba-blog-post-title {";
    str += "display:"+(obj.view.title ? "block" : "none")+";";
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += getTypographyRule(obj.title.typography);
    str += "}";
    let justify = obj.info.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    str += "#"+selector+" .ba-blog-post-info-wrapper {";
    str += "display:"+(obj.view.sub ? "block" : "none")+";";
    for (var ind in obj.info.margin) {
        str += "margin-"+ind+" : "+obj.info.margin[ind]+"px;";
    }
    str += "justify-content :"+justify+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-info-wrapper > * {";
    str += getTypographyRule(obj.info.typography, 'text-align');
    str += "}";
    str += "#"+selector+" .ba-blog-post-intro-wrapper {";
    str += "display:"+(obj.view.intro ? "block" : "none")+";";
    str += getTypographyRule(obj.intro.typography);
    for (var ind in obj.intro.margin) {
        str += "margin-"+ind+" : "+obj.intro.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-app-category-counter {";
    str += "display:"+(obj.view.counter ? "inline" : "none")+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getAddToCartRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-add-to-cart-stock {";
    str += "display:"+(obj.view.availability ? "flex" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-add-to-cart-sku {";
    str += "display:"+(obj.view.sku ? "flex" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-add-to-cart-quantity {";
    str += "display:"+(obj.view.quantity ? "flex" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-add-to-cart-button-wrapper a {";
    str += "display:"+(obj.view.button ? "flex" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-add-to-wishlist {";
    str += "display:"+(obj.view.wishlist ? "flex" : "none")+";";
    str += "}";
    let justify = obj.price.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    str += "#"+selector+" .ba-add-to-cart-price {";
    str += "align-items :"+justify+";";
    str += "justify-content :"+justify+";";
    for (var ind in obj.price.margin) {
        str += "margin-"+ind+" : "+obj.price.margin[ind]+"px;";
    }
    str += getTypographyRule(obj.price.typography, 'text-align');
    str += "}";
    str += "#"+selector+" .ba-add-to-cart-info {";
    for (var ind in obj.info.margin) {
        str += "margin-"+ind+" : "+obj.info.margin[ind]+"px;";
    }
    str += getTypographyRule(obj.info.typography);
    str += "}";
    let family = obj.button.typography['font-family'];
    if (family == '@default') {
        family = getTextParentFamily(app.theme.desktop, 'body');
    }
    str += "#"+selector+" .ba-add-to-cart-quantity {";
    str += "font-family: '"+family.replace(/\+/g, ' ')+"';";
    str += 'font-size: '+obj.button.typography['font-size']+'px;';
    str += 'letter-spacing: '+obj.button.typography['letter-spacing']+'px;';
    str += 'color: '+getCorrectColor(obj.price.typography.color)+';';
    str += "}";
    justify = obj.button.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    str += "#"+selector+" .ba-add-to-cart-button-wrapper {";
    str += "justify-content :"+justify+";";
    for (var ind in obj.button.margin) {
        str += "margin-"+ind+" : "+obj.button.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-add-to-cart-buttons-wrapper {";
    str += "background-color: "+getCorrectColor(obj.button.normal.background)+";";
    str += "border-color : "+getCorrectColor(obj.button.border.color)+";";
    str += "border-style : "+obj.button.border.style+";";
    str += "--border-width : "+obj.button.border.width+"px;";
    str += "--border-radius : "+obj.button.border.radius+"px;";
    str += "--display-wishlist: "+(obj.view.wishlist ? 0 : 1)+";"
    str += "box-shadow: 0 "+(obj.button.shadow.value * 10);
    str += "px "+(obj.button.shadow.value * 20)+"px 0 "+getCorrectColor(obj.button.shadow.color)+";";
    for (var ind in obj.button.padding) {
        str += "--padding-"+ind+" : "+obj.button.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-add-to-cart-button-wrapper a, #"+selector+" .ba-add-to-wishlist {";
    str += getTypographyRule(obj.button.typography, 'text-align');
    str += "background-color: "+getCorrectColor(obj.button.normal.background)+";";
    str += "color: "+getCorrectColor(obj.button.normal.color)+";";
    str += "}";
    str += "#"+selector+" .ba-add-to-cart-button-wrapper a:hover, #"+selector+" .ba-add-to-wishlist:hover {";
    str += "background-color: "+getCorrectColor(obj.button.hover.background)+";";
    str += "color: "+getCorrectColor(obj.button.hover.color)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getBlogPostsRules(obj, selector, type)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-masonry-layout {";
    str += "grid-template-columns: repeat(auto-fill, minmax(calc((100% / "+obj.view.count+") - 21px),1fr));";
    str += "}";
    str += "#"+selector+" .ba-grid-layout .ba-blog-post {";
    str += "width: calc((100% / "+obj.view.count+") - 21px);";
    str += "}";
    str += "#"+selector+" .ba-one-column-grid-layout .ba-blog-post {";
    str += "width: calc(100% - 21px);";
    str += "}";
    if (obj.view.gutter) {
        str += "#"+selector+" .ba-cover-layout .ba-blog-post {";
        str += "margin-left: 10px;margin-right: 10px;";
        str += "width: calc((100% / "+obj.view.count+") - 21px);";
        str += "}";
        str += "#"+selector+" .ba-cover-layout {margin-left: -10px;margin-right: -10px;}";
    } else {
        str += "#"+selector+" .ba-cover-layout .ba-blog-post {";
        str += "margin-left: 0;margin-right: 0;";
        str += "width: calc(100% / "+obj.view.count+");";
        str += "}";
        str += "#"+selector+" .ba-cover-layout {margin-left: 0;margin-right: 0;}";
    }
    str += "#"+selector+" .ba-grid-layout .ba-blog-post:nth-child(n) {";
    str += "margin-top: 30px;";
    str += "}";
    for (var i = 0; i < obj.view.count; i++) {
        str += "#"+selector+" .ba-grid-layout .ba-blog-post:nth-child("+(i + 1)+") {";
        str += "margin-top: 0;";
        str += "}";
    }
    str += "#"+selector+" .ba-cover-layout .ba-blog-post:nth-child(n) {";
    str += "margin-top: "+(obj.view.gutter ? 30 : 0)+"px;";
    str += "}";
    for (var i = 0; i < obj.view.count; i++) {
        str += "#"+selector+" .ba-cover-layout .ba-blog-post:nth-child("+(i + 1)+") {";
        str += "margin-top: 0;";
        str += "}";
    }
    str += "#"+selector+" .ba-overlay {background-color:"
    if (!obj.overlay.type || obj.overlay.type == 'color') {
        str += getCorrectColor(obj.overlay.color)+";";
        str += 'background-image: none;';
    } else if (obj.overlay.type == 'none') {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: none;';
    } else {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
        if (obj.overlay.gradient.effect == 'linear') {
            str += obj.overlay.gradient.angle+'deg';
        } else {
            str += 'circle';
        }
        str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
        str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
        str += ' '+obj.overlay.gradient.position2+'%);';
        str += 'background-attachment: scroll;';
    }
    str += '}';
    if (obj.background) {
        str += "#"+selector+" .ba-blog-post {";
        str += "background-color:"+getCorrectColor(obj.background.color)+';';
        str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
        str += "border-radius : "+obj.border.radius+"px;";
        str += "box-shadow: 0 "+(obj.shadow.value * 10);
        str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
        str += "}";
    }
    if (obj.image.border) {
        str += "#"+selector+" .ba-blog-post-image {";
        str += "border : "+obj.image.border.width+"px "+obj.image.border.style+" "+getCorrectColor(obj.image.border.color)+";";
        str += "border-radius : "+obj.image.border.radius+"px;";
        str += "}";
    }
    if (obj.padding) {
        str += "#"+selector+" .ba-blog-post {";
        for (var ind in obj.padding) {
            str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
        }
        str += "}";
    }
    if (!('author' in obj.view)) {
        obj.view.author = false;
    }
    if (!('comments' in obj.view)) {
        obj.view.comments = false;
    }
    if (!('reviews' in obj.view)) {
        obj.view.reviews = false;
    }
    str += "#"+selector+" .blog-posts-sorting-wrapper {";
    str += "display:"+(obj.view.sorting ? "flex" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-image {";
    str += "display:"+(obj.view.image ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-title-wrapper {";
    str += "display:"+(obj.view.title ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-author {";
    str += "display:"+(obj.view.author ? "inline-block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-date {";
    str += "display:"+(obj.view.date ? "inline-block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-category {";
    str += "display:"+(obj.view.category ? "inline-block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-comments {";
    str += "display:"+(obj.view.comments ? "inline-block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-hits {";
    str += "display:"+(obj.view.hits ? "inline-block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-product-options {";
    str += "display: none;";
    str += "}";
    if (obj.store) {
        str += "#"+selector+" .ba-blog-post-badge-wrapper {";
        str += "display:"+(obj.store.badge ? "flex" : "none")+";";
        str += "}";
        str += "#"+selector+" .ba-blog-post-wishlist-wrapper {";
        str += "display:"+(obj.store.wishlist ? "flex" : "none")+";";
        str += "}";
        str += "#"+selector+" .ba-blog-post-add-to-cart-price {";
        str += "display:"+(obj.store.price ? "flex" : "none")+";";
        str += "}";
        str += "#"+selector+" .ba-blog-post-add-to-cart-button {";
        str += "display:"+(obj.store.cart ? "flex" : "none")+";";
        str += "}";
        for (let ind in obj.store) {
            if (ind == 'badge' || ind == 'wishlist' || ind == 'price' || ind == 'cart') {
                continue;
            }
            str += "#"+selector+' .ba-blog-post-product-options[data-key="'+ind+'"] {';
            str += "display:"+(obj.store[ind] ? "flex" : "none")+";";
            str += "}";
        }
    }
    let blogInfoOrder = app.blogPostsInfo ? app.blogPostsInfo : new Array('author', 'date', 'category', 'hits', 'comments'),
        blogInfoVisible = false;
    for (let i = 0; i < blogInfoOrder.length; i++) {
        if (obj.view[blogInfoOrder[i]]) {
            for (let j = i + 1; j < blogInfoOrder.length; j++) {
                str += "#"+selector+" .ba-blog-post-"+blogInfoOrder[j]+":before {";
                str += 'margin: 0 10px;content: "'+(blogInfoOrder[j] == 'author' ? '' : '\\2022')+'";color: inherit;';
                str += "}";
            }
            blogInfoVisible = true;
            break;
        }
    }
    str += "#"+selector+" .ba-blog-post-reviews {";
    str += "display:"+(obj.view.reviews ? "flex" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-intro-wrapper {";
    str += "display:"+(obj.view.intro ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-field-row {";
    str += "display: none;"
    str += "}";
    if (obj.fields) {
        let visibleField = null;
        for (let i = 0; i < app.blogPostsFields.length; i++) {
            if (obj.fields[app.blogPostsFields[i]]) {
               visibleField = app.blogPostsFields[i];
            }
            str += '#'+selector+' .ba-blog-post-field-row[data-id="'+app.blogPostsFields[i]+'"] {';
            str += "display: "+(obj.fields[app.blogPostsFields[i]] ? 'flex' : 'none')+";";
            str += "margin-bottom: 10px;";
            str += "}";
        }
        if (visibleField) {
            str += '#'+selector+' .ba-blog-post-field-row[data-id="'+visibleField+'"] {';
            str += "margin-bottom: 0;";
            str += "}";
        }
    }
    str += "#"+selector+" .ba-blog-post-button-wrapper {";
    str += "display:"+(obj.view.button ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-image {";
    str += "width :"+obj.image.width+"px;";
    str += "height :"+obj.image.height+"px;";
    str += "background-size: "+(obj.image.size ? obj.image.size : 'cover')+";";
    str += "}";
    str += "#"+selector+" .ba-masonry-layout .ba-blog-post-image {";
    str += "width :100%;";
    str += "height :auto;";
    str += "}";
    str += "#"+selector+" .ba-cover-layout .ba-blog-post {";
    str += "height :"+obj.image.height+"px;";
    str += "}";
    str += "#"+selector+" .ba-blog-post-title {";
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-blog-post-title, #"+selector+" .ba-blog-post-add-to-cart-price {";
    str += getTypographyRule(obj.title.typography, 'text-align');
    if (type == 'post-navigation' && obj.title.typography['text-align'] == 'left') {
        str += "text-align :right;";
    } else if (type == 'post-navigation' && obj.title.typography['text-align'] == 'right') {
        str += "text-align :left;";
    } else {
        str += "text-align :"+obj.title.typography['text-align']+";";
    }
    str += "}";
    if (type == 'post-navigation') {
        str += "#"+selector+" .ba-blog-post:first-child .ba-blog-post-title {";
        str += "text-align :"+obj.title.typography['text-align']+";";
        str += "}";
    }
    let justify = obj.reviews.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    str += "#"+selector+" .ba-blog-post-reviews {";
    if (type == 'post-navigation' && obj.reviews.typography['text-align'] == 'left') {
        str += "justify-content :flex-end;";
    } else if (type == 'post-navigation' && obj.reviews.typography['text-align'] == 'right') {
        str += "justify-content :flex-start;";
    } else {
        str += "justify-content :"+justify+";";
    }
    str += getTypographyRule(obj.reviews.typography, 'text-align');
    for (var ind in obj.reviews.margin) {
        str += "margin-"+ind+" : "+obj.reviews.margin[ind]+"px;";
    }
    str += "}";
    if (obj.postFields) {
        str += "#"+selector+" .ba-blog-post-field-row-wrapper {";
        str += getTypographyRule(obj.postFields.typography, 'text-align');
        for (var ind in obj.postFields.margin) {
            str += "margin-"+ind+" : "+obj.postFields.margin[ind]+"px;";
        }
        str += "}";
    }
    str += "#"+selector+" .ba-blog-post-reviews a:hover {";
    str += "color: "+getCorrectColor(obj.reviews.hover.color)+";";
    str += "}";
    justify = obj.info.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    str += "#"+selector+" .ba-blog-post-info-wrapper {";
    for (var ind in obj.info.margin) {
        str += "margin-"+ind+" : "+(blogInfoVisible ? obj.info.margin[ind] : 0)+"px;";
    }
    if (type == 'post-navigation' && obj.info.typography['text-align'] == 'left') {
        str += "justify-content :flex-end;";
    } else if (type == 'post-navigation' && obj.info.typography['text-align'] == 'right') {
        str += "justify-content :flex-start;";
    } else {
        str += "justify-content :"+justify+";";
    }
    str += "}";
    if (type == 'post-navigation') {
        str += "#"+selector+" .ba-blog-post:first-child .ba-blog-post-info-wrapper {";
        str += "justify-content :"+justify+";";
        str += "}";
    }
    str += "#"+selector+" .ba-post-navigation-info {";
    if (type == 'post-navigation' && obj.info.typography['text-align'] == 'left') {
        str += "text-align :right;";
    } else if (type == 'post-navigation' && obj.info.typography['text-align'] == 'right') {
        str += "text-align :left;";
    } else {
        str += "text-align :"+obj.info.typography['text-align']+";";
    }
    str += "}";
    if (type == 'post-navigation') {
        str += "#"+selector+" .ba-blog-post:first-child .ba-post-navigation-info {";
        str += "text-align :"+obj.info.typography['text-align']+";";
        str += "}";
    }
    str += "#"+selector+" .ba-blog-post-info-wrapper > *, #"+selector+" .ba-post-navigation-info a {";
    str += getTypographyRule(obj.info.typography, 'text-align');
    str += "}";
    str += "#"+selector+" .ba-blog-post-intro-wrapper {";
    str += getTypographyRule(obj.intro.typography, 'text-align');
    for (var ind in obj.intro.margin) {
        str += "margin-"+ind+" : "+obj.intro.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-blog-post-intro-wrapper {";
    if (type == 'post-navigation' && obj.intro.typography['text-align'] == 'left') {
        str += "text-align :right;";
    } else if (type == 'post-navigation' && obj.intro.typography['text-align'] == 'right') {
        str += "text-align :left;";
    } else {
        str += "text-align :"+obj.intro.typography['text-align']+";";
    }
    str += "}";
    if (type == 'post-navigation') {
        str += "#"+selector+" .ba-blog-post:first-child .ba-blog-post-intro-wrapper {";
        str += "text-align :"+obj.intro.typography['text-align']+";";
        str += "}";
    }
    str += "#"+selector+" .ba-blog-post-button-wrapper {";
    if (type == 'post-navigation' && obj.button.typography['text-align'] == 'left') {
        str += "text-align :right;";
    } else if (type == 'post-navigation' && obj.button.typography['text-align'] == 'right') {
        str += "text-align :left;";
    } else {
        str += "text-align :"+obj.button.typography['text-align']+";";
    }
    str += "}";
    if (type == 'post-navigation') {
        str += "#"+selector+" .ba-blog-post:first-child .ba-blog-post-button-wrapper {";
        str += "text-align :"+obj.button.typography['text-align']+";";
        str += "}";
    }
    str += "#"+selector+" .ba-blog-post-button-wrapper a {";
    for (var ind in obj.button.margin) {
        str += "margin-"+ind+" : "+obj.button.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-blog-post-button-wrapper a, #"+selector+" .ba-blog-post-add-to-cart {";
    str += getTypographyRule(obj.button.typography, 'text-align');
    str += "border : "+obj.button.border.width+"px "+obj.button.border.style+" "+getCorrectColor(obj.button.border.color)+";";
    str += "border-radius : "+obj.button.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.button.shadow.value * 10);
    str += "px "+(obj.button.shadow.value * 20)+"px 0 "+getCorrectColor(obj.button.shadow.color)+";";
    str += "background-color: "+getCorrectColor(obj.button.normal.background)+";";
    str += "color: "+getCorrectColor(obj.button.normal.color)+";";
    for (var ind in obj.button.padding) {
        str += "padding-"+ind+" : "+obj.button.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-blog-post-button-wrapper a:hover, #"+selector+" .ba-blog-post-add-to-cart:hover {";
    str += "background-color: "+getCorrectColor(obj.button.hover.background)+";";
    str += "color: "+getCorrectColor(obj.button.hover.color)+";";
    str += "}";
    if (obj.pagination && !obj.pagination.typography) {
        str += "#"+selector+" .ba-blog-posts-pagination span a {";
        str += "color: "+getCorrectColor(obj.pagination.color)+";";
        str += "}";
        str += "#"+selector+" .ba-blog-posts-pagination span.active a,#"+selector;
        str += " .ba-blog-posts-pagination span:hover a {";
        str += "color: "+getCorrectColor(obj.pagination.hover)+";";
        str += "}";
    } else if (obj.pagination && obj.pagination.typography) {
        str += "#"+selector+" .ba-blog-posts-pagination {";
        str += "text-align :"+obj.pagination.typography['text-align']+";";
        str += "}";
        str += "#"+selector+" .ba-blog-posts-pagination a {";
        for (var ind in obj.pagination.margin) {
            str += "margin-"+ind+" : "+obj.pagination.margin[ind]+"px;";
        }
        str += getTypographyRule(obj.pagination.typography, 'text-align');
        str += "border : "+obj.pagination.border.width+"px "+obj.pagination.border.style;
        str += " "+getCorrectColor(obj.pagination.border.color)+";";
        str += "border-radius : "+obj.pagination.border.radius+"px;";
        str += "box-shadow: 0 "+(obj.pagination.shadow.value * 10);
        str += "px "+(obj.pagination.shadow.value * 20)+"px 0 "+getCorrectColor(obj.pagination.shadow.color)+";";
        str += "background-color: "+getCorrectColor(obj.pagination.normal.background)+";";
        str += "color: "+getCorrectColor(obj.pagination.normal.color)+";";
        for (var ind in obj.pagination.padding) {
            str += "padding-"+ind+" : "+obj.pagination.padding[ind]+"px;";
        }
        str += "}";
        str += "#"+selector+" .ba-blog-posts-pagination a:hover {";
        str += "background-color: "+getCorrectColor(obj.pagination.hover.background)+";";
        str += "color: "+getCorrectColor(obj.pagination.hover.color)+";";
        str += "}";
    }
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getRecentCommentsRules(obj, selector, type)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    if (obj.view.count) {
        str += "#"+selector+" .ba-masonry-layout {";
        str += "grid-template-columns: repeat(auto-fill, minmax(calc((100% / "+obj.view.count+") - 21px),1fr));";
        str += "}";
        str += "#"+selector+" .ba-grid-layout .ba-blog-post {";
        str += "width: calc((100% / "+obj.view.count+") - 21px);";
        str += "}";
        str += "#"+selector+" .ba-grid-layout .ba-blog-post:nth-child(n) {";
        str += "margin-top: 30px;";
        str += "}";
        for (var i = 0; i < obj.view.count; i++) {
            str += "#"+selector+" .ba-grid-layout .ba-blog-post:nth-child("+(i + 1)+") {";
            str += "margin-top: 0;";
            str += "}";
        }
    }
    str += "#"+selector+" .ba-blog-post {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "background-color:"+getCorrectColor(obj.background.color)+';';
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-image {";
    str += "border : "+obj.image.border.width+"px "+obj.image.border.style+" "+getCorrectColor(obj.image.border.color)+";";
    str += "border-radius : "+obj.image.border.radius+"px;";
    str += "}";
    str += "#"+selector+" .ba-blog-post-image {";
    str += "display:"+(obj.view.image ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-date {";
    str += "display:"+(obj.view.date ? "inline-block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-intro-wrapper {";
    str += "display:"+(obj.view.intro ? "block" : "none")+";";
    str += "}";
    if ('source' in obj.view) {
        str += "#"+selector+" .ba-reviews-source {";
        str += "display:"+(obj.view.source ? "inline-block" : "none")+";";
        str += "}";
        str += "#"+selector+" .ba-reviews-name {";
        str += "display:"+(obj.view.title ? "inline-block" : "none")+";";
        str += "}";
        str += "#"+selector+" .ba-blog-post-title-wrapper {";
        str += "display:"+(obj.view.title || obj.view.source ? "block" : "none")+";";
        str += "}";
    } else {
        str += "#"+selector+" .ba-blog-post-title-wrapper {";
        str += "display:"+(obj.view.title ? "block" : "none")+";";
        str += "}";
    }
    str += "#"+selector+" .ba-blog-post-image {";
    str += "width :"+obj.image.width+"px;";
    str += "height :"+obj.image.height+"px;";
    str += "}";
    str += "#"+selector+" .ba-blog-post-title {";
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += getTypographyRule(obj.title.typography, 'text-align');
    str += "text-align :"+obj.title.typography['text-align']+";";
    str += "}";    
    str += "#"+selector+" .ba-blog-post-info-wrapper {";
    for (var ind in obj.info.margin) {
        str += "margin-"+ind+" : "+obj.info.margin[ind]+"px;";
    }
    str += "text-align :"+obj.info.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-info-wrapper > * {";
    str += getTypographyRule(obj.info.typography, 'text-align');
    str += "}";
    if ('stars' in obj) {
        let justify = obj.stars.icon['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
        str += "#"+selector+" .ba-review-stars-wrapper {";
        for (var ind in obj.stars.margin) {
            str += "margin-"+ind+" : "+obj.stars.margin[ind]+"px;";
        }
        str += "font-size: "+obj.stars.icon.size+"px;";
        str += "justify-content: "+justify+";";
        str += "}";
    }
    str += "#"+selector+" .ba-blog-post-intro-wrapper {";
    str += getTypographyRule(obj.intro.typography, 'text-align');
    for (var ind in obj.intro.margin) {
        str += "margin-"+ind+" : "+obj.intro.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-blog-post-intro-wrapper {";
    str += "text-align :"+obj.intro.typography['text-align']+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getAuthorRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-posts-author-wrapper .ba-post-author {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-grid-layout .ba-post-author {";
    str += "width: calc((100% / "+obj.view.count+") - 21px);";
    str += "}";
    str += "#"+selector+" .ba-grid-layout .ba-post-author:nth-child(n) {";
    str += "margin-top: 30px;";
    str += "}";
    for (var i = 0; i < obj.view.count; i++) {
        str += "#"+selector+" .ba-grid-layout .ba-post-author:nth-child("+(i + 1)+") {";
        str += "margin-top: 0;";
        str += "}";
    }
    str += "#"+selector+" .ba-overlay {background-color:"
    if (!obj.overlay.type || obj.overlay.type == 'color') {
        str += getCorrectColor(obj.overlay.color)+";";
        str += 'background-image: none;';
    } else if (obj.overlay.type == 'none') {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: none;';
    } else {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
        if (obj.overlay.gradient.effect == 'linear') {
            str += obj.overlay.gradient.angle+'deg';
        } else {
            str += 'circle';
        }
        str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
        str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
        str += ' '+obj.overlay.gradient.position2+'%);';
        str += 'background-attachment: scroll;';
    }
    str += '}';
    if (obj.background) {
        str += "#"+selector+" .ba-post-author {";
        str += "background-color:"+getCorrectColor(obj.background.color)+';';
        str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
        str += "border-radius : "+obj.border.radius+"px;";
        str += "box-shadow: 0 "+(obj.shadow.value * 10);
        str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
        str += "}";
    }
    if (obj.image.border) {
        str += "#"+selector+" .ba-post-author-image {";
        str += "border : "+obj.image.border.width+"px "+obj.image.border.style+" "+getCorrectColor(obj.image.border.color)+";";
        str += "border-radius : "+obj.image.border.radius+"px;";
        str += "}";
    }
    str += "#"+selector+" .ba-post-author-image {";
    str += "display:"+(obj.view.image ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-post-author-title-wrapper {";
    str += "display:"+(obj.view.title ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-post-author-description {";
    str += "display:"+(obj.view.intro ? "block" : "none")+";";
    str += "}";
    str += "#"+selector+" .ba-post-author-image {";
    str += "width :"+obj.image.width+"px;";
    str += "height :"+obj.image.height+"px;";
    str += "}";
    str += "#"+selector+" .ba-post-author-title {";
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += getTypographyRule(obj.title.typography);
    str += "}";
    str += "#"+selector+" .ba-post-author-social-wrapper {";
    str += "text-align: "+obj.intro.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-post-author-social-wrapper a {";
    str += "color: "+getCorrectColor(obj.intro.typography.color)+";";
    str += "}";
    str += "#"+selector+" .ba-post-author-description {";
    str += getTypographyRule(obj.intro.typography);
    for (var ind in obj.intro.margin) {
        str += "margin-"+ind+" : "+obj.intro.margin[ind]+"px;";
    }
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getPostIntroRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .intro-post-wrapper.fullscreen-post {";
    str += "height :"+obj.image.height+"px;";
    if (obj.image.fullscreen) {
        str += "min-height: 100vh;";
    } else {
        str += "min-height: auto;";
    }
    str += "}";
    str += "#"+selector+" .ba-box-model > * {display: none;}";
    str += "#"+selector+" .ba-overlay {background-color:"
    if (!obj.image.type || obj.image.type == 'color') {
        str += getCorrectColor(obj.image.color)+";";
        str += 'background-image: none;';
    } else if (obj.image.type == 'none') {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: none;';
    } else {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: '+obj.image.gradient.effect+'-gradient(';
        if (obj.image.gradient.effect == 'linear') {
            str += obj.image.gradient.angle+'deg';
        } else {
            str += 'circle';
        }
        str += ', '+getCorrectColor(obj.image.gradient.color1)+' ';
        str += obj.image.gradient.position1+'%, '+getCorrectColor(obj.image.gradient.color2);
        str += ' '+obj.image.gradient.position2+'%);';
        str += 'background-attachment: scroll;';
    }
    str += '}';
    str += "#"+selector+" .intro-post-image {";
    str += "height :"+obj.image.height+"px;";
    str += "background-attachment: "+obj.image.attachment+";";
    str += "background-position: "+obj.image.position+";";
    str += "background-repeat: "+obj.image.repeat+";";
    str += "background-size: "+obj.image.size+";";
    if (obj.image.fullscreen) {
        str += "min-height: 100vh;";
    } else {
        str += "min-height: auto;";
    }
    str += "}";
    str += "#"+selector+" .intro-post-title-wrapper {";
    str += "text-align :"+obj.title.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .intro-post-title {";
    str += getTypographyRule(obj.title.typography, 'text-align');
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += "}";
    let justify = obj.info.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    str += "#"+selector+" .intro-post-info {";
    str += "text-align :"+obj.info.typography['text-align']+";";
    str += "justify-content: "+justify+";";
    for (var ind in obj.info.margin) {
        str += "margin-"+ind+" : "+obj.info.margin[ind]+"px;";
    }
    if (typeof(obj.info.show) != 'undefined') {
        str += 'display:'+(obj.info.show ? 'block' : 'none')+';';
    }
    str += "}";
    str += "#"+selector+" .intro-post-info *:not(i):not(a) {";
    if (typeof(obj.info.show) != 'undefined') {
        str += 'display:'+(obj.info.show ? 'block' : 'none')+';';
    }
    str += getTypographyRule(obj.info.typography, 'text-align');
    str += "}";
    if (!('author' in obj.view)) {
        obj.view.author = false;
    }
    if (!('comments' in obj.view)) {
        obj.view.comments = false;
    }
    if (!('reviews' in obj.view)) {
        obj.view.reviews = false;
    }
    str += "#"+selector+" .intro-post-wrapper:not(.fullscreen-post) .intro-post-image-wrapper {";
    str += 'display:'+(obj.image.show ? 'block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .intro-post-title-wrapper {";
    str += 'display:'+(obj.title.show ? 'block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .intro-post-author {";
    str += 'display:'+(obj.view.author ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .intro-post-date {";
    str += 'display:'+(obj.view.date ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .intro-post-category {";
    str += 'display:'+(obj.view.category ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .intro-post-comments {";
    str += 'display:'+(obj.view.comments ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .intro-post-hits {";
    str += 'display:'+(obj.view.hits ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .intro-post-reviews {";
    str += 'display:'+(obj.view.reviews ? 'inline-flex' : 'none')+';';
    str += "}";
    let blogInfoOrder = app.blogPostsInfo ? app.blogPostsInfo : new Array('author', 'date', 'category', 'comments', 'hits', 'reviews');
    for (let i = 0; i < blogInfoOrder.length; i++) {
        if (obj.view[blogInfoOrder[i]]) {
            for (let j = i + 1; j < blogInfoOrder.length; j++) {
                str += "#"+selector+" .intro-post-"+blogInfoOrder[j]+":before {";
                str += 'margin: 0 10px;content: "'+(blogInfoOrder[j] == 'author' ? '' : '\\2022')+'";color: inherit;';
                str += "}";
            }
            break;
        }
    }    
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getStarRatingsRules(obj, selector)
{
    var str = "#"+selector+" {";
    
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .star-ratings-wrapper {";
    str += "text-align: "+obj.icon['text-align']+";";
    str += "}";
    str += "#"+selector+" .rating-wrapper {";
    if (obj.view.rating == 1) {
        str += 'display: inline;';
    } else {
        str += 'display: none;';
    }
    str += "}";
    str += "#"+selector+" .votes-wrapper {";
    if (obj.view.votes == 1) {
        str += 'display: inline;';
    } else {
        str += 'display: none;';
    }
    str += "}";
    str += "#"+selector+" .stars-wrapper {";
    str += "color:"+getCorrectColor(obj.icon.color)+";";
    str += "}";
    str += "#"+selector+" .star-ratings-wrapper i {";
    str += "font-size:"+obj.icon.size+"px;";
    str += "}";
    str += "#"+selector+" .star-ratings-wrapper i.active,#"+selector+" .star-ratings-wrapper i.active + i:after";
    str += ",#"+selector+" .stars-wrapper:hover i {";
    str += "color:"+getCorrectColor(obj.icon.hover)+";";
    str += "}";
    str += "#"+selector+" .info-wrapper * {";
    str += getTypographyRule(obj.info, 'text-align');
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getIconRules(obj, selector)
{
    var str = "#"+selector+" {";
    str += "text-align: "+obj.icon['text-align']+";";
    if (obj.inline) {
        str += "margin : 0 10px;";
        str += "width: auto;";
    } else {
        str += "margin : 0;";
    }
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}"
    str += "#"+selector+" .ba-icon-wrapper i {";
    str += "width : "+obj.icon.size+"px;";
    str += "height : "+obj.icon.size+"px;";
    str += "font-size : "+obj.icon.size+"px;";
    str += "color : "+getCorrectColor(obj.normal.color)+";";
    str += "background-color : "+getCorrectColor(obj.normal['background-color'])+";";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    if (obj.shadow) {
        str += "box-shadow: 0 "+(obj.shadow.value * 10);
        str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    }
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    str += setBoxModel(obj, selector);
    if (obj.inline) {
        str += setItemsVisability(obj.disable, "inline-block", '#'+selector);
    } else {
        str += setItemsVisability(obj.disable, "block", '#'+selector);
    }
    
    return str;
}

function getRecentSliderRules(obj, selector)
{
    var str = "#"+selector+" {",
        margin = obj.gutter ? 30 : 0;
    margin = margin * (obj.slideset.count - 1);
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    if (obj.overflow) {
        str += "#"+selector+" ul.carousel-type .slideshow-content {";
        str += "width: calc(100% + (100% / "+obj.slideset.count+") * 2);";
        str += "margin-left: calc((100% / "+obj.slideset.count+") * -1);";
        str += "}";
    } else {
        str += "#"+selector+" ul.carousel-type .slideshow-content {";
        str += "width: 100%;";
        str += "margin-left: auto;";
        str += "}";
    }
    str += "#"+selector+" ul.carousel-type li {"
    str += "width: calc((100% - "+margin+"px) / "+obj.slideset.count+");";
    str += "}";
    str += "#"+selector+" ul.carousel-type:not(.slideset-loaded) li {";
    str += "position: relative; float:left;";
    str += "}";
    str += "#"+selector+" ul.carousel-type:not(.slideset-loaded) li.item.active:not(:first-child) {";
    str += "margin-left: "+(obj.gutter ? 30 : 0)+"px;";
    str += "}";
    str += "#"+selector+" ul.slideshow-type {";
    if (obj.view.fullscreen) {
        str += "min-height: 100vh;";
    } else {
        str += "min-height: auto;";
    }
    str += "height:"+obj.view.height+"px;";
    str += "}";
    str += "#"+selector+" ul.carousel-type .ba-slideshow-img {";
    str += "height:"+obj.view.height+"px;";
    str += "}";
    str += "#"+selector+" .ba-slideshow-img {";
    str += "background-size :"+obj.view.size+";";
    str += "}";
    str += "#"+selector+" ul.carousel-type .ba-slideshow-caption, #"+selector+" .ba-overlay {background-color :";
    if (!obj.overlay.type || obj.overlay.type == 'color') {
        str += getCorrectColor(obj.overlay.color)+";";
        str += 'background-image: none;';
    } else if (obj.overlay.type == 'none') {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: none;';
    } else {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
        if (obj.overlay.gradient.effect == 'linear') {
            str += obj.overlay.gradient.angle+'deg';
        } else {
            str += 'circle';
        }
        str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
        str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
        str += ' '+obj.overlay.gradient.position2+'%);';
        str += 'background-attachment: scroll;';
    }
    str += "}";
    str += "#"+selector+" .ba-blog-post-title {";
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += 'display:'+(obj.view.title ? 'block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .ba-blog-post-title, #"+selector+" .ba-blog-post-add-to-cart-price {";
    str += getTypographyRule(obj.title.typography);
    str += "}";
    str += "#"+selector+" .ba-blog-post-title:hover {";
    str += "color: "+getCorrectColor(obj.title.hover.color)+";";
    str += "}";
    let justify = obj.reviews.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    str += "#"+selector+" .ba-blog-post-reviews {";
    str += "justify-content: "+justify+";";
    str += getTypographyRule(obj.reviews.typography, 'text-align');
    for (var ind in obj.reviews.margin) {
        str += "margin-"+ind+" : "+obj.reviews.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-blog-post-reviews a:hover {";
    str += "color: "+getCorrectColor(obj.reviews.hover.color)+";";
    str += "}";
    if (obj.postFields) {
        str += "#"+selector+" .ba-blog-post-field-row-wrapper {";
        str += getTypographyRule(obj.postFields.typography, 'text-align');
        for (var ind in obj.postFields.margin) {
            str += "margin-"+ind+" : "+obj.postFields.margin[ind]+"px;";
        }
        str += "}";
    }
    justify = obj.info.typography['text-align'].replace('left', 'flex-start').replace('right', 'flex-end');
    str += "#"+selector+" .ba-blog-post-info-wrapper {";
    for (var ind in obj.info.margin) {
        str += "margin-"+ind+" : "+obj.info.margin[ind]+"px;";
    }
    str += "justify-content: "+justify+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-info-wrapper > * {";
    str += getTypographyRule(obj.info.typography, 'text-align');
    str += "}";
    if (!'author' in obj.view) {
        obj.view.author = false;
    }
    if (!'comments' in obj.view) {
        obj.view.comments = false;
    }
    if (!'reviews' in obj.view) {
        obj.view.reviews = false;
    }
    str += "#"+selector+" .ba-blog-post-info-wrapper span.ba-blog-post-author {";
    str += 'display:'+(obj.view.author ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .ba-blog-post-info-wrapper span.ba-blog-post-date {";
    str += 'display:'+(obj.view.date ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .ba-blog-post-info-wrapper span.ba-blog-post-category {";
    str += 'display:'+(obj.view.category ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .ba-blog-post-info-wrapper span.ba-blog-post-comments {";
    str += 'display:'+(obj.view.comments ? 'inline-block' : 'none')+';';
    str += "}";
    let blogInfoOrder = app.blogPostsInfo ? app.blogPostsInfo : new Array('author', 'date', 'category', 'hits', 'comments');
    for (let i = 0; i < blogInfoOrder.length; i++) {
        if (obj.view[blogInfoOrder[i]]) {
            for (let j = i + 1; j < blogInfoOrder.length; j++) {
                str += "#"+selector+" .ba-blog-post-"+blogInfoOrder[j]+":before {";
                str += 'margin: 0 10px;content: "'+(blogInfoOrder[j] == 'author' ? '' : '\\2022')+'";color: inherit;';
                str += "}";
            }
            break;
        }
    }
    str += "#"+selector+" .ba-blog-post-reviews {";
    str += 'display:'+(obj.view.reviews ? 'flex' : 'none')+';';
    str += "}";
    str += "#"+selector+" .ba-blog-post-product-options {";
    str += "display: none;";
    str += "}";
    if (obj.store) {
        str += "#"+selector+" .ba-blog-post-badge-wrapper {";
        str += "display:"+(obj.store.badge ? "flex" : "none")+";";
        str += "}";
        str += "#"+selector+" .ba-blog-post-wishlist-wrapper {";
        str += "display:"+(obj.store.wishlist ? "flex" : "none")+";";
        str += "}";
        str += "#"+selector+" .ba-blog-post-add-to-cart-price {";
        str += "display:"+(obj.store.price ? "flex" : "none")+";";
        str += "}";
        str += "#"+selector+" .ba-blog-post-add-to-cart-button {";
        str += "display:"+(obj.store.cart ? "flex" : "none")+";";
        str += "}";
        for (let ind in obj.store) {
            if (ind == 'badge' || ind == 'wishlist' || ind == 'price' || ind == 'cart') {
                continue;
            }
            str += "#"+selector+' .ba-blog-post-product-options[data-key="'+ind+'"] {';
            str += "display:"+(obj.store[ind] ? "flex" : "none")+";";
            str += "}";
        }
    }
    str += "#"+selector+" .ba-blog-post-info-wrapper > * a:hover {";
    str += "color: "+getCorrectColor(obj.info.hover.color)+";";
    str += "}";
    str += "#"+selector+" .slideshow-button {";
    str += "text-align :"+obj.button.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-intro-wrapper {";
    str += getTypographyRule(obj.intro.typography);
    for (var ind in obj.intro.margin) {
        str += "margin-"+ind+" : "+obj.intro.margin[ind]+"px;";
    }
    str += 'display:'+(obj.view.intro ? 'block' : 'none')+';'
    str += "}";
    str += "#"+selector+" .ba-blog-post-field-row {";
    str += "display: none;"
    str += "}";
    if (obj.fields) {
        let visibleField = null;
        for (let i = 0; i < app.blogPostsFields.length; i++) {
            if (obj.fields[app.blogPostsFields[i]]) {
               visibleField = app.blogPostsFields[i];
            }
            str += '#'+selector+' .ba-blog-post-field-row[data-id="'+app.blogPostsFields[i]+'"] {';
            str += "display: "+(obj.fields[app.blogPostsFields[i]] ? 'flex' : 'none')+";";
            str += "margin-bottom: 10px;";
            str += "}";
        }
        if (visibleField) {
            str += '#'+selector+' .ba-blog-post-field-row[data-id="'+visibleField+'"] {';
            str += "margin-bottom: 0;";
            str += "}";
        }
    }
    str += "#"+selector+" .ba-blog-post-button-wrapper {";
    str += "text-align :"+obj.button.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-blog-post-button-wrapper a {";
    for (var ind in obj.button.margin) {
        str += "margin-"+ind+" : "+obj.button.margin[ind]+"px;";
    }
    str += 'display:'+(obj.view.button ? 'inline-block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .ba-blog-post-button-wrapper a, #"+selector+" .ba-blog-post-add-to-cart {";
    str += getTypographyRule(obj.button.typography, 'text-align');
    str += "border : "+obj.button.border.width+"px "+obj.button.border.style+" "+getCorrectColor(obj.button.border.color)+";";
    str += "border-radius : "+obj.button.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.button.shadow.value * 10);
    str += "px "+(obj.button.shadow.value * 20)+"px 0 "+getCorrectColor(obj.button.shadow.color)+";";
    str += "background-color: "+getCorrectColor(obj.button.normal.background)+";";
    str += "color: "+getCorrectColor(obj.button.normal.color)+";";
    for (var ind in obj.button.padding) {
        str += "padding-"+ind+" : "+obj.button.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-blog-post-button-wrapper a:hover, #"+selector+" .ba-blog-post-add-to-cart:hover {";
    str += "background-color: "+getCorrectColor(obj.button.hover.background)+";";
    str += "color: "+getCorrectColor(obj.button.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideset-nav {";
    str += 'display:'+(obj.view.arrows == 1 ? 'block' : 'none')+';';
    str += "}";
    str += "#"+selector+" .ba-slideset-nav a {";
    str += "font-size: "+obj.arrows.size+"px;";
    str += "width: "+obj.arrows.size+"px;";
    str += "height: "+obj.arrows.size+"px;";
    str += "background-color: "+getCorrectColor(obj.arrows.normal.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.normal.color)+";";
    str += "padding : "+obj.arrows.padding+"px;";
    str += "box-shadow: 0 "+(obj.arrows.shadow.value * 10);
    str += "px "+(obj.arrows.shadow.value * 20)+"px 0 "+getCorrectColor(obj.arrows.shadow.color)+";";
    str += "border : "+obj.arrows.border.width+"px "+obj.arrows.border.style+" "+getCorrectColor(obj.arrows.border.color)+";";
    str += "border-radius : "+obj.arrows.border.radius+"px;";
    str += "}";
    str += "#"+selector+" .ba-slideset-nav a:hover {";
    str += "background-color: "+getCorrectColor(obj.arrows.hover.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideset-dots {";
    str += 'display:'+(obj.view.dots == 1 ? 'flex' : 'none')+';';
    str += "}";
    str += "#"+selector+" .ba-slideset-dots > div {";
    str += "font-size: "+obj.dots.size+"px;";
    str += "width: "+obj.dots.size+"px;";
    str += "height: "+obj.dots.size+"px;";
    str += "color: "+getCorrectColor(obj.dots.normal.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideset-dots > div:hover,#"+selector+" .ba-slideset-dots > div.active {";
    str += "color: "+getCorrectColor(obj.dots.hover.color)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getTestimonialsRules(obj, selector)
{
    var str = "#"+selector+" {",
        margin = 30 * (obj.slideset.count - 1);
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" li {"
    str += "width: calc((100% - "+margin+"px) / "+obj.slideset.count+");";
    str += "}";
    str += "#"+selector+" ul.style-6 li {";
    str += "width: 100%;";
    str += "}";
    str += "#"+selector+" .slideshow-content .testimonials-wrapper, #"+selector+" .testimonials-info {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "background-color: "+getCorrectColor(obj.background.color)+";";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += "}";
    str += "#"+selector+" .testimonials-info:before {";
    str += "border-color: "+getCorrectColor(obj.background.color)+";";
    str += "left:"+(obj.image.width / 2)+"px";
    str += "}";
    str += "#"+selector+" .testimonials-icon-wrapper i {";
    str += "width : "+obj.icon.size+"px;";
    str += "height : "+obj.icon.size+"px;";
    str += "font-size : "+obj.icon.size+"px;";
    str += "color : "+getCorrectColor(obj.icon.color)+";";
    str += "}";
    str += "#"+selector+" .testimonials-img {";
    str += "width:"+obj.image.width+"px;";
    str += "height:"+obj.image.width+"px;";
    str += "border : "+obj.image.border.width+"px "+obj.image.border.style+" "+getCorrectColor(obj.image.border.color)+";";
    str += "border-radius : "+obj.image.border.radius+"px;";
    str += "}";
    str += "#"+selector+" ul.style-6 .ba-slideset-dots div {";
    str += "width:"+obj.image.width+"px;";
    str += "height:"+obj.image.width+"px;";
    str += "border : "+obj.image.border.width+"px "+obj.image.border.style+" "+getCorrectColor(obj.image.border.color)+";";
    str += "border-radius : "+obj.image.border.radius+"px;";
    str += "}";
    str += "#"+selector+" .ba-testimonials-name {";
    str += getTypographyRule(obj.name.typography);
    str += "}";
    str += "#"+selector+" .ba-testimonials-testimonial {";
    str += getTypographyRule(obj.testimonial.typography);
    str += "}";
    str += "#"+selector+" .ba-testimonials-caption {";
    str += getTypographyRule(obj.caption.typography);
    str += "}";
    str += "#"+selector+" .ba-slideset-nav {";
    if (obj.view.arrows == 1) {
        str += 'display:block;';
    } else {
        str += 'display:none;';
    }
    str += "}";
    str += "#"+selector+" .testimonials-slideshow-content-wrapper {";
    if (obj.view.arrows == 1) {
        str += "width: calc(100% - "+((40 + (obj.arrows.padding * 2) + obj.arrows.size * 1 ) * 2)+"px);"
    } else {
        str += "width: calc(100% - 50px);";
    }
    str += "}";
    str += "#"+selector+" .ba-slideset-nav a {";
    str += "font-size: "+obj.arrows.size+"px;";
    str += "width: "+obj.arrows.size+"px;";
    str += "height: "+obj.arrows.size+"px;";
    str += "background-color: "+getCorrectColor(obj.arrows.normal.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.normal.color)+";";
    str += "padding : "+obj.arrows.padding+"px;";
    str += "box-shadow: 0 "+(obj.arrows.shadow.value * 10);
    str += "px "+(obj.arrows.shadow.value * 20)+"px 0 "+getCorrectColor(obj.arrows.shadow.color)+";";
    str += "border : "+obj.arrows.border.width+"px "+obj.arrows.border.style+" "+getCorrectColor(obj.arrows.border.color)+";";
    str += "border-radius : "+obj.arrows.border.radius+"px;";
    str += "}";
    str += "#"+selector+" .ba-slideset-nav a:hover {";
    str += "background-color: "+getCorrectColor(obj.arrows.hover.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideset-dots {";
    if (obj.view.dots == 1) {
        str += 'display:flex;';
    } else {
        str += 'display:none;';
    }
    str += "}";
    str += "#"+selector+" .ba-slideset-dots > div {";
    str += "font-size: "+obj.dots.size+"px;";
    str += "width: "+obj.dots.size+"px;";
    str += "height: "+obj.dots.size+"px;";
    str += "color: "+getCorrectColor(obj.dots.normal.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideset-dots > div:hover,#"+selector+" .ba-slideset-dots > div.active {";
    str += "color: "+getCorrectColor(obj.dots.hover.color)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getCarouselRules(obj, selector)
{
    var str = "#"+selector+" {",
        margin = obj.gutter ? 30 : 0;
    margin = margin * (obj.slideset.count - 1);
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    if (obj.overflow) {
        str += "#"+selector+" .slideshow-content {";
        str += "width: calc(100% + (100% / "+obj.slideset.count+") * 2);";
        str += "margin-left: calc((100% / "+obj.slideset.count+") * -1);";
        str += "}";
    } else {
        str += "#"+selector+" .slideshow-content {";
        str += "width: 100%;";
        str += "margin-left: auto;";
        str += "}";
    }
    str += "#"+selector+" li {"
    str += "width: calc((100% - "+margin+"px) / "+obj.slideset.count+");";
    str += "}";
    str += "#"+selector+" ul:not(.slideset-loaded) li {";
    str += "position: relative; float:left;";
    str += "}";
    str += "#"+selector+" ul:not(.slideset-loaded) li.item.active:not(:first-child) {";
    str += "margin-left: "+(obj.gutter ? 30 : 0)+"px;";
    str += "}";
    for (var ind in obj.slides) {
        if (obj.slides[ind].image) {
            str += "#"+selector+" li.item:nth-child("+ind+") .ba-slideshow-img {background-image: url(";
            if (obj.slides[ind].image.indexOf('balbooa.com') != -1) {
                str += obj.slides[ind].image+");";
            } else {
                str += JUri+encodeURI(obj.slides[ind].image)+");";
            }
            str += "}"; 
        }
    }
    str += "#"+selector+" .ba-slideshow-img {";
    str += "background-size :"+obj.view.size+";";
    str += "height:"+obj.view.height+"px;";
    str += "}";
    str += "#"+selector+" .ba-slideshow-caption {background-color :";
    if (!obj.overlay.type || obj.overlay.type == 'color') {
        str += getCorrectColor(obj.overlay.color)+";";
        str += 'background-image: none;';
    } else if (obj.overlay.type == 'none') {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: none;';
    } else {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
        if (obj.overlay.gradient.effect == 'linear') {
            str += obj.overlay.gradient.angle+'deg';
        } else {
            str += 'circle';
        }
        str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
        str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
        str += ' '+obj.overlay.gradient.position2+'%);';
        str += 'background-attachment: scroll;';
    }
    str += "}";
    str += "#"+selector+" .slideshow-title-wrapper {";
    str += "text-align :"+obj.title.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-slideshow-title {";
    str += getTypographyRule(obj.title.typography, 'text-align');
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .slideshow-description-wrapper {";
    str += "text-align :"+obj.description.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-slideshow-description {";
    str += getTypographyRule(obj.description.typography, 'text-align');
    for (var ind in obj.description.margin) {
        str += "margin-"+ind+" : "+obj.description.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .slideshow-button {";
    str += "text-align :"+obj.button.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .slideshow-button:not(.empty-content) a {";
    for (var ind in obj.button.margin) {
        str += "margin-"+ind+" : "+obj.button.margin[ind]+"px;";
    }
    str += getTypographyRule(obj.button.typography, 'text-align');
    str += "border : "+obj.button.border.width+"px "+obj.button.border.style+" "+getCorrectColor(obj.button.border.color)+";";
    str += "border-radius : "+obj.button.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.button.shadow.value * 10);
    str += "px "+(obj.button.shadow.value * 20)+"px 0 "+getCorrectColor(obj.button.shadow.color)+";";
    str += "background-color: "+getCorrectColor(obj.button.normal.background)+";";
    str += "color: "+getCorrectColor(obj.button.normal.color)+";";
    for (var ind in obj.button.padding) {
        str += "padding-"+ind+" : "+obj.button.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .slideshow-button a:hover {";
    str += "background-color: "+getCorrectColor(obj.button.hover.background)+";";
    str += "color: "+getCorrectColor(obj.button.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideset-nav {";
    if (obj.view.arrows == 1) {
        str += 'display:block;';
    } else {
        str += 'display:none;';
    }
    str += "}";
    str += "#"+selector+" .ba-slideset-nav a {";
    str += "font-size: "+obj.arrows.size+"px;";
    str += "width: "+obj.arrows.size+"px;";
    str += "height: "+obj.arrows.size+"px;";
    str += "background-color: "+getCorrectColor(obj.arrows.normal.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.normal.color)+";";
    str += "padding : "+obj.arrows.padding+"px;";
    str += "box-shadow: 0 "+(obj.arrows.shadow.value * 10);
    str += "px "+(obj.arrows.shadow.value * 20)+"px 0 "+getCorrectColor(obj.arrows.shadow.color)+";";
    str += "border : "+obj.arrows.border.width+"px "+obj.arrows.border.style+" "+getCorrectColor(obj.arrows.border.color)+";";
    str += "border-radius : "+obj.arrows.border.radius+"px;";
    str += "}";
    str += "#"+selector+" .ba-slideset-nav a:hover {";
    str += "background-color: "+getCorrectColor(obj.arrows.hover.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideset-dots {";
    if (obj.view.dots == 1) {
        str += 'display:flex;';
    } else {
        str += 'display:none;';
    }
    str += "}";
    str += "#"+selector+" .ba-slideset-dots > div {";
    str += "font-size: "+obj.dots.size+"px;";
    str += "width: "+obj.dots.size+"px;";
    str += "height: "+obj.dots.size+"px;";
    str += "color: "+getCorrectColor(obj.dots.normal.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideset-dots > div:hover,#"+selector+" .ba-slideset-dots > div.active {";
    str += "color: "+getCorrectColor(obj.dots.hover.color)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getContentSliderItemsRules(obj, selector)
{
    var str = '';
    str += selector+" > .ba-overlay {background-color: ";
    if (obj.overlay.type == 'color') {
        str += getCorrectColor(obj.overlay.color)+";";
        str += 'background-image: none;';
    } else if (obj.overlay.type == 'none') {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: none;';
    } else {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
        if (obj.overlay.gradient.effect == 'linear') {
            str += obj.overlay.gradient.angle+'deg';
        } else {
            str += 'circle';
        }
        str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
        str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
        str += ' '+obj.overlay.gradient.position2+'%);';
        str += 'background-attachment: scroll;';
    }
    str += "}";
    str += selector+" > .ba-slideshow-img {";
    switch (obj.background.type) {
        case 'image' :
            if (obj.background.image) {
                var image = obj.background.image.image;
                if (image.indexOf('balbooa.com') != -1) {
                    str += "background-image: url("+image+");";
                } else {
                    str += "background-image: url("+JUri+encodeURI(image)+");";
                }
                for (var key in obj.background.image) {
                    if (key == 'image') {
                        continue;
                    }
                    str += "background-"+key+": "+obj.background.image[key]+";";
                }
            }
            str += "background-color: rgba(0, 0, 0, 0);";
            break;
        case 'gradient' :
            str += 'background-image: '+obj.background.gradient.effect+'-gradient(';
            if (obj.background.gradient.effect == 'linear') {
                str += obj.background.gradient.angle+'deg';
            } else {
                str += 'circle';
            }
            str += ', '+getCorrectColor(obj.background.gradient.color1)+' ';
            str += obj.background.gradient.position1+'%, '+getCorrectColor(obj.background.gradient.color2);
            str += ' '+obj.background.gradient.position2+'%);';
            str += "background-color: rgba(0, 0, 0, 0);";
            str += 'background-attachment: scroll;';
            break;
        case 'color' :
            str += "background-color: "+getCorrectColor(obj.background.color)+";";
            str += "background-image: none;";
            break;
        default :
            str += "background-image: none;";
            str += "background-color: rgba(0, 0, 0, 0);";
    }
    str += "}";
    
    return str;
}

function getContentSliderRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > .ba-slideshow {";
    str += "border-bottom-width : "+(obj.border.width * obj.border.bottom)+"px;";
    str += "border-color : "+getCorrectColor(obj.border.color)+";";
    str += "border-left-width : "+(obj.border.width * obj.border.left)+"px;";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "border-right-width : "+(obj.border.width * obj.border.right)+"px;";
    str += "border-style : "+obj.border.style+";";
    str += "border-top-width : "+(obj.border.width * obj.border.top)+"px;";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper {";
    str += "min-height: "+(obj.view.fullscreen ? "100vh" : "auto")+";";
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > ul > .slideshow-content, #"+selector+" > .slideshow-wrapper > ul > .empty-list {";
    str += "height:"+obj.view.height+"px;";
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > .ba-slideshow > .slideshow-content > li.item > .ba-grid-column {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-nav {";
    str += 'display:'+(obj.view.arrows == 1 ? 'block' : 'none')+';';
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-nav a {";
    str += "font-size: "+obj.arrows.size+"px;";
    str += "width: "+obj.arrows.size+"px;";
    str += "height: "+obj.arrows.size+"px;";
    str += "background-color: "+getCorrectColor(obj.arrows.normal.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.normal.color)+";";
    str += "padding : "+obj.arrows.padding+"px;";
    str += "box-shadow: 0 "+(obj.arrows.shadow.value * 10);
    str += "px "+(obj.arrows.shadow.value * 20)+"px 0 "+getCorrectColor(obj.arrows.shadow.color)+";";
    str += "border : "+obj.arrows.border.width+"px "+obj.arrows.border.style+" "+getCorrectColor(obj.arrows.border.color)+";";
    str += "border-radius : "+obj.arrows.border.radius+"px;";
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-nav a:hover {";
    str += "background-color: "+getCorrectColor(obj.arrows.hover.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.hover.color)+";";
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-dots {";
    str += 'display:'+(obj.view.dots == 1 ? 'flex' : 'none')+';';
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-dots > div {";
    str += "font-size: "+obj.dots.size+"px;";
    str += "width: "+obj.dots.size+"px;";
    str += "height: "+obj.dots.size+"px;";
    str += "color: "+getCorrectColor(obj.dots.normal.color)+";";
    str += "}";
    str += "#"+selector+" > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-dots > div:hover,#"+selector;
    str += " > .slideshow-wrapper > .ba-slideshow > .ba-slideshow-dots > div.active {";
    str += "color: "+getCorrectColor(obj.dots.hover.color)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getFeatureBoxRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-feature-box {";
    str += "width: calc((100% - "+((obj.view.count - 1) * 30)+"px) / "+obj.view.count+")";
    str += "}";
    str += "#"+selector+" .ba-feature-box:nth-child(n) {";
    str += "margin-right: 30px;";
    str += "margin-top: 30px;";
    str += "}";
    str += "#"+selector+" .ba-feature-box:nth-child("+obj.view.count+"n) {";
    str += "margin-right: 0;";
    str += "}";
    for (var i = 0; i < obj.view.count; i++) {
        str += "#"+selector+" .ba-feature-box:nth-child("+(i + 1)+") {";
        str += "margin-top: 0;";
        str += "}";
    }
    str += "#"+selector+" .ba-feature-box:hover {";
    str += "background-color: "+getCorrectColor(obj.background.hover.color)+";";
    str += "box-shadow: 0 "+(obj.shadow.hover.value * 10);
    str += "px "+(obj.shadow.hover.value * 20)+"px 0 "+getCorrectColor(obj.shadow.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-feature-box:hover .ba-feature-image-wrapper i {";
    str += "color : "+getCorrectColor(obj.icon.hover.color)+";";
    str += "background-color : "+getCorrectColor(obj.icon.hover.background)+";";
    str += "}";
    str += "#"+selector+" .ba-feature-box:hover .ba-feature-title {";
    str += "color : "+getCorrectColor(obj.title.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-feature-box:hover .ba-feature-description-wrapper * {";
    str += "color : "+getCorrectColor(obj.description.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-feature-box {";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.shadow.normal.value * 10);
    str += "px "+(obj.shadow.normal.value * 20)+"px 0 "+getCorrectColor(obj.shadow.normal.color)+";";
    str += "background-color: "+getCorrectColor(obj.background.normal.color)+";";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "}";
    str += '#'+selector+' .ba-feature-image-wrapper[data-type="icon"] {';
    str += "text-align: "+obj.icon['text-align']+";";
    str += "}";
    str += '#'+selector+' .ba-feature-image-wrapper:not([data-type="icon"]) {';
    str += "text-align: "+obj.image['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-feature-image-wrapper .ba-feature-image {";
    str += "width : "+obj.image.width+"px;";
    str += "height : "+obj.image.height+"px;";
    str += "border : "+obj.image.border.width+"px "+obj.image.border.style+" "+getCorrectColor(obj.image.border.color)+";";
    str += "border-radius : "+obj.image.border.radius+"px;";
    str += "}";
    str += "#"+selector+" .ba-feature-image-wrapper i {";
    str += "padding : "+obj.icon.padding+"px;";
    str += "font-size : "+obj.icon.size+"px;";
    str += "border : "+obj.icon.border.width+"px "+obj.icon.border.style+" "+getCorrectColor(obj.icon.border.color)+";";
    str += "border-radius : "+obj.icon.border.radius+"px;";
    str += "color : "+getCorrectColor(obj.icon.normal.color)+";";
    str += "background-color : "+getCorrectColor(obj.icon.normal.background)+";";
    str += "}";
    str += "#"+selector+" .ba-feature-title {";
    str += getTypographyRule(obj.title.typography);
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-feature-description-wrapper {";
    str += "text-align :"+obj.description.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-feature-description-wrapper * {";
    str += getTypographyRule(obj.description.typography, 'text-align');
    for (var ind in obj.description.margin) {
        str += "margin-"+ind+" : "+obj.description.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-feature-button {";
    str += "text-align :"+obj.button.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-feature-button:not(.empty-content) a {";
    for (var ind in obj.button.margin) {
        str += "margin-"+ind+" : "+obj.button.margin[ind]+"px;";
    }
    str += getTypographyRule(obj.button.typography, 'text-align');
    str += "border : "+obj.button.border.width+"px "+obj.button.border.style+" "+getCorrectColor(obj.button.border.color)+";";
    str += "border-radius : "+obj.button.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.button.shadow.value * 10);
    str += "px "+(obj.button.shadow.value * 20)+"px 0 "+getCorrectColor(obj.button.shadow.color)+";";
    str += "background-color: "+getCorrectColor(obj.button.normal.background)+";";
    str += "color: "+getCorrectColor(obj.button.normal.color)+";";
    for (var ind in obj.button.padding) {
        str += "padding-"+ind+" : "+obj.button.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-feature-button a:hover {";
    str += "background-color: "+getCorrectColor(obj.button.hover.background)+";";
    str += "color: "+getCorrectColor(obj.button.hover.color)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getSlideshowRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    for (var ind in obj.slides) {
        if (obj.slides[ind].image) {
            str += "#"+selector+" li.item:nth-child("+ind+") .ba-slideshow-img {background-image: url(";
            if (obj.slides[ind].image.indexOf('balbooa.com') != -1) {
                str += obj.slides[ind].image+");";
            } else {
                str += JUri+encodeURI(obj.slides[ind].image)+");";
            }
            str += "}";
            str += "#"+selector+' .thumbnails-dots div[data-ba-slide-to="'+(ind * 1 - 1)+'"] {background-image: url(';
            if (obj.slides[ind].image.indexOf('balbooa.com') != -1) {
                str += obj.slides[ind].image+");";
            } else {
                str += JUri+encodeURI(obj.slides[ind].image)+");";
            }
            str += "}";
        } else if (obj.slides[ind].type == 'video' && obj.slides[ind].video.type == 'youtube') {
            str += "#"+selector+' .thumbnails-dots div[data-ba-slide-to="'+(ind * 1 - 1)+'"] {';
            str += 'background-image: url(https://img.youtube.com/vi/'+obj.slides[ind].video.id+'/maxresdefault.jpg);';
            str += "}";
        } else if (obj.slides[ind].type == 'video' && obj.slides[ind].video.type == 'vimeo' && obj.slides[ind].video.thumbnail) {
            str += "#"+selector+' .thumbnails-dots div[data-ba-slide-to="'+(ind * 1 - 1)+'"] {';
            str += 'background-image: url('+obj.slides[ind].video.thumbnail+');';
            str += "}";
        } else if (obj.slides[ind].type == 'video' && !obj.slides[ind].video.thumbnail) {
            str += "#"+selector+' .thumbnails-dots div[data-ba-slide-to="'+(ind * 1 - 1)+'"] {';
            str += 'background-image: url('+JUri+'components/com_gridbox/assets/images/thumb-square.png);';
            str += "}";
        }
    }
    str += "#"+selector+" .slideshow-wrapper {";
    if (obj.view.fullscreen) {
        str += "min-height: 100vh;";
    } else {
        str += "min-height: auto;";
    }
    str += "}";
    str += "#"+selector+" .slideshow-content, #"+selector+" .empty-list {";
    str += "height:"+obj.view.height+"px;";
    str += "}";
    str += "#"+selector+" .ba-slideshow-img, #"+selector+" .thumbnails-dots div {";
    str += "background-size :"+obj.view.size+";";
    str += "}";
    str += "#"+selector+" .ba-overlay {background-color:";
    if (!obj.overlay.type || obj.overlay.type == 'color') {
        str += getCorrectColor(obj.overlay.color)+";";
        str += 'background-image: none;';
    } else if (obj.overlay.type == 'none') {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: none;';
    } else {
        str += 'rgba(0, 0, 0, 0);';
        str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
        if (obj.overlay.gradient.effect == 'linear') {
            str += obj.overlay.gradient.angle+'deg';
        } else {
            str += 'circle';
        }
        str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
        str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
        str += ' '+obj.overlay.gradient.position2+'%);';
        str += 'background-attachment: scroll;';
    }
    str += "height:"+obj.view.height+"px;";
    str += "}";
    str += "#"+selector+" .slideshow-title-wrapper {";
    str += "text-align :"+obj.title.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-slideshow-title {";
    str += "animation-duration :"+obj.title.animation.duration+"s;";
    str += "animation-delay :"+(obj.title.animation.delay ? obj.title.animation.delay : 0)+"s;";
    str += getTypographyRule(obj.title.typography, 'text-align');
    for (var ind in obj.title.margin) {
        str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .slideshow-description-wrapper {";
    str += "text-align :"+obj.description.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .ba-slideshow-description {";
    str += "animation-duration :"+obj.description.animation.duration+"s;";
    str += "animation-delay :"+(obj.description.animation.delay ? obj.description.animation.delay : 0)+"s;";
    str += getTypographyRule(obj.description.typography, 'text-align');
    for (var ind in obj.description.margin) {
        str += "margin-"+ind+" : "+obj.description.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .slideshow-button {";
    str += "text-align :"+obj.button.typography['text-align']+";";
    str += "}";
    str += "#"+selector+" .slideshow-button:not(.empty-content) a {";
    str += "animation-duration :"+obj.button.animation.duration+"s;";
    str += "animation-delay :"+(obj.button.animation.delay ? obj.button.animation.delay : 0)+"s;";
    for (var ind in obj.button.margin) {
        str += "margin-"+ind+" : "+obj.button.margin[ind]+"px;";
    }
    str += getTypographyRule(obj.button.typography, 'text-align');
    str += "border : "+obj.button.border.width+"px "+obj.button.border.style+" "+getCorrectColor(obj.button.border.color)+";";
    str += "border-radius : "+obj.button.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.button.shadow.value * 10);
    str += "px "+(obj.button.shadow.value * 20)+"px 0 "+getCorrectColor(obj.button.shadow.color)+";";
    str += "background-color: "+getCorrectColor(obj.button.normal.background)+";";
    str += "color: "+getCorrectColor(obj.button.normal.color)+";";
    for (var ind in obj.button.padding) {
        str += "padding-"+ind+" : "+obj.button.padding[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .slideshow-button a:hover {";
    str += "background-color: "+getCorrectColor(obj.button.hover.background)+";";
    str += "color: "+getCorrectColor(obj.button.hover.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideshow-nav {";
    if (obj.view.arrows == 1) {
        str += 'display:block;';
    } else {
        str += 'display:none;';
    }
    str += "}";
    str += "#"+selector+" .ba-slideshow-nav a {";
    str += "font-size: "+obj.arrows.size+"px;";
    str += "width: "+obj.arrows.size+"px;";
    str += "height: "+obj.arrows.size+"px;";
    str += "background-color: "+getCorrectColor(obj.arrows.normal.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.normal.color)+";";
    str += "padding : "+obj.arrows.padding+"px;";
    str += "box-shadow: 0 "+(obj.arrows.shadow.value * 10);
    str += "px "+(obj.arrows.shadow.value * 20)+"px 0 "+getCorrectColor(obj.arrows.shadow.color)+";";
    str += "border : "+obj.arrows.border.width+"px "+obj.arrows.border.style+" "+getCorrectColor(obj.arrows.border.color)+";";
    str += "border-radius : "+obj.arrows.border.radius+"px;";
    str += "}";
    str += "#"+selector+" .ba-slideshow-nav a:hover {";
    str += "background-color: "+getCorrectColor(obj.arrows.hover.background)+";";
    str += "color: "+getCorrectColor(obj.arrows.hover.color)+";";
    str += "}";
    if (!obj.thumbnails) {
        str += "#"+selector+" .ba-slideshow-dots {";
        str += 'display:'+(obj.view.dots == 1 ? 'flex' : 'none')+';';
        str += "}";
    } else {
        str += "#"+selector+" .slideshow-wrapper {";
        str += "--thumbnails-count:" +obj.thumbnails.count+";";
        str += "--bottom-thumbnails-height: "+obj.thumbnails.height+"px;";
        if (obj.thumbnails.width) {
            str += "--left-thumbnails-width: "+obj.thumbnails.width+"px;";
        }
        str += "}";
    }
    str += "#"+selector+" .ba-slideshow-dots:not(.thumbnails-dots) > div {";
    str += "font-size: "+obj.dots.size+"px;";
    str += "width: "+obj.dots.size+"px;";
    str += "height: "+obj.dots.size+"px;";
    str += "color: "+getCorrectColor(obj.dots.normal.color)+";";
    str += "}";
    str += "#"+selector+" .ba-slideshow-dots:not(.thumbnails-dots) > div:hover,#"+selector;
    str += " .ba-slideshow-dots:not(.thumbnails-dots) > div.active {";
    str += "color: "+getCorrectColor(obj.dots.hover.color)+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);
    
    return str;
}

function getVideoRules(obj, selector)
{
    var str = "#"+selector+" .ba-video-wrapper {";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += "padding-bottom: calc(56.24% - "+obj.border.width+"px);";
    str += "}";
    str += "#"+selector+" {";
    for (var ind in obj.margin) {
        str += 'margin-'+ind+": "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getPreloaderRules(obj, selector)
{
    var str = "#"+selector+" .preloader-wrapper, #"+selector+" .preloader-wrapper:before, ";
    str += "#"+selector+" .preloader-wrapper:after {";
    str += "background-color: "+getCorrectColor(obj.background)+";";
    str += "}";
    str += "#"+selector+" .preloader-wrapper:before, #"+selector+" .preloader-wrapper:after {";
    str += "border-color: "+getCorrectColor(obj.background)+";";
    str += "}";
    str += "#"+selector+" .preloader-point-wrapper {";
    str += "width: "+obj.size+"px;";
    str += "height: "+obj.size+"px;";
    str += "}";
    str += "#"+selector+" .preloader-point-wrapper div, #"+selector+" .preloader-point-wrapper div:before {";
    str += "background-color: "+getCorrectColor(obj.color)+";";
    str += "}";
    str += "#"+selector+" .preloader-image-wrapper {";
    str += "width: "+obj.width+"px;";
    str += "}";
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getImageRules(obj, selector)
{
    var str = "#"+selector+" {";
    str += "text-align: "+obj.style.align+";";
    for (var ind in obj.margin) {
        str += 'margin-'+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "}";
    str += "#"+selector+" .ba-image-wrapper {";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += "width: "+obj.style.width+"px;";
    str += "}";
    if (obj.overlay) {
        str += "#"+selector+" .ba-image-wrapper {";
        str += "transition-duration: "+obj.animation.duration+"s;"
        str += "}";
        str += "#"+selector+" .ba-image-item-caption .ba-caption-overlay {background-color :";
        if (!obj.overlay.type || obj.overlay.type == 'color') {
            str += getCorrectColor(obj.overlay.color)+";";
            str += 'background-image: none;';
        } else if (obj.overlay.type == 'none') {
            str += 'rgba(0, 0, 0, 0);';
            str += 'background-image: none;';
        } else {
            str += 'rgba(0, 0, 0, 0);';
            str += 'background-image: '+obj.overlay.gradient.effect+'-gradient(';
            if (obj.overlay.gradient.effect == 'linear') {
                str += obj.overlay.gradient.angle+'deg';
            } else {
                str += 'circle';
            }
            str += ', '+getCorrectColor(obj.overlay.gradient.color1)+' ';
            str += obj.overlay.gradient.position1+'%, '+getCorrectColor(obj.overlay.gradient.color2);
            str += ' '+obj.overlay.gradient.position2+'%);';
            str += 'background-attachment: scroll;';
        }
        str += "}";
        str += "#"+selector+" .ba-image-item-title {";
        str += getTypographyRule(obj.title.typography);
        for (var ind in obj.title.margin) {
            str += "margin-"+ind+" : "+obj.title.margin[ind]+"px;";
        }
        str += "}";
        str += "#"+selector+" .ba-image-item-description {";
        str += getTypographyRule(obj.description.typography);
        for (var ind in obj.description.margin) {
            str += "margin-"+ind+" : "+obj.description.margin[ind]+"px;";
        }
        str += "}";
    }
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getScrollTopRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    if (obj.icons.align) {
        str += "text-align : "+obj.icons.align+";";
    }
    str += "}";
    str += "#"+selector+" i.ba-btn-transition {";
    for (var ind in obj.padding) {
        str += "padding-"+ind+" : "+obj.padding[ind]+"px;";
    }
    str += "box-shadow: 0 "+(obj.shadow.value * 10);
    str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
    str += "border : "+obj.border.width+"px "+obj.border.style+" "+getCorrectColor(obj.border.color)+";";
    str += "border-radius : "+obj.border.radius+"px;";
    str += "font-size : "+obj.icons.size+"px;";
    str += "width : "+obj.icons.size+"px;";
    str += "height : "+obj.icons.size+"px;";
    str += "color : "+getCorrectColor(obj.normal.color)+";";
    str += "background-color : "+getCorrectColor(obj.normal['background-color'])+";";
    str += "}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getLogoRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var ind in obj.margin) {
        str += "margin-"+ind+" : "+obj.margin[ind]+"px;";
    }
    str += "text-align: "+obj['text-align']+";";
    str += "}";
    str += "#"+selector+" img {";
    str += "width: "+obj.width+"px;}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

function getMapRules(obj, selector)
{
    var str = "#"+selector+" {";
    for (var key in obj) {
        switch (key) {
            case 'margin' :
                for (var ind in obj[key]) {
                    str += key+'-'+ind+" : "+obj[key][ind]+"px;";
                }
                break;
            case 'shadow' : 
                str += "box-shadow: 0 "+(obj.shadow.value * 10);
                str += "px "+(obj.shadow.value * 20)+"px 0 "+getCorrectColor(obj.shadow.color)+";";
                break;
        }
    }
    str += "}";
    str += "#"+selector+" .ba-map-wrapper {";
    str += "height: "+obj.height+"px;}";
    str += setBoxModel(obj, selector);
    str += setItemsVisability(obj.disable, "block", '#'+selector);

    return str;
}

app.sectionRules();