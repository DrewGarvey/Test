<?php

$this->EE =& get_instance();


// optional language vars set in config
$taxonomy_tree_label 	= ($this->EE->config->item('taxonomy_tree_label')) 
							? $this->EE->config->item('taxonomy_tree_label') : 'Tree';
$taxonomy_trees_label 	= ($this->EE->config->item('taxonomy_trees_label')) 
							? $this->EE->config->item('taxonomy_trees_label') : 'Trees';
$taxonomy_node_label 	= ($this->EE->config->item('taxonomy_node_label')) 
							? $this->EE->config->item('taxonomy_node_label') : 'Node';
$taxonomy_nodes_label 	= ($this->EE->config->item('taxonomy_nodes_label')) 
							? $this->EE->config->item('taxonomy_nodes_label') : 'Nodes';
$taxonomy_add_node_label = ($this->EE->config->item('taxonomy_add_node_label')) 
							? $this->EE->config->item('taxonomy_add_node_label') : 'Add a Node';
$taxonomy_nav_label 	= ($this->EE->config->item('taxonomy_nav_label')) 
							? $this->EE->config->item('taxonomy_nav_label') : 'Taxonomy';
							
/* 
	the above vars can be overridden by the user in  EE/system/config/config.php via:
	$config['taxonomy_label'] 		= 'Menus';
	$config['taxonomy_tree_label'] 	= 'Menu';
	$config['taxonomy_trees_label'] = 'Menus';
	$config['taxonomy_add_node_label'] 	= 'Add a menu item';
	$config['taxonomy_node_label'] 	= 'Item';
	$config['taxonomy_nodes_label'] = 'Items';
*/



