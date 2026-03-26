## 2026-03-26

### Fix undefined $key property warning in TopicSection
References: https://github.com/proudcity/wp-proudcity/issues/2665

- `wp-proud-topic.php` — fixed `TopicSection::register_box()` to use literal `'topic_section'` instead of `$this->key`; parent `ProudMetaBox::$key` is `private` and inaccessible from child classes

## 2026-03-26

### Auto-register Topic Menu widget on plugin activation
References: https://github.com/proudcity/wp-proudcity/issues/2665

- `wp-proud-topic.php` — added `add_topic_menu_to_sidebar()` private method; called from `activate()` to automatically place the TopicMenu widget into the `sidebar-proud-topic` sidebar on plugin activation, skipping if already present

## 2026-03-25

### Topic subpage sidebar/breadcrumb support
References: https://github.com/proudcity/wp-proudcity/issues/2665

- `widgets/topic-menu-widget.class.php` — fixed post type check from `'agency'` to `'proud-topic'`; corrected widget description copy-paste from agency widget
- `wp-proud-topic.php` — fixed `TopicSection::save_meta()`: removed dead `topic_type` conditional (copied from agency meta box) that prevented `post_menu` from ever being saved; now directly saves `post_menu` and `topic_icon` on every save
- `wp-proud-topic.php` — moved `TopicSection` meta box above the TinyMCE/page builder area: overrides `register_box()` to use `edit_form_after_title` at priority 1 instead of `add_meta_box`, so the Topic type fields render between the post title and the editor
- `wp-proud-topic.php` — added helper text to Menu field: "If you update the menu you need to change the Submenu to match"
