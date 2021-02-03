ALTER TABLE `#__template_styles` CHANGE `params` `params` MEDIUMTEXT;

DROP TABLE IF EXISTS `#__gridbox_pages`;
CREATE TABLE `#__gridbox_pages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `theme` varchar(255) NOT NULL,
    `meta_title` varchar(255) NOT NULL,
    `meta_description` text NOT NULL,
    `meta_keywords` text NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `params` mediumtext NOT NULL,
    `style` mediumtext NOT NULL,
    `fonts` text NOT NULL,
    `intro_image` varchar(255) NOT NULL,
    `page_alias` varchar(255) NOT NULL,
    `page_category` varchar(255) NOT NULL,
    `page_access` int(11) NOT NULL DEFAULT 1,
    `intro_text` mediumtext NOT NULL,
    `image_alt` varchar(255) NOT NULL,
    `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `end_publishing` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `hits` int(11) NOT NULL DEFAULT 0,
    `language` varchar(255) NOT NULL DEFAULT '*',
    `app_id` int(11) NOT NULL DEFAULT 0,
    `saved_time` varchar(255) NOT NULL DEFAULT '',
    `class_suffix` varchar(255) NOT NULL DEFAULT '',
    `order_list` int(11) NOT NULL DEFAULT 0,
    `featured` int(11) NOT NULL DEFAULT 0,
    `root_order_list` int(11) NOT NULL DEFAULT 0,
    `robots` varchar(255) NOT NULL DEFAULT '',
    `share_image` varchar(255) NOT NULL DEFAULT 'share_image',
    `share_title` varchar(255) NOT NULL,
    `share_description` text NOT NULL,
    `sitemap_include` int(11) NOT NULL DEFAULT 1,
    `changefreq` varchar(255) NOT NULL DEFAULT 'monthly',
    `priority` varchar(255) NOT NULL DEFAULT '0.5',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_app`;
CREATE TABLE `#__gridbox_app` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(255) NOT NULL,
    `theme` int(11) NOT NULL,
    `type` varchar(255) NOT NULL,
    `page_layout` mediumtext NOT NULL,
    `page_items` mediumtext NOT NULL,
    `page_fonts` text NOT NULL,
    `app_fonts` text NOT NULL,
    `app_layout` mediumtext NOT NULL,
    `app_items` mediumtext NOT NULL,
    `saved_time` varchar(255) NOT NULL DEFAULT '',
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `access` tinyint(1) NOT NULL DEFAULT 1,
    `language` varchar(255) NOT NULL DEFAULT '*',
    `image` varchar(255) NOT NULL,
    `meta_title` varchar(255) NOT NULL,
    `meta_description` text NOT NULL,
    `meta_keywords` text NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 1,
    `post_editor_wrapper` text NOT NULL,
    `description` text NOT NULL,
    `robots` varchar(255) NOT NULL DEFAULT '',
    `fields_groups` text NOT NULL,
    `share_image` varchar(255) NOT NULL DEFAULT 'share_image',
    `share_title` varchar(255) NOT NULL,
    `share_description` text NOT NULL,
    `sitemap_include` int(11) NOT NULL DEFAULT 1,
    `changefreq` varchar(255) NOT NULL DEFAULT 'monthly',
    `priority` varchar(255) NOT NULL DEFAULT '0.5',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_fonts`;
CREATE TABLE `#__gridbox_fonts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `font` varchar(255) NOT NULL,
    `styles` varchar(255) NOT NULL,
    `custom_src` text NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_plugins`;
CREATE TABLE `#__gridbox_plugins` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `joomla_constant` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_page_blocks`;
CREATE TABLE `#__gridbox_page_blocks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `item` mediumtext NOT NULL,
    `image` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_library`;
