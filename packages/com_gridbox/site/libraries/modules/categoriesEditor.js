app.categoriesEditor = function(){
	app.selector = '#'+app.editor.app.edit;
    $g('#categories-settings-dialog .active').removeClass('active');
    $g('#categories-settings-dialog a[href="#categories-general-options"]').parent().addClass('active');
    $g('#categories-general-options').addClass('active');
    setPresetsList($g('#categories-settings-dialog'));
    $g('#categories-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    var value = $g('#categories-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text();
    $g('#categories-settings-dialog .section-access-select input[readonly]').val(value.trim());
    $g('#categories-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#categories-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#categories-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    setDisableState('#categories-settings-dialog');
    $g('#categories-settings-dialog .categories-app-custom-select input[type="hidden"]').val(app.edit.app);
    value = $g('#categories-settings-dialog .categories-app-custom-select li[data-value="'+app.edit.app+'"]').text().trim();
    $g('#categories-settings-dialog .categories-app-custom-select input[readonly]').val(value);
    $g('#categories-settings-dialog input[data-option="maximum"]').val(app.edit.maximum);
    $g('#categories-settings-dialog input[data-group="padding"]').each(function(){
        value = app.getValue('padding', this.dataset.option);
        this.value = value;
    });
    $g('#categories-settings-dialog .blog-posts-layout-select input[type="hidden"]').val(app.edit.layout.layout);
    value = $g('#categories-settings-dialog .blog-posts-layout-select li[data-value="'+app.edit.layout.layout+'"]').text();
    $g('#categories-settings-dialog .blog-posts-layout-select input[readonly]').val(value.trim());
    $g('.blog-posts-cover-options').hide();
    $g('.blog-posts-grid-options').css('display', app.edit.layout.layout == 'ba-classic-layout' ? 'none' : '');
    if (app.edit.layout.layout == 'ba-cover-layout') {
        $g('#categories-design-options .ba-style-image-options').first().find('.ba-settings-item').first().hide();
        $g('.blog-posts-cover-options').css('display', '');
        $g('.blog-posts-background-options').hide();
    }
    value = app.getValue('view', 'count');
    $g('#categories-settings-dialog input[data-option="count"]').val(value);
    value = app.getValue('background', 'color');
    updateInput($g('#categories-settings-dialog .blog-posts-background-options input[data-option="color"]'), value);
    value = app.getValue('shadow', 'color');
    updateInput($g('#categories-settings-dialog .blog-posts-shadow-options input[data-option="color"]'), value);
    value = app.getValue('shadow', 'value');
    value = $g('#categories-settings-dialog .blog-posts-shadow-options input[data-option="value"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'radius');
    value = $g('#categories-settings-dialog input[data-option="radius"][data-group="border"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'width');
    value = $g('#categories-settings-dialog input[data-option="width"][data-group="border"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('border', 'color');
    updateInput($g('#categories-settings-dialog input[data-option="color"][data-group="border"]'), value);
    value = app.getValue('border', 'style');
    $g('#categories-layout-options .border-style-select input[type="hidden"]').val(value);
    value = $g('#categories-layout-options .border-style-select li[data-value="'+value+'"]').text();
    $g('#categories-layout-options .border-style-select input[readonly]').val(value.trim());
    app.recentPostsCallback = 'getBlogCategories';
    $g('#categories-design-options .ba-style-image-options').first().find('.ba-settings-item').first().css('display', '');
    value = app.getValue('overlay', 'effect', 'gradient');
    $g('#categories-settings-dialog .overlay-linear-gradient').hide();
    $g('#categories-settings-dialog .overlay-'+value+'-gradient').css('display', '');
    $g('#categories-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="hidden"]').val(value);
    value = $g('#categories-settings-dialog .overlay-gradient-options .gradient-effect-select li[data-value="'+value+'"]').text().trim();
    $g('#categories-settings-dialog .overlay-gradient-options .gradient-effect-select input[type="text"]').val(value);
    value = app.getValue('overlay', 'type');
    $g('#categories-settings-dialog .overlay-color-options, .overlay-gradient-options').hide();
    $g('#categories-settings-dialog .overlay-'+value+'-options').css('display', '');
    $g('#categories-settings-dialog .background-overlay-select input[type="hidden"]').val(value);
    value = $g('#categories-settings-dialog .background-overlay-select li[data-value="'+value+'"]').text().trim();
    $g('#categories-settings-dialog .background-overlay-select input[type="text"]').val(value);
    $g('#categories-settings-dialog input[data-subgroup="gradient"][data-group="overlay"]').each(function(){
        value = app.getValue('overlay', this.dataset.option, 'gradient');
        if (this.type == 'number') {
            var range = $g(this).val(value).prev().val(value);
            setLinearWidth(range);
        } else {
            updateInput($g(this), value);
        }
    });
    value = app.getValue('overlay', 'color');
    updateInput($g('#categories-settings-dialog input[data-group="overlay"][data-option="color"]'), value);
    $g('#categories-settings-dialog input[data-group="view"][type="checkbox"]').each(function(){
        if (this.dataset.option in app.edit.desktop.view) {
            value = app.getValue('view', this.dataset.option);
            this.checked = value;
        }
    });
    $g('#categories-settings-dialog .ba-style-custom-select input[type="hidden"]').val('image');
    $g('#categories-settings-dialog .ba-style-custom-select input[readonly]').val(gridboxLanguage['IMAGE']);
    $g('#categories-settings-dialog .select-title-html-tag input[type="hidden"]').val(app.edit.tag);
    $g('#categories-settings-dialog .select-title-html-tag input[readonly]').val(app.edit.tag.toUpperCase());
    showBaStyleDesign('image', document.querySelector('#categories-settings-dialog .ba-style-custom-select'));
    setTimeout(function(){
        $g('#categories-settings-dialog').modal();
    }, 150);
}

function getBlogCategories()
{
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.getBlogCategories&tmpl=component",
        data: {
            id : app.edit.app,
            maximum: app.edit.maximum
        },
        complete: function(msg){
            app.editor.document.querySelector(app.selector+' .ba-categories-wrapper').innerHTML = msg.responseText;
            app.editor.app.initMasonryBlog(app.edit, app.editor.app.edit);
            app.editor.app.buttonsPrevent();
            app.addHistory();
        }
    });
}

$g('.categories-app-custom-select').on('customAction', function(){
    var id = this.querySelector('input[type="hidden"]').value;
    if (id != app.edit.app) {
        app.edit.app = id;
        app.editor.$g(app.selector).attr('data-app', app.edit.app);
        getBlogCategories()
    }
});

app.modules.categoriesEditor = true;
app.categoriesEditor();