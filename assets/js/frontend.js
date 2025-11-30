/**
 * File Explorer Frontend JavaScript — engineered by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 */
(function($) {
    'use strict';

    // File Explorer Frontend class
    class FileExplorerFrontend {
        constructor() {
            // Properties
            this.currentPath = '/';
            this.currentPage = 1;
            this.hasMoreItems = false;
            this.isLoading = false;
            
            // DOM elements
            this.$container = $('.file-explorer-frontend-container');
            this.$items = $('#file-explorer-frontend-items');
            this.$empty = $('#file-explorer-frontend-empty');
            this.$loading = $('#file-explorer-frontend-loading');
            this.$currentPath = $('#file-explorer-frontend-current-path');
            this.$loadMore = $('#file-explorer-frontend-load-more');
            
            // Custom template function instead of wp.template
            this.folderTemplate = this.createTemplateFunction('tmpl-file-explorer-frontend-folder');
            this.fileTemplate = this.createTemplateFunction('tmpl-file-explorer-frontend-file');
            
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
         * Create a template function similar to wp.template
         */
        createTemplateFunction(templateId) {
            const templateElement = document.getElementById(templateId);
            if (!templateElement) {
                console.error('Template not found:', templateId);
                return data => '';
            }
            
            const templateString = templateElement.innerHTML;
            
            return function(data) {
                let html = templateString;
                
                // Replace {{ data.xxx }} with actual data
                html = html.replace(/\{\{\s*data\.(\w+)\s*\}\}/g, (match, key) => {
                    return data[key] || '';
                });
                
                // Handle conditionals <# if (condition) { #> content <# } #>
                html = html.replace(/\<\#\s*if\s*\((.+?)\)\s*\{\s*\#\>([\s\S]*?)\<\#\s*\}\s*\#\>/g, (match, condition, content) => {
                    // Replace data.xxx with actual data in the condition
                    const processedCondition = condition.replace(/data\.(\w+)/g, (match, key) => {
                        return `"${data[key] || ''}"` ;
                    });
                    
                    try {
                        return eval(processedCondition) ? content : '';
                    } catch (e) {
                        console.error('Error evaluating condition:', condition, e);
                        return '';
                    }
                });
                
                // Handle else if <# } else if (condition) { #>
                html = html.replace(/\<\#\s*\}\s*else\s*if\s*\((.+?)\)\s*\{\s*\#\>([\s\S]*?)/g, (match, condition, content) => {
                    // Replace data.xxx with actual data in the condition
                    const processedCondition = condition.replace(/data\.(\w+)/g, (match, key) => {
                        return `"${data[key] || ''}"` ;
                    });
                    
                    try {
                        return eval(processedCondition) ? content : '';
                    } catch (e) {
                        console.error('Error evaluating condition:', condition, e);
                        return '';
                    }
                });
                
                // Handle else <# } else { #>
                html = html.replace(/\<\#\s*\}\s*else\s*\{\s*\#\>([\s\S]*?)/g, (match, content) => {
                    return content;
                });
                
                return html;
            };
        }
        
        /**
         * Bind events
         */
        bindEvents() {
            // Navigation
            $('#file-explorer-frontend-home').on('click', this.navigateHome.bind(this));
            $('#file-explorer-frontend-back').on('click', this.navigateBack.bind(this));
            
            // Load more
            this.$loadMore.on('click', this.loadMoreItems.bind(this));
            
            // Download current folder as ZIP
            $('#file-explorer-frontend-download-zip').on('click', this.handleCurrentFolderDownloadZip.bind(this));
            
            // Item click events (delegation)
            this.$items.on('click', '.file-explorer-folder', this.handleFolderClick.bind(this));
            this.$items.on('click', '.file-explorer-action-open', this.handleOpenClick.bind(this));
            this.$items.on('click', '.file-explorer-action-download-zip', this.handleDownloadZipClick.bind(this));
            this.$items.on('click', '.file-explorer-action-download', this.handleDownloadClick.bind(this));
            this.$items.on('click', '.file-explorer-action-copy-link', this.handleCopyLinkClick.bind(this));
        }
        
        /**
         * Load items
         */
        loadItems() {
            if (this.isLoading) return;
            
            console.log('Loading items for path:', this.currentPath, 'page:', this.currentPage);
            
            this.isLoading = true;
            this.showLoading(true);
            
            $.ajax({
                url: fileExplorerFrontend.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'file_explorer_frontend_get_folder_contents',
                    nonce: fileExplorerFrontend.nonce,
                    path: this.currentPath,
                    page: this.currentPage
                },
                success: (response) => {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        this.renderItems(response.data, this.currentPage > 1);
                        this.updatePath(response.data.current_path);
                        this.hasMoreItems = response.data.pagination.has_more;
                        this.$loadMore.toggle(this.hasMoreItems);
                    } else {
                        console.error('AJAX error response:', response.data);
                        this.showError(response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error:', status, error);
                    this.showError(fileExplorerFrontend.strings.error);
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
            
            // Show the download ZIP button only when we're not at the root
            $('#file-explorer-frontend-download-zip').toggle(path !== '/');
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
            this.showNotification(message, 'error');
        }
        
        /**
         * Show success
         */
        showSuccess(message) {
            this.showNotification(message, 'success');
        }
        
        /**
         * Show notification
         */
        showNotification(message, type = 'info') {
            // Remove any existing notifications
            $('.file-explorer-notification').remove();
            
            const $notification = $('<div class="file-explorer-notification ' + type + '">' + message + '</div>');
            $('body').append($notification);
            
            setTimeout(() => {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
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
                fileExplorerFrontend.ajaxUrl + 
                '?action=file_explorer_frontend_download_as_zip' + 
                '&nonce=' + fileExplorerFrontend.nonce + 
                '&path=' + encodeURIComponent(path),
                '_blank'
            );
        }
        
        /**
         * Handle current folder download as ZIP
         */
        handleCurrentFolderDownloadZip(e) {
            e.preventDefault();
            
            // Open download in new tab
            window.open(
                fileExplorerFrontend.ajaxUrl + 
                '?action=file_explorer_frontend_download_as_zip' + 
                '&nonce=' + fileExplorerFrontend.nonce + 
                '&path=' + encodeURIComponent(this.currentPath),
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
            link.href = fileExplorerFrontend.uploadsUrl + path;
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
                url: fileExplorerFrontend.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'file_explorer_frontend_get_file_link',
                    nonce: fileExplorerFrontend.nonce,
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
                        
                        this.showSuccess(fileExplorerFrontend.strings.copySuccess);
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(fileExplorerFrontend.strings.error);
                }
            });
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        console.log('File Explorer Frontend initializing...');
        try {
            new FileExplorerFrontend();
            console.log('File Explorer Frontend initialized successfully');
        } catch (error) {
            console.error('Error initializing File Explorer Frontend:', error);
        }
    });
    
})(jQuery);
