(function($) {
    $(function() {
            Matrix.bind('blue_upload', 'display', function(cell){

                    var fieldname = cell.field.id +'-'+cell.row.id+'-'+cell.col.id;

                    $(this).find('div[id*="field_id_"]').each(function(index,el){
                        $(el).attr('id', fieldname + $(el).attr('class').split(' ')[0]);
                    });

                    $(this).find('ul a').each(function(index,el){
                        $(el).attr('href', '#'+fieldname + $(el).attr('class').split(' ')[0]);
                    });

                    $(this).find(".bsd-biglist").BigList();
                    $(this).find('.blue_upload_field').blueUploadField();

                    $(this).find(".bsd-biglist").bind('bigListReady', $.proxy(function(event){
                        $(this).find('.blue_upload_field').blueUploadField('showPreview');
                    }, this));

            });

    });
})(jQuery);
