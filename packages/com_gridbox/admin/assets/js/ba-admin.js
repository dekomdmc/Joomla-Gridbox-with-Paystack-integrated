/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

if (getCookie('gridbox-sidebar') == 'visible') {
    document.body.classList.add('visible-sidebar');
    setTimeout(function(){
        document.body.dataset.sidebar = 'hidden';
    }, 500);
}

var app = {
        currentOrder: null,
        cart: {},
        objects: {
            productoptions: {
                title: 'Title',
                image: '',
                color: '#1da6f4',
                key: 0
            },
            statuses: {
                title: "Status",
                color: "#1da6f4",
                key: 0
            },
            taxes: {
                type: "tax",
                title: "Tax",
                rate: ''
            }
        },
        modules:{},
        cke:{},
        _: function(key){
            if (gridboxLanguage && gridboxLanguage[key]) {
                return gridboxLanguage[key];
            } else {
                return key;
            }
        },
        createChart: function(array){
            if (!app.chart) {
                app.chart = document.querySelector('.ba-statistics-chart');
            }
            app.chart.innerHTML = '';
            app.chart.classList.remove('ba-chart-loaded');
            if (array.length == 1) {
                app.chart.classList.add('ba-chart-single-point');
            } else {
                app.chart.classList.remove('ba-chart-single-point');
            }
            let chart = new liteChart(),
                labels = [],
                values = [];
            array.forEach(function(obj){
                labels.push(obj.label)
                values.push(obj.value);
            })
            chart.setLabels(labels);
            chart.addLegend({"values": values});
            chart.inject(app.chart);
            chart.draw();
        },
        statisticFilter: function(){
            makeFetchRequest('index.php?option=com_gridbox&task=orders.getStatistic', {
                type: app.statistic.type,
                date: app.statistic.value
            }).then(function(json){
                app.createChart(json.chart);
                $g('.ba-store-statistic-count-wrapper').each(function(){
                    this.querySelector('.ba-store-statistic-count').textContent = json.counts[this.dataset.type];
                });
                $g('.ba-store-statistic-total-price .ba-store-statistic-price').text(app.renderPrice(json.total));
                let parent = $g('.ba-store-statistic-products').empty();
                json.products.forEach(function(product) {
                    let div = document.querySelector('.ba-store-statistic-product-template').content.cloneNode(true);
                    if (product.image) {
                        div.querySelector('.ba-store-statistic-product-image').style.backgroundImage = 'url('+product.image+')';
                    } else {
                        div.querySelector('.ba-store-statistic-product-image').remove();
                    }
                    if (product.info) {
                        div.querySelector('.ba-store-statistic-product-info').innerHTML = product.info;
                    } else {
                        div.querySelector('.ba-store-statistic-product-info').remove();
                    }
                    if (product.link) {
                        div.querySelector('a').href = product.link;
                    } else {
                        div.querySelector('a').remove();
                    }
                    div.querySelector('.ba-store-statistic-product-title').textContent = product.title;
                    div.querySelector('.ba-store-statistic-price').textContent = app.renderPrice(product.price);
                    div.querySelector('.ba-store-statistic-product-sales-count').textContent = product.quantity;
                    parent.append(div);
                });
            })
        },
        prepareEmptyCart: function(modal){
            app.cart = {
                modal: modal,
                products:{},
                promo: null,
                shipping: null,
                subtotal: 0,
                tax: 0,
                discount: 0,
                country: '',
                region: '',
                total: 0
            };
            modal.find('.ba-options-group-toolbar label').not('.add-order-product').addClass('disabled');
        },
        getProductExtraOption: function(key, obj, quantity, symbol, position){
            let price = obj.price != '' ? app.renderPrice(obj.price * quantity, symbol, position) : '',
                str = '<div class="ba-product-extra-option" data-key="'+key+'">';
            str += '<div class="ba-product-delete-extra-option"><i class="zmdi zmdi-delete"></i></div>';
            str += '<div class="ba-product-extra-option-image"></div>';
            str += '<div class="ba-cart-product-extra-option-values">';
            str += '<span class="ba-cart-product-extra-option-value">'+obj.value+'</span>';
            str += '<span class="ba-cart-product-extra-option-price" data-price="'+obj.price+'">'+price+'</span></div>';
            str += '</div>';

            return str;
        },
        getProductExtraRow: function(ind, obj, quantity, symbol, position){
            let str = '<div class="ba-product-extra-option-row" data-ind="'+ind+'">';
            str += '<div class="ba-product-extra-option">';
            str += '<div class="ba-product-delete-extra-option"></div>';
            str += '<div class="ba-product-extra-option-image"></div>';
            str += '<div class="ba-cart-product-extra-option-title">'+obj.title+'</div>';
            str += '</div>';
            for (let key in obj.values) {
                str += app.getProductExtraOption(key, obj.values[key], quantity, symbol, position);
            }
            str += '</div>';

            return str;
        },
        getProductSortingHTML: function(obj, quantity, symbol, position){
            let str = '<div class="sorting-item" data-id="'+obj.id+'"'+(obj.variation ? 'data-variation="'+obj.variation+'"' : ''),
                extraPrice = obj.extra_options.price ? obj.extra_options.price * quantity : 0;
                price = app.renderPrice(obj.price * quantity + extraPrice, symbol, position);
            str += '><div class="ba-order-product-wrapper"><div class="sorting-checkbox">';
            str += '<label class="ba-checkbox ba-hide-checkbox"><input type="checkbox" name="product" value="';
            str += obj.id+'"'+(obj.variation ? 'data-variation="'+obj.variation+'"' : '')+'><span></span></label></div>';
            if (obj.image) {
                str += '<div class="sorting-image"><img src="'+(obj.image.indexOf('balbooa.com') == -1 ? JUri : '')+obj.image+'"></div>';
            }
            str += '<div class="sorting-title"><span class="product-title">'+obj.title+'</span>';
            str += (obj.info ? '<span class="product-info">'+obj.info+'</span>' : '');
            str += '</div>';
            if (obj.product_type != 'digital') {
                str += '<div class="sorting-quantity"><input type="number" value="'+quantity+'" data-id="'+obj.id+'"></div>';
            }
            str += '<div class="ba-cart-product-price-cell">';
            if (obj.sale_price !== '') {
                str += '<span class="ba-cart-sale-price-wrapper"><span class="ba-cart-price-value">'+price+'</span></span>';
            }
            str += '<span class="ba-cart-price-wrapper "><span class="ba-cart-price-value">';
            if (obj.sale_price !== '') {
                price = app.renderPrice(obj.sale_price * quantity + extraPrice, symbol, position);
            }
            str += price+'</span></span></div></div>';
            str += '<div class="ba-product-extra-options">';
            if (obj.extra_options && obj.extra_options.items) {
                for (let ind in obj.extra_options.items) {
                    str += app.getProductExtraRow(ind, obj.extra_options.items[ind], quantity, symbol, position);
                }
            }
            str += '</div>';
            str += '</div>';

            return str;
        },
        checkPromoSales: function(promo, product){
            return promo.disable_sales == 0 || product.sale_price === '';
        },
        checkPromoCode: function(promo, product){
            let valid = false;
            if (promo.applies_to == '*') {
                valid = this.checkPromoSales(promo, product);
            } else if (promo.applies_to == 'product') {
                for (let i in promo.map) {
                    valid = promo.map[i].id == product.id && this.checkPromoSales(promo, product);
                    if (valid) {
                        break;
                    }
                }
            } else {
                for (let i in promo.map) {
                    valid = product.categories.indexOf(promo.map[i].id) != -1 && this.checkPromoSales(promo, product);
                    if (valid) {
                        break;
                    }
                }
            }

            return valid;
        },
        checkProductTaxMap: function(product, categories){
            let valid = false;
            for (let i = 0; i < categories.length; i++) {
                valid = product.categories.indexOf(categories[i]) != -1;
                if (valid) {
                    break;
                }
            }

            return valid;
        },
        getTaxRegion: function(regions){
            let result = null;
            for (let i = 0; i < regions.length; i++) {
                if (regions[i].state_id == app.cart.region) {
                    result = regions[i];
                    break;
                }
            }

            return result;
        },
        calculateProductTax: function(product, price, country, region, category){
            let obj = null,
                array = category ? app.taxRates.categories : app.taxRates.empty;
            for (let i = 0; i < array.length; i++) {
                let tax = array[i],
                    count = country ? tax.country_id == app.cart.country : true,
                    cat = category ? app.checkProductTaxMap(product, tax.categories) : true,
                    reg = region ? app.getTaxRegion(tax.regions) : true,
                    rate = 0;
                if (count && cat && reg) {
                    rate = reg.rate ? reg.rate : tax.rate;
                    obj = {
                        key: tax.key,
                        title: tax.title,
                        rate: rate,
                        amount: app.store.tax.mode == 'excl' ? price * (rate / 100) : price - price / (rate / 100 + 1)
                    };
                    break;
                }
            }
            if (!obj && country && region && category) {
                obj = app.calculateProductTax(product, price, true, false, true);
            } else if (!obj && country && !region && category) {
                obj = app.calculateProductTax(product, price, true, true, false);
            } else if (!obj && country && region && !category) {
                obj = app.calculateProductTax(product, price, true, false, false);
            } else if (!obj && country && !region && !category) {
                obj = app.calculateProductTax(product, price, false, false, true);
            } else if (!obj && !country && !region && category) {
                obj = app.calculateProductTax(product, price, false, false, false);
            }

            return obj;
        },
        getStoreShippingTax: function(country, region){
            let obj = null;
            for (let i = 0; i < app.store.tax.rates.length; i++) {
                let rate = app.store.tax.rates[i],
                    count = country ? rate.country_id == app.cart.country : true,
                    reg = region ? app.getTaxRegion(rate.regions) : true;
                if (rate.shipping && count && reg) {
                    obj = {};
                    obj.key = i;
                    obj.title = rate.title;
                    obj.rate = rate.rate;
                    obj.amount = rate.rate / 100;
                    break;
                }
            }

            if (!obj && country && region) {
                obj = app.getStoreShippingTax(true, false);
            } else if (!obj && country && !region) {
                obj = app.getStoreShippingTax(false, false);
            }

            return obj;
        },
        calculateOrder: function(){
            if (!app.taxRates) {
                app.taxRates = {
                    categories: [],
                    empty: []
                }
                for (let i = 0; i < app.store.tax.rates.length; i++) {
                    let rate = app.store.tax.rates[i];
                    rate.key = i;
                    if (rate.categories.length) {
                        app.taxRates.categories.push(rate);
                    } else {
                        app.taxRates.empty.push(rate);
                    }
                }
            }
            app.cart.total = app.cart.subtotal = app.cart.tax = app.cart.discount = 0;
            app.cart.validPromo = false;
            app.cart.country = app.cart.region = '';
            app.cart.taxes = {};
            app.cart.taxes.count = 0;
            app.cart.quantity = 0;
            app.cart.modal.find('.ba-options-group-element[data-type="country"] select').each(function(){
                app.cart[this.dataset.type] = this.value;
            });
            let mode = app.store.tax.mode,
                promoProducts = 0;
            for (let ind in app.cart.products) {
                let product = app.cart.products[ind];
                product.promo = app.cart.promo && this.checkPromoCode(app.cart.promo, product);
                if (product.promo) {
                    promoProducts++;
                }
            }
            for (let ind in app.cart.products) {
                let product = app.cart.products[ind],
                    price = (product.sale_price !== '' ? product.sale_price : product.price) * product.quantity;
                app.cart.quantity += product.quantity;
                if (product.extra_options.price) {
                    price += product.extra_options.price * product.quantity;
                }
                app.cart.subtotal += price;
                product.tax = app.calculateProductTax(product, price, true, true, true);
                if (product.promo) {
                    app.cart.validPromo = true;
                    let discount = app.cart.promo.discount;
                    discount = app.cart.promo.unit == '%' ? price * (discount / 100) : discount / promoProducts;
                    price -= discount;
                    app.cart.discount += discount;
                }
                product.net_price = price;
                if (product.tax) {
                    let amount = product.tax.amount,
                        rate = product.tax.rate;
                    if (product.promo) {
                        amount = mode == 'excl' ? price * (rate / 100) : price - price / (rate / 100 + 1);
                        product.tax.amount = amount;
                    }
                    app.cart.tax += amount;
                    product.net_price = mode == 'excl' ? price : price - amount;
                    if (!app.cart.taxes[product.tax.key]) {
                        app.cart.taxes[product.tax.key] = {};
                        app.cart.taxes[product.tax.key].title = product.tax.title;
                        app.cart.taxes[product.tax.key].rate = rate;
                        app.cart.taxes[product.tax.key].amount = amount;
                        app.cart.taxes[product.tax.key].net = product.net_price;
                        app.cart.taxes.count++;
                    } else {
                        app.cart.taxes[product.tax.key].amount += amount;
                        app.cart.taxes[product.tax.key].net += product.net_price;
                    }
                }
                app.cart.total += price;
            }
            let price = app.renderPrice(app.cart.subtotal);
            app.cart.modal.find('.order-subtotal-element .ba-cart-price-value').text(price);
            if (mode == 'incl') {
                let title = app._('INCLUDING_TAXES')+' ';
                price = app.renderPrice(app.cart.tax);
                if (app.cart.taxes.count == 1) {
                    for (let ind in app.cart.taxes) {
                        if (ind == 'count') {
                            continue;
                        }
                        title = app._('INCLUDES')+' '+app.cart.taxes[ind].rate+'%'+' '+app.cart.taxes[ind].title;
                    }
                }
                title += ' '+price;
                app.cart.modal.find('.order-tax-element label').text(title);
            } else if (mode == 'excl' && app.cart.taxes.count != 0) {
                app.cart.modal.find('.order-tax-element').remove();
                let html = '';
                for (let ind in app.cart.taxes) {
                    if (ind == 'count') {
                        continue;
                    }
                    app.cart.total += app.cart.taxes[ind].amount;
                    price = app.renderPrice(app.cart.taxes[ind].amount);
                    html += '<div class="ba-options-group-element order-tax-element" data-mode="excl">';
                    html += '<label class="ba-options-group-label">'+app.cart.taxes[ind].title;
                    html += '</label><span class="ba-cart-price-wrapper "><span class="ba-cart-price-value">';
                    html += price+'</span></span></div>';
                }
                app.cart.modal.find('.order-total-element').before(html);
            }
            price = app.renderPrice(app.cart.discount);
            app.cart.modal.find('.order-discount-element .ba-cart-price-value').text(price);
            if (app.cart.shipping) {
                let params = JSON.parse(app.cart.shipping.options),
                    shipping = app.getShippingPrice(params);
                app.cart.shipping.tax = null;
                if (params.type == 'free' || params.type == 'pickup') {
                    price = app._('FREE');
                } else {
                    app.cart.total += shipping;
                    price = app.renderPrice(shipping);
                }
                app.cart.shipping.price = shipping;
                app.cart.modal.find('.order-shipping-element .ba-cart-price-value').text(price);
                app.cart.modal.find('.order-shipping-tax-element').each(function(){
                    let shippingTax = app.getStoreShippingTax(true, true),
                        amount = 0;
                    if (shippingTax) {
                        amount = shippingTax.amount;
                        amount = mode == 'excl' ? shipping * amount : shipping - shipping / (amount + 1);
                        shippingTax.amount = amount;
                        app.cart.total += mode == 'excl' ? amount : 0;
                    }
                    app.cart.shipping.tax = shippingTax;
                    price = app.renderPrice(amount);
                    if (mode == 'excl') {
                        this.querySelector('.ba-cart-price-value').textContent = price;
                    } else {
                        let text = shippingTax ? app._('INCLUDES')+' '+shippingTax.title : app._('INCLUDING_TAXES');
                        this.querySelector('.ba-options-group-label').textContent = text+' '+price;
                    }
                });
            } else {
                price = app.renderPrice(0);
                app.cart.modal.find('.order-shipping-element, .order-shipping-tax-element').find('.ba-cart-price-value').text(price);
            }
            price = app.renderPrice(app.cart.total);
            app.cart.modal.find('.order-total-element .ba-cart-price-value').text(price);
        },
        getShippingPrice: function(params){
            let price = 0,
                object = params[params.type];
            if (params.type == 'flat') {
                price = object.price;
            } else if (params.type == 'weight-unit') {
                let weight = 0;
                for (let ind in app.cart.products) {
                    let product = app.cart.products[ind];
                    if (product.dimensions.weight) {
                        weight += product.dimensions.weight * product.quantity;
                    }
                }
                price = weight * object.price;
            } else if (params.type == 'product') {
                price = app.cart.quantity * params.product.price;
            } else if (params.type == 'prices' || params.type == 'weight') {
                let range = [],
                    obj = null,
                    unlimited = null;
                for (let ind in object.range) {
                    let value = object.range[ind];
                    if (value.rate === '') {
                        unlimited = value;
                    } else {
                        value.rate *= 1;
                        range.push(value);
                    }
                }
                range.sort(function(a, b){
                    if (a.rate == b.rate) {
                        return 0;
                    }

                    return (a.rate < b.rate) ? -1 : 1;
                });
                if (params.type == 'weight') {
                    netValue = 0;
                    for (let ind in app.cart.products) {
                        let product = app.cart.products[ind];
                        if (product.dimensions.weight) {
                            netValue += product.dimensions.weight * product.quantity;
                        }
                    }
                } else {
                    netValue = app.cart.total;
                }
                range.forEach(function(value){
                    if (netValue <= value.rate && obj === null) {
                        obj = value;
                    }
                });
                if (obj === null && unlimited) {
                    obj = unlimited;
                }
                if (obj) {
                    price = obj.price;
                }
            } else if (params.type == 'category') {
                for (let ind in app.cart.products) {
                    let product = app.cart.products[ind],
                        obj = null;
                    for (let ind in object.range) {
                        let value = object.range[ind];
                        for (let id in value.rate) {
                            if (product.categories.indexOf(value.rate[id]) != -1) {
                                obj = value.price;
                                break;
                            }
                        }
                        if (obj !== null) {
                            break;
                        }
                    }
                    if (obj !== null) {
                        price += obj * product.quantity;
                        continue;
                    }
                }
            }
            console.info(object, object.enabled, app.cart.total, object.free * 1, price)
            if (object && object.enabled && app.cart.total > object.free * 1) {
                price = 0;
            }

            return price;
        },
        decimalAdjust: function(type, value, exp){
            if (typeof exp === 'undefined') {
                exp = app.store.currency.decimals * -1;
            }
            if (typeof exp === 'undefined' || +exp === 0) {
                return Math[type](value);
            }
            value = +value;
            exp = +exp;
            if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
                return NaN;
            }
            value = value.toString().split('e');
            value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
            value = value.toString().split('e');

            return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
        },
        strrev: function(string){
            var ret = '', i = 0;
            for (i = string.length - 1; i >= 0; i--) {
                ret += string[i];
            }

            return ret;
        },
        renderPrice: function(value, symbol, position){
            value = String(app.decimalAdjust('round', value));
            let thousand = app.store.currency.thousand,
                separator = app.store.currency.separator,
                decimals = app.store.currency.decimals,
                priceArray = value.replace('-', '').trim().split('.'),
                priceThousand = priceArray[0],
                priceDecimal = priceArray[1] ? priceArray[1] : '',
                price = '';
            if (!symbol) {
                symbol = app.store.currency.symbol;
                position = app.store.currency.position;
            }
            if (priceThousand.length > 3 && thousand != '') {
                for (let i = 0; i < priceThousand.length; i++) {
                    if (i % 3 == 0 && i != 0) {
                        price += thousand;
                    }
                    price += priceThousand[priceThousand.length - 1 - i];
                }
                price = this.strrev(price);
            } else {
                price += priceThousand;
            }
            if (decimals != 0) {
                price += separator;
                for (let i = 0; i < decimals; i++) {
                    price += priceDecimal[i] ? priceDecimal[i] : '0';
                }
            }
            if (position == '') {
                price = symbol+' '+price;
            } else {
                price = price+' '+symbol;
            }

            return price;
        },
        setSubgroupChilds: function(div){
            let count = div.querySelectorAll('.ba-group-element:not([disabled])').length;
            div.style.setProperty('--subgroup-childs', count);
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
        toggleAlertTooltip: function(alert, $this, parent, key){
            if (alert && !$this.alertTooltip && !$this.closest('.hidden-condition-field')) {
                $this.alertTooltip = document.createElement('span');
                $this.alertTooltip.className = 'ba-alert-tooltip';
                $this.alertTooltip.textContent = app._(key);
                parent.classList.add('ba-alert');
                parent.appendChild($this.alertTooltip);
            } else if (alert && $this.alertTooltip) {
                $this.alertTooltip.textContent = app._(key);
            } else if (!alert && $this.alertTooltip) {
                formsApp.removeAlertTooltip($this);
            }
        },
        removeAlertTooltip: function($this){
            if (!$this.alertTooltip && $this.closest('.ba-alert')) {
                $this = $this.closest('.ba-alert');
            }
            if ($this.alertTooltip) {
                $this.alertTooltip.remove();
                $this.alertTooltip = null;
                $this.closest('.ba-alert').classList.remove('ba-alert');
            }
        },
        setMinicolors: function(){
            $g('body').on('click', 'input[data-type="color"]', function(){
                fontBtn = this;
                app.setMinicolorsColor(this.dataset.rgba);
                var rect = this.getBoundingClientRect();
                $g('#color-variables-dialog').css({
                    left : rect.left - 285,
                    top : rect.bottom - ((rect.bottom - rect.top) / 2) - 174
                }).removeClass('ba-right-position ba-bottom-position ba-top-position').modal();
            }).on('click', '.minicolors-swatch.minicolors-trigger', function(){
                $g(this).prev().trigger('click');
            }).on('input', 'input[data-type="color"]', app.inputColor);
            $g('.variables-color-picker').minicolors({
                opacity: true,
                theme: 'bootstrap',
                change: function(hex, opacity) {
                    var rgba = $g(this).minicolors('rgbaString');
                    fontBtn.value = hex;
                    $g('.variables-color-picker').closest('#color-picker-cell')
                        .find('.minicolors-opacity').val(opacity * 1);
                    fontBtn.dataset.rgba = rgba;
                    $g(fontBtn).trigger('minicolorsInput').next().find('.minicolors-swatch-color')
                        .css('background-color', rgba).closest('.minicolors').next()
                        .find('.minicolors-opacity').val(opacity * 1).removeAttr('readonly');
                }
            });
            $g('#color-variables-dialog').on('hide', function(){
                let $this = this;
                setTimeout(function(){
                    $this.style.setProperty('--color-variables-arrow-right', '');
                }, 300);
            });
            $g('#color-variables-dialog .minicolors-opacity').on('input', function(){
                var obj = {
                    color: $g('.variables-color-picker').val(),
                    opacity: this.value * 1,
                    update: false
                }
                $g('.variables-color-picker').minicolors('value', obj);
                fontBtn.dataset.rgba = $g('.variables-color-picker').minicolors('rgbaString');
                $g(fontBtn).trigger('minicolorsInput');
                if (fontBtn.localName == 'input') {
                    $g(fontBtn).next().find('.minicolors-swatch-color').css('background-color', fontBtn.dataset.rgba)
                        .closest('.minicolors').next().find('.minicolors-opacity').val(this.value);
                }
            });
            $g('.minicolors-opacity[data-callback]').on('input', function(){
                var input = $g(this).parent().prev().find('.minicolors-input')[0],
                    opacity = this.value * 1
                    value = input.dataset.rgba;
                if (this.value) {
                    var parts = value.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/),
                        rgba = 'rgba(';
                    if (parts) {
                        for (var i = 1; i < 4; i++) {
                            rgba += parts[i]+', ';
                        }
                    } else {
                        parts = value.match(/[^#]\w/g);
                        for (var i = 0; i < 3; i++) {
                            rgba += parseInt(parts[i], 16);
                            rgba += ', ';
                        }
                    }
                    rgba += this.value+')';
                    input.dataset.rgba = rgba;
                    $g(input).next().find('.minicolors-swatch-color').css('background-color', rgba);
                    $g(input).trigger('minicolorsInput');
                }
            });
        },
        setMinicolorsColor: function(value){
            var rgba = value ? value : 'rgba(255,255,255,0)',
                color = app.rgba2hex(rgba),
                obj = {
                    color : color[0],
                    opacity : color[1],
                    update: false
                }
            $g('.variables-color-picker').minicolors('value', obj).closest('#color-picker-cell')
                .find('.minicolors-opacity').val(color[1]);
            $g('#color-variables-dialog .active').removeClass('active');
            $g('#color-picker-cell, #color-variables-dialog .nav-tabs li:first-child').addClass('active');
        },
        inputColor: function(){
            var value = this.value.trim().toLowerCase(),
                parts = value.match(/[^#]\w/g),
                opacity = 1;
            if (parts && parts.length == 3) {
                var rgba = 'rgba(';
                for (var i = 0; i < 3; i++) {
                    rgba += parseInt(parts[i], 16);
                    rgba += ', ';
                }
                if (!this.dataset.rgba) {
                    rgba += '1)';
                } else {
                    parts = this.dataset.rgba.toLowerCase().match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);
                    if (!parts) {
                        rgba += '1)';
                    } else {
                        opacity = parts[4];
                        rgba += parts[4]+')';
                    }
                }
                this.dataset.rgba = rgba;
                $g(this).next().find('.minicolors-swatch-color').css('background-color', rgba);
                $g(this).trigger('minicolorsInput');
                app.setMinicolorsColor(rgba);
            }
            $g(this).closest('.ba-settings-item').find('.minicolors-opacity').val(opacity).removeAttr('readonly');
        },
        updateInput: function(input, rgba){
            var color = app.rgba2hex(rgba);
            input.attr('data-rgba', rgba).val(color[0]).next().find('.minicolors-swatch-color').css('background-color', rgba);
            input.closest('.minicolors').next().find('.minicolors-opacity').val(color[1]);
        },
        rgba2hex: function(rgb){
            var parts = rgb.toLowerCase().match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/),
                hex = '#',
                part,
                color = new Array();
            if (parts) {
                for (var i = 1; i <= 3; i++) {
                    part = parseInt(parts[i]).toString(16);
                    if (part.length < 2) {
                        part = '0'+part;
                    }
                    hex += part;
                }
                if (!parts[4]) {
                    parts[4] = 1;
                }
                color.push(hex);
                color.push(parts[4] * 1);
                
                return color;
            } else {
                color.push(rgb.trim());
                color.push(1);
                
                return color;
            }
        }
    },
    deleteMode,
    uploadMode,
    $g = jQuery,
    gridboxCallback,
    fontBtn,
    authorSocial = {
        "behance":{
            "title":"behance",
            "label":"Behance",
            "icon":"zmdi zmdi-behance"
        },
        "dribbble":{
            "title":"dribbble",
            "label":"Dribbble",
            "icon":"zmdi zmdi-dribbble"
        },
        "facebook":{
            "title":"facebook",
            "label":"Facebook",
            "icon":"zmdi zmdi-facebook"
        },
        "google+":{
            "title":"google+",
            "label":"Google+",
            "icon":"zmdi zmdi-google-plus"
        },
        "instagram":{
            "title":"instagram",
            "label":"Instagram",
            "icon":"zmdi zmdi-instagram"
        },
        "linkedin":{
            "title":"linkedin",
            "label":"Linkedin",
            "icon":"zmdi zmdi-linkedin"
        },
        "odnoklassniki":{
            "title":"odnoklassniki",
            "label":"Odnoklassniki",
            "icon":"zmdi zmdi-odnoklassniki"
        },
        "pinterest":{
            "title":"pinterest",
            "label":"Pinterest",
            "icon":"zmdi zmdi-pinterest"
        },
        "tumblr":{
            "title":"tumblr",
            "label":"Tumblr",
            "icon":"zmdi zmdi-tumblr"
        },
        "twitter":{
            "title":"twitter",
            "label":"Twitter",
            "icon":"zmdi zmdi-twitter"
        },
        "vimeo":{
            "title":"vimeo",
            "label":"Vimeo",
            "icon":"zmdi zmdi-vimeo"
        },
        "vkontakte":{
            "title":"vkontakte",
            "label":"Vkontakte",
            "icon":"zmdi zmdi-vk"
        },
        "youtube":{
            "title":"youtube",
            "label":"Youtube",
            "icon":"zmdi zmdi-youtube"
        }
    };

async function makeFetchRequest(url, data)
{
    let body = app.getFormData(data),
        options = {
            method: 'POST',
            body: body
        },
        request = await fetch(url, options),
        response = null;
    if (request.ok) {
        let text = await request.text();
        try {
            response = JSON.parse(text);
        } catch (err) {
            console.info(text);
            console.info(err);
        }
    } else {
        let utf8Decoder = new TextDecoder("utf-8"),
            reader = request.body.getReader(),
            textData = await reader.read(),
            text = utf8Decoder.decode(textData.value);
        console.info(getErrorText(text));
    }

    return response;
}

function getCSSrulesString()
{
    var str = 'body.cke_editable  {font-family: sans-serif, Arial, Verdana, "Trebuchet MS";}';
    str += '::-webkit-scrollbar {width: 6px;} ::-webkit-scrollbar-track {background-color: transparent; }'
    str += '::-webkit-scrollbar-thumb {background: #ddd;border-radius: 6px;}';
    
    return str;
}

function setCkeditor()
{
    if (typeof(CKEDITOR) != 'undefined') {
        if ($g('html').attr('dir') == 'rtl') {
            CKEDITOR.config.contentsLangDirection = 'rtl';
        }
        let toolbars = {
            basic: [
                {name: 'document', items: ['Source']},
                {name: 'styles', items: ['Format']},
                {name: 'colors', items: ['TextColor']},
                {name: 'basicstyles', items: ['Bold', 'Italic']},
                {name: 'paragraph',   items: ['NumberedList', 'BulletedList', '-', 'myJustifyLeft', 'JustifyCenter', 'JustifyRight']},
                {name: 'links', items: ['Link', 'Unlink']},
                {name: 'insert', items: ['myImage']},
                {name: 'data-tags', items: ['dataTags', 'resizeEditor']}
            ],
            simple: [
                {name: 'document', items: ['Source']},
                {name: 'basicstyles', items: ['Bold', 'Italic']},
                {name: 'paragraph',   items: ['NumberedList', 'BulletedList', '-', 'myJustifyLeft', 'JustifyCenter', 'JustifyRight']},
                {name: 'links', items: ['Link', 'Unlink']}
            ]
        };
        CKEDITOR.dtd.$removeEmpty.span = 0;
        CKEDITOR.dtd.$removeEmpty.i = 0;
        CKEDITOR.config.removePlugins = 'image,magicline';
        CKEDITOR.config.uiColor = '#fafafa';
        CKEDITOR.config.allowedContent = true;
        CKEDITOR.config.contentsCss = [getCSSrulesString()];
        $g('.category-description, .ckeditor-options-wrapper textarea, #resized-ckeditor-dialog textarea').each(function(){
            let key = this.dataset.settings ? this.dataset.settings : this.dataset.key,
                toolbar = this.dataset.cke ? this.dataset.cke : 'basic';
            if (this.dataset.group) {
                key = this.dataset.group+'-'+key;
            }
            app.cke[key] = CKEDITOR.replace(this);
            app.cke[key].config.toolbar_Basic = toolbars[toolbar];
            app.cke[key].config.toolbar = 'Basic';
            app.cke[key].config.height = 150;
        });
    }
}

function setTooltip(parent)
{
    parent.off('mouseenter mouseleave').on('mouseenter', function(){
        if (this.closest('.ba-sidebar') && document.body.classList.contains('visible-sidebar')) {
            return false;
        }
        var coord = this.getBoundingClientRect(),
            top = coord.top,
            clone = this.querySelector('.ba-tooltip').cloneNode(true),
            center = (coord.right - coord.left) / 2;
        center = coord.left + center;
        if (clone.classList.contains('ba-bottom')) {
            top = coord.bottom;
        }
        $g('body').append(clone);
        var tooltip = $g(clone),
            width = tooltip.outerWidth(),
            height = tooltip.outerHeight();
        if (tooltip.hasClass('ba-top') || tooltip.hasClass('ba-help')) {
            top -= (15 + height);
            center -= (width / 2)
        } else if (tooltip.hasClass('ba-left') || tooltip.hasClass('ba-right')) {
            top += (coord.bottom - coord.top - height) / 2;
        }
        if (tooltip.hasClass('ba-left')) {
            center = coord.left - width - 15
        } else if (tooltip.hasClass('ba-right')) {
            center = coord.right + 15
        }

        if (tooltip.hasClass('ba-bottom')) {
            top += 10;
            center -= (width / 2)
        }
        tooltip.css({
            'top' : top+'px',
            'left' : center+'px'
        });
    }).on('mouseleave', function(){
        var tooltip = $g('body > .ba-tooltip').addClass('tooltip-hidden');
        setTimeout(function(){
            tooltip.remove();
        }, 500);
    });
}

function getProductsHtml(modal, json, type)
{
    let ul = modal.querySelector('ul');
    json.forEach(function(el){
        let li = document.createElement('li'),
            html = '<span class="ba-item-thumbnail"';
        if (el.image) {
            let image = el.image.indexOf('balbooa.com') == -1 ? JUri+el.image : el.image;
            html += ' style="background-image: url('+image+');"';
        }
        html += '>';
        if (!el.image) {
            html += '<i class="zmdi zmdi-'+(type == 'category' ? 'folder' : 'label')+'"></i>';
        }
        html += '</span><span class="picker-item-title"><span class="ba-picker-item-title">'+el.title+'</span>';
        if (type == 'product' && el.info) {
            html += '<span class="ba-picker-item-info">'+el.info+'</span>';
        }
        html += '</span>';
        if (type == 'product') {
            html += '<span class="picker-item-price">'+('sale' in el.prices ? el.prices.sale : el.prices.price)+'</span>';
        }
        li.dataset.value = JSON.stringify(el);
        li.dataset.id = el.id;
        li.innerHTML = html;
        ul.append(li);
    });
}

function getSortingItem(obj)
{
    let item = document.createElement('div'),
        html = '';
    if (obj.type != 'tax') {
        html += '<div class="sorting-icon"><i class="zmdi zmdi-more-vert sortable-handle"></i></div>';
    }
    html += '<div class="sorting-checkbox"><label class="ba-checkbox ba-hide-checkbox">'+
        '<input type="checkbox"><span></span></label></div>';
    html += '<div class="sorting-title"><input type="text" placeholder="'+app._('TITLE')+'"></div>';
    if ('color' in obj) {
        html += '<div class="sorting-color-picker"><div class="minicolors minicolors-theme-bootstrap">'+
            '<input type="text" data-type="color" class="minicolors-input" data-rgba="'+obj.color+
            '"><span class="minicolors-swatch minicolors-trigger"><span class="minicolors-swatch-color" style="background-color: '+
            obj.color+';"></span></span></div></div>';
    }
    if ('image' in obj) {
        html += '<div class="sorting-image-picker" data-image="'+obj.image+
            '" style="--sorting-image: url('+JUri+obj.image+')"><i class="zmdi zmdi-camera"></i></div>';
    }
    if (obj.type == 'tax') {
        html += '<div class="sorting-tax-rate"><input type="text" value="'+obj.rate+'" placeholder="%"></div>';
        html += '<div class="sorting-tax-countries-wrapper">';
        html += '<div class="sorting-tax-country">';
        html += '<div class="tax-rates-items-wrapper"></div>';
        html += '<div class="select-items-wrapper add-tax-country-region" data-target="country">';
        html += '<span class="ba-tooltip ba-top ba-hide-element">'+app._('ADD_COUNTRY')+'</span>';
        html += '<i class="zmdi zmdi-globe"></i></div>';
        html += '</div>';
        html += '</div>';
        html += '<div class="sorting-tax-category-wrapper">';
        html += '<div class="tax-rates-items-wrapper"></div>';
        html += '<div class="select-items-wrapper"><span class="ba-tooltip ba-top ba-hide-element">';
        html += app._('ADD_CATEGORY')+'</span>';
        html += '<i class="zmdi zmdi-folder add-tax-category"></i></div>';
        html += '</div>';
        html += '<div class="sorting-more-options-wrapper">';
        html += '<i class="zmdi zmdi-more show-more-tax-options" data-shipping="0"></i>';
        html += '</div>';
    }
    item.className = 'sorting-item';
    item.innerHTML = html;
    item.querySelector('.sorting-title input').value = obj.title;
    item.querySelector('input[type="checkbox"]').dataset.ind = obj.key;
    if (obj.type == 'tax') {
        item.querySelector('.sorting-tax-category-wrapper').style.setProperty('--placeholder-text', "'"+app._('CATEGORY')+"'");
        item.querySelector('.sorting-tax-country').style.setProperty('--placeholder-text', "'"+app._('COUNTRY')+"'");
        $g(item).find('.ba-tooltip').each(function(){
            setTooltip($g(this).parent())
        });
    }
    
    return item;
}

function checkIframe(modal, view)
{
    var iframe = modal.find('iframe');
    if (iframe.attr('src').indexOf('view='+view) == -1) {
        iframe[0].src = 'index.php?option=com_gridbox&view='+view+'&tmpl=component';
        iframe[0].onload = function(){
            modal.modal();
        }
    } else {
        modal.modal();
    }
}

function createSelectedApplies(obj, type)
{
    let html = '<span class="ba-item-thumbnail"',
        span = document.createElement('span');
    span.className = 'selected-applies selected-items';
    span.dataset.id = obj.id;
    if (obj.variation) {
        span.dataset.variation = obj.variation;
    }
    if (obj.image) {
        html += ' style="background-image: url('+JUri+obj.image+');"';
    }
    html += '>';
    if (!obj.image) {
        html += '<i class="zmdi zmdi-'+(type == 'category' ? 'folder' : 'label')+'"></i>';
    }
    html += '</span><span class="selected-items-name"><span class="selected-items-title">'+obj.title+'</span>';
    if (obj.info) {
        html += '<span class="selected-items-info">'+obj.info+'</span>';
    }
    html += '</span><i class="zmdi zmdi-close remove-selected-items"></i>';
    span.innerHTML = html;
    document.querySelector('.selected-applies-wrapper').append(span);
}

function getErrorText(text)
{
    let div = document.createElement('div');
    div.innerHTML = text;
    if (div.querySelector('title')) {
        text = div.querySelector('title').textContent;
    }

    return text;
}

function prepareCouponApplies(value)
{
    document.querySelectorAll('.ba-options-applies-wrapper, .selected-applies-wrapper').forEach(function(el){
        el.style.display = value == '*' ? 'none' : '';
        if (el.classList.contains('ba-options-applies-wrapper')) {
            let btn = el.querySelector('i');
            btn.dataset.modal = value+'-applies-dialog';
            btn.dataset.type = value;
        } else {
            el.innerHTML = '';
        }
    });
}

function showAppliesModal(modal)
{
    modal.querySelectorAll('li').forEach(function(li){
        let obj = JSON.parse(li.dataset.value),
            str1 = '.selected-applies[data-id="'+obj.id+'"]'+(obj.variation ? '[data-variation="'+obj.variation+'"]' : ''),
            query = str1+', input[name="product"][value="'+obj.id+'"]'+(obj.variation ? '[data-variation="'+obj.variation+'"]' : ''),
            exist = document.querySelector(query);
        li.classList[exist ? 'add' : 'remove']('selected');
    });
    showDataTagsDialog(fontBtn.dataset.modal);
}

function showDataTagsDialog(dialog, margin)
{
    var rect = fontBtn.getBoundingClientRect(),
        modal = $g('#'+dialog),
        width = modal.innerWidth(),
        height = modal.innerHeight(),
        top = rect.bottom - height / 2 - rect.height / 2,
        offset = 15,
        bottom = '50%';
    if (!margin && margin !== 0) {
        margin = 10;
    }
    if (window.innerHeight - top < height) {
        top = window.innerHeight - height - offset;
        bottom = (window.innerHeight - rect.bottom + rect.height / 2 - offset)+'px';
    } else if (top < 0) {
        top = offset;
        bottom = (height - rect.bottom + rect.height / 2 + offset)+'px';
    }
    modal.css({
        left: rect.left - width - margin,
        top: top
    }).modal()[0].style.setProperty('--picker-arrow-bottom', bottom);
}

function getCookie(name)
{
    var matches = document.cookie.match(new RegExp("(?:^|; )"+name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1')+"=([^;]*)"));

    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function setCookie(name, value, options) {
    options = options || {};
    var expires = options.expires;
    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }
    value = encodeURIComponent(value);
    var updatedCookie = name + "=" + value;
    for (var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }
    document.cookie = updatedCookie;
}

function getVisibleBranchClilds(parent)
{
    let childs = parent.find('> ul > li').length;
    parent.find('> ul > li.visible-branch').each(function(){
        childs += getVisibleBranchClilds($g(this));
    });
    parent[0].style.setProperty('--category-childs', childs);

    return childs;
}

function getParentVisibleBranchClilds(el)
{
    let parents = el.parent().parents('li.visible-branch');
    if (parents.length) {
        let parent = parents[parents.length - 1];
        getVisibleBranchClilds($g(parent));
    }
}

function setGravatarDefault(item)
{
    item.previousElementSibling.style.backgroundImage = 'url('+JUri+'components/com_gridbox/assets/images/default-user.png'+')';
}

function deleteCookie(name)
{
    setCookie(name, "", {
        expires: -1
    });
}

function showNotice(message, className)
{
    if (!className) {
        className = '';
    }
    if (notification.hasClass('notification-in')) {
        setTimeout(function(){
            notification.removeClass('notification-in').addClass('animation-out');
            setTimeout(function(){
                addNoticeText(message, className);
            }, 400);
        }, 2000);
    } else {
        addNoticeText(message, className);
    }
}

app.showNotice = showNotice;

function addNoticeText(message, className)
{
    var time = 3000;
    if (className) {
        time = 6000;
    }
    notification.find('p').html(message);
    notification.addClass(className).removeClass('animation-out').addClass('notification-in');
    setTimeout(function(){
        notification.removeClass('notification-in').addClass('animation-out');
        setTimeout(function(){
            notification.removeClass(className);
        }, 400);
    }, time);
}

app.checkModule = function(module){
    if (!(module in app)) {
        loadModule(module);
    } else {
        app[module]();
    }
}

function loadModule(module)
{
    var script = document.createElement('script');
    script.type = 'text/javascript';
    if (module == 'photoEditor') {
        script.src = 'components/com_gridbox/assets/js/'+module+'.js';
    } else {
        script.src = JUri+'components/com_gridbox/libraries/modules/'+module+'.js';
    }
    document.getElementsByTagName('head')[0].appendChild(script);
}

function rangeAction(range, callback)
{
    var $this = $g(range),
        max = $this.attr('max') * 1,
        min = $this.attr('min') * 1,
        number = $this.next();
    number.on('input', function(){
        var value = this.value * 1;
        if (max && value > max) {
            this.value = value = max;
        }
        if (min && value < min) {
            value = min;
        }
        $this.val(value);
        setLinearWidth($this);
        callback(number);
    });
    $this.on('input', function(){
        var value = this.value * 1;
        number.val(value).trigger('input');
    });
}

function inputCallback(input)
{
    var callback = input.attr('data-callback');
    if (callback in app) {
        app[callback]();
    }
}

function setLinearWidth(range)
{
    var max = range.attr('max') * 1,
        value = range.val() * 1,
        sx = ((Math.abs(value) * 100) / max) * range.width() / 100,
        linear = range.prev();
    if (value < 0) {
        linear.addClass('ba-mirror-liner');
    } else {
        linear.removeClass('ba-mirror-liner');
    }
    if (linear.hasClass('letter-spacing')) {
        sx = sx / 2;
    }
    linear.width(sx);
}

app.states = {
    addState: function($this){
        if (!$this.clicked) {
            $this.clicked = true;
            makeFetchRequest('index.php?option=com_gridbox&task=storeSettings.addState', {
                id: app.country.obj.id
            }).then(function(json){
                $this.clicked = false;
                app.country.obj.states[json.id] = json;
                app.states.add(json);
            });
        }
    },
    checkShippingResionsCount(){
        let c = Object.keys(app.country.obj.states).length;
        $g('.shipping-countries-list .selected-items[data-id="'+app.country.obj.id+'"] .selected-regions-count').each(function(){
            this.dataset.totalRegions = c;
        });
    },
    add: function(json){
        let content = this.content.cloneNode(true),
            li = content.querySelector('li');
        li.querySelector('.country-title').textContent = json.title;
        li.querySelector('input').value = json.title;
        li.dataset.title = json.title;
        li.dataset.value = json.id;
        this.ul.append(content);
        this.checkShippingResionsCount();
        $g(li).find('.ba-tooltip').each(function(){
            setTooltip($g(this).parent())
        });
    },
    back: function(){
        app.country.modal.classList.add('visible-country');
    },
    load: function(){
        this.content = app.country.modal.querySelector('template.state-li').content;
        this.ul =  app.country.modal.querySelector('.states-modal-body ul');
        this.header = app.country.modal.querySelector('.states-modal-header');
    },
    show: function(obj){
        this.header.textContent = obj.title;
        this.ul.innerHTML = '';
        for (let ind in obj.states) {
            this.add(obj.states[ind]);
        }
    },
    edit: function(item){
        let li = item.closest('li'),
            input = li.querySelector('input');
        this.obj = app.country.obj.states[li.dataset.value];
        this.ul.classList.add('country-editing');
        input.value = this.obj.title;
        app.country.toggle(li, 'add', 'editing-country');
        input.setSelectionRange(this.obj.title.length, this.obj.title.length);
        input.focus();
    },
    close: function(){
        let adding = app.country.modal.classList.contains('add-region-to-tax');
        this.ul.querySelectorAll('li.editing-country').forEach(function(el){
            app.country.toggle(el, 'remove', 'editing-country'+(adding ? ' prevent-event' : ''));
        });
        this.ul.classList.remove('country-editing');
    },
    save: function(item){
        let li = item.closest('li'),
            adding = app.country.modal.classList.contains('add-region-to-tax');
        this.obj.title = li.querySelector('input').value.trim();
        li.querySelector('.country-title').textContent = this.obj.title;
        makeFetchRequest('index.php?option=com_gridbox&task=storeSettings.updateState', this.obj).then(function(){
            app.country.toggle(li, 'remove', 'editing-country'+(adding ? ' prevent-event' : ''));
            app.states.ul.classList.remove('country-editing');
            $g('.tax-country-state .selected-items[data-id="'+app.states.obj.id+'"]').each(function(){
                this.querySelector('.selected-items-name').textContent = app.states.obj.title;
            });
        });
    },
    deleteState: function(){
        makeFetchRequest('index.php?option=com_gridbox&task=storeSettings.deleteState', this.obj).then(function(){
            app.states.ul.querySelector('li[data-value="'+app.states.obj.id+'"]').remove();
            delete app.country.obj.states[app.states.obj.id];
            $g('.tax-country-state .selected-items[data-id="'+app.states.obj.id+'"] .delete-country-region').trigger('click');
            app.states.checkShippingResionsCount();
        });
    },
    delete: function(item){
        let li = item.closest('li');
        this.obj = app.country.obj.states[li.dataset.value];
        deleteMode = 'state.delete';
        $g('#delete-dialog').modal();
    }
}

app.country = {
    countries: {},
    addCountry: function($this){
        if (!$this.clicked) {
            $this.clicked = true;
            makeFetchRequest('index.php?option=com_gridbox&task=storeSettings.addCountry').then(function(json){
                $this.clicked = false;
                app.country.add(json);
            });
        }
    },
    add: function(json){
        let content = this.content.cloneNode(true),
            li = content.querySelector('li');
        this.countries[json.id] = json;
        li.querySelector('.country-title').textContent = json.title;
        li.querySelector('input').value = json.title;
        li.dataset.title = json.title;
        li.dataset.value = json.id;
        this.ul.append(content);
        $g(li).find('.ba-tooltip').each(function(){
            setTooltip($g(this).parent())
        });
    },
    toggle: function(el, action, classes){
        classes.split(' ').forEach(function(className){
            if (className) {
                el.classList[action](className);
            }
        })
    },
    getShippingEl:function(id, states){
        let obj = this.countries[id],
            c = Object.keys(obj.states).length,
            count = 0,
            div = document.createElement('div'),
            span = null;
        if (!states) {
            states = {};
            for (let ind in obj.states) {
                states[ind] = true;
            }
        }
        for (let ind in states) {
            if (states[ind]) {
                count++;
            }
        }
        div.innerHTML = '<span class="selected-items" data-id="'+obj.id+'"><span class="selected-items-name">'+obj.title+
            '</span><span data-count="'+count+'" data-total-regions="'+c+'" class="selected-regions-count">'+
            app._('REGIONS')+'</span><i class="zmdi zmdi-close remove-selected-items"></i></span>';
        span = div.querySelector('.selected-items');
        span.dataset.regions = JSON.stringify(states);
        
        return span
    },
    load: function(){
        this.modal = document.querySelector('#store-countries-dialog');
        if (this.modal) {
            this.content = this.modal.querySelector('template.country-li').content;
            this.ul =  this.modal.querySelector('.country-modal-body ul');
            app.states.load();
            makeFetchRequest('index.php?option=com_gridbox&task=storeSettings.getCountries').then(function(json){
                json.forEach(function(obj){
                    app.country.add(obj);
                })
            });
        }
    },
    edit: function(item){
        let li = item.closest('li'),
            input = li.querySelector('input');
        this.obj = this.countries[li.dataset.value];
        this.ul.classList.add('country-editing');
        input.value = this.obj.title;
        this.toggle(li, 'add', 'editing-country prevent-event');
        input.setSelectionRange(this.obj.title.length, this.obj.title.length);
        input.focus();
    },
    close: function(){
        this.ul.querySelectorAll('li.editing-country').forEach(function(el){
            app.country.toggle(el, 'remove', 'editing-country prevent-event');
        });
        this.ul.classList.remove('country-editing');
    },
    save: function(item){
        let li = item.closest('li');
        this.obj.title = li.querySelector('input').value.trim();
        li.querySelector('.country-title').textContent = this.obj.title;
        makeFetchRequest('index.php?option=com_gridbox&task=storeSettings.updateCountry', this.obj).then(function(){
            app.country.toggle(li, 'remove', 'editing-country prevent-event');
            app.country.ul.classList.remove('country-editing');
            $g('.sorting-tax-country, .shipping-countries-list')
                .find('.selected-items[data-id="'+app.country.obj.id+'"]').each(function(){
                this.querySelector('.selected-items-name').textContent = app.country.obj.title;
            });
        });
    },
    show: function(item){
        let li = item.closest('li');
        this.obj = this.countries[li.dataset.value];
        app.states.show(this.obj);
        this.modal.classList.remove('visible-country');
    },
    showModal: function(item){
        fontBtn = item;
        let action = app.country.modal.classList.contains('add-region-to-tax') ? 'removeClass' : 'addClass',
            modal = $g(app.country.modal)[action]('visible-country');
        modal.find('.editing-country span[data-action="close"]').trigger('click');
        modal.find('.picker-search').val('');
        modal.find('li').css('display', '');
        showDataTagsDialog('store-countries-dialog', 0);
    },
    deleteCountry: function(){
        makeFetchRequest('index.php?option=com_gridbox&task=storeSettings.deleteCountry', this.obj).then(function(){
            app.country.ul.querySelector('li[data-value="'+app.country.obj.id+'"]').remove();
            $g('.sorting-tax-country, .shipping-countries-list')
                .find(' .selected-items[data-id="'+app.country.obj.id+'"] .delete-tax-country').trigger('click');
        });
    },
    delete: function(item){
        let li = item.closest('li');
        this.obj = this.countries[li.dataset.value];
        deleteMode = 'country.delete';
        $g('#delete-dialog').modal();
    }
};

(function($){
    app.showSystemSettings = function(obj){
        obj.options = JSON.parse(obj.page_options);
        $g('.system-page-title').val(obj.title);
        $g('.system-page-theme-select input[type="hidden"]').val(obj.theme);
        $g('.system-page-theme-select input[type="text"]').val(obj.themeName);
        $g('#system-settings-dialog .ba-checkbox-parent').css('display', '');
        if ('enable_header' in obj.options) {
            $g('.page-enable-header').prop('checked', obj.options.enable_header);
        } else {
            $g('#system-settings-dialog .ba-checkbox-parent').hide();
        }
        $g('.apply-system-settings').removeClass('active-button').addClass('disabled-button').attr('data-id', obj.id);
        $g('#system-settings-dialog').modal();
    }

    app.searchMetaTags = function(title){
        $('.all-tags li').each(function(){
            var search = $(this).text().trim().toLowerCase();
            if (search.indexOf(title) < 0) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    }

    $(document).ready(function(){
        jQuery.ajax({
            type : "POST",
            dataType : 'text',
            url : JUri+"index.php?option=com_gridbox&task=editor.checkSitemap"
        });
        $g.ajax({
            type : "POST",
            dataType : 'text',
            url : JUri+"index.php?option=com_gridbox&task=comments.sendCommentsEmails"
        });
        $g.ajax({
            type : "POST",
            dataType : 'text',
            url : JUri+"index.php?option=com_gridbox&task=reviews.sendCommentsEmails"
        });
        if ($('body').hasClass('view-blogs')) {
            $('#toolbar-download button').on('click', function(event){
                event.preventDefault();
                $('li.export-apps').hide();
                $('#export-dialog').modal();
                $('.apply-export').attr('data-export', 'app');
            });
        }

        if (!('Joomla' in window)) {
            window.Joomla = {};
        }

        Joomla.submitbutton = function(task) {
            if (task == 'pages.export') {
                exportId = new Array();
                $('.table-striped tbody tr').find('input[type="checkbox"]').each(function(){
                    if ($(this).prop('checked')) {
                        var id = $(this).val();
                        exportId.push(id);
                    }
                });
                $('li.export-apps').hide();
                $('#export-dialog').modal();
                $('.apply-export').attr('data-export', 'pages');
            } else if (task == 'themes.delete') {
                var def = 0;
                $('#installed-themes-view label').each(function(){
                    if ($(this).find('input[type="checkbox"]').prop('checked')) {
                        def = $(this).find('p').attr('data-default');
                        if (def == 1) {
                            return false;
                        }
                    }
                });
                if (def == 1) {
                    $('#default-message-dialog').modal();
                } else {
                    deleteMode = 'array';
                    $('#delete-dialog').modal();
                }
                return false;
            } else if (task == 'apps.addTrash' || task == 'pages.addTrash' || task == 'tags.delete' || task == 'orders.delete'
                || task == 'paymentmethods.delete' || task == 'shipping.delete' || task == 'promocodes.delete'
                || task == 'productoptions.delete') {
                deleteMode = task;
                $('#delete-dialog').modal();
            } else if (task == 'apps.moveTo') {
                moveTo = task;
                showMoveTo();
            } else {
                Joomla.submitform(task);
            }
        }

        Joomla.submitform = function(task) {
            if (task == 'apps.duplicate') {
                var str = app._('LOADING')+'<img src="'+JUri;
                str += 'administrator/components/com_gridbox/assets/images/reload.svg"></img>';
                notification[0].className = 'notification-in';
                notification.find('p').html(str);
            }
            $('.status-td i').trigger('mouseleave');
            var form = document.getElementById("adminForm"),
                obj = {
                    'cid' : new Array(),
                    'meta_tags' : new Array()
                },
                src = form.action;
            if (!task) {
                form.submit();
                return false;
            }
            $(form).find('[name]').not('[name="cid[]"]').not('[name="meta_tags[]"]').each(function(){
                if (this.name == 'task') {
                    obj['task'] = task;
                } else if (this.type == 'radio' || this.type == 'checkbox') {
                    if ($(this).prop('checked')) {
                        obj[this.name] = this.value;
                    }
                } else {
                    obj[this.name] = this.value;
                }
            });
            obj.cid = [];
            $('[name="cid[]"]').each(function(){
                if ($(this).prop('checked')) {
                    obj.cid.push(this.value);
                }
            });
            obj.meta_tags = [];
            $('[name="meta_tags[]"] option').each(function(){
                obj.meta_tags.push(this.value);
            });
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : src,
                data : obj,
                error: function(msg){
                    console.info(getErrorText(msg.responseText));
                },
                success: function(msg){
                    if (task == 'apps.addCategory') {
                        var obj = JSON.parse(msg);
                        if ($('li.root li.active').length > 0) {
                            var blog = $('input[name="blog"]').val(),
                                category = $('li.root  li.active')[0].dataset.id;
                                setCookie('blog'+blog+'id'+category, 1);
                        }
                        $('#gridbox-container').load(form.action+'&category='+obj.id+' #gridbox-content', function(){
                            loadPage();
                            showNotice(obj.msg, '');
                        });
                    } else {
                        reloadPage(msg);
                    }
                }
            });
        }

        setInterval(function(){
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task=gridbox.getSession&tmpl=component",
                success : function(msg){
                }
            });
        }, 600000);

        var massage = '',
            sortableInd = $('.category-list ul.root-list .ba-category').length + 1,
            pageId,
            item,
            CKE,
            submitTask = '',
            themeTitle = '',
            flag = true,
            exportId = new Array(),
            currentContext,
            moveTo = '',
            oldTitle = '';

        window.notification = $('#ba-notification');

        function getAuthorPatern(ind)
        {
            var label = authorSocial[app.authorsSocial[ind].title].label,
                str = '<span class="authors-link" data-key="'+ind+'"><span class="authors-link-title">'+
                label+'</span><i class="zmdi zmdi-close delete-author-social-link"></i></span>';

            return str;
        }

        function openAuthorSocialDialog(obj)
        {
            let str = '',
                link = obj ? obj.link : '',
                title = obj ? obj.title : 'facebook',
                modal = $g('#edit-author-social-modal');
            for (let ind in authorSocial) {
                if (ind != 'google+') {
                    str += '<li data-value="'+authorSocial[ind].title+'">'+authorSocial[ind].label+'</li>';
                }
            }
            modal.find('ul').html(str);
            modal.find('.ba-custom-select input[type="hidden"]').val(title).prev().val(authorSocial[title].label);
            modal.find('.author-link-url').val(link);
            if (link.trim()) {
                $g('.apply-author-link').addClass('active-button');
            } else {
                $g('.apply-author-link').removeClass('active-button');
            }
            modal.modal();
        }

        function getCommentLikeStatus()
        {
            let str = app.currentComment.find('td.select-td  input[type="hidden"]').val(),
                obj = JSON.parse(str),
                div = $g('.comment-data-view-pattern').clone(),
                avatar = app.currentComment.find('.ba-author-avatar').clone(),
                view = $g('input[name="ba_view"]').val();
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task="+view+".getCommentLikeStatus",
                data: {
                    id: obj.id
                },
                complete:function(msg){
                    let message = obj.message.replace(/\n/g, '<br>');
                    if (!message) {
                        div.find('.comment-user-message-wrapper').hide();
                    }
                    $g('.comments-sidebar-header > span.disabled').removeClass('disabled');
                    div.find('.comment-user-info-wrapper').prepend(avatar);
                    div.find('.comment-user-name').text(obj.name);
                    div.find('.comment-user-email').text(obj.email);
                    div.find('.comment-user-ip').text(obj.ip);
                    div.find('.comment-user-date').text(obj.time);
                    div.find('.comment-page-title').text(obj.title);
                    div.find('.comment-page-url').attr('href', obj.link);
                    div.find('.comment-message').html(message);
                    div.find('.comment-likes-action[data-action="likes"] .likes-count').text(obj.likes);
                    div.find('.comment-likes-action[data-action="dislikes"] .likes-count').text(obj.dislikes);
                    div.find('.comment-likes-action').removeClass('active');
                    div.find('.comment-likes-action[data-action="'+msg.responseText+'"]').addClass('active');
                    for (let i = 0; i < obj.attachments.length; i++) {
                        if (obj.attachments[i].type == 'file') {
                            let str = '<div class="comment-attachment-file">';
                            str += '<i class="zmdi zmdi-attachment-alt"></i>';
                            str += '<a target="_blank" href="'+obj.attachments[i].link+'">'+
                                obj.attachments[i].name+'</a><span class="comment-attachment-icons-wrapper"><a download href="'+
                                obj.attachments[i].link+'"><i class="zmdi zmdi-download"></i></a>';
                            if (obj.email == joomlaUser.email) {
                                str += '<i class="zmdi zmdi-delete delete-comment-attachment-file" data-id="'+obj.attachments[i].id+
                                    '" data-filename="'+obj.attachments[i].filename+'" data-type="file"></i>';
                            }
                            str += '</span></div>';
                            div.find('.comment-attachments-wrapper').append(str);
                        } else {
                            let str = '<span class="comment-attachment-image-type-wrapper">'
                            str += '<span class="comment-attachment-image-type" style="background-image: url('+
                                obj.attachments[i].link+');" data-img="'+obj.attachments[i].link+'"></span>';
                            if (obj.email == joomlaUser.email) {
                                str += '<i class="zmdi zmdi-close delete-comment-attachment-file" data-id="'+obj.attachments[i].id+
                                    '" data-filename="'+obj.attachments[i].filename+'"></i>';
                            }
                            str += '</span>'
                            div.find('.comment-attachments-image-wrapper').append(str);
                        }
                    }
                    if (obj.user_type == 'user' &&  obj.user_id == joomlaUser.id) {
                        div.find('.comment-user-message-wrapper .ba-comment-message-wrapper').hide();
                    } else {
                        div.find('.edit-user-comment, .comment-user-message-wrapper .ba-comment-message-wrapper').remove();
                    }
                    if (view == 'reviews') {
                        div.find('.review-rating-wrapper').each(function(){
                            if (obj.parent != 0) {
                                this.remove();
                            } else {
                                let stars = this.querySelectorAll('i');
                                for (let i = 0; i < obj.rating; i++) {
                                    stars[i].classList.add('active');
                                }
                            }
                        });
                        if (obj.parent != 0) {
                            div.find('> .ba-comment-message-wrapper').remove();
                        }
                    }
                    let html = div.html();
                    $g('.comments-sidebar-body').html(html);
                    $g('.comments-sidebar-body .ba-tooltip').each(function(){
                        setTooltip($g(this).parent());
                    });
                }
            });
        }

        function insertTextAtCursor(el, text)
        {
            var val = el.value, endIndex, range;
            if (typeof el.selectionStart != "undefined" && typeof el.selectionEnd != "undefined") {
                endIndex = el.selectionEnd;
                el.value = val.slice(0, el.selectionStart) + text + val.slice(endIndex);
                el.selectionStart = el.selectionEnd = endIndex + text.length;
            } else if (typeof document.selection != "undefined" && typeof document.selection.createRange != "undefined") {
                el.focus();
                range = document.selection.createRange();
                range.collapse(false);
                range.text = text;
                range.select();
            }
        }

        function setCommentsImage(image)
        {
            var imgHeight = image.naturalHeight,
                imgWidth = image.naturalWidth,
                modal = $g('.ba-image-modal.instagram-modal').removeClass('instagram-fade-animation'),
                wWidth = $g(window).width(),
                wHeigth = $g(window).height(),
                percent = imgWidth / imgHeight;
            if (wWidth > 1024) {
                if (imgWidth < wWidth && imgHeight < wHeigth) {
                
                } else {
                    if (imgWidth > imgHeight) {
                        imgWidth = wWidth - 100;
                        imgHeight = imgWidth / percent;
                    } else {
                        imgHeight = wHeigth - 100;
                        imgWidth = percent * imgHeight;
                    }
                    if (imgHeight > wHeigth) {
                        imgHeight = wHeigth - 100;
                        imgWidth = percent * imgHeight;
                    }
                    if (imgWidth > wWidth) {
                        imgWidth = wWidth - 100;
                        imgHeight = imgWidth / percent;
                    }
                }
            } else {
                percent = imgWidth / imgHeight;
                if (percent >= 1) {
                    imgWidth = wWidth * 0.90;
                    imgHeight = imgWidth / percent;
                    if (wHeigth - imgHeight < wHeigth * 0.1) {
                        imgHeight = wHeigth * 0.90;
                        imgWidth = imgHeight * percent;
                    }
                } else {
                    imgHeight = wHeigth * 0.90;
                    imgWidth = imgHeight * percent;
                    if (wWidth - imgWidth < wWidth * 0.1) {
                        imgWidth = wWidth * 0.90;
                        imgHeight = imgWidth / percent;
                    }
                }
            }
            var modalTop = (wHeigth - imgHeight) / 2,
                left = (wWidth - imgWidth) / 2;
            setTimeout(function(){
                modal.find('> div').css({
                    'width' : Math.round(imgWidth),
                    'height' : Math.round(imgHeight),
                    'left' : Math.round(left),
                    'top' : Math.round(modalTop)
                }).addClass('instagram-fade-animation');
            }, 1);
        }

        function commentsImageGetPrev(img, images, index)
        {
            var ind = images[index - 1] ? index - 1 : images.length - 1;
            image = document.createElement('img');
            image.onload = function(){
                setCommentsImage(this);
            }
            image.src = images[ind].dataset.img;
            img.style.backgroundImage = 'url('+image.src+')';

            return ind;
        }

        function commentsImageGetNext(img, images, index)
        {
            var ind = images[index + 1] ? index + 1 : 0;
            image = document.createElement('img');
            image.onload = function(){
                setCommentsImage(this);
            }
            image.src = images[ind].dataset.img;
            img.style.backgroundImage = 'url('+image.src+')';

            return ind;
        }

        function commentsImageModalClose(modal, images, index)
        {
            $g(window).off('keyup.instagram');
            modal.addClass('image-lightbox-out');
            var $image = $g(images[index]), 
                width = $image.width(),
                height = $image.height(),
                offset = $image.offset();
            modal.find('> div').css({
                'width' : width,
                'height' : height,
                'left' : offset.left,
                'top' : offset.top - $g(window).scrollTop()
            });
            setTimeout(function(){
                modal.remove();
            }, 500);
        }

        $g('.ba-store-statistic-select input[type="hidden"]').on('change', function(){
            app.statistic = {
                date: new Date(this.dataset.current),
                current: this.dataset.current,
                value: this.dataset.current,
                type: this.value
            }
            $g('.ba-store-statistic-action[data-action="+"]').addClass('ba-disabled');
            if (this.value == 'y') {
                $g('.ba-store-statistic-action[data-action="-"]').addClass('ba-disabled');
            } else {
                $g('.ba-store-statistic-action[data-action="-"]').removeClass('ba-disabled');
            }
            this.closest('.ba-store-statistic-header-filter-wrapper').classList.remove('ba-custom-store-statistic');
            if (this.value == 'c') {
                this.closest('.ba-store-statistic-header-filter-wrapper').classList.add('ba-custom-store-statistic');
                $g('.ba-store-statistic-custom-action input').val(app.statistic.value);
                app.statistic.value = app.statistic.value+' - '+app.statistic.value;
            } else if (this.value == 'm') {
                app.statistic.value = app.statistic.date.getFullYear();
            }
            app.statisticFilter();
        });

        $g('.ba-store-statistic-header-filter-wrapper').on('dateUpdated', function(event, d1, d2){
            app.statistic.value = d1+' - '+d2;
            let date = new Date(d1),
                month = date.getMonth() + 1,
                year = date.getFullYear(),
                day = date.getDate(),
                value = app._('SHORT_M'+month)+' '+day+', '+year;
            date = new Date(d2);
            month = date.getMonth() + 1;
            year = date.getFullYear();
            day = date.getDate();
            value += ' - '+app._('SHORT_M'+month)+' '+(day < 10 ? '0'+day : day)+', '+year;
            $g('.ba-store-statistic-select input[type="text"]').val(value);
            app.statisticFilter();
        })

        $g('.ba-store-statistic-action').on('click', function(){
            let value = '';
            if (app.statistic.type == 'd') {
                let day = app.statistic.date.getDate(),
                    month = year = null;
                app.statistic.date.setDate(day + (this.dataset.action == '+' ? 1 : -1));
                month = app.statistic.date.getMonth() + 1;
                year = app.statistic.date.getFullYear();
                day = app.statistic.date.getDate();
                app.statistic.value = year+'-'+(month < 10 ? '0'+month : month)+'-'+(day < 10 ? '0'+day : day);
                value = app._('SHORT_M'+month)+' '+(day < 10 ? '0'+day : day)+', '+year;
            } else if (app.statistic.type == 'w') {
                let day = app.statistic.date.getDate(),
                    month = year = null;
                app.statistic.date.setDate(day + (this.dataset.action == '+' ? 7 : -7));
                month = app.statistic.date.getMonth() + 1;
                year = app.statistic.date.getFullYear();
                day = app.statistic.date.getDate();
                app.statistic.value = year+'-'+(month < 10 ? '0'+month : month)+'-'+(day < 10 ? '0'+day : day);
                let date = new Date(+app.statistic.date);
                date.setDate(date.getDate() - 7);
                day = date.getDate();
                month = date.getMonth() + 1;
                year = date.getFullYear();
                value = app._('SHORT_M'+month)+' '+day+', '+year+' - ';
                date.setDate(day + 6);
                day = date.getDate();
                month = date.getMonth() + 1;
                year = date.getFullYear();
                value += app._('SHORT_M'+month)+' '+(day < 10 ? '0'+day : day)+', '+year;
            } else if (app.statistic.type == 'm') {
                let year = app.statistic.date.getFullYear();
                app.statistic.date.setFullYear(year + (this.dataset.action == '+' ? 1 : -1));
                year = app.statistic.date.getFullYear();
                app.statistic.value = String(year);
                value = app._('MONTHLY')+', '+year;
            } else if (app.statistic.type == 'c' || app.statistic.type == 'y') {
                return false;
            }
            if (this.dataset.action == '+' && app.statistic.value > app.statistic.current) {
                $g('.ba-store-statistic-action[data-action="+"]').addClass('ba-disabled');
                app.statistic.date = new Date(app.statistic.current);
                return false;
            } else {
                $g('.ba-store-statistic-action.ba-disabled').removeClass('ba-disabled');
            }
            $g('.ba-store-statistic-select input[type="text"]').val(value);
            app.statisticFilter();
        });

        $g('.ba-statistics-chart').each(function(){
            $g('.ba-store-statistic-select input[type="hidden"]').trigger('change');
        });

        $g('.orders-status-select').on('customAction', function(){
            $g('.apply-order-status').addClass('active-button');
        });

        $g('.apply-order-status').on('click', function(event){
            event.preventDefault();
            let modal = $g('#orders-status-modal'),
                id = modal.attr('data-id'),
                status = modal.find('input[type="hidden"]').val();
            if (status == 'completed') {
                var str = app._('LOADING')+'<img src="'+JUri;
                str += 'administrator/components/com_gridbox/assets/images/reload.svg"></img>';
                notification[0].className = 'notification-in';
                notification.find('p').html(str);
            }
            makeFetchRequest('index.php?option=com_gridbox&task=orders.updateStatus', {
                id : id,
                status: status,
                comment: modal.find('textarea').val()
            }).then(function(json){
                $g('.edit-order-status, tr[data-id="'+id+'"] .order-status-cell').each(function(){
                    let $this = $g(this);
                    this.style.setProperty('--order-status-color', app.statuses[status].color);
                    $this.find('.order-status-title').text(app.statuses[status].title);
                });
                if (status == 'completed') {
                    notification.removeClass('notification-in').addClass('animation-out');
                }
                modal.modal('hide');
            });
        });

        $g('#category-applies-dialog').each(function(){
            let url = 'index.php?option=com_gridbox&task=promocodes.getCategories',
                modal = this;
            makeFetchRequest(url).then(function(json){
                app.categories = {};
                json.forEach(function(obj){
                    app.categories[obj.id] = obj;
                });
                getProductsHtml(modal, json, 'category');
                modal.dataset.loaded = 'loaded';
            });
        });

        $g('#store-tax-options .sorting-container').on('click', '.add-tax-category', function(){
            fontBtn = this;
            this.wrapper = this.closest('.sorting-tax-category-wrapper').querySelector('.tax-rates-items-wrapper');
            document.querySelectorAll('#category-applies-dialog li').forEach(function(li){
                let exist = fontBtn.wrapper.querySelector('span[data-id="'+li.dataset.id+'"]');
                li.classList[exist ? 'add' : 'remove']('selected');
            });
            showDataTagsDialog('category-applies-dialog', 15);
        }).on('change', '.add-tax-category', function(){
            let obj = JSON.parse(this.dataset.value),
                html = '<span class="selected-items" data-id="'+obj.id+'"><span class="selected-items-name">';
            html += obj.title+'</span><i class="zmdi zmdi-close remove-selected-items"></i></span>';
            $g(this.wrapper).append(html);
        }).on('click', '.add-tax-country-region', function(){
            let modal = $g('#store-countries-dialog');
            if (this.dataset.target == 'region') {
                let wrapper = this.closest('.sorting-tax-countries-wrapper'),
                    id = wrapper.querySelector('.tax-rates-items-wrapper .selected-items').dataset.id,
                    states = [];
                modal.addClass('add-region-to-tax');
                wrapper.querySelectorAll('.tax-country-state .selected-items').forEach(function(item){
                    states.push(item.dataset.id);
                })
                modal.find('.country-modal-body li[data-value="'+id+'"] span[data-action="show"]').trigger('click');
                modal.find('.states-modal-body li').each(function(){
                    this.classList.remove('prevent-event');
                    if (states.indexOf(this.dataset.value) != -1) {
                        this.classList.add('selected');
                    }
                });
            } else {
                modal.removeClass('add-region-to-tax');
            }
            app.country.showModal(this);
        }).on('change', '.add-tax-country-region', function(){
            let row = $g(this).closest('.sorting-item'),
                id = this.dataset.value,
                region = this.dataset.target == 'region',
                obj = region ? app.country.obj.states[id] : app.country.countries[id],
                html = '<span class="selected-items" data-id="'+obj.id+'"><span class="selected-items-name">'+obj.title+
                    '</span><i class="zmdi zmdi-close '+(region ?'delete-country-region' : 'delete-tax-country')+'"></i></span>';
            if (region) {
                let state = '<div class="tax-country-state">'+html+'</div>';
                row.find('.sorting-tax-rate').append('<input type="text" placeholder="%">');
                row.find('.sorting-tax-countries-wrapper').append(state);
            } else {
                this.querySelector('i').className = 'zmdi zmdi-pin';
                this.querySelector('.ba-tooltip').textContent = app._('ADD_REGION');
                row.find('.tax-country-state').remove();
                row.find('.sorting-tax-rate input').each(function(i){
                    if (i != 0) {
                        this.remove();
                    }
                })
                row.find('.sorting-tax-country .tax-rates-items-wrapper').html(html);
                for (let ind in obj.states) {
                    let state = '<div class="tax-country-state"><span class="selected-items" data-id="'+ind;
                    state += '"><span class="selected-items-name">'+obj.states[ind].title;
                    state += '</span><i class="zmdi zmdi-close delete-country-region"></i></span></div>';
                    row.find('.sorting-tax-rate').append('<input type="text" placeholder="%">');
                    row.find('.sorting-tax-countries-wrapper').append(state);
                }
                this.dataset.target = 'region';
            }
        }).on('click', '.delete-tax-country', function(){
            let row = $g(this).closest('.sorting-item');
            row.find('.tax-country-state, .sorting-tax-country .selected-items').remove();
            row.find('.add-tax-country-region').each(function(){
                this.dataset.target = 'country';
                this.querySelector('i').className = 'zmdi zmdi-globe';
                this.querySelector('.ba-tooltip').textContent = app._('ADD_COUNTRY');
            });
            row.find('.sorting-tax-rate input').each(function(i){
                if (i != 0) {
                    this.remove();
                }
            })
        }).on('click', '.delete-country-region', function(){
            let parent = $g(this).closest('.tax-country-state'),
                ind = parent.index();
            this.closest('.sorting-item').querySelector('.sorting-tax-rate input:nth-child('+(ind + 1)+')').remove();
            parent.remove();
        }).on('click', '.show-more-tax-options', function(){
            fontBtn = this;
            let rect = this.getBoundingClientRect(),
                w = document.documentElement.offsetWidth,
                modal = $g('#more-tax-options-dialog'),
                width = modal.innerWidth(),
                height = modal.innerHeight(),
                top = rect.top - height - 10,
                left = rect.left - width / 2 + rect.width / 2,
                bottom = '50%';
            if (w < left + width) {
                left = w - width;
                bottom = (w - rect.right + rect.width / 2)+'px';
            }
            modal.find('input[type="checkbox"][data-option="shipping"]').prop('checked', Boolean(this.dataset.shipping * 1));
            modal.css({
                top: top+'px',
                left: left+'px'
            }).modal()[0].style.setProperty('--picker-arrow-bottom', bottom);

        });

        $g('#more-tax-options-dialog').on('change', 'input[data-option]', function(){
            fontBtn.dataset[this.dataset.option] = Number(this.checked);
        });

        $g('.select-data-tags').on('click', function(){
            fontBtn = this;
            showDataTagsDialog('data-tags-dialog');
        });
        $g('.select-data-tags-type').on('change', function(){
            let modal = $g('#data-tags-dialog');
            modal.find('div.ba-settings-group[class*="-data-tags"]').hide();
            modal.find('div.ba-settings-group'+(this.value ? '.'+this.value+'-data-tags' : '')).css('display', '');
        });
        $g('#data-tags-dialog').find('.ba-settings-group').on('click', '.ba-settings-input-type', function(){
            let value = this.querySelector('input[type="text"]').value;
            if ('ondataTagsInput' in fontBtn) {
                fontBtn.dataset.value = value;
                $g(fontBtn).trigger('dataTagsInput');
            } else {
                let input = fontBtn.closest('.ba-options-group-element').querySelector('input[type="text"]')
                input.value = (input.value ? input.value+' '+value : value);
                $g(input).trigger('input');
            }
            $g('#data-tags-dialog').modal('hide');
        });

        $g('body .modal').on('shown', function(){
            let backdrop = document.querySelector('.modal-backdrop:last-child');
            backdrop.classList.add(this.id+'-backdrop');
            if (this.classList.contains('ba-modal-picker')) {
                backdrop.classList.add('modal-picker-backdrop');
            }
        });

        $g('body').on('click', '#gridbox-payment-methods-dialog .gridbox-app-element', function(){
            $g.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task=paymentmethods.addMethod",
                data: {
                    'type': this.dataset.type
                },
                complete:function(msg){
                    reloadPage(app._('ITEM_CREATED'));
                }
            });
            $g('#gridbox-payment-methods-dialog').modal('hide');
            this.dataset.installed = 1;
            this.querySelector('.default-theme').classList.remove('ba-hide-element');
        });

        $g('body').on('click', '.select-td label, .status-td a', function(event){
            event.stopPropagation();
        });

        $g('body').on('click', '.edit-order-status', function(){
            makeFetchRequest('index.php?option=com_gridbox&task=orders.getStatus', {
                id: this.dataset.id
            }).then(function(json){
                let modal = $g('#orders-status-modal'),
                    html = '';
                modal.find('.orders-status-select').each(function(){
                    let status = app.statuses[json.status] ? app.statuses[json.status] : app.statuses.undefined;
                    this.querySelector('input[type="hidden"]').value = json.status;
                    this.querySelector('input[type="text"]').value = status.title;
                    this.style.setProperty('--status-color', status.color);
                });
                modal.find('.ba-btn-primary').removeClass('active-button');
                modal.find('textarea').val('');
                if (json.payment == 'admin') {
                    let status = app.statuses.new;
                    html += '<div class="order-status-history-record"><div class="order-status-history-record-header">'+
                        '<div><span class="order-status-history-record-username">'+json.username+'</span>'+
                        '<span class="order-status-history-record-date">'+json.date+'</span></div>'+
                        '<div><span class="order-status-history-record-text">'+app._('ORDER_CREATED')+'</span>'+
                        '<span class="order-status-history-record-status" style="--status-color: '+status.color+
                        '">'+status.title+'</span></div></div><div class="order-status-history-record-comment"></div></div>';
                }
                json.history.forEach(function(record){
                    let status = app.statuses[record.status] ? app.statuses[record.status] : app.statuses.undefined;
                    html += '<div class="order-status-history-record"><div class="order-status-history-record-header">'+
                        '<div><span class="order-status-history-record-username">'+record.username+'</span>'+
                        '<span class="order-status-history-record-date">'+record.date+'</span></div>'+
                        '<div><span class="order-status-history-record-text">'+app._('CHANGED_STATUS_TO')+'</span>'+
                        '<span class="order-status-history-record-status" style="--status-color: '+status.color+
                        '">'+status.title+'</span></div></div><div class="order-status-history-record-comment">'+
                        record.comment+'</div></div>';
                });
                modal.find('#order-status-history').html(html);
                modal.modal().attr('data-id', json.id);
            });
        }).on('click', '.payment-methods-table tbody tr', function(){
            let $this = this,
                data = {
                    id: this.dataset.id
                };
            makeFetchRequest('index.php?option=com_gridbox&task=paymentmethods.getOptions', data).then(function(json){
                if (json) {
                    let title = json.title;
                    document.querySelectorAll('#gridbox-payment-methods-dialog [data-type="'+json.type+'"]').forEach(function(el){
                        title = el.querySelector('.ba-title').textContent;
                    });
                    document.querySelector('.ba-options-group-header').textContent = title;
                    document.querySelector('.twin-view-right-sidebar').dataset.edit = json.type;
                    document.querySelector('.twin-view-right-sidebar').dataset.id = json.id;
                    document.querySelectorAll('tr.active').forEach(function(tr){
                        tr.classList.remove('active');
                    });
                    document.querySelectorAll('.twin-view-sidebar-header > span.disabled').forEach(function(el){
                        el.classList.remove('disabled');
                    });
                    $this.classList.add('active');
                    let settings = JSON.parse(json.settings);
                    document.querySelectorAll('.ba-options-group-element [data-key]').forEach(function(el){
                        el.value = json[el.dataset.key];
                        app.removeAlertTooltip(el);
                    });
                    document.querySelectorAll('.'+json.type+'-payment-options [data-settings]').forEach(function(el){
                        el.value = el.dataset.settings in settings ? settings[el.dataset.settings] : '';
                        if (el.dataset.cke) {
                            app.cke[el.dataset.settings].setData(el.value);
                        }
                        app.removeAlertTooltip(el);
                    });
                }
            });
        }).on('click', '.apply-payment-methods', function(){
            if (!this.classList.contains('disabled')) {
                let alert = false,
                    obj = {
                        id: this.closest('.twin-view-right-sidebar').dataset.id,
                        type: this.closest('.twin-view-right-sidebar').dataset.edit
                    },
                    settings = {};
                document.querySelectorAll('.ba-options-group-element [data-key]').forEach(function($this){
                    obj[$this.dataset.key] = $this.value;
                    if ($this.value == '') {
                        let parent = $this.closest('.ba-options-group-element');
                        alert = true;
                        app.toggleAlertTooltip(alert, $this, parent, 'THIS_FIELD_REQUIRED');
                    }
                });
                document.querySelectorAll('.'+obj.type+'-payment-options [data-settings]').forEach(function($this){
                    settings[$this.dataset.settings] = $this.value;
                    if ($this.dataset.cke) {
                        settings[$this.dataset.settings] = app.cke[$this.dataset.settings].getData()
                    }
                    if ($this.value == '' && $this.dataset.settings != 'description') {
                        let parent = $this.closest('.ba-options-group-element');
                        alert = true;
                        app.toggleAlertTooltip(alert, $this, parent, 'THIS_FIELD_REQUIRED');
                    }
                });
                if (alert) {
                    return false;
                }
                obj.settings = JSON.stringify(settings);
                makeFetchRequest('index.php?option=com_gridbox&task=paymentmethods.updateMethod', obj).then(function(json){
                    if (json) {
                        reloadPage(json.message);
                    }
                });
            }
        }).on('click', '.delete-payment-method', function(){
            if (!this.classList.contains('disabled')) {
                document.querySelector('#context-item').value = this.closest('.twin-view-right-sidebar').dataset.id;
                deleteMode = 'paymentmethods.contextDelete';
                $g('#delete-dialog').modal();
            }
        });

        $g('body').on('focus', '.ba-options-group-wrapper .ba-alert', function(){
            app.removeAlertTooltip(this.querySelector('input, select'));
        })

        $g('body').on('click', '.shipping-table tbody tr', function(){
            let $this = this,
                data = {
                    id: this.dataset.id
                };
            makeFetchRequest('index.php?option=com_gridbox&task=shipping.getOptions', data).then(function(json){
                if (json) {
                    let params = JSON.parse(json.options),
                        value = null;
                    $g('.shipping-options [data-settings]').each(function(){
                        let shippingType = this.closest('.shipping-type-options');
                        if (shippingType && params.type != shippingType.dataset.type) {
                            value = this.type == 'checkbox' ? false : '';
                        } else if (this.dataset.group) {
                            value = params[this.dataset.group][this.dataset.settings];
                        } else {
                            value = params[this.dataset.settings];
                        }
                        if (this.type == 'checkbox') {
                            this.checked = value;
                            $g(this).trigger('change');
                        } else if (this.dataset.settings == 'type') {
                            this.value = value;
                            $g('.shipping-type-options').hide();
                            let length = $g('.'+value+'-shipping-type').css('display', '').length;
                            if (length > 0) {
                                $g('.shipping-type-options-label').css('display', '');
                            } else {
                                $g('.shipping-type-options-label').hide();
                            }
                        } else if (this.type == 'text') {
                            this.value = value;
                        } else if (this.dataset.cke) {
                            this.value = value;
                            app.cke[this.dataset.group+'-'+this.dataset.settings].setData(value);
                        } else if (this.classList.contains('shipping-countries-list')) {
                            this.innerHTML = '';
                            for (let id in value) {
                                let span = app.country.getShippingEl(id, value[id]);
                                this.append(span);
                            }
                        } else if (this.classList.contains('ba-rate-by-list')) {
                            this.innerHTML = '';
                            if (value) {
                                for (let ind in value) {
                                    let clone = document.querySelector('template.rate-by-'+this.dataset.group).content.cloneNode(true);
                                    clone.querySelectorAll('[data-ind]').forEach(function(el){
                                        if (el.localName == 'input') {
                                            el.value = value[ind][el.dataset.ind];
                                        } else {
                                            value[ind][el.dataset.ind].forEach(function(id){
                                                if (app.categories[id]) {
                                                    let obj = app.categories[id],
                                                        html = '<span class="selected-items" data-id="'+id+'">';
                                                    html += '<span class="ba-item-thumbnail"';
                                                    if (obj.image) {
                                                        let image = obj.image.indexOf('balbooa.com') == -1 ? JUri+obj.image : obj.image;
                                                        html += ' style="background-image: url('+image+');"';
                                                    }
                                                    html += '>';
                                                    if (!obj.image) {
                                                        html += '<i class="zmdi zmdi-folder"></i>';
                                                    }
                                                    html += '</span><span class="selected-items-name">'+obj.title;
                                                    html += '</span><i class="zmdi zmdi-close remove-selected-items"></i></span>';
                                                    el.insertAdjacentHTML('beforeend', html);
                                                }
                                            });
                                        }
                                    });
                                    $g(clone).find('.ba-tooltip').each(function(){
                                        setTooltip($g(this).parent());
                                    });
                                    this.append(clone);
                                }
                            }
                        }
                    });
                    document.querySelector('.twin-view-right-sidebar').dataset.edit = json.id;
                    document.querySelectorAll('tr.active').forEach(function(tr){
                        tr.classList.remove('active');
                    });
                    $this.classList.add('active');
                    document.querySelectorAll('.ba-options-group-element [data-key]').forEach(function(el){
                        el.value = json[el.dataset.key];
                        app.removeAlertTooltip(el);
                    });
                    document.querySelectorAll('.twin-view-sidebar-header > span.disabled').forEach(function(el){
                        el.classList.remove('disabled');
                    });
                }
            });
        }).on('change', '.shipping-options select[data-settings="type"]', function(){
            $g('.shipping-type-options').hide();
            let length = $g('.'+this.value+'-shipping-type').css('display', '').length;
            if (length > 0) {
                $g('.shipping-type-options-label').css('display', '');
            } else {
                $g('.shipping-type-options-label').hide();
            }
        }).on('click', '.shipping-add-countries', function(){
            let list = this.closest('.shipping-countries-wrapper').querySelector('.shipping-countries-list');
            app.country.modal.querySelectorAll('.country-modal-body li').forEach(function(li){
                if (list.querySelector('.selected-items[data-id="'+li.dataset.value+'"]')) {
                    li.classList.add('disabled-country');
                } else {
                    li.classList.remove('disabled-country');
                }
            });
            app.country.showModal(this);
        }).on('change', '.shipping-add-countries', function(){
            let span = app.country.getShippingEl(this.dataset.value);
            $g(this).closest('.shipping-countries-wrapper').find('.shipping-countries-list').append(span);
        }).on('click', '.selected-regions-count', function(){
            fontBtn = this;
            let item = this.closest('.selected-items'),
                states = JSON.parse(item.dataset.regions),
                id = item.dataset.id,
                content = document.querySelector('template.states-list-li').content,
                modal = document.querySelector('#store-states-list-dialog'),
                ul = modal.querySelector('ul'),
                country = app.country.countries[id],
                obj = country.states;
            ul.innerHTML = '';
            modal.querySelector('.states-modal-header').textContent = country.title;
            for (let ind in obj) {
                let clone = content.cloneNode(true);
                clone.querySelector('.picker-item-title').textContent = obj[ind].title;
                clone.querySelectorAll('input').forEach(function(input){
                    input.dataset.id = ind;
                    input.checked = (ind in states) ? states[ind] : false;
                });
                ul.append(clone);
            }
            showDataTagsDialog('store-states-list-dialog', 0);
        }).on('change', '.label-toggle-btn', function(){
            this.closest('.ba-options-group-element').classList[this.checked ? 'remove' : 'add']('hidden-element-content');
        }).on('change', '#store-states-list-dialog input[type="checkbox"]', function(){
            let item = fontBtn.closest('.selected-items'),
                c = 0,
                states = JSON.parse(item.dataset.regions);
            states[this.dataset.id] = this.checked;
            for (let ind in states) {
                if (states[ind]) {
                    c++;
                }
            }
            item.querySelector('.selected-regions-count').dataset.count = c;
            item.dataset.regions = JSON.stringify(states);
        }).on('click', '.add-new-rate-by', function(){
            let clone = document.querySelector('template.rate-by-'+this.dataset.target).content.cloneNode(true);
            $g(clone).find('.ba-tooltip').each(function(){
                setTooltip($g(this).parent());
            });
            this.closest('.ba-rate-by-wrapper').querySelector('.ba-rate-by-list').append(clone);
        }).on('click', '.delete-up-to-rate-line', function(){
            $g('body > .ba-tooltip').remove();
            this.closest('.ba-rate-by-line').remove();
        }).on('click', '.apply-shipping', function(){
            if (!this.classList.contains('disabled')) {
                let required = ['title', 'price'],
                    alert = false,
                    params = {},
                    obj = {
                        id: this.closest('.twin-view-right-sidebar').dataset.edit
                    };
                document.querySelectorAll('.ba-options-group-element [data-key]').forEach(function($this){
                    obj[$this.dataset.key] = $this.value;
                    if (required.indexOf($this.dataset.key) != -1 && $this.value == '') {
                        let parent = $this.closest('.ba-options-group-element');
                        alert = true;
                        app.toggleAlertTooltip(alert, $this, parent, 'THIS_FIELD_REQUIRED');
                    }
                });
                if (alert) {
                    return false;
                }
                $g('.shipping-options [data-settings]').each(function(){
                    let shippingType = this.closest('.shipping-type-options');
                    if (shippingType && params.type != shippingType.dataset.type) {
                        return true;
                    }
                    if (this.type == 'checkbox') {
                        value = this.checked;
                    } else if (this.dataset.settings == 'type') {
                        value = this.value;
                    } else if (this.type == 'text') {
                        value = this.value;
                    } else if (this.dataset.cke) {
                        value = app.cke[this.dataset.group+'-'+this.dataset.settings].getData();
                    } else if (this.classList.contains('shipping-countries-list')) {
                        value = {};
                        this.querySelectorAll('.selected-items').forEach(function(item){
                            value[item.dataset.id] = JSON.parse(item.dataset.regions);
                        });
                    } else if (this.classList.contains('ba-rate-by-list')) {
                        value = {};
                        this.querySelectorAll('.ba-rate-by-line').forEach(function(line, i){
                            value[i] = {};
                            line.querySelectorAll('input').forEach(function(input){
                                value[i][input.dataset.ind] = input.value;
                            });
                            line.querySelectorAll('.selected-items-list').forEach(function(div){
                                let array = value[i][div.dataset.ind] = [];
                                div.querySelectorAll('.selected-items').forEach(function(span){
                                    array.push(span.dataset.id);
                                });
                            });
                        });
                    }
                    if (this.dataset.group && !params[this.dataset.group]) {
                        params[this.dataset.group] = {};
                    }
                    if (this.dataset.group) {
                        params[this.dataset.group][this.dataset.settings] = value;
                    } else {
                        params[this.dataset.settings] = value;
                    }
                });
                obj.options = JSON.stringify(params);
                makeFetchRequest('index.php?option=com_gridbox&task=shipping.updateShipping', obj).then(function(json){
                    if (json) {
                        reloadPage(json.message);
                    }
                });
            }
        }).on('click', '.add-category-rate', function(){
            fontBtn = this;
            document.querySelectorAll('#category-applies-dialog li').forEach(function(li){
                let exist = document.querySelector('span[data-id="'+li.dataset.id+'"]');
                li.classList[exist ? 'add' : 'remove']('selected');
            });
            showDataTagsDialog('category-applies-dialog', 15);
        }).on('change', '.add-category-rate', function(){
            let obj = JSON.parse(this.dataset.value),
                wrapper = this.closest('.ba-rate-by-line').querySelector('.selected-items-list'),
                html = '<span class="selected-items" data-id="'+obj.id+'">';
            html += '<span class="ba-item-thumbnail"';
            if (obj.image) {
                let image = obj.image.indexOf('balbooa.com') == -1 ? JUri+obj.image : obj.image;
                html += ' style="background-image: url('+image+');"';
            }
            html += '>';
            if (!obj.image) {
                html += '<i class="zmdi zmdi-folder"></i>';
            }
            html += '</span><span class="selected-items-name">';
            html += obj.title+'</span><i class="zmdi zmdi-close remove-selected-items"></i></span>';


            $g(wrapper).append(html);
        }).on('click', '.delete-shipping', function(){
            if (!this.classList.contains('disabled')) {
                document.querySelector('#context-item').value = this.closest('.twin-view-right-sidebar').dataset.edit;
                deleteMode = 'shipping.contextDelete';
                $g('#delete-dialog').modal();
            }
        });

        $g('body').on('click', '.ba-add-order', function(event){
            event.preventDefault();
            event.stopPropagation();
            let price = app.renderPrice(0),
                html = document.querySelector('.exist-customer-info-fields').content.cloneNode(true),
                footer = document.querySelector('.template-order-footer-total-wrapper').content.cloneNode(true),
                modal = $g('#create-new-order-dialog');
            app.prepareEmptyCart(modal);
            modal.find('.modal-header h3').text(app._('NEW_ORDER'));
            modal.find('.customer-info-wrapper .ba-options-group-wrapper').html(html);
            modal.find('.order-footer-total-wrapper').html(footer);
            modal.find('.order-coupon-code').removeAttr('data-value');
            modal.find('.sorting-container').empty();
            modal.find('.order-info-wrapper').find('input, textarea, select').val('');
            modal.find('.ba-cart-price-value').text(price);
            modal.removeClass('view-created-order').modal();
        }).on('mousedown', '.context-view-order', function(){
            currentContext.trigger('click');
        }).on('mousedown', '.context-download-order', function(){
            let id = currentContext.attr('data-id');
            $g('.download-exist-order[data-layout="pdf"]').attr('data-id', id).trigger('click');
        }).on('mousedown', '.context-delete-order', function(){
            document.querySelector('#context-item').value = currentContext.attr('data-id');
            deleteMode = 'orders.contextDelete';
            $g('#delete-dialog').modal();
        }).on('click', '.orders-list tbody tr', function(){
            let tr = this.closest('tr');
            makeFetchRequest('index.php?option=com_gridbox&task=orders.getOrder', {
                id: tr.dataset.id
            }).then(function(json){
                if (tr.classList.contains('unread-order')) {
                    tr.classList.remove('unread-order');
                    $g('.unread-comments-count[data-type="orders"]').each(function(){
                        let count = this.textContent - 1;
                        if (count) {
                            this.textContent = count;
                        } else {
                            this.remove();
                        }
                    });
                }
                let price = shipping = promo = '',
                    modal = $g('#create-new-order-dialog'),
                    taxes = {
                        count: 0
                    },
                    html = '',
                    footer = document.querySelector('.view-order-footer-total-wrapper').content.cloneNode(true),
                    container = modal.find('.sorting-container').empty(),
                    info = modal.find('.customer-info-wrapper .ba-options-group-wrapper').empty();
                modal.find('.modal-header h3').text(app._('ORDER_DETAILS'));
                modal.find('.orders-details-number').text(json.order_number);
                modal.find('.download-exist-order, .edit-order-status').attr('data-id', json.id);
                modal.find('.order-footer-total-wrapper').html(footer);
                $g('.edit-order-status').each(function(){
                    let $this = $g(this),
                        status = app.statuses[json.status] ? app.statuses[json.status] : app.statuses.undefined;
                    this.style.setProperty('--order-status-color', status.color)
                    $this.find('.order-status-title').text(status.title);
                });
                app.prepareEmptyCart(modal);
                json.info.forEach(function(obj){
                    if (obj.type != 'headline' && obj.type != 'acceptance') {
                        let search = '.customer-info-fields-pattern[data-type="'+obj.type+'"]',
                            settings = JSON.parse(obj.options),
                            content = document.querySelector(search).content.cloneNode(true),
                            infoData = content.querySelector('.customer-info-data'),
                            value = obj.value;
                        if (value == '') {
                            content.querySelector('.ba-options-group-element').classList.add('ba-hide-element');
                        }
                        if (obj.type == 'checkbox') {
                            value = value.replace(/; /g, '<br>');
                        }
                        content.querySelector('.customer-info-title').textContent = obj.title;
                        if (obj.type == 'country' && value) {
                            let object = JSON.parse(value);
                            infoData.innerHTML = object.country;
                            infoData = content.querySelector('.customer-info-data[data-type="region"]');
                            object.region != '' ? infoData.innerHTML = object.region : infoData.remove();
                        } else {
                            infoData.innerHTML = value;
                        }
                        content.querySelectorAll('[data-type="checkbox"], [data-type="radio"]').forEach(function(div){
                            let pattern = div.querySelector('.ba-checkbox-wrapper'),
                                clone = null;
                            settings.options.forEach(function(option){
                                clone = pattern.cloneNode(true);
                                clone.querySelector('label + span').textContent = option;
                                clone.querySelector('input').value = option
                                pattern.parentNode.insertBefore(clone, pattern);
                            });
                            pattern.remove();
                        })
                        content.querySelectorAll('input, textarea, select').forEach(function(input){
                            if (obj.type == 'email') {
                                input.required = true;
                            } else if (obj.customer_id == 1) {
                                input.dataset.customer = 1;
                            }
                            if (obj.type == 'textarea' || obj.type == 'email' || obj.type == 'text') {
                                input.value = obj.value;
                                input.placeholder = settings.placeholder ? settings.placeholder : '';
                            } else if (obj.type == 'country') {
                                let select = document.createElement('select'),
                                    option = document.createElement('option'),
                                    object = value ? JSON.parse(value) : {};
                                select.dataset.type = 'country';
                                input.parentNode.insertBefore(select, input);
                                option.value = '';
                                option.textContent = settings.placeholder;
                                select.append(option);
                                app.countries.forEach(function(country){
                                    option = document.createElement('option')
                                    option.value = country.id;
                                    option.textContent = country.title;
                                    select.append(option);
                                    if (country.title == object.country) {
                                        select.value = country.id;
                                        if (country.states.length > 0) {
                                            let select2 = document.createElement('select');
                                            select2.dataset.type = 'region';
                                            input.parentNode.insertBefore(select2, input);
                                            country.states.forEach(function(region){
                                                option = document.createElement('option')
                                                option.value = region.id;
                                                option.textContent = region.title;
                                                select2.append(option);
                                                if (region.title == object.region) {
                                                    select2.value = region.id;
                                                }
                                            });
                                        }
                                    }
                                });
                            } else if (obj.type == 'dropdown') {
                                let option = document.createElement('option');
                                option.value = '';
                                option.textContent = settings.placeholder;
                                input.append(option);
                                settings.options.forEach(function(title){
                                    option = document.createElement('option');
                                    option.value = title;
                                    option.textContent = title;
                                    input.append(option);
                                });
                                input.querySelectorAll('option').forEach(function(option){
                                    if (option.value == obj.value) {
                                        option.selected = true;
                                    }
                                })
                            } else if (obj.type == 'acceptance') {
                                input.value = obj.value;
                                input.checked = true;
                                content.querySelector('.ba-checkout-acceptance-html').innerHTML = settings.html;
                            } else if (obj.type == 'radio' && obj.value == input.value) {
                                input.checked = true;
                            } else if (obj.type == 'checkbox') {
                                let values = obj.value.split('; ');
                                values.forEach(function(val){
                                    if (val == input.value) {
                                        input.checked = true;
                                    }
                                })
                            }
                            input.name = obj.id;
                        });
                        info.append(content);
                    }
                });
                json.products.forEach(function(product){
                    let obj = {
                            id: product.product_id,
                            image: product.image,
                            info: product.info,
                            price: product.price / product.quantity,
                            quantity: product.quantity,
                            sale_price: product.sale_price != '' ? product.sale_price / product.quantity : '',
                            title: product.title,
                            variation: product.variation,
                            extra_options: product.extra_options,
                            extra: product.extra,
                            product_type: product.product_type
                        }
                    if (product.tax) {
                        let exist = false,
                            key = 0;
                        price = product.sale_price ? product.sale_price : product.price;
                        for (let ind in taxes) {
                            if (ind == 'count') {
                                continue;
                            }
                            if (taxes[ind].title == product.tax_title && taxes[ind].rate == product.tax_rate) {
                                taxes[ind].amount += product.tax * 1;
                                exist = true;
                                break;
                            }
                            key++;
                        }
                        if (!exist) {
                            taxes.count++;
                            taxes[key] = {
                                amount: product.tax * 1,
                                title: product.tax_title,
                                rate: product.tax_rate
                            }
                        }
                    }
                    html = app.getProductSortingHTML(obj, obj.quantity, json.currency_symbol, json.currency_position);
                    container.append(html);
                });
                if (json.payment && json.payment.type != 'admin') {
                    modal.find('.order-payment-method').css('display', '').find('.customer-info-data').text(json.payment.title);
                } else if (json.payment) {
                    modal.find('.order-payment-method').hide().find('.customer-info-data').text(json.payment.title);
                }
                if (json.shipping) {
                    shipping = json.shipping.title;
                }
                if (json.promo) {
                    promo = json.promo.title;
                }
                modal.find('.order-promo-code').css('display', (promo ? '' : 'none')).find('input').val(promo);
                modal.find('.order-shipping-method .customer-info-data').text(shipping);
                modal.find('.order-coupon-code').removeAttr('data-value');
                price = app.renderPrice(json.subtotal, json.currency_symbol, json.currency_position);
                modal.find('.order-subtotal-element .ba-cart-price-value').text(price);
                modal.find('.order-total-element, .order-shipping-element').attr('data-mode', json.tax_mode);
                if (json.shipping) {
                    price = app.renderPrice(json.shipping.price, json.currency_symbol, json.currency_position);
                    modal.find('.order-shipping-element .ba-cart-price-value').text(price);
                    price = app.renderPrice(json.shipping.tax, json.currency_symbol, json.currency_position);
                    if (json.tax_mode == 'incl') {
                        price = app._('INCLUDES')+' '+json.shipping.tax_title+' '+price;
                        json.tax = json.tax * 1 + json.shipping.tax * 1;
                        if (taxes.count == 1) {
                            for (let ind in taxes) {
                                if (ind == 'count') {
                                    continue;
                                }
                                if (taxes[ind].title != json.shipping.tax_title || taxes[0].rate != json.shipping.tax_rate) {
                                    taxes.count++;
                                }
                            }
                        }
                    }
                } else {
                    modal.find('.order-shipping-element, .order-shipping-tax-element').remove();
                }
                if (json.tax_mode == 'incl') {
                    modal.find('.order-shipping-tax-element[data-mode="excl"]').remove();
                    modal.find('.order-tax-element[data-mode="excl"]').remove();
                    modal.find('.order-shipping-tax-element label').text(price);
                } else {
                    modal.find('.order-shipping-tax-element[data-mode="incl"]').remove();
                    modal.find('.order-tax-element[data-mode="incl"]').remove();
                    modal.find('.order-shipping-tax-element .ba-cart-price-value').text(price);
                }
                if (!json.tax || json.tax == '0') {
                    modal.find('.order-tax-element').remove();
                    modal.find('.order-shipping-tax-element').remove();
                } else if (json.tax_mode == 'incl') {
                    price = app.renderPrice(json.tax, json.currency_symbol, json.currency_position);
                    modal.find('.order-total-element').each(function(){
                        let title = taxes.count == 1 ? app._('INCLUDES')+' '+taxes[0].rate+'% '+taxes[0].title : app._('INCLUDING_TAXES');
                        title += ' '+price;
                        this.querySelector('.order-tax-element label').textContent = title;
                    });
                } else if (taxes.count != 0) {
                    let taxElement = modal.find('.order-tax-element').remove(),
                        clone = null;
                    for (let ind in taxes) {
                        if (ind == 'count') {
                            continue;
                        }
                        clone = taxElement.clone();
                        clone.find('.ba-options-group-label').text(taxes[ind].title);
                        price = app.renderPrice(taxes[ind].amount, json.currency_symbol, json.currency_position);
                        clone.find('.ba-cart-price-value').text(price);
                        modal.find('.order-total-element').before(clone);
                    }
                } else {
                    price = app.renderPrice(json.tax, json.currency_symbol, json.currency_position);
                    modal.find('.order-tax-element .ba-cart-price-value').text(price)
                }
                price = app.renderPrice(json.total, json.currency_symbol, json.currency_position)
                modal.find('.order-total-element .ba-cart-price-value').text(price);
                if (json.promo) {
                    price = app.renderPrice(json.promo.value, json.currency_symbol, json.currency_position);
                    modal.find('.order-discount-element .ba-cart-price-value').text(price);
                } else {
                    modal.find('.order-discount-element').remove();
                }
                modal.find('.order-shipping-method').each(function(){
                    if (json.shipping && this.classList.contains('ba-hide-element')) {
                        this.classList.add('ba-visible-element');
                        this.closest('.ba-options-group-wrapper').classList.add('ba-visible-element');
                    }
                });
                modal.find('.order-promo-code').each(function(){
                    if (json.promo && this.classList.contains('ba-hide-element')) {
                        this.classList.add('ba-visible-element');
                        this.closest('.ba-options-group-wrapper').classList.add('ba-visible-element');
                    }
                });
                modal.find('.order-payment-method').each(function(){
                    if (json.payment && json.payment.type != 'admin') {
                        this.closest('.ba-options-group-wrapper').classList.add('ba-visible-element');
                    }
                });
                modal.addClass('view-created-order').modal();
                app.currentOrder = json;
            });
        });

        $g('#create-new-order-dialog').on('show', function(){
            if (!app.store.promos) {
                makeFetchRequest('index.php?option=com_gridbox&task=promocodes.getPromoCodes').then(function(json){
                    app.store.promos = json;
                    getProductsHtml(document.querySelector('#order-coupon-code-dialog'), json, '');
                });
            }
            document.body.style.setProperty('--body-scroll-width', (window.innerWidth - document.documentElement.clientWidth)+'px');
            document.body.classList.add('modal-wrapper-opened');
            $g(this).find('[required], .ba-options-group-sorting-wrapper').each(function(){
                app.removeAlertTooltip(this);
            });
        }).on('hide', function(){
            $g(this).removeClass('edit-created-order').find('.ba-visible-element').removeClass('ba-visible-element');
            setTimeout(function(){
                document.body.classList.remove('modal-wrapper-opened');
                document.body.style.removeProperty('--body-scroll-width');
            }, 500);
        }).on('click', '.edit-exist-order', function(){
            let modal = $g(this).closest('.modal'),
                footer = document.querySelector('.template-order-footer-total-wrapper').content.cloneNode(true);
            app.prepareEmptyCart(modal);
            modal.find('.order-footer-total-wrapper').html(footer);
            app.cart.order_id = app.currentOrder.id;
            app.currentOrder.products.forEach(function(product){
                let search = '.sorting-item[data-id="'+product.product_id+'"]',
                    item = modal.find(search+(product.variation ? '[data-variation="'+product.variation+'"]' : ''));
                if (!product.data) {
                    item.remove();
                } else {
                    product.data.quantity = product.quantity;
                    if (product.data.stock != '' && product.data.quantity * 1 > product.data.stock * 1) {
                        product.data.quantity = product.data.stock * 1;
                    }
                    let html = app.getProductSortingHTML(product.data, product.quantity),
                        key = product.data.variation ? product.data.variation : product.data.id;
                    app.cart.products[key] = $g.extend(true, {}, product.data);
                    app.cart.products[key].db_id = product.id;
                    item.replaceWith(html);
                    modal.find('.ba-tooltip').each(function(){
                        setTooltip($g(this).parent())
                    });
                }
            });
            if (app.currentOrder.shipping) {
                for (let i = 0; i < app.store.shipping.length; i++) {
                    if (app.store.shipping[i].id == app.currentOrder.shipping.shipping_id) {
                        modal.find('.order-shipping-method select').val(i);
                        app.cart.shipping = $g.extend(true, {}, app.store.shipping[i]);
                        app.cart.shipping.db_id = app.currentOrder.shipping.id;
                        break;
                    }
                }
            }
            if (app.currentOrder.promo) {
                for (let i = 0; i < app.store.promos.length; i++) {
                    if (app.store.promos[i].id == app.currentOrder.promo.promo_id) {
                        modal.find('.order-promo-code input').val(app.store.promos[i].title);
                        app.cart.promo = $g.extend(true, {}, app.store.promos[i]);
                        app.cart.promo.db_id = app.currentOrder.promo.id;
                        break;
                    }
                }
                if (!app.cart.promo) {
                    modal.find('.order-promo-code input').val('');
                }
            }
            modal.find('.customer-info-wrapper .ba-hide-element').removeClass('ba-hide-element');
            app.calculateOrder();
            modal.addClass('edit-created-order').find('.order-promo-code').css('display', '');
        }).on('change', '.ba-options-group-element[data-type="country"] select[data-type="country"]', function(){
            let parent = $g(this).closest('.ba-options-group-element'),
                value = this.value;
            parent.find('select[data-type="region"]').remove();
            app.countries.forEach(function(country){
                if (country.id == value) {
                    let select = document.createElement('select');
                    select.dataset.type = 'region';
                    country.states.forEach(function(region){
                        let option = document.createElement('option');
                        option.value = region.id;
                        option.textContent = region.title;
                        select.append(option);
                    });
                    parent.append(select);
                }
            });
            app.calculateOrder();
        }).on('change', '.ba-options-group-element[data-type="country"] select[data-type="region"]', function(){
            app.calculateOrder();
        }).on('click', '.download-exist-order', function(){
            let iframe = document.createElement('iframe');
            iframe.className = 'download-exist-order-iframe';
            document.body.appendChild(iframe);
            iframe.src = JUri+'administrator/index.php?option=com_gridbox&view=orders&layout='+
                this.dataset.layout+'&tmpl=component&id='+this.dataset.id
        }).on('click', '.ba-options-group-toolbar label.add-order-product', function(){
            fontBtn = this;
            $g(this).closest('.ba-options-group-sorting-wrapper').each(function(){
                app.removeAlertTooltip(this);
            });
            let modal = document.getElementById('product-applies-dialog');
            if (!modal.dataset.loaded) {
                makeFetchRequest('index.php?option=com_gridbox&task=promocodes.getProducts', {
                    category: 1
                }).then(function(json){
                    getProductsHtml(modal, json, 'product');
                    modal.dataset.loaded = 'loaded';
                    showAppliesModal(modal);
                });
            } else {
                showAppliesModal(modal);
            }
        }).on('click', 'label.delete-order-product', function(){
            if (!this.classList.contains('disabled')) {
                deleteMode = 'delete-order-cart-item';
                $g('#delete-dialog').modal();
            }
        }).on('change', '.ba-options-group-toolbar label.add-order-product', function(){
            let obj = JSON.parse(this.dataset.value),
                key = obj.variation ? obj.variation : obj.id,
                str = app.getProductSortingHTML(obj, 1);
            obj.quantity = 1;
            $g('.order-info-wrapper .sorting-container').append(str)
            app.cart.products[key] = obj;
            app.calculateOrder();
            app.cart.modal.find('.ba-tooltip').each(function(){
                setTooltip($g(this).parent())
            });
        }).on('click', '.ba-add-product-extra-option', function(){
            if (this.classList.contains('disabled')) {
                return false;
            }
            let item = this.checkbox.closest('.sorting-item'),
                modal = document.querySelector('#extra-options-dialog'),
                ul = modal.querySelector('ul'),
                ind = item.dataset.variation ? item.dataset.variation : item.dataset.id,
                obj = app.cart.products[ind];
            ul.innerHTML = '';
            for (let id in obj.extra) {
                let row = item.querySelector('.ba-product-extra-option-row[data-ind="'+id+'"]');
                if (obj.extra[id].type != 'checkbox' && row) {
                    continue;
                }
                for (let key in obj.extra[id].items) {
                    if (row && row.querySelector('.ba-product-extra-option[data-key="'+key+'"]')) {
                        continue;
                    }
                    let li = document.createElement('li'),
                        extra = obj.extra[id].items[key],
                        price = extra.price ? app.renderPrice(extra.price) : '',
                        data = {
                            id: id,
                            key: key
                        };
                    li.dataset.value = JSON.stringify(data);
                    li.innerHTML = '<span class="picker-item-title"><span class="ba-picker-item-title">'+
                        obj.extra[id].title+': '+extra.title+'</span></span><span class="picker-item-price">'+price+'</span>';
                    ul.append(li);
                }
            }
            modal.querySelector('input.picker-search').value = '';
            fontBtn = this;
            showDataTagsDialog('extra-options-dialog');
        }).on('change', '.ba-add-product-extra-option', function(){
            let item = this.checkbox.closest('.sorting-item'),
                ind = item.dataset.variation ? item.dataset.variation : item.dataset.id,
                obj = app.cart.products[ind],
                str = '',
                div = document.createElement('div'),
                data = JSON.parse(this.dataset.value);
            if (!obj.extra_options.items) {
                obj.extra_options = {
                    count: 0,
                    price: 0,
                    items: {}
                }
            }
            if (!obj.extra_options.items[data.id]) {
                obj.extra_options.items[data.id] = {
                    title: obj.extra[data.id].title,
                    required: obj.extra[data.id].required == '1',
                    values: {}
                }
            }
            obj.extra_options.items[data.id].values[data.key] = {
                price: obj.extra[data.id].items[data.key].price,
                value: obj.extra[data.id].items[data.key].title
            }
            obj.extra_options.count++;
            if (obj.extra[data.id].items[data.key].price) {
                obj.extra_options.price += obj.extra[data.id].items[data.key].price * 1;
            }
            str = app.getProductSortingHTML(obj, obj.quantity);
            app.calculateOrder();
            div.innerHTML = str;
            this.checkbox = div.querySelector('input[type="checkbox"]');
            this.checkbox.checked = true;
            $g(item).replaceWith(div.querySelector('.sorting-item'));
        }).on('click', '.ba-product-delete-extra-option i', function(){
            let option = this.closest('.ba-product-extra-option'),
                row = option.closest('.ba-product-extra-option-row'),
                item = row.closest('.sorting-item'),
                ind = item.dataset.variation ? item.dataset.variation : item.dataset.id,
                obj = app.cart.products[ind],
                object = obj.extra_options.items[row.dataset.ind].values[option.dataset.key],
                str = '';
            if (object.price) {
                obj.extra_options.price -= object.price;
            }
            obj.extra_options.count--;
            delete obj.extra_options.items[row.dataset.ind].values[option.dataset.key];
            option.remove();
            if (!row.querySelector('.ba-product-extra-option[data-key]')) {
                delete obj.extra_options.items[row.dataset.ind];
                row.remove();
                
            }
            str = app.getProductSortingHTML(obj, obj.quantity);
            app.calculateOrder();
            $g(item).replaceWith(str);
        }).on('input', '.sorting-quantity input', function(){
            let item = this.closest('.sorting-item'),
                input = item.querySelector('input[type="checkbox"]'),
                key = input.dataset.variation ? input.dataset.variation : input.value,
                product = app.cart.products[key],
                quantity = this.value * 1,
                extraPrice = price = null;
            if (this.value && product.stock != '' && product.stock < quantity) {
                this.value = quantity = product.stock * 1;
            }
            if (this.value && quantity < 1) {
                this.value = quantity = 1;
            }
            if (this.value) {
                clearTimeout(this.delay);
                this.delay = setTimeout(function(){
                    product.quantity = quantity;
                    app.calculateOrder();
                }, 300);
                extraPrice = product.extra_options.price ? product.extra_options.price * quantity : 0;
                price = app.renderPrice(product.price * quantity + extraPrice);
                if (product.sale_price !== '') {
                    item.querySelector('.ba-cart-sale-price-wrapper .ba-cart-price-value').textContent = price;
                    price = app.renderPrice(product.sale_price * quantity + extraPrice);
                }
                item.querySelector('.ba-cart-price-wrapper .ba-cart-price-value').textContent = price;
                item.querySelectorAll('.ba-cart-product-extra-option-price').forEach(function(extra){
                    if (extra.dataset.price) {
                        price = app.renderPrice(extra.dataset.price * quantity);
                        extra.textContent = price;
                    }
                })
            }
        }).on('click', '.order-coupon-code', function(){
            fontBtn = this;
            showDataTagsDialog('order-coupon-code-dialog');
        }).on('change', '.order-coupon-code', function(){
            app.cart.promo = this.dataset.value ? JSON.parse(this.dataset.value) : null;
            this.closest('.ba-options-input-action-wrapper').querySelector('input').value = app.cart.promo ? app.cart.promo.code : '';
            app.calculateOrder();
        }).on('click', '.reset-coupon-code', function(){
            $g('.order-coupon-code').removeAttr('data-value').trigger('change');
        }).on('change', '.select-order-shipping', function(){
            app.cart.shipping = this.value ? app.store.shipping[this.value] : null;
            app.calculateOrder();
        }).on('focus change', '[required]', function(){
            app.removeAlertTooltip(this);
        }).on('click', '.save-order-cart', function(){
            if (this.clicked) {
                return false;
            }
            let modal = $g('#create-new-order-dialog'),
                not = '[type="checkbox"], [type="radio"]';
            modal.find('input[required], textarea[required], select[required]').not(not).each(function(){
                if (!this.closest('.ba-hide-element')) {
                    let alert = !this.value.trim(),
                        key = 'THIS_FIELD_REQUIRED';
                    if (this.value && this.type == 'email') {
                        alert = !(/@/g.test(this.value) && this.value.match(/@/g).length == 1);
                        key = 'ENTER_VALID_VALUE';
                    }
                    app.toggleAlertTooltip(alert, this, this.closest('.ba-options-group-element'), key);
                }
            });
            modal.find('[data-type="checkbox"], [data-type="radio"], [data-type="acceptance"]').each(function(){
                let alert = this.querySelector('input[required]') ? true : false;
                this.querySelectorAll('input[type="radio"][required], input[type="checkbox"][required]').forEach(function($this){
                    if ($this.checked) {
                        alert = false;
                    }
                });
                app.toggleAlertTooltip(alert, this, this, 'THIS_FIELD_REQUIRED');
            });
            modal.find('.ba-options-group-sorting-wrapper').each(function(){
                let alert = this.querySelectorAll('.sorting-item').length == 0,
                    key = 'THIS_FIELD_REQUIRED';
                app.toggleAlertTooltip(alert, this, this, key);
            });
            let action = app.cart.order_id ? 'updateOrder' : 'createOrder',
                obj = $g.extend(true, {}, app.cart),
                alert = modal.find('.ba-alert'),
                $this = this,
                wrapper = $g('.customer-info-wrapper'),
                str = app._('SAVING')+'<img src="'+JUri;
            if (alert.length) {
                alert[0].scrollIntoView(true);
                return false;
            }
            obj.info = {};
            wrapper.find('input[name], textarea[name], select[name]').not(not).each(function(){
                let parent = this.closest('.ba-options-group-element'),
                    value = this.value.trim();
                if (this.type == 'hidden' && parent.dataset.type == 'country') {
                    let country = parent.querySelector('select[data-type="country"]'),
                        region = parent.querySelector('select[data-type="region"]'),
                        object = {
                            country: country.value,
                            region: region ? region.value : ''
                        };
                    value = JSON.stringify(object);
                }
                obj.info[this.name] = value;
            });
            wrapper.find('[data-type="checkbox"], [data-type="radio"], [data-type="acceptance"]').each(function(){
                let values = [],
                    name = '';
                this.querySelectorAll('input').forEach(function(input){
                    name = input.name;
                    if (input.checked) {
                        values.push(input.value);
                    }
                });
                obj.info[name] = values.join('; ');
            });
            this.clicked = true;
            delete(obj.modal);
            str += 'administrator/components/com_gridbox/assets/images/reload.svg"></img>';
            notification[0].className = 'notification-in';
            notification.find('p').html(str);
            makeFetchRequest('index.php?option=com_gridbox&task=orders.'+action, {
                data: JSON.stringify(obj)
            }).then(function(json){
                $this.clicked = false;
                if (app.cart.order_id) {
                    showNotice(app._('SAVE_SUCCESS'));
                    let total = app.cart.modal.find('.order-total-element .ba-cart-price-value').text(),
                        email = name = '';
                    app.cart.modal.find('.customer-info-wrapper .ba-options-group-element').each(function(){
                        let input = this.querySelector('input, textarea, select'),
                            text = input.value.trim();
                        this.querySelector('.customer-info-data').textContent = text;
                        if (input.type == 'email') {
                            email = text;
                        } else if (input.dataset.customer == 1) {
                            name = text;
                        }
                        if (!text) {
                            this.classList.add('ba-hide-element');
                        }
                    });
                    app.cart.modal.find('.order-shipping-method').each(function(){
                        let select = this.querySelector('select'),
                            text = select.value == '' ? '' : select.querySelector('option[value="'+select.value+'"]').textContent;
                        this.querySelector('.customer-info-data').textContent = text;
                    });
                    app.cart.modal.removeClass('edit-created-order');
                    $g('tr[data-id="'+app.currentOrder.id+'"]').each(function(){
                        this.querySelector('.customer-td').textContent = name;
                        this.querySelector('.email-td').textContent = email;
                        this.querySelector('.total-td').textContent = total;
                    });
                } else {
                    app.cart.modal.modal('hide');
                    reloadPage(app._('ITEM_CREATED'));
                }
            });
        });

        $g('body').on('click', '.product-options-table tbody tr', function(){
            let $this = this;
            makeFetchRequest('index.php?option=com_gridbox&task=productoptions.getOptions', {
                id: this.dataset.id
            }).then(function(json){
                if (json) {
                    let settings = JSON.parse(json.options),
                        container = document.querySelector('.sorting-container');
                    document.querySelector('.twin-view-right-sidebar').dataset.edit = json.id;
                    document.querySelectorAll('tr.active').forEach(function(tr){
                        tr.classList.remove('active');
                    });
                    document.querySelectorAll('.twin-view-sidebar-header > span.disabled').forEach(function(el){
                        el.classList.remove('disabled');
                    });
                    document.querySelectorAll('.ba-options-group-toolbar label[data-action="delete"]').forEach(function(el){
                        el.classList.add('disabled');
                    });
                    $this.classList.add('active');
                    document.querySelectorAll('.ba-options-group-element [data-key]').forEach(function(el){
                        if (el.type == 'checkbox') {
                            el.checked = Boolean(json[el.dataset.key] * 1);
                        } else {
                            el.value = json[el.dataset.key];
                        }
                    });
                    container.innerHTML = '';
                    container.classList.remove('color-picker-sorting-item');
                    container.classList.remove('image-picker-sorting-item');
                    if (json.field_type == 'image' || json.field_type == 'color') {
                        container.classList.add(json.field_type+'-picker-sorting-item');
                    }
                    settings.forEach(function(obj){
                        container.append(getSortingItem(obj));
                    });
                }
            });
        }).on('click', '.ba-options-group-toolbar label[data-action="add"]:not(.add-order-product)', function(){
            let obj = app.objects[this.dataset.object];
            obj.key = +(new Date());
            this.closest('.ba-options-group-element').querySelector('.sorting-container').append(getSortingItem(obj))
        }).on('change', 'select[data-key="field_type"]', function(){
            let container = document.querySelector('.sorting-container');
            container.classList.remove('color-picker-sorting-item');
            container.classList.remove('image-picker-sorting-item');
            if (this.value == 'image' || this.value == 'color') {
                container.classList.add(this.value+'-picker-sorting-item');
            }
        }).on('click', '.sorting-image-picker', function(){
            fontBtn = this;
            uploadMode = 'sortingImage';
            checkIframe($g('#uploader-modal'), 'uploader');
        }).on('change', '.sorting-checkbox input', function(){
            let checked = {
                    count: 0,
                    checkbox: null,
                    flag: false
                };
            if (this.dataset.ind == 'new' || this.dataset.ind == 'completed' || this.dataset.ind == 'refunded') {
                this.checked = false;
                return false;
            }
            this.closest('.sorting-container').querySelectorAll('.sorting-checkbox input').forEach(function($this){
                if ($this.checked) {
                    checked.flag = $this.checked;
                    checked.count++;
                    checked.checkbox = $this;
                }
            });
            this.closest('.ba-options-group-element').querySelectorAll('label[data-action="delete"]').forEach(function($this){
                $this.classList[checked.flag ? 'remove' : 'add']('disabled');
            });
            if (this.name == 'product' && checked.count == 1) {
                let item = checked.checkbox.closest('.sorting-item'),
                    btn = document.querySelector('.ba-add-product-extra-option'),
                    ind = item.dataset.variation ? item.dataset.variation : item.dataset.id,
                    obj = app.cart.products[ind];
                if (Object.keys(obj.extra).length != 0 && checked.flag) {
                    btn.classList.remove('disabled');
                    btn.checkbox = checked.checkbox;
                } else {
                    btn.classList.add('disabled');
                }
            }
        }).on('click', '.ba-options-group-toolbar label[data-action="delete"]:not(.delete-order-product)', function(){
            if (!this.classList.contains('disabled')) {
                deleteMode = {
                    type: 'delete-sorting-item',
                    container: this.closest('.ba-options-group-element').querySelector('.sorting-container'),
                    btn: this
                }
                $g('#delete-dialog').modal();
            }
        }).on('click', '.apply-product-options', function(){
            if (!this.classList.contains('disabled')) {
                let obj = {
                        id: this.closest('.twin-view-right-sidebar').dataset.edit
                    },
                    options = [];
                document.querySelectorAll('.ba-options-group-element [data-key]').forEach(function($this){
                    obj[$this.dataset.key] = $this.type == 'checkbox' ? Number($this.checked) : $this.value;
                });
                document.querySelectorAll('.ba-options-group-sorting-wrapper .sorting-item').forEach(function($this){
                    let item = {
                        title: $this.querySelector('.sorting-title input').value.trim(),
                        image: $this.querySelector('.sorting-image-picker').dataset.image,
                        color: $this.querySelector('.sorting-color-picker input').dataset.rgba,
                        key: $this.querySelector('input[type="checkbox"]').dataset.ind
                    }
                    options.push(item);
                });
                obj.options = JSON.stringify(options);
                makeFetchRequest('index.php?option=com_gridbox&task=productoptions.updateProductoptions', obj).then(function(json){
                    if (json) {
                        reloadPage(json.message);
                    }
                });
            }
        }).on('click', '.delete-product-options', function(){
            if (!this.classList.contains('disabled')) {
                document.querySelector('#context-item').value = this.closest('.twin-view-right-sidebar').dataset.edit;
                deleteMode = 'productoptions.contextDelete';
                $g('#delete-dialog').modal();
            }
        });

        $g('body').on('click', '.promo-codes-table tbody tr', function(){
            let $this = this,
                data = {
                    id: this.dataset.id
                };
            makeFetchRequest('index.php?option=com_gridbox&task=promocodes.getOptions', data).then(function(json){
                if (json) {
                    document.querySelector('.twin-view-right-sidebar').dataset.edit = json.id;
                    document.querySelectorAll('tr.active').forEach(function(tr){
                        tr.classList.remove('active');
                    });
                    $this.classList.add('active');
                    let decimals = 0,
                        symbol = '%';
                    document.querySelectorAll('.ba-options-group-element [data-key]').forEach(function(el){
                        if (el.type == 'checkbox') {
                            el.checked = Boolean(json[el.dataset.key] * 1);
                        } else {
                            el.value = json[el.dataset.key];
                        }
                        app.removeAlertTooltip(el);
                        if (el.dataset.key == 'unit') {
                            decimals = json.unit == '%' ? 0 : el.dataset.decimals;
                            symbol = json.unit == '%' ? '%' : el.dataset.symbol;
                        }
                    });
                    document.querySelector('.ba-options-price-currency').textContent = symbol;
                    document.querySelector('.coupon-type-select input').dataset.decimals = decimals;
                    $g('.coupon-type-select input').trigger('input');
                    document.querySelectorAll('.twin-view-sidebar-header > span.disabled').forEach(function(el){
                        el.classList.remove('disabled');
                    });
                    prepareCouponApplies(json.applies_to);
                    json.map.forEach(function(obj){
                        createSelectedApplies(obj, json.applies_to);
                    });
                }
            });
        }).on('change', '.coupon-type-select select', function(){
            let symbol = this.value == '%' ? '%' : this.dataset.symbol,
                decimals = this.value == '%' ? 0 : this.dataset.decimals;
            this.closest('.coupon-type-select').querySelector('.ba-options-price-currency').textContent = symbol;
            document.querySelector('.coupon-type-select input').dataset.decimals = decimals;
            $g('.coupon-type-select input').trigger('input');
        }).on('change', '.ba-options-group-applies-wrapper select', function(){
            prepareCouponApplies(this.value);
        }).on('click', '.ba-options-applies-wrapper i', function(){
            fontBtn = this;
            let modal = document.getElementById(this.dataset.modal);
            if (!modal.dataset.loaded) {
                let url = 'index.php?option=com_gridbox&task=promocodes.get';
                url += fontBtn.dataset.type == 'category' ? 'Categories' : 'Products';
                makeFetchRequest(url).then(function(json){
                    getProductsHtml(modal, json, fontBtn.dataset.type);
                    modal.dataset.loaded = 'loaded';
                    showAppliesModal(modal)
                });
            } else {
                showAppliesModal(modal);
            }
        }).on('change', '.ba-options-applies-wrapper i', function(){
            let obj = JSON.parse(this.dataset.value);
            createSelectedApplies(obj, this.dataset.type);
        }).on('click', '.remove-selected-items', function(){
            this.closest('.selected-items').remove();
        }).on('click', '.apply-promo-code', function(){
            if (!this.classList.contains('disabled')) {
                let required = ['title', 'code', 'discount'],
                    alert = false,
                    obj = {
                        id: this.closest('.twin-view-right-sidebar').dataset.edit
                    },
                    map = [];
                document.querySelectorAll('.ba-options-group-element [data-key]').forEach(function($this){
                    obj[$this.dataset.key] = $this.type == 'checkbox' ? Number($this.checked) : $this.value;
                    if (required.indexOf($this.dataset.key) != -1 && $this.value == '') {
                        let parent = $this.closest('.ba-options-price-wrapper, .ba-options-group-element');
                        alert = true;
                        app.toggleAlertTooltip(alert, $this, parent, 'THIS_FIELD_REQUIRED');
                    }
                });
                if (alert) {
                    return false;
                }
                document.querySelectorAll('.selected-applies').forEach(function($this){
                    map.push({
                        id: $this.dataset.id,
                        variation: $this.dataset.variation ? $this.dataset.variation : ''
                    });
                });
                obj.map = JSON.stringify(map);
                makeFetchRequest('index.php?option=com_gridbox&task=promocodes.updatePromoCode', obj).then(function(json){
                    if (json) {
                        reloadPage(json.message);
                    }
                });
            }
        }).on('click', '.duplicate-promo-code', function(){
            if (!this.classList.contains('disabled')) {
                document.querySelector('#context-item').value = this.closest('.twin-view-right-sidebar').dataset.edit;
                Joomla.submitbutton('promocodes.contextDuplicate');
            }
        }).on('click', '.delete-promo-code', function(){
            if (!this.classList.contains('disabled')) {
                document.querySelector('#context-item').value = this.closest('.twin-view-right-sidebar').dataset.edit;
                deleteMode = 'promocodes.contextDelete';
                $g('#delete-dialog').modal();
            }
        });

        $g('.store-email-options-filter').on('change', function(){
            $g(this).closest('.ba-options-group-wrapper').find('> div[class*="-email-options"]').hide();
            $g(this).closest('.ba-options-group-wrapper').find('.'+this.value+'-email-options').css('display', '');
        });

        $g('.set-resized-ckeditor-data').on('click', function(event){
            event.preventDefault();
            let data = app.cke.resized.getData();
            this.ckeditor.setData(data)
            $g('#resized-ckeditor-dialog').modal('hide');
        });

        $g('input[data-group="checkout"]').each(function(){
            let $this = this;
            this.checkout = {};
            document.querySelectorAll('input[data-group="checkout"]').forEach(function(input){
                $this.checkout[input.dataset.key] = input;
            });
        }).on('change', function(){
            if (!this.checkout.login.checked && !this.checkout.guest.checked) {
                this.checkout.guest.checked = true;
            }
            let flag = this.checkout.login.checked
            this.checkout.registration.closest('.ba-options-group-element').style.display = flag ? '' : 'none';
            flag = flag && this.checkout.registration.checked;
            this.checkout.terms.closest('.ba-options-group-element').style.display = flag ? '' : 'none';
            flag = flag && this.checkout.terms.checked;
            this.checkout.terms_text.closest('.ba-options-group-element').style.display = flag ? '' : 'none';
        });

        $g('.apply-store-settings').on('click', function(){
            let data = {
                id: this.dataset.id
            };
            document.querySelectorAll('.store-settings-table [data-key]').forEach(function($this){
                let value = $this.value;
                if (!data[$this.dataset.group]) {
                    data[$this.dataset.group] = {}
                }
                if ($this.type == 'checkbox') {
                    value = $this.checked;
                } else if ($this.closest('.ckeditor-options-wrapper')) {
                    value = app.cke[$this.dataset.group+'-'+$this.dataset.key].getData();
                }
                data[$this.dataset.group][$this.dataset.key] = value;
            });
            data.sales.map = [];
            document.querySelectorAll('.selected-applies').forEach(function($this){
                data.sales.map.push($this.dataset.id);
            });
            data.notification.admins = [];
            data.stock.admins = [];
            data.statuses = [];
            data.tax.rates = [];
            document.querySelectorAll('.entered-emails-wrapper[data-group="notification"] .entered-emails').forEach(function($this){
                data.notification.admins.push($this.dataset.email);
            });
            document.querySelectorAll('.entered-emails-wrapper[data-group="stock"] .entered-emails').forEach(function($this){
                data.stock.admins.push($this.dataset.email);
            });
            $g('#store-order-statuses-options .sorting-item').each(function(){
                data.statuses.push({
                    "title": this.querySelector('.sorting-title input').value,
                    "color": this.querySelector('.sorting-color-picker input').dataset.rgba,
                    "key": this.querySelector('.sorting-checkbox input').dataset.ind
                });
            });
            $g('#store-tax-options .sorting-container .sorting-item').each(function(){
                let $this = this,
                    country = this.querySelector('.sorting-tax-country .selected-items'),
                    obj = {
                        title: this.querySelector('.sorting-title input').value.trim(),
                        rate: this.querySelector('.sorting-tax-rate input:nth-child(1)').value.trim(),
                        categories: [],
                        country_id: country ? country.dataset.id : '',
                        regions: [],
                        shipping: Boolean(this.querySelector('.show-more-tax-options').dataset.shipping * 1)
                    }
                this.querySelectorAll('.sorting-tax-category-wrapper .selected-items').forEach(function(category){
                    obj.categories.push(category.dataset.id);
                });
                this.querySelectorAll('.tax-country-state .selected-items').forEach(function(state){
                    let ind = $g(state).index();
                    obj.regions.push({
                        state_id: state.dataset.id,
                        rate: $this.querySelector('.sorting-tax-rate input:nth-child('+(ind + 1)+')').value
                    });
                });
                data.tax.rates.push(obj);
            });
            for (let ind in data) {
                if (typeof data[ind] == 'object') {
                    data[ind] = JSON.stringify(data[ind]);
                }
            }
            makeFetchRequest('index.php?option=com_gridbox&task=storesettings.updateSettings', data).then(function(json){
                if (json) {
                    showNotice(json.message);
                }
            });
        });

        $g('.ba-add-email-action').on('keyup', function(event){
            let wrapper = this.closest('.ba-options-group-element').querySelector('.entered-emails-wrapper');
            if (event.keyCode == 13 && /@/g.test(this.value) && this.value.match(/@/g).length == 1
                && !wrapper.querySelector('.entered-emails[data-email="'+this.value+'"]')) {
                let html = '<span class="selected-items-name">'+this.value+'</span><i class="zmdi zmdi-close remove-selected-items"></i>',
                    span = document.createElement('span');
                span.className = 'entered-emails selected-items';
                span.dataset.email = this.value;
                span.innerHTML = html;
                wrapper.append(span);
                this.value = '';
            }
        });

        $g('.picker-search').on('input', function(){
            let search = this.value.toLowerCase(),
                li = this.closest('div.modal-list-type-wrapper').querySelectorAll('li[data-value]');
            clearTimeout(this.delay);
            this.delay = setTimeout(function(){
                li.forEach(function($this){
                    let title = $this.textContent.toLowerCase();
                    $this.style.display = search == '' || title.indexOf(search) != -1 ? '' : 'none';
                });
            }, 300);
        });

        app.country.load();

        $g('.ba-modal-list-picker').on('click', '.prevent-event', function(event){
            event.preventDefault();
            event.stopPropagation();
        }).on('click', '.picker-item-action-icon[data-action]', function(){
            app[this.dataset.wrapper][this.dataset.action](this);
        }).on('click', '.add-new-country', function(){
            app.country.addCountry(this);
        }).on('click', '.add-new-state', function(){
            app.states.addState(this);
        }).on('click', '.states-back-wrapper', function(){
            app.states.back();
        }).on('click', 'li', function(){
            if (!this.classList.contains('prevent-event') && !this.classList.contains('disabled-country')) {
                fontBtn.dataset.value = this.dataset.value;
                $g(fontBtn).trigger('change');
                $g(this).closest('.ba-modal-list-picker').modal('hide');
            }
        });

        $g('body').on('click', '.copy-to-clipboard', function(event){
            var textarea = document.createElement('textarea');
            document.body.appendChild(textarea);
            textarea.value = this.previousElementSibling.value;
            textarea.select()
            document.execCommand('copy');
            textarea.remove();
            showNotice(app._('SUCCESSFULLY_COPIED_TO_CLIPBOARD'));
        }).on('input', 'input.integer-validation', function(){
            let decimals = this.dataset.decimals * 1,
                max = decimals > 0 ? 1 : 0,
                match = this.value.match(new RegExp('\\d+\\.{0,'+max+'}\\d{0,'+decimals+'}'));
            if (!match) {
                this.value = '';
            } else if (match[0] != this.value) {
                this.value = match[0];
            }
        });

        $g('body').on('click', '.dashboard-view-media-manager', function(event){
            event.preventDefault();
            checkIframe($g('#uploader-modal'), 'uploader');
        });

        $g('body').on('click', '.gridbox-app-item.add-new-app', function(event){
            event.preventDefault();
            event.stopPropagation();
            $g('#ba-gridbox-apps-dialog .search-gridbox-apps').val('');
            $g('#ba-gridbox-apps-dialog .gridbox-app-element').css('display', '');
            var modal = $g('#ba-gridbox-apps-dialog');
            modal.find('.gridbox-app-element[data-system][data-installed="1"]')
                .attr('data-installed', 0).find('.default-theme').remove();
            $g.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_gridbox&task=appslist.getSystemApps",
                success: function(msg){
                    var array = JSON.parse(msg)
                    for (var i = 0; i < array.length; i++) {
                        modal.find('.gridbox-app-element.gridbox-app-item-'+array[i].title)
                            .attr('data-installed', 1).find('.gridbox-app-item-body')
                            .append('<span class="default-theme"><i class="zmdi zmdi-check-circle"></i></span>');
                    }
                    modal.modal();
                }
            });
        });

        $g('body').on('click', '.ba-add-payment-method', function(event){
            event.preventDefault();
            event.stopPropagation();
            $g('#gridbox-payment-methods-dialog .search-gridbox-apps').val('');
            $g('#gridbox-payment-methods-dialog .gridbox-app-element').css('display', '');
            $g('#gridbox-payment-methods-dialog').modal();
        }).on('click', '.ba-create-store-product', function(event){
            event.preventDefault();
            event.stopPropagation();
            document.querySelector('.ba-select-store-product-type').classList.add('visible-store-product-type');
        });

        $g('body').on('click', '.ba-add-promocodes-method', function(event){
            event.preventDefault();
            event.stopPropagation();
            $g.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task=promocodes.addPromoCode",
                complete:function(msg){
                    reloadPage(app._('ITEM_CREATED'));
                }
            });
        }).on('click', '.ba-add-shipping', function(event){
            event.preventDefault();
            event.stopPropagation();
            $g.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task=shipping.addShipping",
                complete:function(msg){
                    reloadPage(app._('ITEM_CREATED'));
                }
            });
        }).on('click', '.ba-add-product-options', function(event){
            event.preventDefault();
            event.stopPropagation();
            makeFetchRequest('index.php?option=com_gridbox&task=productoptions.addProductOptions').then(function(json){
                reloadPage(app._('ITEM_CREATED'));
            });
        });

        $g('body').on('click', '.gridbox-app-item.add-new-theme', function(event){
            $g('#ba-gridbox-themes-dialog .search-gridbox-apps').val('');
            $g('#ba-gridbox-themes-dialog .gridbox-app-element').css('display', '');
            $g('#ba-gridbox-themes-dialog').modal();
        });

        $g('body').on('click', '.gridbox-app-item-footer-action.delete-gridbox-app-item', function(event){
            event.preventDefault();
            deleteMode = {
                action: 'pages.deleteGridboxAppItem',
                item : this.closest('.gridbox-app-item'),
                id: this.dataset.id
            };
            $g('#delete-dialog').modal();
        });

        $g('body').on('click', '.gridbox-app-item-footer-action.theme-duplicate', function(event){
            event.preventDefault();
            var id = this.closest('.gridbox-app-item').dataset.id;
            $g('#context-item').val(id);
            Joomla.submitbutton('themes.contextDuplicate');
        });

        $g('body').on('click', '.gridbox-app-item-footer-action.theme-delete', function(event){
            event.preventDefault();
            var item = this.closest('.gridbox-app-item')
                id = item.dataset.id,
                def = item.querySelector('p').dataset.default;
            if (def == 1) {
                $g('#default-message-dialog').modal();
                return false;
            }
            $g('#context-item').val(id);
            deleteMode = 'single';
            $g('#delete-dialog').modal();
        });

        $g('body').on('click', '.gridbox-app-item-footer-action.theme-settings', function(event){
            event.preventDefault();
            item = this.closest('.gridbox-app-item')
            var obj = {
                    id : item.dataset.id,
                    name : item.querySelector('p > span').textContent,
                    default : item.querySelector('p').dataset.default,
                    image : item.querySelector('.image-container').dataset.image
                };
            pageId = obj.id;
            setThemeSettings(obj);
        });

        $g('body').on('change', '.set-group-display', function(){
            let action = this.checked ? 'addClass' : 'removeClass',
                $this = $g(this).closest('.ba-group-element').nextAll();
            $this[action]('visible-subgroup').removeClass('subgroup-animation-ended');
            clearTimeout(this.subDelay);
            if (this.checked) {
                this.subDelay = setTimeout(function(){
                    $this.addClass('subgroup-animation-ended');
                }, 750);
            }
        });

        $g('body').on('click.lightbox', '.comment-attachment-image-type', function(){
            var wrapper = $g(this).closest('.comment-attachments-image-wrapper'),
                div = document.createElement('div'),
                index = 0,
                $this = this,
                endCoords = startCoords = {},
                image = document.createElement('img'),
                images = new Array(),
                width = this.offsetWidth,
                height = this.offsetHeight,
                offset = $g(this).offset(),
                modal = $g(div),
                img = document.createElement('div');
            img.style.backgroundImage = 'url('+this.dataset.img+')';
            div.className = 'ba-image-modal instagram-modal ba-comments-image-modal';
            img.style.top = (offset.top - $g(window).scrollTop())+'px';
            img.style.left = offset.left+'px';
            img.style.width = width+'px';
            img.style.height = height+'px';
            div.appendChild(img);
            modal.on('click', function(){
                commentsImageModalClose(modal, images, index)
            });
            $g('body').append(div);
            image.onload = function(){
                setCommentsImage(this);
            }
            image.src = this.dataset.img;
            setTimeout(function(){
                var str = '';
                if (wrapper.find('.comment-attachment-image-type').length > 1) {
                    str += '<i class="zmdi zmdi-chevron-left"></i><i class="zmdi zmdi-chevron-right"></i>';
                }
                str += '<i class="zmdi zmdi-close">';
                modal.append(str);
                modal.find('.zmdi-chevron-left').on('click', function(event){
                    event.stopPropagation();
                    index = commentsImageGetPrev(img, images, index);
                });
                modal.find('.zmdi-chevron-right').on('click', function(event){
                    event.stopPropagation();
                    index = commentsImageGetNext(img, images, index);
                });
                modal.find('.zmdi-close').on('click', function(event){
                    event.stopPropagation();
                    commentsImageModalClose(modal, images, index)
                });
            }, 600);
            wrapper.find('.comment-attachment-image-type').each(function(ind){
                images.push(this);
                if (this == $this) {
                    index = ind;
                }
            });
            $g(window).on('keyup.instagram', function(event) {
                event.preventDefault();
                event.stopPropagation();
                if (event.keyCode === 37) {
                    index = commentsImageGetPrev(img, images, index);
                } else if (event.keyCode === 39) {
                    index = commentsImageGetNext(img, images, index);
                } else if (event.keyCode === 27) {
                    commentsImageModalClose(modal, images, index)
                }
            });
        });

        $g('body').on('click', '.ban-user-comment', function(event){
            let str = app.currentComment.find('input[type="hidden"]').val(),
                obj = JSON.parse(str),
                view = $g('input[name="ba_view"]').val();
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task="+view+".banUser",
                data: {
                    'email': obj.email,
                    'ip': obj.ip
                },
                complete:function(msg){
                    showNotice(msg.responseText);
                }
            });
        });
        
        $g('body').on('click', '.approve-user-comment, .spam-user-comment', function(event){
            let str = app.currentComment.find('input[type="hidden"]').val(),
                status = this.dataset.status,
                task = this.dataset.task,
                obj = JSON.parse(str),
                view = $g('input[name="ba_view"]').val();
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task="+view+"."+task,
                data: {
                    'context-item': obj.id
                },
                complete:function(msg){
                    let iconClassName = 'zmdi zmdi-eye ba-icon-md',
                        constName = 'COM_GRIDBOX_N_ITEMS_APPROVED';
                        iconTooltip = app._('APPROVED');
                    if (status == 'spam') {
                        iconClassName = 'zmdi zmdi-alert-octagon ba-icon-md';
                        iconTooltip = app._('SPAM');
                        constName = 'COM_GRIDBOX_N_ITEMS_SPAMED';
                    }
                    obj.status = status;
                    str = JSON.stringify(obj);
                    app.currentComment.find('input[type="hidden"]').val(str);
                    app.currentComment.find('.status-td i')[0].className = iconClassName;
                    app.currentComment.find('.status-td .ba-tooltip').text(iconTooltip);
                    showNotice(app._(constName));
                }
            });
        });

        $g('body').on('click', '.delete-user-comment', function(event){
            var id = app.currentComment.find('input[type="checkbox"]').val(),
                view = $g('input[name="ba_view"]').val();
            $g('#context-item').val(id);
            deleteMode = view+'.contextDelete';
            $g('#delete-dialog').modal();
        });

        $g('body').on('click', '.edit-user-comment', function(event){
            let parent = $g(this).closest('.comment-user-message-wrapper'),
                message = parent.find('> .comment-message').html().trim().replace(/<br>/g, '\n');
            parent.find('> .comment-message, .edit-user-comment').hide();
            parent.find('> .ba-comment-message-wrapper').css('display', '').find('.ba-comment-message').val(message);
        });

        $g('body').on('click', '.ba-dashboard-popover-trigger', function(event){
            event.stopPropagation();
            let div = document.querySelector('.'+this.dataset.target),
                rect = this.getBoundingClientRect();
            div.classList.add('visible-dashboard-dialog');
            let left = (rect.left - div.offsetWidth / 2 + rect.width / 2),
                arrow = '50%';
            if (this.dataset.target == 'blog-settings-context-menu' && left < 110) {
                left = 110;
                arrow = (rect.left - 110 + rect.width / 2)+'px'
            }
            div.style.setProperty('--arrow-position', arrow);
            div.style.top = (rect.bottom + window.pageYOffset + 10)+'px';
            div.style.left = left+'px';
        });

        $g('.ba-dashboard-apps-dialog').on('click', function(event){
            event.stopPropagation();
        })

        $g('body').on('click', '.ba-comment-smiles-picker', function(event){
            event.stopPropagation();
            let picker = $g('.ba-comment-smiles-picker-dialog').addClass('visible-smiles-picker'),
                rect = this.getBoundingClientRect(),
                div = picker[0];
            fontBtn = $g(this).closest('.ba-comment-message-wrapper').find('.ba-comment-message')[0];
            div.style.top = (rect.top + window.pageYOffset - div.offsetHeight / 2 + rect.height / 2)+'px';
            div.style.left = (rect.left - div.offsetWidth - 10)+'px';
        });

        $g('body').on('click', '.ba-submit-cancel', function(){
            let parent = $g(this).closest('.comment-user-message-wrapper');
            parent.find('.comment-message, .edit-user-comment').css('display', '');
            parent.find('> .ba-comment-message-wrapper').hide()
                .find('.ba-comment-message').next().find('.ba-comment-xhr-attachment .zmdi-delete').trigger('click');
        });

        $g('body').on('input', '.ba-comment-message', function(){
            checkCommentDisabledBtn(this);
        });

        $g('body').on('click', '.ba-submit-comment', function(event){
            let str = app.currentComment.find('input[type="hidden"]').val(),
                obj = JSON.parse(str),
                attachments = {},
                message = $g(this).closest('.ba-comment-message-wrapper').find('.ba-comment-message').val(),
                data = {
                    message: message,
                    type: this.dataset.type,
                    parent: obj.id
                },
                view = $g('input[name="ba_view"]').val();
            $g(this).closest('.ba-comment-message-wrapper').find('.ba-comment-xhr-attachment').each(function(){
                attachments[this.dataset.id] = app.tmpAttachments[this.dataset.id];
            });
            data.attachments = JSON.stringify(attachments);
            var matches = data.message.match(/(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff])[\ufe0e\ufe0f]?(?:[\u0300-\u036f\ufe20-\ufe23\u20d0-\u20f0]|\ud83c[\udffb-\udfff])?(?:\u200d(?:[^\ud800-\udfff]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff])[\ufe0e\ufe0f]?(?:[\u0300-\u036f\ufe20-\ufe23\u20d0-\u20f0]|\ud83c[\udffb-\udfff])?)*/g);
            if (matches) {
                for (var i = 0; i < matches.length; i++) {
                    let charCode = '&#'+matches[i].codePointAt(0)+';';
                    data.message = data.message.replace(matches[i], charCode);
                }
            }
            if (data.message || data.attachments != '{}') {
                $.ajax({
                    type : "POST",
                    dataType : 'text',
                    url : "index.php?option=com_gridbox&task="+view+".sendCommentMesssage",
                    data: data,
                    complete:function(msg){
                        if (data.type == 'reply') {
                            reloadPage();
                        } else {
                            obj.message = data.message;
                            obj.attachments = JSON.parse(msg.responseText);
                            str = JSON.stringify(obj);
                            app.currentComment.find('input[type="hidden"]').val(str);
                            app.currentComment.find('span.comments-message').text(message);
                            app.currentComment.trigger('click');
                        }
                    }
                });
            }
        });

        $g('body').on('click', '.delete-comment-attachment-file', function(){
            let $this = this,
                data = {
                    id: this.dataset.id,
                    filename: this.dataset.filename
                },
                view = $g('input[name="ba_view"]').val();
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task="+view+".removeTmpAttachment",
                data: data,
                complete:function(msg){
                    var str = app.currentComment.find('input[type="hidden"]').val(),
                        obj = JSON.parse(str),
                        list = new Array();
                    for (var i = 0; i < obj.attachments.length; i++) {
                        if (obj.attachments[i].id != data.id) {
                            list.push(obj.attachments[i]);
                        }
                    }
                    obj.attachments = list;
                    str = JSON.stringify(obj);
                    app.currentComment.find('input[type="hidden"]').val(str);
                    if ($this.dataset.type == 'file') {
                        $this.closest('.comment-attachment-file').remove();
                    } else {
                        $this.closest('.comment-attachment-image-type-wrapper').remove();
                    }
                }
            });
        });

        $g('.ba-comment-smiles-picker-dialog').on('click', 'span', function(event){
            event.stopPropagation();
            insertTextAtCursor(fontBtn, this.textContent);
        });

        $g('body').on('click', function(event){
            $g('.ba-comment-smiles-picker-dialog.visible-smiles-picker').removeClass('visible-smiles-picker');
            $g('.ba-dashboard-apps-dialog.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
            $g('.ba-select-store-product-type.visible-store-product-type').each(function(i, el){
                el.classList.remove('visible-store-product-type');
                el.classList.add('store-product-type-out');
                setTimeout(function(){
                    el.classList.remove('store-product-type-out');
                }, 300);
            });
        });

        $g('body').on('click', '.ba-comment-attachment-trigger', function(){
            let $this = $g(this).next();
            if (!$this[0].dataset.uploading) {
                setTimeout(function(){
                    $this.trigger('click');
                }, 150);
            }
        });

        $g('body').on('change', '.ba-comment-attachment', function(){
            this.dataset.uploading = 'uploading';
            let files = [].slice.call(this.files),
                container = $g(this).closest('.ba-comment-message-wrapper').find('.ba-comment-xhr-attachment-wrapper'),
                flag = true;
            for (let i = 0; i < files.length; i++) {
                var size = this.dataset.size * 1000,
                    name = files[i].name.split('.'),
                    msg = '',
                    ext = name[name.length - 1].toLowerCase(),
                    types = this.dataset.types.replace(/ /g, '').split(',');
                if (size < files[i].size) {
                    msg = 'NOT_ALLOWED_FILE_SIZE';
                } else if (types.indexOf(ext) == -1) {
                    msg = 'NOT_SUPPORTED_FILE';
                }
                if (size < files[i].size || types.indexOf(ext) == -1) {
                    flag = false;
                    showNotice(app._(msg), 'ba-alert');
                    this.dataset.uploading = '';
                    break
                }
            }
            if (flag) {
                uploadCommentAttachmentFile(files, this.dataset.attach, container);
            }
        });

        app.tmpAttachments = {};

        function removeTmpAttachment($this)
        {
            if ($this.dataset.id) {
                let view = $g('input[name="ba_view"]').val();
                $.ajax({
                    type : "POST",
                    dataType : 'text',
                    url : "index.php?option=com_gridbox&task="+view+".removeTmpAttachment",
                    data: {
                        id: $this.dataset.id,
                        filename: app.tmpAttachments[$this.dataset.id].filename
                    },
                    complete:function(msg){
                        let container = $this.closest('.ba-comment-xhr-attachment-wrapper');
                        $this.remove();
                        delete(app.tmpAttachments[$this.dataset.id]);
                        checkCommentDisabledBtn(container);
                    }
                });
            }
        }

        function checkCommentDisabledBtn($this)
        {
            let btn = $g($this).closest('.ba-comment-message-wrapper').find('.ba-submit-comment'),
                message = $g($this).closest('.ba-comment-message-wrapper').find('.ba-comment-message').val(),
                attachments = {},
                str = '';
            $g($this).closest('.ba-comment-message-wrapper').find('.ba-comment-xhr-attachment').each(function(){
                attachments[this.dataset.id] = app.tmpAttachments[this.dataset.id];
            });
            str = JSON.stringify(attachments);
            if (message.trim() || str != '{}') {
                btn.removeClass('ba-disabled-submit');
            } else {
                btn.addClass('ba-disabled-submit');
            }
        }

        function uploadCommentAttachmentFile(files, type, container)
        {
            if (files.length) {
                var file = files.shift(),
                    attachment = document.createElement('div'),
                    str = '',
                    xhr = new XMLHttpRequest(),
                    formData = new FormData(),
                    view = $g('input[name="ba_view"]').val();
                attachment.className = 'ba-comment-xhr-attachment';
                if (type == 'file') {
                    str += '<i class="zmdi zmdi-attachment-alt"></i>';
                } else {
                    str += '<span class="post-intro-image"></span>';
                }
                str += '<span class="attachment-title">'+file.name;
                str += '</span><span class="attachment-progress-bar-wrapper"><span class="attachment-progress-bar">';
                str += '</span></span><i class="zmdi zmdi-delete"></i>';
                attachment.innerHTML = str;
                if (type == 'image') {
                    let reader = new FileReader();
                    reader.onloadend = function() {
                        attachment.querySelector('.post-intro-image').style.backgroundImage = 'url('+reader.result+')';
                    }
                    reader.readAsDataURL(file);
                }
                $g(attachment).find('.zmdi-delete').on('click', function(){
                    removeTmpAttachment(this.closest('.ba-comment-xhr-attachment'));
                });
                formData.append('file', file);
                formData.append('type', type);
                xhr.upload.onprogress = function(event) {
                    attachment.querySelector('.attachment-progress-bar').style.width = Math.round(event.loaded / event.total * 100)+"%";
                }
                xhr.onload = xhr.onerror = function(){
                    try {
                        let obj = JSON.parse(this.responseText);
                        app.tmpAttachments[obj.id] = obj;
                        attachment.dataset.id = obj.id;
                    } catch (e){
                        console.info(e)
                        console.info(this.responseText)
                    }
                    setTimeout(function(){
                        attachment.classList.add('attachment-file-uploaded')
                    }, 300);
                    uploadCommentAttachmentFile(files, type, container);
                };
                container.append(attachment);
                xhr.open("POST", "index.php?option=com_gridbox&task="+view+".uploadAttachmentFile", true);
                xhr.send(formData);
            } else {
                checkCommentDisabledBtn(container);
                $g('body .ba-comment-attachment[data-uploading="uploading"]').removeAttr('data-uploading');
            }
        }

        $g('body').on('click', '.comments-table tbody tr', function(){
            $g('.active-comment').removeClass('active-comment');
            app.currentComment = $g(this).addClass('active-comment');
            app.tmpAttachments = {};
            getCommentLikeStatus();
        });

        $g('body').on('click', '.comment-likes-action', function(){
            if (this.dataset.disabled) {
                return false;
            }
            $g('.comments-right-sidebar .comment-likes-action').attr('data-disabled', 'disabled');
            let str = app.currentComment.find('td.select-td  input[type="hidden"]').val(),
                obj = JSON.parse(str),
                action = this.dataset.action,
                view = $g('input[name="ba_view"]').val();
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task="+view+".setLikes",
                data: {
                    id: obj.id,
                    action: this.dataset.action
                },
                complete:function(msg){
                    let obj = JSON.parse(msg.responseText);
                    setTimeout(function(){
                        $g('.comments-right-sidebar .comment-likes-action').removeAttr('data-disabled');
                    }, 100);
                    $g('.comments-right-sidebar .comment-likes-action[data-action="likes"] .likes-count').text(obj.likes);
                    $g('.comments-right-sidebar .comment-likes-action[data-action="dislikes"] .likes-count').text(obj.dislikes);
                    $g('.comments-right-sidebar .comment-likes-action').removeClass('active');
                    $g('.comments-right-sidebar .comment-likes-action[data-action="'+obj.status+'"]').addClass('active');
                }
            });
        });

        $g('body').on('click', '.ba-comment-unread', function(){
            this.classList.remove('ba-comment-unread');
            let id = this.querySelector('input[type="checkbox"]').value,
                view = $g('input[name="ba_view"]').val();
            $g('.unread-comments-count[data-type="'+view+'"]').each(function(){
                let count = this.textContent - 1;
                if (count) {
                    this.textContent = count;
                } else {
                    this.remove();
                }
            });
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&task="+view+".setReadStatus",
                data: {
                    id: id
                }
            });
        });

        $g('body').on('click', '.delete-author-social-link', function(event){
            event.stopPropagation();
            deleteMode = this;
            $g('#delete-dialog').modal();
        });

        $g('body').on('click', 'span.authors-link', function(){
            let key = this.dataset.key;
            $g('.apply-author-link').attr('data-key', key);
            openAuthorSocialDialog(app.authorsSocial[key]);
        });

        $g('.add-new-author-social-link i').on('click', function(){
            var key = -1;
            for (key in app.authorsSocial) {}
            $g('.apply-author-link').attr('data-key', key * 1 + 1);
            openAuthorSocialDialog();
        });

        $g('#edit-author-social-modal .author-link-url').on('input', function(){
            if (this.value.trim()) {
                $g('.apply-author-link').addClass('active-button');
            } else {
                $g('.apply-author-link').removeClass('active-button');
            }
        });

        $g('.apply-author-link').on('click', function(event){
            event.preventDefault();
            if (this.classList.contains('active-button')) {
                let title = $g('#edit-author-social-modal .ba-custom-select input[type="hidden"]').val(),
                    link = $g('#edit-author-social-modal .author-link-url').val().trim(),
                    key = this.dataset.key;
                app.authorsSocial[key] = $g.extend(true, {}, authorSocial[title]);
                app.authorsSocial[key].link = link;
                let str = '';
                for (var ind in app.authorsSocial) {
                    str += getAuthorPatern(ind);
                }
                $g('.authors-links-list').html(str);
                $g('#edit-author-social-modal').modal('hide');
            }
        });

        $g('.system-page-title').on('input', function(){
            if (this.value.trim()) {
                $g('.apply-system-settings').addClass('active-button').removeClass('disabled-button');
            } else {
                $g('.apply-system-settings').removeClass('active-button').addClass('disabled-button');
            }
        });

        $g('.page-enable-header').on('change', function(){
            $g('.apply-system-settings').addClass('active-button').removeClass('disabled-button');
        });

        $g('.system-page-theme-select').on('customAction', function(){
            $g('.apply-system-settings').addClass('active-button').removeClass('disabled-button');
        });

        $g('.apply-system-settings').on('click', function(event){
            event.preventDefault();
            var options = {};
            if ($g('.page-enable-header').closest('.ba-checkbox-parent')[0].style.display != 'none') {
                options.enable_header = $g('.page-enable-header').prop('checked');
            }
            if (this.classList.contains('active-button')) {
                var data = {
                    title: $g('.system-page-title').val().trim(),
                    theme: $g('.system-page-theme-select input[type="hidden"]').val(),
                    options: JSON.stringify(options),
                    id: this.dataset.id
                }
                $.ajax({
                    type : "POST",
                    dataType : 'text',
                    url : 'index.php?option=com_gridbox&task=system.applySettings',
                    data : data,
                    success: function(msg){
                        reloadPage(msg);
                        $g('#system-settings-dialog').modal('hide');
                    }
                });
            }
        })

        $g('.ba-range-wrapper input[type="range"]').each(function(){
            rangeAction(this, inputCallback);
        });

        $g('.ba-settings-toolbar input[type="number"]').on('input', function(){
            inputCallback($g(this));
        });

        notification.find('.zmdi.zmdi-close').on('click', function(){
            notification.removeClass('notification-in').addClass('animation-out');
        });

        function reloadPage(message, type)
        {
            if (submitTask == 'pages.deleteApp') {
                showNotice(message);
                window.location.href = 'index.php?option=com_gridbox'
            } else {
                $g('#gridbox-container').load(window.location.href+' #gridbox-content', function(){
                    loadPage();
                    $g('body > .ba-tooltip').remove();
                    if (message) {
                        showNotice(message, type);
                    }
                });
            }
        }

        function checkContext(context, deltaY, deltaX)
        {
            if (deltaX - context.width() < 0) {
                context.addClass('ba-left');
            } else {
                context.removeClass('ba-left');
            }
            if (deltaY - context.height() < 0) {
                context.addClass('ba-top');
            } else {
                context.removeClass('ba-top');
            }
        }

        function listenMessage(event)
        {
            if (uploadMode == 'sortingImage') {
                fontBtn.dataset.image = IMAGE_PATH+event.data.path;
                fontBtn.style.setProperty('--sorting-image', 'url('+JUri+IMAGE_PATH+event.data.path+')');
                $g('#uploader-modal').modal('hide');
            } else if (uploadMode == 'introImage') {
                $g(fontBtn).val(IMAGE_PATH+event.data.path).trigger('change');
                $g('#uploader-modal').modal('hide');
                showNotice($g('#upload-const').val());
            } else if (uploadMode == 'reloadPage') {
                reloadPage(event.data);
            } else if (uploadMode == 'ckeImage') {
                var url = event.data.url;
                $g('.cke-upload-image').val(url);
                $g('#add-cke-image').addClass('active-button');
                $g('#uploader-modal').modal('hide');
            } else if (uploadMode == 'themeImage') {
                var img = IMAGE_PATH+event.data.path;
                $g('.theme-image').val(img);
                $g('.theme-apply').addClass('active-button');
                $g('#uploader-modal').modal('hide');
            }
        }

        function setTabsUnderline()
        {
            $g('.general-tabs > ul li.active a').each(function(){
                var coord = this.getBoundingClientRect();
                $g(this).closest('.general-tabs').find('div.tabs-underline').css({
                    'left' : coord.left,
                    'right' : document.documentElement.clientWidth - coord.right,
                }); 
            });
        }

        function showContext(event, context)
        {
            event.stopPropagation();
            event.preventDefault();
            $g('.context-active').removeClass('context-active');
            currentContext.addClass('context-active');
            var deltaX = document.documentElement.clientWidth - event.pageX,
                deltaY = document.documentElement.clientHeight - event.clientY;
            setTimeout(function(){
                context.css({
                    'top' : event.pageY,
                    'left' : event.pageX,
                }).show();
                checkContext(context, deltaY, deltaX);
            }, 50);
        }

        function calculateNewPermissions(usergroup, key, value, div)
        {
            if (!app.permissions.rules[key]) {
                app.permissions.rules[key] = {};
            }
            if (value === '' && app.permissions.rules[key][usergroup.group]) {
                delete app.permissions.rules[key][usergroup.group];
            } else {
                app.permissions.rules[key][usergroup.group] = value;
            }
            let ind = null,
                actions = getPermissionsActions(div);
            for (ind in app.permissions.rules[key]) {

            }
            if (ind == null) {
                delete app.permissions.rules[key]
            }
            $.ajax({
                type:"POST",
                dataType:'text',
                data:{
                    id: usergroup.id,
                    type: usergroup.type,
                    actions: actions,
                    rules: JSON.stringify(app.permissions.rules)
                },
                url:"index.php?option=com_gridbox&task=gridbox.testNewPermissions",
                success: function(msg){
                    app.permissions.groups = JSON.parse(msg);
                    setGroupPermissions(usergroup.id, usergroup.type, usergroup.group, div);
                }
            });
        }

        function getPermissionsActions(div)
        {
            let actions = new Array();
            div.querySelectorAll('.ba-group-element:not([disabled]) .select-permission-action input[data-key]').forEach(function(el){
                actions.push(el.dataset.key);
            });

            return actions.join(', ');
        }

        function setGroupPermissions(id, type, group, div)
        {
            div.querySelectorAll('.permission-action-wrapper .ba-group-element:not([disabled])').forEach(function(el){
                let input = el.querySelector('input[type="hidden"][data-key]'),
                    obj = app.permissions,
                    key = input.dataset.key,
                    value = obj.rules[key] ? ((group in obj.rules[key]) ? obj.rules[key][group] : '') : '',
                    text = el.querySelector('li[data-value="'+value+'"]').textContent.trim();
                input.value = value;
                el.querySelector('input[type="text"]').value = text;
                el.querySelectorAll('.calculated-permission').forEach(function(calculated){
                    calculated.dataset.status = obj.groups[group][key].status;
                    calculated.querySelector('i').className = obj.groups[group][key].icon;
                    calculated.querySelector('span.ba-tooltip').textContent = obj.groups[group][key].text;
                });
                input.usergroup = {
                    id: id,
                    type: type,
                    group: group
                }
            });
        }

        function getPermissions(id, type, $this)
        {
            let actions = getPermissionsActions($this);
            $.ajax({
                type:"POST",
                dataType:'text',
                data:{
                    id: id,
                    type: type,
                    actions: actions
                },
                url:"index.php?option=com_gridbox&task=gridbox.getPermissions",
                success: function(msg){
                    app.permissions = JSON.parse(msg);
                    app.permissions.id = id;
                    app.permissions.type = type;
                    let group = 0,
                        div = $g($this);
                    div.find('.select-permission-usergroup').each(function(){
                        let li = this.querySelector('ul li');
                        group = li.dataset.value;
                        this.querySelector('input[type="hidden"]').value = group;
                        this.querySelector('input[type="text"]').value = li.textContent.trim();
                        this.usergroup = {
                            id: id,
                            type: type
                        }
                    });
                    div.find('.permission-action-wrapper').each(function(){
                        setGroupPermissions(id, type, group, this);
                    });
                }
            });
        }

        function updatePermissions()
        {
            $.ajax({
                type:"POST",
                dataType:'text',
                data:{
                    id: app.permissions.id,
                    type: app.permissions.type,
                    rules: JSON.stringify(app.permissions.rules)
                },
                url:"index.php?option=com_gridbox&task=gridbox.updatePermissions"
            });
        }

        function showPageSettings(obj, tr)
        {
            if (!tr.querySelector('.title-cell a')) {
                showNotice(app._('EDIT_NOT_PERMITTED'));
                return false;
            }
            var end = obj.end_publishing;
            if (end == '0000-00-00 00:00:00') {
                end = '';
            }
            $g('#published_on').val(obj.created);
            $g('.select-post-author').each(function(){
                $g('span.selected-author').remove();
                var author = new Array(),
                    li = $g(this).find('li[data-value]'),
                    authorId = '';
                for (var i = 0; i < obj.author.length; i++) {
                    if (!obj.author[i].avatar) {
                        obj.author[i].avatar = 'components/com_gridbox/assets/images/default-user.png';
                    }
                    var str = '<span class="selected-author" data-id="'+obj.author[i].id
                    str += '"><span class="ba-author-avatar" style="background-image: url(';
                    str += JUri+obj.author[i].avatar+')"></span><span class="ba-author-name">'+obj.author[i].title+'</span>';
                    str += '<i class="zmdi zmdi-close remove-selected-author"></i></span>';
                    $g(this).before(str);
                    author.push(obj.author[i].id);
                }
                li.each(function(){
                    this.style.display = author.indexOf(this.dataset.value) == -1 ? '' : this.style.display = 'none';
                });
                if (li.length == author.length) {
                    $g('.select-post-author').hide();
                } else {
                    $g('.select-post-author').css('display', '');
                }
                authorId = author.join(',');
                this.querySelector('input[type="hidden"]').value = authorId;
            });
            $g('#published_down').val(end);
            $g('#access').val(obj.page_access);
            var value = $g('.access-select li[data-value="'+obj.page_access+'"]').text().trim();
            $g('.access-select input[type="text"]').val(value);
            $g('#language').val(obj.language);
            value = $g('.language-select li[data-value="'+obj.language+'"]').text().trim();
            $g('.language-select input[type="text"]').val(value);
            $g('#robots').val(obj.robots);
            value = $g('.robots-select li[data-value="'+obj.robots+'"]').text().trim();
            $g('.robots-select input[type="text"]').val(value);
            $g('.theme-list').val(obj.theme);
            var theme = $g('.theme-select li[data-value="'+obj.theme+'"]').text(),
                modalFlag = true;
            theme = $.trim(theme);
            $g('.theme-select input[type="text"]').val(theme);
            $g('#settings-dialog .ba-alert-container').hide();
            $g('#settings-dialog .permissions-options').each(function(){
                getPermissions(obj.id, 'page', this);
            });
            if ($g('.meta-tags').length > 0) {
                $g('#page-category').val(obj.page_category).prev().val(obj.category);
                $.ajax({
                    type:"POST",
                    dataType:'text',
                    async: false,
                    url:"index.php?option=com_gridbox&task=apps.getTags",
                    success: function(msg){
                        var tags = JSON.parse(msg);
                        $g('.meta-tags .all-tags').empty();
                        tags.forEach(function(el){
                            $g('.meta-tags .all-tags').append('<li data-id="'+el.id+'" style="display:none;">'+el.title+'</li>');
                        });
                    }
                });
                $.ajax({
                    type:"POST",
                    dataType:'text',
                    url:"index.php?option=com_gridbox&task=gridbox.getPageTags",
                    data : {
                        page_id : obj.id
                    },
                    success: function(msg){
                        msg = JSON.parse(msg);
                        $g('select.meta_tags').empty()
                        if (msg) {
                            $g('.picked-tags .tags-chosen').remove();
                            $g('select[name="meta_tags"]').empty();
                            $g('.all-tags li').removeClass('selected-tag');
                            for (var i = 0; i < msg.length; i++) {
                                var title = msg[i].title,
                                    tagId = msg[i].id,
                                    str = '<li class="tags-chosen"><span>';
                                $g('.all-tags li[data-id="'+tagId+'"]').addClass('selected-tag');
                                str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
                                $g('.picked-tags .search-tag').before(str);
                                str = '<option value="'+tagId+'" selected>'+title+'</option>';
                                $g('select.meta_tags').append(str);
                            }
                            $g('.meta-tags .picked-tags .search-tag input').val('');
                            $g('.all-tags li').hide();
                        }
                    }
                });
            }
            $g('#settings-dialog .page-id').val(obj.id);
            $g('#settings-dialog .page-title').val(obj.title);
            $g('#settings-dialog .page-class-suffix').val(obj.class_suffix);
            $g('#settings-dialog .page-meta-title').val(obj.meta_title);
            $g('#settings-dialog .page-meta-description').val(obj.meta_description);
            $g('#settings-dialog .page-meta-keywords').val(obj.meta_keywords);
            $g('#settings-dialog .page-alias').val(obj.page_alias);
            $g('#settings-dialog .intro-text').val(obj.intro_text);
            let image = obj.intro_image.indexOf('balbooa.com') == -1 ? JUri+obj.intro_image : obj.intro_image;
            $g('#settings-dialog .intro-image').val(obj.intro_image).parent().find('.image-field-tooltip').css({
                'background-image': obj.intro_image ? 'url('+image+')' : ''
            });
            if (obj.share_image == 'share_image') {
                obj.share_image = obj.intro_image;
            }
            image = obj.share_image.indexOf('balbooa.com') == -1 ? JUri+obj.share_image : obj.share_image;
            $g('#settings-dialog .share-image').val(obj.share_image).parent().find('.image-field-tooltip').css({
                'background-image': obj.share_image ? 'url('+image+')' : ''
            });
            $g('#settings-dialog .share-title').val(obj.share_title);
            $g('#settings-dialog .share-description').val(obj.share_description);
            $g('#settings-dialog .sitemap-include').prop('checked', Boolean(obj.sitemap_include * 1));
            var range = $g('#settings-dialog .priority').val(obj.priority).prev().val(obj.priority);
            setLinearWidth(range);
            $g('#settings-dialog .changefreq').val(obj.changefreq).prev().each(function(){
                this.value = $g(this).closest('.ba-custom-select').find('li[data-value="'+obj.changefreq+'"]').text().trim();
            });
            $g('#settings-dialog .set-group-display').each(function(){
                var action = this.checked ? 'addClass' : 'removeClass';
                $g(this).closest('.ba-group-element').nextAll()[action]('visible-subgroup subgroup-animation-ended');
            });
            $g('.settings-apply').removeClass('disabled-button');
            $g('#settings-dialog').modal();
        }

        function drawBlogMoveTo(array)
        {
            var str = '',
                type = 'blog';
            if (moveTo != 'apps.moveTo' && !currentContext.hasClass('ba-category')) {
                var obj = currentContext.find('.select-td input[type="hidden"]').val();
                obj = JSON.parse(obj);
                type = obj.app_type;
            } else if (moveTo == 'apps.moveTo') {
                var obj = jQuery('td.select-td input[type="hidden"]').first().val();
                obj = JSON.parse(obj);
                type = obj.app_type;
            } else if (moveTo == 'apps.categoryMoveTo') {
                var obj = $g('#blog-data').val();
                obj = JSON.parse(obj);
                type = obj.type;
            }
            array.forEach(function(el, i){
                if (el.type == type) {
                    var value = '{"id":0, "app_id":'+el.id+'}';
                    str += '<li class="root '+el.type+'"><label><i class="zmdi zmdi-folder"></i>';
                    str += el.title+'<input type="radio" style="display:none;"';
                    str += " name='category_id' value='"+value+"'></label>";
                    if (el.categories.length > 0) {
                        var catStr = drawRestoreBlog(el.categories, el.id);
                        if (catStr != '<ul></ul>') {
                            str += catStr;
                            str += '<i class="zmdi zmdi-chevron-right ba-icon-md"></i>';
                        }
                    }
                    str += '</li>';
                }
            });

            return str;
        }

        function drawRestoreBlog(array, app_id)
        {
            var str = '<ul>',
                id = 0;
            if (moveTo != 'apps.moveTo' && currentContext.hasClass('ba-category')) {
                id = currentContext.attr('data-id');
            }
            array.forEach(function(el, i){
                if (id != el.id) {
                    var value = '{"id":'+el.id+', "app_id":'+app_id+'}';
                    str += '<li><label><i class="zmdi zmdi-folder"></i>';
                    str += el.title+'<input type="radio" style="display:none;"';
                    str += " name='category_id' value='"+value+"'></label>";
                    if (el.child.length > 0) {
                        var catStr = drawRestoreBlog(el.child, app_id);
                        if (catStr != '<ul></ul>') {
                            str += catStr;
                            str += '<i class="zmdi zmdi-chevron-right ba-icon-md"></i>';
                        }
                    }
                    str += '</li>';
                }
            });
            str += '</ul>';

            return str;
        }

        function createAjax()
        {
            var form = document.getElementById('adminForm'),
                view = $g('[name="ba_view"]').val(),
                src = form.action,
                obj = {
                    'filter_search' : $g('[name="filter_search"]').val(),
                    'filter_state' : $g('[name="filter_state"]').val(),
                    'filter_order' : $g('[name="filter_order"]').val(),
                    'theme_filter' : $g('[name="theme_filter"]').val(),
                    'author_filter' : $g('[name="author_filter"]').val(),
                    'access_filter' : $g('[name="access_filter"]').val(),
                    'language_filter' : $g('[name="language_filter"]').val(),
                    'filter_order_Dir' : $g('[name="filter_order_Dir"]').val(),
                    'limit' : $g('[name="limit"]').val(),
                    'publish_up': $g('[name="publish_up"]').val(),
                    'publish_down': $g('[name="publish_down"]').val()
                };
            view = view.split('&');
            obj['view'] = view[0];
            view = '&task=pages.setFilters';
            $g('body > .ba-tooltip').remove();
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : src+view,
                data : obj,
                success: function(msg){
                    $g('#gridbox-container').load(src+' #gridbox-content', function(){
                        loadPage();
                    });
                }
            });
        }

        function showUsersDialog(id, $this)
        {
            fontBtn = $this;
            $g('.user-sorting-select').each(function(){
                var value = $g(this).find('li[data-value="id"]').text().trim();
                $g(this).find('input[type="text"]').val(value);
                $g(this).find('input[type="hidden"]').val('id');
            });
            $g('.user-direction-select').each(function(){
                var value = $g(this).find('li[data-value="asc"]').text().trim();
                $g(this).find('input[type="text"]').val(value);
                $g(this).find('input[type="hidden"]').val('asc');
            });
            $g('.user-group-select').each(function(){
                var value = $g(this).find('li[data-value=""]').text().trim();
                $g(this).find('input[type="text"]').val(value);
                $g(this).find('input[type="hidden"]').val('');
            });
            $g('.user-sorting-select').trigger('customAction');
            $g('.search-ba-author-users').val('');
            $g('#ba-author-users-dialog .ba-options-group').css('display', '');
            $g('#ba-author-users-dialog .ba-group-wrapper').attr('data-id', id);
            $g('#ba-author-users-dialog').modal();
        }

        function showComemntsModeratorDialog()
        {
            $g('#ba-comments-users-dialog .user-sorting-select').each(function(){
                var value = $g(this).find('li[data-value="id"]').text().trim();
                $g(this).find('input[type="text"]').val(value);
                $g(this).find('input[type="hidden"]').val('id');
            });
            $g('#ba-comments-users-dialog .user-direction-select').each(function(){
                var value = $g(this).find('li[data-value="asc"]').text().trim();
                $g(this).find('input[type="text"]').val(value);
                $g(this).find('input[type="hidden"]').val('asc');
            });
            $g('#ba-comments-users-dialog .user-group-select').each(function(){
                var value = $g(this).find('li[data-value=""]').text().trim();
                $g(this).find('input[type="text"]').val(value);
                $g(this).find('input[type="hidden"]').val('');
            });
            $g('#ba-comments-users-dialog .user-sorting-select').trigger('customAction');
            $g('#ba-comments-users-dialog .search-ba-author-users').val('');
            $g('#ba-comments-users-dialog .ba-options-group').css('display', '');
            $g('#ba-comments-users-dialog').modal();
        }

        function createCalendar($this)
        {
            if ($this.dataset.type == 'range-dates') {
                $this.range = $g('[data-type="range-dates"]').not($this)[0];
            }
            Calendar.setup({
                inputField: $this,
                ifFormat: $this.dataset.format ? $this.dataset.format : "%Y-%m-%d %H:%M:%S",
                button : $this.nextElementSibling,
                align: "Tl",
                singleClick: true,
                firstDay: 0,
                disableFunc: function(date){
                    let input = this.params.inputField,
                        now = input.range ? new Date(input.range.value) : date;
                    if (input.dataset.type == 'range-dates' && input.dataset.key == 'to' && input.range.value != '') {
                        return now > date;
                    } else if (input.dataset.type == 'range-dates' && input.dataset.key == 'from' && input.range.value != '') {
                        return now < date;
                    }
                },
                onUpdate: function(){
                    let input = this.inputField;
                    if (input.dataset.type == 'range-dates' && input.dataset.key == 'to' && input.range.value == '') {
                        input.range.value = input.value;
                        input.value = '';
                    }
                    if (input.dataset.type == 'range-dates' && input.range.value && input.value
                        && input.dataset.action == 'filter') {
                        createAjax();
                    } else if (input.dataset.type == 'range-dates' && input.range.value && input.value) {
                        let params = [];
                        if (input.dataset.key == 'from') {
                            params.push(input.value);
                            params.push(input.range.value);
                        } else {
                            params.push(input.range.value);
                            params.push(input.value);
                        }
                        $g(input).trigger('dateUpdated', params);
                    }
                }
            });
            $this.dataset.created = 'true';
        }

        if (typeof(Calendar) == 'function') {
            $g('#published_on, #published_down, input.open-calendar-dialog').each(function(){
                createCalendar(this);
            });
            $g('.reset-date-field').on('click', function(){
                this.closest('.date-field-wrapper').querySelector('input').value = '';
            });
        }

        $g('#settings-dialog, #category-settings-dialog, #photo-editor-dialog').on('shown', function(){
            setTabsUnderline();
        });

        $g('.search-ba-author-users').off('input').on('input', function(){
            var search = this.value.trim(),
                modal = $g(this).closest('.ba-modal-lg');
            modal.find('.ba-options-group').each(function(){
                var name = this.querySelector('.ba-author-name').textContent.trim().toLowerCase(),
                    username = this.querySelector('.ba-author-username').textContent.trim().toLowerCase();
                if (name.indexOf(search) != -1 || username.indexOf(search) != -1) {
                    this.style.display = '';
                } else {
                    this.style.display = 'none';
                }
            });
        });
        $g('.user-group-select').off('customAction').on('customAction', function(){
            var group = this.querySelector('input[type="hidden"]').value,
                modal = $g(this).closest('.ba-modal-lg');
            modal.find('.ba-options-group').each(function(){
                var usergroup = this.querySelector('.ba-author-usergroup').textContent.trim();
                if (usergroup == group || group == '') {
                    this.style.display = '';
                } else {
                    this.style.display = 'none';
                }
            });
        });
        $g('.user-sorting-select, .user-direction-select').off('customAction').on('customAction', function(){
            var sort = $g('.user-sorting-select input[type="hidden"]').val(),
                dir = $g('.user-direction-select input[type="hidden"]').val(),
                modal = $g(this).closest('.ba-modal-lg'),
                items = Array.prototype.slice.call(modal[0].querySelectorAll('.ba-options-group'));
            items.sort(function(a, b){
                var text1 = a.querySelector('.ba-author-'+sort).textContent.trim(),
                    text2 = b.querySelector('.ba-author-'+sort).textContent.trim()
                if (text1 > text2) return 1;
                if (text1 < text2) return -1;
            });
            if (dir == 'desc') {
                items.reverse();
            }
            for (var i = 0; i < items.length; i++) {
                modal.find('.ba-group-wrapper').append(items[i]);
            }
        });

        $g('.comments-settings-apply').on('click', function(){
            let modal = $g('#comments-settings-dialog'),
                view = $g('input[name="ba_view"]').val(),
                moderators = modal.find('.comments-moderators-list')[0],
                obj = {
                    website: {},
                    commentsBannedList:{
                        emails: new Array(),
                        words: new Array(),
                        ip: new Array()
                    }
                }
            modal.find('.website-comments-settings').each(function(){
                if (this.type == 'checkbox') {
                    obj.website[this.dataset.website] = Number(this.checked);
                } else {
                    obj.website[this.dataset.website] = this.value.trim();
                }
            });
            obj.website[moderators.dataset.website] = '';
            $g(moderators).find('li[data-value]').each(function(){
                if (obj.website[moderators.dataset.website]) {
                    obj.website[moderators.dataset.website] += ',';
                }
                obj.website[moderators.dataset.website] += this.dataset.value;
            });
            $g('.comments-banned-list-wrapper ul').each(function(){
                var banned = this.dataset.type;
                $g(this).find('li:not(.enter-comments-banned-item)').each(function(){
                    obj.commentsBannedList[banned].push(this.textContent.trim())
                });
            });
            $g.ajax({
                type:"POST",
                dataType:'text',
                url: 'index.php?option=com_gridbox&task='+view+'.saveCommentsOptions',
                data : {
                    obj : JSON.stringify(obj)
                },
                complete: function(response){
                    $g('#comments-settings-dialog').modal('hide');
                    showNotice(response.responseText);
                }
            });
        });
        $g('#ba-comments-users-dialog .users-table-list .ba-author-username span').on('click', function(){
            var id = this.dataset.id,
                name = this.closest('.ba-group-element').querySelector('.ba-author-name').textContent.trim();
            if ($g('#comments-settings-dialog .comments-moderators-list li[data-value="'+id+'"]').length == 0) {
                var str = '<li data-value="'+id+'"><span>'+name+'</span><i class="zmdi zmdi-close"></i></li>';
                $g('#comments-settings-dialog .comments-moderators-list li.add-comments-moderator').before(str);
                $g('#ba-comments-users-dialog').modal('hide');
            }
        });
        $g('#comments-settings-dialog .comments-moderators-list').on('click', 'i.zmdi-close', function(){
            this.closest('li').remove();
        }).on('click', '.add-comments-moderator i', function(){
            showComemntsModeratorDialog();
        });
        $g('#comments-settings-dialog .comments-banned-list-wrapper').on('click', 'i.zmdi-close', function(){
            this.closest('li').remove();
        }).on('keyup', 'input[type="text"]', function(event){
            if (event.keyCode == 13 && this.value.trim()) {
                var str = '<li><span>'+this.value.trim()+'</span><i class="zmdi zmdi-close"></i></li>';
                $g(this).closest('li').before(str);
                this.value = '';
            }
        });
        $g('#comments_recaptcha option').each(function(){
            if (this.value == 'recaptcha' || this.value == 'recaptcha_invisible') {
                var str = '<li data-value="'+this.value+'">'+this.textContent.trim()+'</li>';
                $g(this).closest('.ba-group-element').find('ul').append(str);
            }
        });
        $g('.ba-subgroup-element').each(function(){
            app.setSubgroupChilds(this);
        });
        $g('#ba-author-users-dialog .users-table-list .ba-author-username span').off('click').on('click', function(){
            var currentUser = $g(this).closest('.ba-group-wrapper').attr('data-id'),
                id = this.dataset.id,
                username = this.textContent.trim(),
                flag = true,
                modal = $g('#create-new-tag-modal');
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : 'index.php?option=com_gridbox&task=authors.checkUser',
                data : {
                    currentUser: currentUser,
                    id: id
                },
                success: function(msg){
                    if (msg != 0) {
                        showNotice(msg, 'ba-alert');
                    } else {
                        $g(fontBtn).val(username).prev().val(id);
                        modal.find('input').each(function(){
                            if (!this.value.trim()) {
                                flag = false;
                            }
                        });
                        if (!flag) {
                            modal.find('.ba-btn-primary').removeClass('active-button');
                        } else {
                            modal.find('.ba-btn-primary').addClass('active-button');
                        }
                        $g('#ba-author-users-dialog').modal('hide');
                    }
                }
            });
        });

        function loadPage(firstLoading)
        {
            if ($g('.general-tabs').length > 0) {
                setTabsUnderline();
                $g('.general-tabs ul.uploader-nav').off('show').on('show', function(event){
                    event.stopPropagation();
                    var ind = new Array(),
                        ul = $g(event.currentTarget),
                        id = $g(event.relatedTarget).attr('href'),
                        aId = $g(event.target).attr('href');
                    ul.find('li a').each(function(i){
                        if (this == event.target) {
                            ind[0] = i;
                        }
                        if (this == event.relatedTarget) {
                            ind[1] = i;
                        }
                    });
                    if (ind[0] > ind[1]) {
                        $g(id).addClass('out-left');
                        $g(aId).addClass('right');
                        setTimeout(function(){
                            $g(id).removeClass('out-left');
                            $g(aId).removeClass('right');
                        }, 500);
                    } else {
                        $g(id).addClass('out-right');
                        $g(aId).addClass('left');
                        setTimeout(function(){
                            $g(id).removeClass('out-right');
                            $g(aId).removeClass('left');
                        }, 500);
                    }
                    var coord = event.target.getBoundingClientRect();
                    ul.next().css({
                        'left' : coord.left,
                        'right' : document.documentElement.clientWidth - coord.right,
                    });
                });
            }
            jQuery('#filter-bar .ba-custom-select input[type="text"]').each(function(){
                this.size = this.value.length;
            });
            $g('.open-calendar-dialog[data-action="filter"]').each(function(){
                if (!this.dataset.created) {
                    createCalendar(this);
                }
            })
            jQuery('#filter-bar .ba-custom-select').on('customAction', function(){
                var input = this.querySelector('input[type="text"]');
                input.size = input.value.length;
            });
            $g('span[data-sorting]').on('click', function(){
                var order = $g('[name="filter_order"]'),
                    direction = $g('[name="filter_order_Dir"]'),
                    dir = direction.val();
                if (order.val() == this.dataset.sorting) {
                    dir = dir == 'asc' ? 'desc' : 'asc';
                }
                order.val(this.dataset.sorting);
                direction.val(dir);
                createAjax();
            });
            if (document.querySelector('.payment-methods-table, .shipping-table') && !firstLoading) {
                setCkeditor()
            }
            $g('#theme-import-file').on('change', function(){
                if (this.files.length > 0) {
                    var array = this.files[0].name.split('.'),
                        n = array.length - 1,
                        ext = array[n];
                    $g('.theme-import-trigger').val(this.files[0].name);
                    if (ext != 'xml') {
                        showNotice(app._('UPLOAD_ERROR'), 'ba-alert');
                        $g('.apply-import').removeClass('active-button');
                    } else {
                        $g('.apply-import').addClass('active-button');
                    }
                }
            });

            $g('#theme-import-trigger').on('click', function(){
                document.getElementById('theme-import-file').click();
            });

            app.getAppLicense = function(){
                $.ajax({
                    type:"POST",
                    dataType:'text',
                    url:"index.php?option=com_gridbox&task=pages.getAppLicense",
                    data:{
                        data: gridboxUser.data
                    },
                    success : function(msg){
                        if ($g('#login-modal').hasClass('in')) {
                            $g('#login-modal').modal('hide');
                        }
                        if ('callback' in gridboxUser) {
                            gridboxUser.callback();
                        }
                        if (gridboxCallback == 'dashboard') {
                            showNotice(app._('YOUR_LICENSE_ACTIVE'));
                            $g('.ba-gridbox-dashboard-row.gridbox-activate-license').hide();
                            $g('.ba-gridbox-dashboard-row.gridbox-deactivate-license').css('display', '');
                            $g('.ba-dashboard-popover-trigger[data-target="ba-dashboard-about"]').each(function(){
                                let count = this.querySelector('.about-notifications-count');
                                count.textContent = count.textContent * 1 - 1;
                                if (count.textContent == 0) {
                                    this.querySelector('i').className = 'zmdi zmdi-info';
                                    count.style.display = 'none';
                                }
                            });
                        }
                    }
                });
            }

            $g('.login-button.active-button').on('click', function(event){
                event.preventDefault();
                if (!$g(this).attr('data-submit')) {
                    $g(this).attr('data-submit', 'false');
                    var script = document.createElement('script'),
                        url = 'https://www.balbooa.com/demo/index.php?',
                        domain = window.location.host.replace('www.', '');
                    domain += window.location.pathname.replace('index.php', '').replace('/administrator', '');
                    url += 'option=com_baupdater&task=gridbox.getGridboxUser';
                    url += '&login='+window.btoa($g('.ba-username').val().trim());
                    url += '&password='+window.btoa($g('.ba-password').val().trim());
                    if (domain[domain.length - 1] != '/') {
                        domain += '/';
                    }
                    url += '&domain='+window.btoa(domain);
                    url += '&time='+(+(new Date()));
                    script.src = url;
                    script.onload = function(){
                        $g('.login-button.active-button').removeAttr('data-submit');
                    }
                    document.head.appendChild(script);
                }
            });

            $g('.ba-username, .ba-password').on('keyup', function(event){
                if (event.keyCode == 13) {
                    document.querySelector('.login-button.active-button').click();
                }
            });

            $g('#filter_search').on('keydown', function(event){
                if (event.keyCode == 13) {
                    createAjax();
                }
            });

            $g('div[class$="-filter"] [type="hidden"], #limit').on('change', function(event){
                if (this.dataset.name) {
                    $g('input[name="'+this.dataset.name+'"]').val(this.value);
                }
                createAjax();
            });

            $g('.ba-custom-select > i, div.ba-custom-select input').on('click', function(event){
                var $this = $g(this),
                    parent = $this.parent();
                if (!parent.find('ul').hasClass('visible-select')) {
                    event.stopPropagation();
                    $g('.visible-select').removeClass('visible-select');
                    parent.find('ul').addClass('visible-select');
                    parent.find('li').off('click').one('click', function(){
                        var text = this.textContent.trim(),
                            val = this.dataset.value;
                        if (parent.hasClass('orders-status-select')) {
                            parent[0].style.setProperty('--status-color', this.dataset.color);
                        } else if (parent.hasClass('ba-store-statistic-select')) {
                            text = this.dataset.text;
                        }
                        parent.find('input[type="text"]').val(text);
                        parent.find('input[type="hidden"]').val(val).trigger('change');
                        parent.trigger('customAction');
                    });
                    parent.trigger('show');
                    setTimeout(function(){
                        $g('body').one('click', function(){
                            $g('.visible-select').removeClass('visible-select');
                        });
                    }, 50);
                }
            });

            $g('.ba-custom-author-select > i, div.ba-custom-author-select input').on('click', function(event){
                var $this = $g(this),
                    parent = $this.parent();
                if (!parent.find('ul').hasClass('visible-select')) {
                    event.stopPropagation();
                    $g('.visible-select').removeClass('visible-select');
                    parent.find('ul').addClass('visible-select');
                    parent.find('li').off('click').one('click', function(){
                        var text = this.textContent.trim(),
                            image = this.dataset.image,
                            authors = new Array(),
                            author = '',
                            li = parent.find('li[data-value]'),
                            val = this.dataset.value,
                            str = '<span class="selected-author" data-id="'+val;
                        str += '"><span class="ba-author-avatar" style="background-image: url(';
                        str += image+')"></span><span class="ba-author-name">'+text+'</span>';
                        str += '<i class="zmdi zmdi-close remove-selected-author"></i></span>';
                        parent.before(str);
                        parent.trigger('customAction');
                        parent.parent().find('.selected-author').each(function(){
                            authors.push(this.dataset.id);
                        });
                        li.each(function(){
                            if (authors.indexOf(this.dataset.value) == -1) {
                                this.style.display = ''
                            } else {
                                this.style.display = 'none';
                            }
                        });
                        if (li.length == authors.length) {
                            $g('.select-post-author').hide();
                        } else {
                            $g('.select-post-author').css('display', '');
                        }
                        author = authors.join(',');
                        parent.find('input[type="hidden"]').val(author);
                    });
                    parent.trigger('show');
                    setTimeout(function(){
                        $g('body').one('click', function(){
                            $g('.visible-select').removeClass('visible-select');
                        });
                    }, 50);
                }
            });

            $g('div.ba-custom-select').on('show', function(){
                if (!this.classList.contains('orders-status-select')) {
                    var $this = $g(this),
                        ul = $this.find('ul'),
                        value = $this.find('input[type="hidden"]').val();
                    ul.find('i').remove();
                    ul.find('.selected').removeClass('selected');
                    ul.find('li[data-value="'+value+'"]').addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
                }
            });

            $g('.reset-filtering').off('click').on('click', function(){
                $g('[name="filter_state"], [name$="_filter"], [name="publish_up"], [name="publish_down"]').val('');
                createAjax();
            });
            $g('.reset-calendar-filtering').off('click').on('click', function(){
                $g('[name="publish_up"], [name="publish_down"]').val('');
                createAjax();
            });
            $g('.enable-custom-pages-order').off('click').on('click', function(){
                var order = $g('[name="filter_order"]');
                if (order.val() != 'order_list') {
                    $g('[name="filter_order"]').val('order_list');
                } else {
                    $g('[name="filter_order"]').val('id');
                }
                createAjax();
            });
            $g('.create-categery').on('click', function(event){
                event.preventDefault();
                event.stopPropagation();
                if (!('permitted' in this.dataset)) {
                    var id = 0,
                        $this = $g('.category-list > ul').find('li.active');
                    if ($this.hasClass('ba-category')) {
                        var obj = $this.find('> a input[type="hidden"]').val();
                        obj = JSON.parse(obj);
                        id = obj.id;
                    }
                    $g('.parent-id').val(id);
                    $g('.category-name').val('');
                    $g('#create-category-modal').modal();
                } else {
                    showNotice(app._('CREATE_NOT_PERMITTED'));
                }
            });

            $g('body div .ba-tooltip').each(function(){
                setTooltip($g(this).parent());
            });

            $g('ul.root-list').off('click').on('click', 'i.zmdi-chevron-right', function(){
                var $this = $g(this).parent(),
                    blog = $g('input[name="blog"]').val(),
                    category = this.parentNode.dataset.id;
                getVisibleBranchClilds($this);
                if ($this.hasClass('visible-branch')) {
                    $this.removeClass('visible-branch');
                    deleteCookie('blog'+blog+'id'+category);
                } else {
                    $this.addClass('visible-branch');
                    setCookie('blog'+blog+'id'+category, 1);
                }
                getParentVisibleBranchClilds($this);
            });

            $g('.main-table tbody.order-list-sorting').each(function(){
                let handle = this.dataset.handle;
                $g(this).sortable({
                    handle : handle ? handle : '> tr > td',
                    selector : '> tr',
                    change: function(element){
                        var cid = new Array(),
                            order = new Array(),
                            root_order = new Array(),
                            type = 'pages',
                            category = $g('.order-list-sorting').attr('data-category');
                        $g('.order-list-sorting tr').each(function(){
                            cid.push($g(this).find('.select-td input[type="checkbox"]').val() * 1);
                            order.push($g(this).find('.title-cell input[name="order[]"]').val() * 1)
                            root_order.push($g(this).find('.title-cell input[name="root_order[]"]').val() * 1)
                        });
                        order.sort(function(a, b){
                            return a * 1 > b * 1 ? 1 : -1;
                        });
                        root_order.sort(function(a, b){
                            return a * 1 > b * 1 ? 1 : -1;
                        });
                        if ($g('.main-table').hasClass('tags-table')) {
                            type = 'tags';
                        } else if ($('.main-table').hasClass('authors-table')) {
                            type = 'authors';
                        } else if ($('.main-table').hasClass('shipping-table')) {
                            type = 'store_shipping';
                        } else if ($('.main-table').hasClass('payment-methods-table')) {
                            type = 'store_payment_methods';
                        }
                        $.ajax({
                            type : "POST",
                            dataType : 'text',
                            url : 'index.php?option=com_gridbox&task=pages.orderPages&tmpl=component',
                            data : {
                                cid : cid,
                                type: type,
                                category: category,
                                root_order: root_order,
                                order: order
                            }
                        });
                    },
                    group: 'pages'
                });
            });

            $('input[name="category_order_list"]').val(sortableInd);

            $('.category-list ul.root-list .root ul').each(function(ind){
                $(this).sortable({
                    handle : '> .ba-category > span > .sorting-handle',
                    selector : '> .ba-category',
                    change: function(element){
                        sortableInd = 1;
                        var data = new Array();
                        $('.category-list ul.root-list .ba-category').each(function(){
                            var obj = {
                                id : this.dataset.id,
                                order_list : sortableInd++
                            }
                            data.push(obj);
                        });
                        $('input[name="category_order_list"]').val(sortableInd);
                        $.ajax({
                            type : "POST",
                            dataType : 'text',
                            url : 'index.php?option=com_gridbox&task=apps.orderCategories&tmpl=component',
                            data : {
                                data : JSON.stringify(data)
                            },
                            success: function(msg){
                                
                            }
                        });
                    },
                    group : 'categories-'+ind
                });
            });

            $g('.sorting-container').each(function(){
                $g(this).sortable({
                    handle : '> .sorting-item .sortable-handle',
                    selector : '> .sorting-item',
                    group : 'sorting-container'
                });
            })

            $('ul.root-list a').on('click', function(event){
                event.preventDefault();
                event.stopPropagation();
                var src = this.href;
                window.history.pushState(null, null, src);
                $('#gridbox-container').load(src+' #gridbox-content', function(){
                    loadPage();
                });
            });

            $('ul.root-list li.ba-category').on('contextmenu', function(event){
                currentContext = $(this);
                showContext(event, $('.category-context-menu'));
            });

            $('ul.root-list i.open-category-settings').on('mousedown', function(event){
                event.stopPropagation();
                currentContext = $(this).closest('li.ba-category');
                $('span.category-settings').trigger('mousedown');
            });

            $('.main-table:not(.dashboard-content) tbody tr').on('contextmenu', function(event){
                if (document.querySelector('.page-context-menu')) {
                    currentContext = $(this);
                    showContext(event, $('.page-context-menu'));
                }
            });
            $g('.ba-title-click-trigger').on('click', function(){
                currentContext = $(this).closest('tr');
                $('span.tags-settings').trigger('mousedown');
            });
            $('.toggle-sidebar').on('click', function(event){
                event.preventDefault();
                event.stopPropagation();
                let body = $g('body');
                if (body.hasClass('visible-sidebar')) {
                    $g('body').removeClass('visible-sidebar');
                    deleteCookie('gridbox-sidebar');
                } else {
                    $g('body').addClass('visible-sidebar');
                    setCookie('gridbox-sidebar', 'visible', {
                        expires: 60 * 60 * 24 * 30 * 365
                    });
                }
            });
            $g('.sidebar-context-parent').on('click', function(event){
                if (!this.classList.contains('app-list') && !this.classList.contains('gridbox-store')) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }).on('mouseenter', function(event){
                var rect = this.getBoundingClientRect(),
                    div = $g('div.'+this.dataset.context),
                    h = div.height(),
                    y = rect.top - (rect.top + h > window.innerHeight ? h - rect.height : 0) + window.pageYOffset;
                div.css({
                    left: rect.right,
                    display: ''
                })[0].style.setProperty('--context-top', y+'px');
            }).on('mouseleave', function(event){
                if (!(event.relatedTarget && (event.relatedTarget.classList.contains(this.dataset.context)
                        || event.relatedTarget.closest('.'+this.dataset.context)))) {
                    $('div.'+this.dataset.context).hide();
                }
            });
            $('div.ba-context-menu[data-source]').on('mouseleave', function(event){
                if (!(event.relatedTarget && (event.relatedTarget.classList.contains(this.dataset.source)
                        || event.relatedTarget.closest('.'+this.dataset.source)))) {
                    this.style.display = 'none';
                }
            });

            $('.ba-create-tags').on('mousedown', function(event){
                event.preventDefault();
                var modal = $('#create-new-tag-modal');
                modal.find('.ba-btn-primary').removeClass('active-button');
                modal.find('input[type="text"]').val('');
                modal.modal();
            });

            $('#tag-name').off('input').on('input', function(){
                var flag = true,
                    modal = $(this).closest('.modal');
                modal.find('input').each(function(){
                    if (!this.value.trim()) {
                        flag = false;
                    }
                });
                if (flag) {
                    modal.find('.ba-btn-primary').addClass('active-button');
                } else {
                    modal.find('.ba-btn-primary').removeClass('active-button');
                }
            });

            $('.select-user').on('click', function(){
                showUsersDialog(0, this);
            });
            $('.blog-settings').on('mousedown', function(){
                var obj = $('#blog-data').val(),
                    value;
                obj = JSON.parse(obj);
                $('#category-settings-dialog input[data-key="core.edit.layouts"]').closest('.ba-group-element').removeAttr('disabled');
                app.setSubgroupChilds($('#category-settings-dialog .permission-action-wrapper')[0]);
                $('#category-settings-dialog .permissions-options').each(function(){
                    getPermissions(obj.id, 'app', this);
                });
                $g('.ba-dashboard-apps-dialog.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
                $('.category-title').val(obj.title);
                $('.category-id').val(obj.id);
                $('.category-alias').val(obj.alias);
                $('.apply-blog-settings').css('display', '');
                $('.category-settings-apply').hide();
                $('.blog-theme-select').closest('.ba-options-group').css('display', '');
                $('.category-access-select input[type="hidden"]').val(obj.access);
                var access = $('.category-access-select li[data-value="'+obj.access+'"]').text().trim(),
                    language = $('.category-language-select li[data-value="'+obj.language+'"]').text().trim();
                $('.category-access-select input[type="text"]').val(access);
                $('.category-language-select input[type="hidden"]').val(obj.language);
                $('.category-language-select input[type="text"]').val(language);
                value = $('.blog-theme-select li[data-value="'+obj.theme+'"]').text().trim();
                $('.blog-theme-select input[type="hidden"]').val(obj.theme);
                $('.blog-theme-select input[type="text"]').val(value);
                value = $('.category-robots-select li[data-value="'+obj.robots+'"]').text().trim();
                $('.category-robots-select input[type="hidden"]').val(obj.robots);
                $('.category-robots-select input[type="text"]').val(value);
                $('.category-meta-title').val(obj.meta_title);
                $('.category-meta-description').val(obj.meta_description);
                $('.category-meta-keywords').val(obj.meta_keywords);
                app.cke.description.setData(obj.description);
                if (obj.published == 1) {
                    $('.category-publish').attr('checked', true);
                } else {
                    $('.category-publish').removeAttr('checked');
                }
                let image = obj.image.indexOf('balbooa.com') == -1 ? JUri+obj.image : obj.image;
                $('.category-intro-image').val(obj.image).parent().find('.image-field-tooltip').css({
                    'background-image': obj.image ? 'url('+image+')' : ''
                });
                if (obj.share_image == 'share_image') {
                    obj.share_image = obj.image;
                }
                image = obj.share_image.indexOf('balbooa.com') == -1 ? JUri+obj.share_image : obj.share_image;
                $('.category-share-image').val(obj.share_image).parent().find('.image-field-tooltip').css({
                    'background-image': obj.share_image ? 'url('+image+')' : ''
                });
                $('.category-share-title').val(obj.share_title);
                $('.category-share-description').val(obj.share_description);
                $('#category-settings-dialog .sitemap-include').prop('checked', Boolean(obj.sitemap_include * 1));
                var range = $('#category-settings-dialog .priority').val(obj.priority).prev().val(obj.priority);
                setLinearWidth(range);
                $('#category-settings-dialog .changefreq').val(obj.changefreq).prev().each(function(){
                    this.value = $g(this).closest('.ba-custom-select').find('li[data-value="'+obj.changefreq+'"]').text().trim();
                });
                $g('#category-settings-dialog .set-group-display').each(function(){
                    var action = this.checked ? 'addClass' : 'removeClass';
                    $g(this).closest('.ba-group-element').nextAll()[action]('visible-subgroup subgroup-animation-ended');
                });
                $('i.zmdi-check.disabled-button').removeClass('disabled-button');
                $('.ba-alert-container').hide();
                $('#category-settings-dialog').modal();
            });
            $('.single-settings').on('click', function(){
                var blog = $('#blog-data').val();
                blog = JSON.parse(blog);
                oldTitle = blog.title;
                $('.blog-title').val(blog.title);
                $('.apply-single-settings').removeClass('active-button');
                $('#single-settings-modal').modal();
            });
            $g('.comments-settings').on('click', function(){
                let view = $g('input[name="ba_view"]').val();
                $.ajax({
                    type : "POST",
                    dataType : 'text',
                    url : 'index.php?option=com_gridbox&task='+view+'.getSettings',
                    success: function(msg){
                        let obj = JSON.parse(msg),
                            modal = $g('#comments-settings-dialog'),
                            str = '';
                        modal.find('.website-comments-settings').each(function(){
                            if (this.type == 'checkbox') {
                                this.checked = Boolean(obj.website[this.dataset.website] * 1);
                            } else {
                                this.value = obj.website[this.dataset.website];
                            }
                        });
                        if (obj.moderators == 'super_user') {
                            commentsModerators = new Array();
                            for (let i = 0; i < obj.users.length; i++) {
                                if (obj.users[i].level == 8) {
                                    commentsModerators.push(obj.users[i].id);
                                }
                            }
                        } else {
                            commentsModerators = obj.moderators.split(',');
                        }
                        for (let i = 0; i < obj.users.length; i++) {
                            if (commentsModerators.indexOf(obj.users[i].id) != -1) {
                                str += '<li data-value="'+obj.users[i].id+'"><span>'+obj.users[i].name;
                                str += '</span><i class="zmdi zmdi-close"></i></li>';
                            }
                        }
                        $g('.comments-moderators-list li:not(.add-comments-moderator)').remove();
                        $g('.comments-moderators-list li.add-comments-moderator').before(str);
                        $g('.comments-banned-list-wrapper li:not(.enter-comments-banned-item)').remove();
                        str = '';
                        for (let i = 0; i < obj.commentsBanList.emails.length; i++) {
                            str += '<li><span>'+obj.commentsBanList.emails[i].email+'</span><i class="zmdi zmdi-close"></i></li>';
                        }
                        $g('.comments-banned-emails li.enter-comments-banned-item').before(str);
                        str = '';
                        for (let i = 0; i < obj.commentsBanList.words.length; i++) {
                            str += '<li><span>'+obj.commentsBanList.words[i].word+'</span><i class="zmdi zmdi-close"></i></li>';
                        }
                        $g('.comments-banned-words li.enter-comments-banned-item').before(str);
                        str = '';
                        for (let i = 0; i < obj.commentsBanList.ip.length; i++) {
                            str += '<li><span>'+obj.commentsBanList.ip[i].ip+'</span><i class="zmdi zmdi-close"></i></li>';
                        }
                        $g('.comments-banned-ip li.enter-comments-banned-item').before(str);
                        modal.find('.set-group-display').each(function(){
                            var action = this.checked ? 'addClass' : 'removeClass';
                            $g(this).closest('.ba-group-element').nextAll()[action]('visible-subgroup subgroup-animation-ended');
                        });
                        modal.find('.ba-custom-select').each(function(){
                            let value = this.querySelector('input[type="hidden"]').value,
                                text = $g(this).find('li[data-value="'+value+'"]').text().trim();
                            if (!text) {
                                text = app._('NONE_SELECTED');
                                this.querySelector('input[type="hidden"]').value = '';
                            }
                            $g(this).find('input[type="text"]').val(text);
                        });
                        modal.modal();
                    }
                });
            });
            $('.blog-delete').on('click', function(event){
                event.preventDefault();
                deleteMode = 'pages.deleteApp';
                $('#delete-dialog').modal();
            });
            $('.app-duplicate').on('mousedown', function(){
                $g('.ba-dashboard-apps-dialog.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
                $(this).off('mousedown');
                var str = app._('LOADING')+'<img src="'+JUri;
                str += 'administrator/components/com_gridbox/assets/images/reload.svg"></img>';
                notification[0].className = 'notification-in';
                notification.find('p').html(str);
                Joomla.submitbutton('pages.duplicateApp');
            });
            $('.set-featured-post').on('click', function(){
                var id = $(this).closest('tr').find('td.select-td input[type="checkbox"]').val();
                $.ajax({
                    type : "POST",
                    dataType : 'text',
                    url : 'index.php?option=com_gridbox&task=apps.setFeatured',
                    data : {
                        id: id,
                        featured: this.dataset.featured
                    },
                    success: function(msg){
                        $('body > .ba-tooltip').remove();
                        reloadPage(msg);
                    }
                });
            });
            $('.default-action').on('mousedown', function(event){
                if (event.button > 1) {
                    return false;
                }
                event.stopPropagation();
                setTimeout(function(){
                    $(this).closest('div.ba-context-menu').hide();
                }, 150);
            });
        }

        $g('.default-action').on('click', function(){
            if (this.classList.contains('single-post-layout') && this.parentNode.dataset.count == 0) {
                event.preventDefault();
            }
        });

        $('.modal').on('hide', function(){
            $(this).addClass('ba-modal-close');
            setTimeout(function(){
                $('.ba-modal-close').removeClass('ba-modal-close');
            }, 500);
        });

        setTimeout(function(){
            $('.alert.alert-success').addClass('animation-out');
        }, 2000);

        app.checkGridboxData = function(obj){
            var url = 'https://www.balbooa.com/demo/index.php?',
                domain = window.location.host.replace('www.', ''),
                script = document.createElement('script');
            domain += window.location.pathname.replace('index.php', '').replace('/administrator', '');
            url += 'option=com_baupdater&task=gridbox.checkGridboxUser';
            url += '&data='+obj.data;
            if (domain[domain.length - 1] != '/') {
                domain += '/';
            }
            url += '&domain='+window.btoa(domain);
            script.src = url;
            document.head.appendChild(script);
        }

        app.showGridboxLogin = function(){
            $g('.ba-username').val('');
            $g('.ba-password').val('');
            $g('#login-modal').modal();
        }

        app.checkGridboxState = function(){
            $g.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_gridbox&task=pages.checkGridboxState",
                success: function(msg){
                    var flag = true,
                        obj;
                    if (msg) {
                        obj = JSON.parse(msg);
                        flag = !obj.data;
                    }
                    if (flag) {
                        app.showGridboxLogin();
                    } else {
                        app.checkGridboxData(obj);
                    }
                }
            });
        }

        $('#ba-gridbox-apps-dialog div.gridbox-app-element').on('click', function(event){
            app.loginItem = this;
            gridboxCallback = 'appAction';
            app.checkGridboxState();
        });

        $('.search-gridbox-apps').off('input').on('input', function(){
            var search = this.value.trim(),
                modal = $g(this).closest('.ba-modal-lg');
            modal.find('.gridbox-app-element').each(function(){
                var name = this.querySelector('.ba-title').textContent.trim().toLowerCase();
                if (name.indexOf(search) != -1) {
                    this.style.display = '';
                } else {
                    this.style.display = 'none';
                }
            });
        });

        $('.create-new-tag').on('click', function(event){
            event.preventDefault();
            if ($(this).hasClass('active-button')) {
                $('#create-new-tag-modal').modal('hide');
                Joomla.submitbutton('tags.addTag');
            }
        });

        $('.create-new-author').on('click', function(event){
            event.preventDefault();
            if ($(this).hasClass('active-button')) {
                $('#create-new-tag-modal').modal('hide');
                Joomla.submitbutton('authors.addAuthor');
            }
        });

        $('body').on('mousedown', function(){
            $('.context-active').removeClass('context-active');
            $('.ba-context-menu').hide();
        });

        $('.export-page').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            exportId = new Array(id);
            $('li.export-apps').hide();
            $('#export-dialog').modal();
            $('.apply-export').attr('data-export', 'pages');
        });

        $('.export-gridbox').on('mousedown', function(){
            exportId = new Array();
            $('li.export-apps').css('display', '');
            $('#export-dialog').modal();
            $('.apply-export').attr('data-export', 'gridbox');
        });

        $('.import-gridbox').on('mousedown', function(){
            $('#import-dialog').modal();
            $('.theme-import-trigger').val('');
            $('.apply-import').removeClass('active-button');
        });

        $('.import-joomla-content').on('mousedown', function(){
            $.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_gridbox&task=trashed.getCategories",
                success: function(msg){
                    var array = JSON.parse(msg)
                        str = '',
                        ul = $('#import-joomla-content-modal .availible-folders ul.root-list');
                    array.forEach(function(el, i){
                        var value = '{"id":'+el.id+', "type":"'+el.type+'"}';
                        str += '<li class="root single"><label><i class="zmdi zmdi-folder"></i>';
                        str += el.title+'<input type="radio" style="display:none;"';
                        str += " name='category_id' value='"+value+"'></label>";
                        str += '</li>';
                    });
                    ul.html(str);
                    $('.apply-import-joomla-content').removeClass('active-button');
                    $('#import-joomla-content-modal').modal();
                }
            });
        });

        $('.apply-import-joomla-content').on('click', function(event){
            event.preventDefault();
            if (this.classList.contains('active-button')) {
                $('#import-joomla-content-modal').modal('hide');
                var obj;
                $('#import-joomla-content-modal [name="category_id"]').each(function(){
                    if (this.checked) {
                        obj = JSON.parse(this.value);
                        return false;
                    }
                });
                $.ajax({
                    type:"POST",
                    dataType:'text',
                    data: {
                        type: obj.type
                    },
                    url:"index.php?option=com_gridbox&task=gridbox.checkJoomlaContentCount",
                    success: function(msg){
                        var data = JSON.parse(msg),
                            str = '<span>'+app._('INSTALLING');
                        str += ' <span class="installed-joomla-content">0</span> / '+data.count;
                        str +='</span><img src="'+JUri+'components/com_gridbox/assets/images/reload.svg"></img>';
                        if (data.count > 0) {
                            notification.find('p').html(str);
                            notification.removeClass('animation-out').addClass('notification-in');
                            importObject.joomla(data, obj);
                        }
                        
                    }
                });
            }
        });

        var importObject = {
            data: {},
            joomla: function(obj, app){
                this.data = {
                    tags: [],
                    categories: []
                }
                this.data.categories[1] = 0;
                this.joomlaCategories(obj, app);
            },
            joomlaArticles: function(obj, app){
                if (obj.articles && obj.articles.length > 0) {
                    var article = obj.articles.shift();
                    $.ajax({
                        type:"POST",
                        dataType:'text',
                        data: {
                            tags: importObject.data.tags,
                            categories: importObject.data.categories,
                            app_id: app.id,
                            app_type: app.type,
                            id: article.id
                        },
                        url:"index.php?option=com_gridbox&task=gridbox.importJoomlaArticles",
                        success: function(msg){
                            setTimeout(function(){
                                $('.installed-joomla-content').each(function(){
                                    this.textContent = this.textContent * 1 + 1;
                                });
                                importObject.joomlaArticles(obj, app);
                                reloadPage();
                            }, 100);
                        }
                    });
                } else {
                    showNotice(app._('INSTALLED'));
                }
            },
            joomlaCategories: function(obj, app){
                if (obj.categories && obj.categories.length > 0) {
                    var category = obj.categories.shift();
                    $.ajax({
                        type:"POST",
                        dataType:'text',
                        data: {
                            categories: importObject.data.categories,
                            app_id: app.id,
                            id: category.id
                        },
                        url:"index.php?option=com_gridbox&task=gridbox.importJoomlaCategories",
                        success: function(msg){
                            setTimeout(function(){
                                $('.installed-joomla-content').each(function(){
                                    this.textContent = this.textContent * 1 + 1;
                                });
                                importObject.data.categories[category.id] = msg;
                                importObject.joomlaCategories(obj, app);
                                reloadPage();
                            }, 100);
                        }
                    });
                } else {
                    this.joomlaTags(obj, app);
                }
            },
            joomlaTags: function(obj, app){
                if (obj.tags && obj.tags.length > 0) {
                    var tag = obj.tags.shift();
                    $.ajax({
                        type:"POST",
                        dataType:'text',
                        data: {
                            id: tag.id
                        },
                        url:"index.php?option=com_gridbox&task=gridbox.importJoomlaTags",
                        success: function(msg){
                            setTimeout(function(){
                                $('.installed-joomla-content').each(function(){
                                    this.textContent = this.textContent * 1 + 1;
                                });
                                importObject.data.tags[tag.id] = msg;
                                importObject.joomlaTags(obj, app);
                                reloadPage();
                            }, 100);
                        }
                    });
                } else {
                    this.joomlaArticles(obj, app);
                }
            }
        }

        $('#import-joomla-content-modal .availible-folders').on('change', '[name="category_id"]', function(event){
            event.stopPropagation();
            $('#import-joomla-content-modal .availible-folders > ul .active').removeClass('active');
            $(this).closest('li').addClass('active');
            $('#import-joomla-content-modal .ba-btn-primary').addClass('active-button');
        });
        $('span.gridbox-languages').on('mousedown', function(){
            $('#languages-dialog').modal();
        });

        $('#languages-dialog .languages-wrapper').on('click', 'span.language-title', function(){
            $('#languages-dialog').modal('hide');
            var installing = app._('INSTALLING')+'<img src="components/com_gridbox/assets/images/reload.svg"></img>';
            notification[0].className = 'notification-in';
            notification.find('p').html(installing);
            $.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_gridbox&task=pages.addLanguage&tmpl=component",
                data:{
                    method: window.atob('YmFzZTY0X2RlY29kZQ=='),
                    url: gridboxApi.languages[this.dataset.key].url,
                    zip: gridboxApi.languages[this.dataset.key].zip,
                },
                success: function(msg){
                    showNotice(msg);
                }
            });
        });

        $('.share-image-wrapper input[type="text"]').on('click', function(){
            checkIframe($('#uploader-modal'), 'uploader');
            fontBtn = this;
            uploadMode = 'introImage';
        }).on('change', function(){
            $(this).parent().find('.image-field-tooltip').css({
                'background-image': 'url('+JUri+this.value+')'
            });
        });

        $('.select-permission-usergroup').on('customAction', function(){
            let data = this.usergroup,
                group = this.querySelector('input[type="hidden"]').value;
            $g(this).closest('.permissions-options').find('.permission-action-wrapper').each(function(){
                setGroupPermissions(data.id, data.type, group, this);
            });
        });

        $('.select-permission-action').on('customAction', function(){
            let input = this.querySelector('input[type="hidden"]');
            calculateNewPermissions(input.usergroup, input.dataset.key, input.value, input.closest('.permission-action-wrapper'));
        });

        $('.reset-share-image').on('click', function(){
            $(this).parent().find('input[type="text"]').val('');
            $(this).parent().find('.image-field-tooltip').css('background-image', '');
        });

        $('.select-author-username').on('click', function(){
            showUsersDialog(this.dataset.user_id, this);
        });

        $(document).on('click', '.remove-selected-author', function(){
            $(this).parent().remove();
            var authors = new Array(),
                li = $('.select-post-author li[data-value]'),
                author = '';
            $('.selected-author').each(function(){
                authors.push(this.dataset.id);
            });
            li.each(function(){
                if (authors.indexOf(this.dataset.value) == -1) {
                    this.style.display = ''
                } else {
                    this.style.display = 'none';
                }
            });
            if (li.length == authors.length) {
                $('.select-post-author').hide();
            } else {
                $('.select-post-author').css('display', '');
            }
            author = authors.join(',');
            $('.select-post-author input[type="hidden"]').val(author);
        });

        if (window.addEventListener) {
            window.addEventListener("message", function(event){listenMessage(event)}, false);
        } else {
            window.attachEvent("onmessage", function(event){listenMessage(event)});
        }

        $('.blog-title').on('input', function(){
            var val = $(this).val();
            val = $.trim(val);
            if (val && val != oldTitle) {
                $(this).closest('.modal').find('.ba-btn-primary').addClass('active-button');
            } else {
                $(this).closest('.modal').find('.ba-btn-primary').removeClass('active-button');
            }
        });

        $('.apply-blog-settings').on('click', function(event){
            event.preventDefault();
            event.stopPropagation();
            var description = app.cke.description.getData();
            $('.category-description').val(description);
            $('#category-settings-dialog').modal('hide');
            updatePermissions();
            Joomla.submitbutton('apps.applySettings');
        });

        $('.apply-single-settings').on('click', function(event){
            event.preventDefault();
            event.stopPropagation();
            if (!$(this).hasClass('active-button')) {
                return false;
            }
            $('#single-settings-modal').modal('hide');
            Joomla.submitbutton('pages.applySingle');
        });

        $g('.activate-link').on('click', function(event){
            event.preventDefault();
            $g('.ba-dashboard-about.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
            gridboxCallback = 'dashboard';
            app.showGridboxLogin();
        });

        $g('.deactivate-link').on('click', function(event){
            event.preventDefault();
            $g('.ba-dashboard-about.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
            $g('#deactivate-dialog').modal();
        });

        $g('#apply-deactivate').on('click', function(event){
            event.preventDefault();
            $g.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_gridbox&task=pages.checkGridboxState",
                success: function(msg){
                    var obj = JSON.parse(msg),
                        url = 'https://www.balbooa.com/demo/index.php?',
                        script = document.createElement('script');
                    url += 'option=com_baupdater&task=gridbox.deactivateLicense';
                    url += '&data='+obj.data;
                    url += '&time='+(+(new Date()));
                    script.onload = function(){
                        $g.ajax({
                            type : "POST",
                            dataType : 'text',
                            url : JUri+"index.php?option=com_gridbox&task=editor.setAppLicense",
                            success: function(msg){
                                app.showNotice(app._('SUCCESSFULY_DEACTIVATED'));
                                $g('.ba-dashboard-popover-trigger[data-target="ba-dashboard-about"]').each(function(){
                                    this.querySelector('i').className = 'zmdi zmdi-notifications';
                                    let count = this.querySelector('.about-notifications-count');
                                    count.textContent = count.textContent * 1 + 1;
                                    count.style.display = '';
                                });
                                $g('.gridbox-activate-license').css('display', '');
                                $g('.gridbox-deactivate-license').hide();
                            }
                        });
                    }
                    script.src = url;
                    document.head.appendChild(script);
                }
            });
            $g('#deactivate-dialog').modal('hide');
        });
        
        $g('.gridbox-update-wrapper').off('click').on('click', '.update-link', function(event){
            event.preventDefault();
            $g('.ba-dashboard-about.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
            gridboxCallback = 'updateAction';
            app.checkGridboxState();
        });

        $('.apply-import').on('click', function(event){
            event.preventDefault();
            var files = document.getElementById('theme-import-file').files;
            if (files.length > 0 && this.classList.contains('active-button')) {
                var data = new FormData(),
                    url = document.getElementById("adminForm").action+"&task=themes.uploadTheme&file="+files[0].name,
                    installing = app._('INSTALLING')+'<img src="components/com_gridbox/assets/images/reload.svg"></img>';
                notification[0].className = 'notification-in';
                notification.find('p').html(installing);
                data.append('file', files[0]);
                $.ajax({
                    url: url,
                    data: data,
                    type: 'post',
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function(msg){
                        setTimeout(function(){
                            notification.removeClass('notification-in').addClass('animation-out');
                            setTimeout(function(){
                                showNotice(msg, '');
                                setTimeout(function(){
                                    window.location.href = window.location.href;
                                }, 400);
                            }, 400);
                        }, 2000);
                    }
                });
                $('#import-dialog').modal('hide');
            }
        });
        
        $('.apply-export').on('click', function(event){
            event.preventDefault();
            if (this.dataset.export == 'app') {
                exportId = new Array($('input[name="blog"]').val());
            }
            var exportPages = {
                "id" : exportId,
                type : this.dataset.export,
                "menu" : $('.menu-export').prop('checked')
            }
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_gridbox&view=pages&task=pages.exportXML",
                data : {
                    'export_data' : JSON.stringify(exportPages)
                },
                success: function(msg){
                    var msg = JSON.parse(msg);
                    if (msg.success) {
                        var a = document.createElement('a');
                        a.setAttribute('download', '');
                        a.style.display = 'none';
                        a.href = JUri+msg.message;
                        document.body.appendChild(a);
                        a.click();
                    }
                }
            });
            $('#export-dialog').modal('hide');
        });

        setCkeditor();

        $('span.category-settings').on('mousedown', function(){
            var obj = currentContext.find('> a input').val();
            obj = JSON.parse(obj);
            $('#category-settings-dialog input[data-key="core.edit.layouts"]').closest('.ba-group-element').attr('disabled', 'true');
            app.setSubgroupChilds($('#category-settings-dialog .permission-action-wrapper')[0]);
            $('#category-settings-dialog .permissions-options').each(function(){
                getPermissions(obj.id, 'category', this);
            });
            $('.category-title').val(obj.title);
            $('.category-id').val(obj.id);
            $('.category-parent').val(obj.parent);
            $('.category-alias').val(obj.alias);
            $('.apply-blog-settings').hide();
            $('.category-settings-apply').css('display', '');
            $('.blog-theme-select').closest('.ba-options-group').hide();
            $('#category-settings-dialog .cke-editor-container').closest('.ba-options-group')
                .css('display', '').prev().css('display', '');
            $('.category-access-select input[type="hidden"]').val(obj.access);
            var access = $('.category-access-select li[data-value="'+obj.access+'"]').text().trim(),
                language = $('.category-language-select li[data-value="'+obj.language+'"]').text().trim();
            $('.category-access-select input[type="text"]').val(access);
            $('.category-language-select input[type="hidden"]').val(obj.language);
            $('.category-language-select input[type="text"]').val(language);
            var value = $('.category-robots-select li[data-value="'+obj.robots+'"]').text().trim();
            $('.category-robots-select input[type="hidden"]').val(obj.robots);
            $('.category-robots-select input[type="text"]').val(value);
            app.cke.description.setData(obj.description);
            $('.category-meta-title').val(obj.meta_title);
            $('.category-meta-description').val(obj.meta_description);
            $('.category-meta-keywords').val(obj.meta_keywords);
            if (obj.published == 1) {
                $('.category-publish').attr('checked', true);
            } else {
                $('.category-publish').removeAttr('checked');
            }
            let image = obj.image.indexOf('balbooa.com') == -1 ? JUri+obj.image : obj.image;
            $('.category-intro-image').val(obj.image).parent().find('.image-field-tooltip').css({
                'background-image': obj.image ? 'url('+image+')' : ''
            });
            if (obj.share_image == 'share_image') {
                obj.share_image = obj.image;
            }
            image = obj.share_image.indexOf('balbooa.com') == -1 ? JUri+obj.share_image : obj.share_image;
            $('.category-share-image').val(obj.share_image).parent().find('.image-field-tooltip').css({
                'background-image': obj.share_image ? 'url('+image+')' : ''
            });
            $('.category-share-title').val(obj.share_title);
            $('.category-share-description').val(obj.share_description);
            $('#category-settings-dialog .sitemap-include').prop('checked', Boolean(obj.sitemap_include * 1));
            var range = $('#category-settings-dialog .priority').val(obj.priority).prev().val(obj.priority);
            setLinearWidth(range);
            $('#category-settings-dialog .changefreq').val(obj.changefreq).prev().each(function(){
                this.value = $g(this).closest('.ba-custom-select').find('li[data-value="'+obj.changefreq+'"]').text().trim();
            });
            $g('#category-settings-dialog .set-group-display').each(function(){
                var action = this.checked ? 'addClass' : 'removeClass';
                $g(this).closest('.ba-group-element').nextAll()[action]('visible-subgroup subgroup-animation-ended');
            });
            $('i.zmdi-check.disabled-button').removeClass('disabled-button');
            $('.ba-alert-container').hide();
            $('#category-settings-dialog').modal();
        });

        $('span.tags-settings').on('mousedown', function(){
            var obj = currentContext.find('.select-td input[type="hidden"]').val();
            obj = JSON.parse(obj);
            $('.category-title').val(obj.title);
            $('.category-id').val(obj.id);
            $('.category-alias').val(obj.alias);
            $('#category-settings-dialog .cke-editor-container');
            $('.category-access-select').each(function(){
                var value = $(this).find('li[data-value="'+obj.access+'"]').text().trim();
                $(this).find('input[type="hidden"]').val(obj.access);
                $(this).find('input[type="text"]').val(value);
            });
            $('.category-language-select').each(function(){
                var value = $(this).find('li[data-value="'+obj.language+'"]').text().trim();
                $(this).find('input[type="hidden"]').val(obj.language);
                $(this).find('input[type="text"]').val(value);
            });
            $('.category-robots-select').each(function(){
                var value = $(this).find('li[data-value="'+obj.robots+'"]').text().trim();
                $(this).find('input[type="hidden"]').val(obj.robots);
                $(this).find('input[type="text"]').val(value);
            });
            $('.select-author-username').each(function(){
                this.value = obj.username;
                this.dataset.user_id = obj.user_id;
                this.previousElementSibling.value = obj.user_id;
            });
            $('.select-author-avatar').each(function(){
                let image = obj.avatar.indexOf('balbooa.com') == -1 ? JUri+obj.avatar : obj.avatar;
                $(this).val(obj.avatar).parent().find('.image-field-tooltip').css({
                    'background-image': obj.avatar ? 'url('+image+')' : ''
                });
            });
            $g('.authors-links-wrapper').each(function(){
                app.authorsSocial = JSON.parse(obj.author_social);
                let str = '';
                for (var ind in app.authorsSocial) {
                    str += getAuthorPatern(ind);
                }
                $g('.authors-links-list').html(str);
            });
            app.cke.description.setData(obj.description);
            $('.category-meta-title').val(obj.meta_title);
            $('.category-meta-description').val(obj.meta_description);
            $('.category-meta-keywords').val(obj.meta_keywords);
            image = obj.image.indexOf('balbooa.com') == -1 ? JUri+obj.image : obj.image;
            $('.category-intro-image').val(obj.image).parent().find('.image-field-tooltip').css({
                'background-image': obj.image ? 'url('+image+')' : ''
            });
            if (obj.share_image == 'share_image') {
                obj.share_image = obj.image;
            }
            image = obj.share_image.indexOf('balbooa.com') == -1 ? JUri+obj.share_image : obj.share_image;
            $('.category-share-image').val(obj.share_image).parent().find('.image-field-tooltip').css({
                'background-image': obj.share_image ? 'url('+image+')' : ''
            });
            $('.category-share-title').val(obj.share_title);
            $('.category-share-description').val(obj.share_description);
            $('#category-settings-dialog .sitemap-include').prop('checked', Boolean(obj.sitemap_include * 1));
            var range = $('#category-settings-dialog .priority').val(obj.priority).prev().val(obj.priority);
            setLinearWidth(range);
            $('#category-settings-dialog .changefreq').val(obj.changefreq).prev().each(function(){
                this.value = $g(this).closest('.ba-custom-select').find('li[data-value="'+obj.changefreq+'"]').text().trim();
            });
            $g('#category-settings-dialog .set-group-display').each(function(){
                var action = this.checked ? 'addClass' : 'removeClass';
                $g(this).closest('.ba-group-element').nextAll()[action]('visible-subgroup subgroup-animation-ended');
            });
            $('i.zmdi-check.disabled-button').removeClass('disabled-button');
            $('.ba-alert-container').hide();
            $('#category-settings-dialog').modal();
        });

        $('.tags-settings-apply').on('click', function(){
            if ($(this).hasClass('disabled-button')) {
                return false;
            }
            var description = app.cke.description.getData();
            $('.category-description').val(description);
            $('#category-settings-dialog').modal('hide');
            Joomla.submitbutton('tags.updateTags');
        });

        $('.authors-settings-apply').on('click', function(){
            if ($(this).hasClass('disabled-button')) {
                return false;
            }
            var description = app.cke.description.getData(),
                social = JSON.stringify(app.authorsSocial);
            $('.category-description').val(description);
            $('textarea[name="author_social"]').val(social);
            $('#category-settings-dialog').modal('hide');
            Joomla.submitbutton('authors.updateAuthors');
        });

        $('.category-settings-apply').on('click', function(){
            if ($(this).hasClass('disabled-button')) {
                return false;
            }
            var description = app.cke.description.getData();
            $('.category-description').val(description);
            $('#category-settings-dialog').modal('hide');
            updatePermissions();
            Joomla.submitbutton('apps.updateCategory');
        });

        $('span.category-delete').on('mousedown', function(){
            var obj = currentContext.find('> a input[type="hidden"]').val();
            $('#context-item').val(obj);
            deleteMode = 'apps.deleteCategory';
            $('#delete-dialog').modal();
        });

        $('span.category-duplicate').on('mousedown', function(){
            var id = currentContext.attr('data-id');
            $('#context-item').val(id);
            Joomla.submitbutton('apps.categoryDuplicate');
        });

        $('span.category-move').on('mousedown', function(){
            var id = currentContext.attr('data-id');
            moveTo = 'apps.categoryMoveTo';
            $('#context-item').val(id);
            showMoveTo();
        });

        function showMoveTo()
        {
            $.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_gridbox&task=trashed.getCategories",
                success: function(msg){
                    msg = JSON.parse(msg);
                    var str = drawBlogMoveTo(msg),
                        ul = $('#move-to-modal .availible-folders ul.root-list');
                    if (moveTo != 'apps.moveTo' && currentContext.hasClass('ba-category')) {
                        ul.addClass('ba-move-category');
                    } else {
                        ul.removeClass('ba-move-category');
                    }
                    ul.html(str);
                    $('.apply-move').removeClass('active-button');
                    $('#move-to-modal').modal();
                }
            });
        }

        $('span.page-move').on('mousedown', function(){
            var obj = currentContext.find('.select-td input[type="hidden"]').val();
            obj = JSON.parse(obj)
            moveTo = 'apps.pageMoveTo';
            $('#context-item').val(obj.id);
            showMoveTo();
        });

        $('span.page-move-single').on('mousedown', function(){
            var obj = currentContext.find('.select-td input[type="hidden"]').val();
            obj = JSON.parse(obj)
            moveTo = 'trashed.restoreSingle';
            $('#context-item').val(obj.id);
            showMoveTo();
        });

        $('span.page-duplicate').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            Joomla.submitbutton('pages.contextDuplicate');
        });

        $('span.tags-duplicate').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            Joomla.submitbutton('tags.contextDuplicate');
        });

        $('span.page-trash').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            deleteMode = 'pages.contextTrash';
            $('#delete-dialog').modal();
        });

        $('span.tags-delete').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            deleteMode = 'tags.contextDelete';
            $('#delete-dialog').modal();
        });

        $('span.comments-delete').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val(),
                view = $g('input[name="ba_view"]').val();
            $('#context-item').val(id);
            deleteMode = view+'.contextDelete';
            $('#delete-dialog').modal();
        });

        $('span.comments-approve').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val(),
                view = $g('input[name="ba_view"]').val();
            $('#context-item').val(id);
            Joomla.submitbutton(view+'.contextApprove');
        });

        $('span.comments-spam').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val(),
                view = $g('input[name="ba_view"]').val();
            $('#context-item').val(id);
            Joomla.submitbutton(view+'.contextSpam');
        });

        $('span.authors-delete').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            deleteMode = 'authors.contextDelete';
            $('#delete-dialog').modal();
        });

        $('span.blog-duplicate').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            var str = app._('LOADING')+'<img src="'+JUri;
            str += 'administrator/components/com_gridbox/assets/images/reload.svg"></img>';
            notification[0].className = 'notification-in';
            notification.find('p').html(str);
            Joomla.submitbutton('apps.contextDuplicate');
        });

        $('span.blog-trash').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            deleteMode = 'apps.contextTrash';
            $('#delete-dialog').modal();
        });

        $('input.category-name').on('input', function(){
            $('#create-new-category')[this.value.trim() ? 'addClass' : 'removeClass']('active-button');
        });

        $('#create-new-category').on('click', function(event){
            event.preventDefault();
            event.stopPropagation();
            if (this.classList.contains('active-button')) {
                $('#create-category-modal').modal('hide');
                Joomla.submitbutton('apps.addCategory');
            }
        })

        $('#apply-delete').on('click', function(event){
            event.preventDefault();
            event.stopPropagation();
            if (deleteMode == 'state.delete') {
                app.states.deleteState();
            } else if (deleteMode == 'country.delete') {
                app.country.deleteCountry();
            } else if (typeof(deleteMode) == 'object' && deleteMode.action && deleteMode.action == 'pages.deleteGridboxAppItem') {
                $.ajax({
                    type : "POST",
                    dataType : 'text',
                    url : "index.php?option=com_gridbox&task=pages.deleteGridboxAppItem",
                    data: {
                        'blog': deleteMode.id
                    },
                    complete:function(msg){
                        deleteMode.item.remove();
                        showNotice(app._('COM_GRIDBOX_N_ITEMS_DELETED'));
                    }
                });
            } else if (typeof(deleteMode) == 'object' && deleteMode.type == 'delete-sorting-item') {
                $g(deleteMode.container).find('.sorting-checkbox input').each(function(){
                    if (this.checked) {
                        this.closest('.sorting-item').remove();
                    }
                });
                deleteMode.btn.classList.add('disabled');
            } else if (typeof(deleteMode) == 'object' && ('classList' in deleteMode)
                && $g(deleteMode).hasClass('delete-author-social-link')) {
                let key = deleteMode.closest('.authors-link').dataset.key,
                    list = {},
                    i = 0;
                for (let ind in app.authorsSocial) {
                    if (ind != key) {
                        list[i++] = app.authorsSocial[ind];
                    }
                }
                app.authorsSocial = list;
                let str = '';
                for (var ind in app.authorsSocial) {
                    str += getAuthorPatern(ind);
                }
                $g('.authors-links-list').html(str);
            } else if (deleteMode == 'delete-order-cart-item') {
                $g('.sorting-container .sorting-checkbox input').each(function(){
                    if (this.checked) {
                        let key = this.dataset.variation ? this.dataset.variation : this.value;
                        delete(app.cart.products[key]);
                        this.closest('.sorting-item').remove();
                    }
                });
                app.calculateOrder();
            } else if (deleteMode == 'single') {
                Joomla.submitbutton('themes.contextDelete');
            } else if (deleteMode == 'array') {
                Joomla.submitform('themes.delete');
            } else if (deleteMode == 'apps.addTrash' || deleteMode == 'pages.addTrash' || deleteMode == 'tags.delete'
                || deleteMode == 'orders.delete' || deleteMode == 'productoptions.delete'
                || deleteMode == 'paymentmethods.delete' || deleteMode == 'shipping.delete' || deleteMode == 'promocodes.delete') {
                Joomla.submitform(deleteMode);
            } else {
                submitTask = deleteMode;
                Joomla.submitbutton(deleteMode);
            }
            $('#delete-dialog').modal('hide');
        });

        $('span.page-delete').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            Joomla.submitbutton('pages.contextDelete');
        });

        $('span.trashed-delete').on('mousedown', function(){
            var id = currentContext.find('input[type="checkbox"]').val();
            $('#context-item').val(id);
            deleteMode = 'trashed.contextDelete'
            $('#delete-dialog').modal();
        });

        $('span.trashed-restore').on('mousedown', function(){
            var obj = currentContext.find('.select-td input[type="hidden"]').val();
            obj = JSON.parse(obj);
            $('#context-item').val(obj.id);
            if (obj.app_type == 'single') {
                moveTo = 'trashed.restoreSingle';
            } else if (obj.app_type) {
                moveTo = 'trashed.restoreBlog';
            }
            showMoveTo();
        });

        $('#move-to-modal .availible-folders').on('change', '[name="category_id"]', function(event){
            event.stopPropagation();
            let li = $(this).closest('li');
            if (li.hasClass('root') && !li.hasClass('single') && !currentContext.hasClass('ba-category')) {
                return false;
            }
            $('#move-to-modal .availible-folders > ul .active').removeClass('active');
            li.addClass('active');
            $('#move-to-modal .apply-move').addClass('active-button');
        });

        $('#move-to-modal .apply-move').on('click', function(event){
            event.preventDefault();
            if (!$(this).hasClass('active-button')) {
                return false;
            }
            $('#move-to-modal').modal('hide');
            Joomla.submitform(moveTo);
        });

        $('span.page-settings').on('mousedown', function(){
            var obj = currentContext.find('.select-td input[type="hidden"]').val();
            pageId = currentContext.find('.select-td input[type="checkbox"]').val();
            obj = JSON.parse(obj);
            item = $(this);
            if (!this.dataset.callback) {
                showPageSettings(obj, currentContext[0]);
            } else {
                app[this.dataset.callback](obj);
            }
        });

        $('#toolbar-settings').on('click', function(){
            var options = new Array(),
                obj = tr = null;
            $('.table-striped tbody input[type="checkbox"]').each(function(){
                if ($(this).prop('checked')) {
                    tr = this.closest('tr');
                    obj = this.closest('td').querySelector('input[type="hidden"]').value;
                    options.push('option');
                }
            });
            if (options.length != 1) {
                alert($('.jlib-selection').val());
                return false;
            }
            obj = JSON.parse(obj);
            if (!this.dataset.callback) {
                showPageSettings(obj, tr);
            } else {
                app[this.dataset.callback](obj);
            }
        });

        $('.meta-tags .picked-tags .search-tag input').on('mousedown', function(){
            $('ul.all-tags').css({
                'left': this.parentNode.offsetLeft
            });
            var title = this.value.trim().toLowerCase();
            setTimeout(function(){
                app.searchMetaTags(title);
                $('body').one('mousedown', function(){
                    $('.all-tags li').hide();
                });
            }, 100);
        }).on('keyup', function(event){
            var title = this.value.trim().toLowerCase();
            if (event.keyCode == 13) {
                if (!title) {
                    return false;
                }
                var str = '<li class="tags-chosen"><span>',
                    tagId = 'new$'+title;
                $('.all-tags li').each(function(){
                    var search = $(this).text();
                    search = $.trim(search);
                    search = search.toLowerCase();
                    if (title.toLowerCase() == search) {
                        $(this).addClass('selected-tag');
                        tagId = $(this).attr('data-id');
                        return false;
                    }
                });
                if ($('.picked-tags .tags-chosen i[data-remove="'+tagId+'"]').length > 0) {
                    return false;
                }
                str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
                $('.picked-tags .search-tag').before(str);
                str = '<option value="'+tagId+'" selected>'+title+'</option>';
                $('select.meta_tags').append(str);
                $(this).val('');
                $('.all-tags li').hide();
                event.stopPropagation();
                event.preventDefault();
                return false;
            } else {
                app.searchMetaTags(title);
            }
        });

        $('.all-tags').on('mousedown', 'li', function(){
            if ($(this).hasClass('selected-tag')) {
                return false;
            }
            var title = $(this).text(),
                tagId = $(this).attr('data-id');
            title = $.trim(title);
            var str = '<li class="tags-chosen"><span>';
            str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
            $('.picked-tags .search-tag').before(str);
            str = '<option value="'+tagId+'" selected>'+title+'</option>';
            $('select.meta_tags').append(str);
            $('.meta-tags .picked-tags .search-tag input').val('');
            $('.all-tags li').hide();
            $(this).addClass('selected-tag');
        });

        $('.meta-tags .picked-tags').on('click', '.zmdi.zmdi-close', function(){
            var del = $(this).attr('data-remove');
            $('select.meta_tags option[value="'+del+'"]').remove();
            $(this).closest('li').remove();
            $('.all-tags li[data-id="'+del+'"]').removeClass('selected-tag');
            $('.all-tags li').hide();
        });

        app.updateThemes = function(obj){
                var installing = app._('INSTALLING')+'<img src="components/com_gridbox/assets/images/reload.svg"></img>';
                notification[0].className = 'notification-in';
                notification.find('p').html(installing);
                if (window.gridboxApi.plugins) {
                    $.ajax({
                        type:"POST",
                        dataType:'text',
                        url:"index.php?option=com_gridbox&task=pages.addPlugins&tmpl=component",
                        data:{
                            'plugins' : JSON.stringify(window.gridboxApi.plugins)
                        },
                        async : false
                    });
                }
                var data = window.atob(obj.data),
                    XHR = new XMLHttpRequest(),
                    url = "index.php?option=com_gridbox&task=themes.downloadTheme";
                XHR.onreadystatechange = function(e) {
                    if (XHR.readyState == 4) {
                        if (XHR.status == 200 && XHR.responseText == app._('SUCCESS_UPLOAD')) {
                            notification.removeClass('notification-in').addClass('animation-out');
                            setTimeout(function(){
                                showNotice(XHR.responseText);
                                setTimeout(function(){
                                    window.location.href = window.location.href;
                                }, 400);
                            }, 400);
                        } else {
                            $.ajax({
                                type:"POST",
                                dataType:'text',
                                url:"index.php?option=com_gridbox&task=themes.downloadThemeCurl",
                                data: {
                                    url : obj.url
                                },
                                success : function(msg){
                                    notification.removeClass('notification-in').addClass('animation-out');
                                    setTimeout(function(){
                                        showNotice(msg);
                                        setTimeout(function(){
                                            window.location.href = window.location.href;
                                        }, 400);
                                    }, 400);
                                }
                            });
                        }
                    }
                }
                XHR.open("POST", url, true);
                XHR.send(data);
            }

        app.updateApps = function(obj){
            if (obj.type) {
                $g.ajax({
                    type:"POST",
                    dataType:'text',
                    data: {
                        type: obj.type
                    },
                    url:"index.php?option=com_gridbox&task=pages.addApp",
                    error: function(msg){
                        console.info(msg.responseText)
                    },
                    success: function(msg){
                        $g('#ba-gridbox-apps-dialog').modal('hide');
                        reloadPage(app._('SUCCESS_INSTALL'));
                    }
                });
            } else if (obj.system) {
                if (obj.installed == 1) {
                    return false;
                }
                $g.ajax({
                    type:"POST",
                    dataType:'text',
                    data: {
                        type: obj.system
                    },
                    url:"index.php?option=com_gridbox&task=appslist.addSystemApp",
                    success: function(msg){
                        obj.installed = 1;
                        $g('#ba-gridbox-apps-dialog').modal('hide');
                        reloadPage(app._('SUCCESS_INSTALL'));
                    }
                });
            }
        }

        app.updateGridbox = function(package){
            $g('.ba-dashboard-about.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
            setTimeout(function(){
                var str = app._('UPDATING')+'<img src="'+JUri;
                str += 'administrator/components/com_gridbox/assets/images/reload.svg"></img>';
                notification[0].className = 'notification-in';
                notification.find('p').html(str);
            }, 400);
            var XHR = new XMLHttpRequest(),
                url = 'index.php?option=com_gridbox&task=pages.updateGridbox&tmpl=component',
                data = {
                    method: window.atob('YmFzZTY0X2RlY29kZQ=='),
                    package: package
                };
            XHR.onreadystatechange = function(e) {
                if (XHR.readyState == 4) {
                    setTimeout(function(){
                        notification[0].className = 'animation-out';
                        setTimeout(function(){
                            notification.find('p').html(app._('UPDATED'));
                            notification[0].className = 'notification-in';
                            setTimeout(function(){
                                notification[0].className = 'animation-out';
                                setTimeout(function(){
                                    window.location.href = window.location.href;
                                }, 400);
                            }, 3000);
                        }, 400);
                    }, 2000);
                }
            };
            XHR.open("POST", url, true);
            XHR.send(JSON.stringify(data));
        }
        
        $('.settings-apply').on('click', function(event){
            event.stopPropagation();
            event.preventDefault();
            var title = $('#settings-dialog .page-title').val().replace(new RegExp(";",'g'), '')
            title = $.trim(title);
            if (!title) {
                return false;
            }
            $('#settings-dialog').modal('hide');
            updatePermissions();
            Joomla.submitbutton('gridbox.updateParams');
        });

        $('.modal .page-title, .modal .category-title').on('input', function(event){
            event.stopPropagation();
            event.preventDefault();
            var $this = $(this),
                title = $this.val();
            title = $.trim(title);
            if (!title) {
                $this.closest('.modal').find('.modal-header i.zmdi-check').addClass('disabled-button');
                $this.parent().find('.ba-alert-container').show();
            } else {
                $this.closest('.modal').find('.modal-header i.zmdi-check').removeClass('disabled-button');
                $this.parent().find('.ba-alert-container').hide();
            }
        });

        function setThemeSettings(obj)
        {
            $('#theme-edit-dialog .theme-name').val(obj.name);
            $('#theme-edit-dialog .theme-image').val(obj.image);
            $('#theme-edit-dialog .theme-default').prop('checked', obj.default == 1);
            $('#theme-edit-dialog .theme-default').prop('disabled', obj.default == 1);
            if (obj.image != 'components/com_gridbox/assets/images/default-theme.png') {
                $('#theme-edit-dialog .theme-image + i')[0].className = 'zmdi zmdi-close';
            } else {
                $('#theme-edit-dialog .theme-image + i')[0].className = 'zmdi zmdi-attachment-alt';
            }
            $('.theme-apply').removeClass('active-button');
            $('#theme-edit-dialog').modal();
        }

        $('.theme-image + i').on('click', function(){
            if (this.classList.contains('zmdi-close')) {
                $('#theme-edit-dialog .theme-image').val('components/com_gridbox/assets/images/default-theme.png');
                $('.theme-apply').addClass('active-button');
            }
        });

        $('.theme-image').on('click', function(){
            uploadMode = 'themeImage';
            checkIframe($('#uploader-modal'), 'uploader');
        });

        $('.theme-name').on('input', function(event){
            event.stopPropagation();
            event.preventDefault();
            var val = $(this).val();
            val = $.trim(val);
            if (val && themeTitle != val) {
                $('.theme-apply').addClass('active-button');
            } else {
                $('.theme-apply').removeClass('active-button');
            }
        });

        $('.theme-default').on('change', function(event){
            event.stopPropagation();
            event.preventDefault();
            var val = this.value.trim();
            if (val && themeTitle != val) {
                $('.theme-apply').addClass('active-button');
            } else {
                $('.theme-apply').removeClass('active-button');
            }
        });

        $('.theme-apply').on('click', function(event){
            event.stopPropagation();
            event.preventDefault();
            if (!$(this).hasClass('active-button')) {
                return false;
            }
            var name = $('#theme-edit-dialog .theme-name').val(),
                image = $('.theme-image').val();
                defaultTheme = Number($('#theme-edit-dialog .theme-default').prop('checked')),
                oldDefault = Number($('#theme-edit-dialog .theme-default').prop('disabled'));
            $.ajax({
                type:"POST",
                dataType:'text',
                url:"index.php?option=com_gridbox&task=theme.updateParams",
                data:{
                    ba_id: pageId,
                    image : image,
                    theme_title: name,
                    default_theme: defaultTheme,
                    old_default: oldDefault
                },
                success: function(msg){
                    showNotice(msg)
                    if (defaultTheme == 1 && oldDefault == 0) {
                        var i = $('.installed-themes-view .gridbox-app-item span.default-theme');
                        $('.installed-themes-view .gridbox-app-item p').attr('data-default', 0);
                        $g(item).find('p').attr('data-default', 1).before(i);
                    }
                    item.querySelector('.image-container').dataset.image = image;
                    if (image.indexOf('balbooa.com') !== -1) {
                        $g(item).find('.image-container').css('background-image', 'url('+image+')');
                    } else {
                        $g(item).find('.image-container').css('background-image', 'url(../'+image+')');
                    }
                    item.querySelector('p span').textContent = name;
                    $('#theme-edit-dialog').modal('hide');
                }
            });
        });
        if ('minicolors' in $g.fn) {
            app.setMinicolors();
        }
        loadPage(true);
    });
})(jQuery);

