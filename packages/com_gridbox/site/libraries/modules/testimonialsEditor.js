/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.testimonialsEditor = function(){
    app.selector = '#'+app.editor.app.edit;
    $g('#testimonials-settings-dialog .active').removeClass('active');
    $g('#testimonials-settings-dialog a[href="#testimonials-general-options"]').parent().addClass('active');
    $g('#testimonials-general-options').addClass('active');
    var value;
    drawTestimonialsSortingList();
    $g('#testimonials-settings-dialog [data-group="slideset"]').each(function(){
        value = app.getValue('slideset', this.dataset.option);
        if (this.type == 'checkbox') {
            this.checked = value;
        } else {
            this.value = value;
        }
    });
    $g('#testimonials-settings-dialog .select-testimonial-layout input[type="hidden"]').val(app.edit.layout);
    value = $g('#testimonials-settings-dialog .select-testimonial-layout li[data-value="'+app.edit.layout+'"]').text().trim();
    $g('#testimonials-settings-dialog .select-testimonial-layout input[readonly]').val(value);
    value = app.getValue('view', 'dots');
    $g('#testimonials-settings-dialog [data-group="view"][data-option="dots"]')[0].checked = value;
    value = app.getValue('view', 'arrows');
    $g('#testimonials-settings-dialog [data-group="view"][data-option="arrows"]')[0].checked = value;
    $g('#testimonials-settings-dialog .section-access-select input[type="hidden"]').val(app.edit.access);
    value = $g('#testimonials-settings-dialog .section-access-select li[data-value="'+app.edit.access+'"]').text().trim();
    $g('#testimonials-settings-dialog .section-access-select input[readonly]').val(value);
    $g('#testimonials-settings-dialog .class-suffix').val(app.edit.suffix);
    value = app.getValue('margin', 'top');
    $g('#testimonials-settings-dialog [data-group="margin"][data-option="top"]').val(value);
    value = app.getValue('margin', 'bottom');
    $g('#testimonials-settings-dialog [data-group="margin"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'top');
    $g('#testimonials-settings-dialog [data-group="padding"][data-option="top"]').val(value);
    value = app.getValue('padding', 'right');
    $g('#testimonials-settings-dialog [data-group="padding"][data-option="right"]').val(value);
    value = app.getValue('padding', 'bottom');
    $g('#testimonials-settings-dialog [data-group="padding"][data-option="bottom"]').val(value);
    value = app.getValue('padding', 'left');
    $g('#testimonials-settings-dialog [data-group="padding"][data-option="left"]').val(value);
    setDisableState('#testimonials-settings-dialog');
    setPresetsList($g('#testimonials-settings-dialog'));
    value = app.getValue('shadow', 'value');
    value = $g('#testimonials-settings-dialog input[data-option="value"][data-group="shadow"]').val(value).prev().val(value);
    setLinearWidth(value);
    value = app.getValue('shadow', 'color');
    updateInput($g('#testimonials-settings-dialog input[data-option="color"][data-group="shadow"]'), value);
    for (var key in app.edit.desktop.border) {
        var input = $g('#testimonials-settings-dialog input[data-option="'+key+'"][data-group="border"]');
        value = app.getValue('border', key);
        switch (key) {
            case 'color' :
                updateInput(input, value);
                break;
            case 'width' :
            case 'radius' :
                input.val(value);
                var range = input.prev();
                range.val(value);
                setLinearWidth(range);
                break;
            case 'style' :
                input.val(value);
                var select = input.closest('.ba-custom-select');
                value = select.find('li[data-value="'+value+'"]').text();
                select.find('input[readonly]').val($g.trim(value));
                break;
            default:
                if (value == 1) {
                    input.prop('checked', true);
                } else {
                    input.prop('checked', false);
                }
        }
    }
    $g('#testimonials-settings-dialog .select-testimonials-options input[type="hidden"]').val('testimonial');
    $g('#testimonials-settings-dialog .select-testimonials-options input[readonly]').val(gridboxLanguage['TESTIMONIAL']);
    showTestimonialsDesign('testimonial', $g('#testimonials-settings-dialog .select-testimonials-options'));
    value = app.getValue('background', 'color');
    updateInput($g('#testimonials-settings-dialog input[data-option="color"][data-group="background"]'), value);
    $g('#testimonials-settings-dialog').attr('data-edit', app.edit.type);
    setTimeout(function(){
        $g('#testimonials-settings-dialog').modal();
    }, 150);
}

app.testimonialsCallback = function(){
    app.sectionRules();
    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    }
    app.editor.app.checkModule('initItems', object);
}

