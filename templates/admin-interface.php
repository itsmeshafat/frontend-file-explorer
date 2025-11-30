<?php
/**
 * Admin interface template — designed by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 *
 * @package File_Explorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap file-explorer-container">
    <h1><?php _e('File Upload Manager', 'file-explorer'); ?></h1>
    
    <div class="file-explorer-toolbar">
        <div class="file-explorer-actions">
            <button type="button" id="file-explorer-create-folder" class="button button-primary">
                <span class="material-icons">create_new_folder</span>
                <?php _e('New Folder', 'file-explorer'); ?>
            </button>
            
            <button type="button" id="file-explorer-upload-files" class="button button-primary">
                <span class="material-icons">upload_file</span>
                <?php _e('Upload Files', 'file-explorer'); ?>
            </button>
            
            <button type="button" id="file-explorer-select-media" class="button button-secondary">
                <span class="material-icons">perm_media</span>
                <?php _e('Add from Media Library', 'file-explorer'); ?>
            </button>
        </div>
        
        <div class="file-explorer-breadcrumb">
            <button type="button" id="file-explorer-home" class="button button-secondary" title="<?php _e('Home', 'file-explorer'); ?>">
                <span class="material-icons">home</span>
            </button>
            
            <button type="button" id="file-explorer-back" class="button button-secondary" title="<?php _e('Go Back', 'file-explorer'); ?>">
                <span class="material-icons">arrow_back</span>
            </button>
            
            <span id="file-explorer-current-path">/</span>
        </div>
    </div>
    
    <div class="file-explorer-content">
        <div id="file-explorer-items" class="file-explorer-items">
            <!-- Items will be loaded here via JavaScript -->
        </div>
        
        <div id="file-explorer-empty" class="file-explorer-empty" style="display: none;">
            <span class="material-icons">folder_open</span>
            <p><?php _e('This folder is empty', 'file-explorer'); ?></p>
        </div>
        
        <div id="file-explorer-loading" class="file-explorer-loading" style="display: none;">
            <span class="spinner is-active"></span>
            <p><?php _e('Loading...', 'file-explorer'); ?></p>
        </div>
    </div>
    
    <div class="file-explorer-pagination">
        <button type="button" id="file-explorer-load-more" class="button button-secondary" style="display: none;">
            <?php _e('Load More', 'file-explorer'); ?>
        </button>
    </div>

    <p class="file-explorer-credit">
        <?php echo sprintf(
            /* translators: 1: developer name, 2: portfolio URL */
            esc_html__('Built by %1$s — WordPress Developer. Portfolio: %2$s', 'file-explorer'),
            '<a href="https://itsmeshafat.com" target="_blank" rel="noopener noreferrer">Shafat Mahmud Khan</a>',
            '<a href="https://itsmeshafat.com" target="_blank" rel="noopener noreferrer">itsmeshafat.com</a>'
        ); ?>
    </p>
</div>

<!-- Templates -->
<script type="text/html" id="tmpl-file-explorer-folder">
    <div class="file-explorer-item file-explorer-folder" data-path="{{ data.path }}" data-type="folder">
        <div class="file-explorer-item-icon">
            <span class="material-icons">folder</span>
        </div>
        <div class="file-explorer-item-name">{{ data.name }}</div>
        <div class="file-explorer-item-actions">
            <button type="button" class="file-explorer-action-open" title="<?php _e('Open', 'file-explorer'); ?>">
                <span class="material-icons">open_in_new</span>
            </button>
            <button type="button" class="file-explorer-action-download-zip" title="<?php _e('Download as ZIP', 'file-explorer'); ?>">
                <span class="material-icons">download</span>
            </button>
            <button type="button" class="file-explorer-action-delete" title="<?php _e('Delete', 'file-explorer'); ?>">
                <span class="material-icons">delete</span>
            </button>
        </div>
    </div>
</script>

