/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.addLibrary = function(){
    setTimeout(function(){
        $g('.library-item-title, .library-item-image').val('');
        $g('.save-as-global').prop('checked', false);
        $g('#add-to-library-dialog').modal();
    }, 50);
}

$g('.library-item-image').on('mousedown', function(){
    uploadMode = 'LibraryImage';
    checkIframe($g('#uploader-modal').attr('data-check', 'single'), 'uploader');
});

$g('.library-item-title').on('input', function(){
    if (this.value.trim()) {
        $g('#library-apply').addClass('active-button').removeClass('disable-button');
    } else {
        $g('#library-apply').removeClass('active-button').addClass('disable-button');
    }
});

$g('#library-apply').on('click', function(){
    if (this.classList.contains('active-button')) {
        var item = app.editor.document.getElementById(app.editor.app.edit),
            items = {},
            obj = {
                title : $g('.library-item-title').val().trim(),
                image : $g('.library-item-image').val(),
                item : {},
                type : 'section',
                global_item : $g('.save-as-global').prop('checked')
            };
        if (app.editor.app.items[app.editor.app.edit].type != 'section') {
            obj.type = 'plugin';
            if (app.editor.app.items[item.id].type == 'overlay-button') {
                var overlay =  app.editor.document.querySelector('.ba-overlay-section-backdrop[data-id="'+item.dataset.overlay+'"]');
                item.appendChild(overlay);
            }
        }
        app.editor.$g('.ba-overlay-section-backdrop').each(function(){
            var button = app.editor.document.querySelector('.ba-item-overlay-section[data-overlay="'+this.dataset.id+'"]');
            if (button) {
                button.appendChild(this);
            }
        });
        if (obj.global_item) {
            obj.global_item = item.id;
            if (obj.type == 'section') {
                item.parentNode.dataset.global = item.id;
            } else {
                item.dataset.global = item.id;
            }
        } else {
            obj.global_item = '';
        }
        if (obj.type == 'section') {
            item = item.parentNode;
        }
        item = item.cloneNode(true);
        $g(item).find('.ba-menu-wrapper .tabs-content-wrapper').each(function(){
            $g(this).closest('.ba-menu-wrapper').append(this);
        });
        $g(item).find('.ba-section').each(function(){
            if (app.editor.app.items[this.id]) {
                items[this.id] = app.editor.app.items[this.id];
            }
        });
        $g(item).find('.ba-row').each(function(){
            if (app.editor.app.items[this.id]) {
                items[this.id] = app.editor.app.items[this.id];
            }
        });
        $g(item).find('.ba-grid-column').each(function(){
            if (app.editor.app.items[this.id]) {
                items[this.id] = app.editor.app.items[this.id];
            }
        });
        $g(item).find('.ba-item').each(function(){
            if (app.editor.app.items[this.id]) {
                items[this.id] = app.editor.app.items[this.id];
                prepareItem(this, items[this.id]);
            }
        });
        if (obj.type != 'section') {
            if (app.editor.app.items[item.id]) {
                items[item.id] = app.editor.app.items[item.id];
                prepareItem(item, items[item.id]);
            }
        }
        obj.item.items = items;
        obj.item.html = item.outerHTML;
        var data = JSON.stringify(obj),
            url = JUri+"index.php?option=com_gridbox&task=editor.addLibrary"
        $g.ajax({
            type:"POST",
            dataType:'text',
            url:url,
            data:{
                object : data
            },
            complete: function(msg){
                if (msg.responseText == 'empty_data') {
                    var XHR = new XMLHttpRequest();
                    url = JUri+"index.php?option=com_gridbox&task=editor.requestAddLibrary"
                    XHR.onreadystatechange = function(e) {
                        if (XHR.readyState == 4) {
                            var obj = JSON.parse(XHR.responseText);
                            app.showNotice(obj.text, obj.type);
                        }
                    };
                    XHR.open("POST", url, true);
                    XHR.send(data);
                } else {
                    var obj = JSON.parse(msg.responseText);
                    app.showNotice(obj.text, obj.type);
                }
            }
        });
        app.editor.app.checkModule('checkOverlay');
        $g('#add-to-library-dialog').modal('hide');
    }
});

app.modules.addLibrary = true;
app.addLibrary();