function drawTestimonialsSortingList()
{
    var li = app.editor.document.querySelectorAll(app.selector+' ul li.item'),
        container = $g('#testimonials-settings-dialog .sorting-container').empty();
    sortingList = [];
    for (var i = 0; i < li.length; i++) {
        var obj = {
            image: app.edit.slides[i + 1].image,
            link: app.edit.slides[i + 1].link,
            name: li[i].querySelector('.ba-testimonials-name').textContent.trim(),
            testimonial: li[i].querySelector('.ba-testimonials-testimonial').textContent.trim(),
            caption: li[i].querySelector('.ba-testimonials-caption').textContent.trim(),
        }
        sortingList.push(obj);
        container.append(addTestimonialsSortingList(obj, i));
    }
}

function addTestimonialsSortingList(obj, key)
{
    var str = '<div class="sorting-item" data-key="'+key;
    str += '"><div class="sorting-handle"><i class="zmdi zmdi-apps"></i></div>';
    if (obj.image) {
        str += '<div class="sorting-image">';
        var src = obj.image;
        if (src.indexOf('balbooa.com') == -1) {
            src = JUri+obj.image;
        }
        str += '<img src="'+src+'">';
        str += '</div>';
    }
    str += '<div class="sorting-title">';
    str += obj.name
    str += '</div><div class="sorting-icons">';
    str += '<span><i class="zmdi zmdi-edit"></i></span>';
    str += '<span><i class="zmdi zmdi-copy"></i></span>';
    str += '<span><i class="zmdi zmdi-delete"></i></span></div></div>';

    return str;
}

function showTestimonialsDesign(search, select)
{
    var parent = $g(select).closest('.tab-pane');
    parent.children().not('.slideshow-design-group, .testimonials-background-options').hide();
    switch (search) {
        case 'name' :
        case 'testimonial' :
        case 'caption' :
            parent.find('.slideshow-typography-options').css('display', '').find('[data-subgroup="typography"]').attr('data-group', search);
            parent.find('.slideshow-typography-options .typography-options').addClass('ba-active-options');
            setTimeout(function(){
                parent.find('.slideshow-typography-options .typography-options').removeClass('ba-active-options');
            }, 1);
            break;
        case 'arrows' :
            parent.find('.testimonials-normal-options').css('display', '').find('input[data-subgroup]').attr('data-group', search);
            parent.find('.testimonials-hover-options').css('display', '').find('input[data-subgroup]').attr('data-group', search);
            parent.find('.testimonials-arrows-options').css('display', '').find('input[data-subgroup]').attr('data-group', search);
            parent.find('.testimonials-border-options').css('display', '').find('input[data-subgroup]').attr('data-group', search);
            parent.find('.testimonials-shadow-options').css('display', '').find('input[data-subgroup]').attr('data-group', search);
            parent.find('.testimonials-normal-options, .testimonials-hover-options, .testimonials-arrows-options')
                .addClass('ba-active-options');
            setTimeout(function(){
                parent.find('.testimonials-normal-options, .testimonials-hover-options, .testimonials-arrows-options')
                    .removeClass('ba-active-options');
            }, 1);
            break;
        case 'dots' :
            parent.find('.testimonials-dots-options').css('display', '').find('input[data-subgroup]').attr('data-group', search);
            parent.find('.testimonials-dots-options').addClass('ba-active-options');
            setTimeout(function(){
                parent.find('.testimonials-dots-options').removeClass('ba-active-options');
            }, 1);
            break;
        case 'icon' :
            parent.find('.testimonials-icon-options').css('display', '').find('input[data-subgroup]').attr('data-group', search);
            parent.find('.testimonials-icon-options').addClass('ba-active-options');
            setTimeout(function(){
                parent.find('.testimonials-icon-options').removeClass('ba-active-options');
            }, 1);
            break;
        case 'image' :
            parent.find('.testimonials-image-options').css('display', '').find('input[data-subgroup]').attr('data-group', search);
            parent.find('.testimonials-image-options').addClass('ba-active-options');
            setTimeout(function(){
                parent.find('.testimonials-image-options').removeClass('ba-active-options');
            }, 1);
            break;
    }
    value = app.getValue(search);
    for (var ind in value) {
        if (typeof(value[ind]) == 'object') {
            if (ind == 'typography') {
                app.setTypography(parent.find('.slideshow-typography-options .typography-options'), search, ind);
            } else {
                for (var key in value[ind]) {
                    var input = parent.find('[data-group="'+search+'"][data-option="'+key+'"][data-subgroup="'+ind+'"]');
                    if (input.attr('data-type') == 'color') {
                        updateInput(input, value[ind][key]);
                    } else if (input.attr('type') == 'number') {
                        var range = input.prev();
                        input.val(value[ind][key]);
                        range.val(value[ind][key]);
                        setLinearWidth(range);
                    } else {
                        input.val(value[ind][key]);
                        if (input.attr('type') == 'hidden') {
                            var text = input.closest('.ba-custom-select').find('li[data-value="'+value[ind][key]+'"]').text();
                            input.closest('.ba-custom-select').find('input[readonly]').val($g.trim(text));
                        }
                    }
                }
            }
        } else {
            var input = parent.find('[data-group="'+search+'"][data-option="'+ind+'"]');
            if (input.attr('data-type') == 'color') {
                updateInput(input, value[ind]);
            } else if (input.attr('type') == 'number') {
                var range = input.prev();
                input.val(value[ind]);
                range.val(value[ind]);
                setLinearWidth(range);
            } else {
                input.val(value[ind][key]);
                if (input.attr('type') == 'hidden') {
                    var text = input.closest('.ba-custom-select').find('li[data-value="'+value[ind]+'"]').text();
                    input.closest('.ba-custom-select').find('input[readonly]').val($g.trim(text));
                }
            }
        }
    }
}

