// resources/js/modules/utils.js

/**
 * Utility functions for the referral network
 */
export class NetworkUtils {
    /**
     * Get CSRF token from meta tag
     * @returns {string|null}
     */
    static getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /**
     * Get provider icon data URL (embedded SVG for reliability)
     * @param {string} provider
     * @returns {string}
     */
    static getProviderIcon(provider) {
        const icons = {
            'Gmail': '/images/providers/google.png',
            'Outlook': '/images/providers/microsoft.png',
            'Yahoo': '/images/providers/yahoo.png',
            'iCloud': '/images/providers/apple.png',
        };

        return icons[provider] || this.getDefaultIcon();
    }

    /**
     * Get default icon for unknown providers
     * @returns {string}
     */
    static getDefaultIcon() {
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM2QjcyODAiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiA0QzE2LjQxIDQgMjAgNy41OSAyMCAxMkMyMCAxNi40MSAxNi40MSAyMCAxMiAyMEM3LjU5IDIwIDQgMTYuNDEgNCAxMkM0IDcuNTkgNy41OSA0IDEyIDRaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4KPC9zdmc+';
    }

    /**
     * Get status color for referral status
     * @param {string} status
     * @returns {string}
     */
    static getStatusColor(status) {
        const colors = {
            'pending': '#F59E0B',
            'completed': '#00DEA3',
            'cancelled': '#F56565'
        };
        return colors[status] || '#6B7280';
    }

    /**
     * Debounce function for performance optimization
     * @param {Function} func
     * @param {number} wait
     * @returns {Function}
     */
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Format tooltip text
     * @param {Object} data
     * @returns {string}
     */
    static formatTooltip(data) {
        return Object.entries(data)
            .map(([key, value]) => `${key}: ${value}`)
            .join('\n');
    }

    /**
     * Validate connection between two nodes
     * @param {number} inviterId
     * @param {number} inviteeId
     * @param {Array} existingEdges
     * @returns {Object}
     */
    static validateConnection(inviterId, inviteeId, existingEdges) {
        if (inviterId === inviteeId) {
            return { valid: false, message: 'A node cannot invite itself' };
        }

        const connectionExists = existingEdges.some(edge =>
            edge.from === inviterId && edge.to === inviteeId
        );

        if (connectionExists) {
            return { valid: false, message: 'This referral connection already exists' };
        }

        return { valid: true };
    }

    /**
     * Generate unique ID for elements
     * @returns {string}
     */
    static generateId() {
        return Math.random().toString(36).substr(2, 9);
    }
}