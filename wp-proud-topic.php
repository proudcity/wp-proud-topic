<?php

/**
Plugin Name: Proud Topic
Plugin URI: http://proudcity.com/
Description: Declares a Topic custom post type.
Version: 2026.03.17.1358
Author: ProudCity
Author URI: http://proudcity.com/
License: Affero GPL v3
 **/
// @todo: use CMB2: https://github.com/WebDevStudios/CMB2 or https://github.com/humanmade/Custom-Meta-Boxes

namespace Proud\Proud_Topic;

// Load Extendible
// -----------------------
if (! class_exists('ProudPlugin')) {
    require_once(plugin_dir_path(__FILE__) . '../wp-proud-core/proud-plugin.class.php');
}

// We need the pagebuilder file for the default pagebuilder layout
// @todo: Make this WORK!!
// @todo: dont make this required, gracefully degrade
//require_once( plugin_dir_path(__FILE__) . '../wp-proud-core/modules/so-pagebuilder/proud-so-pagebuilder.php' );

class Proud_Topic extends \ProudPlugin
{
    public static $key = 'topic_edit';

    public function __construct()
    {
        parent::__construct(array(
            'textdomain'     => 'wp-proud-topic',
            'plugin_path'    => __FILE__,
        ));

        add_action('init', array( $this, 'create_topic' ));
        add_filter('option_siteorigin_panels_settings', array( $this, 'enable_panels_for_topic' ));

        $this->hook('admin_enqueue_scripts', 'topic_assets');
        $this->hook('wp_enqueue_scripts', 'enqueue_frontend_assets');
        $this->hook('plugins_loaded', 'topic_init_widgets');
        $this->hook('rest_api_init', 'topic_rest_support');
        $this->hook('before_delete_post', 'delete_topic_menu');
    }

    /**
     * Ensure the proud-topic post type is included in SiteOrigin Page Builder's
     * enabled post types, regardless of what is saved in the database settings.
     *
     * Filters the raw option value before SiteOrigin processes it, so the
     * Page Builder meta box appears on proud-topic edit screens without
     * requiring manual configuration in the SiteOrigin admin settings panel.
     *
     * @since 2026.03.17
     *
     * @filter option_siteorigin_panels_settings
     *
     * @param  array $settings The SiteOrigin panels settings array from the database.
     * @return array           The settings array with proud-topic appended to post-types.
     */
    public function enable_panels_for_topic( $settings ) {
        if ( ! isset( $settings['post-types'] ) ) {
            $settings['post-types'] = array( 'page', 'post' );
        }
        if ( ! in_array( 'proud-topic', $settings['post-types'] ) ) {
            $settings['post-types'][] = 'proud-topic';
        }
        return $settings;
    }

    //add assets
    public function topic_assets()
    {
        $path = plugins_url('assets/', __FILE__);
        wp_enqueue_script('proud-topic/js', $path . 'js/proud-topic.js', ['proud', 'jquery'], null, true);
    }

    //add assets
    public function enqueue_frontend_assets()
    {
        $path = plugins_url('assets/', __FILE__);
        wp_enqueue_style('proud-topic/css', $path . 'css/proud-topic-styles.css', '', '1.0');
    }

    // Init on plugins loaded
    public function topic_init_widgets()
    {
        if (class_exists('ProudMetaBox')) {
            require_once plugin_dir_path(__FILE__) . '/widgets/topic-contact-widget.class.php';
            require_once plugin_dir_path(__FILE__) . '/widgets/topic-menu-widget.class.php';

            // @TODO these were from the Agency plugin and can probably be deleted
            //require_once plugin_dir_path(__FILE__) . '/widgets/custom-contact-widget.class.php';
            //require_once plugin_dir_path(__FILE__) . '/widgets/topic-hours-widget.class.php';
            //require_once plugin_dir_path(__FILE__) . '/widgets/topic-social-links-widget.class.php';
        }
    }

    public function create_topic()
    {
        $labels = array(
            'name'               => _x('Topics', 'post name', 'wp-proud-topic'),
            'singular_name'      => _x('Topic', 'post type singular name', 'wp-proud-topic'),
            'menu_name'          => _x('Topics', 'admin menu', 'wp-proud-topic'),
            'name_admin_bar'     => _x('Topic', 'add new on admin bar', 'wp-proud-topic'),
            'add_new'            => _x('Add New', 'agency', 'wp-proud-topic'),
            'add_new_item'       => __('Add New Topic', 'wp-proud-topic'),
            'new_item'           => __('New Topic', 'wp-proud-topic'),
            'edit_item'          => __('Edit Topic', 'wp-proud-topic'),
            'view_item'          => __('View Topic', 'wp-proud-topic'),
            'all_items'          => __('All topics', 'wp-proud-topic'),
            'search_items'       => __('Search agency', 'wp-proud-topic'),
            'parent_item_colon'  => __('Parent agency:', 'wp-proud-topic'),
            'not_found'          => __('No topics found.', 'wp-proud-topic'),
            'not_found_in_trash' => __('No topics found in Trash.', 'wp-proud-topic')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Description.', 'wp-proud-topic'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => _x('topics', 'slug', 'wp-proud-topic')),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'         => 'dashicons-megaphone',
            'show_in_rest'       => true,
            'rest_base'          => 'topics',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt')
        );

        register_post_type('proud-topic', $args);
    }

    public function topic_rest_support()
    {
        register_rest_field(
            'agency',
            'meta',
            array(
                'get_callback'    => 'topic_rest_metadata',
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }

    /**
     * Delete menu when agency is deleted.
     */
    public function delete_topic_menu($post_id)
    {
        $menu = get_post_meta($post_id, 'post_menu');
        wp_delete_nav_menu($menu);
    }

    /**
     * Alter the REST endpoint.
     * Add metadata to the post response
     */
    public function topic_rest_metadata($object, $field_name, $request)
    {
        $Contact = new Proud_TopicContact();
        $return = $Contact->get_options($object['id']);
        $Social = new Proud_TopicSocial();
        $return['social'] = $Social->get_options($object['id']);
        return $return;
    }
} // class
$Proud_Topic = new Proud_Topic();