CREATE TABLE `#__gridbox_library` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `item` mediumtext NOT NULL,
    `type` varchar(255) NOT NULL DEFAULT 'section',
    `global_item` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_categories`;
CREATE TABLE `#__gridbox_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(255) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `access` tinyint(1) NOT NULL DEFAULT 1,
    `app_id` int(11) NOT NULL,
    `language` varchar(255) NOT NULL DEFAULT '*',
    `description` text NOT NULL,
    `image` varchar(255) NOT NULL,
    `meta_title` varchar(255) NOT NULL,
    `meta_description` text NOT NULL,
    `meta_keywords` text NOT NULL,
    `parent` int(11) NOT NULL DEFAULT 0,
    `order_list` int(11) NOT NULL DEFAULT 1,
    `robots` varchar(255) NOT NULL DEFAULT '',
    `share_image` varchar(255) NOT NULL DEFAULT 'share_image',
    `share_title` varchar(255) NOT NULL,
    `share_description` text NOT NULL,
    `sitemap_include` int(11) NOT NULL DEFAULT 1,
    `changefreq` varchar(255) NOT NULL DEFAULT 'monthly',
    `priority` varchar(255) NOT NULL DEFAULT '0.5',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_tags`;
CREATE TABLE `#__gridbox_tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(255) NOT NULL,
    `hits` int(11) NOT NULL DEFAULT 0,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `access` tinyint(1) NOT NULL DEFAULT 1,
    `language` varchar(255) NOT NULL DEFAULT '*',
    `description` text NOT NULL,
    `image` varchar(255) NOT NULL,
    `meta_title` varchar(255) NOT NULL,
    `meta_description` text NOT NULL,
    `meta_keywords` text NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 0,
    `robots` varchar(255) NOT NULL DEFAULT '',
    `share_image` varchar(255) NOT NULL DEFAULT 'share_image',
    `share_title` varchar(255) NOT NULL,
    `share_description` text NOT NULL,
    `sitemap_include` int(11) NOT NULL DEFAULT 1,
    `changefreq` varchar(255) NOT NULL DEFAULT 'monthly',
    `priority` varchar(255) NOT NULL DEFAULT '0.5',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_authors`;
CREATE TABLE `#__gridbox_authors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(255) NOT NULL,
    `hits` int(11) NOT NULL DEFAULT 0,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `avatar` varchar(255) NOT NULL DEFAULT '',
    `description` text NOT NULL,
    `image` varchar(255) NOT NULL,
    `meta_title` varchar(255) NOT NULL,
    `meta_description` text NOT NULL,
    `meta_keywords` text NOT NULL,
    `user_id` int(11) NOT NULL DEFAULT 0,
    `order_list` int(11) NOT NULL DEFAULT 0,
    `author_social` text NOT NULL,
    `robots` varchar(255) NOT NULL DEFAULT '',
    `share_image` varchar(255) NOT NULL DEFAULT 'share_image',
    `share_title` varchar(255) NOT NULL,
    `share_description` text NOT NULL,
    `sitemap_include` int(11) NOT NULL DEFAULT 1,
    `changefreq` varchar(255) NOT NULL DEFAULT 'monthly',
    `priority` varchar(255) NOT NULL DEFAULT '0.5',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_authors_map`;
CREATE TABLE `#__gridbox_authors_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `author_id` int(11) NOT NULL DEFAULT 0,
    `page_id` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_tags_map`;
CREATE TABLE `#__gridbox_tags_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `tag_id` int(11) NOT NULL,
    `page_id` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_api`;
CREATE TABLE `#__gridbox_api` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `service` varchar(255) NOT NULL,
    `key` text NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_website`;
CREATE TABLE `#__gridbox_website` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `favicon` varchar(255) NOT NULL,
    `header_code` mediumtext NOT NULL,
    `body_code` mediumtext NOT NULL,
    `enable_autosave` varchar(255) NOT NULL DEFAULT "false",
    `autosave_delay` varchar(255) NOT NULL DEFAULT "10",
    `breakpoints` text NOT NULL,
    `date_format` varchar(255) NOT NULL DEFAULT "j F Y",
    `container` varchar(255) NOT NULL DEFAULT '1170',
    `disable_responsive` tinyint(1) NOT NULL DEFAULT 0,
    `compress_html` tinyint(1) NOT NULL DEFAULT 0,
    `compress_css` tinyint(1) NOT NULL DEFAULT 0,
    `compress_js` tinyint(1) NOT NULL DEFAULT 0,
    `compress_images` tinyint(1) NOT NULL DEFAULT 0,
    `images_max_size` varchar(255) NOT NULL DEFAULT '1440',
    `images_quality` varchar(255) NOT NULL DEFAULT '60',
    `compress_images_webp` tinyint(1) NOT NULL DEFAULT 0,
    `page_cache` tinyint(1) NOT NULL DEFAULT 0,
    `browser_cache` tinyint(1) NOT NULL DEFAULT 0,
    `images_lazy_load` tinyint(1) NOT NULL DEFAULT 0,
    `adaptive_images` tinyint(1) NOT NULL DEFAULT 0,
    `adaptive_quality` varchar(255) NOT NULL DEFAULT '60',
    `adaptive_images_webp` tinyint(1) NOT NULL DEFAULT 0,
    `preloader` tinyint(1) NOT NULL DEFAULT 0,
    `currency_code` varchar(255) NOT NULL DEFAULT "USD",
    `enable_canonical` tinyint(1) NOT NULL DEFAULT 0,
    `canonical_domain` varchar(255) NOT NULL,
    `enable_sitemap` tinyint(1) NOT NULL DEFAULT 0,
    `sitemap_domain` varchar(255) NOT NULL,
    `sitemap_frequency` varchar(255) NOT NULL DEFAULT 'never',
    `image_path` varchar(255) NOT NULL DEFAULT 'images',
    `file_types` varchar(255) NOT NULL DEFAULT 'csv, doc, gif, ico, jpg, jpeg, pdf, png, txt, xls, svg, mp4, webp',
    `email_encryption` tinyint(1) NOT NULL DEFAULT 0,
    `enable_attachment` tinyint(1) NOT NULL DEFAULT 1,
    `attachment_size` int(11) NOT NULL DEFAULT 1024,
    `attachment_types` varchar(255) NOT NULL DEFAULT 'csv, doc, gif, ico, jpg, jpeg, pdf, png, txt, xls, svg, mp4, webp',
    `enable_gravatar` tinyint(1) NOT NULL DEFAULT 1,
    `comments_premoderation` tinyint(1) NOT NULL DEFAULT 0,
    `ip_tracking` tinyint(1) NOT NULL DEFAULT 0,
    `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
    `user_notifications` tinyint(1) NOT NULL DEFAULT 1,
    `comments_recaptcha` varchar(255) NOT NULL,
    `comments_recaptcha_guests` tinyint(1) NOT NULL DEFAULT 0,
    `comments_block_links` tinyint(1) NOT NULL DEFAULT 0,
    `comments_auto_deleting_spam` tinyint(1) NOT NULL DEFAULT 0,
    `comments_facebook_login` tinyint(1) NOT NULL DEFAULT 0,
    `comments_facebook_login_key` varchar(255) NOT NULL,
    `comments_google_login` tinyint(1) NOT NULL DEFAULT 0,
    `comments_google_login_key` varchar(255) NOT NULL,
    `comments_vk_login` tinyint(1) NOT NULL DEFAULT 0,
    `comments_vk_login_key` varchar(255) NOT NULL,
    `comments_moderator_label` varchar(255) NOT NULL DEFAULT 'Moderator',
    `comments_moderator_admins` varchar(255) NOT NULL DEFAULT 'super_user',
    `reviews_enable_attachment` tinyint(1) NOT NULL DEFAULT 1,
    `reviews_attachment_size` int(11) NOT NULL DEFAULT 1024,
    `reviews_enable_gravatar` tinyint(1) NOT NULL DEFAULT 1,
    `reviews_premoderation` tinyint(1) NOT NULL DEFAULT 0,
    `reviews_ip_tracking` tinyint(1) NOT NULL DEFAULT 0,
    `reviews_email_notifications` tinyint(1) NOT NULL DEFAULT 1,
    `reviews_admin_emails` varchar(255) NOT NULL,
    `reviews_user_notifications` tinyint(1) NOT NULL DEFAULT 1,
    `reviews_recaptcha` varchar(255) NOT NULL,
    `reviews_recaptcha_guests` tinyint(1) NOT NULL DEFAULT 0,
    `reviews_block_links` tinyint(1) NOT NULL DEFAULT 0,
    `reviews_auto_deleting_spam` tinyint(1) NOT NULL DEFAULT 0,
    `reviews_facebook_login` tinyint(1) NOT NULL DEFAULT 0,
    `reviews_facebook_login_key` varchar(255) NOT NULL,
    `reviews_google_login` tinyint(1) NOT NULL DEFAULT 0,
    `reviews_google_login_key` varchar(255) NOT NULL,
    `reviews_vk_login` tinyint(1) NOT NULL DEFAULT 0,
    `reviews_vk_login_key` varchar(255) NOT NULL,
    `reviews_moderator_label` varchar(255) NOT NULL DEFAULT 'Moderator',
    `reviews_moderator_admins` varchar(255) NOT NULL DEFAULT 'super_user',
    `sitemap_slash` tinyint(1) NOT NULL DEFAULT 0,
    `defer_loading` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_star_ratings`;
