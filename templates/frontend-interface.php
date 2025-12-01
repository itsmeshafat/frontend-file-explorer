<?php
/**
 * Frontend interface template — designed by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 *
 * @package File_Explorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// We'll allow all users to view the file explorer
// The AJAX handlers will handle permissions
?>
<div class="file-explorer-frontend-container">
    <div class="file-explorer-toolbar">
        <div class="file-explorer-breadcrumb">
            <button type="button" id="file-explorer-frontend-home" class="file-explorer-btn" title="<?php _e('Home', 'frontend-file-explorer-plugin'); ?>">
                <span class="material-icons">home</span>
            </button>
            
            <button type="button" id="file-explorer-frontend-back" class="file-explorer-btn" title="<?php _e('Go Back', 'frontend-file-explorer-plugin'); ?>">
                <span class="material-icons">arrow_back</span>
            </button>
            
            <span id="file-explorer-frontend-current-path">/</span>
        </div>
        
        <div class="file-explorer-actions">
            <button type="button" id="file-explorer-frontend-download-zip" class="file-explorer-btn" title="<?php _e('Download as ZIP', 'frontend-file-explorer-plugin'); ?>" style="display: none;">
                <span class="material-icons">archive</span>
                <span class="button-text"><?php _e('Download as ZIP', 'file-explorer'); ?></span>
            </button>
        </div>
    </div>
    
    <div class="file-explorer-content">
        <div id="file-explorer-frontend-items" class="file-explorer-items">
            <!-- Items will be loaded here via JavaScript -->
        </div>
        
        <div id="file-explorer-frontend-empty" class="file-explorer-empty" style="display: none;">
            <span class="material-icons">folder_open</span>
            <p><?php _e('This folder is empty', 'frontend-file-explorer-plugin'); ?></p>
        </div>
        
        <div id="file-explorer-frontend-loading" class="file-explorer-loading" style="display: none;">
            <span class="material-icons">hourglass_empty</span>
            <p><?php _e('Loading...', 'frontend-file-explorer-plugin'); ?></p>
        </div>
    </div>
    
    <div class="file-explorer-pagination">
        <button type="button" id="file-explorer-frontend-load-more" class="file-explorer-btn" style="display: none;">
            <?php _e('Load More', 'frontend-file-explorer-plugin'); ?>
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
<script type="text/html" id="tmpl-file-explorer-frontend-folder">
    <div class="file-explorer-item file-explorer-folder" data-path="{{ data.path }}" data-type="folder">
        <div class="file-explorer-item-icon">
            <span class="material-icons folder-icon">folder</span>
        </div>
        <div class="file-explorer-item-name">{{ data.name }}</div>
        <div class="file-explorer-item-actions">
            <button type="button" class="file-explorer-action-open" title="<?php _e('Open', 'frontend-file-explorer-plugin'); ?>">
                <span class="material-icons">open_in_new</span>
            </button>
            <button type="button" class="file-explorer-action-download-zip" title="<?php _e('Download as ZIP', 'frontend-file-explorer-plugin'); ?>">
                <span class="material-icons">download</span>
            </button>
        </div>
    </div>
</script>

<script type="text/html" id="tmpl-file-explorer-frontend-file">
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
            <button type="button" class="file-explorer-action-download" title="<?php _e('Download', 'frontend-file-explorer-plugin'); ?>">
                <span class="material-icons">download</span>
            </button>
            <button type="button" class="file-explorer-action-copy-link" title="<?php _e('Copy Link', 'frontend-file-explorer-plugin'); ?>">
                <span class="material-icons">link</span>
            </button>
        </div>
    </div>
</script>
