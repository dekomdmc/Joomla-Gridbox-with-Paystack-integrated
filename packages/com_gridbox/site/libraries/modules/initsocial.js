/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/
if (!$g.fn.social) {
    var file = document.createElement('script');
    file.onload = function(){
        if (app.modules.initsocial) {
            app.initsocial(app.modules.initsocial.data, app.modules.initsocial.selector);
        }
    }
    file.src = JUri+'components/com_gridbox/libraries/social/social.js';
    document.getElementsByTagName('head')[0].appendChild(file);
} else if (app.modules.initsocial) {
    app.initsocial(app.modules.initsocial.data, app.modules.initsocial.selector);
}

app.initsocial = function(obj, key){
    var item = $g('#'+key)[0];
    if ($g('#'+key).closest('.ba-item-blog-content').length > 0) {
        $g('#'+key).addClass('ba-item-in-blog-post');
    } else {
        $g('#'+key).removeClass('ba-item-in-blog-post');
    }
    if (item.classList.contains('ba-social-sidebar')) {
        if (item.parentNode.localName != 'body') {
            obj.parent = item.parentNode.id;
            document.body.appendChild(item);
        }
    }
    $g('#'+key+' .ba-social').social({
        "facebook" : obj.facebook,
        "twitter" : obj.twitter,
        "google" : obj.google,
        "linkedin" : obj.linkedin,
        "pinterest" : obj.pinterest,
        "vk" : obj.vk,
        "counters" : obj.view.counters
    });
    initItems();
}