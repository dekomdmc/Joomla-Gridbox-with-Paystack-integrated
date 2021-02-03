/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.socialEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#social-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    var value = $g('#social-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#social-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#social-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#social-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#social-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    $g('#social-settings-dialog input[data-option="counters"]').prop('checked', app.edit.view.counters);
    $g('#social-settings-dialog input[data-option="facebook"]').prop('checked', app.edit.facebook);
    $g('#social-settings-dialog input[data-option="twitter"]').prop('checked', app.edit.twitter);
    $g('#social-settings-dialog input[data-option="google"]').prop('checked', app.edit.google);
    $g('#social-settings-dialog input[data-option="linkedin"]').prop('checked', app.edit.linkedin);
    $g('#social-settings-dialog input[data-option="pinterest"]').prop('checked', app.edit.pinterest);
    $g('#social-settings-dialog input[data-option="vk"]').prop('checked', app.edit.vk);
    $g('#social-settings-dialog .social-layout-select input[type="hidden"]').val(app.edit.view.layout);
    value = $g('#social-settings-dialog .social-layout-select li[data-value="'+app.edit.view.layout+'"]').text();
    $g('#social-settings-dialog .social-layout-select input[readonly]').val($g.trim(value));
    $g('#social-settings-dialog .social-size-select input[type="hidden"]').val(app.edit.view.size);
    value = $g('#social-settings-dialog .social-size-select li[data-value="'+app.edit.view.size+'"]').text();
    $g('#social-settings-dialog .social-size-select input[readonly]').val($g.trim(value));
    $g('#social-settings-dialog .social-style-select input[type="hidden"]').val(app.edit.view.style);
    value = $g('#social-settings-dialog .social-style-select li[data-value="'+app.edit.view.style+'"]').text();
    $g('#social-settings-dialog .social-style-select input[readonly]').val($g.trim(value));
    setDisableState('#social-settings-dialog');
    $g('#social-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#social-settings-dialog').modal();
    }, 150);
}

$g('.show-social').on('change', function(){
    var option = this.dataset.option,
        n = 0,
        item = app.editor.document.querySelector('#'+app.editor.app.edit+' .'+option);
    $g('.show-social').each(function(){
        if (!this.checked) {
            n++;
        }
    });
    if (n == 5) {
        this.checked = true;
        n--;
    }
    if (this.checked) {
        item.style.display = '';
    } else {
        item.style.display = 'none';
    }
    app.edit[option] = this.checked;
    app.editor.document.querySelector(app.selector).style.setProperty('--social-count', 5 - n);
    app.addHistory();
});

app.modules.socialEditor = true;
app.socialEditor();