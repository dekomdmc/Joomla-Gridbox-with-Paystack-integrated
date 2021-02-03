/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.initText = function(obj, key){
    $g('#'+key).on('click', 'a[href]', function(event){
        if (this.hash && this.href == window.location.href.replace(window.location.hash, '')+this.hash) {
            let target = $g(this.hash+', [name="'+this.hash.replace('#', '')+'"]');
            if (target.length) {
                event.preventDefault();
                window.history.replaceState(null, null, this.href);
                let position = window.compileOnePageValue ? compileOnePageValue(target) : target.offset().top;
                $g('html, body').stop().animate({
                    scrollTop: position
                }, 500);
            }
        }
    });
    initItems();
}

if (app.modules.initText) {
    app.initText(app.modules.initText.data, app.modules.initText.selector);
}