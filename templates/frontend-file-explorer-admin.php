<?php
/**
 * Admin interface template
 *
 * @package Frontend_File_Explorer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap frontend-file-explorer-container">
    <h1><?php esc_html_e('File Upload Manager', 'frontend-file-explorer'); ?></h1>

    <div class="frontend-file-explorer-toolbar">
        <div class="frontend-file-explorer-actions">
            <button type="button" id="frontend-file-explorer-create-folder" class="button button-primary">
                <span class="material-icons">create_new_folder</span>
                <?php esc_html_e('New Folder', 'frontend-file-explorer'); ?>
            </button>

            <button type="button" id="frontend-file-explorer-upload-files" class="button button-primary">
                <span class="material-icons">upload_file</span>
                <?php esc_html_e('Upload Files', 'frontend-file-explorer'); ?>
            </button>

            <button type="button" id="frontend-file-explorer-select-media" class="button button-secondary">
                <span class="material-icons">perm_media</span>
                <?php esc_html_e('Add from Media Library', 'frontend-file-explorer'); ?>
            </button>
        </div>

        <div class="frontend-file-explorer-sort">
            <label for="frontend-file-explorer-sort-select" class="frontend-file-explorer-sort-label"><?php esc_html_e('Sort:', 'frontend-file-explorer'); ?></label>
            <select id="frontend-file-explorer-sort-select" class="frontend-file-explorer-sort-select">
                <option value="name"><?php esc_html_e('Name', 'frontend-file-explorer'); ?></option>
                <option value="modified"><?php esc_html_e('Date Modified', 'frontend-file-explorer'); ?></option>
                <option value="size"><?php esc_html_e('Size', 'frontend-file-explorer'); ?></option>
                <option value="type"><?php esc_html_e('Type', 'frontend-file-explorer'); ?></option>
            </select>

            <button type="button" id="frontend-file-explorer-sort-dir" class="button button-secondary" title="<?php esc_attr_e('Toggle sort direction', 'frontend-file-explorer'); ?>">
                <span class="material-icons">arrow_upward</span>
            </button>

            <button type="button" id="frontend-file-explorer-sort-save" class="button button-secondary" title="<?php esc_attr_e('Set as default for frontend', 'frontend-file-explorer'); ?>">
                <span class="material-icons">save</span>
            </button>
        </div>

        <div class="frontend-file-explorer-breadcrumb">
            <button type="button" id="frontend-file-explorer-home" class="button button-secondary" title="<?php esc_attr_e('Home', 'frontend-file-explorer'); ?>">
                <span class="material-icons">home</span>
            </button>

            <button type="button" id="frontend-file-explorer-back" class="button button-secondary" title="<?php esc_attr_e('Go Back', 'frontend-file-explorer'); ?>">
                <span class="material-icons">arrow_back</span>
            </button>

            <span id="frontend-file-explorer-current-path">/</span>
        </div>
    </div>

    <div class="frontend-file-explorer-content">
        <div id="frontend-file-explorer-items" class="frontend-file-explorer-items">
            <!-- Items loaded via JavaScript -->
        </div>

        <div id="frontend-file-explorer-empty" class="frontend-file-explorer-empty" style="display: none;">
            <span class="material-icons">folder_open</span>
            <p><?php esc_html_e('This folder is empty', 'frontend-file-explorer'); ?></p>
        </div>

        <div id="frontend-file-explorer-loading" class="frontend-file-explorer-loading" style="display: none;">
            <span class="spinner is-active"></span>
            <p><?php esc_html_e('Loading...', 'frontend-file-explorer'); ?></p>
        </div>
    </div>

    <div class="frontend-file-explorer-pagination">
        <button type="button" id="frontend-file-explorer-load-more" class="button button-secondary" style="display: none;">
            <?php esc_html_e('Load More', 'frontend-file-explorer'); ?>
        </button>
    </div>

    <div class="frontend-file-explorer-shortcodes">
        <div class="frontend-file-explorer-shortcodes-header">
            <div class="frontend-file-explorer-shortcodes-header-icon">
                <span class="material-icons">code</span>
            </div>
            <div class="frontend-file-explorer-shortcodes-header-text">
                <h2><?php esc_html_e('Shortcodes & Tips', 'frontend-file-explorer'); ?></h2>
                <p><?php esc_html_e('Copy and paste these shortcodes to display the file explorer on your site.', 'frontend-file-explorer'); ?></p>
            </div>
        </div>

        <div class="frontend-file-explorer-shortcode-grid">
            <div class="frontend-file-explorer-shortcode-card">
                <div class="frontend-file-explorer-shortcode-label">
                    <span class="material-icons">code</span>
                    <?php esc_html_e('Basic Shortcode', 'frontend-file-explorer'); ?>
                </div>
                <div class="frontend-file-explorer-shortcode-code">
                    <code>[frontend_file_explorer]</code>
                    <button type="button" class="frontend-file-explorer-copy-btn button button-secondary" data-copy="[frontend_file_explorer]" data-tooltip="<?php esc_attr_e('Copied!', 'frontend-file-explorer'); ?>">
                        <span class="material-icons">content_copy</span>
                    </button>
                </div>
                <div class="frontend-file-explorer-shortcode-desc"><?php esc_html_e('Displays the file explorer on any page or post.', 'frontend-file-explorer'); ?></div>
            </div>

            <div class="frontend-file-explorer-shortcode-card">
                <div class="frontend-file-explorer-shortcode-label">
                    <span class="material-icons">folder</span>
                    <?php esc_html_e('With Custom Folder', 'frontend-file-explorer'); ?>
                </div>
                <div class="frontend-file-explorer-shortcode-code">
                    <code>[frontend_file_explorer folder="subdir"]</code>
                    <button type="button" class="frontend-file-explorer-copy-btn button button-secondary" data-copy='[frontend_file_explorer folder="subdir"]' data-tooltip="<?php esc_attr_e('Copied!', 'frontend-file-explorer'); ?>">
                        <span class="material-icons">content_copy</span>
                    </button>
                </div>
                <div class="frontend-file-explorer-shortcode-desc"><?php esc_html_e('Replace "subdir" with a folder name inside the uploads directory.', 'frontend-file-explorer'); ?></div>
            </div>
        </div>

        <div class="frontend-file-explorer-tips">
            <div class="frontend-file-explorer-tips-icon">
                <span class="material-icons">lightbulb</span>
            </div>
            <div class="frontend-file-explorer-tips-content">
                <div class="frontend-file-explorer-tips-content-label"><?php esc_html_e('Things to know', 'frontend-file-explorer'); ?></div>
                <ul class="frontend-file-explorer-tips-list">
                    <li><?php esc_html_e('Frontend visitors see the file explorer sorted by the default sort order you set here.', 'frontend-file-explorer'); ?></li>
                    <li><?php esc_html_e('The shortcode folder value is relative to the uploads root (e.g. wp-content/uploads/subdir).', 'frontend-file-explorer'); ?></li>
                    <li><?php esc_html_e('Use the shortcode in any post, page, or widget area that supports shortcodes.', 'frontend-file-explorer'); ?></li>
                    <li><?php esc_html_e('Set a default sort order with the save button above — it applies to both admin and frontend.', 'frontend-file-explorer'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="frontend-file-explorer-preferences-card">
        <div class="frontend-file-explorer-preferences-card-main">
            <div class="frontend-file-explorer-preferences-card-header">
                <span class="material-icons">tune</span>
                <?php esc_html_e('Preferences', 'frontend-file-explorer'); ?>
            </div>
            <div class="frontend-file-explorer-preferences-card-body">
                <label class="frontend-file-explorer-preferences-toggle">
                    <input type="checkbox" id="frontend-file-explorer-hide-credits" <?php checked($hide_credits); ?>>
                    <span class="frontend-file-explorer-preferences-toggle-switch"></span>
                    <span class="frontend-file-explorer-preferences-toggle-label"><?php esc_html_e('Hide author credit', 'frontend-file-explorer'); ?></span>
                </label>
                <p class="frontend-file-explorer-preferences-note"><?php esc_html_e('Removes the "Built by" line from both admin and frontend views.', 'frontend-file-explorer'); ?></p>
            </div>
        </div>
        <div class="frontend-file-explorer-preferences-card-divider"></div>
        <div class="frontend-file-explorer-preferences-card-support">
            <div class="frontend-file-explorer-preferences-support-text">
                <strong><?php esc_html_e('Enjoying the plugin?', 'frontend-file-explorer'); ?></strong>
                <span><?php esc_html_e('Support its development with a coffee.', 'frontend-file-explorer'); ?></span>
            </div>
            <a href="https://www.buymeacoffee.com/itsmeshafat" target="_blank" rel="noopener noreferrer">
                <img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me a Coffee" style="height: 50px; width: auto;">
            </a>
        </div>
    </div>

    <?php if (!$hide_credits) : ?>
    <p class="frontend-file-explorer-credit">
        <?php echo sprintf(
            esc_html__('Built by %1$s', 'frontend-file-explorer'),
            '<a href="https://itsmeshafat.com" target="_blank" rel="noopener noreferrer">Shafat Mahmud Khan</a>'
        ); ?>
    </p>
    <?php endif; ?>
</div>

<script type="text/html" id="tmpl-frontend-file-explorer-folder">
    <div class="frontend-file-explorer-item frontend-file-explorer-folder" data-path="{{ data.path }}" data-type="folder">
        <div class="frontend-file-explorer-item-icon">
            <span class="material-icons">folder</span>
        </div>
        <div class="frontend-file-explorer-item-name">{{ data.name }}</div>
        <div class="frontend-file-explorer-item-actions">
            <button type="button" class="frontend-file-explorer-action-open" title="<?php esc_attr_e('Open', 'frontend-file-explorer'); ?>">
                <span class="material-icons">open_in_new</span>
            </button>
            <button type="button" class="frontend-file-explorer-action-download-zip" title="<?php esc_attr_e('Download as ZIP', 'frontend-file-explorer'); ?>">
                <span class="material-icons">download</span>
            </button>
            <button type="button" class="frontend-file-explorer-action-delete" title="<?php esc_attr_e('Delete', 'frontend-file-explorer'); ?>">
                <span class="material-icons">delete</span>
            </button>
        </div>
    </div>
</script>

<script type="text/html" id="tmpl-frontend-file-explorer-file">
    <div class="frontend-file-explorer-item frontend-file-explorer-file" data-path="{{ data.path }}" data-type="file" data-extension="{{ data.extension }}">
        <# if (data.extension === 'jpg' || data.extension === 'jpeg' || data.extension === 'png' || data.extension === 'gif') { #>
            <div class="frontend-file-explorer-item-preview">
                <img src="{{ data.url }}" alt="{{ data.name }}">
            </div>
        <# } else if (data.extension === 'pdf') { #>
            <div class="frontend-file-explorer-item-icon">
                <span class="material-icons">picture_as_pdf</span>
            </div>
        <# } else if (data.extension === 'doc' || data.extension === 'docx') { #>
            <div class="frontend-file-explorer-item-icon">
                <span class="material-icons">description</span>
            </div>
        <# } else if (data.extension === 'xls' || data.extension === 'xlsx') { #>
            <div class="frontend-file-explorer-item-icon">
                <span class="material-icons">table_chart</span>
            </div>
        <# } else if (data.extension === 'zip' || data.extension === 'rar') { #>
            <div class="frontend-file-explorer-item-icon">
                <span class="material-icons">folder_zip</span>
            </div>
        <# } else { #>
            <div class="frontend-file-explorer-item-icon">
                <span class="material-icons">insert_drive_file</span>
            </div>
        <# } #>
        <div class="frontend-file-explorer-item-name">{{ data.name }}</div>
        <div class="frontend-file-explorer-item-actions">
            <button type="button" class="frontend-file-explorer-action-download" title="<?php esc_attr_e('Download', 'frontend-file-explorer'); ?>">
                <span class="material-icons">download</span>
            </button>
            <button type="button" class="frontend-file-explorer-action-copy-link" title="<?php esc_attr_e('Copy Link', 'frontend-file-explorer'); ?>">
                <span class="material-icons">link</span>
            </button>
            <button type="button" class="frontend-file-explorer-action-delete" title="<?php esc_attr_e('Delete', 'frontend-file-explorer'); ?>">
                <span class="material-icons">delete</span>
            </button>
        </div>
    </div>
</script>

<!-- Modals -->
<div id="frontend-file-explorer-create-folder-modal" class="frontend-file-explorer-modal" style="display: none;">
    <div class="frontend-file-explorer-modal-content">
        <button class="frontend-file-explorer-modal-close">&times;</button>
        <h2><?php esc_html_e('Create New Folder', 'frontend-file-explorer'); ?></h2>
        <div class="frontend-file-explorer-modal-body">
            <label for="frontend-file-explorer-folder-name"><?php esc_html_e('Folder Name', 'frontend-file-explorer'); ?></label>
            <input type="text" id="frontend-file-explorer-folder-name" class="regular-text" autofocus>
        </div>
        <div class="frontend-file-explorer-modal-footer">
            <button type="button" id="frontend-file-explorer-create-folder-cancel" class="button button-secondary"><?php esc_html_e('Cancel', 'frontend-file-explorer'); ?></button>
            <button type="button" id="frontend-file-explorer-create-folder-submit" class="button button-primary"><?php esc_html_e('Create', 'frontend-file-explorer'); ?></button>
        </div>
    </div>
</div>

<div id="frontend-file-explorer-upload-modal" class="frontend-file-explorer-modal" style="display: none;">
    <div class="frontend-file-explorer-modal-content">
        <button class="frontend-file-explorer-modal-close">&times;</button>
        <h2><?php esc_html_e('Upload Files', 'frontend-file-explorer'); ?></h2>
        <div class="frontend-file-explorer-modal-body">
            <div id="frontend-file-explorer-dropzone" class="frontend-file-explorer-dropzone">
                <div class="frontend-file-explorer-dropzone-text">
                    <span class="material-icons">cloud_upload</span>
                    <p><?php esc_html_e('Drag files here or click to select', 'frontend-file-explorer'); ?></p>
                </div>
                <input type="file" id="frontend-file-explorer-file-input" multiple style="display: none;">
            </div>
            <div id="frontend-file-explorer-upload-progress" class="frontend-file-explorer-upload-progress" style="display: none;">
                <div class="frontend-file-explorer-upload-progress-bar"></div>
                <div class="frontend-file-explorer-upload-progress-text">0%</div>
            </div>
            <div id="frontend-file-explorer-upload-file-list" class="frontend-file-explorer-upload-files"></div>
        </div>
        <div class="frontend-file-explorer-modal-footer">
            <button type="button" id="frontend-file-explorer-upload-cancel" class="button button-secondary"><?php esc_html_e('Cancel', 'frontend-file-explorer'); ?></button>
            <button type="button" id="frontend-file-explorer-upload-submit" class="button button-primary"><?php esc_html_e('Upload', 'frontend-file-explorer'); ?></button>
        </div>
    </div>
</div>
