/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.weatherEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    setPresetsList($g('#weather-settings-dialog'));
    $g('#weather-settings-dialog .active').removeClass('active');
    $g('#weather-settings-dialog a[href="#weather-general-options"]').parent().addClass('active');
    $g('#weather-general-options').addClass('active');
    var value = '';
    if (app.edit.type == 'weather') {
        if (!('name' in app.edit.weather)) {
            app.edit.weather.name = ''
        }
        $g('.openweathermap-api-key').val(openweathermap);
        $g('.weather-location-name').val(app.edit.weather.name);
        app.setTypography($g('#weather-settings-dialog .weather-options .typography-options'), 'city');
        $g('#weather-settings-dialog .typography-select input[type="hidden"]').val('city');
        value = $g('#weather-settings-dialog .typography-select li[data-value="city"]').text().trim();
        $g('#weather-settings-dialog .typography-select input[readonly]').val(value);
        $g('.weather-unit-select input[type="hidden"]').val(app.edit.weather.unit);
        value = $g('.weather-unit-select li[data-value="'+app.edit.weather.unit+'"]').text().trim();
        $g('.weather-unit-select input[readonly]').val(value);
        value = app.getValue('view', 'forecast');
        $g('.weather-forecast-select input[type="hidden"]').val(value);
        value = $g('.weather-forecast-select li[data-value="'+value+'"]').text();
        $g('.weather-forecast-select input[readonly]').val(value);
        value = app.getValue('view', 'layout');
        $g('.weather-layout-select input[type="hidden"]').val(value);
        value = $g('.weather-layout-select li[data-value="'+value+'"]').text();
        $g('.weather-layout-select input[readonly]').val(value);
        $g('.weather-view').each(function(){
            this.checked = app.getValue(this.dataset.group, this.dataset.option);
        });
        $g('.weather-location').val(app.edit.weather.location);
        $g('.weather-options').css('display', '');
        $g('.error-message-options').hide();
    } else if (app.edit.type == 'error-message') {
        $g('.weather-options').hide();
        $g('.error-message-options').css('display', '').find('input[data-group="view"]').each(function(){
            value = app.getValue('view', this.dataset.option);
            this.checked = value;
        });
        $g('#weather-settings-dialog .error-message-options [data-subgroup="typography"]').attr('data-group', 'code');
        app.setTypography($g('#weather-settings-dialog .error-message-options .typography-options'), 'code', 'typography');
        $g('#weather-settings-dialog .404-typography-select input[type="hidden"]').val('code');
        value = $g('#weather-settings-dialog .404-typography-select li[data-value="code"]').text().trim();
        $g('#weather-settings-dialog .404-typography-select input[readonly]').val(value);
        $g('.error-message-options [data-subgroup="margin"]').each(function(){
            this.dataset.group = 'code';
            if (this.dataset.type == 'reset') {
                this.dataset.option = 'code';
            } else {
                this.value = app.getValue('code', this.dataset.option, 'margin');
            }
        });
    }
    $g('#weather-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#weather-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text().trim();
    $g('#weather-settings-dialog .section-access-select input[readonly]').val(value);
    $g('#weather-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#weather-layout-options [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#weather-layout-options [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#weather-settings-dialog');
    $g('#weather-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#weather-settings-dialog').modal();
    }, 150);
}

$g('.openweathermap-api-key').on('input', function(){
    openweathermap = this.value.trim();
    clearTimeout(this.delay);
    this.delay = setTimeout(function(){
        $g.ajax({
            type: "POST",
            dataType: 'text',
            url: "index.php?option=com_gridbox&task=editor.setOpenWeatherMapKey&tmpl=component",
            data: {
                openweathermap : openweathermap
            },
            success:function(){
                app.editor.app.initweather(app.edit, app.editor.app.edit);
            }
        });
    }, 500);
});

$g('.weather-location-name').on('input', function(){
    app.edit.weather.name = this.value.trim();
    var name = app.edit.weather.name ? app.edit.weather.name : app.edit.weather.location
    app.editor.$g(app.selector+' span.city').text(name);
    clearTimeout(this.delay);
    this.delay = setTimeout(function(){
        app.addHistory();
    }, 300);
});

$g('.404-typography-select').on('customAction', function(){
    var target = $g(this).find('input[type="hidden"]').val(),
        parent = $g(this).closest('.ba-settings-group').find('.typography-options');
    parent.find('> div').hide();
    if (target == 'links') {
        parent.find('.links').removeAttr('style');
    } else {
        parent.find('> div').not('.links').removeAttr('style')
    }
    $g('.error-message-options [data-subgroup="margin"]').each(function(){
        this.dataset.group = target;
        if (this.dataset.type == 'reset') {
            this.dataset.option = target;
        } else {
            this.value = app.getValue('code', this.dataset.option, 'margin');
        }
    });
    parent.find('[data-subgroup="typography"]').attr('data-group', target);
    app.setTypography(parent, target, 'typography');
    parent.addClass('ba-active-options');
    setTimeout(function(){
        parent.removeClass('ba-active-options');
    }, 1);
});

$g('.weather-location').on('input', function(){
    clearTimeout(delay);
    var $this = this;
    delay = setTimeout(function(){
        var value = $this.value;
        if (!value) {
            value = 'New York, NY, United States';
        }
        app.edit.weather.location = value;
        app.editor.app.initweather(app.edit, app.editor.app.edit);
        app.addHistory();
    }, 500);
});

$g('.weather-unit-select').on('customAction', function(){    
    var value = $g(this).find('input[type="hidden"]').val();
    app.edit.weather.unit = value;
    clearTimeout(delay);
    delay = setTimeout(function(){
        app.editor.app.initweather(app.edit, app.editor.app.edit);
        app.addHistory();
    }, 500);
});

app.modules.weatherEditor = true;
app.weatherEditor();