<script type="text/html" id="tmpl-file-explorer-file">
    <div class="file-explorer-item file-explorer-file" data-path="{{ data.path }}" data-type="file">
        <# if (data.extension === 'jpg' || data.extension === 'jpeg' || data.extension === 'png' || data.extension === 'gif') { #>
            <div class="file-explorer-item-preview">
                <img src="{{ data.url }}" alt="{{ data.name }}">
            </div>
        <# } else if (data.extension === 'pdf') { #>
            <div class="file-explorer-item-icon">
                <span class="material-icons">picture_as_pdf</span>
            </div>
        <# } else if (data.extension === 'doc' || data.extension === 'docx') { #>
            <div class="file-explorer-item-icon">
                <span class="material-icons">description</span>
            </div>
        <# } else if (data.extension === 'xls' || data.extension === 'xlsx') { #>
            <div class="file-explorer-item-icon">
                <span class="material-icons">table_chart</span>
            </div>
        <# } else if (data.extension === 'zip' || data.extension === 'rar') { #>
            <div class="file-explorer-item-icon">
                <span class="material-icons">folder_zip</span>
            </div>
        <# } else { #>
            <div class="file-explorer-item-icon">
                <span class="material-icons">insert_drive_file</span>
            </div>
        <# } #>
        <div class="file-explorer-item-name">{{ data.name }}</div>
        <div class="file-explorer-item-actions">
            <button type="button" class="file-explorer-action-download" title="<?php _e('Download', 'file-explorer'); ?>">
                <span class="material-icons">download</span>
            </button>
            <button type="button" class="file-explorer-action-copy-link" title="<?php _e('Copy Link', 'file-explorer'); ?>">
                <span class="material-icons">link</span>
            </button>
            <button type="button" class="file-explorer-action-delete" title="<?php _e('Delete', 'file-explorer'); ?>">
                <span class="material-icons">delete</span>
            </button>
        </div>
    </div>
</script>

<!-- Modals -->
<div id="file-explorer-create-folder-modal" class="file-explorer-modal" style="display: none;">
    <div class="file-explorer-modal-content">
        <span class="file-explorer-modal-close">&times;</span>
        <h2><?php _e('Create New Folder', 'file-explorer'); ?></h2>
        <div class="file-explorer-modal-body">
            <label for="file-explorer-folder-name"><?php _e('Folder Name', 'file-explorer'); ?></label>
            <input type="text" id="file-explorer-folder-name" class="regular-text">
        </div>
        <div class="file-explorer-modal-footer">
            <button type="button" id="file-explorer-create-folder-cancel" class="button button-secondary"><?php _e('Cancel', 'file-explorer'); ?></button>
            <button type="button" id="file-explorer-create-folder-submit" class="button button-primary"><?php _e('Create', 'file-explorer'); ?></button>
        </div>
    </div>
</div>

<div id="file-explorer-upload-modal" class="file-explorer-modal" style="display: none;">
    <div class="file-explorer-modal-content">
        <span class="file-explorer-modal-close">&times;</span>
        <h2><?php _e('Upload Files', 'file-explorer'); ?></h2>
        <div class="file-explorer-modal-body">
            <div id="file-explorer-dropzone" class="file-explorer-dropzone">
                <div class="file-explorer-dropzone-text">
                    <span class="material-icons">cloud_upload</span>
                    <p><?php _e('Drag files here or click to select files', 'file-explorer'); ?></p>
                </div>
                <input type="file" id="file-explorer-file-input" multiple style="display: none;">
            </div>
            <div id="file-explorer-upload-progress" class="file-explorer-upload-progress" style="display: none;">
                <div class="file-explorer-upload-progress-bar"></div>
                <div class="file-explorer-upload-progress-text">0%</div>
            </div>
            <div id="file-explorer-upload-files" class="file-explorer-upload-files"></div>
        </div>
        <div class="file-explorer-modal-footer">
            <button type="button" id="file-explorer-upload-cancel" class="button button-secondary"><?php _e('Cancel', 'file-explorer'); ?></button>
            <button type="button" id="file-explorer-upload-submit" class="button button-primary"><?php _e('Upload', 'file-explorer'); ?></button>
        </div>
    </div>
</div>