CREATE TABLE IF NOT EXISTS  `#__gridbox_star_ratings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_id` varchar(255) NOT NULL,
    `option` varchar(255) NOT NULL,
    `view` varchar(255) NOT NULL,
    `page_id` varchar(255) NOT NULL,
    `rating` FLOAT NOT NULL,
    `count` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_star_ratings_users`;
CREATE TABLE IF NOT EXISTS  `#__gridbox_star_ratings_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_id` varchar(255) NOT NULL,
    `option` varchar(255) NOT NULL,
    `view` varchar(255) NOT NULL,
    `page_id` varchar(255) NOT NULL,
    `ip` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_filter_state`;
CREATE TABLE `#__gridbox_filter_state` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `value` varchar(255) NOT NULL,
    `user` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_instagram`;
CREATE TABLE `#__gridbox_instagram` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_id` varchar(255) NOT NULL,
    `accessToken` varchar(255) NOT NULL,
    `count` int(11) NOT NULL,
    `images` mediumtext NOT NULL,
    `saved_time` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_weather`;
CREATE TABLE `#__gridbox_weather` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_id` varchar(255) NOT NULL,
    `location` varchar(255) NOT NULL,
    `data` mediumtext NOT NULL,
    `saved_time` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_custom_user_icons`;
CREATE TABLE `#__gridbox_custom_user_icons` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `group` varchar(255) NOT NULL,
    `path` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_system_pages`;
CREATE TABLE `#__gridbox_system_pages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(255) NOT NULL DEFAULT '',
    `type` varchar(255) NOT NULL,
    `theme` varchar(255) NOT NULL,
    `html` mediumtext NOT NULL,
    `items` mediumtext NOT NULL,
    `fonts` text NOT NULL,
    `saved_time` varchar(255) NOT NULL DEFAULT '',
    `order_list` int(11) NOT NULL DEFAULT 0,
    `page_options` mediumtext NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_page_fields`;
CREATE TABLE `#__gridbox_page_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_id` int(11) NOT NULL,
    `field_id` varchar(255) NOT NULL,
    `field_type` varchar(255) NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_fields`;
CREATE TABLE `#__gridbox_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `app_id` int(11) NOT NULL,
    `field_key` varchar(255) NOT NULL,
    `field_type` varchar(255) NOT NULL,
    `label` varchar(255) NOT NULL,
    `required` tinyint(1) NOT NULL,
    `options` text NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_fields_data`;
CREATE TABLE `#__gridbox_fields_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL,
    `field_type` varchar(255) NOT NULL,
    `option_key` varchar(255) NOT NULL,
    `value` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_comments`;
CREATE TABLE `#__gridbox_comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_id` int(11) NOT NULL,
    `parent` int(11) NOT NULL DEFAULT 0,
    `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `status` varchar(255) NOT NULL,
    `ip` varchar(255) NOT NULL,
    `unread` tinyint(1) NOT NULL DEFAULT 1,
    `message` text NOT NULL,
    `email` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `avatar` varchar(255) NOT NULL,
    `likes` int(11) NOT NULL DEFAULT 0,
    `dislikes` int(11) NOT NULL DEFAULT 0,
    `user_id` varchar(255) NOT NULL DEFAULT '0',
    `user_type` varchar(255) NOT NULL DEFAULT 'guest',
    `user_notification` tinyint(1) NOT NULL DEFAULT 0,
    `admin_notification` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_comments_unsubscribed_users`;
