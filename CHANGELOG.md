## 2026.11.18.1220

- New topics' Submenu ("In this section") now uses the topic's own menu. The template's hard-coded `homelessness` menu is now empty, and `TopicSection::save_meta()` points Submenu widgets at the topic's menu when they are empty, `homelessness`, or still on the topic's previous menu. Submenus staff pointed at a different menu are left alone.
- A topic title that matches an existing menu name no longer triggers a PHP warning; an admin notice is shown and no menu is set.
- A menu is no longer deleted automatically when a topic is permanently deleted (the old code passed an array to `wp_delete_nav_menu()` and never worked). If the topic created its menu (new protected `_proud_topic_menu_created` meta, checked only on topics; Delete and Keep use separate nonces), an admin notice asks whether to delete it, with a nonce-protected Delete menu link and a Keep menu link. Topics that only selected an existing menu, and topics created before this release, are never prompted.
- Updated the Menu field description to match.

References: https://github.com/proudcity/wp-proudcity/issues/3029

## 2026.09.16.1356

- Fixed a stored XSS in the topic contact widget, a verbatim copy of the one closed in wp-proud-agency (#2933). `TopicContact::hasContent()` validated `name_link` with `filter_var( $v, FILTER_VALIDATE_EMAIL )`, which accepts RFC quoted local parts — so `"><img/src/onerror=alert(1)>"@a.com` passed validation, was concatenated into `"mailto:$v"`, and printed unescaped by `widgets/templates/topic-contact.php`. Now uses `sanitize_email()` + `is_email()`, with `esc_url()` at the template output.
- The phone/email half of that fix carried over automatically: `topic-contact.php` calls `Proud\Agency\AgencyContact::phone_tel_links()` and `::email_mailto_links()`, both hardened in wp-proud-agency this release.

References: https://github.com/proudcity/wp-proudcity/issues/2933

## 2026-04-30

### Defer flush_rewrite_rules to init to fix Topic CPT 404s after activation
References: https://github.com/proudcity/wp-proudcity/issues/2816

- `wp-proud-topic.php` — replaced direct `flush_rewrite_rules()` call in `activate()` with a flag-based deferred flush; added `maybe_flush_rewrite_rules()` hooked to `init` at priority 99 to perform the flush after all CPTs are registered

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
