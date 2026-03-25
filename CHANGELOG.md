## 2026-03-25

### Topic subpage sidebar/breadcrumb support
References: https://github.com/proudcity/wp-proudcity/issues/2665

- `widgets/topic-menu-widget.class.php` — fixed post type check from `'agency'` to `'proud-topic'`; corrected widget description copy-paste from agency widget
- `wp-proud-topic.php` — fixed `TopicSection::save_meta()`: removed dead `topic_type` conditional (copied from agency meta box) that prevented `post_menu` from ever being saved; now directly saves `post_menu` and `topic_icon` on every save
