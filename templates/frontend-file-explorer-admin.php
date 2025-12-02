<?php
/**
 * Admin interface template — designed by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 *
 * @package Frontend_File_Explorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap file-explorer-container">
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
            <!-- Items will be loaded here via JavaScript -->
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

    <p class="frontend-file-explorer-credit">
        <?php echo sprintf(
            /* translators: 1: developer name, 2: portfolio URL */
            esc_html__('Built by %1$s — WordPress Developer. Portfolio: %2$s', 'frontend-file-explorer'),
            '<a href="https://itsmeshafat.com" target="_blank" rel="noopener noreferrer">Shafat Mahmud Khan</a>',
            '<a href="https://itsmeshafat.com" target="_blank" rel="noopener noreferrer">itsmeshafat.com</a>'
        ); ?>
    </p>
</div>

<!-- Templates -->
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
    <div class="frontend-file-explorer-item frontend-file-explorer-file" data-path="{{ data.path }}" data-type="file">
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
        <span class="frontend-file-explorer-modal-close">&times;</span>
        <h2><?php esc_html_e('Create New Folder', 'frontend-file-explorer'); ?></h2>
        <div class="frontend-file-explorer-modal-body">
            <label for="frontend-file-explorer-folder-name"><?php esc_html_e('Folder Name', 'frontend-file-explorer'); ?></label>
            <input type="text" id="frontend-file-explorer-folder-name" class="regular-text">
        </div>
        <div class="frontend-file-explorer-modal-footer">
            <button type="button" id="frontend-file-explorer-create-folder-cancel" class="button button-secondary"><?php esc_html_e('Cancel', 'frontend-file-explorer'); ?></button>
            <button type="button" id="frontend-file-explorer-create-folder-submit" class="button button-primary"><?php esc_html_e('Create', 'frontend-file-explorer'); ?></button>
        </div>
    </div>
</div>

<div id="frontend-file-explorer-upload-modal" class="frontend-file-explorer-modal" style="display: none;">
    <div class="frontend-file-explorer-modal-content">
        <span class="frontend-file-explorer-modal-close">&times;</span>
        <h2><?php esc_html_e('Upload Files', 'frontend-file-explorer'); ?></h2>
        <div class="frontend-file-explorer-modal-body">
            <div id="frontend-file-explorer-dropzone" class="frontend-file-explorer-dropzone">
                <div class="frontend-file-explorer-dropzone-text">
                    <span class="material-icons">cloud_upload</span>
                    <p><?php esc_html_e('Drag files here or click to select files', 'frontend-file-explorer'); ?></p>
                </div>
                <input type="file" id="frontend-file-explorer-file-input" multiple style="display: none;">
            </div>
            <div id="frontend-file-explorer-upload-progress" class="frontend-file-explorer-upload-progress" style="display: none;">
                <div class="frontend-file-explorer-upload-progress-bar"></div>
                <div class="frontend-file-explorer-upload-progress-text">0%</div>
            </div>
            <div id="frontend-file-explorer-upload-files" class="frontend-file-explorer-upload-files"></div>
        </div>
        <div class="frontend-file-explorer-modal-footer">
            <button type="button" id="frontend-file-explorer-upload-cancel" class="button button-secondary"><?php esc_html_e('Cancel', 'frontend-file-explorer'); ?></button>
            <button type="button" id="frontend-file-explorer-upload-submit" class="button button-primary"><?php esc_html_e('Upload', 'frontend-file-explorer'); ?></button>
        </div>
    </div>
</div>
