/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.addSection = function(){
    setTimeout(function(){
        $g('#add-section-dialog').modal();
    }, 150);
}

$g('#add-section-dialog .advanced-column').on('keyup', function(){
    var array = this.value.split('+'),
        count = 0
        flag = true;
    array.forEach(function(el){
        count += el*1;
        if (el * 1 == 0 || el * 1 < 0) {
            flag = false;
        }
    });
    if (count == 12 && flag) {
        $g('#apply-column').addClass('active-button');
    } else {
        $g('#apply-column').removeClass('active-button');
    }
});

$g('#apply-column').on('mousedown', function(){
    if ($g(this).hasClass('active-button')) {
        var obj = {
            data : $g('.advanced-column').val()
        };
        app.editor.app.checkModule('addColumns', obj);
    }
});

$g('#add-section-dialog .ba-column').on('mousedown', function(){
    var obj = {
        data : this.dataset.count
    };
    app.editor.app.checkModule('addColumns', obj);
});

app.addSection();
app.modules.addSection = true;