/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.initAddToCart = function(obj, key){
    let parent = $g('#'+key),
        variations = parent.find('.ba-add-to-cart-variation'),
        extra = parent.find('.ba-add-to-cart-extra-option');
    app.addToCart.getProduct(obj, parent);
    app.addToCart.updateExtraPrice(obj, extra);
    if (themeData.page.view != 'gridbox') {
        for (let ind in obj.productData.variations) {
            if (obj.productData.variations[ind].default) {
                obj.defaultProduct = obj.productData.variations[ind];
                break;
            }
        }
        if (obj.defaultProduct && !obj.product) {
            app.addToCart.updateVariationData(parent, obj, obj.defaultProduct.variation, true);
        }
    }
    parent.find('.ba-add-to-wishlist').on('click', function(){
        app.addToCart.showExtraNotice(extra);
        if (obj.product && !this.clicked && !document.querySelector('.ba-variation-notice')) {
            let $this = this;
            this.clicked = true;
            app.fetch(JUri+'index.php?option=com_gridbox&view=editor&task=store.addProductToWishlist', {
                id: obj.productData.data.product_id,
                variation: obj.product.variation ? obj.product.variation : '',
                extra_options: JSON.stringify(app.addToCart.extra_options.options)
            }).then(function(text){
                let object = JSON.parse(text),
                    str = '';
                if (('status' in object) && !object.status && object.message) {
                    app.showNotice(object.message, 'ba-alert');
                } else {
                    if (app.wishlist) {
                        app.wishlist.updateWishlist();
                    }
                    app.addToCart.clear(obj, parent);
                    $this.clicked = false;
                    if (object.images.length) {
                        object.image = object.images[0];
                    }
                    if (object.image && object.image.indexOf('balbooa.com') == -1) {
                        object.image = JUri+object.image;
                    }
                    str = '<span class="ba-product-notice-message">';
                    if (object.image) {
                        str += '<span class="ba-product-notice-image-wrapper"><img src="'+object.image+'"></span>';
                    }
                    str += '<span class="ba-product-notice-text-wrapper">'+object.title+
                        ' '+gridboxLanguage['ADDED_TO_WISHLIST']+'</span></span>';
                    app.showNotice(str, 'ba-product-notice');
                }
            });
        } else if (!obj.product && !this.closest('.ba-add-to-cart-button-wrapper').classList.contains('disabled')) {
            app.addToCart.showVariationsNotice(variations);
        }
    });
    parent.find('.ba-add-to-cart-button-wrapper a').on('click', function(event){
        event.preventDefault();
        app.addToCart.showExtraNotice(extra);
        if (obj.product && obj.product.stock != '0' && !this.clicked && !document.querySelector('.ba-variation-notice')) {
            let $this = this,
                qty = document.querySelector('.ba-add-to-cart-quantity')
                quantity = qty ? qty.querySelector('input').value : 1;
            this.clicked = true;
            app.fetch(JUri+'index.php?option=com_gridbox&view=editor&task=store.addProductToCart', {
                id: obj.productData.data.product_id,
                variation: obj.product.variation ? obj.product.variation : '',
                extra_options: JSON.stringify(app.addToCart.extra_options.options),
                quantity: quantity
            }).then(function(text){
                if (app.storeCart) {
                    app.storeCart.updateCartTotal();
                    $g('.ba-item-cart a').first().trigger('click');
                }
                app.addToCart.clear(obj, parent);
                $this.clicked = false;
            });
        } else if (!obj.product && !this.closest('.ba-add-to-cart-button-wrapper').classList.contains('disabled')) {
            app.addToCart.showVariationsNotice(variations);
        }
    });
    parent.find('.ba-add-to-cart-quantity i[data-action]').on('click', function(){
        if (!obj.product && !this.closest('.ba-add-to-cart-button-wrapper').classList.contains('disabled')) {
            app.addToCart.showVariationsNotice(variations);
        }
        if (!obj.product) {
            return false;
        }
        if (!this.input) {
            this.input = this.closest('.ba-add-to-cart-quantity').querySelector('input');
        }
        let value = this.dataset.action == '+' ? this.input.value * 1 + 1 : this.input.value * 1 - 1,
            $this = this;
        if (value > 0 && (obj.product.stock == '' || value <= obj.product.stock * 1)) {
            this.input.value = value;
        } else if (obj.product.stock != '' && obj.product.stock != '0' && value > obj.product.stock * 1) {            
            if (!this.notice) {
                this.notice =  document.createElement('span');
                this.notice.className = 'ba-variation-notice';
                this.notice.textContent = gridboxLanguage['IN_STOCK']+' '+obj.product.stock;
                this.closest('.ba-add-to-cart-quantity').append(this.notice);
            }
            clearTimeout(this.delay);
            this.delay = setTimeout(function(){
                $this.notice.remove();
                $this.notice = null;
            }, 3000);
        }
    });
    parent.find('.ba-add-to-cart-quantity input').on('input', function(){
        let match = this.value.match(/\d+/),
            value = this.value;
        if (!obj.product) {
            value = 1;
        } else if (!match) {
            value = '';
        } else if (match) {
            value = match[0] * 1;
        }
        if (obj.product && obj.product.stock == '0' && value > 0) {
            value = 1;
        } else if (obj.product && obj.product.stock != '' && value > obj.product.stock * 1) {
            value = obj.product.stock * 1;
        }
        if (String(value) != this.value) {
            this.value = value;
        }
    });
    variations.find('.ba-add-to-cart-row-value:not([data-type="dropdown"]) > span').on('click', function(){
        app.addToCart.variationAction(this, parent, variations, obj);
    });
    variations.find('.ba-add-to-cart-row-value[data-type="dropdown"]').on('customAction', function(){
        app.addToCart.variationAction(this.querySelector('li.selected'), parent, variations, obj);
    });
    variations.find('.ba-add-to-cart-row-value').on('change', 'input', function(){
        app.addToCart.variationAction(this, parent, variations, obj);
    });
    extra.find('.ba-add-to-cart-row-value:not([data-type="dropdown"]) > span').on('click', function(){
        app.addToCart.extraAction(this, parent, extra, obj);
    });
    extra.find('.ba-add-to-cart-row-value[data-type="dropdown"]').on('customAction', function(){
        app.addToCart.extraAction(this.querySelector('li.selected'), parent, extra, obj);
    });
    extra.find('.ba-add-to-cart-row-value').on('change', 'input', function(){
        app.addToCart.extraAction(this, parent, extra, obj);
    });
    let select = localStorage.getItem('select-options')
    if (select) {
        localStorage.removeItem('select-options');
        app.addToCart.showVariationsNotice(variations, select);
        app.addToCart.showExtraNotice(extra, select);
    }
    initItems();
}

