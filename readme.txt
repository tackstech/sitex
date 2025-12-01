=== BotsMap - XML Sitemap Manager ===
Contributors: Bhumika Goel
Tags: sitemap, visibility, SEO, admin
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

BotsMap - XML Sitemap Manager is your ultimate tool to control how search bots can crawl your website.

== Description ==
Control which sections of your WordPress sitemap are visible to search engines. 
Toggle posts, pages, users, categories, and tags from a simple admin panel. 
This plugin also allows site owners to selectively exclude posts and pages from WordPress's native XML sitemap (introduced in WordPress 5.5+). It adds a simple checkbox to the post and page editor. When checked, the content will be omitted from the default sitemap at `/wp-sitemap.xml`.

Perfect for excluding sensitive, temporary, or low-value content from search engine indexing — without relying on third-party SEO plugins.


= Docs and support =

You can find [docs](https://www.wpvedam.com/botsmap/), [FAQ](https://www.wpvedam.com/botsmap).

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/sitex` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings → BotsMap. Toggle the options to include or exclude item from sitemaps list.
4. Edit any specific post or page and check the "Exclude from Sitemap" box to omit it from the sitemap.



== Frequently Asked Questions ==

1. Does this work with custom post types? 
Not yet, but support can be added easily. Reach out via the support forum if you'd like help extending it.

2. Does this affect Yoast or Rank Math sitemaps? 
No. This plugin only modifies WordPress's native sitemap. For third-party SEO plugins, use their built-in filters.

3. Where is the sitemap located? 
WordPress's default sitemap is available at `/wp-sitemap.xml`.

== Changelog ==
= 1.0 =
* Initial release.

= 1.2 =
* Update

= 1.3 =
* Update

= 1.4 =
* Added specific post or page exclusion field.

= 2.0 =
* Changed Plugin name from Sitex to BotsMap - XML Sitemap Manager. All the files updated.

= 2.1 =
* Improved code logic with conditional filtering of post types from sitemap.

= 2.2 =
* Sanitization and Escaping functions Improved

== Screenshots ==

1. Meta box in post editor
2. Sitemap with excluded content removed
