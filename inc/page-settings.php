<?php
/**
 * Display page settings
 */
?>
<h3>Settings VietNam Location</h3>
<p>Display Location on Post Type:</p>
<form id="vn-location-form" action="options.php" method="POST">
<?php 
$arr_post_types_excerpt = array('attachment', 'revision', 'nav_menu_item');
$post_types = get_post_types();
foreach($arr_post_types_excerpt as $item){
    unset($post_types[$item]);
}

$options = get_option('vn-types');
settings_fields('vn-location');

foreach($post_types as $type) {
    $post_type_obj = get_post_type_object($type);
    $checked = '';
    if($options){
        if (in_array($type, $options)) {
            $checked = 'checked="check"';
        }
    }
    
    echo '<div style="padding: 3px 0">';
    echo '<input type="checkbox" name="vn-types['. $type .']"  value="'. $type .'" '. $checked .' />';
    echo '<label>'. $post_type_obj->label .'</label>';
    echo '</div>';
}
?>
<div style="margin-top: 20px">
    <input name="vn-location-submit" id="submit_options_form" type="submit" class="button-primary" value="<?php esc_attr_e('Save Settings'); ?>" />
</div>
</form>
