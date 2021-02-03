/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

app.addColumns = function(count){
    var layout = 'section',
        target = $g('#ba-edit-section');
    if (window.parent.document.getElementById('add-section-dialog').classList.contains('add-columns')) {
        layout = 'row';
        target = $g('#'+app.edit+'> .ba-section-items');
        if ($g('#'+app.edit).hasClass('ba-grid-column')) {
            target = $g('#'+app.edit);
        } else if ($g('#'+app.edit).hasClass('ba-row')) {
            target = $g('#'+app.edit);
        }
    }
    $g.ajax({
        type: "POST",
        dataType: 'text',
        url: "index.php?option=com_gridbox&task=editor.loadLayout",
        data: {
            layout : layout,
            count : count
        },
        complete: function(msg){
            msg = JSON.parse(msg.responseText);
            var id = '',
                wrapper = target.closest('.ba-wrapper');
            for (var key in msg.items) {
                var type = msg.items[key].type;
                if (app.theme.defaultPresets[type] && app.theme.presets[type]
                    && app.theme.presets[type][app.theme.defaultPresets[type]]) {
                    msg.items[key] = $g.extend(true, msg.items[key], app.theme.presets[type][app.theme.defaultPresets[type]].data);
                }
                app.items[key] = msg.items[key];
                if (msg.items[key].type == layout) {
                    id = key;
                }
            }
            if (target.hasClass('ba-grid-column')) {
                $g('#'+app.edit).find('> .empty-item').before(msg.html);
                var nested = window.parent.gridboxLanguage['NESTED_ROW'];
                $g('#'+app.edit).find('> .ba-row-wrapper > .ba-row > .ba-edit-item .edit-settings .ba-tooltip').text(nested);
            } else {
                target.append(msg.html);
            }
            for (var key in msg.items) {
                document.getElementById(key).classList.add('visible');
                window.parent.setShapeDividers(app.items[key], key);
            }
            app.checkModule('sectionRules');
            var modal = window.parent.document.getElementById('add-section-dialog'),
                str = '';
            $g(modal).find('.zmdi.zmdi-close').trigger('click');
            editItem(id);
            if (target.hasClass('ba-section-items') && (wrapper.hasClass('ba-overlay-section')
                || wrapper.hasClass('ba-lightbox'))) {
                makeRowSortable($g('#'+id).find('.ba-section-items'), 'lightbox-row');
                makeColumnSortable($g('#'+id).find('.ba-grid-column'), 'lightbox-column');
            } else if (target.hasClass('ba-section-items') && !wrapper.hasClass('tabs-content-wrapper')) {
                makeRowSortable($g('#'+id).find('.ba-section-items'), 'tabs-row');
                makeColumnSortable($g('#'+id).find('.ba-grid-column'), 'column');
            } else if (wrapper.attr('data-megamenu')) {
                makeRowSortable($g('#'+id).find('.ba-section-items'), 'row');
                makeColumnSortable($g('#'+id).find('.ba-grid-column'), 'lightbox-column');
            } else if (target.hasClass('ba-row') && (wrapper.hasClass('ba-overlay-section')
                || wrapper.hasClass('ba-lightbox') || wrapper.hasClass('ba-sticky-header'))) {
                makeRowSortable($g('#'+id).find('.ba-section-items'), 'lightbox-row');
                makeColumnSortable($g('#'+id).find('.ba-grid-column'), 'lightbox-column');
            } else {
                makeRowSortable($g('#'+id).find('.ba-section-items'), 'row');
                makeColumnSortable($g('#'+id).find('.ba-grid-column'), 'column');
            }
            setColumnResizer($g('#'+id)[0]);
            if (target.hasClass('ba-row')) {
                var div = target.find('> .ba-row-wrapper > .ba-row');
                target.append(div.find('.column-wrapper'));
                delete(app.items[div.attr('id')]);
                target.append(div.find('.column-wrapper'));
                var rowColumns = target.find('> .column-wrapper').first().find('> .ba-grid-column-wrapper > .ba-grid-column'),
                    newRowColumns = target.find('> .column-wrapper').last().find('> .ba-grid-column-wrapper > .ba-grid-column');
                rowColumns.each(function(ind){
                    if (newRowColumns[ind]) {
                        delete(app.items[newRowColumns[ind].id]);
                        let className = this.closest('.ba-grid-column-wrapper').className;
                        newRowColumns[ind].closest('.ba-grid-column-wrapper').className = className;
                        $g(newRowColumns[ind]).replaceWith(this);
                        newRowColumns[ind] = this;
                    } else {
                        var column = newRowColumns[newRowColumns.length - 1];
                        $g(column).find('> .empty-item').before($g(this).find('> .ba-item, > .ba-row-wrapper'));
                        delete(app.items[this.id]);
                    }
                });
                target.find('.ba-item').each(function(){
                    if (app.items[this.id]) {
                        initMapTypes(app.items[this.id].type, this.id);
                    }
                });
                target.find('> .column-wrapper').first().remove();
                div.closest('.ba-row-wrapper').remove();
            }
            window.parent.app.addHistory();
        }
    });
}
app.addColumns(app.modules.addColumns.data);