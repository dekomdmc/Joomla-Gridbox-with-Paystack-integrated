/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

var $g = jQuery,
    delay = '',
    itemsInit = new Array(),
    app = {
        view : 'desktop',
        modules : {},
        loading : {},
        edit : null,
        cache: {},
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
        query: function(selector){
            if (!this.cache[selector]) { 
                this.cache[selector] = document.querySelector(selector);
            }
            
            return this.cache[selector];
        },
        getObject: function(key){
            var object = $g.extend(true, {}, app.items[key].desktop);
            if (app.view != 'desktop') {
                for (var ind in breakpoints) {
                    if (!app.items[key][ind]) {
                        app.items[key][ind] = {};
                    }
                    object = $g.extend(true, {}, object, app.items[key][ind]);
                    if (ind == app.view) {
                        break;
                    }
                }
            }

            return object;
        },
        checkModule : function(module, obj){
            if (typeof(obj) != 'undefined') {
                app.modules[module] = obj;
            }
            if (typeof(app[module]) == 'undefined' && !app.loading[module]) {
                app.loading[module] = true;
                app.loadModule(module);
            } else if (typeof(app[module]) != 'undefined') {
                if (typeof(obj) != 'undefined') {
                    app[module](obj.data, obj.selector);
                } else {
                    app[module]();
                }
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
    app.checkModule('gridboxEditorLoaded');
    if ('setPostMasonryHeight' in window) {
        $g('.ba-blog-posts-wrapper.ba-masonry-layout').each(function(){
            var key = $g(this).closest('.ba-item').attr('id');
            setPostMasonryHeight(key);
        });
    }
    if ('setGalleryMasonryHeight' in window) {
        $g('.instagram-wrapper.simple-gallery-masonry-layout').each(function(){
            setGalleryMasonryHeight(this.closest('.ba-item').id);
        });
    }
});