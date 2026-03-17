<?php

use Proud\Core;

class ProudTopicCustomContact extends Core\ProudWidget
{
    public function __construct()
    {
        parent::__construct(
            'custom_contact', // Base ID
            __('Custom contact block', 'wp-proud-topic'), // Name
            array( 'description' => __("Display custom contact information in a sidebar", 'wp-proud-topic'), ) // Args
        );
    }


    /**
     * Define shortcode settings.
     *
     * @return  void
     */
    public function initialize()
    {
        $this->settings += Proud\Topic\TopicContact::get_fields(false);
        $this->settings['social_title'] = array(
          '#type' => 'html',
          '#html' => '<h3>Social Media Networks</h3>'
        );
        $this->settings += Proud\Topic\TopicSocial::get_fields(false);
    }

    // This is required by TopicSocial::set_fields()
    public function topic_social_services()
    {
        return Proud\Topic\topic_social_services();
    }


    /**
     * Determines if content empty, show widget, title ect?
     *
     * @see self::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function hasContent($args, &$instance)
    {

        foreach (Proud\Topic\topic_social_services() as $service => $label) {
            if (in_array('social_'.esc_attr($service), $instance)) {
                $url = esc_html($instance['social_'.$service]);
                $instance['social'][$service] = $url;
            }
        }

        return !empty($instance['name'])
            || !empty($instance['email'])
            || !empty($instance['phone'])
            || !empty($instance['address'])
            || !empty($instance['hours'])
            || !empty($instance['social']);
    }


    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function printWidget($args, $instance)
    {
        extract($instance);
        include(plugin_dir_path(__FILE__) . 'templates/topic-contact.php');
    }

}

// register Foo_Widget widget
function register_custom_topic_contact_widget()
{
    register_widget('ProudTopicCustomContact');
}
add_action('widgets_init', 'register_custom_topic_contact_widget');
