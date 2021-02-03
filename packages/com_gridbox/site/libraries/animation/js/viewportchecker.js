/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

(function($){
    $.fn.viewportChecker = function(options){
        var $this = this,
            flag = true;
        this.checkElements = function(){
            var top = window.pageYOffset,
                bottom = top + window.innerHeight,
                elemTop = Math.round($this.offset().top) + 100,
                elemBottom = elemTop + $this[0].offsetHeight;
            if (flag) {
                if (elemTop < bottom && elemBottom > top) {
                    $this.addClass('visible animated '+options.classToAdd);
                    flag = false;
                    setTimeout(function(){
                        $this.removeClass(options.classToAdd);
                    }, 4000);
                }
            }
        };
        $(window).on("load scroll touchmove", this.checkElements);
        
        return this;
    };
})(window.$g ? window.$g : window.jQuery);