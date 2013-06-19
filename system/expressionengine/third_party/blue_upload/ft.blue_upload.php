<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Controller $EE
 */
class Blue_upload_ft extends EE_Fieldtype {
    const BYTES_PER_MB = 1048576;

    public $info = array(
        'name' => 'Blue Upload Matrix (local shim)',
        'version' => '2.0'
    );

    function display_field($data)
    {
        return form_input(array(
            'name'  => $this->field_name,
            'id'    => $this->field_id,
            'value' => $data
        ));
    }

    /**
     * Matrix
     */

    function display_cell_settings($data)
    {
        if (! isset($data['directory'])) $data['directory'] = '';
        if (! isset($data['content_type'])) $data['content_type'] = '';
        if (! isset($data['maxsize'])) $data['maxsize'] = '';

        return array(
            array('directory', form_input('directory', $data['directory'], 'class="matrix-textarea"')),
            array('content_type', form_input('content_type', $data['content_type'], 'class="matrix-textarea"')),
            array('maxsize', form_input('maxsize', $data['maxsize'], 'class="matrix-textarea"'))
        );
    }

    function display_cell( $data )
    {
      return '<textarea class="matrix-textarea" name="'.$this->cell_name.'" rows="1">'.$data.'</textarea>';
    }



    public function replace_tag($data, $params = array(), $tagdata = FALSE) {
        
        $client = $this->EE->config->config['blue_upload_client'];
        
        if (isset($this->EE->config->config['blue_upload_branch'])) {
            $branch = $this->EE->config->config['blue_upload_branch'];
        } else {
            $branch = 'default';
        }
        
        if ($this->EE->config->config['blue_upload_cloudfront'] == 'n'){
            $path = 'http://' . $client . '.bluestatedigital.com/page/-/';
        } else {
            $path = 'http://dnwssx4l7gl7s.cloudfront.net/' . $client . '/' .$branch . '/page/-/';
        }

        if ($data != 0) {
            $query = $this->EE->db->query("SELECT file.storage_key, file.filename, file.upload_dir_id, dir.name, dir.parent_upload_dir_id
                FROM upload_dir as dir, upload_file as file
                WHERE file.upload_file_id = $data
                AND dir.upload_dir_id = file.upload_dir_id");        
            $filename = $query->row('filename');
            $dir = $query->row('name');
            $dir_array = array($dir);
            $dir_parent = $query->row('parent_upload_dir_id');
            $counter = 0;

            // loop through until you've gotten to the parent, adding each directory to the front of the dir_array
            while (!($dir_parent == NULL)) {
                $query = $this->EE->db->query("SELECT name, parent_upload_dir_id FROM upload_dir WHERE upload_dir_id = " . $dir_parent );
                $dir_name = $query->row('name');
                $dir_parent = $query->row('parent_upload_dir_id');
                if (!($dir_parent == NULL)) {
                    array_unshift($dir_array, $dir_name);
                }
                $counter++;
            }

            $dir_path = implode("/", $dir_array);

            return $path . $dir_path . '/' . $filename;
        } else {
            return '';
        }        
    }

}
