// resources/js/modules/ui-components.js

import { NetworkUtils } from './utils.js';

/**
 * UI Components manager for modals, toasts, and other interface elements
 */
export class UIComponents {
    constructor() {
        this.activeToast = null;
        this.activeModal = null;
    }

    /**
     * Create and show a modal
     * @param {string} content - HTML content for the modal
     * @param {Object} options - Modal options
     * @returns {HTMLElement}
     */
    createModal(content, options = {}) {
        this.closeModal(); // Close any existing modal

        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease-out';

        const modalContent = document.createElement('div');
        modalContent.className = `bg-white rounded-lg p-6 max-w-md w-full mx-4 transform transition-all duration-300 ease-out ${options.size || ''}`;
        modalContent.style.opacity = '0';
        modalContent.style.transform = 'scale(0.95) translateY(-10px)';
        modalContent.innerHTML = content;

        modal.appendChild(modalContent);
        document.body.appendChild(modal);

        // Smooth animation in
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            modalContent.style.opacity = '1';
            modalContent.style.transform = 'scale(1) translateY(0)';
        });

        // Close on outside click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeModal();
            }
        });

        // Close on escape key
        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);

        this.activeModal = modal;
        return modal;
    }

    /**
     * Close the active modal
     */
    closeModal() {
        if (this.activeModal && this.activeModal.parentNode) {
            const modalContent = this.activeModal.querySelector('div > div');

            // Smooth animation out
            this.activeModal.style.opacity = '0';
            if (modalContent) {
                modalContent.style.opacity = '0';
                modalContent.style.transform = 'scale(0.95) translateY(-10px)';
            }

            setTimeout(() => {
                if (this.activeModal && this.activeModal.parentNode) {
                    this.activeModal.parentNode.removeChild(this.activeModal);
                }
                this.activeModal = null;
            }, 300);
        }
    }

    /**
     * Show a toast notification
     * @param {string} title
     * @param {string} message
     * @param {string} type
     * @param {number} duration
     */
    showToast(title, message = '', type = 'info', duration = 0) {
        this.hideToast(); // Hide any existing toast

        const colors = {
            success: 'bg-success text-white',
            error: 'bg-danger text-white',
            warning: 'bg-warning text-white',
            info: 'bg-primary text-white'
        };

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.id = 'networkToast';
        toast.className = `fixed z-50 top-4 left-1/2 transform ${colors[type]} px-6 py-3 rounded-lg shadow-lg z-[9999] transition-all duration-300 max-w-lg w-full mx-4`;
        toast.style.opacity = '0';

        const closeButton = `
            <button onclick="window.uiComponents?.hideToast()" 
                    class="ml-4 text-white hover:text-gray-200 transition-colors flex-shrink-0">
                <i class="fas fa-times"></i>
            </button>
        `;

        toast.innerHTML = `
            <div class="flex items-center">
                <i class="${icons[type]} mr-3 flex-shrink-0"></i>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold truncate">${title}</div>
                    ${message ? `<div class="text-sm opacity-90 break-words">${message}</div>` : ''}
                </div>
                ${closeButton}
            </div>
        `;

        document.body.appendChild(toast);

        // Store reference globally for close button
        window.uiComponents = this;

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
        });

        this.activeToast = toast;

        // Auto-hide if duration is specified
        if (duration > 0) {
            setTimeout(() => this.hideToast(), duration);
        } else if (type === 'success' || type === 'error') {
            setTimeout(() => this.hideToast(), 5000);
        }
    }

    /**
     * Hide the active toast
     */
    hideToast() {
        const toast = document.getElementById('networkToast');
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translate(-50%, -20px)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
        this.activeToast = null;
    }

    /**
     * Show connection confirmation modal
     * @param {string} inviterEmail
     * @param {string} inviteeEmail
     * @returns {Promise<Object>}
     */
    showConnectionConfirmation(inviterEmail, inviteeEmail) {
        return new Promise((resolve) => {
            const modalId = `modal-${Date.now()}`;
            const content = `
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-link text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">Create Referral Connection</h3>
                    <p class="text-text-secondary mb-6">
                        <strong>${inviterEmail}</strong> will invite <strong>${inviteeEmail}</strong>
                    </p>
                    
                    <div class="mb-6 text-left">
                        <label for="referralLink-${modalId}" class="block text-sm font-medium text-text-secondary mb-2">
                            Referral Link (Optional)
                        </label>
                        <input type="url" id="referralLink-${modalId}" placeholder="https://example.com/ref/..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:border-secondary focus:ring focus:ring-secondary/20">
                        <p class="text-xs text-text-secondary mt-1">This link will be visible when viewing account details</p>
                    </div>
                    
                    <div class="flex justify-center space-x-3">
                        <button id="cancelConnection-${modalId}" class="bg-gray-200 hover:bg-gray-300 text-text-primary px-4 py-2 rounded transition-colors">
                            Cancel
                        </button>
                        <button id="confirmConnection-${modalId}" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded transition-colors">
                            <i class="fas fa-check mr-2"></i>Create Connection
                        </button>
                    </div>
                </div>
            `;

            const modal = this.createModal(content, { size: 'max-w-lg' });

            const cancelBtn = document.getElementById(`cancelConnection-${modalId}`);
            const confirmBtn = document.getElementById(`confirmConnection-${modalId}`);
            const referralInput = document.getElementById(`referralLink-${modalId}`);

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    this.closeModal();
                    resolve({ confirmed: false, referralLink: null });
                });
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    const referralLink = referralInput?.value || null;
                    this.closeModal();
                    resolve({ confirmed: true, referralLink });
                });
            }
        });
    }

    /**
     * Show delete confirmation modal
     * @param {string} fromEmail
     * @param {string} toEmail
     * @returns {Promise<boolean>}
     */
    showDeleteConfirmation(fromEmail, toEmail) {
        return new Promise((resolve) => {
            const modalId = `modal-${Date.now()}`;
            const content = `
                <div class="text-center">
                    <div class="w-16 h-16 bg-danger/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trash text-danger text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">Delete Referral Connection</h3>
                    <p class="text-text-secondary mb-6">
                        Are you sure you want to delete the referral connection from 
                        <strong>${fromEmail}</strong> to <strong>${toEmail}</strong>?
                    </p>
                    <p class="text-sm text-text-secondary mb-6">This action cannot be undone.</p>
                    
                    <div class="flex justify-center space-x-3">
                        <button id="cancelDelete-${modalId}" class="bg-gray-200 hover:bg-gray-300 text-text-primary px-4 py-2 rounded transition-colors">
                            Cancel
                        </button>
                        <button id="confirmDelete-${modalId}" class="bg-danger hover:bg-danger/90 text-white px-4 py-2 rounded transition-colors">
                            <i class="fas fa-trash mr-2"></i>Delete Connection
                        </button>
                    </div>
                </div>
            `;

            const modal = this.createModal(content);

            const cancelBtn = document.getElementById(`cancelDelete-${modalId}`);
            const confirmBtn = document.getElementById(`confirmDelete-${modalId}`);

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    this.closeModal();
                    resolve(false);
                });
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    this.closeModal();
                    resolve(true);
                });
            }
        });
    }

    /**
     * Show node details modal
     * @param {Object} nodeData
     * @param {Array} sentInvitations
     * @param {Array} receivedInvitation
     */
    showNodeDetails(nodeData, sentInvitations = [], receivedInvitation = []) {
        const modalId = `modal-${Date.now()}`;
        const isInvited = receivedInvitation.length > 0;
        let referralLinkHtml = '';

        if (isInvited && receivedInvitation[0].data?.referral_link) {
            const link = receivedInvitation[0].data.referral_link;
            const displayLink = link.length > 30 ? link.substring(0, 30) + '...' : link;

            referralLinkHtml = `
                <div class="mt-3 p-3 bg-gray-50 rounded-md">
                    <div class="text-sm font-medium text-text-secondary mb-1">Referral Link:</div>
                    <div class="flex items-center">
                        <a href="${link}" target="_blank" class="text-primary hover:underline text-sm flex-1" title="${link}">
                            ${displayLink}
                        </a>
                        <button onclick="navigator.clipboard.writeText('${link}'); this.innerHTML='<i class=\\'fas fa-check\\'></i>'" 
                                class="ml-2 bg-primary hover:bg-primary/90 text-white px-2 py-1 rounded text-xs transition-colors">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        const content = `
            <div class="text-center">
                <div class="w-16 h-16 ${isInvited ? 'bg-success/10' : 'bg-secondary/10'} rounded-full flex items-center justify-center mx-auto mb-4">
                    <img src="${NetworkUtils.getProviderIcon(nodeData.provider)}" alt="${nodeData.provider}" class="w-10 h-10 rounded-full">
                </div>
                <h3 class="text-lg font-semibold text-text-primary mb-4">Account Details</h3>
                
                <div class="space-y-3 text-left">
                    <div class="flex justify-between">
                        <span class="text-text-secondary">Email:</span>
                        <span class="font-medium text-text-primary">${nodeData.email}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-secondary">Provider:</span>
                        <span class="font-medium text-text-primary">${nodeData.provider}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-secondary">Type:</span>
                        <span class="font-medium ${isInvited ? 'text-success' : 'text-secondary'}">
                            ${isInvited ? 'Invited Account' : 'Root Account'}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-secondary">Sent Invitations:</span>
                        <span class="font-medium text-text-primary">${sentInvitations.length}/5</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-secondary">Remaining Slots:</span>
                        <span class="font-medium ${5 - sentInvitations.length > 0 ? 'text-success' : 'text-danger'}">
                            ${5 - sentInvitations.length}
                        </span>
                    </div>
                </div>
                
                ${referralLinkHtml}
                
                <button id="closeNodeDetails-${modalId}" class="mt-6 bg-secondary hover:bg-secondary/90 text-white px-6 py-2 rounded transition-colors">
                    Close
                </button>
            </div>
        `;

        const modal = this.createModal(content, { size: 'max-w-lg' });

        const closeBtn = document.getElementById(`closeNodeDetails-${modalId}`);
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.closeModal();
            });
        }
    }

    /**
     * Create control panel for network
     * @param {HTMLElement} container
     * @returns {HTMLElement}
     */
    createControlPanel(container) {
        const controlPanel = document.createElement('div');
        controlPanel.className = 'absolute top-4 right-4 bg-white rounded-lg shadow-lg p-4 z-10 border border-gray-200';
        controlPanel.innerHTML = `
            <div class="space-y-3">
                <h4 class="font-semibold text-text-primary text-sm flex items-center">
                    <i class="fas fa-cogs mr-2"></i>Network Controls
                </h4>
                <div class="space-y-2">
                    <button id="addConnectionMode" class="w-full bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded text-sm transition-colors flex items-center justify-center">
                        <i class="fas fa-link mr-2"></i>Add Connection
                    </button>
                    <button id="resetView" class="w-full bg-gray-200 hover:bg-gray-300 text-text-primary px-3 py-2 rounded text-sm transition-colors flex items-center justify-center">
                        <i class="fas fa-expand-arrows-alt mr-2"></i>Reset View
                    </button>
                    <button id="togglePhysics" class="w-full bg-gray-200 hover:bg-gray-300 text-text-primary px-3 py-2 rounded text-sm transition-colors flex items-center justify-center">
                        <i class="fas fa-pause mr-2"></i>Freeze Layout
                    </button>
                </div>
            </div>
        `;

        container.style.position = 'relative';
        container.appendChild(controlPanel);

        return controlPanel;
    }

    /**
     * Show loading state
     * @param {HTMLElement} container
     */
    showLoading(container) {
        container.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-secondary mx-auto mb-4"></div>
                    <p class="text-text-secondary">Loading network visualization...</p>
                </div>
            </div>
        `;
    }

    /**
     * Show error state
     * @param {HTMLElement} container
     * @param {string} message
     */
    showError(container, message) {
        container.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-danger text-4xl mb-4"></i>
                    <p class="text-danger text-lg mb-4">${message}</p>
                    <button onclick="location.reload()" class="bg-secondary hover:bg-secondary/90 text-white px-4 py-2 rounded transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Retry
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Show empty state
     * @param {HTMLElement} container
     */
    showEmptyState(container) {
        container.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-project-diagram text-gray-400 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-text-primary mb-2">No Referrals Found</h4>
                    <p class="text-text-secondary mb-4">No referral connections matching your criteria.</p>
                </div>
            </div>
        `;
    }
}