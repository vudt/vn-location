<?php

class Meta_Box {
    
    protected $builder;
    
    protected $path;

    public function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies(){
        require_once 'builder.php';
        $this->builder = new VBuilder();
    }
    
    private function init_hooks(){
        $this->builder->add_action('admin_menu', $this, 'lct_menu');
        $this->builder->add_action('add_meta_boxes', $this, 'add_meta_box_cb');
        $this->builder->add_action('save_post', $this, 'save_dtv_meta_box');
        $this->builder->add_action('admin_enqueue_scripts', $this, 'vu_enqueue');
        $this->builder->add_action('wp_ajax_cb_ajax_location', $this, 'cb_ajax_location');
        $this->builder->add_action('admin_init', $this, 'vn_setup_options');
    }
    
    public function lct_menu(){
        add_menu_page('VN Location', 'VN Location', 'manage_options', 'settings-location', array($this, 'settings_page'));
    }
    
    public function settings_page(){
        require_once 'page-settings.php';
    }
    
    public function vn_setup_options(){
        register_setting('vn-location', 'vn-types', array($this, 'vn_settings_callback'));
    }
    
    public function vn_settings_callback($input) {
        $options = array();
        if(count($input) > 0) {
            foreach($input as $value) {
                $options[] = $value;
            }
        }
        return $options;
    }

    public function vu_enqueue(){
        wp_enqueue_script('ajax-script', PATH_PLUGIN.'js/common.js', array('jquery'));
        wp_localize_script('ajax-script', 'ajaxObj', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));
    }
    
    private function initObject(){
        $obj = new stdClass();
        $obj->value= 0 ;
        $obj->name = ' -- Select -- ';
        return $obj;
    }
    
    private function _create_fields($args = null){
        if(count($args) > 0) {
            $options_district  = $this->get_location_where($args['v_city'], 'districts', 'districtid', 'name', 'provinceid');
            $options_ward      = $this->get_location_where($args['v-district'], 'wards', 'wardid', 'name', 'districtid');
        }else {
            $options_district[] = $this->initObject(); 
            $options_ward[]     = $this->initObject();
            
        }
        $cities_arr = $this->get_location('cities', 'provinceid', 'name');
        $cities_options[] = $this->initObject();

        foreach ($cities_arr as $item) {
            $cities_options[] = $item;
        }

        // Create Select box city
        $city_field = array(
            'title'     => 'Tỉnh/Thành phố',
            'id'        => 'v_city',
            'class'     => 'v-select',
            'name'      => 'v-city',
            'type'      => 'select',
            'options'   => $cities_options
        );
        
        // Create Select box district
        $district_field = array(
            'title'     => 'Quận/Huyện',
            'id'        => 'v-district',
            'class'     => 'v-select',
            'name'      => 'v-district',
            'type'      => 'select',
            'options'   => $options_district
        );
        
        // Create Select box wards
        $ward_field = array(
            'title'     => 'Phường/Xã',
            'id'        => 'v-ward',
            'class'     => 'v-select',
            'name'      => 'v-ward',
            'type'      => 'select',
            'options'   => $options_ward
        );
        
        $fields = array($city_field, $district_field, $ward_field);
        return $fields;
    }
    
    public function add_meta_box_cb(){
        $args = array('action' => $_GET['action']);
        $post_types = get_option('vn-types');
        if(count($post_types) > 0){
            foreach($post_types as $slug) {
                add_meta_box('v-location', 'Location', array($this, 'show_meta_box'), $slug, 'normal', 'low', $args);
            }
        }
    }
    
    public function show_meta_box($post, $box){
        echo '<table id="tbl-location">';
        echo '<input type="hidden" name="v_custome_meta_box_nonce" value="'. wp_create_nonce(basename(__FILE__)) .'"/>';
            
        if($box['args']['action'] == 'edit'){    
            $val_city       = get_post_meta($post->ID, 'v_city');
            $val_district   = get_post_meta($post->ID, 'v-district');
            $val_ward       = get_post_meta($post->ID, 'v-ward');
            
            $arr_meta = array(
                'v_city'        => $val_city[0],
                'v-district'    => $val_district[0],
                'v-ward'        => $val_ward[0]
            );
            $fields = $this->_create_fields($arr_meta);
            foreach($fields as $field) {
                echo '<tr>';
                echo $this->render_field($field, $arr_meta[$field['id']]);
                echo '</tr>';
            }
        }else {
            $fields = $this->_create_fields();
            foreach($fields as $field) {
                echo '<tr>';
                echo $this->render_field($field);
                echo '</tr>';
            }
        } 
        echo '</table>';
    }
    
    private function render_field($field, $meta_value = null){
        $html = '';
        $html .= '<td>'.$field['title'].'</td>';
        $options = '';
        if($meta_value) {
            foreach($field['options'] as $option) {
                $selected = '';
                if($meta_value == $option->value) 
                    $selected = 'selected = "selected"';
                $options .= '<option '. $selected .' value="' . $option->value . '">' . $option->name . '</option>';
            }
        }else {
            foreach ($field['options'] as $option) {
                $options .= '<option value="' . $option->value . '">' . $option->name . '</option>';
            }
        }
        
        $html .= '<td><select name="'. $field['name'] .'" class="'. $field['class'] .'" id="'. $field['id'] .'">'.$options.'</select></td>'; 
        return $html;
    }
    
    public function cb_ajax_location(){
        if(!isset($_GET['val']) || !isset($_GET['type']))
            return FALSE;
        
        $type = $_GET['type'];
        switch ($type) {
            case 'v_city':
                $result = $this->get_location_where($_GET['val'], 'districts', 'districtid', 'name', 'provinceid');
                echo json_encode($result);
                exit;
            case 'v-district':
                $result =$this->get_location_where($_GET['val'], 'wards', 'wardid', 'name', 'districtid');
                echo json_encode($result);
                exit;
            default:
                break;
        }
    }
    
    private function get_location_where($id, $tblName, $col1, $col2, $where){
        global $wpdb;
        $tbl = $wpdb->prefix.$tblName;
        $results = $wpdb->get_results("SELECT $col1 AS value, $col2 AS name FROM $tbl WHERE $where = $id ");
        return $results;
    }

    private function get_location($tbl, $col1, $col2){
        global $wpdb;
        $tbl_Wards = $wpdb->prefix.$tbl;
        $wards = $wpdb->get_results("SELECT lc.$col1 AS value, lc.$col2 AS name FROM $tbl_Wards  AS lc");
        return $wards;
    }
    
    public function save_dtv_meta_box($post_id){
        if (!wp_verify_nonce($_POST['v_custome_meta_box_nonce'], basename(__FILE__))){
            return $post_id;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
            return $post_id;
        }
        $fields = $this->_create_fields();
        foreach($fields as $field) {
            update_post_meta($post_id, $field['id'], $_POST[$field['name']]);
        }
    }
    
    public function excute(){
        $this->builder->run();
    }
    
}

