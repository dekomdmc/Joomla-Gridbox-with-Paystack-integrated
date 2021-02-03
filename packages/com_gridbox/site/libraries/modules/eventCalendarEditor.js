/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.eventCalendarEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#event-calendar-settings-dialog .active').removeClass('active');
    $g('#event-calendar-settings-dialog a[href="#event-calendar-general-options"]').parent().addClass('active');
    $g('#event-calendar-general-options').addClass('active');
    setPresetsList($g('#event-calendar-settings-dialog'));
    $g('#event-calendar-settings-dialog .event-calendar-app-select input[type="hidden"]').val(app.edit.app);
    value = $g('#event-calendar-settings-dialog .event-calendar-app-select li[data-value="'+app.edit.app+'"]').text().trim();
    $g('#event-calendar-settings-dialog .event-calendar-app-select input[readonly]').val(value);
    if (!app.edit.start) {
        app.edit.start = 0;
    }
    if (!app.edit.fields) {
        app.edit.layout = "list";
        app.edit.fields = [];
        app.edit.info = ["author", "date", "category", "comments"];
        app.edit.desktop.view = {
            "author": false,
            "button": false,
            "category": true,
            "comments": false,
            "date": true,
            "image": true,
            "reviews": false,
            "title": true
        };
        app.edit.desktop.fields = {};
    }
    $g('#event-calendar-settings-dialog input[data-group="view"][type="checkbox"]').each(function(){
        if (this.dataset.option in app.edit.desktop.view) {
            value = app.getValue('view', this.dataset.option);
            this.checked = value;
        }
    });
    $g('#event-calendar-settings-dialog .event-calendar-first-day-select input[type="hidden"]').val(app.edit.start);
    value = $g('#event-calendar-settings-dialog .event-calendar-first-day-select li[data-value="'+app.edit.start+'"]').text().trim();
    $g('#event-calendar-settings-dialog .event-calendar-first-day-select input[readonly]').val(value);
    $g('#event-calendar-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#event-calendar-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#event-calendar-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#event-calendar-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#event-calendar-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#event-calendar-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    $g('#event-calendar-settings-dialog .ba-style-custom-select input[type="hidden"]').val('months');
    $g('#event-calendar-settings-dialog .ba-style-custom-select input[readonly]').val(gridboxLanguage['MONTHS']);
    showBaStyleDesign('months', document.querySelector('#event-calendar-settings-dialog .ba-style-custom-select'));
    setDisableState('#event-calendar-settings-dialog');
    $g('#event-calendar-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#event-calendar-settings-dialog').modal();
    }, 150);
}

$g('#event-calendar-settings-dialog .event-calendar-app-select').on('customAction', function(){
    app.edit.app = this.querySelector('input[type="hidden"]').value;
    app.editor.app.renderEventCalendar(app.editor.app.edit);
});

$g('#event-calendar-settings-dialog .event-calendar-first-day-select').on('customAction', function(){
    app.edit.start = this.querySelector('input[type="hidden"]').value;
    app.editor.app.renderEventCalendar(app.editor.app.edit);
});

app.modules.eventCalendarEditor = true;
app.eventCalendarEditor();