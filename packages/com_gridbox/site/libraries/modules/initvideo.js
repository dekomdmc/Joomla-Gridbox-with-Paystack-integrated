/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.initvideo = function(obj, key){
    var video = document.querySelector('#'+key+' video');
    if (themeData.page.view == 'gridbox' && obj.video.type == 'source' && obj.video.source.file) {
        video.querySelector('source').src = JUri+obj.video.source.file;
    }
    if (video && obj.video.source.autoplay) {
        var promise = video.play();
        if (promise !== undefined) {
            promise.then(_ => {
                
            }).catch(error => {
                video.muted = true;
                video.play();
                console.warn('Autoplay with sound prevented.')
            });
        }
    }
    $g('#'+key+' .video-lazy-load-thumbnail').on('click', function(){
        var src = 'https://www.youtube.com/embed/'+obj.video.id+'?';
        for (var ind in obj.video.youtube) {
            src += ind+'='+Number(ind == 'autoplay' ? true : obj.video.youtube[ind])+'&';
        }
        src = src.substr(0, src.length - 1);
        var str = '<iframe src="'+src+'" frameborder="0" allowfullscreen allow="autoplay"></iframe>',
            $this = $g(this),
            overlay = $this.closest('.ba-overlay-section-backdrop');
        $this.replaceWith(str);
        if (overlay.length) {
            overlayOpen(overlay[0]);
        }
    });
    initItems();
}

if (app.modules.initvideo) {
    app.initvideo(app.modules.initvideo.data, app.modules.initvideo.selector);
}