document.addEventListener('DOMContentLoaded', function(){
    let script = document.createElement('script');
    script.onload = function(){
        if (window.installedPlugins) {
            for (let key in gridboxApi.plugins) {
                for (let ind in gridboxApi.plugins[key]) {
                    if (installedPlugins[ind]) {
                        delete(gridboxApi.plugins[key][ind]);
                    }
                }
            }
            let flag = true;
            for (let key in gridboxApi.plugins) {
                flag = true;
                for (let ind in gridboxApi.plugins[key]) {
                    flag = false;
                }
                if (flag) {
                    delete(gridboxApi.plugins[key])
                }
            }
            flag = true;
            for (let key in gridboxApi.plugins) {
                flag = false;
                break;
            }
            if (flag) {
                delete(gridboxApi.plugins)
            }
        }
        $g.ajax({
            type : "POST",
            dataType : 'text',
            url : 'index.php?option=com_gridbox&task=pages.versionCompare',
            data : {
                version: gridboxApi.version
            },
            success: function(msg){
                if (msg == -1) {
                    $g('.gridbox-update-wrapper').each(function(){
                        this.classList.add('gridbox-update-available');
                        this.querySelector('i').className = 'zmdi zmdi-alert-triangle';
                        this.querySelector('span').textContent = app._('UPDATE_AVAILABLE');
                        if (this.classList.contains('gridbox-update-wrapper')) {
                            let a = document.createElement('a');
                            a.className = 'update-link dashboard-link-action';
                            a.href = "#";
                            a.textContent = app._('UPDATE');
                            this.appendChild(a);
                        }
                    });
                    $g('.ba-dashboard-popover-trigger[data-target="ba-dashboard-about"]').each(function(){
                        this.querySelector('i').className = 'zmdi zmdi-notifications';
                        let count = this.querySelector('.about-notifications-count');
                        count.textContent = count.textContent * 1 + 1;
                        count.style.display = '';
                    });
                }
            }
        });
        gridboxApi.languages.forEach(function(el, ind){
            var str = '<div class="language-line"><span class="language-img"><img src="'+el.flag+'">';
            str += '</span><span class="language-title" data-key="'+ind+'">'+el.title;
            str += '</span><span class="language-code">'+el.code+'</span></div>';
            $g('#languages-dialog .languages-wrapper').append(str);
        });
        let div = document.querySelector('#ba-gridbox-themes-dialog .upload-theme');
        if (div) {
            let str = title = '',
                demo = 'https://www.balbooa.com/showcase-template/gridbox-themes/';
            gridboxApi.themes.forEach(function(el, ind){
                title = el.title.toLowerCase();
                let uri = demo+title;
                str += '<div class="gridbox-app-element" data-id="'+ind+'"><div class="gridbox-app-item-body">'+
                    '<div class="image-container" background-image="'+el.image+'"><img src="'+el.image+'"></div>'+
                    '<p data-default="0"><span class="ba-title">'+el.title+'</span></p></div>'+
                    '<div class="gridbox-app-item-footer">'+
                    '<a class="gridbox-app-item-footer-action" href="#"><i class="zmdi zmdi-download"></i>'+
                    '<span class="ba-tooltip ba-bottom ba-hide-element">'+app._('IMPORT')+'</span></a>'+
                    '<a class="gridbox-app-item-footer-action footer-action-view theme-demo-link" href="'+uri+'" target="_blank">'+
                    '<i class="zmdi zmdi-eye"></i>'+
                    '<span class="ba-tooltip ba-bottom ba-hide-element">'+app._('VIEW')+'</span></a>'+
                    '</div></div>';
            });
            div.innerHTML = str;
        }
        $g('.gridbox-apps-wrapper div.gridbox-app-element').on('click', function(event){
            if (!event.target || !(event.target.classList.contains('theme-demo-link') || event.target.closest('.theme-demo-link'))) {
                event.preventDefault();
            }
        });

        $g('#ba-gridbox-themes-dialog div.gridbox-app-element').on('click', function(event){
            if (!event.target || !(event.target.classList.contains('theme-demo-link') || event.target.closest('.theme-demo-link'))) {
                app.loginItem = this;
                gridboxCallback = 'themeAction';
                app.checkGridboxState();
            }
        });
    }
    let classList = document.body.classList;
    if (classList.contains('view-dashboard') || classList.contains('view-themes')) {
        script.type = 'text/javascript';
        script.src = 'https://www.balbooa.com/updates/gridbox/gridboxApi/admin/gridboxApi.js';
        document.head.appendChild(script);
    }
});