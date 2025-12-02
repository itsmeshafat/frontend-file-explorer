# Frontend File Explorer

<p align="center">
  <img src="https://img.shields.io/badge/WordPress-Plugin-blue?logo=wordpress" alt="WordPress badge" />
  <img src="https://img.shields.io/badge/Tested%20up%20to-6.8-green" alt="WP compatibility badge" />
  <img src="https://img.shields.io/badge/PHP-7.4%2B-8892BF?logo=php" alt="PHP version badge" />
  <img src="https://img.shields.io/badge/License-GPL%20v2%2B-orange" alt="License badge" />
</p>

<p align="center">
  A modern, Windows Explorer–inspired file manager for WordPress with admin interface and frontend shortcode.
</p>

---

## Table of Contents

1. [Features](#features)
2. [Developer](#developer)
3. [Requirements](#requirements)
4. [Installation](#installation)
5. [Usage](#usage)
6. [Project structure](#project-structure)
7. [Development notes](#development-notes)
8. [Security considerations](#security-considerations)
9. [Troubleshooting](#troubleshooting)
10. [FAQ](#faq)
11. [Roadmap](#roadmap)
12. [Changelog](#changelog)
13. [Contributing](#contributing)
14. [License](#license)

## Features

<p align="center">
  <img src="assets/images/backend.jpg" alt="File Explorer admin interface screenshot" width="700" />
</p>

Frontend File Explorer is a modern, Windows Explorer–inspired file manager for WordPress. It gives you a clean admin interface to organize and share files plus a responsive frontend explorer powered by a simple shortcode.

Use it to create download areas for courses, client file portals, or resource libraries — without relying on heavy external file management tools.

The plugin provides a seamless experience for both administrators and frontend users:

| Category | Highlights |
|----------|------------|
| **UI/UX** | Explorer-style layout with breadcrumbs, toolbar actions, pagination, Material Icons visuals, responsive grid view. |
| **Storage** | Auto-creates and secures `wp-content/uploads/downloads`, including `.htaccess` and `index.php`. |
| **Permissions** | Admin actions (upload, copy, delete, ZIP) gated by `upload_files`; frontend browsing is read-only. |
| **Performance** | AJAX navigation with server-side pagination. |
| **Sharing** | One-click ZIP downloads plus copyable public links. |
| **Localization** | Text domain `frontend-file-explorer` ready for translation. |

> 💡 **Tip:** Pair the plugin with WP roles/capabilities plugins (e.g., Members) to fine-tune who can manage files.

## Developer

Built by **Shafat Mahmud Khan**, WordPress Developer – [itsmeshafat.com](https://itsmeshafat.com)

## Requirements

| Requirement | Minimum |
|-------------|---------|
| WordPress   | 5.6     |
| PHP         | 7.4     |
| Permissions | Ability to upload plugins / manage files |

The plugin relies only on WordPress core APIs plus the bundled Material Icons stylesheet.

## Installation

### Installation from within WordPress

1. Visit **Plugins > Add New**.
2. Search for **Frontend File Explorer**.
3. Install and activate the Frontend File Explorer plugin.
4. On activation, the plugin will create `wp-content/uploads/downloads`. Make sure your hosting environment allows file creation in `wp-content/uploads`.

### Manual installation

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the Frontend File Explorer plugin.

## Usage

### Admin interface

> 🎛️ **Dashboard experience**

- Find the menu item **File Upload** in the WordPress admin sidebar.
- From here you can:
  - Create folders
  - Upload files (multi-select support)
  - Copy existing Media Library items into the Explorer
  - Delete files and folders
  - Download a folder as a ZIP archive
- All actions are handled via nonce-protected AJAX endpoints for a smoother UX.

### Frontend shortcode

Embed the explorer anywhere (pages, posts, custom post types):

```text
[file_explorer folder="/"]
```

<p align="center">
  <img src="assets/images/frontend.png" alt="File Explorer frontend shortcode output screenshot" width="700" />
</p>

- `folder` (optional) – starting subdirectory relative to `uploads/downloads`. Use `/` for the root.
- Visitors can browse folders, download files, and copy direct links.
- Mutating actions (upload, delete, etc.) remain restricted to logged-in users with the `upload_files` capability.

## Project structure

```
frontend-file-explorer.php              # Plugin bootstrap; defines constants, hooks, activation logic
includes/
  class-frontend-file-explorer.php      # Main singleton: menus, assets, shortcode, activation helpers
  class-frontend-file-explorer-ajax.php # AJAX controller for admin + frontend requests
assets/
  css/, js/                             # Admin & frontend styles/scripts
templates/
  frontend-file-explorer-admin.php      # Admin UI markup
  frontend-file-explorer-shortcode.php  # Shortcode output template
```

## Development notes

- Hooks are registered inside `Frontend_File_Explorer::init_hooks()`. Extend or override behavior there when adding new features.
- Admin and frontend scripts are localized with runtime data (`frontendFileExplorerAdmin`, `frontendFileExplorerFrontend`). Reuse these objects for new AJAX calls or UI strings.
- Default options (allowed file types, upload size, pagination) are managed via `Frontend_File_Explorer::set_default_options()`. Update the option keys there if adding new settings fields.
- AJAX methods expect sanitized paths relative to `/downloads` and reject directory traversal attempts. Preserve these checks when modifying file operations.

### Running in development

1. Use a local WordPress environment (e.g., Local WP, WP-CLI, Laravel Valet) running PHP ≥7.4.
2. Activate the plugin and ensure the `uploads/downloads` directory is writable.
3. Inspect browser dev tools for AJAX responses; errors return localized JSON messages via `wp_send_json_error`.
4. For quick resets, delete the `uploads/downloads` subdirectories; the plugin recreates guard files automatically.

## Security considerations

- Capability checks guard all mutating admin actions (`current_user_can( 'upload_files' )`).
- Nonces (`wp_create_nonce( 'frontend_file_explorer_nonce' )`) are validated for every request.
- Paths are sanitized and disallow `..` traversal before interacting with the filesystem.
- Download directory contains `.htaccess` and `index.php` files to prevent raw listing.
- ZIP downloads stream from server-side archives created per request, minimizing stale artifacts.

## Troubleshooting

| Symptom | Likely cause | Fix |
|---------|--------------|-----|
| **“You do not have permission” error** | Current user lacks `upload_files` capability | Elevate the role or adjust via a role editor plugin. |
| **Frontend stays on “Loading…”** | Missing shortcode nonce or cached JS | Clear cache, ensure `[file_explorer]` is rendered, and flush permalinks. |
| **Uploads fail silently** | Server blocks `wp-content/uploads/downloads` writes | Confirm directory permissions (755/775) and PHP upload limits. |
| **ZIP downloads corrupted** | Hosting disables `ZipArchive` | Enable the PHP `zip` extension or install server support. |

> ℹ️ Enable `WP_DEBUG_LOG` to capture AJAX responses when chasing edge cases.

## FAQ

**Q: Can I point the explorer to a different base folder?**  
A: Yes—override the constants in a custom mu-plugin before File Explorer loads, or filter `FILE_EXPLORER_UPLOADS_DIR`/`URL` via `wp_loaded` hooks. This is an advanced customization and should be done carefully.

**Q: Does the plugin work in multisite?**  
A: Yes. Each site manages its own `uploads/downloads` directory. You can network-activate the plugin for consistency across sites.

**Q: Are file types restricted?**  
A: By default, allowed file types are defined via options during activation. You can adjust the allowed extensions by updating the plugin options (e.g., `file_explorer_allowed_file_types`).

**Q: How do I translate the UI?**  
A: The plugin is fully localization-ready and uses the `frontend-file-explorer` text domain. You can use tools like Loco Translate or Poedit to create translations and drop `.mo` files in the `languages/` directory.

## Roadmap

- [ ] Settings page for controlling allowed file types, pagination, and custom labels.
- [ ] Bulk selection UX for mass move/delete.
- [ ] Optional download logs with user + timestamp tracking.
- [ ] Gutenberg block for embedding the explorer without shortcodes.

Have a feature request? Open an issue describing the use case.

## Changelog

| Version | Date | Notes |
|---------|------|-------|
| 1.0.1 | 2025-12-03 | Rename plugin to "Frontend File Explorer", align text domain, and improve documentation. |
| 1.0.0 | 2025-11-30 | Initial public release with admin/ frontend explorers, ZIP downloads, and AJAX tooling. |

## Contributing

1. Fork and clone the repository.
2. Create a feature branch: `git checkout -b feature/my-improvement`.
3. Make your changes with clear commits and adhere to WordPress coding standards.
4. Open a pull request describing the changes, testing steps, and screenshots/GIFs for UI updates.
5. Be sure to mention environment details (WP/PHP versions) for reproducibility.

## License

GPL v2 or later. See the plugin header in `frontend-file-explorer.php` for details.
