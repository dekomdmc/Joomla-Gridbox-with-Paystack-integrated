/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.headlineEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#headline-settings-dialog .active').removeClass('active');
    $g('#headline-settings-dialog a[href="#headline-general-options"]').parent().addClass('active');
    $g('#headline-general-options').addClass('active');
    $g('#headline-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    var value = $g('#headline-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text().trim();
    $g('#headline-settings-dialog .section-access-select input[readonly]').val(value);
    $g('#headline-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#headline-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#headline-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#headline-settings-dialog');
    if (app.edit.type == 'headline') {
        $g('.headline-option').css('display', '');
        app.setTypography($g('#headline-settings-dialog .typography-options'), app.edit.tag);
        $g('#headline-settings-dialog .headline-effect-select input[type="hidden"]').val(app.edit.desktop.animation.effect);
        value = $g('#headline-settings-dialog .headline-effect-select li[data-value="'+app.edit.desktop.animation.effect+'"]').text().trim();
        $g('#headline-settings-dialog .headline-effect-select input[type="text"]').val(value);
        value = app.getValue('animation', 'duration');
        var range = $g('#headline-settings-dialog input[data-group="animation"][data-option="duration"]').val(value).prev().val(value);
        setLinearWidth(range);
    } else {
        $g('.headline-option').hide();
        app.setTypography($g('#headline-settings-dialog .typography-options'), 'typography');
    }
    value = app.editor.document.querySelector(app.selector+' > div[class*="-wrapper"] '+app.edit.tag).textContent.trim();
    $g('#headline-settings-dialog .headline-label').val(value);
    $g('#headline-settings-dialog .select-headline-html-tag input[type="hidden"]').val(app.edit.tag);
    value = $g('#headline-settings-dialog .select-headline-html-tag li[data-value="'+app.edit.tag+'"]').text().trim();
    $g('#headline-settings-dialog .select-headline-html-tag input[readonly]').val(value);
    setPresetsList($g('#headline-settings-dialog'));
    $g('#headline-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#headline-settings-dialog').modal();
    }, 150);
}

$g('.headline-effect-select').on('customAction', function(){
    var value = app.edit.desktop.animation.effect,
        tag = app.editor.document.querySelector(app.selector+' > .headline-wrapper '+app.edit.tag),
        text = tag.textContent.trim(),
        data = '',
        duration = app.getValue('animation', 'duration'),
        delta = duration / text.length,
        delay = 0;
    app.editor.$g(app.selector+' .headline-wrapper').removeClass(value);
    value = this.querySelector('input[type="hidden"]').value;
    app.edit.desktop.animation.effect = value;
    app.editor.$g(app.selector+' .headline-wrapper').addClass(value);
    tag.style.animationDelay = '';
    if (value) {
        data += '<span>';
        for (var i = 0; i < text.length; i++) {
            data += '<span style="animation-delay: '+delay+'s">'+(text[i].trim() == '' ? '&nbsp;' : text[i])+'</span>';
            if (text[i].trim() == '') {
                data += '</span><span>';
            }
            delay += delta;
        }
        data += '</span>';
        if (value == 'type') {
            tag.style.animationDelay = duration+'s';
        }
    } else {
        data = text;
    }
    tag.innerHTML = data;
});

$g('#headline-settings-dialog .headline-label').on('input', function(){
    app.editor.document.querySelector(app.selector+' > div[class*="-wrapper"] '+app.edit.tag).textContent = this.value;
});

$g('#headline-settings-dialog .select-headline-html-tag').on('customAction', function(){
    var value = this.querySelector('input[type="hidden"]').value,
        text = $g('#headline-settings-dialog .headline-label').val().trim(),
        tag = document.createElement(value);
    tag.textContent = text;
    app.editor.$g(app.selector+' > div[class*="-wrapper"] '+app.edit.tag).replaceWith(tag);
    app.edit.tag = value;
    if (app.edit.type == 'headline') {
        app.setTypography($g('#headline-settings-dialog .typography-options'), app.edit.tag);
    }
    app.addHistory();
});

app.modules.headlineEditor = true;
app.headlineEditor();