app.addToCart = {
    extra_options: {
        options:{},
        price: 0
    },
    showExtraNotice: function(extra, text){
        extra.each(function(){
            if (this.dataset.required == 1 && !this.querySelector('.active') && !this.querySelector('.ba-variation-notice')) {
                let span = document.createElement('span');
                span.className = 'ba-variation-notice';
                span.textContent = text ? text : gridboxLanguage['PLEASE_SELECT_OPTION'];
                this.querySelector('.ba-add-to-cart-row-label').append(span)
            }
        });
    },
    getProduct: function(obj, parent){
        let variations = parent.find('.ba-add-to-cart-variation');
        if (themeData.page.view == 'gridbox') {
            obj.product = null;
        } else if (variations.length) {
            let keys = [],
                key = '';
            variations.each(function(){
                this.querySelectorAll('.active, .selected').forEach(function($this){
                    keys.push($this.dataset.value);
                });
            });
            key = keys.join('+');
            obj.product = obj.productData.variations[key] ? obj.productData.variations[key] : null;
        } else {
            obj.product = obj.productData.data;
        }
    },
    showVariationsNotice: function(variations, text){
        variations.each(function(){
            if (!this.querySelector('.active') && !this.querySelector('.ba-variation-notice')) {
                let span = document.createElement('span');
                span.className = 'ba-variation-notice';
                span.textContent = text ? text : gridboxLanguage['PLEASE_SELECT_OPTION'];
                this.querySelector('.ba-add-to-cart-row-label').append(span)
            }
        });
    },
    clear: function(obj, parent){
        let variations = parent.find('.ba-add-to-cart-variation').each(function(){
                let variation = $g(this);
                variation.find('.active').removeClass('active');
                variation.find('.selected').removeClass('selected');
                variation.find('.disabled').removeClass('disabled');
                variation.find('input[type="radio"], input[type="checkbox"]').prop('checked', false);
                variation.find('.ba-custom-select').each(function(){
                    this.querySelector('input[type="hidden"]').value = '';
                    this.querySelector('input[type="text"]').value = gridboxLanguage['SELECT'];
                });
            }),
            extra = parent.find('.ba-add-to-cart-extra-option').each(function(){
                let variation = $g(this);
                variation.find('.active').removeClass('active');
                variation.find('.selected').removeClass('selected');
                variation.find('.disabled').removeClass('disabled');
                variation.find('input[type="radio"], input[type="checkbox"]').prop('checked', false);
                variation.find('.ba-custom-select').each(function(){
                    this.querySelector('input[type="hidden"]').value = '';
                    this.querySelector('input[type="text"]').value = gridboxLanguage['SELECT'];
                });
            });
        this.extra_options.options = {};
        this.extra_options.price = 0;
        if (obj.defaultProduct) {
            app.addToCart.updateVariationData(parent, obj, obj.defaultProduct.variation, true);
        } else {
            app.addToCart.getProduct(obj, parent);
            parent.find('.ba-add-to-cart-button-wrapper').removeClass('disabled');
            $g('.ba-item-product-slideshow').each(function(){
                app.addToCart.initSlideshow(app.items[this.id], 'original', this);
            });
            $g('.ba-item-product-gallery').each(function(){
                app.addToCart.initGallery(app.items[this.id], 'original', this);
            });
            let stock = obj.productData.data.stock == '0' ? gridboxLanguage['OUT_OF_STOCK'] : obj.productData.data.stock,
                data = {
                    price: obj.productData.data.price,
                    sale_price: obj.productData.data.sale_price,
                    button: variations.length == 0 && extra.length == 0 ? obj['button-label'] : gridboxLanguage['SELECT_AN_OPTION'],
                    stock: stock,
                    sku: obj.productData.data.sku
                }
            app.addToCart.setCartValues(parent, data, obj);
            app.addToCart.clearSearch(obj);
        }
    },
    clearSearch: function(obj){
        let search = window.location.search,
            url = window.location.href;
        if (window.location.search) {
            for (let variation in obj.productData.variations) {
                if (window.location.search.indexOf(obj.productData.variations[variation].url) != -1) {
                    url = url.replace(obj.productData.variations[variation].url, '');
                    search = search.replace(obj.productData.variations[variation].url, '');
                    if (search == '?') {
                        url = url.replace('?', '');
                    }
                    window.history.replaceState(null, null, url);
                    break;
                }
            }
        }
    },
    updateExtraPrice: function(obj, extra){
        this.extra_options.options = {};
        this.extra_options.price = 0;
        extra.find('.active[data-value], .selected[data-value], input[type="radio"], input[type="checkbox"]').each(function(){
            if (this.localName != 'input' || this.checked) {
                let id = this.closest('.ba-add-to-cart-extra-option').dataset.ind,
                    value = this.localName == 'input' ? this.value : this.dataset.value,
                    option = obj.productData.data.extra_options[id].items[value];
                app.addToCart.extra_options.options[value] = {
                    price: option.price,
                    field_id: id
                };
                if (option.price) {
                    app.addToCart.extra_options.price += option.price * 1;
                }
            }
        });
    },
    extraAction: function($this, parent, extra, obj){
        if (themeData.page.view == 'gridbox') {
            return false;
        }
        let variations = parent.find('.ba-add-to-cart-variation'),
            variation = $g($this).closest('.ba-add-to-cart-extra-option'),
            flag = true;
        variation.find('.active').removeClass('active');
        variation.find('.ba-variation-notice').remove();
        $this.classList.add('active');
        extra.find('.disabled').removeClass('disabled');
        extra.each(function(){
            if (flag && this.dataset.required == 1 && !this.querySelector('input[type="radio"], input[type="checkbox"]')) {
                flag = this.querySelector('.active[data-value], .selected[data-value]');
            } else if (flag && this.dataset.required == 1) {
                let checked = false;
                this.querySelectorAll('input').forEach(function(input){
                    if (!checked) {
                        checked = input.checked;
                    }
                });
                flag = checked;
            }
        });
        variations.each(function(){
            if (flag && !this.querySelector('input[type="radio"]')) {
                flag = this.querySelector('.active[data-value], .selected[data-value]');
            } else if (flag) {
                let checked = false;
                this.querySelectorAll('input').forEach(function(input){
                    if (!checked) {
                        checked = input.checked;
                    }
                });
                flag = checked;
            }
        });
        this.updateExtraPrice(obj, extra);
        parent.find('.ba-add-to-cart-buttons-wrapper a').text(flag ? obj['button-label'] : gridboxLanguage['SELECT_AN_OPTION']);
        this.setCartPrices(parent, obj);
    },
    variationAction: function($this, parent, variations, obj){
        if (themeData.page.view == 'gridbox') {
            return false;
        }
        let variation = $g($this).closest('.ba-add-to-cart-variation'),
            keys = [],
            key = '';
        variation.find('.active').removeClass('active');
        variation.find('.ba-variation-notice').remove();
        $this.classList.add('active');
        variations.find('.disabled').removeClass('disabled');
        variations.find('.active[data-value], .selected[data-value]').each(function(){
            keys.push(this.dataset.value);
        });
        variations.find('input[type="radio"]').each(function(){
            if (this.checked) {
                keys.push(this.value);
            }
        });
        if (keys.length) {
            key = keys.join('+');
            app.addToCart.updateVariationData(parent, obj, key);
        }
    },
    updateVariationData: function(parent, obj, key, setActive){
        let keys = key.split('+'),
            img = 'original';
        keys.forEach(function(value){
            if (obj.productData.images[value].length) {
                img = value;
            }
        });
        app.addToCart.updateProductItems(obj.productData, img);
        if (setActive) {
            keys.forEach(function(ind){
                parent.find('[data-value="'+ind+'"]').each(function(){
                    this.classList.add('active');
                    if (this.localName == 'li') {
                        this.classList.add('selected');
                        let text = this.textContent.trim(),
                            select = this.closest('.ba-custom-select');
                        select.querySelector('input[type="text"]').value = text;
                        select.querySelector('input[type="hidden"]').value = ind;
                    }
                });
                parent.find('input[value="'+ind+'"]').each(function(){
                    this.checked = true;
                })
            });
        }
        if (obj.productData.variations[key]) {
            obj.product = obj.productData.variations[key];
            app.addToCart.clearSearch(obj);
            let stock = obj.product.stock == '0' ? gridboxLanguage['OUT_OF_STOCK'] : obj.product.stock,
                url = window.location.href,
                extra = parent.find('.ba-add-to-cart-extra-option'),
                flag = true,
                data = {
                    price: obj.product.price,
                    sale_price: obj.product.sale_price,
                    button: obj['button-label'],
                    stock: stock,
                    sku: obj.product.sku
                };
            extra.each(function(){
                if (flag && this.dataset.required == 1 && !this.querySelector('input[type="radio"], input[type="checkbox"]')) {
                    flag = this.querySelector('.active[data-value], .selected[data-value]');
                } else if (flag && this.dataset.required == 1) {
                    let checked = false;
                    this.querySelectorAll('input').forEach(function(input){
                        if (!checked) {
                            checked = input.checked;
                        }
                    });
                    flag = checked;
                }
            });
            if (!flag) {
                data.button = gridboxLanguage['SELECT_AN_OPTION'];
            }
            if (window.location.hash) {
                url = url.replace(window.location.hash, '');
            }
            url += window.location.search ? '&' : '?';
            url += obj.productData.variations[key].url;
            if (window.location.hash) {
                url += window.location.hash;
            }
            window.history.replaceState(null, null, url);
            app.addToCart.setCartValues(parent, data, obj);
            if (obj.product.stock == '0') {
                parent.find('.ba-add-to-cart-button-wrapper').addClass('disabled').find('a').text(gridboxLanguage['OUT_OF_STOCK']);
            } else {
                parent.find('.ba-add-to-cart-button-wrapper').removeClass('disabled').find('a').text(data.button);
            }
        } else {
            obj.product = null;
        }
    },
    setCartValues: function(parent, data, obj){
        parent.find('.ba-add-to-cart-quantity input').val(1);
        parent.find('.ba-add-to-cart-sku .ba-add-to-cart-row-value').text(data.sku);
        parent.find('.ba-add-to-cart-stock .ba-add-to-cart-row-value').text(data.stock);
        parent.find('.ba-add-to-cart-buttons-wrapper a').text(data.button);
        app.addToCart.setCartPrices(parent, obj);
    },
    setCartPrices: function(parent, obj){
        let price = +(obj.product ? obj.product.price : obj.productData.data.price) + this.extra_options.price,
            sale_price = obj.product ? obj.product.sale_price : obj.productData.data.sale_price,
            thousand = obj.productData.thousand,
            separator = obj.productData.separator,
            decimals = obj.productData.decimals;
        parent.find('.ba-add-to-cart-price .ba-add-to-cart-price-wrapper').each(function(){
            let div = this.closest('.ba-add-to-cart-price'),
                clone = this.cloneNode(true);
            price = app.renderPrice(price, thousand, separator, decimals);
            div.innerHTML = '';
            if (sale_price != '') {
                let saleClone = this.cloneNode(true);
                sale_price = sale_price * 1 + app.addToCart.extra_options.price
                sale_price = app.renderPrice(sale_price, thousand, separator, decimals);
                saleClone.classList.remove('ba-add-to-cart-price-wrapper');
                saleClone.classList.add('ba-add-to-cart-sale-price-wrapper');
                saleClone.querySelector('.ba-add-to-cart-price-value').textContent = sale_price;
                div.append(saleClone);
            }
            clone.querySelector('.ba-add-to-cart-price-value').textContent = price;
            div.append(clone);
        });
    },
    getSlideshowDefault: function(object, $this){
        if (!object.images) {
            let wrapper = $this.querySelector('ul.ba-slideshow'),
                key = 'original';
            object.images = {
                key: 'original',
                original: []
            };
            if (wrapper.dataset.original) {
                object.images.original = JSON.parse(wrapper.dataset.original);
                key = wrapper.dataset.variation;
                object.images.key = key;
                object.images[key] = [];
            }
            $this.querySelectorAll('li .ba-slideshow-img').forEach(function(img){
                object.images[key].push('url('+img.dataset.src+')');
            });
        }
    },
    initSlideshow: function(object, key, $this){
        app.addToCart.getSlideshowDefault(object, $this);
        if (object.images[key] != object.images[object.images.key]) {
            object.images.key = key;
            let style = html = '';
            object.images[key].forEach(function(image, i){
                style += '--thumbnails-dots-image-'+i+': '+image+';';
                html += '<li class="item'+(i == 0 ? ' active' : '');
                html += '"><div class="ba-slideshow-img" style="background-image: '+image+';"></div></li>';
            });
            $this.querySelector('ul.ba-slideshow .slideshow-content').innerHTML = html;
            $this.querySelector('ul.ba-slideshow .ba-slideshow-dots').setAttribute('style', style);
            app.initslideshow(object, $this.id);
        }
    },
    getGalleryDefault: function(object, $this){
        if (!object.images) {
            let wrapper = $this.querySelector('.instagram-wrapper'),
                key = 'original';
            object.images = {
                key: 'original',
                original: []
            };
            if (wrapper.dataset.original) {
                object.images.original = JSON.parse(wrapper.dataset.original);
                key = wrapper.dataset.variation;
                object.images.key = key;
                object.images[key] = [];
            }
            $this.querySelectorAll('.ba-instagram-image img').forEach(function(img){
                object.images[key].push(img.src ? img.src : img.dataset.gridboxLazyloadSrc);
            });
        }
    },
    initGallery:function(object, key, $this){
        app.addToCart.getGalleryDefault(object, $this);
        if (object.images[key] != object.images[object.images.key]) {
            object.images.key = key;
            let html = '';
            object.images[key].forEach(function(image, i){
                html += '<div class="ba-instagram-image" style="background-image: url('+image+');"><img alt="" src="'+
                image+'"><div class="ba-simple-gallery-image"></div></div>';
            });
            $this.querySelector('.instagram-wrapper').innerHTML = html;
            app.initSimpleGallery(object, $this.id);
            if ($this.querySelector('.instagram-wrapper').classList.contains('simple-gallery-masonry-layout')) {
                setGalleryMasonryHeight($this.id);
            }
        }
    },
    updateProductItems: function(product, key){
        let defs = {
            slideshow: ['getSlideshowDefault', 'initSlideshow'],
            gallery: ['getGalleryDefault', 'initGallery']
        }
        $g('.ba-item-product-slideshow, .ba-item-product-gallery').each(function(){
            let def = this.classList.contains('ba-item-product-slideshow') ? 'slideshow' : 'gallery';
                object = app.items[this.id];
            app.addToCart[defs[def][0]](object, this);
            if (key != 'original' && !object.images[key]) {
                object.images[key] = [];
                product.images[key].forEach(function(image){
                    image = image.indexOf('balbooa.com') == -1 ? (JUri+image) : image;
                    object.images[key].push(def == 'slideshow' ? 'url('+image+')' : image);
                });
            }
            app.addToCart[defs[def][1]](object, key, this);
        });
    }
}

if (app.modules.initAddToCart) {
    app.initAddToCart(app.modules.initAddToCart.data, app.modules.initAddToCart.selector);
}