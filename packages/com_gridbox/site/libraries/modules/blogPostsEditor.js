/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.blogPostsEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#blog-posts-settings-dialog .active').removeClass('active');
    $g('#blog-posts-settings-dialog a[href="#blog-posts-general-options"]').parent().addClass('active');
    $g('#blog-posts-general-options').addClass('active');
    $g('#blog-posts-settings-dialog').attr('data-edit', app.edit.type);
    $g('#blog-posts-settings-dialog .class-suffix').val(app.edit.suffix);
    var value = app.getValue('margin', 'top');
    $g('#blog-posts-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#blog-posts-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    if (!app.edit.desktop.image.size) {
        app.edit.desktop.image.size = 'cover';
    }
    if (!app.edit.desktop.padding) {
        app.edit.desktop.padding = {
            "bottom":0,
            "left": 0,
            "right": 0,
            "top":0
        }
    }
    if (!app.edit.desktop.image.border) {
        app.edit.desktop.image.border = {
            "color":"@border",
            "radius":0,
            "style":"solid",
            "width":"0"
        }
    }
    $g('#blog-posts-settings-dialog input[data-group="padding"]').each(function(){
        value = app.getValue('padding', this.dataset.option);
        this.value = value;
    });
    if (!app.edit.info) {
        app.edit.info = new Array('author', 'date', 'category', 'hits', 'comments');
    }
    value = app.getValue('view', 'gutter');
    $g('#blog-posts-settings-dialog [data-option="gutter"]')[0].checked = value;
    setDisableState('#blog-posts-settings-dialog');
    $g('#blog-posts-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#blog-posts-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#blog-posts-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    value = app.getValue('border', 'radius');
    value = $g('#blog-posts-settings-dialog input[data-option="radius"][data-group="border"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'width');
    value = $g('#blog-posts-settings-dialog input[data-option="width"][data-group="border"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'color');
    updateInput($g('#blog-posts-settings-dialog input[data-option="color"][data-group="border"]'), value);
    value = app.getValue('border', 'style');
    $g('#blog-posts-layout-options .border-style-select input[type="hidden"]').val(value);
    value = $g('#blog-posts-layout-options .border-style-select li[data-value="'+value+'"]').text();
    $g('#blog-posts-layout-options .border-style-select input[readonly]').val($g.trim(value));
    $g('#blog-posts-settings-dialog .blog-posts-layout-select input[type="hidden"]').val(app.edit.layout.layout);
    value = $g('#blog-posts-settings-dialog .blog-posts-layout-select li[data-value="'+app.edit.layout.layout+'"]').text();
    $g('#blog-posts-settings-dialog .blog-posts-layout-select input[readonly]').val($g.trim(value));
    $g('#blog-posts-settings-dialog .ba-style-custom-select input[type="hidden"]').val('image');
    $g('#blog-posts-settings-dialog .ba-style-custom-select input[readonly]').val(gridboxLanguage['IMAGE']);
    value = app.getValue('background', 'color');
    updateInput($g('#blog-posts-settings-dialog .blog-posts-background-options input[data-option="color"]'), value);
    value = app.getValue('shadow', 'color');
    updateInput($g('#blog-posts-settings-dialog .blog-posts-shadow-options input[data-option="color"]'), value);
    value = app.getValue('shadow', 'value');
    var input = $g('#blog-posts-settings-dialog .blog-posts-shadow-options input[data-option="value"]'),
        range = input.prev();
    input.val(value);
    range.val(value);
    setLinearWidth(range);
    value = app.getValue('view', 'count');
    $g('#blog-posts-settings-dialog input[data-option="count"]').val(value);
    $g('#blog-posts-settings-dialog input[data-option="maximum"]').val(app.edit.maximum);
    $g('#blog-posts-settings-dialog input[data-option="limit"]').val(app.edit.limit);
    if (!app.edit.order) {
        app.edit.order = 'created';
    }
    $g('.blog-posts-sort-select input[type="hidden"]').val(app.edit.order);
    value = $g('.blog-posts-sort-select li[data-value="'+app.edit.order+'"]').text().trim();
    $g('.blog-posts-sort-select input[type="text"]').val(value);
    if (typeof(app.edit.desktop.overlay) == 'string') {
        app.edit.desktop.overlay = {
            color: app.edit.desktop.overlay
        }
    }
    if (!app.edit.desktop.overlay.gradient) {
        app.edit.desktop.overlay.type = 'color';
        app.edit.desktop.overlay.gradient = {
            "effect": "linear",
            "angle": 45,
            "color1": "@bg-dark",
            "position1": 25,
            "color2": "@bg-dark-accent",
            "position2": 75
        }
    }
    value = app.getValue('overlay', 'effect', 'gradient');
    $g('#blog-posts-settings-dialog .overlay-linear-gradient').hide();
    $g('#blog-posts-settings-dialog .overlay-'+value+'-gradient').css('display', '');
    $g('#blog-posts-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="hidden"]').val(value);
    value = $g('#blog-posts-settings-dialog .overlay-gradient-options .gradient-effect-select li[data-value="'+value+'"]').text().trim();
    $g('#blog-posts-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="text"]').val(value);
    value = app.getValue('overlay', 'type');
    $g('#blog-posts-settings-dialog .overlay-color-options, .overlay-gradient-options').hide();
    $g('#blog-posts-settings-dialog .overlay-'+value+'-options').css('display', '');
    $g('#blog-posts-settings-dialog .background-overlay-select input[type="hidden"]').val(value);
    value = $g('#blog-posts-settings-dialog .background-overlay-select li[data-value="'+value+'"]').text().trim();
    $g('#blog-posts-settings-dialog .background-overlay-select input[type="text"]').val(value);
    $g('#blog-posts-settings-dialog input[data-subgroup="gradient"][data-group="overlay"]').each(function(){
        value = app.getValue('overlay', this.dataset.option, 'gradient');
        if (this.type == 'number') {
            var range = $g(this).val(value).prev().val(value);
            setLinearWidth(range);
        } else {
            updateInput($g(this), value);
        }
    });
    if (!app.edit.tag) {
        app.edit.tag = 'h3';
    }
    value = app.getValue('overlay', 'color');
    updateInput($g('#blog-posts-settings-dialog input[data-group="overlay"][data-option="color"]'), value);
    $g('#blog-posts-settings-dialog .select-title-html-tag input[type="hidden"]').val(app.edit.tag);
    $g('#blog-posts-settings-dialog .select-title-html-tag input[readonly]').val(app.edit.tag.toUpperCase());
    showBaStyleDesign('image', document.querySelector('#blog-posts-settings-dialog .ba-style-custom-select'));
    $g('.blog-posts-cover-options').hide();
    $g('.blog-posts-background-options').css('display', '');
    $g('#blog-posts-design-options .ba-style-image-options').first().find('.ba-settings-item').first().css('display', '');
    if (app.edit.layout.layout == 'ba-classic-layout' || app.edit.layout.layout == 'ba-one-column-grid-layout') {
        $g('.blog-posts-grid-options').hide();
        $g('.blog-posts-grid-options input[data-option="count"]').closest('.ba-settings-group');
    } else {
        $g('.blog-posts-grid-options').css('display', '');
        $g('.blog-posts-grid-options input[data-option="count"]').closest('.ba-settings-group');
        if (app.edit.layout.layout == 'ba-cover-layout') {
            $g('#blog-posts-design-options .ba-style-image-options').first().find('.ba-settings-item').first().hide();
            $g('.blog-posts-cover-options').css('display', '');
            $g('.blog-posts-background-options').hide();
        }
    }
    $g('.blog-posts-view-options input[type="checkbox"]').each(function(){
        var option = this.dataset.option,
            group = this.dataset.group;
        if (group) {
            value = app.getValue(group, option);
        } else {
            value = app.getValue(option);
        }
        this.checked = value;
    });
    if (!app.edit.desktop.postFields) {
        app.edit.desktop.postFields = {
            "margin":{
                "bottom":"25",
                "top":"0"
            },
            "typography":{
                "color":"@text",
                "font-family":"@default",
                "font-size":"16",
                "font-style":"normal",
                "font-weight":"400",
                "letter-spacing":"0",
                "line-height":"26",
                "text-decoration":"none",
                "text-align":"left",
                "text-transform":"uppercase"
            }
        }
    }
    setTimeout(function(){
        $g('#blog-posts-settings-dialog').modal();
    }, 150);
}

$g('#blog-posts-settings-dialog').find('input[data-option="maximum"], input[data-option="limit"]').on('input', function(){
    clearTimeout(delay);
    var $this = this,
        value = 0;
    delay = setTimeout(function(){
        if ($this.value) {
            value = $this.value
        }
        app.edit[$this.dataset.option] = value;
        getBlogPosts(app.edit.maximum, app.edit.limit, app.editor.themeData.id, app.edit.order);
    }, 500);
});

$g('.blog-posts-sort-select').on('customAction', function(){
    app.edit.order = this.querySelector('input[type="hidden"]').value;
    getBlogPosts(app.edit.maximum, app.edit.limit, app.editor.themeData.id, app.edit.order);
});

function getBlogPosts(max, limit, id, order)
{
    var posts = false,
        pag = false;
    $g.ajax({
        type:"POST",
        dataType:'text',
        url:"index.php?option=com_gridbox&task=editor.getBlogPosts",
        data:{
            max : max,
            limit : limit,
            order: order,
            id : id
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .ba-blog-posts-wrapper').innerHTML = msg.responseText;
            app.editor.app.buttonsPrevent();
            posts = true;
            app.sectionRules();
            app.editor.app.initMasonryBlog(app.edit, app.editor.app.edit);
            if (posts && pag) {
                app.addHistory();
            }
        }
    });
    $g.ajax({
        type:"POST",
        dataType:'text',
        url:"index.php?option=com_gridbox&task=editor.getBlogPagination",
        data:{
            max : max,
            limit : limit,
            id : id
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .ba-blog-posts-pagination-wrapper').innerHTML = msg.responseText;
            app.editor.app.buttonsPrevent();
            pag = true;
            if (posts && pag) {
                app.addHistory();
            }
        }
    });
}

app.modules.blogPostsEditor = true;
app.blogPostsEditor();