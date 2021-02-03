/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.codemirror = function(){
    setTimeout(function(){
        $g('#code-editor-dialog').one('shown', function(){
            app.codeCss.refresh();
        }).modal();
    }, 50);
}

var func = function(){
    $g('#code-editor-dialog a[href="#code-edit-javascript"]').one('shown', function(){
        app.codeJs.refresh();
    });
    app.modules.codemirror = true;
    app.codemirror();
}
if (!app.modules.loadCodemirror && !app.loading.loadCodemirror) {
    if (!app.actionStack['loadCodemirror']) {
        app.actionStack['loadCodemirror'] = new Array();
    }
    app.actionStack['loadCodemirror'].push(func);
    app.checkModule('loadCodemirror');
} else {
    func();
}