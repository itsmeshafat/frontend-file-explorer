=== Frontend File Explorer ===
Contributors:      itsmeshafat
Tested up to:      6.8
Stable tag:        1.0.1
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Donate link:       https://itsmeshafat.com
Tags:              file manager, file explorer, downloads, frontend, media library

A modern, Windows Explorer–inspired file manager for WordPress with admin interface and frontend shortcode.

== Description ==

Frontend File Explorer is a modern, Windows Explorer–inspired file manager for WordPress. It gives you a clean admin interface to organize and share files plus a responsive frontend explorer powered by a simple shortcode.

Use it to create download areas for courses, client file portals, or resource libraries — without relying on heavy external file management tools.

The plugin provides a seamless experience for both administrators and frontend users:

* **Explorer-style UI:** Navigate with breadcrumbs, toolbar actions, pagination, and Material Icons.
* **Dedicated Directory:** Files are stored in a secured `wp-content/uploads/downloads` directory.
* **Admin Management:** Create folders, upload files, delete items, and download ZIPs directly from the admin dashboard.
* **Frontend Integration:** Embed the explorer anywhere using the `[file_explorer]` shortcode.
* **AJAX-Powered:** Fast, smooth navigation and pagination without page reloads.
* **Translation Ready:** Fully localized with the `frontend-file-explorer` text domain.

The explorer can be used via the Admin interface or the Frontend shortcode:

* **Admin interface:** Find the menu item **File Upload** in the WordPress admin sidebar. From here you can create folders, upload files (multi-select support), copy existing Media Library items, delete files/folders, and download folders as ZIP archives.
* **Frontend explorer:** Embed the explorer anywhere (pages, posts, custom post types) using the shortcode `[file_explorer folder="/"]`.
    * `folder` (optional) — starting subdirectory relative to `uploads/downloads`. Use `/` for the root.
    * Visitors can browse folders, download files, and copy direct links.
    * Mutating actions (upload, delete, etc.) remain restricted to logged-in users with the `upload_files` capability.

**Who is it for?**

* Course creators who need a simple, branded downloads area.
* Agencies and freelancers who share files with clients.
* Site owners who want a lightweight, Explorer-like file manager in WordPress.

== Installation ==

= Installation from within WordPress =

1. Visit **Plugins > Add New**.
2. Search for **Frontend File Explorer**.
3. Install and activate the Frontend File Explorer plugin.
4. On activation, the plugin will create `wp-content/uploads/downloads`.

= Manual installation =

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the Frontend File Explorer plugin.

== Frequently Asked Questions ==

= Can I point the explorer to a different base folder? =

Yes. You can override the constants in a custom mu-plugin before File Explorer loads, or use filters/hooks (e.g. on `wp_loaded`) to adjust the base path/URL. This is an advanced customization and should be done carefully.

= Does the plugin work in multisite? =

Yes. Each site manages its own `uploads/downloads` directory. You can network-activate the plugin for consistency across sites.

= Are file types restricted? =

By default, allowed file types are defined via options during activation. You can adjust the allowed extensions by updating the plugin options (e.g., `file_explorer_allowed_file_types`).

= How do I translate the UI? =

The plugin is fully localization-ready and uses the `frontend-file-explorer` text domain. You can use tools like Loco Translate or Poedit to create translations and drop `.mo` files in the `languages/` directory.

== Screenshots ==

1. Admin file explorer with folders, uploads, and toolbar actions (backend).
2. Frontend file explorer embedded via shortcode (frontend view).

== Changelog ==

= 1.0.1 =

* Rename plugin to "Frontend File Explorer"
* Align text domain and translation loading with slug `frontend-file-explorer`
* Improve README and readme.txt descriptions and screenshots
