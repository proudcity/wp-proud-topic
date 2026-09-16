<?php

use Proud\Core;

class TopicContact extends Core\ProudWidget {

  function __construct() {
    parent::__construct(
      'topic_contact', // Base ID
      __( 'Topic contact info', 'wp-proud-topic' ), // Name
      array( 'description' => __( "Display current topic's contact info", 'wp-proud-topic' ), ) // Args
    );
  }

  function initialize() {
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
    global $pageInfo;
    $id = get_post_type() === 'proud-topic' ? get_the_ID(): $pageInfo['parent_post'];
    $instance['name'] = get_post_meta( $id, 'name', true );
    $instance['name_title'] = get_post_meta( $id, 'name_title', true );
    $instance['name_link'] = get_post_meta( $id, 'name_link', true );
    // filter_var(FILTER_VALIDATE_EMAIL) accepts RFC quoted local parts, so a
    // payload like '"><img/src/onerror=alert(1)>"@a.com' validates as an email
    // and used to be concatenated into a mailto: and printed unescaped.
    // Mirrors the fix in wp-proud-agency's AgencyContact (#2933).
    $email = sanitize_email( $instance['name_link'] );
    $instance['name_link'] = ( !empty( $email ) && is_email( $email ) )
        ? esc_url( 'mailto:' . $email )
        : esc_url( $instance['name_link'] );
    $instance['email'] = get_post_meta( $id, 'email', true );
    $instance['phone'] = get_post_meta( $id, 'phone', true );
    $instance['fax'] = get_post_meta( $id, 'fax', true );
    $instance['sms'] = get_post_meta( $id, 'sms', true );
    $instance['address'] = get_post_meta( $id, 'address', true );
    $instance['hours'] = get_post_meta( $id, 'hours', true );
    $instance['social'] = [];
    foreach ( Proud\Topic\topic_social_services() as $service => $label ) {
      $url = esc_html( get_post_meta( $id, 'social_'.$service, true ) );
      if ( !empty( $url ) ) {
          $instance['social'][$service] = $url;
      }
    }

    return !empty( $instance['name'] )  
        || !empty( $instance['email'] )
        || !empty( $instance['phone'] )
        || !empty( $instance['address'] )
        || !empty( $instance['hours'] )
        || !empty( $instance['social'] );
  }

  /**
   * Outputs the content of the widget
   *
   * @param array $args
   * @param array $instance
   */
  public function printWidget( $args, $instance ) {
    extract( $instance );
    include(plugin_dir_path( __FILE__ ) . 'templates/topic-contact.php');
  }
}

// register Foo_Widget widget
function register_topic_contact_widget() {
  register_widget( 'TopicContact' );
}
add_action( 'widgets_init', 'register_topic_contact_widget' );
