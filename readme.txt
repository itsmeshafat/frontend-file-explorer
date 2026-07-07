=== Frontend File Explorer ===
Contributors:      itsmeshafat
Requires at least: 5.6
Requires PHP:      7.4
Tested up to:      7.0
Stable tag:        1.0.8
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Donate link:       https://itsmeshafat.com
Tags:              file manager, files, downloads, frontend, media library

A modern, Windows Explorer–inspired file manager for WordPress with admin interface and frontend shortcode.

== Description ==

Frontend File Explorer is a modern, Windows Explorer–inspired file manager for WordPress. It gives you a clean admin interface to organize and share files plus a responsive frontend explorer powered by a simple shortcode.

Use it to create download areas for courses, client file portals, or resource libraries — without relying on heavy external file management tools.

The plugin provides a seamless experience for both administrators and frontend users:

* **Explorer-style UI:** Navigate with breadcrumbs, toolbar actions, pagination, and Material Icons.
* **Dedicated Directory:** Files are stored in a secured `wp-content/uploads/downloads` directory.
* **Admin Management:** Create folders, upload files, delete items, and download ZIPs directly from the admin dashboard.
* Frontend Integration: Embed the explorer anywhere using the `[frontend_file_explorer]` shortcode.
* **AJAX-Powered:** Fast, smooth navigation and pagination without page reloads.
* **Translation Ready:** Fully localized with the `frontend-file-explorer` text domain.

**How to use the Explorer:**

* **Admin Interface:** 
    Navigate to **File Upload** in your WordPress admin sidebar. From this dedicated dashboard, administrators can create nested folders, upload bulk files (featuring multi-select and drag-and-drop), import existing Media Library assets, delete items, and download entire directories as ZIP archives.

* **Frontend Shortcode:** 
    Embed the user-facing explorer interface on any Page, Post, or Custom Post Type using the following shortcode setup:
    
    `[frontend_file_explorer]`
    *(Renders the explorer starting at the root storage directory)*
    
    **Advanced Shortcode Usage:**
    You can explicitly define the starting folder path relative to the root `uploads/downloads` directory by using the `folder` attribute:
    
    `[frontend_file_explorer folder="/course-materials"]`
    `[frontend_file_explorer folder="/clients/acme-corp"]`

    * **Frontend Capabilities:** Visitors browsing the frontend can view contents, click files to download them, and copy direct sharing links.
    * **Security:** Destructive or mutating actions (like file upload, folder creation, or deletion) remain strictly hidden and blocked from public visitors. They are only accessible to logged-in users who possess the WordPress `upload_files` capability.

**Who is this plugin for?**

* Course creators who need a simple, branded downloads area.
* Agencies and freelancers who share files with clients.
* Site owners who want a lightweight, Explorer-like file manager in WordPress.

== Upgrade Notice ==

= 1.0.8 =

This release adds Buy Me a Coffee support options, a credit removal toggle, and a settings panel. No manual action is required.

= 1.0.7 =

This release adds server-side sorting and an admin shortcode reference panel. No manual action is required.

= 1.0.6 =

This release includes a redesigned admin interface and multiple bug fixes. No manual action is required.

= 1.0.5 =

This release includes important security improvements and coding standards updates. No manual action is required.

= 1.0.4 =

This release includes security enhancements for file upload handling. No manual action is required.

= 1.0.3 =

This is a major security release addressing multiple vulnerabilities. Please update immediately. No manual action is required.

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

== Support Development ==

If you find this plugin useful and want to support its continued development, consider buying me a coffee!