CREATE TABLE `#__gridbox_comments_unsubscribed_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_comments_likes_map`;
CREATE TABLE `#__gridbox_comments_likes_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `comment_id` int(11) NOT NULL,
    `ip` varchar(255) NOT NULL,
    `status` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_comments_attachments`;
CREATE TABLE `#__gridbox_comments_attachments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `comment_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `filename` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_comments_banned_emails`;
CREATE TABLE `#__gridbox_comments_banned_emails` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_comments_banned_words`;
CREATE TABLE `#__gridbox_comments_banned_words` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `word` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_comments_banned_ip`;
CREATE TABLE `#__gridbox_comments_banned_ip` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `ip` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_reviews`;
CREATE TABLE `#__gridbox_reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_id` int(11) NOT NULL,
    `parent` int(11) NOT NULL DEFAULT 0,
    `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `status` varchar(255) NOT NULL,
    `ip` varchar(255) NOT NULL,
    `unread` tinyint(1) NOT NULL DEFAULT 1,
    `message` text NOT NULL,
    `email` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `avatar` varchar(255) NOT NULL,
    `likes` int(11) NOT NULL DEFAULT 0,
    `dislikes` int(11) NOT NULL DEFAULT 0,
    `user_id` varchar(255) NOT NULL DEFAULT '0',
    `user_type` varchar(255) NOT NULL DEFAULT 'guest',
    `user_notification` tinyint(1) NOT NULL DEFAULT 0,
    `admin_notification` tinyint(1) NOT NULL DEFAULT 0,
    `rating` FLOAT NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_reviews_unsubscribed_users`;
