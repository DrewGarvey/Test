function matrix_display(cell) 
{
	var textarea = $('textarea', cell.dom.$td);
	var id = cell.field.id+'_'+cell.row.id+'_'+cell.col.id+'_'+Math.floor(Math.random()*10000);
	var file_uploads = eval("expresso_file_upload_matrix_"+cell.col.id);
	var config = eval("expresso_config_matrix_"+cell.col.id);

	$(textarea).attr('id', id);		
	
	expresso(id, file_uploads, config);	
}


$(function()
{		
	Matrix.bind('expresso', 'display', function(cell) {	
		matrix_display(cell);
	});
	
	Matrix.bind('expresso', 'beforeSort', function(cell){
		var textarea = $('textarea', cell.dom.$td);
		var html = $('iframe:first', cell.dom.$td)[0].contentDocument.body.innerHTML;
		$(textarea).html(html);
	});
	
	Matrix.bind('expresso', 'afterSort', function(cell) {
		var textarea = $('textarea', cell.dom.$td);
		cell.dom.$td.empty().append($(textarea));
		matrix_display(cell);
	});
});