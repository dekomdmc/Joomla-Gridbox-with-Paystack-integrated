/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

if (!$g.fn.countdown) {
    var file = document.createElement('script');
    file.onload = function(){
        if (app.modules.initcountdown) {
            app.initcountdown(app.modules.initcountdown.data, app.modules.initcountdown.selector);
        }
    }
    file.src = JUri+'components/com_gridbox/libraries/countdown/countdown.js';
    document.getElementsByTagName('head')[0].appendChild(file);
} else if (app.modules.initcountdown) {
    app.initcountdown(app.modules.initcountdown.data, app.modules.initcountdown.selector);
}

app.initcountdown = function(obj, key){
    var now = new Date(),
        timezone = now.getTimezoneOffset(),
        minutes = String(Math.abs(timezone) % 60),
        hours = String(Math.floor(Math.abs(timezone) / 60)),
        str = timezone < 0 ? '+' : '-';
    if (hours.length == 1) {
        hours = '0'+hours;
    }
    if (minutes.length == 1) {
        minutes = '0'+minutes;
    }
    str += hours+':'+minutes;
    $g('#'+key).countdown({
        end : new Date(obj.date.replace(/ /g, 'T')+str),
        mode : obj.display,
        callback : function(){
            if (obj['hide-after']) {
                $g('#'+key).find('.ba-countdown').hide();
            }
        }
    });
    initItems();
}