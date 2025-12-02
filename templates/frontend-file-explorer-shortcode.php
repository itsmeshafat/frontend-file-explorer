<?php
/**
 * Frontend interface template — designed by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 *
 * @package Frontend_File_Explorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// We'll allow all users to view the file explorer
// The AJAX handlers will handle permissions
?>
<div class="frontend-file-explorer-frontend-container">
    <div class="frontend-file-explorer-toolbar">
        <div class="frontend-file-explorer-breadcrumb">
            <button type="button" id="frontend-file-explorer-frontend-home" class="frontend-file-explorer-btn" title="<?php esc_attr_e('Home', 'frontend-file-explorer'); ?>">
                <span class="material-icons">home</span>
            </button>
            
            <button type="button" id="frontend-file-explorer-frontend-back" class="frontend-file-explorer-btn" title="<?php esc_attr_e('Go Back', 'frontend-file-explorer'); ?>">
                <span class="material-icons">arrow_back</span>
            </button>
            
            <span id="frontend-file-explorer-frontend-current-path">/</span>
        </div>
        
        <div class="frontend-file-explorer-actions">
            <button type="button" id="frontend-file-explorer-frontend-download-zip" class="frontend-file-explorer-btn" title="<?php esc_attr_e('Download as ZIP', 'frontend-file-explorer'); ?>" style="display: none;">
                <span class="material-icons">archive</span>
                <span class="button-text"><?php esc_html_e('Download as ZIP', 'frontend-file-explorer'); ?></span>
            </button>
        </div>
    </div>
    
    <div class="frontend-file-explorer-content">
        <div id="frontend-file-explorer-frontend-items" class="frontend-file-explorer-items">
            <!-- Items will be loaded here via JavaScript -->
        </div>
        
        <div id="frontend-file-explorer-frontend-empty" class="frontend-file-explorer-empty" style="display: none;">
            <span class="material-icons">folder_open</span>
            <p><?php esc_html_e('This folder is empty', 'frontend-file-explorer'); ?></p>
        </div>
        
        <div id="frontend-file-explorer-frontend-loading" class="frontend-file-explorer-loading" style="display: none;">
            <span class="material-icons">hourglass_empty</span>
            <p><?php esc_html_e('Loading...', 'frontend-file-explorer'); ?></p>
        </div>
    </div>
    
    <div class="frontend-file-explorer-pagination">
        <button type="button" id="frontend-file-explorer-frontend-load-more" class="frontend-file-explorer-btn" style="display: none;">
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
<script type="text/html" id="tmpl-file-explorer-frontend-folder">
    <div class="frontend-file-explorer-item frontend-file-explorer-folder" data-path="{{ data.path }}" data-type="folder">
        <div class="frontend-file-explorer-item-icon">
            <span class="material-icons folder-icon">folder</span>
        </div>
        <div class="frontend-file-explorer-item-name">{{ data.name }}</div>
        <div class="frontend-file-explorer-item-actions">
            <button type="button" class="frontend-file-explorer-action-open" title="<?php esc_attr_e('Open', 'frontend-file-explorer'); ?>">
                <span class="material-icons">open_in_new</span>
            </button>
            <button type="button" class="frontend-file-explorer-action-download-zip" title="<?php esc_attr_e('Download as ZIP', 'frontend-file-explorer'); ?>">
                <span class="material-icons">download</span>
            </button>
        </div>
    </div>
</script>

<script type="text/html" id="tmpl-file-explorer-frontend-file">
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
        </div>
    </div>
</script>
