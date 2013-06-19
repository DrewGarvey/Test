<?php
/**
 * Developed by Biber Ltd. (http://www.biberltd.com)
 *
 * version:         1.0.3
 * last update:     25 August 2010
 * author:          Can Berkol
 * copyright:       Biber Ltd. (http://biberltd.com)
 * license:         GPLv3.0 (http://www.opensource.org/licenses/gpl-3.0.html)
 *
 *
 * description:
 * This field type enables you to have a WYSIWYG editor as a field type in EE 2.x
 *
 * The extension makes use of CK Editor which can be found at http://ckeditor.com
 * The versİon of CK Editor that is provided with this field type is CKEditor 3.3.1.
 *
 * Documentation: http://biberltd.com/wiki/English:ckeditor_field
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bbr_ckeditor_ft extends EE_Fieldtype {
	/**
	 * Fieldtype Information
	 */
	public $info = array(
		'name'     => 'CKEditor Field',
		'version'  => '1.0.2'
	);
    public $has_array_data = FALSE;
	/**
	 * Constructor for PHP < 5.0
	 */
	function Bbr_ckeditor_ft()
	{
		$this->__construct();
	}
    /**
     * Constructor
     *
     * @since       1.0.0
     * @date        21.07.2010
     * @author      Can Berkol
     *
     * PHP 5.0 & above
     */
    public function __construct(){
        $this->EE =& get_instance();
        parent::EE_Fieldtype();
    }
    /**
     * Destructor
     *
     * @since       1.0.0
     * @date        21.07.2010
     * @author      Can Berkol
     *
     * PHP 5.0 & above
     */
    public function __destructor(){
        foreach($this as $property => $value){
            $this->$property = null;
        }
    }
    /**
     * display_field
     *
     * @since       1.0.2
     * @date        04.08.2010
     * @author      Can Berkol
     *
     * Displays field data in publish screen
     */
	public function display_field($data){
	   /**
        * Global Settings
        */
        $custom_name = '';
        $custom_info = '';
        $preset = '';
        if(isset($this->settings['preset'])){
           $preset = $this->settings['preset'];
        }
        if(isset($this->settings['custom_name'])){
           $custom_name = $this->settings['custom_name'];
        }
        if(isset($this->settings['custom_info'])){
           $custom_info = $this->settings['custom_info'];
        }
        $custom_row1_selected = array();
        $custom_row2_selected = array();
        $custom_row3_selected = array();
        if(isset($this->settings['custom_row1_selected'])){
           $custom_row1_selected = $this->settings['custom_row1_selected'];
        }
        if(isset($this->settings['custom_row2_selected'])){
           $custom_row2_selected = $this->settings['custom_row2_selected'];
        }
        if(isset($this->settings['custom_row3_selected'])){
           $custom_row3_selected = $this->settings['custom_row3_selected'];
        }
        $groups = $this->groups();
        $toolbars = $this->toolbars();
        $ck_toolbars = array();
        /** *********** */
        switch($preset){
            case 'preset_bsd':
                $ck_toolbars = "BSDBasic";
                break;
            case 'preset_minimalistic':
                $ck_toolbars = $this->prepare_toolbars($groups['minimalistic']);
                break;
            case 'preset_fundemental':
                $ck_toolbars = $this->prepare_toolbars($groups['fundemental']);
                break;
            case 'preset_loaded':
                $ck_toolbars = $this->prepare_toolbars($groups['loaded']);
                break;
            case 'preset_full':
                $ck_toolbars = $this->prepare_toolbars($groups['full']);
                break;
            case 'preset_custom':
                $toolbars_to_add = array();
                $rows = array();
                foreach($custom_row1_selected as $code){
                    $toolbars_to_add[] = $toolbars[$code];
                }
                $toolbars_to_add[] = $toolbars['about'];
                $rows['1'] = $toolbars_to_add;
                $toolbars_to_add = array();
                foreach($custom_row2_selected as $code){
                    $toolbars_to_add[] = $toolbars[$code];
                }
                $rows['2'] = $toolbars_to_add;
                $toolbars_to_add = array();
                foreach($custom_row3_selected as $code){
                    $toolbars_to_add[] = $toolbars[$code];
                }
                $rows['3'] = $toolbars_to_add;
                $toolbars_to_add = array();
                $ck_toolbars = $this->prepare_toolbars($rows);
                break;
        }
        $default_language_code = 'en';
	    $rootpath =  pathinfo(FCPATH);
	   /**
        * We want this to be a text area field - CKEditor has been applied to it.
        */
        /**
         * Load javascript files
         */
        /**
         * Now get the CKEditor object
         */
        include('ckeditor.php');
        $ckeditor = new CKEditor('/cms/expressionengine/third_party/bbr_ckeditor/ckeditor/');
        $ckeditor->returnOutput = TRUE;
        $ckeditor->textareaAttributes = array('id' => $this->field_name);
        /**
         * Detect & Set Interface Language
         * Note: auto language detection only works with Biber Multi Language Support Extension for EE 2.x
         */
        if(isset($this->EE->config->_global_vars['language_code'])){
            $language_code = $this->EE->config->_global_vars['language_code'];
        }
        else{
            $language_code = 'en';
        }

        $ckeditor_config = array('language'         => $language_code,
                                 'emailProtection'  => 'encode',
                                 'resize_dir'       => 'vertical',
                                 'width'            => '100%',
                                 'entities'         => false,
                                 'htmlEncodeOutput' => false,
                                 'fullPage'         => false,
                                 'startupMode'      => 'wysiwyg',
                                 'removePlugins'    => 'entities',
                                 'toolbar'          => $ck_toolbars
                                 );
        $ckeditor->config = $ckeditor_config;
        /**
         * and create it.
         */
        $text_area = $ckeditor->editor($this->field_name, $data);
        return $text_area;
	}
    /**
     * install
     *
     * @since       1.0.0
     * @date        21.07.2010
     * @author      Can Berkol
     *
     * installs the field type.
     */
	public function install(){
	   $default_settings = array('preset' => 'preset_fundemental',
                                 'custom_name' => '',
                                 'custom_info' => '',
                                 'custom_row1_selected' => '',
                                 'custom_row2_selected' => '',
                                 'custom_row3_selected' => ''
                                );
		return $default_settings;
	}
    /**
     * display_global_settings
     *
     * @since       1.0.0
     * @date        23.07.2010
     * @author      Can Berkol
     *
     * displays global settings.
     */
	public function display_global_settings(){
	   $groups = $this->groups();
	   $toolbars = $this->toolbars();
       $buttons = $this->buttons();
       $current_custom_name = '';
       $current_custom_info = '';
       $current_custom_row1 = array();
       $current_custom_row1_selected = array();
       $current_custom_row2 = array();
       $current_custom_row2_selected = array();
       $current_custom_row3 = array();
       $current_custom_row3_selected = array();
        /**
         * We need to create a form for global settings. To ease the development we will user CodeIgniter helper functions:
         *
         * form_helper
         * html_helper
         *
         * Also it would be perfect that we have CodeIgniter library to create tables called Table.
        */
        $this->EE->load->library('Table');
        $this->EE->cp->load_package_css('bbr_ckeditor');
        $this->EE->cp->load_package_js('bbr_ckeditor');
        $this->EE->cp->load_package_js('bbr_texotela');
        /**
         * Load module text
         */
        $this->EE->lang->loadfile('bbr_ckeditor', 'bbr_ckeditor');
        $default_toolbar_options = array();
        foreach($toolbars as $code => $buttons){
            if('about' != $code){
                $default_toolbar_options[$code] = $this->EE->lang->line('toolbar-'.$code);
            }
        }
        asort($default_toolbar_options);
        /**
         * Get stored settings
         */
        $current_preset = '';
        $current_custom_name = '';
        $current_custom_info = '';
        if(isset($this->settings['preset'])){
           $current_preset = $this->settings['preset'];
        }
        if(isset($this->settings['custom_name'])){
           $current_custom_name = $this->settings['custom_name'];
        }
        if(isset($this->settings['custom_info'])){
           $current_custom_info = $this->settings['custom_info'];
        }
        if(!empty($this->settings['custom_row1_selected'])){
            foreach($this->settings['custom_row1_selected'] as $code){
                 $current_custom_row1_selected[$code] = $this->EE->lang->line('toolbar-'.$code);
            }
        }
        if(!empty($this->settings['custom_row2_selected'])){
            foreach($this->settings['custom_row2_selected'] as $code){
                 $current_custom_row2_selected[$code] = $this->EE->lang->line('toolbar-'.$code);
            }
        }
        if(!empty($this->settings['custom_row3_selected'])){
            foreach($this->settings['custom_row3_selected'] as $code){
                 $current_custom_row3_selected[$code] = $this->EE->lang->line('toolbar-'.$code);
            }
        }
        /**
         * Here we create innter tables.
         *
         * The first one will be the table that we will use for custom groups.
         */
        $this->EE->table->set_heading(array(
            array(
                'data'  => '',
                'style' => 'width:50%;height:1px;padding:2px;'
            ),
            array(
                'data'  => '',
                'style' => 'width:50%;height:1px;padding:2px;'
            ),
        ));
        $all_selected = array_merge($current_custom_row1_selected,
                                    $current_custom_row2_selected,
                                    $current_custom_row3_selected);
        $current_custom_row1 = array_diff($default_toolbar_options, $all_selected);
        $current_custom_row2 = array_diff($default_toolbar_options, $all_selected);
        $current_custom_row3 = array_diff($default_toolbar_options, $all_selected);
        /**
         * We don't need these in these release.
        $this->EE->table->add_row('<label>'.$this->EE->lang->line('custom-name').'</label>'
                                 .'<br />'.form_input(array('name' => 'custom_name', 'id' => 'custom_name'), $current_custom_name),
                                  '<label>'.$this->EE->lang->line('custom-info').'</label>'
                                 .'<br />'.form_input(array('name' => 'custom_info', 'id' => 'custom_info'), $current_custom_info)
                                 );
        */
        $this->EE->table->add_row('<label>'.$this->EE->lang->line('row-1').'</label>'
                                 .'<br /><div class="bbr_selectbox_container">'
                                 .form_multiselect('custom_row1', $current_custom_row1, array(), 'id="custom_row1"').'</div>'
                                 .'<br />'.form_button(array('name' => 'custom_row1_add', 'id' => 'custom_row1_add'),
                                          $this->EE->lang->line('btn-select'), 'class="submit" style="float:right;cursor:pointer;"'
                                          ),
                                  '<br /><div class="bbr_selectbox_container">'
                                 .form_multiselect('custom_row1_selected[]', $current_custom_row1_selected, array(), 'id="custom_row1_selected"').'</div>'
                                 .'<br />'.form_button(array('name' => 'custom_row1_remove', 'id' => 'custom_row1_remove'),
                                          $this->EE->lang->line('btn-deselect'),  'class="submit" style="cursor:pointer;"'
                                          )
                                 );
        $this->EE->table->add_row('<label>'.$this->EE->lang->line('row-2').'</label>'
                                 .'<br /><div class="bbr_selectbox_container">'
                                 .form_multiselect('custom_row2', $current_custom_row2, array(), 'id="custom_row2"').'</div>'
                                 .'<br />'.form_button(array('name' => 'custom_row2_add', 'id' => 'custom_row2_add'),
                                          $this->EE->lang->line('btn-select'), 'class="submit" style="float:right;cursor:pointer;"'
                                          ),
                                  '<br /><div class="bbr_selectbox_container">'
                                 .form_multiselect('custom_row2_selected[]', $current_custom_row2_selected, array(), 'id="custom_row2_selected"').'</div>'
                                 .'<br />'.form_button(array('name' => 'custom_row2_remove', 'id' => 'custom_row2_remove'),
                                          $this->EE->lang->line('btn-deselect'),  'class="submit" style="cursor:pointer;"'
                                          )
                                 );
        $this->EE->table->add_row('<label>'.$this->EE->lang->line('row-3').'</label>'
                                 .'<br /><div class="bbr_selectbox_container">'
                                 .form_multiselect('custom_row3', $current_custom_row3, array(), 'id="custom_row3"').'</div>'
                                 .'<br />'.form_button(array('name' => 'custom_row3_add', 'id' => 'custom_row3_add'),
                                          $this->EE->lang->line('btn-select'), 'class="submit" style="float:right;cursor:pointer;"'
                                          ),
                                  '<br /><div class="bbr_selectbox_container">'
                                 .form_multiselect('custom_row3_selected[]', $current_custom_row3_selected, array(), 'id="custom_row3_selected"').'</div>'
                                 .'<br />'.form_button(array('name' => 'custom_row3_remove', 'id' => 'custom_row3_remove'),
                                          $this->EE->lang->line('btn-deselect'),  'class="submit" style="cursor:pointer;"'
                                          )
                                 );
        $table_custom = $this->EE->table->generate();
        $this->EE->table->clear();
        /**
         * Creating settings layout
         */
        $this->EE->table->set_template($this->EE->load->_ci_cached_vars['cp_pad_table_template']);
        $this->EE->table->set_heading(array(
            array(
                'data'  => $this->EE->lang->line('heading-options'),
                'style' => 'width:40%;'
            ),
            array(
                'data'  => $this->EE->lang->line('heading-settings'),
                'style' => 'width:60%;'
            ),
        ));
        /**
         * Create list of groups
         */
        $list_groups = '';
        $checked = FALSE;
        $custom_checked = FALSE;
        foreach($groups as $code => $row){
            if('preset_'.$code == $current_preset){
                $checked = TRUE;
            }
            $group = form_radio(array('name' => 'preset', 'id' => 'preset_'.$code, 'value' => 'preset_'.$code), $code, $checked)
                     .'<label for="preset_'.$code.'">'.$this->EE->lang->line('groups-'.$code).'</label><br />'
                     .'<div class="bbr_small_info">'.$this->EE->lang->line('groups-'.$code.'-i').'</div>';
            if($checked){
                $checked = FALSE;
            }
            $list_groups .= $group;
        }
        if($current_preset == 'preset_custom'){
            $custom_checked = TRUE;
        }
        /**
         * Add "Custom" option
         */
        $list_groups .= form_radio(array('name' => 'preset', 'id' => 'preset_custom', 'value' => 'preset_custom'), 'custom', $custom_checked)
                     .'<label for="preset_custom">'.$this->EE->lang->line('groups-custom').'</label><br />'
                     .'<div class="bbr_small_info">'.$this->EE->lang->line('groups-custom-i').'</div>';
        $hidden_settings = '<div id="bbr_ck_custom_settings" style="display:none">'.$table_custom.'</div>';
        $this->EE->table->add_row('<label>'.$this->EE->lang->line('options-preset').'</label>'
                                 .'<br /><div class="bbr_small_info">'.$this->EE->lang->line('options-preset-i').'</div>',
                                  $list_groups.$hidden_settings);
        $table = $this->EE->table->generate();
        $table = '<div id="bbr_fieldtype_settings_container">'.$table.'</div>';
	    return $table;
	}
    /**
     * save_global_settings
     *
     * @since       1.0.0
     * @date        21.07.2010
     * @author      Can Berkol
     *
     * saves global settings.
     */
	public function save_global_settings(){
	   /**
        * For some reason just serializing $_POST gives errors therefore we first will store data in a new array.
        *
        */
        $settings = array();
        /**
         * get rid off unnecessary $_POST['data']
         */
        unset($_POST['submit']);
        foreach($_POST as $setting_name => $setting){
            $settings[$setting_name] = $setting;
        }
        return $settings;
	}
    /**
     * replace_tag
     *
     * @since       1.0.1
     * @date        25.07.2010
     * @author      Can Berkol
     *
     * creates the ability to output field contents in templates.
     *
     * @param       mixed       $data       field data
     * @params      mixed       $params     additional parameters
     * @tagdata     mixed       $tagdata    tag data within tag pairs.
     */
    public function replace_tag($data, $params = array(), $tagdata = FALSE){
        return $data;
	}
    /**
     * groups
     *
     * @since       1.0.0
     * @date        22.07.2010
     * @author      Can Berkol
     *
     * groups all the toolbars on rows.
     *
     * @param       mixed       $data       field data
     * @params      mixed       $params     additional parameters
     *
     * @return      mixed       $groups|FALSE     Either all groups or a specific one.
     */
    private function groups($get = 'all'){
        $available_options = array('all', 'minimalistic', 'fundemental', 'loaded', 'full', 'bsd');
        /**
         * Validate parameter
         */
        if(!in_array($get, $available_options)){
            return FALSE;
        }
        /**
         * Get toolbars
         */
        $toolbars = $this->toolbars();
        /**
         * Assign toolbars to groups
         */
        $groups = array('minimalistic'  => array('1' => array(
                                                 $toolbars['font'],
                                                 $toolbars['font-adv'],
                                                 $toolbars['edit'],
                                                 $toolbars['paragraph'],
                                                 $toolbars['about'],
                                                 )),
                        'fundemental'   => array('1' => array(
                                                 $toolbars['font'],
                                                 array_merge($toolbars['edit'], $toolbars['edit-adv']),
                                                 array_merge($toolbars['find'], $toolbars['history']),
                                                 $toolbars['print'],
                                                 $toolbars['editor'],
                                                 $toolbars['about'],
                                                )),
                        'bsd'           =>  array('1' => array()),
                        'loaded'        => array('1' => array(
                                                 $toolbars['font'],
                                                 array_merge($toolbars['edit'], $toolbars['edit-adv']),
                                                 array_merge($toolbars['find'], $toolbars['history']),
                                                 $toolbars['form'],
                                                 $toolbars['html'],
                                                 $toolbars['file'],
                                                 $toolbars['print'],
                                                 $toolbars['about'],
                                                ),
                                                '2'  => array(
                                                 $toolbars['font-adv'],
                                                 $toolbars['paragraph'],
                                                 $toolbars['lists'],
                                                 $toolbars['link'],
                                                 $toolbars['media'],
                                                 $toolbars['source'],
                                                 $toolbars['editor'],
                                                )),
                        'full'          => array('1' => array(
                                                 $toolbars['font'],
                                                 array_merge($toolbars['edit'], $toolbars['edit-adv']),
                                                 array_merge($toolbars['find'], $toolbars['history']),
                                                 $toolbars['file'],
                                                 $toolbars['print'],
                                                 $toolbars['about'],
                                                 ),
                                                 '2' => array(
                                                 $toolbars['font-adv'],
                                                 $toolbars['paragraph'],
                                                 $toolbars['lists'],
                                                 $toolbars['link'],
                                                 $toolbars['media'],
                                                 $toolbars['editor'],
                                                 ),
                                                 '3' => array(
                                                 $toolbars['form'],
                                                 $toolbars['html'],
                                                 $toolbars['smiley'],
                                                 $toolbars['spellcheck'],
                                                 $toolbars['source'],
                                                )),
                       );
        if($get == 'all'){
            return $groups;
        }
        else{
            return $groups[$get];
        }
    }
    /**
     * toolbars
     *
     * @since       1.0.0
     * @date        22.07.2010
     * @author      Can Berkol
     *
     * combines buttons into functional toolbars.
     *
     * @return      array       $toolbars
     */
    private function toolbars(){
        $buttons = $this->buttons();
        /**
         * Define all toolbars;
         */
        $toolbars = array('about'       => array($buttons['about']
                                                ),
                          'lists'       => array($buttons['bullist'],
                                                 $buttons['numlist']
                                                ),
                          'edit'        => array($buttons['cut'],
                                                 $buttons['copy'],
                                                 $buttons['paste'],
                                                ),
                          'edit-adv'    => array($buttons['pasteword'],
                                                 $buttons['selectall'],
                                                 $buttons['removefor'],
                                                ),
                          'editor'      => array($buttons['maximize'],
                                                 $buttons['showblcks']
                                                 ),
                          'smiley'      => array($buttons['smiley']
                                                ),
                          'find'        => array($buttons['find'],
                                                 $buttons['replace']
                                                ),
                          'file'        => array($buttons['save'],
                                                 $buttons['newpage'],
                                                 $buttons['preview'],
                                                ),
                          'form'        => array($buttons['form'],
                                                 $buttons['checkbox'],
                                                 $buttons['radio'],
                                                 $buttons['textfield'],
                                                 $buttons['textarea'],
                                                 $buttons['select'],
                                                 $buttons['button'],
                                                 $buttons['imgbutton'],
                                                 $buttons['hiddenfld'],
                                                ),
                          'font'        => array($buttons['bold'],
                                                 $buttons['italic'],
                                                 $buttons['strike'],
                                                 $buttons['underline'],
                                                 $buttons['subscript'],
                                                 $buttons['supscript'],
                                                 $buttons['specialch'],
                                                ),
                          'font-adv'    => array($buttons['font'],
                                                 $buttons['fontsize'],
                                                 $buttons['textcolor'],
                                                 $buttons['bgcolor'],
                                                ),
                          'history'     => array($buttons['undo'],
                                                 $buttons['redo']
                                                ),
                          'html'        => array($buttons['blockquot'],
                                                 $buttons['creatediv'],
                                                 $buttons['table']
                                                ),
                          'layout'      => array($buttons['templates'],
                                                 $buttons['horizrule'],
                                                 $buttons['pagebreak'],
                                                ),
                          'link'        => array($buttons['link'],
                                                 $buttons['unlink'],
                                                 $buttons['anchor'],
                                                ),
                          'media'       => array($buttons['image'],
                                                 $buttons['flash']
                                                ),
                          'paragraph'   => array($buttons['justleft'],
                                                 $buttons['justcntr'],
                                                 $buttons['justright'],
                                                 $buttons['justblock'],
                                                ),
                          'paragraph-adv'=>array($buttons['format'],
                                                ),
                          'print'       => array($buttons['print'],
                                                ),
                          'source'      => array($buttons['source']
                                                ),
                          'spellcheck'  => array($buttons['spell'],
                                                 $buttons['spelltyp'],
                                                ),
                         );
        return $toolbars;
    }
    /**
     * buttons
     *
     * @since       1.0.0
     * @date        21.07.2010
     * @author      Can Berkol
     *
     * registers all available buttons of CKEditor for later use
     *
     * @return      array       $buttons
     */
    private function buttons(){
        /**
         * Here we register all possible buttons of CKEditor.
         */
        $buttons = array('about'    =>  'About',
                         'anchor'   =>  'Anchor',
                         'bgcolor'  =>  'BGColor',
                         'blockquot'=>  'Blockquote',
                         'bold'     =>  'Bold',
                         'bullist'  =>  'BulletedList',
                         'button'   =>  'Button',
                         'checkbox' =>  'Checkbox',
                         'copy'     =>  'Copy',
                         'creatediv'=>  'CreateDiv',
                         'cut'      =>  'Cut',
                         'flash'    =>  'Flash',
                         'find'     =>  'Find',
                         'font'     =>  'Font',
                         'fontsize' =>  'FontSize',
                         'form'     =>  'Form',
                         'format'   =>  'Format',
                         'hiddenfld'=>  'HiddenField',
                         'horizrule'=>  'HorizontalRule',
                         'image'    =>  'Image',
                         'imgbutton'=>  'Image Button',
                         'indent'   =>  'Indent',
                         'italic'   =>  'Italic',
                         'justblock'=>  'JustifyBlock',
                         'justcntr' =>  'JustifyCenter',
                         'justleft' =>  'JustifyLeft',
                         'justright'=>  'JustifyRight',
                         'link'     =>  'Link',
                         'maximize' =>  'Maximize',
                         'newpage'  =>  'NewPage',
                         'numlist'  =>  'NumberedList',
                         'outdent'  =>  'Outdent',
                         'pagebreak'=>  'PageBreak',
                         'paste'    =>  'Paste',
                         'pastetext'=>  'PasteText',
                         'pasteword'=>  'PasteFromWord',
                         'preview'  =>  'Preview',
                         'print'    =>  'Print',
                         'radio'    =>  'Radio',
                         'redo'     =>  'Redo',
                         'removefor'=>  'RemoveFormat',
                         'replace'  =>  'Replace',
                         'save'     =>  'Save',
                         'select'   =>  'Select',
                         'selectall'=>  'SelectAll',
                         'showblcks'=>  'ShowBlocks',
                         'smiley'   =>  'Smiley',
                         'source'   =>  'Source',
                         'styles'   =>  'Styles',
                         'specialch'=>  'SpecialChar',
                         'spell'    =>  'SpellChecker',
                         'spelltyp' =>  'Scayt',
                         'strike'   =>  'Strike',
                         'subscript'=>  'Subscript',
                         'supscript'=>  'Superscript',
                         'table'    =>  'Table',
                         'textcolor'=>  'TextColor',
                         'templates'=>  'Templates',
                         'textarea' =>  'Textarea',
                         'textfield'=>  'TextField',
                         'underline'=>  'Underline',
                         'undo'     =>  'Undo',
                         'unlink'   =>  'Unlink',
                         );
        return  $buttons;
    }
    /**
     * prepare_toolbars
     *
     * @since       1.0.0
     * @date        24.07.2010
     * @author      Can Berkol
     *
     * prepares CKEditor valid toolbars list.
     *
     * @param       array       $group          Holds a group of toolbars
     */
    private function prepare_toolbars(array $group){
        $separator = '-';
        $newline = '/';
        $ck_toolbars = array();
        $grouped_buttons = array();
        foreach($group as $group_name => $row){
            if(is_array($row) && count($row) > 0){
                foreach($row as $toolbar){
                    if(is_array($toolbar) && count($toolbar) > 0){
                        foreach($toolbar as $button){
                            $grouped_buttons[] = $button;
                        }
                        $grouped_buttons[] = $separator;
                    }
                }
                $current_size = count($grouped_buttons) - 1;
                if($grouped_buttons[$current_size] == $separator){
                    array_pop($grouped_buttons);
                }
                $ck_toolbars[] = $grouped_buttons;
                $grouped_buttons = array();
                $ck_toolbars[] = $newline;
            }
        }
        $element_count = count($ck_toolbars);
        if(is_array($ck_toolbars) && $element_count > 0){
            $current_size = $element_count - 1;
            if($ck_toolbars[$current_size] == $newline){
                array_pop($ck_toolbars);
            }
        }
        return $ck_toolbars;
    }
}

/**
 * Change Log:
 *
 *  v 1.0.3
 *
 * - A bug causing JS Erros on custom field selection is fixed.
 *
 *  v 1.0.2
 *
 * - A bug causing notices to be outputted due to loose variable assignments have been fixed. (reported: by Patrick Stinnett).
 *
 * v 1.0.1
 *
 * - Bug fixed that prevented the saved data to be shown in templates.
 *
 */
// END bbr_ckeditor class

/* End of file ft.bbr_ckeditor.php */
/* Location: ./expressionengine/third_party/bbr_ckeditor/ft.bbr_ckeditor.php */