/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.editItem = function(obj, key){
    restoreTabs(app.edit);
    var parent = window.parent.app,
        type = app.items[app.edit].type;
    parent.edit = app.items[app.edit];
    if (type == 'row' && $g('#'+app.edit).parent().parent().hasClass('ba-grid-column')) {
        type = 'nested-row';
    }
    var title = getItemTitle(type),
        modals = window.parent.$g('.ba-modal-cp.draggable-modal-cp').not('#theme-settings-dialog');
    modals.find('.ba-dialog-title').text(title);
    modals.find('.modal-header > span.status-icons').remove();
    if (app.items[app.edit].preset) {
        var str = '<span class="status-icons"><i class="zmdi zmdi-roller"></i><span class="ba-tooltip ba-top">'+
            window.parent.gridboxLanguage['PRESET']+'</span></span>';
        modals.find('.ba-dialog-title').after(str);
    }
    if (document.getElementById(app.edit).dataset.global) {
        var str = '<span class="status-icons"><i class="zmdi zmdi-globe"></i><span class="ba-tooltip ba-top">'+
            window.parent.gridboxLanguage['GLOBAL_ITEM']+'</span></span>';
        modals.find('.ba-dialog-title').after(str);
    }
    switch (app.items[app.edit].type) {
        case 'checkout-form': 
            parent.checkModule('customerInfoEditor');
            break
        case 'comments-box': 
        case 'reviews': 
            parent.checkModule('commentsBoxEditor');
            break
        case 'recent-comments':
        case 'recent-reviews':
            parent.checkModule('recentCommentsEditor');
            break
        case 'field':
        case 'field-group':
            parent.checkModule('fieldEditor');
            break;
        case 'add-to-cart':
            parent.checkModule('addToCartEditor');
            break;
        case 'fields-filter':
            parent.checkModule('fieldsFilterEditor');
            break;
        case 'feature-box':
            parent.checkModule('featureBoxEditor');
            break;
        case 'search':
        case 'store-search':
            parent.checkModule('searchEditor');
            break;
        case 'yandex-maps':
            parent.checkModule('yandexMapsEditor');
            break;
        case 'icon-list':
            parent.checkModule('iconListEditor');
            break;
        case 'preloader':
            parent.checkModule('preloaderEditor');
            break;
        case 'recent-posts' :
        case 'search-result' :
        case 'store-search-result' :
        case 'related-posts' :
        case 'post-navigation' :
        case 'author' :
            parent.checkModule('recentPostsEditor');
            break;
        case 'blog-posts' :
            parent.checkModule('blogPostsEditor');
            break;
        case 'star-ratings' :
            parent.checkModule('starRatingsEditor');
            break;
        case 'post-intro' :
        case 'category-intro' :
            parent.checkModule('introPostEditor');
            break;
        case 'blog-content' :
            break;
        case 'disqus' :
        case 'vk-comments' :
        case 'hypercomments' :
        case 'facebook-comments' :
        case 'gallery' :
        case 'modules' :
        case 'forms' :
        case 'logo' :
        case 'simple-gallery':
        case 'field-simple-gallery':
        case 'product-gallery':
            parent.checkModule('itemEditor');
            break;
        case 'event-calendar':
            parent.checkModule('eventCalendarEditor');
            break;
        case 'video':
        case 'field-video':
        case 'image-field':
            parent.checkModule('imageEditor');
            break;
        case 'accordion' :
        case 'tabs' :
            parent.checkModule('tabsEditor');
            break;
        case 'field-google-maps':
        case 'google-maps-places':
            parent.checkModule('mapEditor');
            break;
        case 'image' :
        case 'text' :
        case 'map' :
        case 'social' :
        case 'slideshow' :
        case 'categories' :
        case 'headline' :
        case 'openstreetmap' :
            parent.checkModule(app.items[app.edit].type+'Editor');
            break;
        case 'testimonials-slider' :
            parent.checkModule('testimonialsEditor');
            break;
        case 'search-result-headline' :
            parent.checkModule('headlineEditor');
            break;
        case 'weather':
        case 'error-message':
            parent.checkModule('weatherEditor');
            break;
        case 'field-slideshow':
        case 'product-slideshow':
        case 'slideset':
        case 'carousel':
        case 'recent-posts-slider':
        case 'related-posts-slider':
        case 'recently-viewed-products':
            parent.checkModule('slideshowEditor');
            break;
        case 'one-page' :
        case 'menu' :
            parent.checkModule('menuEditor');
            break;
        case 'social-icons' :
            parent.checkModule('socialIconsEditor');
            break;
        case 'content-slider' :
            parent.checkModule('contentSliderEditor');
            break;
        case 'cart':
        case 'wishlist':
            parent.checkModule('cartEditor');
            break;
        case 'icon' :
        case 'button':
        case 'tags' :
        case 'post-tags' :
        case 'overlay-button' :
        case 'scroll-to-top' :
        case 'scroll-to' :
        case 'countdown' :
        case 'counter' :
            parent.checkModule('countdownEditor');
            break;
        case 'progress-bar' :
        case 'progress-pie' :
            parent.checkModule('progressBarEditor');
            break;
        case 'custom-html' :
            parent.checkModule('editCustomHtml');
            break;
        default :
            parent.checkModule('sectionEditor');
    }
}

function getItemTitle(type)
{
    var title = type.toUpperCase().replace(/-/g, '_');
    if (title == 'SOCIAL') {
        title = 'SOCIAL_SHARE';
    } else if (title == 'SEARCH_RESULT') {
        title = 'SEARCH';
    } else if (title == 'OVERLAY_BUTTON') {
        title = 'OVERLAY_SECTION'
    } else if (title == 'SCROLL_TO') {
        title = 'SMOOTH_SCROLLING';
    } else if (title == 'ONE_PAGE') {
        title = 'ONE_PAGE_MENU';
    } else if (title == 'FORMS' || title == 'GALLERY') {
        title = 'BALBOOA_'+title;
    } else if (title == 'MODULES') {
        title = 'JOOMLA_MODULES'
    } else if (title == 'MEGA_MENU_SECTION') {
        title = 'MEGAMENU';
    } else if (title == 'RECENT_POSTS_SLIDER') {
        title = 'POST_SLIDER';
    } else if (title == 'BLOG_POSTS') {
        title = 'CATEGORY_LIST';
    } else if (title == 'AUTHOR') {
        title = 'AUTHOR_BOX';
    } else if (title == 'IMAGE_FIELD') {
        title = 'FIELD_IMAGE';
    } else if (title == 'FIELDS_FILTER') {
        title = 'CONTENT_FILTERS';
    } else if (title == 'CHECKOUT_FORM') {
        title = 'CUSTOMER_INFO';
    }

    return window.parent.gridboxLanguage[title] ? window.parent.gridboxLanguage[title] : title.toLowerCase().replace(/_/g, ' ');
}

app.editItem();