$lang = array(

"module_home"					=> 'Module Home',
"taxonomy" 						=> 'Taxonomy',
"taxonomy_module_name" 			=> 'Taxonomy',
"taxonomy_module_description"	=> 'Build Nested Sets from Channel Entries',
"welcome"						=> 'Welcome to Taxonomy',
"add_tree"						=> "Add A $taxonomy_tree_label",
"nav_taxonomy_nav_label"		=> "$taxonomy_nav_label",
"create_tree_instructions"		=> "Give your $taxonomy_tree_label a name, then select which Templates and Channels are available when creating $taxonomy_nodes_label.",
"manage_trees" 					=> "Manage $taxonomy_trees_label",
"configuration" 				=> 'Settings',
"tree_id" 						=> 'ID',
"tree_label"					=> "$taxonomy_tree_label Label",
"edit_nodes_label"				=> "Edit $taxonomy_nodes_label",
"edit_tree_label"				=> "Edit $taxonomy_tree_label",
"save_settings"					=> 'Save Settings',
"access"						=> 'Member Access',
"properties"					=> 'Properties',
"edit_selected"					=> 'Edit Selected',
"delete_selected"				=> 'Delete Selected',
"delete_trees"					=> "Delete $taxonomy_trees_label",
"tree_delete_question"			=> 'Are you sure you want to delete?',
"node_delete_question"			=> "Are you sure you want to delete this $taxonomy_node_label?",
"branch_delete_question"		=> 'Are you sure you want to delete this entire branch?',
"no_trees_exist"				=> "No $taxonomy_trees_label exist yet!",
"tree_added"					=> "Your new $taxonomy_tree_label has been created!",
"edit_nodes"					=> "Edit $taxonomy_nodes_label",
"insert_a_root"					=> "Please insert a Root $taxonomy_node_label:",
"root_added"					=> 'Root node has been created!',
"no_root_node"					=> "This $taxonomy_tree_label has no $taxonomy_nodes_label, please add at least a root $taxonomy_node_label first!",
"create_node"					=> $taxonomy_add_node_label,
"name"							=> "$taxonomy_node_label",
"title"							=> 'Title',
"internal_url"					=> 'Internal URL: &nbsp;  template &rarr; entry',
"internal_url_no_templates"		=> 'Internal URL: Select entry',
"override_url"					=> 'Override Url:',
"node_label"					=> "$taxonomy_node_label Label:",
"add"							=> 'Add',
"node_added"					=> "$taxonomy_node_label Added!",
"edit_node"						=> "Edit $taxonomy_node_label",
"manage_node"					=> "Add/Edit $taxonomy_node_label",
"manage_nodes"					=> "Manage $taxonomy_node_label",
"tree_preferences"				=> "$taxonomy_tree_label Preferences",
"path_to_here"					=> 'Path to here:',
"option"						=> 'Option',
"value"							=> 'Value',
"create_tree"					=> "Create $taxonomy_tree_label",
"edit_tree"						=> "Edit $taxonomy_tree_label",
"parent_node"					=> "Parent $taxonomy_node_label",
"select_parent"					=> 'Select Parent:',
"template_preferences"			=> '<strong>Selected Templates</strong><br />Choose which templates are available to publishers',
"use_pages_mode"				=> '<strong>Use Pages Mode</strong><br />Remove ability to select templates',
"template"						=> 'Template',
"taxonomy_channel_preferences"			=> '<strong>Selected Channels</strong><br />Only Entries from your selected channels will be available when creating nodes via the module interface',
"no_such_tree"					=> "No $taxonomy_tree_label selected, or the selected $taxonomy_tree_label does not exist!",
"invalid_trees"					=> "Invalid $taxonomy_tree_label!",
"this_is_root"					=> "This is the Root $taxonomy_node_label!",
"properties_updated"			=> 'Properties have been updated!',
"no_templates_exist"			=> 'No Templates exist in this site yet, please create at least 1 Template group!',
"no_channels_exist"				=> 'No Channels exist in this site yet, please create at least 1 Channel!',
"visit"							=> 'Visit: ',
"select_tree"					=> "Select the $taxonomy_tree_label associated with this field",
"node_properties"				=> "$taxonomy_node_label Properties",
"search_for_nodes"				=> "Search for $taxonomy_node_label",
"taxonomy_config"				=> 'Module Configuration',
"asset_path_config"				=> 'Location of the <strong>taxonomy_assets</strong> folder',
"taxonomy_config_updated"		=> 'Taxonomy preferences have been updated',
"use_pages_module_uri"			=> 'Use Pages Module URI',
"enable_pages_mode"				=> 'Use \'Pages Module\' mode <br />(gives option to use Page URI)',
"hide_template_select"			=> 'Hide Template Select option',
"fetch_title"					=> 'Fetch the Title',
"custom_field_label"			=> 'Field label <br /><small>(Visible to publishers)</small>',
"custom_field_short"			=> 'Field short name <br /><small>(Single word, no spaces. Underscores and dashes allowed)</small>',
"type"							=> 'Type',
"display_on_publish"			=> 'Display on publish?',
"order"							=> 'Order',
"advanced_settings"				=> "Advanced Settings: $taxonomy_tree_label Custom Fields",
"advanced_settings_instructions" => "Custom fields are optional, and will appear to publishers when editing $taxonomy_nodes_label via the module interface.<br />By selecting 'Display on publish?' the field will appear on the Taxonomy Fieldtype too.<br />To remove a row, leave the 'Field short name' blank and save.",
"root_node_notice"				=> "<h3>Please insert a Root $taxonomy_node_label<br /><small>All Taxonomy $taxonomy_trees_label must have a Root $taxonomy_node_label</small></h3>",
"field_notice"					=> 'Please note: Changing a \'Field short name \' will not update already existing values if they have been entered.',
"taxonomy_member_preferences"			=> "<strong>Member Preferences</strong><br />Which member groups have access to this $taxonomy_tree_label via the module interface. (Super Admins always have access)",
"no_trees_assigned"				=> "You currently don\'t have permission to manage any Taxonomy $taxonomy_tree_label.",
"edit_preferences"				=> 'Edit Preferences',
"save_and_close"				=> 'Save &amp; Close',
"select_channel" 				=> 'Select Channel',
"field_not_configured"			=> 'This field is not configured, please update this field\'s settings',
"field_not_configured_for_this_channel" => 'This field is not configured for publishing to this channel.',
"howdy_stranger"				=> 'Howdy Stranger!',
"get_started"					=> 'Get started',
"new_install"					=> 'Looks like we\'ve got a brand new install of Taxonomy here for you&hellip;',
"create_first_tree"				=> "Create your first $taxonomy_tree_label",
"internal_url_no_entries"		=> 'Select Template:',
"maximum_tree_depth"			=> "<strong>Maximum Depth</strong><br />How many nesting levels can the $taxonomy_tree_label have at the most (excluding the root node). <br />Leave \'0\' for unrestricted nesting levels.",
"unauthorised"					=> "You are not authorised",
//
''=>''
);