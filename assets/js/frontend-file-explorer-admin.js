/**
 * File Explorer Admin JavaScript — engineered by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 */
(function ($) {
    'use strict';

    // Frontend File Explorer class
    class FrontendFileExplorer {
        constructor() {
            // Properties
            this.currentPath = '/';
            this.currentPage = 1;
            this.hasMoreItems = false;
            this.isLoading = false;
            this.uploadFiles = [];

            // DOM elements
            this.$container = $('.frontend-file-explorer-container');
            this.$items = $('#frontend-file-explorer-items');
            this.$empty = $('#frontend-file-explorer-empty');
            this.$loading = $('#frontend-file-explorer-loading');
            this.$currentPath = $('#frontend-file-explorer-current-path');
            this.$loadMore = $('#frontend-file-explorer-load-more');

            // Templates will be initialized after DOM is ready
            this.folderTemplate = null;
            this.fileTemplate = null;

            // Initialize
            this.init();
        }

        /**
         * Initialize
         */
        init() {
            // Initialize templates after DOM is ready using custom template function
            this.folderTemplate = this.createTemplateFunction('tmpl-frontend-file-explorer-folder');
            this.fileTemplate = this.createTemplateFunction('tmpl-frontend-file-explorer-file');
            
            // Templates initialized successfully
            
            this.bindEvents();
            this.loadItems();
        }

        /**
         * Create a template function similar to wp.template
         */
        createTemplateFunction(templateId) {
            const templateElement = document.getElementById(templateId);
            if (!templateElement) {
                console.error('Template not found:', templateId);
                return data => '';
            }

            // Special handling for file template with conditional logic
            if (templateId === 'tmpl-frontend-file-explorer-file') {
                return function (data) {
                    let iconHtml = '';
                    
                    // Handle file icon based on extension
                    if (data.extension === 'jpg' || data.extension === 'jpeg' || data.extension === 'png' || data.extension === 'gif') {
                        iconHtml = `<div class="frontend-file-explorer-item-preview">
                            <img src="${data.url || ''}" alt="${data.name || ''}">
                        </div>`;
                    } else if (data.extension === 'pdf') {
                        iconHtml = `<div class="frontend-file-explorer-item-icon">
                            <span class="material-icons">picture_as_pdf</span>
                        </div>`;
                    } else if (data.extension === 'doc' || data.extension === 'docx') {
                        iconHtml = `<div class="frontend-file-explorer-item-icon">
                            <span class="material-icons">description</span>
                        </div>`;
                    } else if (data.extension === 'xls' || data.extension === 'xlsx') {
                        iconHtml = `<div class="frontend-file-explorer-item-icon">
                            <span class="material-icons">table_chart</span>
                        </div>`;
                    } else if (data.extension === 'zip' || data.extension === 'rar') {
                        iconHtml = `<div class="frontend-file-explorer-item-icon">
                            <span class="material-icons">folder_zip</span>
                        </div>`;
                    } else {
                        iconHtml = `<div class="frontend-file-explorer-item-icon">
                            <span class="material-icons">insert_drive_file</span>
                        </div>`;
                    }
                    
                    return `<div class="frontend-file-explorer-item frontend-file-explorer-file" data-path="${data.path || ''}" data-type="file">
                        ${iconHtml}
                        <div class="frontend-file-explorer-item-name">${data.name || ''}</div>
                        <div class="frontend-file-explorer-item-actions">
                            <button type="button" class="frontend-file-explorer-action-download" title="Download">
                                <span class="material-icons">download</span>
                            </button>
                            <button type="button" class="frontend-file-explorer-action-copy-link" title="Copy Link">
                                <span class="material-icons">link</span>
                            </button>
                            <button type="button" class="frontend-file-explorer-action-delete" title="Delete">
                                <span class="material-icons">delete</span>
                            </button>
                        </div>
                    </div>`;
                };
            }
            
            // For folder template, use simple replacement
            if (templateId === 'tmpl-frontend-file-explorer-folder') {
                return function (data) {
                    return `<div class="frontend-file-explorer-item frontend-file-explorer-folder" data-path="${data.path || ''}" data-type="folder">
                        <div class="frontend-file-explorer-item-icon">
                            <span class="material-icons">folder</span>
                        </div>
                        <div class="frontend-file-explorer-item-name">${data.name || ''}</div>
                        <div class="frontend-file-explorer-item-actions">
                            <button type="button" class="frontend-file-explorer-action-open" title="Open">
                                <span class="material-icons">open_in_new</span>
                            </button>
                            <button type="button" class="frontend-file-explorer-action-download-zip" title="Download as ZIP">
                                <span class="material-icons">download</span>
                            </button>
                            <button type="button" class="frontend-file-explorer-action-delete" title="Delete">
                                <span class="material-icons">delete</span>
                            </button>
                        </div>
                    </div>`;
                };
            }

            // Fallback for any other templates
            const templateString = templateElement.innerHTML;
            return function (data) {
                let html = templateString;
                html = html.replace(/\{\{\s*data\.(\w+)\s*\}\}/g, (match, key) => {
                    return data[key] || '';
                });
                return html;
            };
        }

        /**
         * Bind events
         */
        bindEvents() {
            // Navigation
            $('#frontend-file-explorer-home').on('click', this.navigateHome.bind(this));
            $('#frontend-file-explorer-back').on('click', this.navigateBack.bind(this));

            // Actions
            $('#frontend-file-explorer-create-folder').on('click', this.showCreateFolderModal.bind(this));
            $('#frontend-file-explorer-upload-files').on('click', this.showUploadModal.bind(this));
            $('#frontend-file-explorer-select-media').on('click', this.openMediaLibrary.bind(this));

            // Load more
            this.$loadMore.on('click', this.loadMoreItems.bind(this));

            // Item click events (delegation)
            this.$items.on('click', '.frontend-file-explorer-folder', this.handleFolderClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-open', this.handleOpenClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-download-zip', this.handleDownloadZipClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-download', this.handleDownloadClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-copy-link', this.handleCopyLinkClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-delete', this.handleDeleteClick.bind(this));

            // Create folder modal
            $('#frontend-file-explorer-create-folder-submit').on('click', this.createFolder.bind(this));
            $('#frontend-file-explorer-create-folder-cancel, .frontend-file-explorer-modal-close').on('click', this.closeModals.bind(this));

            // Upload modal
            // File input handlers are now set in the showUploadModal method
            $('#frontend-file-explorer-upload-submit').on('click', this.uploadSelectedFiles.bind(this));
            $('#frontend-file-explorer-upload-cancel, .frontend-file-explorer-modal-close').on('click', this.closeModals.bind(this));

            // Drag and drop
            const $dropzone = $('#frontend-file-explorer-dropzone');
            $dropzone.on('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });

            $dropzone.on('dragleave', function (e) {
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
                url: frontendFileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_get_folder_contents',
                    nonce: frontendFileExplorerAdmin.nonce,
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
                    this.showError(frontendFileExplorerAdmin.strings.error);
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
                    if (this.folderTemplate) {
                        html = this.folderTemplate(item);
                    } else {
                        console.error('Folder template not initialized');
                        return;
                    }
                } else {
                    if (this.fileTemplate) {
                        html = this.fileTemplate(item);
                    } else {
                        console.error('File template not initialized');
                        return;
                    }
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
            if ($(e.target).closest('.frontend-file-explorer-item-actions').length) {
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

            const $item = $(e.target).closest('.frontend-file-explorer-item');
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

            const $item = $(e.target).closest('.frontend-file-explorer-item');
            const path = $item.data('path');

            // Open download in new tab
            window.open(
                frontendFileExplorerAdmin.ajaxUrl +
                '?action=frontend_file_explorer_download_as_zip' +
                '&nonce=' + frontendFileExplorerAdmin.nonce +
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

            const $item = $(e.target).closest('.frontend-file-explorer-item');
            const path = $item.data('path');

            // Create a temporary link and click it
            const link = document.createElement('a');
            link.href = frontendFileExplorerAdmin.uploadsUrl + path;
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

            const $item = $(e.target).closest('.frontend-file-explorer-item');
            const path = $item.data('path');

            $.ajax({
                url: frontendFileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_get_file_link',
                    nonce: frontendFileExplorerAdmin.nonce,
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

                        this.showSuccess(frontendFileExplorerAdmin.strings.copySuccess);
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(frontendFileExplorerAdmin.strings.error);
                }
            });
        }

        /**
         * Handle delete click
         */
        handleDeleteClick(e) {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(e.target).closest('.frontend-file-explorer-item');
            const path = $item.data('path');
            const type = $item.data('type');
            const name = $item.find('.frontend-file-explorer-item-name').text();

            if (!confirm(frontendFileExplorerAdmin.strings.confirmDelete)) {
                return;
            }

            this.showLoading(true);

            $.ajax({
                url: frontendFileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_delete_item',
                    nonce: frontendFileExplorerAdmin.nonce,
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
                    this.showError(frontendFileExplorerAdmin.strings.error);
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
            $('#frontend-file-explorer-folder-name').val('');
            $('#frontend-file-explorer-create-folder-modal').show();
        }

        /**
         * Create folder
         */
        createFolder() {
            const folderName = $('#frontend-file-explorer-folder-name').val().trim();

            if (!folderName) {
                this.showError(frontendFileExplorerAdmin.strings.createFolder || 'Please enter a folder name');
                return;
            }

            this.closeModals();
            this.showLoading(true);

            $.ajax({
                url: frontendFileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_create_folder',
                    nonce: frontendFileExplorerAdmin.nonce,
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
                    this.showError(frontendFileExplorerAdmin.strings.error);
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
                title: frontendFileExplorerAdmin.strings.uploadFiles || 'Upload Files',
                button: {
                    text: frontendFileExplorerAdmin.strings.upload || 'Upload'
                },
                multiple: true
            });

            // When files are selected, handle the upload
            mediaFrame.on('select', () => {
                const selection = mediaFrame.state().get('selection');
                const mediaIds = [];

                selection.forEach((attachment) => {
                    mediaIds.push(attachment.get('id'));
                });

                if (mediaIds.length > 0) {
                    this.addMediaFilesToFolder(mediaIds);
                }
            });

            // Open the media frame
            mediaFrame.open();
        }

        /**
         * Add media files to the current folder (secure version using IDs)
         */
        addMediaFilesToFolder(mediaIds) {
            if (!mediaIds || !mediaIds.length) {
                return;
            }

            this.showLoading(true);

            $.ajax({
                url: frontendFileExplorerAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_add_media_files',
                    nonce: frontendFileExplorerAdmin.nonce,
                    media_ids: mediaIds,
                    folder_path: this.currentPath
                },
                success: (response) => {
                    if (response.success) {
                        // Reload items
                        this.currentPage = 1;
                        this.loadItems();
                        this.showSuccess(response.data || frontendFileExplorerAdmin.strings.uploadSuccess || 'Files uploaded successfully');
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(frontendFileExplorerAdmin.strings.error || 'An error occurred');
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
                this.showError(frontendFileExplorerAdmin.strings.selectFiles || 'Please select files to upload');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'frontend_file_explorer_upload_files');
            formData.append('nonce', frontendFileExplorerAdmin.nonce);
            formData.append('folder_path', this.currentPath);

            this.uploadFiles.forEach((file) => {
                formData.append('files[]', file);
            });

            const $progress = $('#frontend-file-explorer-upload-progress');
            const $progressBar = $('.frontend-file-explorer-upload-progress-bar');
            const $progressText = $('.frontend-file-explorer-upload-progress-text');

            $progress.show();
            $progressBar.width('0%');
            $progressText.text('0%');

            $.ajax({
                url: frontendFileExplorerAdmin.ajaxUrl,
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
                            $('.frontend-file-explorer-upload-file').each(function () {
                                const $this = $(this);
                                if ($this.find('.frontend-file-explorer-upload-file-name').text() === file.name) {
                                    $this.find('.frontend-file-explorer-upload-file-status')
                                        .addClass('success')
                                        .text(frontendFileExplorerAdmin.strings.uploaded);
                                }
                            });
                        });

                        if (response.data.errors && response.data.errors.length) {
                            response.data.errors.forEach((error) => {
                                // Find the file by name in the error message
                                const match = error.match(/Error uploading (.+?):/);
                                if (match && match[1]) {
                                    const fileName = match[1];
                                    $('.frontend-file-explorer-upload-file').each(function () {
                                        const $this = $(this);
                                        if ($this.find('.frontend-file-explorer-upload-file-name').text() === fileName) {
                                            $this.find('.frontend-file-explorer-upload-file-status')
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
                    this.showError(frontendFileExplorerAdmin.strings.error);
                }
            });
        }

        /**
         * Open media library
         */
        openMediaLibrary() {
            const frame = wp.media({
                title: frontendFileExplorerAdmin.strings.selectFiles,
                button: {
                    text: frontendFileExplorerAdmin.strings.addToFileExplorer
                },
                multiple: true
            });

            frame.on('select', () => {
                const selection = frame.state().get('selection');
                const mediaIds = selection.pluck('id');

                if (!mediaIds.length) {
                    this.showError(frontendFileExplorerAdmin.strings.selectFiles || 'Please select files from the media library');
                    return;
                }

                this.addMediaFilesToFolder(mediaIds);
            });

            frame.open();
        }

        /**
         * Close modals
         */
        closeModals() {
            $('.frontend-file-explorer-modal').hide();
        }
    }

    // Initialize when document is ready
    $(document).ready(function () {
        new FrontendFileExplorer();
    });

})(jQuery);
