(function($, Proud) {
  Proud.behaviors.proud_topic = {
    attach: function(context, settings) {
      var topic_settings = settings.proud_topic;
      if (!topic_settings || !topic_settings.isNewPost) {
        return;
      }

      function loadPanels(builderView) {
        builderView.model.loadPanelsData(JSON.parse(topic_settings.topic_panels));
      }

      // Listen for panels_setup in case SiteOrigin initializes after us.
      $(document).on('panels_setup', function(e, builderView) {
        loadPanels(builderView);
      });

      // Fallback: if SiteOrigin already initialized before our behavior ran,
      // the global soPanelsBuilderView is available immediately.
      if (window.soPanelsBuilderView) {
        loadPanels(window.soPanelsBuilderView);
      }
    }
  };
})(jQuery, Proud);
