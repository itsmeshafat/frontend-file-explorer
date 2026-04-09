/**
 * File Explorer Frontend JavaScript
 */
(function ($) {
    'use strict';

    class FrontendFileExplorerFrontend {
        constructor() {
            this.currentPath = frontendFileExplorerFrontendConfig.folder || '/';
            this.currentPage = 1;
            this.hasMoreItems = false;
            this.isLoading = false;

            this.$container = $('.frontend-file-explorer-frontend-container');
            this.$items = $('#frontend-file-explorer-frontend-items');
            this.$empty = $('#frontend-file-explorer-frontend-empty');
            this.$loading = $('#frontend-file-explorer-frontend-loading');
            this.$currentPath = $('#frontend-file-explorer-frontend-current-path');
            this.$loadMore = $('#frontend-file-explorer-frontend-load-more');

            this.folderTemplate = wp.template('file-explorer-frontend-folder');
            this.fileTemplate = wp.template('file-explorer-frontend-file');

            this.init();
        }

        init() {
            this.bindEvents();
            this.loadItems();
        }

        bindEvents() {
            $('#frontend-file-explorer-frontend-home').on('click', this.navigateHome.bind(this));
            $('#frontend-file-explorer-frontend-back').on('click', this.navigateBack.bind(this));

            this.$loadMore.on('click', this.loadMoreItems.bind(this));

            $('#frontend-file-explorer-frontend-download-zip').on('click', this.handleCurrentFolderDownloadZip.bind(this));

            this.$items.on('click', '.frontend-file-explorer-folder', this.handleFolderClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-open', this.handleOpenClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-download-zip', this.handleDownloadZipClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-download', this.handleDownloadClick.bind(this));
            this.$items.on('click', '.frontend-file-explorer-action-copy-link', this.handleCopyLinkClick.bind(this));
        }

        loadItems() {
            if (this.isLoading) return;

            this.isLoading = true;
            this.showLoading(true);

            $.ajax({
                url: frontendFileExplorerFrontendConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_frontend_get_folder_contents',
                    nonce: frontendFileExplorerFrontendConfig.nonce,
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
                    this.showError(frontendFileExplorerFrontendConfig.strings.error);
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
                    html = this.folderTemplate(item);
                } else {
                    html = this.fileTemplate(item);
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

            $('#frontend-file-explorer-frontend-download-zip').toggle(path !== '/');
        }

        showLoading(show) {
            this.$loading.toggle(show);
        }

        showError(message) {
            this.showNotification(message, 'error');
        }

        showSuccess(message) {
            this.showNotification(message, 'success');
        }

        showNotification(message, type) {
            $('.frontend-file-explorer-notification').remove();

            var $notification = $('<div class="frontend-file-explorer-notification ' + type + '">' + $('<div>').text(message).html() + '</div>');
            $('body').append($notification);

            setTimeout(() => {
                $notification.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
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
                frontendFileExplorerFrontendConfig.ajaxUrl +
                '?action=frontend_file_explorer_frontend_download_as_zip' +
                '&nonce=' + encodeURIComponent(frontendFileExplorerFrontendConfig.nonce) +
                '&path=' + encodeURIComponent(path),
                '_blank'
            );
        }

        handleCurrentFolderDownloadZip(e) {
            e.preventDefault();

            var path = String(this.currentPath || '');

            if (!path || path.indexOf('..') !== -1) return;

            window.open(
                frontendFileExplorerFrontendConfig.ajaxUrl +
                '?action=frontend_file_explorer_frontend_download_as_zip' +
                '&nonce=' + encodeURIComponent(frontendFileExplorerFrontendConfig.nonce) +
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
            link.href = frontendFileExplorerFrontendConfig.uploadsUrl + path;
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
                url: frontendFileExplorerFrontendConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'frontend_file_explorer_frontend_get_file_link',
                    nonce: frontendFileExplorerFrontendConfig.nonce,
                    path: path
                },
                success: (response) => {
                    if (response.success) {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(response.data).then(() => {
                                this.showSuccess(frontendFileExplorerFrontendConfig.strings.copySuccess);
                            });
                        } else {
                            var tempInput = document.createElement('input');
                            document.body.appendChild(tempInput);
                            tempInput.value = response.data;
                            tempInput.select();
                            document.execCommand('copy');
                            document.body.removeChild(tempInput);
                            this.showSuccess(frontendFileExplorerFrontendConfig.strings.copySuccess);
                        }
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(frontendFileExplorerFrontendConfig.strings.error);
                }
            });
        }
    }

    $(document).ready(function () {
        new FrontendFileExplorerFrontend();
    });

})(jQuery);
