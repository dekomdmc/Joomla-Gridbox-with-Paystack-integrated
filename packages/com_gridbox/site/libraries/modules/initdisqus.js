/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.initdisqus = function(obj){
    $g('#disqus_thread').removeClass('empty-content').empty();
    if (obj.subdomen) {
        var disqus = document.createElement('script');
        disqus.type = 'text/javascript';
        disqus.async = true;
        if (typeof(DISQUS) != 'undefined') {
            delete(DISQUS)
        }
        disqus.src = '//'+obj.subdomen+'.disqus.com/embed.js';
        document.getElementsByTagName('head')[0].appendChild(disqus);
    } else {
        $g('#disqus_thread').addClass('empty-content');
    }
    initItems();
}

if (app.modules.initdisqus) {
    app.initdisqus(app.modules.initdisqus.data);
}