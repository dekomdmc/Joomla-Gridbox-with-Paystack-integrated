/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/
var vkFlag = false;
app.initvkcomments = function(obj){
    $g("#ba-vk-comments").empty().attr('style', '').removeClass('empty-content');
    if (obj.app_id) {
        if (!vkFlag) {
            var vkScript = document.createElement('script');
            vkScript.src = '//vk.com/js/api/openapi.js?125';
            $g(vkScript).on('load', function(){
                VK.init({
                    apiId: obj.app_id,
                    onlyWidgets: true
                });
                VK.Widgets.Comments("ba-vk-comments", obj.options);
                vkFlag = true;
            });
            document.getElementsByTagName('head')[0].appendChild(vkScript);
        } else {
            VK.Widgets.Comments("ba-vk-comments", obj.options);
        }
    } else {
        $g("#ba-vk-comments").addClass('empty-content');
    }
    initItems();
}

if (app.modules.initvkcomments) {
    app.initvkcomments(app.modules.initvkcomments.data);
}