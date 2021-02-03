/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

var file = document.createElement('script');
file.type = 'text/javascript';
file.src = JUri+'components/com_gridbox/libraries/draggable/js/draggable.js';
document.getElementsByTagName('head')[0].appendChild(file);
file.onload = function(){
    $g('#code-editor-dialog, .draggable-modal-cp').draggable({
        'handle' : '.modal-header'
    });
    app.modules.draggable = true;
}