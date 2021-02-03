/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.messageListener = function(){
	switch (uploadMode) {
        case 'fonts':
            var font = app.messageData.font.split(' '),
                callback = fontBtn.dataset.callback,
                subgroup = fontBtn.dataset.subgroup,
                group = fontBtn.dataset.group;
            if (!app.messageData.custom) {
                addFontLink(font);
            } else {
                addFontStyle(app.messageData);
            }
            fontBtn.value = font[0].replace(new RegExp('\\+','g'), ' ')+' '+font[1].replace('i', 'italic');
            if (!subgroup) {
                app.edit.desktop[group]['font-family'] = font[0];
                app.edit.desktop[group]['font-weight'] = font[1];
                app.edit.desktop[group]['custom'] = app.messageData.custom;
            } else {
                app.edit.desktop[group][subgroup]['font-family'] = font[0];
                app.edit.desktop[group][subgroup]['font-weight'] = font[1];
                app.edit.desktop[group][subgroup]['custom'] = app.messageData.custom;
            }
            $g('#fonts-editor-dialog').modal('hide');
            setTimeout(function(){
                app[callback]();
            }, 300);
            app.addHistory();
            break;
        case 'addNewSlides' : 
            var array = app.messageData,
                index = 1;
            for (var ind in app.edit.desktop.slides) {
                index++;
            }
            for (var i = 0; i < array.length; i++) {
                var obj = {
                        image : IMAGE_PATH+array[i].path,
                        index : index++,
                        type : 'image',
                        video : null,
                        title : '',
                        description :'',
                        button : {
                            href : '#',
                            type : 'ba-btn-transition',
                            title : '',
                            target : '_blank'
                        }
                    },
                    str = getSlideHtml(obj),
                    dots = app.editor.document.querySelector('#'+app.editor.app.edit+' .ba-slideshow-dots'),
                    div = app.editor.document.querySelector('#'+app.editor.app.edit+' .slideshow-content');
                $g(div).append(str);
                str = '<div data-ba-slide-to="'+(obj.index - 1)+'" class="zmdi zmdi-circle"></div>';
                $g(dots).append(str);
                sortingList.push(obj);
                app.edit['desktop'].slides[obj.index] = {
                    image : obj.image,
                    type : obj.type,
                    link : "",
                    video : obj.video
                }
                $g('#slideshow-settings-dialog .sorting-container').append(addSlideSortingList(obj, sortingList.length - 1));
            }
            var object = {
                data : app.edit,
                selector : app.editor.app.edit
            }
            app.sectionRules();
            app.editor.app.checkModule('initItems', object);
            app.addHistory();
            $g('#uploader-modal').modal('hide');
            break;
        case 'addFieldSortingItem' :
            var array = app.messageData;
            for (var i = 0; i < array.length; i++) {
                let str = addFieldSortingList(IMAGE_PATH+array[i].path, '');
                app.addFieldSortingWrapper.append(str);
            }
            app.addFieldSortingWrapper.closest('.blog-post-editor-options-group').removeClass('ba-alert-label');
            $g('#uploader-modal').modal('hide');
            break;
        case 'reselctSlideshowFieldSortingImg':
            app.addFieldSortingItem.dataset.img = IMAGE_PATH+app.messageData.path;
            app.addFieldSortingItem.dataset.path = IMAGE_PATH+app.messageData.path;
            app.addFieldSortingItem.querySelector('img').src = JUri+IMAGE_PATH+app.messageData.path;
            var array = app.messageData.path.split('/');
            app.addFieldSortingItem.querySelector('.sorting-title').textContent = array[array.length - 1];
            $g('#uploader-modal').modal('hide');
            break;
        case 'uploadVariationsPhotos':
            var array = app.messageData;
            for (var i = 0; i < array.length; i++) {
                app.productImages[fontBtn].push(IMAGE_PATH+array[i].path);
            }
            updateOptionsImageCount(fontBtn);
            if (document.querySelector('#product-variations-photos-dialog').classList.contains('in')) {
                prepareVariationsPhotosDialog(fontBtn);
            }
            $g('#uploader-modal').modal('hide');
            break;
        case 'reselctFieldSortingImg':
            var array = app.messageData.path.split('/');
            fontBtn.dataset.image = IMAGE_PATH+app.messageData.path;
            fontBtn.dataset.path = IMAGE_PATH+app.messageData.path;
            fontBtn.value = array[array.length - 1];
            $g('#uploader-modal').modal('hide');
            break;
        case 'addSimpleImages' :
            var array = app.messageData,
                wrapper = app.editor.$g(app.selector+' .instagram-wrapper');
            for (var i = 0; i < array.length; i++) {
                var str = '<div class="ba-instagram-image" style="background-image: url(',
                    n = sortingList.length,
                    obj = {
                        src: IMAGE_PATH+array[i].path,
                        alt: '',
                        title: '',
                        description: ''
                    };
                str += IMAGE_PATH+array[i].path+')"><img src="'+IMAGE_PATH+array[i].path+
                    '" data-src="'+IMAGE_PATH+array[i].path+'"><div class="ba-simple-gallery-image"></div>'+
                    '<div class="ba-simple-gallery-caption"><div class="ba-caption-overlay"></div>'+
                    '<'+app.edit.tag+' class="ba-simple-gallery-title"></'+app.edit.tag+
                    '><div class="ba-simple-gallery-description"></div></div></div>';
                if (wrapper.find('.empty-list').length > 0) {
                    wrapper.find('.empty-list').before(str);
                } else {
                    wrapper.append(str);
                }
                sortingList.push(obj);
                $g('#item-settings-dialog .sorting-container').append(addSimpleSortingList(sortingList[n], n));
            }
            app.addHistory();
            $g('#uploader-modal').modal('hide');
            break;
        case 'reselectSimpleImage':
            var img = IMAGE_PATH+app.messageData.path;
            fontBtn.value = img;
            $g(fontBtn).trigger('input');
            $g('#uploader-modal').modal('hide');
            break;
        case 'selectFile' :
            var img = IMAGE_PATH+app.messageData.path;
            $g(fontBtn).val(img).trigger('input');
            $g('#uploader-modal').modal('hide');
            break;
        case 'slideImage' :
            var img = IMAGE_PATH+app.messageData.path;
            $g('#uploader-modal').modal('hide');
            fontBtn.value = img;
            $g(fontBtn).trigger('input');
            break;
        case 'reselectLibraryImage':
            var obj = {
                    id: app.itemDelete,
                    image: IMAGE_PATH+app.messageData.path
                };
            $g('.camera-container[data-id="'+obj.id+'"]').parent().css('background-image', 'url('+obj.image+')')
            $g.ajax({
                type: "POST",
                dataType: 'text',
                url: "index.php?option=com_gridbox&task=editor.setLibraryImage",
                data:{
                    object: JSON.stringify(obj)
                },
                complete: function(msg){
                    
                }
            });
            $g('#uploader-modal').modal('hide');
            break;
        case 'itemSimpleGallery' :
            var array = app.messageData,
                obj = {
                    data : 'simple-gallery',
                    selector : new Array()
                }
            for (var i = 0; i < array.length; i++) {
                obj.selector.push(IMAGE_PATH+array[i].path)
            }
            app.editor.app.checkModule('loadPlugin' , obj);
            $g('#uploader-modal').modal('hide');
            break;
        case 'itemSlideshow':
        case 'itemSlideset':
        case 'itemCarousel':
        case 'itemContent-Slider':
            var array = app.messageData,
                obj = {
                    data : uploadMode.replace('item', '').toLowerCase(),
                    selector : new Array()
                }
            for (var i = 0; i < array.length; i++) {
                obj.selector.push(IMAGE_PATH+array[i].path)
            }
            app.editor.app.checkModule('loadPlugin' , obj);
            $g('#uploader-modal').modal('hide');
            break;
        case 'contentSliderAdd':
            var array = app.messageData,
                data = new Array();
            for (var i = 0; i < array.length; i++) {
                data.push(IMAGE_PATH+array[i].path)
            }
            contentSliderAdd(data);
            $g('#uploader-modal').modal('hide');
            break;
        case 'itemImage' :
            var obj = {
                    data : 'image',
                    selector : IMAGE_PATH+app.messageData.path,
                }
            app.editor.app.checkModule('loadPlugin' , obj);
            if ($g('#uploader-modal').hasClass('in')) {
                $g('#uploader-modal').modal('hide');
            }
            break;
        case 'itemLogo' :
            var obj = {
                    data : 'logo',
                    selector : IMAGE_PATH+app.messageData.path,
                }
            app.editor.app.checkModule('loadPlugin' , obj);
            $g('#uploader-modal').modal('hide');
            break;
        case 'reselectSocialIcon':
            fontBtn.value = app.messageData.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '')
            fontBtn.dataset.icon = app.messageData;
            $g(fontBtn).trigger('change');
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'addSocialIcon':
            var obj = {
                    "icon" : app.messageData,
                    "title": app.messageData.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', ''),
                    "link" : {
                        "link" : "",
                        "target" : "_blank"
                    }
                },
                i = 0;
            for (var ind in app.edit.icons) {
                i++;
            }
            app.edit.icons[i] = obj;
            getSocialIconsHtml(app.edit.icons);
            sortingList.push(app.edit.icons[i]);
            $g('#social-icons-settings-dialog .sorting-container').append(addSortingList(app.edit.icons[i], i));
            $g('#icon-upload-dialog').modal('hide');
            app.addHistory();
            break;
        case 'itemIcon' :
            var obj = {
                    data : 'icon',
                    selector : app.messageData,
                };
            app.editor.app.checkModule('loadPlugin' , obj);
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'scrolltopIcon' :
            var i = app.editor.document.getElementById(app.editor.app.edit),
                classList;
            i = i.querySelector('i.ba-btn-transition');
            classList = app.edit.icon;
            $g(i).removeClass(classList);
            classList = app.messageData;
            $g(i).addClass(classList);
            app.edit.icon = app.messageData;
            fontBtn.value = classList.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
            app.addHistory();
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'smoothScrollingIcon' :
            var item = app.editor.document.querySelector('#'+app.editor.app.edit+' a'),
                i = item.querySelector('a i');
            if (i) {
                i.className = app.messageData;
            } else {
                i = document.createElement('i');
                i.className = app.messageData;
                item.appendChild(i);
            }
            app.edit.icon = app.messageData;
            fontBtn.value = app.messageData.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
            app.addHistory();
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'selectItemIcon' :
            fontBtn.value = app.messageData.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
            fontBtn.dataset.value = app.messageData;
            $g(fontBtn).trigger('input');
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'reselectIcon' :
            var i = app.editor.document.getElementById(app.editor.app.edit),
                classList;
            i = i.querySelector('.ba-icon-wrapper i');
            classList = i.dataset.icon;
            $g(i).removeClass(classList);
            classList = app.messageData;
            $g(i).addClass(classList);
            i.dataset.icon = app.messageData;
            fontBtn.value = classList.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
            app.addHistory();
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'addSearchIcon':
            var item = app.editor.document.getElementById(app.editor.app.edit),
                classList,
                i = item.querySelector('.ba-search-wrapper i');
            if (i) {
                classList = i.className;
                $g(i).removeClass(classList);
                classList = app.messageData;
                $g(i).addClass(classList);
            } else {
                i = document.createElement('i');
                i.className = app.messageData;
                item = item.querySelector('.ba-search-wrapper');
                item.appendChild(i);
            }
            app.edit.icon.icon = app.messageData;
            fontBtn.value = app.messageData.replace('zmdi zmdi-', '').replace('fa fa-', '').replace('flaticon-', '');
            app.addHistory();
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'addFieldIcon':
            var label = app.editor.$g(app.selector+' .ba-field-label'),
                i = label.find('> i')[0];
            if (i) {
                i.className = app.messageData;
            } else {
                i = document.createElement('i');
                i.className = app.messageData;
                label.prepend(i);
            }
            app.edit.icon = app.messageData;
            fontBtn.value = app.messageData.replace(/zmdi zmdi-|fa fa-|flaticon-/, '');
            app.addHistory();
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'addCartIcon' :
            fontBtn.value = app.messageData.replace(/zmdi zmdi-|fa fa-|flaticon-/, '');
            fontBtn.dataset.value = app.messageData;
            $g(fontBtn).trigger('change');
            $g('#icon-upload-dialog').modal('hide');
            break;    
        case 'addButtonIcon' :
            var item = app.editor.document.getElementById(app.editor.app.edit),
                classList,
                i = item.querySelector('a i');
            if (i) {
                classList = i.className;
                $g(i).removeClass(classList);
                classList = app.messageData;
                $g(i).addClass(classList);
            } else {
                i = document.createElement('i');
                i.className = app.messageData;
                item = item.querySelector('a');
                item.appendChild(i);
            }
            fontBtn.value = app.messageData.replace(/zmdi zmdi-|fa fa-|flaticon-/, '');
            app.addHistory();
            $g('#icon-upload-dialog').modal('hide');
            break;
        case 'selectMarker' :
            fontBtn.value = IMAGE_PATH+app.messageData.path;
            $g(fontBtn).trigger('input change')
            $g('#uploader-modal').modal('hide');
        case 'reselectImage' :
            var img = app.editor.document.getElementById(app.editor.app.edit);
            app.edit.image = IMAGE_PATH+app.messageData.path;
            img = img.querySelector('img');
            img.src = app.messageData.url;
            fontBtn.value = app.edit.image;
            app.addHistory();
            $g('#uploader-modal').modal('hide');
            break;
        case 'selectImageCaption' :
            app.setValue(IMAGE_PATH+app.messageData.path, 'image');
            fontBtn.value = IMAGE_PATH+app.messageData.path;
            app.sectionRules();
            app.addHistory();
            $g('#uploader-modal').modal('hide');
            break;
        case 'image':
            var group = fontBtn.dataset.group,
                option = fontBtn.dataset.option,
                action  = fontBtn.dataset.action;
            app.setValue(IMAGE_PATH+app.messageData.path, 'background', 'image', 'image');
            if (app.edit.type) {
                app.setValue(IMAGE_PATH+app.messageData.path, 'image', 'image');
            }
            app.edit[app.view].background.type = 'image';
            app[action]();
            fontBtn.value = IMAGE_PATH+app.messageData.path;
            $g('#uploader-modal').modal('hide');
            app.addHistory();
            break;
        case 'ckeImage':
            $g('.cke-upload-image').val(JUri+IMAGE_PATH+app.messageData.path);
            $g('#add-cke-image').addClass('active-button');
            $g('#uploader-modal').modal('hide');
            break;
        case 'attachmentFileField':
            var size = fontBtn.dataset.size * 1000,
                array = app.messageData.path.split('/'),
                types = fontBtn.dataset.types.replace(/ /g, '').split(',');
            if (size < app.messageData.size || types.indexOf(app.messageData.ext) == -1) {
                app.showNotice(gridboxLanguage['FILE_COULD_NOT_UPLOADED'], 'ba-alert');
                return false;
            }
            fontBtn.dataset.value = IMAGE_PATH+app.messageData.path;
            fontBtn.value = array[array.length - 1];
            $g(fontBtn).trigger('input');
            $g('#uploader-modal').modal('hide');
            break;
        case 'imageField':
            var array = app.messageData.path.split('/');
            fontBtn.value = array[array.length - 1];
            fontBtn.dataset.value = IMAGE_PATH+app.messageData.path;
            $g(fontBtn).trigger('input');
            $g('#uploader-modal').modal('hide');
            break;
        case 'shareImage':
            fontBtn.value = IMAGE_PATH+app.messageData.path;
            $g(fontBtn).trigger('input');
            $g('#uploader-modal').modal('hide');
            break;
        case 'introImage':
            var img = IMAGE_PATH+app.messageData.path,
                meta = app.editor.document.querySelector('meta[property="og:image"]'),
                intro = app.editor.document.querySelector('.ba-item-post-intro .intro-post-image');
            if (intro) {
                intro.style.backgroundImage = 'url('+JUri+img+')';
            }
            $g('.blog-post-editor-img-thumbnail').css({
                'background-image': 'url('+JUri+img+')'
            }).removeClass('empty-intro-image');
            $g('.intro-image').val(img).prev().css({
                'background-image': 'url('+JUri+img+')'
            });
            $g('#uploader-modal').modal('hide');
            meta.content = JUri+img;
            break;
        case 'videoSource':
            var file = IMAGE_PATH+app.messageData.path,
                array = app.messageData.path.split('/'),
                ext = file.split('.');
            ext = ext[ext.length - 1];
            if (ext == 'mp4') {
                fontBtn.value = array[array.length - 1];
                fontBtn.dataset.value = file;
                $g(fontBtn).trigger('change');
            } else {
                app.showNotice(gridboxLanguage['NOT_SUPPORTED_FILE'], 'ba-alert');
            }
            $g('#uploader-modal').modal('hide');
            break;
        case 'pluginVideoSource':
            var file = IMAGE_PATH+app.messageData.path,
                ext = file.split('.');
            ext = ext[ext.length - 1];
            if (ext == 'mp4') {
                fontBtn.value = file;
                $g(fontBtn).trigger('change');
            } else {
                app.showNotice(gridboxLanguage['NOT_SUPPORTED_FILE'], 'ba-alert');
            }
            $g('#uploader-modal').modal('hide');
            break;
        case 'favicon' :
            var img = IMAGE_PATH+app.messageData.path,
                ext = img.split('.');
            ext = ext[ext.length - 1];
            if (ext == 'ico') {
                $g('input.favicon').val(img);
            } else {
                app.showNotice($g('input.favicon-error').val(), 'ba-alert');
            }
            $g('#uploader-modal').modal('hide');
            break;
        case 'LibraryImage':
            $g('.library-item-image').val(IMAGE_PATH+app.messageData.path);
            $g('#uploader-modal').modal('hide');
            break;
    }
}

app.modules.messageListener = true;
app.messageListener();