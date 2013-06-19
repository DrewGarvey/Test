function expresso(field, file_uploads, config) 
{
	var defaults = 
	{
		entities_additional: "#39,#123,#125",
		forcePasteAsPlainText: true,
		toolbarCanCollapse: false,
		resize_enabled: true,
		extraPlugins: "headers,video",
		removePlugins: "elementspath",
		removeFormatTags: "b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var,h1,h2,h3,h4,h5,h6",
		skin: "expresso,../skins/expresso2/",
		resize_maxWidth: "100%",
		dialog_backgroundCoverColor: "#262626",
		dialog_backgroundCoverOpacity: "0.85"
	};
	
	config = $.extend(defaults, config);
	
	if (file_uploads) 
	{
		$("textarea#" + field).ckeditor(function() 
			{
				var button = $("#cke_" + field + " span.cke_button a.cke_button_link").parent();
				var new_button = $(button).clone();
				$(new_button).find("a").attr("title", "File").attr("id", "").attr("href", "#").attr("onkeydown", "").attr("onfocus", "").attr("onclick", "");
				$(new_button).find("span.cke_icon").css("background", "url(" + theme_folder_url + "icons2/file_icon.png)");
				$(new_button).find("span.cke_label").html("");
				$(button).before($(new_button));
				
				add_filebrowser_trigger(new_button, $("textarea#" + field), 'all', 'all');
			}, 
			config
		);
	}
		
	else 
	{
		$("textarea#" + field).ckeditor(config);
	}
}


function customise_dialogs(file_uploads)
{		
	// customise dialogs
	CKEDITOR.on('dialogDefinition', function(ev) 
	{
		var dialogName = ev.data.name;
		var dialogDefinition = ev.data.definition;
		var infoTab = dialogDefinition.getContents('info');

		// link dialog
		if (dialogName == 'link') 
		{
			// remove browse button		 		
			infoTab.remove('browse');
		
 			var urlOptionsPanel = infoTab.get('urlOptions'); 
			
			// add extra link dropdowns
			$.each(extra_links, function(key, links)
			{
	            urlOptionsPanel.children.push(
	            {
						type : 'select',
						label : links['name'],
						id : links['name'],
						title : 'Select a page to link to',
						items: links['links'],
						onChange : function(ev) 
						{
							var diag = CKEDITOR.dialog.getCurrent();
							var url = diag.getContentElement('info', 'url');
							url.setValue(ev.data.value);
						}
				});
			});
			
			// if file uploads allowed
			if (file_uploads) 
			{	
				// add browse button			 			
	            urlOptionsPanel.children.push(
	            {
						type : 'button',
						label : 'Browse Files',
						id : 'expresso_browse_button',
						style : 'margin-top:13px;'
				});
			
				// on dialog load (after it has been created)
				dialogDefinition.onLoad = function()
				{
					// add filebrowser trigger to browse button
					add_filebrowser_trigger($("#" + this.getContentElement('info', 'expresso_browse_button').domId), $("#" + this.getContentElement('info', 'url').domId).find("input"), 'all', 'all');
				}			
			}
		}
		
		// image dialog
		else if (dialogName == 'image') 
		{
			// remove browse button		 		
			infoTab.remove('browse');
		
			// if file uploads allowed
			if (file_uploads) 
			{	
				// add browse button		 			
	            infoTab.elements[0].children[0].children.push(
	            {
					type : 'button',
					label : 'Browse Files',
					id : 'expresso_browse_button',
					style : 'display:inline-block; margin-top:13px;'
				});			
				
				// on dialog load (after it has been created)
				dialogDefinition.onLoad = function()
				{				
					// add filebrowser trigger to browse button
					add_filebrowser_trigger($("#" + this.getContentElement('info', 'expresso_browse_button').domId), $("#" + this.getContentElement('info', 'txtUrl').domId).find("input"), 'images', 'all');
				}			
			}
		}
		
		// video dialog
		else if (dialogName == 'video') 
		{
			// remove browse button		 		
			infoTab.remove('browse');
		
			// if file uploads allowed
			if (file_uploads) 
			{	
				// add browse buttons	 			
	            infoTab.elements[0].children.push(
	            {
					type : 'button',
					label : 'Browse Files',
					id : 'expresso_browse_button_1',
					style : 'display:inline-block; margin-top:13px;'
				});	 			
	            infoTab.elements[2].children[1] =
	            {
					type : 'button',
					label : 'Browse Files',
					id : 'expresso_browse_button_2',
					style : 'display:inline-block; margin-top:13px;'
				};	 			
	            infoTab.elements[3].children[1] =
	            {
					type : 'button',
					label : 'Browse Files',
					id : 'expresso_browse_button_3',
					style : 'display:inline-block; margin-top:13px;'
				};	
				
				// on dialog load (after it has been created)
				dialogDefinition.onLoad = function()
				{				
					// add filebrowser triggers to browse buttons
					add_filebrowser_trigger($("#" + this.getContentElement('info', 'expresso_browse_button_1').domId), $("#" + this.getContentElement('info', 'poster').domId).find("input"), 'images', 'all');
					
					add_filebrowser_trigger($("#" + this.getContentElement('info', 'expresso_browse_button_2').domId), $("#" + this.getContentElement('info', 'src0').domId).find("input"), 'all', 'all');
					
					add_filebrowser_trigger($("#" + this.getContentElement('info', 'expresso_browse_button_3').domId), $("#" + this.getContentElement('info', 'src1').domId).find("input"), 'all', 'all');
				}	
			}
		}
	});
}


function add_filebrowser_trigger(button, field, content_type, directory)
{
	if (ee22) 
	{
		$.ee_filebrowser.add_trigger($(button), field, {content_type: content_type, directory: directory}, function(file, field) 
		{
			var url = EE["upload_directories"][file.upload_location_id]["url"] + file.file_name;
			
			if ($(field).is('textarea'))
			{
				var selection = "";
				
				if (!CKEDITOR.env.ie) 
				{
				   selection = $(field).ckeditorGet().getSelection().getNative().toString();
				}		
								
				var link = selection ? selection : file.file_name;
		    	var html = file.is_image ? '<img src="' + url + '" alt="' + file.file_name + '" />' : '<a href="' + url + '">' + link + '</a>';
		    	
				$(field).ckeditorGet().insertHtml(html);
		    }
		    
		    else 
		    {
		    	$(field).val(url);
		    	$(field).focus();
		    	
		    	if (txtUrl = CKEDITOR.dialog.getCurrent().getContentElement('info', 'txtUrl'))
		    	{
		    		txtUrl.fire('change');
		    	}
		    }			    
		});
	}
	else 
	{
		$.ee_filebrowser.add_trigger($(button), field, function(file, field) 
		{
			var url = EE["upload_directories"][file.directory]["url"] + file.name;
			
			if ($(field).is('textarea'))
			{				
				var selection = "";
			
				if (!CKEDITOR.env.ie) 
				{
				   selection = $(field).ckeditorGet().getSelection().getNative().toString();
				}						
			
				var link = selection ? selection : file.name;
		    	var html = file.is_image ? '<img src="' + url + '" alt="' + file.name + '" />' : '<a href="' + url + '">' + link + '</a>';
		    	
				$(field).ckeditorGet().insertHtml(html);
		    }
		    		
		    else 
		    {
				$(field).val(url);
		 		//$(field).focus();
		    	
		    	if (txtUrl = CKEDITOR.dialog.getCurrent().getContentElement('info', 'txtUrl'))
		    	{
		    		txtUrl.fire('change');
		    	}
		    }
		    		    
		    $.ee_filebrowser.reset();
		});
	}
}