<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once $_SERVER['DOCUMENT_ROOT'] . '/ee_config.inc.php';

class Matrix_blue_upload_ft extends EE_Fieldtype{
    const BYTES_PER_MB = 1048576;

    public $info = array(
        'name' => 'Blue Upload'
    );

	public $default_settings = array(
		'content_type' => 'any'
	);

    public $cell_name;
    private $_chapter_id;
    private $_settings_manager;
    private $_publisher;
    private $_display;

    public function __construct() {
        parent::__construct();
        $this->EE =& get_instance();
        $this->_initialize();
		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['matrix']['celltypes']['blue_upload']))
		{
			$this->EE->session->cache['matrix']['celltypes']['blue_upload'] = array();
		}
		$this->cache =& $this->EE->session->cache['matrix']['celltypes']['blue_upload'];
    }

    public function display_cell_settings($data) {
       return  $this->_getSetingsManager()->getSettings($data);
    }

    public function save_cell($data) {
        return $this->_getPublisher()->save($data);
    }

    public function validate_cell($data) {
        return $this->_getPublisher()->validate($data);
    }

    public function display_cell($data) {
        if(!in_array('<script type="text/javascript" src="/javascript/mixin/getVar.js"></script>', $this->EE->cp->footer_item)){
            $this->EE->cp->add_to_foot('<script type="text/javascript" src="/javascript/mixin/getVar.js"></script>');
        }

        if(!in_array('<script type="text/javascript" src="/ext/jquery/ui/jquery-ui-1.8.7.custom.min.js"></script>', $this->EE->cp->footer_item)){
            $this->EE->cp->add_to_foot('<script type="text/javascript" src="/ext/jquery/ui/jquery-ui-1.8.7.custom.min.js"></script>');
        }

        if(!in_array('<script type="text/javascript" src="/javascript/Core/widget/BigList.js"></script>', $this->EE->cp->footer_item)){
            $this->EE->cp->add_to_foot('<script type="text/javascript" src="/javascript/Core/widget/BigList.js"></script>');
        }

        if(!in_array(
               '<script type="text/javascript" src="/cms/expressionengine/third_party/blue_upload_field/blueUploadField.js"></script>',
               $this->EE->cp->footer_item)){

            $this->EE->cp->add_to_foot('<script type="text/javascript" src="/cms/expressionengine/third_party/blue_upload_field/blueUploadField.js"></script>');
        }

        if(!in_array('<link rel="stylesheet" href="/css/core.css" type="text/css" />', $this->EE->cp->footer_item)){
            $this->EE->cp->add_to_foot('<link rel="stylesheet" href="/css/core.css" type="text/css" />');
        }

        $this->EE->cp->add_to_foot('<script type="text/javascript" src="/cms/index.php?D=cp&C=javascript&M=combo_load&ui=tabs&file=cp/global"></script>');
        $this->EE->cp->add_to_foot('<script type="text/javascript" src="/cms/expressionengine/third_party/matrix/celltypes/blue_upload.js"></script>');

        return $this->_getPublisher()->displayField($data);
    }

    public function replace_tag($data, $params = array(), $tagdata = FALSE) {
        return $this->_getDisplay()->replaceTag($data, $params, $tagdata);
    }

    /**
     * Never, ever EVER write an init() function. All of this stuff should be done in a factory and passed to the
     * constructor, and assignments should be done there. But, since CodeIgniter/ExpressionEngine are to good code
     * what broken elevator cables are to not plummeting to your death, here is an init() function.
     */
    private function _initialize() {
        $subsiteChapterFactory = new Blue_ExpressionEngine_SubsiteChapter_Factory();
        $subsiteChapter = $subsiteChapterFactory->create();
        $this->_chapter_id = $subsiteChapter->getChapterId(
            $this->EE->config->item('site_id')
        );
    }

    private function _getSetingsManager() {
        if (!isset($this->_settings_manager)) {

            $blue_env_settings = new Blue_Env_Settings();
            $blue_env_settings->setCurrentChapterId($this->_chapter_id);

            $dl = new Framework_Upload_DirectoryList(
                new Upload_Dir_Gateway(DBBlue::db('frontend_read'), $blue_env_settings),
                '&nbsp;'
            );

            $this->_settings_manager = new Blue_ExpressionEngine_Fieldtype_MatrixBlueUpload_Settings(
                $dl,
                new Blue_CodeIgniter_Helper_Form($this->EE->load)
            );

            if (isset($this->EE->table)) {
                $this->_settings_manager->setTable($this->EE->table);
            }
        }

        return $this->_settings_manager;
    }

    private function _getUploader($uploadGateway, $maxSize, $allowedTypes){

        $customUploader = $_SERVER['DOCUMENT_ROOT'].'/cms/expressionengine/third_party/bsd_customuploader/CustomUploader.php';

        //TODO: Choose uploader based on user-provided setting
        if(file_exists($customUploader)){
            require_once $customUploader;
            return new Blue_ExpressionEngine_CustomUploader();
        } else{
            return new Framework_Upload_FileUploader(
                                $uploadGateway,
                                $maxSize,
                                $allowedTypes,
                                $this->_chapter_id
                      );
        }

    }

    private function _getPublisher() {
        $blue_env_settings = new Blue_Env_Settings();
        $blue_env_settings->setCurrentChapterId($this->_chapter_id);
        $upload_file_gateway = new Upload_File_Gateway(DBBlue::db('frontend_read'), $blue_env_settings);
        $view_factory = new Blue_View_Factory();
        $upload_dir_gateway = new Upload_Dir_Gateway(DBBlue::db('frontend_read'), $blue_env_settings);

        switch ($this->settings['content_type']) {
        case 'images':
            $allowed_types = array('gif', 'jpg', 'jpeg', 'png');
            break;
        case 'all':
        default:
            $allowed_types = array();
        }

        if (!isset($this->_publisher) || $this->_publisher->getFieldName() != $this->field_name) {

            $name = (property_exists($this,'cell_name')) ? $this->cell_name: $this->field_name;
            $this->_publisher = new Blue_ExpressionEngine_Fieldtype_MatrixBlueUpload_Publisher(
                $name,
                $this->settings,
                new Blue_CodeIgniter_Helper_Form($this->EE->load),
                $upload_file_gateway,
                $this->EE->javascript,
                $this->EE->cp,
                $view_factory->createForExpressionEngine(),
                $this->_getUploader($upload_file_gateway,
                    $this->settings['maxsize'] * self::BYTES_PER_MB,
                    $allowed_types),
                new Blue_SessionProvider(),
                new Framework_Ui_Widget_BigList_Factory(),
                $upload_dir_gateway
                );
        }
        return $this->_publisher;
    }

    private function _getDisplay() {
        if (!isset($this->_display)) {
            $displayFactory = new Blue_ExpressionEngine_Fieldtype_BlueUpload_Display_Factory();
            $this->_display = $displayFactory->create($this->_chapter_id);
        }

        return $this->_display;
    }

}
