/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

var file = document.createElement('link'),
    css = document.getElementById('code-editor-css'),
    js = document.getElementById('code-editor-javascript'),
    customHtmlArea = document.getElementById('custom-html-edit-html'),
    cssFlag = false,
    jsFlag = false,
    htmlFlag = false,
    customCssArea = document.getElementById('custom-html-edit-css');

function checkLoading()
{
    if (cssFlag && jsFlag && htmlFlag) {
        app.modules.loadCodeMirror = true;
        if (app.actionStack['loadCodemirror']) {
            while (app.actionStack['loadCodemirror'].length > 0) {
                var func = app.actionStack['loadCodemirror'].pop();
                func();
            }
        }
    }
}
file.rel = 'stylesheet';
file.href = JUri+'media/editors/codemirror/lib/codemirror.min.css';
document.getElementsByTagName('head')[0].appendChild(file);
file = document.createElement('link');
file.rel = 'stylesheet';
file.href = JUri+'media/editors/codemirror/theme/material.css';
document.getElementsByTagName('head')[0].appendChild(file);
file = document.createElement('link');
file.rel = 'stylesheet';
file.href = JUri+'media/editors/codemirror/theme/ttcn.css';
document.getElementsByTagName('head')[0].appendChild(file);
file = document.createElement('script');
file.src = JUri+'media/editors/codemirror/lib/codemirror.min.js';
document.getElementsByTagName('head')[0].appendChild(file);
file.onload = function(){
    file = document.createElement('script');
    file.type = 'text/javascript';
    file.src = JUri+'media/editors/codemirror/addon/hint/show-hint.min.js';
    document.getElementsByTagName('head')[0].appendChild(file);
    file = document.createElement('script');
    file.type = 'text/javascript';
    file.src = JUri+'media/editors/codemirror/addon/hint/css-hint.min.js';
    document.getElementsByTagName('head')[0].appendChild(file);
    file = document.createElement('script');
    file.type = 'text/javascript';
    file.src = JUri+'media/editors/codemirror/addon/hint/javascript-hint.min.js';
    document.getElementsByTagName('head')[0].appendChild(file);
    file = document.createElement('script');
    file.type = 'text/javascript';
    file.src = JUri+'media/editors/codemirror/mode/css/css.min.js';
    document.getElementsByTagName('head')[0].appendChild(file);
    file.onload = function(){
        app.codeCss = CodeMirror.fromTextArea(css, {
            lineNumbers: true,
            theme: 'material',
            lineWrapping: true,
            tabSize: 2,
            mode: "css"
        });
        var input = app.editor.document.getElementById('code-css-value');
        app.codeCss.setValue(input.value);
        app.customCssEditor = CodeMirror.fromTextArea(customCssArea, {
            lineNumbers: true,
            theme: 'ttcn',
            lineWrapping: true,
            tabSize: 2,
            mode: "css"
        });
        cssFlag = true;
        checkLoading();
        app.customCssEditor.on('change', function(from, too) {
            clearTimeout(delay);
            delay = setTimeout(function(){
                var value = app.customCssEditor.getValue(),
                    item = app.editor.document.querySelector('#'+app.editor.app.edit+' > style');
                item.innerHTML = value;
                app.edit.css = value;
            }, 500);
        });
        app.customCssEditor.on('keydown', function(cm, event){
            event.stopPropagation();
        });
        app.codeCss.on('keydown', function(cm, event){
            event.stopPropagation();
        });
        app.codeCss.on("inputRead", function(cm, event) {
            if (!cm.state.completionActive && event.text[0] != ':' && event.text[0] != ';'
                && event.text[0] != '{' && $g.trim(event.text[0]) != '' && event.origin != 'paste') {
                CodeMirror.commands.autocomplete(cm, null, {completeSingle: false});
            }
        });
        var style = app.editor.document.querySelector('#custom-css-editor > style');
        app.codeCss.on('change', function(from, too) {
            clearTimeout(delay);
            delay = setTimeout(function(){
                input.value = app.codeCss.getValue();
                style.innerHTML = app.codeCss.getValue();
            }, 500);
        });
    }
    file = document.createElement('script');
    file.type = 'text/javascript';
    file.src = JUri+'media/editors/codemirror/mode/javascript/javascript.min.js';
    document.getElementsByTagName('head')[0].appendChild(file);
    file.onload = function(){
        app.codeJs = CodeMirror.fromTextArea(js, {
            lineNumbers: true,
            theme: 'material',
            lineWrapping: true,
            tabSize: 2,
            mode: "javascript"
        });
        var input = app.editor.document.getElementById('code-js-value');
        app.codeJs.setValue(input.value);
        jsFlag = true;
        checkLoading();
        app.codeJs.on("inputRead", function(cm, event) {
            if (!cm.state.completionActive && event.text[0] != ':' && event.text[0] != ';'
                && event.text[0] != '{' && $g.trim(event.text[0]) != '' && event.origin != 'paste') {
                CodeMirror.commands.autocomplete(cm, null, {completeSingle: false});
            }
        });
        app.codeJs.on('keydown', function(cm, event){
            event.stopPropagation();
        });
        app.codeJs.on('change', function(from, too) {
            clearTimeout(delay);
            delay = setTimeout(function(){
                input.value = app.codeJs.getValue();
            }, 500);
        });
    }
    file = document.createElement('script');
    file.type = 'text/javascript';
    file.src = JUri+'media/editors/codemirror/mode/xml/xml.min.js';
    document.getElementsByTagName('head')[0].appendChild(file);
    file.onload = function(){
        file = document.createElement('script');
        file.type = 'text/javascript';
        file.src = JUri+'media/editors/codemirror/mode/htmlmixed/htmlmixed.min.js';
        document.getElementsByTagName('head')[0].appendChild(file);
        file.onload = function(){
            app.customHtmlEditor = CodeMirror.fromTextArea(customHtmlArea, {
                lineNumbers: true,
                theme: 'ttcn',
                lineWrapping: true,
                tabSize: 2,
                mode: "htmlmixed"
            });
            htmlFlag = true;
            checkLoading();
            app.customHtmlEditor.on('change', function(from, too) {
                clearTimeout(delay);
                delay = setTimeout(function(){
                    var value = app.customHtmlEditor.getValue(),
                        item = app.editor.document.getElementById(app.editor.app.edit);
                    item = item.querySelector('div.custom-html');
                    item.innerHTML = value;
                    app.edit.html = value;
                }, 500);
            });
            app.customHtmlEditor.on('keydown', function(cm, event){
                event.stopPropagation();
            });


            var headerTextarea = document.querySelector('.header-code'),
                headerCodemirror = CodeMirror.fromTextArea(headerTextarea, {
                lineNumbers: true,
                theme: 'ttcn',
                lineWrapping: true,
                tabSize: 2,
                mode: "htmlmixed"
            });
            var bodyTextarea = document.querySelector('.body-code'),
                bodyCodemirror = CodeMirror.fromTextArea(bodyTextarea, {
                lineNumbers: true,
                theme: 'ttcn',
                lineWrapping: true,
                tabSize: 2,
                mode: "htmlmixed"
            });
            headerCodemirror.on('change', function(from, too) {
                clearTimeout(delay);
                delay = setTimeout(function(){
                    headerTextarea.value = headerCodemirror.getValue();
                }, 500);
            });
            bodyCodemirror.on('change', function(from, too) {
                clearTimeout(delay);
                delay = setTimeout(function(){
                    bodyTextarea.value = bodyCodemirror.getValue();
                }, 500);
            });
            $g('#site-options a[href="#site-scripts-options"]').one('shown', function(){
                headerCodemirror.refresh();
                bodyCodemirror.refresh();
            })
        }
    }
}