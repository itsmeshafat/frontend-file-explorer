/**
 * File Explorer Admin JavaScript — engineered by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 */
(function($) {
    'use strict';

    // File Explorer class
    class FileExplorer {
        constructor() {
            // Properties
            this.currentPath = '/';
            this.currentPage = 1;
            this.hasMoreItems = false;
            this.isLoading = false;
            this.uploadFiles = [];
            
            // DOM elements
            this.$container = $('.file-explorer-container');
            this.$items = $('#file-explorer-items');
            this.$empty = $('#file-explorer-empty');
            this.$loading = $('#file-explorer-loading');
            this.$currentPath = $('#file-explorer-current-path');
            this.$loadMore = $('#file-explorer-load-more');
            
            // Templates
            this.folderTemplate = wp.template('file-explorer-folder');
            this.fileTemplate = wp.template('file-explorer-file');
            
            // Initialize
            this.init();
        }
        
        /**
         * Initialize
         */
        init() {
            this.bindEvents();
            this.loadItems();
        }
        
        /**
         * Bind events
         */
        bindEvents() {
            // Navigation
            $('#file-explorer-home').on('click', this.navigateHome.bind(this));
            $('#file-explorer-back').on('click', this.navigateBack.bind(this));
            
            // Actions
            $('#file-explorer-create-folder').on('click', this.showCreateFolderModal.bind(this));
            $('#file-explorer-upload-files').on('click', this.showUploadModal.bind(this));
            $('#file-explorer-select-media').on('click', this.openMediaLibrary.bind(this));
            
            // Load more
            this.$loadMore.on('click', this.loadMoreItems.bind(this));
            
            // Item click events (delegation)
            this.$items.on('click', '.file-explorer-folder', this.handleFolderClick.bind(this));
            this.$items.on('click', '.file-explorer-action-open', this.handleOpenClick.bind(this));
            this.$items.on('click', '.file-explorer-action-download-zip', this.handleDownloadZipClick.bind(this));
            this.$items.on('click', '.file-explorer-action-download', this.handleDownloadClick.bind(this));
            this.$items.on('click', '.file-explorer-action-copy-link', this.handleCopyLinkClick.bind(this));
            this.$items.on('click', '.file-explorer-action-delete', this.handleDeleteClick.bind(this));
            
            // Create folder modal
            $('#file-explorer-create-folder-submit').on('click', this.createFolder.bind(this));
            $('#file-explorer-create-folder-cancel, .file-explorer-modal-close').on('click', this.closeModals.bind(this));
            
            // Upload modal
            // File input handlers are now set in the showUploadModal method
            $('#file-explorer-upload-submit').on('click', this.uploadSelectedFiles.bind(this));
            $('#file-explorer-upload-cancel, .file-explorer-modal-close').on('click', this.closeModals.bind(this));
            
            // Drag and drop
            const $dropzone = $('#file-explorer-dropzone');
            $dropzone.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });
            
            $dropzone.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });
            
            $dropzone.on('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                $dropzone.removeClass('drag-over');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    this.handleFileSelection({ target: { files: files } });
                }
            });
        }
        
        /**
         * Load items
         */
        loadItems() {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.showLoading(true);
            
            $.ajax({
                url: fileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'file_explorer_get_folder_contents',
                    nonce: fileExplorerAdmin.nonce,
                    path: this.currentPath,
                    page: this.currentPage
                },
                success: (response) => {
                    if (response.success) {
                        this.renderItems(response.data, this.currentPage > 1);
                        this.updatePath(response.data.current_path);
                        this.hasMoreItems = response.data.pagination.has_more;
                        this.$loadMore.toggle(this.hasMoreItems);
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(fileExplorerAdmin.strings.error);
                },
                complete: () => {
                    this.isLoading = false;
                    this.showLoading(false);
                }
            });
        }
        
        /**
         * Render items
         */
        renderItems(data, append = false) {
            if (!append) {
                this.$items.empty();
            }
            
            const items = data.items || [];
            
            if (items.length === 0 && !append) {
                this.$items.hide();
                this.$empty.show();
                return;
            }
            
            this.$items.show();
            this.$empty.hide();
            
            items.forEach((item) => {
                let html = '';
                
                if (item.type === 'folder') {
                    html = this.folderTemplate(item);
                } else {
                    html = this.fileTemplate(item);
                }
                
                this.$items.append(html);
            });
        }
        
        /**
         * Load more items
         */
        loadMoreItems() {
            if (this.isLoading || !this.hasMoreItems) return;
            
            this.currentPage++;
            this.loadItems();
        }
        
        /**
         * Navigate home
         */
        navigateHome() {
            this.currentPath = '/';
            this.currentPage = 1;
            this.loadItems();
        }
        
        /**
         * Navigate back
         */
        navigateBack() {
            if (this.currentPath === '/') return;
            
            const parts = this.currentPath.split('/').filter(Boolean);
            parts.pop();
            
            this.currentPath = parts.length ? '/' + parts.join('/') : '/';
            this.currentPage = 1;
            this.loadItems();
        }
        
        /**
         * Update path
         */
        updatePath(path) {
            this.currentPath = path;
            this.$currentPath.text(path);
        }
        
        /**
         * Show loading
         */
        showLoading(show) {
            this.$loading.toggle(show);
        }
        
        /**
         * Show error
         */
        showError(message) {
            console.error('File Explorer Error:', message);
            alert(message);
        }
        
        /**
         * Show success
         */
        showSuccess(message) {
            // Could be implemented with a nicer notification system
            // For now, just use alert
            alert(message);
        }
        
        /**
         * Handle folder click
         */
        handleFolderClick(e) {
            if ($(e.target).closest('.file-explorer-item-actions').length) {
                return;
            }
            
            const $folder = $(e.currentTarget);
            const path = $folder.data('path');
            
            this.currentPath = path;
            this.currentPage = 1;
            this.loadItems();
        }
        
        /**
         * Handle open click
         */
        handleOpenClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $item = $(e.target).closest('.file-explorer-item');
            const path = $item.data('path');
            const type = $item.data('type');
            
            if (type === 'folder') {
                this.currentPath = path;
                this.currentPage = 1;
                this.loadItems();
            }
        }
        
        /**
         * Handle download zip click
         */
        handleDownloadZipClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $item = $(e.target).closest('.file-explorer-item');
            const path = $item.data('path');
            
            // Open download in new tab
            window.open(
                fileExplorerAdmin.ajaxUrl + 
                '?action=file_explorer_download_as_zip' + 
                '&nonce=' + fileExplorerAdmin.nonce + 
                '&path=' + encodeURIComponent(path),
                '_blank'
            );
        }
        
        /**
         * Handle download click
         */
        handleDownloadClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $item = $(e.target).closest('.file-explorer-item');
            const path = $item.data('path');
            
            // Create a temporary link and click it
            const link = document.createElement('a');
            link.href = fileExplorerAdmin.uploadsUrl + path;
            link.download = path.split('/').pop();
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        /**
         * Handle copy link click
         */
        handleCopyLinkClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $item = $(e.target).closest('.file-explorer-item');
            const path = $item.data('path');
            
            $.ajax({
                url: fileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'file_explorer_get_file_link',
                    nonce: fileExplorerAdmin.nonce,
                    path: path
                },
                success: (response) => {
                    if (response.success) {
                        // Copy to clipboard
                        const tempInput = document.createElement('input');
                        document.body.appendChild(tempInput);
                        tempInput.value = response.data;
                        tempInput.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempInput);
                        
                        this.showSuccess(fileExplorerAdmin.strings.copySuccess);
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(fileExplorerAdmin.strings.error);
                }
            });
        }
        
        /**
         * Handle delete click
         */
        handleDeleteClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $item = $(e.target).closest('.file-explorer-item');
            const path = $item.data('path');
            const type = $item.data('type');
            const name = $item.find('.file-explorer-item-name').text();
            
            if (!confirm(fileExplorerAdmin.strings.confirmDelete)) {
                return;
            }
            
            this.showLoading(true);
            
            $.ajax({
                url: fileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'file_explorer_delete_item',
                    nonce: fileExplorerAdmin.nonce,
                    path: path,
                    type: type
                },
                success: (response) => {
                    if (response.success) {
                        // Reload items
                        this.currentPage = 1;
                        this.loadItems();
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(fileExplorerAdmin.strings.error);
                },
                complete: () => {
                    this.showLoading(false);
                }
            });
        }
        
        /**
         * Show create folder modal
         */
        showCreateFolderModal() {
            $('#file-explorer-folder-name').val('');
            $('#file-explorer-create-folder-modal').show();
        }
        
        /**
         * Create folder
         */
        createFolder() {
            const folderName = $('#file-explorer-folder-name').val().trim();
            
            if (!folderName) {
                this.showError(fileExplorerAdmin.strings.createFolder || 'Please enter a folder name');
                return;
            }
            
            this.closeModals();
            this.showLoading(true);
            
            $.ajax({
                url: fileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'file_explorer_create_folder',
                    nonce: fileExplorerAdmin.nonce,
                    folder_name: folderName,
                    parent_path: this.currentPath
                },
                success: (response) => {
                    if (response.success) {
                        // Reload items
                        this.currentPage = 1;
                        this.loadItems();
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(fileExplorerAdmin.strings.error);
                },
                complete: () => {
                    this.showLoading(false);
                }
            });
        }
        
        /**
         * Show WordPress media uploader
         */
        showUploadModal() {
            // Create a new media frame
            const mediaFrame = wp.media({
                title: fileExplorerAdmin.strings.uploadFiles || 'Upload Files',
                button: {
                    text: fileExplorerAdmin.strings.upload || 'Upload'
                },
                multiple: true
            });
            
            // When files are selected, handle the upload
            mediaFrame.on('select', () => {
                const selection = mediaFrame.state().get('selection');
                const files = [];
                
                selection.forEach((attachment) => {
                    const fileData = attachment.toJSON();
                    files.push({
                        id: fileData.id,
                        url: fileData.url,
                        name: fileData.filename
                    });
                });
                
                if (files.length > 0) {
                    this.copyMediaFilesToFolder(files);
                }
            });
            
            // Open the media frame
            mediaFrame.open();
        }
        
        /**
         * Copy media files to the current folder
         */
        copyMediaFilesToFolder(files) {
            if (!files || !files.length) {
                return;
            }
            
            this.showLoading(true);
            
            $.ajax({
                url: fileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'file_explorer_copy_media_to_folder',
                    nonce: fileExplorerAdmin.nonce,
                    files: JSON.stringify(files),
                    folder_path: this.currentPath
                },
                success: (response) => {
                    if (response.success) {
                        // Reload items
                        this.currentPage = 1;
                        this.loadItems();
                        this.showSuccess(fileExplorerAdmin.strings.uploadSuccess || 'Files uploaded successfully');
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(fileExplorerAdmin.strings.error || 'An error occurred');
                },
                complete: () => {
                    this.showLoading(false);
                }
            });
        }
        
        /**
         * Upload selected files
         */
        uploadSelectedFiles() {
            if (!this.uploadFiles.length) {
                this.showError(fileExplorerAdmin.strings.selectFiles || 'Please select files to upload');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'file_explorer_upload_files');
            formData.append('nonce', fileExplorerAdmin.nonce);
            formData.append('folder_path', this.currentPath);
            
            this.uploadFiles.forEach((file) => {
                formData.append('files[]', file);
            });
            
            const $progress = $('#file-explorer-upload-progress');
            const $progressBar = $('.file-explorer-upload-progress-bar');
            const $progressText = $('.file-explorer-upload-progress-text');
            
            $progress.show();
            $progressBar.width('0%');
            $progressText.text('0%');
            
            $.ajax({
                url: fileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: () => {
                    const xhr = new window.XMLHttpRequest();
                    
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            $progressBar.width(percentComplete + '%');
                            $progressText.text(percentComplete + '%');
                        }
                    }, false);
                    
                    return xhr;
                },
                success: (response) => {
                    if (response.success) {
                        // Show success/error for each file
                        response.data.files.forEach((file) => {
                            $('.file-explorer-upload-file').each(function() {
                                const $this = $(this);
                                if ($this.find('.file-explorer-upload-file-name').text() === file.name) {
                                    $this.find('.file-explorer-upload-file-status')
                                        .addClass('success')
                                        .text(fileExplorerAdmin.strings.uploaded);
                                }
                            });
                        });
                        
                        if (response.data.errors && response.data.errors.length) {
                            response.data.errors.forEach((error) => {
                                // Find the file by name in the error message
                                const match = error.match(/Error uploading (.+?):/);
                                if (match && match[1]) {
                                    const fileName = match[1];
                                    $('.file-explorer-upload-file').each(function() {
                                        const $this = $(this);
                                        if ($this.find('.file-explorer-upload-file-name').text() === fileName) {
                                            $this.find('.file-explorer-upload-file-status')
                                                .addClass('error')
                                                .text(error.replace(`Error uploading ${fileName}: `, ''));
                                        }
                                    });
                                }
                            });
                        }
                        
                        // Reload items after a short delay
                        setTimeout(() => {
                            this.closeModals();
                            this.currentPage = 1;
                            this.loadItems();
                        }, 1500);
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(fileExplorerAdmin.strings.error);
                }
            });
        }
        
        /**
         * Open media library
         */
        openMediaLibrary() {
            const frame = wp.media({
                title: fileExplorerAdmin.strings.selectFiles,
                button: {
                    text: fileExplorerAdmin.strings.addToFileExplorer
                },
                multiple: true
            });
            
            frame.on('select', () => {
                const selection = frame.state().get('selection');
                const mediaIds = selection.pluck('id');
                
                if (!mediaIds.length) {
                    this.showError(fileExplorerAdmin.strings.selectFiles || 'Please select files from the media library');
                    return;
                }
                
                this.showLoading(true);
                
                $.ajax({
                    url: fileExplorerAdmin.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'file_explorer_add_media_files',
                        nonce: fileExplorerAdmin.nonce,
                        folder_path: this.currentPath,
                        media_ids: mediaIds
                    },
                    success: (response) => {
                        if (response.success) {
                            // Reload items
                            this.currentPage = 1;
                            this.loadItems();
                        } else {
                            this.showError(response.data);
                        }
                    },
                    error: () => {
                        this.showError(fileExplorerAdmin.strings.error);
                    },
                    complete: () => {
                        this.showLoading(false);
                    }
                });
            });
            
            frame.open();
        }
        
        /**
         * Close modals
         */
        closeModals() {
            $('.file-explorer-modal').hide();
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        new FileExplorer();
    });
    
})(jQuery);
