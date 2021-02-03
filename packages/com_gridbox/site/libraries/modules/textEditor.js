/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.textEditor = function() {
    $g('#text-editor-dialog .active').removeClass('active');
    $g('#text-editor-dialog a[href="#text-editor-general-options"]').parent().addClass('active');
    $g('#text-editor-general-options').addClass('active');
    app.selector = '#'+app.editor.app.edit;
    var array = new Array('h1' ,'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'links');
    if (app.edit.global) {
        delete(app.edit.global);
        array.forEach(function(el){
            delete(app.edit.desktop[el]);
            for (var ind in app.editor.breakpoints) {
                delete(app.edit[ind][el]);
            }
        });
    }
    if (!app.edit.desktop.p) {
        array.forEach(function(el){
            if (el != 'links') {
                app.edit.desktop[el] = {
                    "font-family" : "@default",
                    "font-style" : "@default"
                };
                for (var ind in app.editor.breakpoints) {
                    app.edit[ind][el] = {};
                }
            }
        });
    }
    if (!app.edit.desktop.links) {
        app.edit.desktop.links = {};
    }
    value = app.editor.document.querySelector(app.selector+' > .content-text > *');
    if (value) {
        value = value.localName;
        if (array.indexOf(value) == -1) {
            value = 'h1';
        }
    } else {
        value = 'h1';
    }
    $g('#text-editor-dialog .typography-select input[type="hidden"]').val(value);
    $g('#text-editor-dialog .typography-select input[type="text"]').val(value.toUpperCase().replace('P', 'Paragraph'));
    app.setTypography($g('#text-editor-dialog .typography-options'), value);
    $g('#text-editor-dialog .typography-options .ba-settings-item').css('display', '').last().hide().prev().hide();
    $g('#text-editor-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    var value = $g('#text-editor-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#text-editor-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#text-editor-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#text-editor-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#text-editor-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#text-editor-dialog');
    setTimeout(function(){
        if ('WFEditor' in window || CKE.loaded) {
            app.setTextContent();
            $g('#text-editor-dialog').modal();
        }
    }, 150);
}

app.setTextContent = function(){
    var item = app.editor.document.querySelector(app.selector+' .content-text');
    app.setContent(item.innerHTML);
    app.editor.$g(item).trigger('input');
}

app.getTextContent = function(textarea){
    if (textarea) {
        app.editor.document.querySelector(app.selector+' .content-text').innerHTML = textarea.value;
    } else {
        app.editor.document.querySelector(app.selector+' .content-text').innerHTML = app.getContent();
    }
    app.editor.$g(app.selector+' .content-text').trigger('input');
    clearTimeout(this.gridboxTextDelay);
    this.gridboxTextDelay = setTimeout(function(){
        app.addHistory();
    }, 300);
}

if ('WFEditor' in window) {
    app.setContent = function(data){
        WFEditor.setContent('editor', data);
    }
    app.getContent = function(){
        var data = WFEditor.getContent('editor');

        return data;
    }
    WFEditor._getEditor('editor').onKeyUp.add(function(){
        app.getTextContent();
    });
    WFEditor._getEditor('editor').onChange.add(function(){
        app.getTextContent();
    });
    $g('#editor').on('keyup', function(){
        app.getTextContent();
    });
    $g('.ba-editor-wrapper').addClass('jce-editor-enabled');
} else {
    app.setContent = function(data){
        CKE.setData(data);
    }
    app.getContent = function(){
        var data = CKE.getData();

        return data;
    }
    CKE.on('change', function(){
        app.getTextContent();
    });
    CKE.on('selectionChange', function(){
        CKE.plugins.myTextColor.setBtnColorEvent(CKE);
    });
    $g('#cke_1_contents').on('keyup', 'textarea', function(){
        app.getTextContent(this);
    });
}

$g('#text-editor-dialog .resize-text-editor').on('mousedown', function(event){
    event.preventDefault();
    event.stopPropagation();
    var $this = $g(this),
        modal = $g('#text-editor-dialog'),
        offset = modal[0].getBoundingClientRect();
        left = offset.left,
        right = document.documentElement.clientWidth - offset.right;
    if (left + 970 > document.documentElement.clientWidth) {
        left = 'auto';
    } else {
        right = 'auto';
    }
    if ($this.hasClass('zmdi-fullscreen')) {
        $this.removeClass('zmdi-fullscreen').addClass('zmdi-fullscreen-exit');
        modal.css({
            left : left,
            right : right,
            position: 'fixed',
            'margin-left': 0
        });
        if (left == 'auto' && offset.right - 970 < 0) {
            modal.animate({
                'right': document.documentElement.clientWidth - 995
            }, 300);
        }
        modal.addClass('text-editor-resized').addClass('text-editor-animation');
        setTimeout(function(){
            modal.removeClass('text-editor-animation');
        }, 300);
    } else {
        $this.removeClass('zmdi-fullscreen-exit').addClass('zmdi-fullscreen');
        modal.removeClass('text-editor-resized').addClass('text-editor-animation');
        setTimeout(function(){
            modal.removeClass('text-editor-animation');
            offset = modal[0].getBoundingClientRect();
            modal.css({
                left : offset.left,
                right : '',
            });
        }, 300);
    }
});

app.modules.textEditor = true;
app.textEditor();