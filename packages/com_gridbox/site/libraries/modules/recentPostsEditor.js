/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.recentPostsEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#recent-posts-settings-dialog .active').removeClass('active');
    $g('#recent-posts-settings-dialog a[href="#recent-posts-general-options"]').parent().addClass('active');
    $g('#recent-posts-general-options').addClass('active');
    $g('#recent-posts-settings-dialog').attr('data-edit', app.edit.type);
    if (app.edit.type == 'store-search-result') {
        $g('#recent-posts-settings-dialog').attr('data-edit', 'search-result');
    }
    checkAppFields($g('#recent-posts-settings-dialog'));
    if (app.edit.type != 'search-result' && app.edit.type != 'store-search-result') {
        setPresetsList($g('#recent-posts-settings-dialog'));
        $g('#recent-posts-general-options .preset-options').css('display', '');
    } else {
        $g('#recent-posts-general-options .preset-options').hide();
    }
    if (app.edit.type != 'search-result' && app.edit.type != 'store-search-result') {
        $g('#recent-posts-settings-dialog li[data-value="ba-one-column-grid-layout"]').hide();
    } else {
        $g('#recent-posts-settings-dialog li[data-value="ba-one-column-grid-layout"]').css('display', '');
    }
    if (app.edit.type != 'author') {
        if (!app.edit.desktop.image.size) {
            app.edit.desktop.image.size = 'cover';
        }
        if (!app.edit.desktop.store) {
            app.edit.desktop.store = {}
        }
        $g('#recent-posts-settings-dialog .recent-posts-app-select input[type="hidden"]').val(app.edit.app);
        var value = $g('#recent-posts-settings-dialog .recent-posts-app-select li[data-value="'+app.edit.app+'"]').text();
        $g('#recent-posts-settings-dialog .recent-posts-app-select input[readonly]').val(value.trim());
        $g('#recent-posts-settings-dialog .recent-posts-display-select input[type="hidden"]').val(app.edit.sorting);
        value = $g('#recent-posts-settings-dialog .recent-posts-display-select li[data-value="'+app.edit.sorting+'"]').text();
        $g('#recent-posts-settings-dialog .recent-posts-display-select input[readonly]').val(value.trim());
        $g('#recent-posts-settings-dialog input[data-option="limit"]').val(app.edit.limit);
        $g('#recent-posts-settings-dialog input[data-option="maximum"]').val(app.edit.maximum);
        $g('#recent-posts-settings-dialog').find('.not-author-options').css('display', '');
        $g('#recent-posts-settings-dialog').find('.author-options').hide();
        if (!app.edit.info) {
            app.edit.info = new Array('author', 'date', 'category', 'comments');
        }
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
    } else {
        $g('#recent-posts-settings-dialog').find('.not-author-options').hide();
        $g('#recent-posts-settings-dialog').find('.author-options').css('display', '');
    }
    if (!app.edit.desktop.padding) {
        app.edit.desktop.padding = {
            "bottom":0,
            "left": 0,
            "right": 0,
            "top":0
        }
    }
    $g('#recent-posts-settings-dialog input[data-group="padding"]').each(function(){
        value = app.getValue('padding', this.dataset.option);
        this.value = value;
    });
    $g('#recent-posts-settings-dialog .blog-posts-layout-select input[type="hidden"]').val(app.edit.layout.layout);
    value = $g('#recent-posts-settings-dialog .blog-posts-layout-select li[data-value="'+app.edit.layout.layout+'"]').text();
    $g('#recent-posts-settings-dialog .blog-posts-layout-select input[readonly]').val(value.trim());
    $g('.blog-posts-cover-options').hide();
    if (app.edit.layout.layout == 'ba-classic-layout' || app.edit.layout.layout == 'ba-one-column-grid-layout') {
        $g('.blog-posts-grid-options').hide();
    } else {
        $g('.blog-posts-grid-options').css('display', '');
    }
    if (app.edit.layout.layout == 'ba-cover-layout') {
        $g('#recent-posts-design-options .ba-style-image-options').first().find('.ba-settings-item').first().hide();
        $g('.blog-posts-cover-options').css('display', '');
        $g('.blog-posts-background-options').hide();
    }
    value = app.getValue('view', 'count');
    $g('#recent-posts-settings-dialog input[data-option="count"]').val(value);
    value = app.getValue('background', 'color');
    updateInput($g('#recent-posts-settings-dialog .blog-posts-background-options input[data-option="color"]'), value);
    value = app.getValue('shadow', 'color');
    updateInput($g('#recent-posts-settings-dialog .blog-posts-shadow-options input[data-option="color"]'), value);
    value = app.getValue('shadow', 'value');
    var input = $g('#recent-posts-settings-dialog .blog-posts-shadow-options input[data-option="value"]'),
        range = input.prev();
    input.val(value);
    range.val(value);
    setLinearWidth(range);
    value = app.getValue('border', 'radius');
    value = $g('#recent-posts-settings-dialog input[data-option="radius"][data-group="border"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'width');
    value = $g('#recent-posts-settings-dialog input[data-option="width"][data-group="border"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'color');
    updateInput($g('#recent-posts-settings-dialog input[data-option="color"][data-group="border"]'), value);
    value = app.getValue('border', 'style');
    $g('#recent-posts-layout-options .border-style-select input[type="hidden"]').val(value);
    value = $g('#recent-posts-layout-options .border-style-select li[data-value="'+value+'"]').text();
    $g('#recent-posts-layout-options .border-style-select input[readonly]').val($g.trim(value));
    if (app.edit.type == 'related-posts') {
        $g('#recent-posts-settings-dialog .related-posts-display-select input[type="hidden"]').val(app.edit.related);
        value = $g('#recent-posts-settings-dialog .related-posts-display-select li[data-value="'+app.edit.related+'"]').text();
        $g('#recent-posts-settings-dialog .related-posts-display-select input[readonly]').val($g.trim(value));
        app.recentPostsCallback = 'getRelatedPosts';
    } else if (app.edit.type == 'recent-posts') {
        checkRecentPostsAppType(app.edit.app, $g('#recent-posts-settings-dialog'));
        if (!app.edit.categories) {
            app.edit.categories = {};
        }
        if (!('featured' in app.edit)) {
            app.edit.featured = false;
        }
        if (!app.edit.desktop.pagination) {
            app.edit.layout.pagination = '';
            app.edit.desktop.pagination = {
                "typography":{
                    "font-family":"@default",
                    "font-size":10,
                    "font-style":"normal",
                    "font-weight":"700",
                    "letter-spacing":4,
                    "line-height":26,
                    "text-align":"center",
                    "text-decoration":"none",
                    "text-transform":"uppercase"
                },
                "margin":{
                    "bottom":"25",
                    "top":"25"
                },
                "padding":{
                    "bottom":"20",
                    "left":"80",
                    "right":"80",
                    "top":"20"
                },
                "border":{
                    "color":"@border",
                    "radius":"50",
                    "style":"solid",
                    "width":"0"
                },
                "normal":{
                    "color":"@title-inverse",
                    "background":"@primary"
                },
                "hover":{
                    "color":"@title-inverse",
                    "background":"@hover"
                },
                "shadow":{
                    "value":"0",
                    "color":"@shadow"
                }
            }
        }
        $g('#recent-posts-settings-dialog .recent-posts-pagination-select input[type="hidden"]').val(app.edit.layout.pagination);
        value = $g('#recent-posts-settings-dialog .recent-posts-pagination-select li[data-value="'+app.edit.layout.pagination+'"]').text();
        $g('#recent-posts-settings-dialog .recent-posts-pagination-select input[readonly]').val(value.trim());
        $g('#recent-posts-settings-dialog input[data-option="featured"]').prop('checked', app.edit.featured);
        $g('.selected-categories li:not(.search-category)').remove();
        $g('.all-categories-list .selected-category').removeClass('selected-category');
        for (var key in app.edit.categories) {
            var str = getCategoryHtml(key, app.edit.categories[key].title);
            $g('#recent-posts-settings-dialog .selected-categories li.search-category').before(str);
            $g('#recent-posts-settings-dialog .all-categories-list [data-id="'+key+'"]').addClass('selected-category');
        }
        if ($g('.selected-categories li:not(.search-category)').length > 0) {
            $g('.ba-settings-item.tags-categories-list').addClass('not-empty-list');
        } else {
            $g('.ba-settings-item.tags-categories-list').removeClass('not-empty-list');
        }
        $g('.tags-categories .all-categories-list li').hide();
        app.recentPostsCallback = 'getRecentPosts';
    } else if (app.edit.type == 'post-navigation') {
        app.recentPostsCallback = 'getPostNavigation';
    } else if (app.edit.type == 'search-result' || app.edit.type == 'store-search-result') {
        app.recentPostsCallback = null
    }
    $g('#recent-posts-design-options .ba-style-image-options').first().find('.ba-settings-item').first().css('display', '');
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
    $g('#recent-posts-settings-dialog .overlay-linear-gradient').hide();
    $g('#recent-posts-settings-dialog .overlay-'+value+'-gradient').css('display', '');
    $g('#recent-posts-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="hidden"]').val(value);
    value = $g('#recent-posts-settings-dialog .overlay-gradient-options .gradient-effect-select li[data-value="'+value+'"]').text().trim();
    $g('#recent-posts-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="text"]').val(value);
    value = app.getValue('overlay', 'type');
    $g('#recent-posts-settings-dialog .overlay-color-options, .overlay-gradient-options').hide();
    $g('#recent-posts-settings-dialog .overlay-'+value+'-options').css('display', '');
    $g('#recent-posts-settings-dialog .background-overlay-select input[type="hidden"]').val(value);
    value = $g('#recent-posts-settings-dialog .background-overlay-select li[data-value="'+value+'"]').text().trim();
    $g('#recent-posts-settings-dialog .background-overlay-select input[type="text"]').val(value);
    $g('#recent-posts-settings-dialog input[data-subgroup="gradient"][data-group="overlay"]').each(function(){
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
    updateInput($g('#recent-posts-settings-dialog input[data-group="overlay"][data-option="color"]'), value);
    $g('#recent-posts-settings-dialog input[data-group="view"][type="checkbox"]').each(function(){
        if (this.dataset.option in app.edit.desktop.view) {
            value = app.getValue('view', this.dataset.option);
            this.checked = value;
        }
    });
    $g('#recent-posts-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#recent-posts-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#recent-posts-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#recent-posts-settings-dialog');
    $g('#recent-posts-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#recent-posts-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#recent-posts-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#recent-posts-settings-dialog .ba-style-custom-select input[type="hidden"]').val('image');
    $g('#recent-posts-settings-dialog .ba-style-custom-select input[readonly]').val(gridboxLanguage['IMAGE']);
    $g('#recent-posts-settings-dialog .select-title-html-tag input[type="hidden"]').val(app.edit.tag);
    $g('#recent-posts-settings-dialog .select-title-html-tag input[readonly]').val(app.edit.tag.toUpperCase());
    showBaStyleDesign('image', document.querySelector('#recent-posts-settings-dialog .ba-style-custom-select'));
    setTimeout(function(){
        $g('#recent-posts-settings-dialog').modal();
    }, 150);
}

function getPostNavigation()
{
    app.editor.$g(app.selector).attr('data-maximum', app.edit.maximum);
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getPostNavigation&tmpl=component",
        data: {
            id : app.editor.themeData.id,
            edit_type: app.editor.themeData.edit_type,
            maximum : app.edit.maximum
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .ba-blog-posts-wrapper').innerHTML = msg.responseText;
            app.editor.$g(app.selector+' .ba-blog-posts-wrapper .ba-blog-post').each(function(ind){
                let title = gridboxLanguage['PREVIOUS'],
                    href = $g(this).find('.ba-blog-post-title-wrapper .ba-blog-post-title a').attr('href');
                if (ind != 0) {
                    title = gridboxLanguage['NEXT'];
                }
                let str = '<div class="ba-post-navigation-info"><a href="'+href+'">'+title+'</a></div>';
                $g(this).find('.ba-blog-post-title-wrapper').before(str);
            })
            replaceBlogPostsTag();
            app.editor.app.buttonsPrevent();
            app.sectionRules();
            app.editor.app.initMasonryBlog(app.edit, app.editor.app.edit);
            app.editor.$g(app.selector).find('.ba-blog-post-button-wrapper a')
                .text(app.edit.buttonLabel ? app.edit.buttonLabel : 'Read More');
            checkAppFields($g('#recent-posts-settings-dialog'));
            app.addHistory();
        }
    });
}

function getRelatedPosts()
{
    app.editor.$g(app.selector).attr('data-app', app.edit.app).attr('data-count', app.edit.limit)
        .attr('data-related', app.edit.related).attr('data-maximum', app.edit.maximum);
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getRelatedPosts&tmpl=component",
        data: {
            id : app.editor.themeData.id,
            edit_type: app.editor.themeData.edit_type,
            app : app.edit.app,
            limit : app.edit.limit,
            related : app.edit.related,
            maximum : app.edit.maximum
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .ba-blog-posts-wrapper').innerHTML = msg.responseText;
            replaceBlogPostsTag();
            app.editor.app.buttonsPrevent();
            app.sectionRules();
            app.editor.app.initMasonryBlog(app.edit, app.editor.app.edit);
            app.editor.$g(app.selector).find('.ba-blog-post-button-wrapper a')
                .text(app.edit.buttonLabel ? app.edit.buttonLabel : 'Read More');
            checkAppFields($g('#recent-posts-settings-dialog'));
            app.addHistory();
        }
    });
}

