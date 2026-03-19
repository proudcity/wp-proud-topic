(function($, Proud) {
  Proud.behaviors.proud_topic = {
    attach: function(context, settings) {
      var topic_settings = settings.proud_topic;
      if (!topic_settings || !topic_settings.isNewPost) {
        return;
      }

      var builder;
      $(document).on('panels_setup', function(e, builderView) {
        builder = builderView;
        $('input[name="panels_data"]').val(topic_settings.topic_panels);
        builder.model.loadPanelsData(JSON.parse(topic_settings.topic_panels));
      });

      $('#content-panels').trigger('click');
    }
  };
})(jQuery, Proud);
