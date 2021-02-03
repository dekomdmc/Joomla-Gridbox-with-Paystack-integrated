/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.recentCommentsEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#recent-comments-settings-dialog .active').removeClass('active');
    $g('#recent-comments-settings-dialog a[href="#recent-comments-general-options"]').parent().addClass('active');
    $g('#recent-comments-general-options').addClass('active');
    $g('#recent-comments-settings-dialog').attr('data-edit', app.edit.type);
    setPresetsList($g('#recent-comments-settings-dialog'));
    $g('#recent-comments-general-options .preset-options').css('display', '');
    $g('#recent-comments-settings-dialog .recent-posts-app-select input[type="hidden"]').val(app.edit.app);
    var value = $g('#recent-comments-settings-dialog .recent-posts-app-select li[data-value="'+app.edit.app+'"]').text().trim();
    $g('#recent-comments-settings-dialog .recent-posts-app-select input[readonly]').val(value);
    $g('#recent-comments-settings-dialog .recent-comments-display-select input[type="hidden"]').val(app.edit.sorting);
    value = $g('#recent-comments-settings-dialog .recent-comments-display-select li[data-value="'+app.edit.sorting+'"]').text();
    $g('#recent-comments-settings-dialog .recent-comments-display-select input[readonly]').val($g.trim(value));
    $g('#recent-comments-settings-dialog input[data-option="limit"]').val(app.edit.limit);
    $g('#recent-comments-settings-dialog input[data-option="maximum"]').val(app.edit.maximum);
    value = app.getValue('background', 'color');
    updateInput($g('#recent-comments-settings-dialog .blog-posts-background-options input[data-option="color"]'), value);
    value = app.getValue('shadow', 'color');
    updateInput($g('#recent-comments-settings-dialog .blog-posts-shadow-options input[data-option="color"]'), value);
    value = app.getValue('shadow', 'value');
    var input = $g('#recent-comments-settings-dialog .blog-posts-shadow-options input[data-option="value"]'),
        range = input.prev();
    input.val(value);
    range.val(value);
    setLinearWidth(range);
    value = app.getValue('border', 'radius');
    value = $g('#recent-comments-settings-dialog input[data-option="radius"][data-group="border"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'width');
    value = $g('#recent-comments-settings-dialog input[data-option="width"][data-group="border"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'color');
    updateInput($g('#recent-comments-settings-dialog input[data-option="color"][data-group="border"]'), value);
    value = app.getValue('border', 'style');
    $g('#recent-comments-layout-options .border-style-select input[type="hidden"]').val(value);
    value = $g('#recent-comments-layout-options .border-style-select li[data-value="'+value+'"]').text();
    $g('#recent-comments-layout-options .border-style-select input[readonly]').val($g.trim(value));
    $g('#recent-comments-settings-dialog .recent-posts-display-select input[type="hidden"]').val(app.edit.sorting);
    value = $g('#recent-comments-settings-dialog .recent-posts-display-select li[data-value="'+app.edit.sorting+'"]').text().trim();
    $g('#recent-comments-settings-dialog .recent-posts-display-select input[readonly]').val(value);
    if (!app.edit.categories) {
        app.edit.categories = {};
    }
    $g('.selected-categories li:not(.search-category)').remove();
    $g('.all-categories-list .selected-category').removeClass('selected-category');
    for (var key in app.edit.categories) {
        var str = getCategoryHtml(key, app.edit.categories[key].title);
        $g('#recent-comments-settings-dialog .selected-categories li.search-category').before(str);
        $g('#recent-comments-settings-dialog .all-categories-list [data-id="'+key+'"]').addClass('selected-category');
    }
    if ($g('.selected-categories li:not(.search-category)').length > 0) {
        $g('.ba-settings-item.tags-categories-list').addClass('not-empty-list');
    } else {
        $g('.ba-settings-item.tags-categories-list').removeClass('not-empty-list');
    }
    $g('.tags-categories .all-categories-list li').hide();
    if (app.edit.type == 'recent-comments') {
        app.recentPostsCallback = 'getRecentComments';
        $g('#recent-comments-settings-dialog .recent-reviews-options').hide();
        $g('#recent-comments-settings-dialog .recent-comments-options').css('display', '');
    } else {
        app.recentPostsCallback = 'getRecentReviews';
        $g('#recent-comments-settings-dialog .recent-reviews-options').css('display', '');
        $g('#recent-comments-settings-dialog .recent-comments-options').hide();


        $g('#recent-comments-settings-dialog .blog-posts-layout-select input[type="hidden"]').val(app.edit.layout.layout);
        value = $g('#recent-comments-settings-dialog .blog-posts-layout-select li[data-value="'+app.edit.layout.layout+'"]').text().trim();
        $g('#recent-comments-settings-dialog .blog-posts-layout-select input[readonly]').val(value);
        if (app.edit.layout.layout == 'ba-classic-layout') {
            $g('.blog-posts-grid-options').hide();
        } else {
            $g('.blog-posts-grid-options').css('display', '');
        }
        value = app.getValue('view', 'count');
        $g('#recent-comments-settings-dialog input[data-option="count"]').val(value);
    }
    $g('#recent-comments-design-options .ba-style-image-options').first().find('.ba-settings-item').first().css('display', '');
    $g('#recent-comments-settings-dialog input[data-group="view"][type="checkbox"]').each(function(){
        if (this.dataset.option in app.edit.desktop.view) {
            value = app.getValue('view', this.dataset.option);
            this.checked = value;
        }
    })
    $g('#recent-comments-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#recent-comments-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#recent-comments-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    $g('#recent-comments-settings-dialog input[data-group="padding"]').each(function(){
        value = app.getValue('padding', this.dataset.option);
        this.value = value;
    });
    setDisableState('#recent-comments-settings-dialog');
    $g('#recent-comments-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#recent-comments-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#recent-comments-settings-dialog .section-access-select input[readonly]').val($g.trim(value));
    $g('#recent-comments-settings-dialog .ba-style-custom-select input[type="hidden"]').val('image');
    $g('#recent-comments-settings-dialog .ba-style-custom-select input[readonly]').val(gridboxLanguage['IMAGE']);
    $g('#recent-comments-settings-dialog .select-title-html-tag input[type="hidden"]').val(app.edit.tag);
    $g('#recent-comments-settings-dialog .select-title-html-tag input[readonly]').val(app.edit.tag.toUpperCase());
    showBaStyleDesign('image', document.querySelector('#recent-comments-settings-dialog .ba-style-custom-select'));
    setTimeout(function(){
        $g('#recent-comments-settings-dialog').modal();
    }, 150);
}



function getRecentComments()
{
    var category = new Array();
    for (var key in app.edit.categories) {
        category.push(key);
    }
    category = category.join(',');
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getRecentComments&tmpl=component",
        data: {
            id : app.edit.app,
            limit : app.edit.limit,
            sorting : app.edit.sorting,
            category : category,
            maximum : app.edit.maximum
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .ba-blog-posts-wrapper').innerHTML = msg.responseText;
            replaceBlogPostsTag();
            app.editor.app.buttonsPrevent();
            app.addHistory();
        }
    });
}

function getRecentReviews()
{
    var category = new Array();
    for (var key in app.edit.categories) {
        category.push(key);
    }
    category = category.join(',');
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getRecentReviews&tmpl=component",
        data: {
            id : app.edit.app,
            limit : app.edit.limit,
            sorting : app.edit.sorting,
            category : category,
            maximum : app.edit.maximum
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .ba-blog-posts-wrapper').innerHTML = msg.responseText;
            replaceBlogPostsTag();
            app.editor.app.initMasonryBlog(app.edit, app.editor.app.edit);
            app.editor.app.buttonsPrevent();
            app.addHistory();
        }
    });
}

app.modules.recentCommentsEditor = true;
app.recentCommentsEditor();