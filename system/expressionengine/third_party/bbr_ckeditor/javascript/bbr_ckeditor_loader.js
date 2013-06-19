    /**
     * Programmed by Biber Ltd. (http://biberltd.com)
     * Author: Can Berkol
     * Version: 1.0.0
     * 
     * Loads External JS Files.
     * http://biberltd.com/wiki/English:ckeditor/
     */

/**
 * Load ckeditor.js and the adapter file
 */
$(document).load(function(){
    include('expressionengine/third_party/bbr_ckeditor/ckeditor/ckeditor.js');
    include('expressionengine/third_party/bbr_ckeditor/ckeditor/adapters/jquery.js');
});


/**
 * Includes new JS files in current documents head
 */
function include(filename)
{
	var head = document.getElementsByTagName('head')[0];
	
	script = document.createElement('script');
	script.src = filename;
	script.type = 'text/javascript';
	
	head.appendChild(script);
}