$g('#testimonials-settings-dialog .sorting-container').on('click', 'i.zmdi.zmdi-edit', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key') * 1,
        obj = sortingList[key * 1],
        modal = $g('#testimonial-item-edit-modal');
    $g('#apply-testimonial-item').attr('data-key', key);
    modal.find('.image-item-testimonial').val(obj.testimonial);
    modal.find('.image-item-upload-image').val(obj.image);
    modal.find('.image-item-name').val(obj.name);
    modal.find('.image-item-caption').val(obj.caption);
    modal.find('.image-item-link').val(obj.link);
    modal.modal();
});

$g('#testimonials-settings-dialog .zmdi-plus-circle').on('click', function(){
    var modal = $g('#testimonial-item-edit-modal');
    $g('#apply-testimonial-item').attr('data-key', -1);
    modal.find('.image-item-testimonial').val('');
    modal.find('.image-item-upload-image').val('');
    modal.find('.image-item-name').val('');
    modal.find('.image-item-caption').val('');
    modal.find('.image-item-link').val('');
    modal.modal();
});

$g('#apply-testimonial-item').on('click', function(){
    var key = this.dataset.key * 1,
    modal = $g('#testimonial-item-edit-modal'),
    obj = {
        testimonial : modal.find('.image-item-testimonial').val().trim(),
        image: modal.find('.image-item-upload-image').val(),
        name: modal.find('.image-item-name').val().trim(),
        caption: modal.find('.image-item-caption').val().trim(),
        link: modal.find('.image-item-link').val().trim(),
    };
    if (key < 0) {
        var str = '<li class="item">'+
                '<div class="testimonials-wrapper">'+
                    '<div class="testimonials-icon-wrapper"><i class="zmdi zmdi-quote"></i></div>'+
                    '<div class="ba-testimonials-img"><div class="testimonials-img"></div></div>'+
                    '<div class="testimonials-info">'+
                        '<div class="testimonials-icon-wrapper"><i class="zmdi zmdi-quote"></i></div>'+
                        '<div class="testimonials-testimonial-wrapper"><div class="ba-testimonials-testimonial"></div></div>'+
                    '</div>'+
                    '<div class="testimonials-title-wrapper">'+
                        '<div class="testimonials-name-wrapper"><span class="ba-testimonials-name"></span></div>'+
                        '<div class="testimonials-caption-wrapper"><span class="ba-testimonials-caption"></span></div>'+
                    '</div>'+
                 '</div>'+
             '</li>';
        app.editor.$g(app.selector+' .slideshow-content').append(str);
        key = 0;
        for (var ind in app.edit.slides) {
            key = ind *1;
        }
        app.edit.slides[key + 1] = {
            image: '',
            link: ''
        }
    }
    var li = app.editor.document.querySelector(app.selector+' li.item:nth-child('+(key + 1)+')'),
        img = li.querySelector('.testimonials-img'),
        caption = li.querySelector('.ba-testimonials-caption');
    app.edit.slides[key + 1].image = obj.image;
    app.edit.slides[key + 1].link = obj.link;
    if (!obj.image && img) {
        img.parentNode.removeChild(img);
    } else if (obj.image && !img) {
        li.querySelector('.ba-testimonials-img').innerHTML = '<div class="testimonials-img"></div>';
    }
    if (obj.link && caption.localName != 'a') {
        caption.parentNode.innerHTML = '<a class="ba-testimonials-caption" target="_blank"></a>';
        caption = li.querySelector('.ba-testimonials-caption');
    } else if (!obj.link && caption.localName == 'a') {
        caption.parentNode.innerHTML = '<span class="ba-testimonials-caption"></span>';
        caption = li.querySelector('.ba-testimonials-caption');
    }
    if (obj.link) {
        caption.setAttribute('href', obj.link);
    }
    li.querySelector('.ba-testimonials-name').textContent = obj.name;
    li.querySelector('.ba-testimonials-testimonial').textContent = obj.testimonial;
    caption.textContent = obj.caption;
    drawTestimonialsSortingList();
    app.testimonialsCallback();
    app.addHistory();
    modal.modal('hide');
});

