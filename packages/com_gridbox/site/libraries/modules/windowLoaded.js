/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.addHistory = function(task){
    var div = document.createElement('div'),
        obj = {
            items : $g.extend(true, {}, app.editor.app.items),
            content : div
        };
    obj.items.body = $g.extend(true, {}, app.editor.app.theme);
    div.innerHTML = app.editor.document.body.innerHTML;
    var scrollTop = div.querySelectorAll('.ba-item-scroll-to-top, .ba-social-sidebar');
    for (var i = 0; i < scrollTop.length; i++) {
        var id = obj.items[scrollTop[i].id].parent,
            item = div.querySelector('#'+id);
        if (!item) {
            item = div.querySelector('.ba-grid-column');
            if (item) {
                obj.items[scrollTop[i].id].parent = item.id;
            }
        }
        if (item) {
            item.insertBefore(scrollTop[i], $g(item).find('> .empty-item')[0]);
        }
    };
    scrollTop = div.querySelectorAll('.ba-item-overlay-section');
    for (var i = 0; i < scrollTop.length; i++) {
        var overlay =  div.querySelector('.ba-overlay-section-backdrop[data-id="'+scrollTop[i].dataset.overlay+'"]');
        if (overlay) {
            overlay.classList.remove('visible-section');
            scrollTop[i].appendChild(overlay);
        }
    }
    if (task == 'init') {
        app.history = new Array();
        $g('.ba-action-undo').removeClass('active');
        $g('.ba-action-redo').removeClass('active');
    } else {
        app.history.length = app.hIndex;
        obj.edit = app.editor.app.edit;
        $g('.ba-action-undo').addClass('active');
        $g('.ba-action-redo').removeClass('active');
    }
    if (app.editor.app.blogEditor) {
        app.editor.$g('.content-text').each(function(){
            app.editor.setTextPlaceholder(this);
        });
    }
    app.history.push(obj);
    app.hIndex = app.history.length;
}

app.windowLoaded = function(){
    app.editor = window.frames['editor-iframe'];
    if (!app.editor.themeData.edit_type && !document.querySelector('.gridbox-apps-editor-wrapper')) {
        $g.ajax({
            type : "POST",
            dataType : 'text',
            url : "index.php?option=com_gridbox&task=editor.checkProductTour&tmpl=component",
            success : function(msg){
                if (msg == 'true') {
                    app.checkModule('productTour');
                }
            }
        });
    }
    app.checkModule('getSession');
    app.editor.app.loadModule('backgroundRule');
    app.loadModule('pageSettings');
}

app.modules.windowLoaded = true;
app.windowLoaded();