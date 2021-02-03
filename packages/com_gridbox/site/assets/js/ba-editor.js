/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

var $g = jQuery,
    uploadMode = '',
    gridboxCallback,
    delay = '',
    app = {
        view : 'desktop',
        itemDelete : null,
        messageData : '',
        modules : {},
        loading : {},
        actionStack : new Array(),
        getErrorText: function(text){
            let div = document.createElement('div');
            div.innerHTML = text;
            if (div.querySelector('title')) {
                text = div.querySelector('title').textContent;
            }

            return text;
        },
        fetch: async function(url, data){
            let request = await fetch(url, {
                    method: 'POST',
                    body: app.getFormData(data)
                }),
                response = null;
            if (request.ok) {
                response = await request.text();
            } else {
                let utf8Decoder = new TextDecoder("utf-8"),
                    reader = request.body.getReader(),
                    textData = await reader.read(),
                    text = utf8Decoder.decode(textData.value);
                console.info(app.getErrorText(text));
            }

            return response;
        },
        getFormData: function(data){
            let formData = new FormData();
            if (data) {
                for (let ind in data) {
                    formData.append(ind, data[ind]);
                }
            }

            return formData;
        },
        checkModule : function(module){
            if (!app.modules[module] && !app.loading[module]) {
                app.loading[module] = true;
                app.loadModule(module);
            } else if (app.modules[module]) {
                app[module]();
            }
        },
        loadModule : function(module){
            if (module != 'setCalendar' && module != 'defaultElementsStyle' && module != 'gridboxLanguage' &&
                module != 'shapeDividers' && module != 'presetsPatern') {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = JUri+'components/com_gridbox/libraries/modules/'+module+'.js?'+gridboxVersion;
                document.getElementsByTagName('head')[0].appendChild(script);
                return false;
            }
            $g.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_gridbox&task=editor.loadModule&module="+module+"&"+gridboxVersion,
                data:{
                    module : module
                },
                complete: function(msg){
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    document.getElementsByTagName('head')[0].appendChild(script);
                    script.innerHTML = msg.responseText;
                }
            });
        }
    };

document.addEventListener("DOMContentLoaded", function(){
    app.checkModule('editorLoaded');
    var scrollDiv = document.querySelector('.gridbox-scroll-div'),
        scrollWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
    document.body.parentNode.style.setProperty('--scroll-width', scrollWidth+'px');
    $g.ajax({
        type : "POST",
        dataType : 'text',
        url : "index.php?option=com_gridbox&task=editor.checkSitemap"
    });
});