<?php
/**
 * Plugin Name:       OutsourcingVN Change Category Name
 * Plugin URI:        https://outsourcingvn.com/
 * Description:       Change Category, Tag, Taxonomy Name
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            OutsourcingVN 
 * Author URI:        https://outsourcingvn.com/contact-us/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       auto-change-category-name
 * Domain Path:       /languages
 */



function accn_add_setting_page() {
    add_menu_page(
        'Auto change category name',
        'Auto Change Category Name',
        'manage_options',
        'accn-slug',
        'accn_content_function',
        'dashicons-menu-alt',
        10
    );
}
add_action('admin_menu', 'accn_add_setting_page');

function accn_content_function() {
   ?>
   <form action="options.php" method="post">
        <?php
            settings_fields('accn_plugin_options');
            do_settings_sections('accn-slug');
        ?>
        <input name = "submit" class = "btn btn-primary" type = "submit" value="<?php esc_attr_e('Save'); ?>" />
   </form><br>
   <form action="" method="post">
   <input type="submit" class = "btn btn-primary" name = "change_category" value ="<?php esc_attr_e('Change Category Title'); ?>"/>
<?php
 if(isset($_POST['change_category'])){
     accn_settings_listing_taxonomy();    
 }
  ?>
   </form>
   <?php
}

function accn_register_settings() {
    register_setting('accn_plugin_options','accn_plugin_options', 'accn_plugin_options_validate' );

    add_settings_section('accn_settings', 'Auto Change Category Name', 'accn_plugin_section_text', 'accn-slug');

    add_settings_field('new_settings_plugin_list_taxonomy', 'Select Taxonomy', 'accn_plugin_settings_list_taxonomy', 'accn-slug', 'accn_settings');
    add_settings_field('new_settings_plugin_before', 'Update Before The Category Name','accn_plugin_settings_before', 'accn-slug', 'accn_settings');
    add_settings_field('new_settings_plugin_after', 'Update After The Category Name','accn_plugin_settings_after', 'accn-slug', 'accn_settings');
    add_settings_field('new_settings_plugin_offset','Offset','accn_settings_plugin_offset','accn-slug', 'accn_settings');
}
add_action('admin_init', 'accn_register_settings');

function accn_plugin_section_text() {
}

function accn_settings_plugin_offset(){
    $options = get_option('accn_plugin_options');
    ?>
    <input id= "new_settings_plugin_offset" name="accn_plugin_options[offset]" type="text" value="<?php echo isset($options['offset']) ? esc_attr($options['offset']) : ''; ?> " style="width: 210px;" />
    <?php
}

function accn_plugin_settings_before() {
    $options = get_option( 'accn_plugin_options' );
    ?>
    <input id="new_settings_plugin_before" name="accn_plugin_options[add_before]" type="text" value="<?php echo isset($options['add_before']) ? esc_attr( $options['add_before'] ) : ''; ?>" style="width: 210px;" />
    <?php  
}

function accn_plugin_settings_after() {
    $options = get_option( 'accn_plugin_options' );
    ?>
    <input id="new_settings_plugin_after" name="accn_plugin_options[add_after]" type="text" value="<?php echo isset($options['add_after']) ? esc_attr( $options['add_after'] ) : ''; ?>" style="width: 210px;" />
    <?php  
}

function accn_plugin_settings_list_taxonomy() {
    $args = array(
        'public'   => true
      ); 
      $output = 'names'; 
      $operator = 'and'; 
      $taxonomies = get_taxonomies( $args, $output, $operator ); 
      $options = get_option( 'accn_plugin_options' );
        echo "<select id='new_settings_plugin_list_taxonomy' name='accn_plugin_options[list]'>";
          echo "<option values=''>-- Please chose Taxonomy --</option>";
          foreach ( $taxonomies as $taxonomy ) {
              ?>
                  <option value="<?php echo esc_attr($taxonomy) ?>" <?php selected( $options['list'], $taxonomy); ?>><?php echo esc_html__( $taxonomy, 'list' ) ?></option>
              <?php 
          }
        echo '</select>';  
}

function accn_settings_listing_taxonomy() {
    $options = get_option( 'accn_plugin_options' );
    $setting_before = $options['add_before'];
    $setting_after = $options['add_after'];
    $offset = $options['offset'];
    $length_before = strlen($setting_before);
    $length_after = strlen($setting_after);
    $listing_taxonomy = $options['list'];
    $dem_category = get_categories( array('taxonomy'=>  $listing_taxonomy,'hide_empty'=> false));
    $dem = count($dem_category);
    $count = 0;
    $categorise = get_terms( array('taxonomy'=>  $listing_taxonomy,'orderby' => 'name','offset'=> $offset,'number' => $dem, 'order' => 'ASC', 'hide_empty'=> false));
    foreach($categorise as $category) {
        $category_names = $category->name;      
        $ids = $category->term_id;
        if(!empty($setting_before) and substr_compare($category->name,$setting_before,0,$length_before) != 0) {
            $category_names = $setting_before ." ". $category_names;
        }           
        if(!empty($setting_after) and  substr_compare($category->name,$setting_after,-$length_after,$length_after) != 0){
            $category_names = $category_names ." ". $setting_after;   
        }
        if(!empty($setting_before) and !empty($setting_after)){
            $update = wp_update_term( $ids, $listing_taxonomy, array('name' => $category_names , 'slug' => $category_names ));
            if(!is_wp_error($update)){
              $count++;
            }else{
                echo '<br>';
                print_r("<h3 style=\"color: red;\">".esc_html($update->get_error_message())."</h3");
            } 
        }   
    }  
    echo '<br>';
    print_r("<h3>"."Having ".esc_html($dem)." categories and ".esc_html($count)." updated!"."</h3>");  
    echo '</br>';
}




