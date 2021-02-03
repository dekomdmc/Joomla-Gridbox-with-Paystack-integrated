/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

function setPostMasonryHeight(key)
{
    var wrapper = $g('#'+key).find('.ba-blog-posts-wrapper, .ba-categories-wrapper')[0],
        computed = null,
        reviews = document.getElementById(key).classList.contains('ba-item-recent-reviews'),
        gap = 20,
        height = 0;
    $g('#'+key).find('.ba-blog-posts-wrapper, .ba-categories-wrapper').not('.ba-masonry-layout').find('.ba-blog-post').each(function(){
        this.classList.remove('ba-masonry-image-loaded');
        this.style.transitionDelay = '';
        this.style.gridRowEnd = '';
    });
    $g('#'+key+' .ba-masonry-layout .empty-list').each(function(){
        this.closest('.ba-masonry-layout').classList.add('empty-masonry-wrapper');
    })
    $g('#'+key+' .ba-masonry-layout .ba-blog-post').each(function(ind){
        var post = this,
            offsetHeight = post.querySelector('.ba-blog-post-content').offsetHeight,
            $this = this.querySelector('.ba-blog-post-image img'),
            img = document.createElement('img');
        if (!computed) {
            computed = getComputedStyle(this)
        }
        offsetHeight += (computed.paddingBottom.replace(/[^\d\.]/g, '') * 1)+(computed.paddingTop.replace(/[^\d\.]/g, '') * 1);
        offsetHeight += (computed.borderBottomWidth.replace(/[^\d\.]/g, '') * 1)+(computed.borderTopWidth.replace(/[^\d\.]/g, '') * 1);
        this.style.transitionDelay = (0.15 * ind)+'s';
        if (!$this || reviews) {
            post.style.gridRowEnd = "span "+Math.ceil(((offsetHeight + gap) / (height + gap)) + 0);
            if (!post.classList.contains('ba-masonry-image-loaded')) {
                post.classList.add('ba-masonry-image-loaded');
            }
        } else if (!$this.src) {
            $this.onload = function(){
                offsetHeight += $this.offsetHeight;
                post.style.gridRowEnd = "span "+Math.ceil(((offsetHeight + gap) / (height + gap)) + 0);
                if (!post.classList.contains('ba-masonry-image-loaded')) {
                    post.classList.add('ba-masonry-image-loaded');
                }
            }
        } else {
            img.onload = function(){
                offsetHeight += $this.offsetHeight;
                post.style.gridRowEnd = "span "+Math.ceil(((offsetHeight + gap) / (height + gap)) + 0);
                if (!post.classList.contains('ba-masonry-image-loaded')) {
                    post.classList.add('ba-masonry-image-loaded');
                }
            }
            img.src = $this.src;
        }
        this.closest('.ba-masonry-layout').classList.remove('empty-masonry-wrapper');
    });
}

