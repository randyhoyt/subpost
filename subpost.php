<?php

/*
Plugin Name: Subordinate Post Type Helpers
Description: This plugin provides a number of helpers for registering a custom post type that is subordinate to another post type.
Version: VERSION_NUMBER
Author: randyhoyt
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

function register_sub_post_type($post_type,$args,$parent_post_type) {
    
    $current_sub_post_type = array (
            'post_type' => $post_type,
            'args' => $args,
            'parent_post_type' => $parent_post_type
        ); 

    register_post_type($post_type,$args);

    $sub_post_type_factory = SubPostTypeFactory::getInstance();
    $sub_post_type_factory->addPostType($current_sub_post_type);
    add_action('init',$sub_post_type_factory->wp_init(),99999);

}

class SubPostTypeFactory
{

    private static $sub_post_type_factory;
    private static $sub_post_types;
    private static $sub_post_type_current;

    private function __construct() {}

    public static function getInstance() {

        if (!self::$sub_post_type_factory) {
            self::$sub_post_type_factory = new SubPostTypeFactory();
        }
        return self::$sub_post_type_factory;
    }

    public static function addPostType($post_type_new) {

        self::$sub_post_types[$post_type_new["post_type"]] = $post_type_new;
    
    }

    public function getPostTypes() {

        return self::$sub_post_types;

    }

    public function setCurrentPostType($post_type) {

        self::$sub_post_type_current = $post_type;

    }

    public function getCurrentPostType() {

        return self::$sub_post_type_current;

    }

    public function wp_init() {

        $sub_post_type_factory = self::getInstance();
        $post_types = $sub_post_type_factory->getPostTypes();
        
        if ($post_types) {
        
            add_action("admin_menu", "subpost_add_to_submenu",10,2);
            add_action("add_meta_boxes","subpost_add_meta_box");

        }
    }

}

function subpost_add_to_submenu() {

    global $menu,$submenu;

    $sub_post_type_factory = SubPostTypeFactory::getInstance(); 
    $post_types = $sub_post_type_factory->getPostTypes();

    if ($post_types) {
        foreach ($post_types as $post_type) {
            if ($post_type["parent_post_type"] != "post") {
                $parent_query_string = '?post_type=' . $post_type["parent_post_type"];
            } else {
                $parent_query_string = "";
            }
            if ($post_type['args']['show_in_menu']!== false) {
                add_submenu_page('edit.php' . $parent_query_string, $post_type['args']['labels']['name'], $post_type['args']['labels']['name'], 'edit_posts', 'edit.php?post_type=' . $post_type["post_type"]);
                add_submenu_page('edit.php' . $parent_query_string, $post_type['args']['labels']['all_items'], "&#8212; " . $post_type['args']['labels']['all_items'], 'edit_posts', 'edit.php?post_type=' . $post_type["post_type"]);
                add_submenu_page('edit.php' . $parent_query_string, $post_type['args']['labels']['add_new_item'], "&#8212; " . $post_type['args']['labels']['add_new_item'], 'edit_posts', 'post-new.php?post_type=' . $post_type["post_type"]);
            }
            remove_menu_page('edit.php?post_type=' . $post_type["post_type"]);
        }
    }

}

function subpost_add_meta_box() {

    global $post;
    $sub_post_type_factory = SubPostTypeFactory::getInstance();
    $post_types = $sub_post_type_factory->getPostTypes();
        
    if ($post_types) {

        foreach($post_types as $post_type) {

            if ($post->post_type == $post_type["parent_post_type"]) {

                $sub_post_type_factory->setCurrentPostType($post_type["post_type"]);

                add_meta_box(
                            'custom_meta_box_' . $post_type["post_type"], 
                            $post_type['args']['labels']['name'], 
                            'subpost_render_meta_box', 
                            $post_type["parent_post_type"], 
                            'normal', 
                            'high'
                    );

            }
        }

    }

}

function subpost_render_meta_box() {

    global $post;

    $sub_post_type_factory = SubPostTypeFactory::getInstance();
    $post_types = $sub_post_type_factory->getPostTypes();
    $current_sub_post_type = $post_types[$sub_post_type_factory->getCurrentPostType()];

    echo '<div id="subpost_list_children_' . $current_sub_post_type["post_type"] . '">';
    $output = subpost_display_all_children($post->ID,$current_sub_post_type["post_type"]);
    if ($output) {
        echo $output;
    } else {
        echo '<p>' . $current_sub_post_type["args"]['labels']['not_found'] . '</p>';
    }
    echo '</div>';
    echo '<a title="' . $current_sub_post_type["args"]['labels']['add_new_item'] . '" class="button thickbox" href="'. plugin_dir_url(__FILE__) . 'children.php?&amp;form_title=' . $current_sub_post_type["args"]['labels']['add_new_item'] . '&amp;post_parent=' . $post->ID . '&amp;post_type=' . $current_sub_post_type["post_type"] . '&amp;TB_iframe=1&amp;width=480&amp;height=440">' . $current_sub_post_type["args"]['labels']['add_new_item'] . '</a>';   
}

function subpost_display_all_children($post_id,$sub_post_type_name) {

    $output = "";

    $sub_post_type_factory = SubPostTypeFactory::getInstance();
    $post_types = $sub_post_type_factory->getPostTypes();
    $sub_post_type = $post_types[$sub_post_type_name];  

    $subposts = get_posts(array(
                'post_type' => $sub_post_type["post_type"],
                'post_parent' => $post_id,
                'numberposts' => -1,
                'order' => 'ASC'
            ));
    if ($subposts) {

        $output .= '<table class="wp-list-table widefat fixed posts" style="margin: 12px 0; border-bottom: 0;" cellspacing="0"><tbody id="the-list">';
        
        foreach($subposts as $subpost) {
            $output .= subpost_display_one_child($subpost,$sub_post_type);
        }

        $output .= '</tbody></table>';

        return $output;

    } else {
        return false;
    }

}

function subpost_display_one_child($post,$sub_post_type) {

    $output = "";

    $edit_link = plugin_dir_url(__FILE__) . 'children.php?&amp;form_title=' . $sub_post_type["args"]['labels']['edit_item'] . '&amp;post=' . $post->ID . '&amp;post_parent=' . $post->post_parent . '&amp;post_type=' . $sub_post_type["post_type"] . '&amp;TB_iframe=1&amp;width=480&amp;height=440';
    $output .= '<tr id="post-' . $post->ID . '" class="post-' . $post->ID . '" valign="top">';
    $output .= '<td class="post-title page-title column-title"><strong><a class="row-title thickbox" href="' . $edit_link . '" title="' . $sub_post_type["args"]['labels']['edit_item'] . '">' . esc_attr($post->post_title) . '</a></strong>';
    $output .= '<span class="row-actions"><span class="edit"><a class="thickbox" href="' . $edit_link . '" title="' . $sub_post_type["args"]['labels']['edit_item'] . '">' . $sub_post_type["args"]['labels']['edit_item'] . '</a></span></td>';

    $output .= apply_filters("subpost_display_column","",$post,$sub_post_type);

    $output .= "</tr>";

    return $output;

}

add_action( 'admin_footer', 'subpost_javascript_footer');
function subpost_javascript_footer() {

?>

    <script>
        
        function subpost_list_children(html_display,sub_post_type) {            
            jQuery('#subpost_list_children_' + sub_post_type).empty().append(html_display);         
            tb_remove();
        }

    </script>
    
<?php
    
}