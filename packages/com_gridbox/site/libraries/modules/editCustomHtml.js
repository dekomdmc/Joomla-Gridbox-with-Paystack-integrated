/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.editCustomHtml = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#custom-html-dialog .show-general-cell').removeClass('show-general-cell').addClass('hide-general-cell');
    $g('#custom-html-dialog .active').removeClass('active');
    $g('#custom-html-dialog li').first().addClass('active');
    $g('#custom-edit-html').addClass('active');
    $g('#custom-html-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    var value = $g('#custom-html-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#custom-html-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#custom-html-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#custom-html-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#custom-html-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#custom-html-dialog');
    app.customHtmlEditor.setValue(app.edit.html);
    app.customCssEditor.setValue(app.edit.css);
    setTimeout(function(){
        $g('#custom-html-dialog').one('shown', function(){
            app.customHtmlEditor.refresh();
        }).modal();
    }, 150);
}

var func = function(){
    $g('#custom-html-dialog a').on('click', function(){
        delay = setInterval(function(){
            app.customCssEditor.refresh();
            app.customHtmlEditor.refresh();
        }, 50);
    }).on('shown', function(){
        clearInterval(delay);
        app.customCssEditor.refresh();
        app.customHtmlEditor.refresh();
    });
    app.modules.editCustomHtml = true;
    app.editCustomHtml();
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