if ('app' in window && app.modules && app.modules.initMasonryBlog && app.modules.initMasonryBlog.data) {
    app.initMasonryBlog = function(obj, key){
        setPostMasonryHeight(key);
        $g('#'+key).off('mouseover.options').on('mouseover.options', '.ba-blog-post-product-option', function(event){
            let search = 'ba-blog-post-product-option',
                t1 = event.target ? event.target.closest('.'+search) : null,
                t2 = event.relatedTarget ? event.relatedTarget.closest('.'+search) : null;
            if (t1 != t2) {
                let post = this.closest('.ba-blog-post');
                if (this.dataset.image) {
                    let image = this.dataset.image.indexOf('balbooa.com') == -1 ? JUri+this.dataset.image : this.dataset.image;
                    post.style.setProperty('--product-option-image', 'url('+image+')');
                } else {
                    post.style.setProperty('--product-option-image', '');
                }
                post.classList.add('product-option-hovered');
            }
        }).off('mouseout.options').on('mouseout.options', '.ba-blog-post-product-option', function(event){
            let search = 'ba-blog-post-product-option',
                t1 = event.target ? event.target.closest('.'+search) : null,
                t2 = event.relatedTarget ? event.relatedTarget.closest('.'+search) : null;
            if (t1 != t2 && (!t2 || !t2.classList.contains(search))) {
                let post = this.closest('.ba-blog-post');
                post.classList.remove('product-option-hovered');
                post.style.setProperty('--product-option-image', '');
            }
        }).off('click.wishlist').on('click.wishlist', '.ba-blog-post-wishlist-wrapper', function(){
            if (themeData.page.view == 'gridbox') {
                return false;
            }
            let post = this.closest('.ba-blog-post')
            app.fetch(JUri+'index.php?option=com_gridbox&task=store.addPostToWishlist', {
                id: post.dataset.id
            }).then(function(text){
                let response = JSON.parse(text),
                    str = '';
                if (response.status) {
                    if (response.data.images.length) {
                        response.data.image = response.data.images[0];
                    }
                    if (response.data.image && response.data.image.indexOf('balbooa.com') == -1) {
                        response.data.image = JUri+response.data.image;
                    }
                    str = '<span class="ba-product-notice-message">';
                    if (response.data.image) {
                        str += '<span class="ba-product-notice-image-wrapper"><img src="'+response.data.image+'"></span>';
                    }
                    str += '<span class="ba-product-notice-text-wrapper">'+response.data.title+
                        ' '+gridboxLanguage['ADDED_TO_WISHLIST']+'</span></span>';
                    app.showNotice(str, 'ba-product-notice');
                    if (app.wishlist) {
                        app.wishlist.updateWishlist();
                    }
                } else if (!response.status && response.message) {
                    app.showNotice(response.message, 'ba-alert');
                } else {
                    localStorage.setItem('select-options', gridboxLanguage['PLEASE_SELECT_OPTION']);
                    post.querySelector('.ba-blog-post-title a').click();
                }
            });
        }).off('click.cart').on('click.cart', '.ba-blog-post-add-to-cart', function(){
            if (themeData.page.view == 'gridbox') {
                return false;
            }
            let post = this.closest('.ba-blog-post')
            app.fetch(JUri+'index.php?option=com_gridbox&task=store.addPostToCart', {
                id: post.dataset.id
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
                    post.querySelector('.ba-blog-post-title a').click();
                }
            });
        }).on('change', '.blog-posts-sorting', function(){
            if (themeData.page.view == 'gridbox') {
                return false;
            }
            window.location.href = this.dataset.url+this.value;
        });
        if (obj.type == 'recent-posts' && themeData.page.view != 'gridbox') {
            $g('#'+key).off('click.pagination').on('click.pagination', '.ba-blog-posts-pagination a', function(event){
                event.preventDefault();
                if (!this.dataset.clicked) {
                    this.dataset.clicked = true;
                    var category = new Array(),
                        match = this.href.match(/page=\d+/),
                        page = match[0].match(/\d+/),
                        notId = new Array(),
                        notStr = '',
                        cats = '';
                    for (var ind in obj.categories) {
                        category.push(ind);
                    }
                    cats = category.join(',');
                    if (obj.sorting == 'random') {
                        $g('#'+key+' .ba-blog-post').each(function(){
                            notId.push(this.dataset.id);
                        });
                        notStr = notId.join(',');
                    }
                    $g.ajax({
                        type: "POST",
                        dataType: 'text',
                        url: JUri+"index.php?option=com_gridbox&task=page.getRecentPosts",
                        data: {
                            id : obj.app,
                            limit : obj.limit,
                            sorting : obj.sorting,
                            category : cats,
                            maximum : obj.maximum,
                            featured: Number(obj.featured),
                            page: page[0],
                            pagination: obj.layout.pagination,
                            not: notStr
                        },
                        complete: function(msg){
                            let object = JSON.parse(msg.responseText);
                            $g('#'+key+' .ba-blog-posts-pagination').remove();
                            $g('#'+key+' .ba-blog-posts-wrapper').append(object.posts).after(object.pagination);
                            if (obj.tag != 'h3') {
                                $g('#'+key+' h3[class*="-title"]').each(function(){
                                    var h = document.createElement(obj.tag);
                                    h.className = this.className;
                                    h.innerHTML = this.innerHTML;
                                    $g(this).replaceWith(h);
                                });
                            }
                            setPostMasonryHeight(key);
                            $g('#'+key+' .ba-blog-post-button-wrapper a')
                                .text(obj.buttonLabel ? obj.buttonLabel : gridboxLanguage['READ_MORE']);
                            if (obj.layout.pagination == 'load-more-infinity' && page[0] == 2) {
                                $g(document).on('scroll.'+key, function(){
                                    recentPostsInfinityAction(key);
                                });
                            }
                        }
                    });
                }
            });
            if (obj.layout.pagination == 'infinity') {
                $g(document).on('scroll.'+key, function(){
                    recentPostsInfinityAction(key);
                });
                recentPostsInfinityAction(key);
            }
        }
        initItems();
    }
    app.initMasonryBlog(app.modules.initMasonryBlog.data, app.modules.initMasonryBlog.selector);
}

function recentPostsInfinityAction(key)
{
    let scroll = window.pageYOffset + window.innerHeight,
        rect = document.querySelector('#'+key+' .ba-blog-posts-wrapper').getBoundingClientRect(),
        y = rect.bottom + window.pageYOffset,
        btn = document.querySelector('#'+key+' .ba-blog-posts-pagination a');
    if (y < scroll && btn && !btn.dataset.clicked) {
        btn.click();
    } else if (!btn) {
        $g(document).off('scroll.'+key);
    }
}