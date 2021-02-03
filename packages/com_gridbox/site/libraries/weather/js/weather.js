/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/


!function ($) {

    var weather = function (element, options) {
        this.item = $(element);
        this.options = options;
        this.query;
    }
    
    weather.prototype = {
        init : function(){
            var $this = this;
            $.ajax({
                type:"POST",
                dataType:'text',
                data:{
                    weather: JSON.stringify($this.options),
                    view: themeData.page.view
                },
                url:"index.php?option=com_gridbox&task=editor.renderWeather",
                success: function(msg){
                    if (msg) {
                        $this.item.html(msg);
                    }
                }
            });
        }
    }
    
    $.fn.weather = function (option) {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('weather'),
                options = $.extend({}, $.fn.weather.defaults, typeof option == 'object' && option);
            if (data) {
                $this.removeData();
            }
            $this.data('weather', (data = new weather(this, options)));
            data.init();
        });
    }
    
    $.fn.weather.defaults = {
        location : 'New York, NY, United States',
        unit : 'c'
    }
    $.fn.weather.Constructor = weather;
}(window.$g ? window.$g : window.jQuery);