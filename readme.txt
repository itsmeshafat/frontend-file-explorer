=== Frontend File Explorer Plugin ===
Contributors: itsmeshafat
Donate link: https://itsmeshafat.com
Tags: file manager, file explorer, downloads, frontend, media library, ajax
Requires at least: 5.6
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Frontend File Explorer Plugin is a modern, Windows Explorer–inspired file manager for WordPress. It gives you a clean admin interface to organize and share files plus a responsive frontend explorer powered by a simple shortcode.

Use it to create download areas for courses, client file portals, or resource libraries — without relying on heavy external file management tools.

**Key features**

* Explorer-style UI with breadcrumbs, toolbar actions, pagination, and Material Icons
* Dedicated and secured `wp-content/uploads/downloads` directory
* Admin-side file manager with folder creation, uploads, delete, and ZIP downloads
* Frontend explorer via `[file_explorer]` shortcode
* Direct file links for easy sharing
* AJAX-powered navigation and pagination
* Fully translation-ready (`frontend-file-explorer-plugin` text domain)

**Who is it for?**

* Course creators who need a simple, branded downloads area
* Agencies and freelancers who share files with clients
* Site owners who want a lightweight, Explorer-like file manager in WordPress

== Screenshots ==

1. Admin file explorer with folders, uploads, and toolbar actions (backend).
2. Frontend file explorer embedded via shortcode (frontend view).

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory or install via the WordPress Plugins screen by uploading the ZIP.
2. Activate the plugin through the **Plugins → Installed Plugins** screen in WordPress.
3. On activation, the plugin will create `wp-content/uploads/downloads`. Make sure your hosting environment allows file creation in `wp-content/uploads`.
4. (Optional) Create a page and add the shortcode:

   `[file_explorer folder="/"]`

== Usage ==

= Admin interface =

* Find the menu item **File Upload** in the WordPress admin sidebar.
* From here you can:
  * Create folders
  * Upload files (multi-select support)
  * Copy existing Media Library items into the Explorer
  * Delete files and folders
  * Download a folder as a ZIP archive
* All actions are handled via nonce-protected AJAX endpoints for a smoother UX.

= Frontend explorer =

Embed the explorer anywhere (pages, posts, custom post types) using:

`[file_explorer folder="/"]`

* `folder` (optional) — starting subdirectory relative to `uploads/downloads`. Use `/` for the root.
* Visitors can browse folders, download files, and copy direct links.
* Mutating actions (upload, delete, etc.) remain restricted to logged-in users with the `upload_files` capability.

== Frequently Asked Questions ==

= Can I point the explorer to a different base folder? =

Yes. You can override the constants in a custom mu-plugin before File Explorer loads, or use filters/hooks (e.g. on `wp_loaded`) to adjust the base path/URL. This is an advanced customization and should be done carefully.

= Does the plugin work in multisite? =

Yes. Each site manages its own `uploads/downloads` directory. You can network-activate the plugin for consistency across sites.

= Are file types restricted? =

By default, allowed file types are defined via options during activation. You can adjust the allowed extensions by updating the plugin options (e.g., `file_explorer_allowed_file_types`).

= How do I translate the UI? =

The plugin is fully localization-ready and uses the `frontend-file-explorer-plugin` text domain. You can use tools like Loco Translate or Poedit to create translations and drop `.mo` files in the `languages/` directory.

== Changelog ==

= 1.0.1 =
* Rename plugin to "Frontend File Explorer Plugin"
* Align text domain and translation loading with slug `frontend-file-explorer-plugin`
* Improve README and readme.txt descriptions and screenshots

= 1.0.0 =
* Initial public release
* Admin file explorer with folder creation, uploads, delete, and ZIP download actions
* Frontend shortcode explorer with responsive UI
* AJAX-powered navigation and pagination
* Dedicated secured downloads directory under `wp-content/uploads/downloads`

== Upgrade Notice ==

= 1.0.1 =
Recommended upgrade to reflect new plugin name and text domain for translations.

== Developer ==

Built by **Shafat Mahmud Khan**, WordPress Developer  
Portfolio: https://itsmeshafat.com