$g('#testimonials-settings-dialog .sorting-container').on('click', 'i.zmdi.zmdi-copy', function(){
    var key = $g(this).closest('.sorting-item').attr('data-key') * 1,
        image = app.editor.$g(app.selector+' .slideshow-content > li').get(key),
        clone = image.cloneNode(true),
        obj = $g.extend({}, app.edit.slides[key + 1]),
        slides = {};
    key += 1;
    $g(image).after(clone);
    app.editor.app.buttonsPrevent();
    for (var ind in app.edit.slides) {
        if (ind == key) {
            slides[ind] = app.edit.slides[ind];
            slides[key + 1] = obj;
        } else if (ind >= key + 1) {
            slides[ind * 1 + 1] = app.edit.slides[ind];
        } else {
            slides[ind] = app.edit.slides[ind];
        }
    }
    app.edit.slides = slides;
    drawTestimonialsSortingList();
    app.testimonialsCallback();
    app.addHistory();
});

$g('#testimonials-settings-dialog .sorting-container').on('click', '.zmdi.zmdi-delete', function(){
    app.itemDelete = $g(this).closest('.sorting-item').attr('data-key');
    app.checkModule('deleteItem');
});

$g('#testimonials-settings-dialog [data-group="slideset"]').on('change input', function(){
    var option = this.dataset.option,
        value = this.value;
    if (this.type == 'checkbox') {
        value = this.checked;
    } else if (value == '') {
        value = app.getValue('slideset', option);
    }
    app.setValue(value, 'slideset', option);
    app.testimonialsCallback();
    delay = setTimeout(function(){
        app.addHistory();
    }, 300);
});

$g('#testimonials-settings-dialog .select-testimonial-layout').on('customAction', function(){
    value = this.querySelector('input[type="hidden"]').value;
    var ul = app.editor.$g(app.selector+' ul.ba-testimonials').removeClass(app.edit.layout).addClass(value);
    app.edit.layout = value;





    var object = {
        data : app.edit,
        selector : app.editor.app.edit
    }
    app.editor.app.checkModule('initItems', object);
    app.addHistory();
});

$g('.select-testimonials-options').on('customAction', function(){
    value = this.querySelector('input[type="hidden"]').value;
    showTestimonialsDesign(value, $g('#testimonials-settings-dialog .select-testimonials-options'));
});

app.modules.testimonialsEditor = true;
app.testimonialsEditor();