CREATE TABLE `#__gridbox_reviews_unsubscribed_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_reviews_likes_map`;
CREATE TABLE `#__gridbox_reviews_likes_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `comment_id` int(11) NOT NULL,
    `ip` varchar(255) NOT NULL,
    `status` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_reviews_attachments`;
CREATE TABLE `#__gridbox_reviews_attachments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `comment_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `filename` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_reviews_banned_emails`;
CREATE TABLE `#__gridbox_reviews_banned_emails` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_reviews_banned_words`;
CREATE TABLE `#__gridbox_reviews_banned_words` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `word` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_reviews_banned_ip`;
CREATE TABLE `#__gridbox_reviews_banned_ip` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `ip` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_fields_desktop_files`;
CREATE TABLE `#__gridbox_fields_desktop_files` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_id` int(11) NOT NULL,
    `app_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `filename` varchar(255) NOT NULL,
    `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_payment_methods`;
CREATE TABLE `#__gridbox_store_payment_methods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `settings` text NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_promo_codes`;
CREATE TABLE `#__gridbox_store_promo_codes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `code` varchar(255) NOT NULL DEFAULT '',
    `unit` varchar(255) NOT NULL DEFAULT '%',
    `discount` varchar(255) NOT NULL DEFAULT '',
    `applies_to` varchar(255) NOT NULL DEFAULT '*',
    `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `limit` int(11) NOT NULL DEFAULT 0,
    `used` int(11) NOT NULL DEFAULT 0,
    `disable_sales` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_promo_codes_map`;
CREATE TABLE `#__gridbox_store_promo_codes_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` varchar(255) NOT NULL,
    `code_id` int(11) NOT NULL,
    `item_id` int(11) NOT NULL,
    `variation` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_shipping`;
CREATE TABLE `#__gridbox_store_shipping` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `price` varchar(255) NOT NULL DEFAULT '',
    `free` varchar(255) NOT NULL DEFAULT '',
    `options` text,
    `order_list` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_products_fields`;
CREATE TABLE `#__gridbox_store_products_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_key` varchar(255) NOT NULL,
    `field_type` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `options` text NOT NULL,
    `required` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_products_fields_data`;
CREATE TABLE `#__gridbox_store_products_fields_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL,
    `option_key` varchar(255) NOT NULL,
    `value` varchar(255) NOT NULL,
    `color` varchar(255) NOT NULL DEFAULT '',
    `image` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_product_data`;
CREATE TABLE `#__gridbox_store_product_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `price` varchar(255) NOT NULL,
    `sale_price` varchar(255) NOT NULL,
    `sku` varchar(255) NOT NULL,
    `stock` varchar(255) NOT NULL,
    `variations` text NOT NULL,
    `extra_options` text NOT NULL,
    `product_type` varchar(255),
    `digital_file` text,
    `dimensions` text,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_product_variations_map`;
CREATE TABLE `#__gridbox_store_product_variations_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `field_id` int(11) NOT NULL,
    `option_key` varchar(255) NOT NULL,
    `images` text NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 0,
    `order_group` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_customer_info`;
CREATE TABLE `#__gridbox_store_customer_info` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `required` tinyint(1) NOT NULL DEFAULT 0,
    `invoice` tinyint(1) NOT NULL DEFAULT 0,
    `options` text NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_cart`;
CREATE TABLE `#__gridbox_store_cart` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT 0,
    `promo_id` int(11) NOT NULL DEFAULT 0,
    `country` varchar(255) NOT NULL DEFAULT '',
    `region` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_cart_products`;
CREATE TABLE `#__gridbox_store_cart_products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cart_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `variation` varchar(255) NOT NULL,
    `quantity` int(11) NOT NULL,
    `extra_options` text,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_orders_status_history`;
CREATE TABLE `#__gridbox_store_orders_status_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `date` datetime NOT NULL,
    `status` varchar(255) NOT NULL DEFAULT 'new',
    `comment` text NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_orders`;
CREATE TABLE `#__gridbox_store_orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `date` datetime NOT NULL,
    `cart_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `status` varchar(255) NOT NULL DEFAULT 'new',
    `published` tinyint(1) NOT NULL DEFAULT 0,
    `unread` tinyint(1) NOT NULL DEFAULT 1,
    `order_number` varchar(255) NOT NULL DEFAULT '#00000000',
    `subtotal` varchar(255) NOT NULL,
    `tax` varchar(255) NOT NULL,
    `total` varchar(255) NOT NULL,
    `currency_symbol` varchar(255) NOT NULL,
    `currency_position` varchar(255) NOT NULL,
    `params` text NOT NULL,
    `tax_mode` varchar(255) NOT NULL DEFAULT 'excl',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_orders_discount`;
