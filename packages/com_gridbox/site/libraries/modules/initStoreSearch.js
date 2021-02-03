/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.initStoreSearch = function(obj, key){
    let wrapper = $g('#'+key+' .ba-search-wrapper');
    wrapper.off('click.wrapper').on('click.wrapper', function(event){
        if (obj.live && themeData.page.view != 'gridbox') {
            event.stopPropagation();
        }
    }).find('input').off('keyup.input').on('keyup.input', function(event){
        if (event.keyCode == 13 && this.value.trim() && themeData.page.view != 'gridbox') {
            app.storeSearch.submit(this);
        }
    }).off('input.input').on('input.input', function(){
        if (obj.live && themeData.page.view != 'gridbox') {
            app.storeSearch.setInput(this);
            clearTimeout(this.delay);
            this.delay = setTimeout(function(){
                let length = app.storeSearch.input.value.trim().length
                if (length > 2) {
                    app.storeSearch.getResults();
                } else if (length == 0) {
                    app.storeSearch.clearSearch();
                }
            }, 500);
        }
    });
    wrapper.find('i').off('click.input').on('click.input', function(){
        if (obj.live && themeData.page.view != 'gridbox') {
            app.storeSearch.clearSearch();
        }
    });

    initItems();
}

app.storeSearch = {
    submit: function(input){
        if (input.value.trim() && themeData.page.view != 'gridbox') {
            window.location.href = input.dataset.searchUrl+input.value.trim();
        }
    },
    addEvents: function(){
        app.storeSearch.modal.on('click', function(event){
            event.stopPropagation();
        }).on('click', '.ba-live-search-add-to-cart-btn', function(){
            let a = this.closest('.ba-live-search-product-row').querySelector('.ba-live-search-product-title a');
            app.fetch(JUri+'index.php?option=com_gridbox&task=store.addPostToCart', {
                id: this.dataset.id
            }).then(function(text){
                let response = JSON.parse(text),
                    str = '';
                if (response.status) {
                    if (app.storeCart) {
                        app.storeCart.updateCartTotal();
                        $g('.ba-item-cart a').first().trigger('click');
                    }
                } else {
                    localStorage.setItem('select-options', gridboxLanguage['PLEASE_SELECT_OPTION']);
                    a.click();
                }
            });
        }).on('click', '.ba-live-search-show-all-btn', function(){
            app.storeSearch.submit(app.storeSearch.input);
        })
    },
    clearSearch: function(){
        app.storeSearch.input.value = '';
        app.storeSearch.wrapper.classList.remove('live-search-data-loaded');
        app.storeSearch.wrapper.classList.remove('live-search-data-loaded');
        if (app.storeSearch.visible) {
            app.storeSearch.modal.removeClass('visible-live-search-results').addClass('ba-live-search-out');
            setTimeout(function(){
                app.storeSearch.modal.removeClass('ba-live-search-out');
            }, 300);
            app.storeSearch.visible = false;
        }
    },
    setInput: function(input){
        app.storeSearch.input = input;
        app.storeSearch.wrapper = input.closest('.ba-search-wrapper');
    },
    setModalProperties: function(){
        let rect = app.storeSearch.wrapper.getBoundingClientRect(),
            left = rect.left,
            scroll = window.pageYOffset;
        if (left + 600 > window.innerWidth) {
            left = rect.right - 600;
        }
        app.storeSearch.div.style.setProperty('--input-width', rect.width+'px');
        app.storeSearch.div.style.setProperty('--input-left', left+'px');
        app.storeSearch.div.style.setProperty('--input-bottom', (rect.bottom + scroll)+'px');
    },
    getResults: function(){
        app.storeSearch.wrapper.classList.remove('live-search-data-loaded');
        app.storeSearch.wrapper.classList.add('live-search-loading-data');
        app.fetch(JUri+'index.php?option=com_gridbox&view=editor&task=store.getLiveSearchData', {
            search: app.storeSearch.input.value.trim()
        }).then(function(html){
            if (!app.storeSearch.modal) {
                app.storeSearch.div = document.createElement('div');
                app.storeSearch.div.className = 'ba-live-search-results';
                document.body.append(app.storeSearch.div);
                app.storeSearch.modal = $g(app.storeSearch.div);
                app.storeSearch.addEvents();
            }
            app.storeSearch.setModalProperties();
            app.storeSearch.modal.html(html).addClass('visible-live-search-results');
            app.storeSearch.wrapper.classList.add('live-search-data-loaded');
            app.storeSearch.wrapper.classList.remove('live-search-loading-data');
            app.storeSearch.visible = true;
        });
    }
}

if (app.modules.initStoreSearch) {
    app.initStoreSearch(app.modules.initStoreSearch.data, app.modules.initStoreSearch.selector);
}