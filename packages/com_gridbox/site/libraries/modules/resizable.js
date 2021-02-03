/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

var file = document.createElement('script');
file.type = 'text/javascript';
file.src = JUri+'components/com_gridbox/libraries/resizable/js/resizable.js';
document.getElementsByTagName('head')[0].appendChild(file);
file.onload = function(){
    $g('#code-editor-dialog').resizable({
        handle : '.resizable-handle-right',
        change : function(){
        	app.codeCss.refresh();
        	app.codeJs.refresh();
        }
    });
    $g('.draggable-modal-cp').find('.tab-content').resizable({
        handle : '.resize-handle-bottom',
        direction : 'bottom'
    });
    app.modules.resizable = true;
}