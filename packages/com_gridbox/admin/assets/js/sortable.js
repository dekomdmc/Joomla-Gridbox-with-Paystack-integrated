/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

!function ($) {
    var dragEl,
        sortGroups = {},
        cloneEl,
        placeEl,
        sortable = function (element, options) {
            this.delete = function(){
                var item = $(element);
                item.off('mousedown.sortable')
            };
            this.init = function(){
                var item = $(element),
                    div = document.createElement('div');
                if (!sortGroups[options.group]) {
                    sortGroups[options.group] = new Array();
                }
                sortGroups[options.group].unshift(item);
                item.on('mousedown.sortable', options.handle, function(event){
                    if (event.button != 0  || (options.group == 'pages' && !event.target.classList.contains('sortable-handle') 
                        && (event.target.localName == 'a' || event.target.localName == 'i'))) {
                        return false;
                    }
                    $(item).closest('.ba-wrapper').addClass('sortable-parent-node');
                    options.start(item[0]);
                    dragEl = $(this).closest(element.children)[0];
                    cloneEl = dragEl.cloneNode(true);
                    $(cloneEl).find('.ba-edit-item').parent().find('> *').not('.ba-edit-item').remove();
                    placeEl = cloneEl.cloneNode(true);
                    placeEl.classList.add('sortable-placeholder');
                    cloneEl.classList.add('sortable-helper');
                    element.insertBefore(cloneEl, dragEl);
                    element.insertBefore(placeEl, cloneEl);
                    $(cloneEl).css({
                        'width' : $(dragEl).width()+'px',
                        'position' : 'fixed',
                        'top' : event.clientY+'px',
                        'left' : event.clientX+'px',
                        'margin-left' : 0,
                        'transition' : 'none'
                    }).on('mouseover', function(event){
                        event.stopPropagation();
                    })
                    $(dragEl).find('.edit-settings').trigger('mouseleave');
                    div.appendChild(dragEl)
                    item.removeClass('active-item');
                    $(document).on('mousemove.sortable', function(event){
                        $(cloneEl).css({
                            'top' : event.clientY+'px',
                            'left' : event.clientX+'px',
                        });
                        var target = null,
                            array = sortGroups[options.group];
                        for (var i = 0; i < array.length; i++) {
                            array[i].find(options.selector).not(placeEl).not(cloneEl).each(function(){
                                var rect = this.getBoundingClientRect();
                                if (rect.top < event.clientY && rect.bottom > event.clientY &&
                                    rect.left < event.clientX && event.clientX < rect.right) {
                                    target = this;
                                    return false;
                                }
                            });
                            if (target) {
                                var rect = target.getBoundingClientRect(),
                                    next = (event.clientY - rect.top) / (rect.bottom - rect.top) > .5,
                                    after = next && target.nextSibling || target;
                                if (next && !target.nextSibling) {
                                    after.parentNode.appendChild(placeEl);
                                } else {
                                    after.parentNode.insertBefore(placeEl, after);
                                }
                            } else {
                                var rect = array[i][0].getBoundingClientRect(),
                                    length = $(array[i][0]).find(options.selector).not(placeEl).not(cloneEl).length;
                                if (rect.top < event.clientY && rect.bottom > event.clientY &&
                                    rect.left < event.clientX && event.clientX < rect.right && length == 0) {
                                    target = array[i][0];
                                }
                                if (target && !target.classList.contains('ba-grid-column')) {
                                    target.appendChild(placeEl);
                                } else if (target) {
                                    $(target).find('> .empty-item').before(placeEl);
                                }
                            }
                            if (target) {
                                break;
                            }
                        }
                        return false;
                    }).off('mouseup.sortable').on('mouseup.sortable', function(){
                        var classList = cloneEl.classList;
                        cloneEl.parentNode.removeChild(cloneEl);
                        placeEl.parentNode.insertBefore(dragEl, placeEl);
                        placeEl.parentNode.removeChild(placeEl);
                        $(document).off('mousemove.sortable mouseup.sortable');
                        $('.sortable-parent-node').removeClass('sortable-parent-node');
                        options.change(dragEl);
                    });
                    return false;
                });
            }
        }

    $.fn.sortable = function(option) {
        return this.each(function() {
            var $this = $(this),
                data = $this.data('sortable'),
                options = $.extend({}, $.fn.sortable.defaults, typeof option == 'object' && option);
            if (data) {
                data.delete();
                $this.removeData();
            }
            $this.data('sortable', (data = new sortable(this, options)));
            data.init();
        });
    }

    $.fn.sortable.defaults = {
        'selector' : '> *',
        change : function(){
            
        },
        start : function(){

        }
    }
    
    $.fn.sortable.Constructor = sortable;
}(window.$g ? window.$g : window.jQuery);