[![Buy Me a Coffee](https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png)](https://www.buymeacoffee.com/itsmeshafat)

Your support helps keep the plugin maintained, secure, and free for everyone.

== Screenshots ==

1. Admin file explorer with folders, uploads, and toolbar actions (backend).
2. Frontend file explorer embedded via shortcode (frontend view).

== Changelog ==

= 1.0.8 =

* **Feature:** Added "Hide author credits" toggle (Settings panel) — removes credits from DOM when checked, dofollow attribution when visible.
* **Feature:** Added Buy Me a Coffee support button on the admin page, Plugins screen, readme.txt, and README.md.
* **UI:** Added a settings card below the shortcode panel with checkbox and support links.
* **Internal:** Added `plugin_row_meta` filter for plugin action links, `save_credits_preference` AJAX endpoint, and `frontend_file_explorer_hide_credits` option.

= 1.0.7 =

* **Feature:** Added server-side sorting for folder/file listings (Name, Date Modified, Size, Type, asc/desc).
* **Feature:** Admin can set a default sort order that persists for frontend visitors.
* **Feature:** Added shortcode reference panel with copyable code snippets below the admin explorer.
* **Feature:** Frontend explorer now respects the admin-configured default sort order.
* **UI:** Modernized shortcode & tips card with dark code blocks, hover effects, tooltip copy feedback.

= 1.0.6 =

* **UI:** Redesigned admin interface with modern styling, CSS custom properties, card-based grid, smoother transitions, and responsive layouts.
* **UI:** Replaced browser alerts with styled toast notifications for success/error messages.
* **UI:** Added file-type color coding for PDFs, Word docs, Excel files, and archives.
* **UI:** Improved modal design with backdrop blur, scale animation, and Escape-key dismissal.
* **Fix:** Fixed broken CSS asset paths preventing Material Icons and all styles from loading.
* **Fix:** Fixed Material Icons font URL resolution in bundled stylesheet.
* **Fix:** Fixed duplicate element ID causing toolbar button text to disappear on upload modal open.
* **Fix:** Fixed infinite recursion crash when clicking upload dropzone.
* **Fix:** Fixed "Upload Files" button incorrectly opening Media Library instead of the upload modal.
* **Fix:** Fixed ZIP download failing with "No path specified" (GET vs POST mismatch).
* **Fix:** Fixed trailing comma syntax error in admin JavaScript class method.

= 1.0.5 =

* **Security:** Added comprehensive array structure validation for `$_FILES` superglobal before accessing elements.
* **Security:** Moved `is_uploaded_file()` validation to immediately after accessing tmp_name for improved security.
* **Security:** Removed PHPCS ignore comment and implemented proper sanitization for file upload handling.
* **Standards:** Replaced `$_REQUEST` with `$_POST` for AJAX POST requests per WordPress coding standards.
* **Standards:** Replaced PHP `basename()` with WordPress `wp_basename()` for i18n compatibility with multibyte characters.

= 1.0.4 =

* **Security:** Sanitized and validated all `$_FILES` upload fields individually (name, type, tmp_name, error, size).
* **Security:** Added `is_uploaded_file()` guard against path injection on file uploads.
* **Standards:** Fixed unordered placeholders in translatable strings per WordPress i18n guidelines.

= 1.0.3 =

* **Security:** Fixed unauthenticated file downloads, arbitrary PHP uploads, XSS via eval(), and server path disclosure.
* **Standards:** Migrated all filesystem operations to WP_Filesystem API, bundled Material Icons locally, added proper nonce verification and capability checks to all AJAX endpoints.
* **Standards:** Renamed classes to use WordPress underscore convention (Frontend_File_Explorer, Frontend_File_Explorer_Ajax).
* **Standards:** Removed discouraged load_plugin_textdomain() call, added proper prefixing to all handles and identifiers.

= 1.0.2 =

* **Fix:** Resolved a critical bug causing the frontend explorer to execute filesystem deletion logic instead of listing directory contents.
* **Fix:** Repaired the "Download as ZIP" mechanism to eliminate `ERR_INVALID_RESPONSE` failures by safely building ZipArchive temp files and explicitly managing PHP output buffers and Safari download headers.
* **Feature:** Fully integrated the missing backend endpoints required for the UI, enabling seamless frontend and backend folder creation, file uploads, and Media Library imports.
* **Security & Standards:** Swept codebase for strict WordPress PHPCS warnings. Corrected all variable unslashing, resolved missing nonce verification checks, migrated deprecated filesystem functions to `WP_Filesystem`, and reinforced `esc_html__` translation domain strings and translators comments.

= 1.0.1 =

* Rename plugin to "Frontend File Explorer"
* Align text domain and translation loading with slug `frontend-file-explorer`
* Improve README and readme.txt descriptions and screenshots
