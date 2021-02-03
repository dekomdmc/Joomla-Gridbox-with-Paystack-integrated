/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.starRatingsEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#star-ratings-settings-dialog .active').removeClass('active');
    $g('#star-ratings-settings-dialog a[href="#star-ratings-general-options"]').parent().addClass('active');
    $g('#star-ratings-general-options').addClass('active');
    $g('#star-ratings-settings-dialog').attr('data-edit', app.edit.type);
    setPresetsList($g('#star-ratings-settings-dialog'));
    $g('#star-ratings-settings-dialog .class-suffix').val(app.edit.suffix);
    var value = app.getValue('margin', 'top');
    $g('#star-ratings-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#star-ratings-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#star-ratings-settings-dialog');
    value = app.getValue('view', 'rating');
    $g('#star-ratings-settings-dialog input[data-option="rating"][data-group="view"]').prop('checked', value);
    value = app.getValue('view', 'votes');
    $g('#star-ratings-settings-dialog input[data-option="votes"][data-group="view"]').prop('checked', value);
    $g('#star-ratings-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#star-ratings-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#star-ratings-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#star-ratings-settings-dialog .star-ratings-design-group input[type="hidden"]').val('icon');
    value = $g('#star-ratings-settings-dialog .star-ratings-design-group li[data-value="icon"]').text();
    $g('#star-ratings-settings-dialog .star-ratings-design-group input[readonly]').val($g.trim(value));
    showstarRatingsDesign('icon');
    setTimeout(function(){
        $g('#star-ratings-settings-dialog').modal();
    }, 150);
}

$g('#star-ratings-settings-dialog .star-ratings-design-group .ba-custom-select').on('customAction', function(){
    var value = $g(this).find('input[type="hidden"]').val();
    showstarRatingsDesign(value);
});

function showstarRatingsDesign(search)
{
    var parent = $g('#star-ratings-design-options');
    parent.children().not('.star-ratings-design-group').hide();
    parent.find('.last-element-child').removeClass('last-element-child')
    switch (search) {
        case 'info' :
            parent.find('.star-ratings-typography-options').show()
                .find('[data-subgroup="typography"]').attr('data-group', search);
            parent.find('.star-ratings-typography-options .typography-options').addClass('ba-active-options');
            setTimeout(function(){
                parent.find('.star-ratings-typography-options .typography-options').removeClass('ba-active-options');
            }, 1);
            app.setTypography(parent.find('.star-ratings-typography-options .typography-options'), search);
            break;
        case 'icon' :
            parent.find('.star-ratings-icon-options').show().last().addClass('last-element-child');
            app.setTypography(parent.find('.star-ratings-icon-options'), search);
            break;
    }
}

app.modules.starRatingsEditor = true;
app.starRatingsEditor();