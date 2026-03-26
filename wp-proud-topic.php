<?php

/**
Plugin Name: Proud Topic
Plugin URI: http://proudcity.com/
Description: Declares a Topic custom post type.
Version: 2026.03.25.1702
Author: ProudCity
Author URI: http://proudcity.com/
License: Affero GPL v3
 **/

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
        add_filter('siteorigin_panels_settings', array( $this, 'load_on_attach_for_new_topic' ));
        add_action('wp_insert_post', array( $this, 'set_default_panels_data' ), 10, 3);

        $this->hook('admin_enqueue_scripts', 'topic_assets');
        $this->hook('wp_enqueue_scripts', 'enqueue_frontend_assets');
        $this->hook('plugins_loaded', 'topic_init_widgets');
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

    /**
     * Save default SiteOrigin panels layout to post meta when a new proud-topic
     * auto-draft is created. SiteOrigin reads from post meta to populate its
     * hidden data field in PHP, so this ensures the template is present before
     * SiteOrigin's JS reads the field value during initialization.
     *
     * @action wp_insert_post
     */
    public function set_default_panels_data( $post_id, $post, $update ) {
        if ( $post->post_type !== 'proud-topic' ) {
            return;
        }
        if ( $post->post_status === 'auto-draft' && empty( get_post_meta( $post_id, 'panels_data', true ) ) ) {
            update_post_meta( $post_id, 'panels_data', json_decode( topic_pagebuilder_code( 'page' ), true ) );
        }
    }

    /**
     * Auto-switch to the SiteOrigin Page Builder tab when creating a new topic.
     *
     * @filter siteorigin_panels_settings
     */
    public function load_on_attach_for_new_topic( $settings ) {
        global $pagenow;
        if ( $pagenow === 'post-new.php' &&
             isset( $_GET['post_type'] ) &&
             $_GET['post_type'] === 'proud-topic' ) {
            $settings['load-on-attach'] = true;
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

    /**
     * Delete menu when agency is deleted.
     */
    public function delete_topic_menu($post_id)
    {
        $menu = get_post_meta($post_id, 'post_menu');
        wp_delete_nav_menu($menu);
    }

    /**
     * Fired when plugin is activated
     *
     * @param   bool    $network_wide   TRUE if WPMU 'super admin' uses Network Activate option
     */
    public function activate($network_wide)
    {
        $this->create_topic();
        flush_rewrite_rules();
    } // activate

} // class
$Proud_Topic = new Proud_Topic();


register_activation_hook(__FILE__, array( $Proud_Topic, 'activate' ));

// Agency meta box
if (class_exists('ProudMetaBox')) {
    class TopicSection extends \ProudMetaBox
    {
        public $options = [  // Meta options, key => default
            'agency_type' => 'page',
            'url' => '',
            'post_menu' => 'new',
            'agency_icon' => '',
            'list_exclude' => ''
        ];

        public function __construct()
        {
            parent::__construct(
                'topic_section', // key
                'Topic type', // title
                'proud-topic', // screen
                'normal',  // position
                'high' // priority
            );
        }

        /**
         * Override parent: skip add_meta_box and render above the editor instead
         */
        public function register_box()
        {
            $this->set_fields(false);
            $this->form = new \Proud\Core\FormHelper($this->key, $this->fields, 1, 'form');
            add_action('edit_form_after_title', [$this, 'render_above_editor'], 1);
        }

        /**
         * Renders the meta box above the TinyMCE / page builder area
         */
        public function render_above_editor($post)
        {
            if ($post->post_type !== 'proud-topic') {
                return;
            }
            echo '<div class="postbox" id="topic_section_meta_box" style="margin-top:12px">';
            echo '<h2 class="hndle"><span>' . esc_html__('Topic type', 'wp-proud-topic') . '</span></h2>';
            echo '<div class="inside">';
            $this->settings_content($post);
            echo '</div></div>';
        }

        /**
         * Called on form creation
         * @param $displaying : false if just building form, true if about to display
         * Use displaying:true to do any difficult loading that should only occur when
         * the form actually will display
         */
        public function set_fields($displaying)
        {

            // Already set
            if ($displaying) {

                // Build menu options
                $menus = get_registered_nav_menus();
                $menus = get_terms('nav_menu', array('hide_empty' => false));
                global $menuArray;
                $menuArray = array(
                    '' => 'No menu',
                    'new' => 'Create new menu',
                );
                foreach ($menus as $menu) {
                    $menuArray[$menu->slug] = $menu->name;
                }
                $this->fields['post_menu']['#options'] = $menuArray;
                return;
            }

            $this->fields = [];

            $this->fields['post_menu'] = [
                '#type' => 'select',
                '#title' => __('Menu'),
                '#options' => [],
                '#description' => __('If you update the menu you need to change the Submenu to match', 'wp-proud-topic'),
            ];

            $this->fields['topic_icon'] = [
                '#type' => 'fa-icon',
                '#title' => __('Icon'),
                '#description' => __('If you are using the Icon Button list style, select an icon'),
            ];

        }

        /**
         * Displays the Agency Type metadata fieldset.
         */
        public function settings_content($post)
        {
            // Call parent
            parent::settings_content($post);
            global $proudcore;
            $proudcore->addJsSettings([
                'proud_topic' => [
                    'isNewPost'    => empty($post->post_title),
                    'topic_panels' => topic_pagebuilder_code('page'),
                ]
            ]);
        }

        /**
         * Saves form values
         * OVERRIDEN from parent for additional processing
         */
        public function save_meta($post_id, $post, $update)
        {
            $values = $this->validate_values($post);
            if (empty($values)) {
                return;
            }

            $menu = isset($values['post_menu']) ? $values['post_menu'] : '';
            if ('new' === $menu) {
                $menuId = wp_create_nav_menu($post->post_title);
                $objMenu = get_term_by('id', $menuId, 'nav_menu');
                $menu = $objMenu->slug;
            }
            if (!empty($menu) && !is_array($menu)) {
                update_post_meta($post_id, 'post_menu', $menu);
            } elseif ('' === $menu) {
                delete_post_meta($post_id, 'post_menu');
            }

            if (!empty($values['topic_icon'])) {
                update_post_meta($post_id, 'topic_icon', $values['topic_icon']);
            }
        }
    }

    if (is_admin()) {
        new TopicSection();
    }
}

/**
 * Returns the default SiteOrigin pagebuilder layout for a topic.
 * Reads from test-topic.json in the plugin directory.
 */
function topic_pagebuilder_code($type)
{
    $json = file_get_contents( plugin_dir_path( __FILE__ ) . 'topic-template.json' );
    if ( $json !== false ) {
        return $json;
    }

    // Fallback if file is missing
    if ($type === 'section') {
        $code = array(
            'name' => __('Topic home page', 'proud'),
            'description' => __('Topic header and sidebar with contact info', 'proud'),    // Optional
            'widgets' =>
            array(
                0 =>
                array(
                    'text' => '<h1>[title]</h1>',
                    'headertype' => 'header',
                    'background' => 'image',
                    'pattern' => '',
                    'repeat' => 'full',
                    'image' => '[featured-image]',
                    'make_inverse' => 'make_inverse',
                    'panels_info' =>
                    array(
                        'class' => 'JumbotronHeader',
                        'grid' => 0,
                        'cell' => 0,
                        'id' => 0,
                    ),
                ),
                1 =>
                array(
                    'title' => '',
                    'panels_info' =>
                    array(
                        'class' => 'TopicMenu',
                        'raw' => false,
                        'grid' => 1,
                        'cell' => 0,
                        'id' => 1,
                    ),
                ),
                5 =>
                array(
                    'title' => '',
                    'text' => '',
                    'text_selected_editor' => 'tinymce',
                    'autop' => true,
                    '_sow_form_id' => '56ab38067a600',
                    'panels_info' =>
                    array(
                        'class' => 'SiteOrigin_Widget_Editor_Widget',
                        'grid' => 1,
                        'cell' => 1,
                        'id' => 5,
                        'style' =>
                        array(
                            'background_image_attachment' => false,
                            'background_display' => 'tile',
                        ),
                    ),
                ),
            ),
            'grids' =>
            array(
                0 =>
                array(
                    'cells' => 1,
                    'style' =>
                    array(
                        'row_stretch' => 'full',
                        'background_display' => 'tile',
                    ),
                ),
                1 =>
                array(
                    'cells' => 2,
                    'style' =>
                    array(),
                ),
            ),
            'grid_cells' =>
            array(
                0 =>
                array(
                    'grid' => 0,
                    'weight' => 1,
                ),
                1 =>
                array(
                    'grid' => 1,
                    'weight' => 0.33345145287029998,
                ),
                2 =>
                array(
                    'grid' => 1,
                    'weight' => 0.66654854712970002,
                ),
            ),
        );
    } else {
        $code = array(
            'name' => __('Topic home page', 'proud'),
            'description' => __('Topic header and sidebar with contact info', 'proud'),    // Optional
            'widgets' =>
            array(
                0 =>
                array(
                    'text' => '<h1>[title]</h1>',
                    'headertype' => 'header',
                    'background' => 'image',
                    'pattern' => '',
                    'repeat' => 'full',
                    'image' => '[featured-image]',
                    'make_inverse' => 'make_inverse',
                    'panels_info' =>
                    array(
                        'class' => 'JumbotronHeader',
                        'grid' => 0,
                        'cell' => 0,
                        'id' => 0,
                    ),
                ),
                4 =>
                array(
                    'title' => '',
                    'text' => '',
                    'text_selected_editor' => 'tinymce',
                    'autop' => true,
                    '_sow_form_id' => '56ab38067a600',
                    'panels_info' =>
                    array(
                        'class' => 'SiteOrigin_Widget_Editor_Widget',
                        'grid' => 1,
                        'cell' => 1,
                        'id' => 5,
                        'style' =>
                        array(
                            'background_image_attachment' => false,
                            'background_display' => 'tile',
                        ),
                    ),
                ),
            ),
            'grids' =>
            array(
                0 =>
                array(
                    'cells' => 1,
                    'style' =>
                    array(
                        'row_stretch' => 'full',
                        'background_display' => 'tile',
                    ),
                ),
                1 =>
                array(
                    'cells' => 2,
                    'style' =>
                    array(),
                ),
            ),
            'grid_cells' =>
            array(
                0 =>
                array(
                    'grid' => 0,
                    'weight' => 1,
                ),
                1 =>
                array(
                    'grid' => 1,
                    'weight' => 0.33345145287029998,
                ),
                2 =>
                array(
                    'grid' => 1,
                    'weight' => 0.66654854712970002,
                ),
            ),
        );
    }
    return json_encode( $code );
}
