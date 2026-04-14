/**
 * File Explorer Admin JavaScript
 */
(function ($) {
    'use strict';

    class Frontend_File_Explorer_Admin {
        constructor() {
            this.currentPath = '/';
            this.currentPage = 1;
            this.hasMoreItems = false;
            this.isLoading = false;
            this.uploadFiles = [];

            this.$container = $('.frontend-file-explorer-container');
            this.$items = $('#frontend-file-explorer-items');
            this.$empty = $('#frontend-file-explorer-empty');
            this.$loading = $('#frontend-file-explorer-loading');
            this.$currentPath = $('#frontend-file-explorer-current-path');
            this.$loadMore = $('#frontend-file-explorer-load-more');

            this.folderTemplate = wp.template('frontend-file-explorer-folder');
            this.fileTemplate = wp.template('frontend-file-explorer-file');

            this.init();
        }

        init() {
            this.bindEvents();
            this.loadItems();
        }

        bindEvents() {
            $('#frontend-file-explorer-home').on('click', this.navigateHome.bind(this));
            $('#frontend-file-explorer-back').on('click', this.navigateBack.bind(this));

            $('#frontend-file-explorer-create-folder').on('click', this.showCreateFolderModal.bind(this));
            $('#frontend-file-explorer-upload-files').on('click', this.showUploadModal.bind(this));
            $('#frontend-file-explorer-select-media').on('click', this.openMediaLibrary.bind(this));

            this.$loadMore.on('click', this.loadMoreItems.bind(this));

            this.$items.on('click', '.frontend-file-explorer-folder', this.handleFolderClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-open', this.handleOpenClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-download-zip', this.handleDownloadZipClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-download', this.handleDownloadClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-copy-link', this.handleCopyLinkClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-delete', this.handleDeleteClick.bind(this));

            $('#frontend-file-explorer-create-folder-submit').on('click', this.createFolder.bind(this));
            $('#frontend-file-explorer-create-folder-cancel, .frontend-file-explorer-modal-close').on('click', this.closeModals.bind(this));

            $('#frontend-file-explorer-upload-submit').on('click', this.uploadSelectedFiles.bind(this));
            $('#frontend-file-explorer-upload-cancel, .frontend-file-explorer-modal-close').on('click', this.closeModals.bind(this));

            var $dropzone = $('#frontend-file-explorer-dropzone');
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

                var files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    this.handleFileSelection({ target: { files: files } });
                }
            });
        }

        loadItems() {
            if (this.isLoading) return;

            this.isLoading = true;
            this.showLoading(true);

            $.ajax({
                url: frontendFileExplorerAdminConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_get_folder_contents',
                    nonce: frontendFileExplorerAdminConfig.nonce,
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
                    this.showError(frontendFileExplorerAdminConfig.strings.error);
                },
                complete: () => {
                    this.isLoading = false;
                    this.showLoading(false);
                }
            });
        }

        renderItems(data, append) {
            if (!append) {
                this.$items.empty();
            }

            var items = data.items || [];

            if (items.length === 0 && !append) {
                this.$items.hide();
                this.$empty.show();
                return;
            }

            this.$items.show();
            this.$empty.hide();

            items.forEach((item) => {
                var html = '';

                if (item.type === 'folder') {
                    if (this.folderTemplate) {
                        html = this.folderTemplate(item);
                    }
                } else {
                    if (this.fileTemplate) {
                        html = this.fileTemplate(item);
                    }
                }

                this.$items.append(html);
            });
        }

        loadMoreItems() {
            if (this.isLoading || !this.hasMoreItems) return;

            this.currentPage++;
            this.loadItems();
        }

        navigateHome() {
            this.currentPath = '/';
            this.currentPage = 1;
            this.loadItems();
        }

        navigateBack() {
            if (this.currentPath === '/') return;

            var parts = this.currentPath.split('/').filter(Boolean);
            parts.pop();

            this.currentPath = parts.length ? '/' + parts.join('/') : '/';
            this.currentPage = 1;
            this.loadItems();
        }

        updatePath(path) {
            this.currentPath = path;
            this.$currentPath.text(path);
        }

        showLoading(show) {
            this.$loading.toggle(show);
        }

        showError(message) {
            alert(message);
        }

        showSuccess(message) {
            alert(message);
        }

        handleFolderClick(e) {
            if ($(e.target).closest('.frontend-file-explorer-item-actions').length) {
                return;
            }

            var $folder = $(e.currentTarget);
            var path = $folder.data('path');

            this.currentPath = path;
            this.currentPage = 1;
            this.loadItems();
        }

        handleOpenClick(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(e.target).closest('.frontend-file-explorer-item');
            var path = $item.data('path');
            var type = $item.data('type');

            if (type === 'folder') {
                this.currentPath = path;
                this.currentPage = 1;
                this.loadItems();
            }
        }

        handleDownloadZipClick(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(e.target).closest('.frontend-file-explorer-item');
            var path = String($item.data('path') || '');

            if (!path || path.indexOf('..') !== -1) return;

            window.open(
                frontendFileExplorerAdminConfig.ajaxUrl +
                '?action=frontend_file_explorer_download_as_zip' +
                '&nonce=' + encodeURIComponent(frontendFileExplorerAdminConfig.nonce) +
                '&path=' + encodeURIComponent(path),
                '_blank'
            );
        }

        handleDownloadClick(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(e.target).closest('.frontend-file-explorer-item');
            var path = String($item.data('path') || '');

            if (!path || path.indexOf('..') !== -1) return;

            var link = document.createElement('a');
            link.href = frontendFileExplorerAdminConfig.uploadsUrl + path;
            link.download = path.split('/').pop();
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        handleCopyLinkClick(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(e.target).closest('.frontend-file-explorer-item');
            var path = $item.data('path');

            $.ajax({
                url: frontendFileExplorerAdminConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_get_file_link',
                    nonce: frontendFileExplorerAdminConfig.nonce,
                    path: path
                },
                success: (response) => {
                    if (response.success) {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(response.data).then(() => {
                                this.showSuccess(frontendFileExplorerAdminConfig.strings.copySuccess);
                            });
                        } else {
                            var tempInput = document.createElement('input');
                            document.body.appendChild(tempInput);
                            tempInput.value = response.data;
                            tempInput.select();
                            document.execCommand('copy');
                            document.body.removeChild(tempInput);
                            this.showSuccess(frontendFileExplorerAdminConfig.strings.copySuccess);
                        }
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(frontendFileExplorerAdminConfig.strings.error);
                }
            });
        }

        handleDeleteClick(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(e.target).closest('.frontend-file-explorer-item');
            var path = $item.data('path');
            var type = $item.data('type');
            var name = $item.find('.frontend-file-explorer-item-name').text();

            if (!confirm(frontendFileExplorerAdminConfig.strings.confirmDelete)) {
                return;
            }

            this.showLoading(true);

            $.ajax({
                url: frontendFileExplorerAdminConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_delete_item',
                    nonce: frontendFileExplorerAdminConfig.nonce,
                    path: path,
                    type: type
                },
                success: (response) => {
                    if (response.success) {
                        this.currentPage = 1;
                        this.loadItems();
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(frontendFileExplorerAdminConfig.strings.error);
                },
                complete: () => {
                    this.showLoading(false);
                }
            });
        }

        showCreateFolderModal() {
            $('#frontend-file-explorer-folder-name').val('');
            $('#frontend-file-explorer-create-folder-modal').show();
        }

        createFolder() {
            var folderName = $('#frontend-file-explorer-folder-name').val().trim();

            if (!folderName) {
                this.showError(frontendFileExplorerAdminConfig.strings.createFolder || 'Please enter a folder name');
                return;
            }

            this.closeModals();
            this.showLoading(true);

            $.ajax({
                url: frontendFileExplorerAdminConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_create_folder',
                    nonce: frontendFileExplorerAdminConfig.nonce,
                    folder_name: folderName,
                    parent_path: this.currentPath
                },
                success: (response) => {
                    if (response.success) {
                        this.currentPage = 1;
                        this.loadItems();
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(frontendFileExplorerAdminConfig.strings.error);
                },
                complete: () => {
                    this.showLoading(false);
                }
            });
        }

        showUploadModal() {
            var mediaFrame = wp.media({
                title: frontendFileExplorerAdminConfig.strings.uploadFiles || 'Upload Files',
                button: {
                    text: frontendFileExplorerAdminConfig.strings.upload || 'Upload'
                },
                multiple: true
            });

            mediaFrame.on('select', () => {
                var selection = mediaFrame.state().get('selection');
                var mediaIds = [];

                selection.forEach((attachment) => {
                    mediaIds.push(attachment.get('id'));
                });

                if (mediaIds.length > 0) {
                    this.addMediaFilesToFolder(mediaIds);
                }
            });

            mediaFrame.open();
        }

        addMediaFilesToFolder(mediaIds) {
            if (!mediaIds || !mediaIds.length) {
                return;
            }

            this.showLoading(true);

            $.ajax({
                url: frontendFileExplorerAdminConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_add_media_files',
                    nonce: frontendFileExplorerAdminConfig.nonce,
                    media_ids: mediaIds,
                    folder_path: this.currentPath
                },
                success: (response) => {
                    if (response.success) {
                        this.currentPage = 1;
                        this.loadItems();
                        this.showSuccess(response.data || frontendFileExplorerAdminConfig.strings.uploadSuccess || 'Files uploaded successfully');
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(frontendFileExplorerAdminConfig.strings.error || 'An error occurred');
                },
                complete: () => {
                    this.showLoading(false);
                }
            });
        }

        uploadSelectedFiles() {
            if (!this.uploadFiles.length) {
                this.showError(frontendFileExplorerAdminConfig.strings.selectFiles || 'Please select files to upload');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'frontend_file_explorer_upload_files');
            formData.append('nonce', frontendFileExplorerAdminConfig.nonce);
            formData.append('folder_path', this.currentPath);

            this.uploadFiles.forEach((file) => {
                formData.append('files[]', file);
            });

            var $progress = $('#frontend-file-explorer-upload-progress');
            var $progressBar = $('.frontend-file-explorer-upload-progress-bar');
            var $progressText = $('.frontend-file-explorer-upload-progress-text');

            $progress.show();
            $progressBar.width('0%');
            $progressText.text('0%');

            $.ajax({
                url: frontendFileExplorerAdminConfig.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: () => {
                    var xhr = new window.XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            var percentComplete = Math.round((e.loaded / e.total) * 100);
                            $progressBar.width(percentComplete + '%');
                            $progressText.text(percentComplete + '%');
                        }
                    }, false);

                    return xhr;
                },
                success: (response) => {
                    if (response.success) {
                        response.data.files.forEach((file) => {
                            $('.frontend-file-explorer-upload-file').each(function () {
                                var $this = $(this);
                                if ($this.find('.frontend-file-explorer-upload-file-name').text() === file.name) {
                                    $this.find('.frontend-file-explorer-upload-file-status')
                                        .addClass('success')
                                        .text(frontendFileExplorerAdminConfig.strings.uploaded);
                                }
                            });
                        });

                        if (response.data.errors && response.data.errors.length) {
                            response.data.errors.forEach((error) => {
                                var match = error.match(/Error uploading (.+?):/);
                                if (match && match[1]) {
                                    var fileName = match[1];
                                    $('.frontend-file-explorer-upload-file').each(function () {
                                        var $this = $(this);
                                        if ($this.find('.frontend-file-explorer-upload-file-name').text() === fileName) {
                                            $this.find('.frontend-file-explorer-upload-file-status')
                                                .addClass('error')
                                                .text(error.replace('Error uploading ' + fileName + ': ', ''));
                                        }
                                    });
                                }
                            });
                        }

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
                    this.showError(frontendFileExplorerAdminConfig.strings.error);
                }
            });
        }

        openMediaLibrary() {
            var frame = wp.media({
                title: frontendFileExplorerAdminConfig.strings.selectFiles,
                button: {
                    text: frontendFileExplorerAdminConfig.strings.addToFileExplorer
                },
                multiple: true
            });

            frame.on('select', () => {
                var selection = frame.state().get('selection');
                var mediaIds = selection.pluck('id');

                if (!mediaIds.length) {
                    this.showError(frontendFileExplorerAdminConfig.strings.selectFiles || 'Please select files from the media library');
                    return;
                }

                this.addMediaFilesToFolder(mediaIds);
            });

            frame.open();
        }

        closeModals() {
            $('.frontend-file-explorer-modal').hide();
        }
    }

    $(document).ready(function () {
        new Frontend_File_Explorer_Admin();
    });

})(jQuery);