CREATE TABLE `#__gridbox_store_orders_discount` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `promo_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `code` varchar(255) NOT NULL,
    `unit` varchar(255) NOT NULL DEFAULT '%',
    `discount` varchar(255) NOT NULL DEFAULT '',
    `value` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_orders_shipping`;
CREATE TABLE `#__gridbox_store_orders_shipping` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `cart_id` int(11) NOT NULL DEFAULT 0,
    `shipping_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `price` varchar(255) NOT NULL,
    `tax` varchar(255) NOT NULL,
    `tax_title` varchar(255) NOT NULL DEFAULT '',
    `tax_rate` varchar(255) NOT NULL DEFAULT '',
    `type` varchar(255) NOT NULL DEFAULT 'flat',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_orders_payment`;
CREATE TABLE `#__gridbox_store_orders_payment` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `cart_id` int(11) NOT NULL DEFAULT 0,
    `title` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `payment_id` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_order_customer_info`;
CREATE TABLE `#__gridbox_store_order_customer_info` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `cart_id` int(11) NOT NULL DEFAULT 0,
    `title` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `value` text NOT NULL,
    `invoice` tinyint(1) NOT NULL DEFAULT 0,
    `options` text NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_order_products`;
CREATE TABLE `#__gridbox_store_order_products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    `product_id` int(11) NOT NULL DEFAULT 0,
    `variation` varchar(255) NOT NULL,
    `quantity` int(11) NOT NULL,
    `price` varchar(255) NOT NULL,
    `sale_price` varchar(255) NOT NULL,
    `sku` varchar(255) NOT NULL,
    `tax` varchar(255) NOT NULL DEFAULT '',
    `tax_title` varchar(255) NOT NULL DEFAULT '',
    `tax_rate` varchar(255) NOT NULL DEFAULT '',
    `net_price` varchar(255) NOT NULL DEFAULT '',
    `extra_options` text,
    `product_type` varchar(255),
    `product_token` varchar(255),
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_order_license`;
CREATE TABLE `#__gridbox_store_order_license` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11),
    `order_id` int(11) NOT NULL,
    `downloads` int(11) NOT NULL DEFAULT 0,
    `limit` varchar(255),
    `expires` varchar(255),
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_order_product_variations`;
CREATE TABLE `#__gridbox_store_order_product_variations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `order_id` int(11) NOT NULL,
    `type` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `value` varchar(255) NOT NULL,
    `color` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_user_info`;
CREATE TABLE `#__gridbox_store_user_info` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_wishlist`;
CREATE TABLE `#__gridbox_store_wishlist` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_wishlist_products`;
CREATE TABLE `#__gridbox_store_wishlist_products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `wishlist_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `variation` varchar(255) NOT NULL,
    `extra_options` text,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_badges`;
CREATE TABLE `#__gridbox_store_badges` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `color` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_badges_map`;
CREATE TABLE `#__gridbox_store_badges_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `badge_id` int(11) NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_store_related_products`;
CREATE TABLE `#__gridbox_store_related_products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `related_id` int(11) NOT NULL,
    `order_list` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_countries`;
