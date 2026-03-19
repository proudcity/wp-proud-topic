(function($, Proud) {
  Proud.behaviors.proud_topic = {
    attach: function(context, settings) {
      // Default panels data is set via PHP (wp_insert_post hook) so SiteOrigin
      // reads it from post meta before its JS initializes. Tab auto-switch is
      // handled by the siteorigin_panels_settings 'load-on-attach' filter.
    }
  };
})(jQuery, Proud);
