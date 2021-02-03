/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.cartEditor = function(){
    let modal = $g('#cart-settings-dialog');
    app.selector = '#'+app.editor.app.edit;
    modal.find('.active').removeClass('active');
    modal.find('a[href="#cart-general-options"]').parent().addClass('active');
    modal.find('#cart-general-options').addClass('active');
    setPresetsList(modal);
    modal.find('.ba-wishlist-options, .ba-cart-options').hide();
    if (app.edit.type == 'cart') {
        modal.find('.ba-cart-options').css('display', '');
        value = app.getValue('view', 'subtotal');
        modal.find('[data-group="view"][data-option="subtotal"]').prop('checked', value);
        modal.find('.select-cart-layout input[type="hidden"]').val(app.edit.layout);
        modal.find('.select-cart-layout input[type="text"]').val(gridboxLanguage[app.edit.layout.toUpperCase()]);
    } else {
        modal.find('.ba-wishlist-options').css('display', '');
        modal.find('.ba-wishlist-title').val(app.edit.title);
    }



    value = app.getValue('padding', 'top');
    modal.find('[data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    modal.find('[data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    modal.find('[data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    modal.find('[data-group="padding"][data-option="left"]').val(value);
    updateInput(modal.find('[data-option="color"][data-group="hover"]'), app.edit.hover.color);
    value = app.edit.hover['background-color'];
    updateInput(modal.find('[data-option="background-color"][data-group="hover"]'), value);
    value = app.getValue('normal', 'color');
    updateInput(modal.find('[data-option="color"][data-group="normal"]'), value);
    value = app.getValue('normal', 'background-color');
    updateInput(modal.find('[data-option="background-color"][data-group="normal"]'), value);
    value = app.getValue('shadow', 'value');
    value = modal.find('input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('shadow', 'color');
    updateInput(modal.find('input[data-option="color"][data-group="shadow"]'), value);
    value = app.getValue('icons', 'size');
    modal.find('[data-option="size"][data-group="icons"]').val(value);
    var range = modal.find('[data-option="size"][data-group="icons"]').prev();
    range.val(value);
    setLinearWidth(range);
    value = app.edit.icon.icon.replace(/zmdi zmdi-|fa fa-|flaticon-/, '');
    modal.find('[data-option="icon"][data-group="icon"]').val(value);
    modal.find('.button-icon-position input[type="hidden"]').val(app.edit.icon.position);
    value = modal.find('.button-icon-position li[data-value="'+app.edit.icon.position+'"]').text();
    modal.find('.button-icon-position input[readonly]').val(value.trim());
    app.setTypography(modal.find('.typography-options'), 'typography');
    modal.find('.section-access-select input[type="hidden"]').val(app.edit.access);
    value = modal.find('.section-access-select li[data-value="'+app.edit.access+'"]').text();
    modal.find('.section-access-select input[readonly]').val($g.trim(value));
    modal.find('.class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    modal.find('[data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    modal.find('[data-group="margin"][data-option="bottom"]').val(value);
    for (var key in app.edit.desktop.border) {
        var input = modal.find('input[data-option="'+key+'"][data-group="border"]');
        value = app.getValue('border', key);
        switch (key) {
            case 'color' :
                updateInput(input, value);
                break;
            case 'style' :
                input.val(value);
                var select = input.closest('.ba-custom-select');
                value = select.find('li[data-value="'+value+'"]').text();
                select.find('input[readonly]').val($g.trim(value));
                break;
            default:
                input.val(value);
                var range = input.prev();
                range.val(value);
                setLinearWidth(range);
        }
    }
    setDisableState('#cart-settings-dialog');
    modal.attr('data-edit', app.edit.type);
    setTimeout(function(){
        modal.modal();
    }, 150);
}

$g('#cart-settings-dialog input[data-option="icon"][data-group="icon"]').on('click', function(){
    uploadMode = 'addCartIcon';
    checkIframe($g('#icon-upload-dialog'), 'icons');
    fontBtn = this;
}).on('change', function(){
    app.edit.icon.icon = this.dataset.value;
    app.editor.$g(app.selector+' .ba-button-wrapper a').each(function(){
        let i = this.querySelector('i');
        if (app.edit.icon.icon && !i) {
            i = document.createElement('i');
            this.append(i);
        }
        if (app.edit.icon.icon) {
            i.className = app.edit.icon.icon;
        } else if (i) {
            i.remove()
        }
    });
    app.addHistory();
});

$g('.ba-wishlist-title').on('input', function(){
    app.edit.title = this.value;
    app.editor.$g(app.selector+' .ba-wishlist-title').text(this.value);
    clearTimeout(this.delay);
    this.delay = setTimeout(function(){
        app.addHistory();
    }, 500);
})

app.modules.cartEditor = true;
app.cartEditor();