CREATE TABLE `#__gridbox_countries` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255),
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__gridbox_country_states`;
CREATE TABLE `#__gridbox_country_states` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `country_id` varchar(255),
    `title` varchar(255),
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__gridbox_store_badges` (`title`, `color`, `type`) VALUES
('', '#f64231', 'sale'),
('Sale', '#ff7a2f', ''),
('New', '#34dca2', ''),
('Hot', '#ffc700', '');

INSERT INTO `#__gridbox_store_customer_info` (`title`, `type`, `required`, `options`, `order_list`, `invoice`) VALUES
('First name', 'text', 1, '{"placeholder":"","html":"","options":[],"width":"50"}', 1, 1),
('Contact Information', 'headline', 0, '{"placeholder":"","html":"","options":[],"width":"100"}', 0, 0),
('Last name', 'text', 0, '{"placeholder":"","html":"","options":[],"width":"50"}', 2, 1),
('Phone', 'text', 0, '{"placeholder":"","html":"","options":[],"width":"100"}', 3, 1),
('Email', 'email', 1, '{"placeholder":"","html":"","options":[],"width":"100"}', 4, 1),
('Shipping Address', 'headline', 0, '{"placeholder":"","html":"","options":[],"width":"100"}', 5, 0),
('Address', 'text', 1, '{"placeholder":"","html":"","options":[],"width":"100"}', 6, 1),
('Apartment, suite, etc. (optional)', 'text', 0, '{"placeholder":"","html":"","options":[],"width":"100"}', 7, 1),
('City', 'text', 1, '{"placeholder":"","html":"","options":[],"width":"100"}', 8, 1),
('Country / Region', 'dropdown', 1, '{"placeholder":"Select","html":"","options":["Value","Anguilla","Argentina","Australia","Austria","Bahamas","Barbados","Belgium","Bhutan","Bosnia And Herzegovina","British Virgin Islands","Bulgaria","Canada","Chile","Colombia","Croatia","Czechia","Denmark","Estonia","Finland","France","French Guiana","French Polynesia","Germany","Greece","Greenland","Grenada","Hong Kong SAR China","Hungary","Iceland","India","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Liechtenstein","Lithuania","Luxembourg","Malta","Martinique","Mexico","Moldova","Monaco","Netherlands","New Zealand","Norway","Panama","Paraguay","Poland","Portugal","Qatar","Russia","Saudi Arabia","Singapore","Slovenia","Spain","Saint Barth\u00e9lemy","Saint Kitts And Nevis","St. Lucia","St. Martin","St. Vincent and Grenadines","Sweden","Switzerland","Trinidad and Tobago","U.S. Outlying Islands","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay"],"width":"50"}', 9, 1),
('Zip Code', 'text', 0, '{"placeholder":"","html":"","options":[],"width":"50"}', 10, 1),
('', 'acceptance', 1, '{"placeholder":"","html":"I have read and agree to the <a href=\\"#\\" target=\\"_blank\\">Terms and Conditions<\\/a>","options":[],"width":"100"}', 11, 0);

