var sample_contentsCss = '\
body {\n\
	font-family: Arial, Verdana, sans-serif;\n\
	font-size: 12px;\n\
	color: #222;\n\
}\
';

var sample_custom_toolbar = '\
[ "Bold", "Italic", "Underline", "Strike", "Subscript", "Superscript", "-", "RemoveFormat" ],\n\
[ "NumberedList", "BulletedList", "-", "Outdent", "Indent", "-", "Blockquote", "CreateDiv", "-", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock", "-", "BidiLtr", "BidiRtl" ],\n\
[ "Link", "Unlink", "Anchor" ],\n\
[ "Image", "Flash", "Table", "HorizontalRule", "Smiley", "SpecialChar", "PageBreak", "Iframe" ],\n\
"/",\n\
[ "Styles", "Format", "Font", "FontSize" ],\n\
[ "TextColor", "BGColor" ],\n\
[ "Maximize", "ShowBlocks", "Source" ]\
';

var sample_styles = '\
{ name: "Red Title" , element: "h3", styles: { "color": "Red" } },\n\
{ name: "CSS Style", element: "span", attributes: { "class": "my_style" } },\n\
{ name: "Marker: Yellow", element: "span", styles: { "background-color": "Yellow" } }\
';


$(document).ready(function()
{
	// export/import settings 
	$(".rightNav .button a").click(function() {
		if ($(this).attr("href") == "#export_settings") {			
			$("#expresso_export_settings").slideToggle();
			$("#expresso_export_settings textarea").select();
			$("#expresso_import_settings").hide();
		}
		else if ($(this).attr("href") == "#import_settings") {
			$("#expresso_export_settings").hide();
			$("#expresso_import_settings").slideToggle();
			$("#expresso_import_settings textarea").focus();
		}
		
		return false;
	});
	
	// settings close button
	$(".expresso_close_settings").click(function() {
		$(this).parent().slideUp();
		return false;
	});	
	
	// add sample code buttons
	$("a.add_sample_code").click(function() {
		var id = $(this).attr("id");
		$("textarea[name=" + id + "]").val(eval("sample_" + id));
		return false;
	});
});