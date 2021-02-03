/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

if (!$g.fn.scrolltop) {
    var file = document.createElement('script');
    file.onload = function(){
        if (app.modules['initscroll-to-top']) {
            app['initscroll-to-top'](app.modules['initscroll-to-top'].data, app.modules['initscroll-to-top'].selector);
        }
    }
    file.src = JUri+'components/com_gridbox/libraries/scrolltop/scrolltop.js';
    document.getElementsByTagName('head')[0].appendChild(file);
} else if (app.modules['initscroll-to-top']) {
    app['initscroll-to-top'](app.modules['initscroll-to-top'].data, app.modules['initscroll-to-top'].selector);
}


app['initscroll-to-top'] = function(obj, key){
    var column = $g('#'+key).closest('.ba-grid-column')[0],
        item = $g('#'+key);
    if (item.closest('.ba-item-blog-content').length > 0) {
        item.addClass('ba-item-in-blog-post');
    } else {
        item.removeClass('ba-item-in-blog-post');
    }
    if (column) {
        app.items[key].parent = column.id;
        $g('body').append(item);
    }
    item.find('.ba-scroll-to-top i').scrolltop(obj.init);
    initItems();
}