INSERT INTO `#__gridbox_system_pages`(`title`, `alias`, `type`, `theme`, `order_list`, `page_options`, `html`, `items`, `fonts`) VALUES
('404 Error Page', '', '404', 0, 1, '{"enable_header":false}', '', '', ''),
('Coming Soon Page', '', 'offline', 0, 1, '{}', '', '', ''),
('Search Results Page', 'search', 'search', 0, 1, '{}', '', '', ''),
('Preloader', '', 'preloader', 0, 1, '{}', '', '', ''),
('Checkout Page', 'checkout', 'checkout', 0, 1, '{}', '', '', ''),
('Thank You Page', 'thank-you', 'thank-you-page', 0, 1, '{}', '', '', ''),
('Store Search Results Page', 'store-search', 'store-search', 0, 1, '{}', '', '', '');

INSERT INTO `#__gridbox_website` (`favicon`, `header_code`, `body_code`, `breakpoints`) VALUES
('', '', '', '{"laptop":1440, "tablet":1280,"tablet-portrait":1024,"phone":768,"phone-portrait":420,"menuBreakpoint":1024}');

INSERT INTO `#__gridbox_api` (`service`, `key`) VALUES
('google_maps', ''),
('library_font', ''),
('user_colors', '{"0":"#eb523c","1":"#f65954","2":"#ec821a","3":"#f5c500","4":"#34dca2","5":"#20364c","6":"#32495f","7":"#0075a9","8":"#1996dd","9":"#6cc6fa"}'),
('openweathermap', ''),
('yandex_maps', ''),
('gridbox_sitemap', ''),
('store', '{}');

INSERT INTO `#__gridbox_plugins` (`title`, `image`, `type`, `joomla_constant`) VALUES
('ba-image', 'flaticon-picture', 'content', 'IMAGE'),
('ba-text', 'flaticon-file', 'content', 'TEXT'),
('ba-button', 'plugins-button', 'content', 'BUTTON'),
('ba-logo', 'flaticon-diamond', 'navigation', 'LOGO'),
('ba-menu', 'flaticon-app', 'navigation', 'MENU'),
('ba-modules', 'plugins-modules', '3rd-party-plugins', 'JOOMLA_MODULES'),
('ba-forms', 'plugins-forms', '3rd-party-plugins', 'BALBOOA_FORMS'),
('ba-gallery', 'plugins-gallery', '3rd-party-plugins', 'BALBOOA_GALLERY');

INSERT INTO `#__gridbox_fonts` (`font`, `styles`) VALUES
('Open+Sans', 300),
('Open+Sans', 400),
('Open+Sans', 700),
('Poppins', 300),
('Poppins', 400),
('Poppins', 500),
('Poppins', 600),
('Poppins', 700),
('Roboto', 300),
('Roboto', 400),
('Roboto', 500),
('Roboto', 700),
('Roboto', 900),
('Lato', 300),
('Lato', 400),
('Lato', 700),
('Slabo+27px', 400),
('Oswald', 300),
('Oswald', 400),
('Oswald', 600),
('Roboto+Condensed', 300),
('Roboto+Condensed', 400),
('Roboto+Condensed', 700),
('PT+Sans', 400),
('PT+Sans', 700),
('Montserrat', 200),
('Montserrat', 300),
('Montserrat', 400),
('Montserrat', 700),
('Playfair+Display', 400),
('Playfair+Display', 700),
('Comfortaa', 300),
('Comfortaa', 400),
('Comfortaa', 700);