function getRecentPosts()
{
    var category = new Array();
    for (var key in app.edit.categories) {
        category.push(key);
    }
    category = category.join(',');
    checkRecentPostsAppType(app.edit.app, $g('#recent-posts-settings-dialog'));
    app.editor.$g(app.selector).attr('data-app', app.edit.app).attr('data-count', app.edit.limit)
        .attr('data-sorting', app.edit.sorting).attr('data-maximum', app.edit.maximum).attr('data-category', category);
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getRecentPosts&tmpl=component",
        data: {
            id : app.edit.app,
            limit : app.edit.limit,
            sorting : app.edit.sorting,
            category : category,
            maximum : app.edit.maximum,
            featured: Number(app.edit.featured),
            pagination: app.edit.layout.pagination
        },
        complete: function(msg){
            let obj = JSON.parse(msg.responseText);
            app.editor.$g(app.selector+' .ba-blog-posts-pagination').remove();
            app.editor.$g(app.selector+' .ba-blog-posts-wrapper').html(obj.posts).after(obj.pagination);
            replaceBlogPostsTag();
            app.editor.app.buttonsPrevent();
            app.sectionRules();
            app.editor.app.initMasonryBlog(app.edit, app.editor.app.edit);
            app.editor.$g(app.selector).find('.ba-blog-post-button-wrapper a')
                .text(app.edit.buttonLabel ? app.edit.buttonLabel : 'Read More');
            checkAppFields($g('#recent-posts-settings-dialog'));
            app.addHistory();
        }
    });
}

$g('.recent-posts-pagination-select').on('customAction', function(){
    app.edit.layout.pagination = this.querySelector('input[type="hidden"]').value;
    getRecentPosts();
});

app.modules.recentPostsEditor = true;
app.recentPostsEditor();