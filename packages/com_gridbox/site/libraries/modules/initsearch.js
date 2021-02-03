/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.initsearch = function(obj, key){
    document.querySelector('#'+key+' .ba-search-wrapper input').addEventListener('keyup', function(event){
    	if (event.keyCode == 13 && this.value.trim() && themeData.page.view != 'gridbox') {
            window.location.href = this.dataset.searchUrl+this.value.trim();
        }
    });
    initItems();
}

if (app.modules.initsearch) {
	app.initsearch(app.modules.initsearch.data, app.modules.initsearch.selector);
}