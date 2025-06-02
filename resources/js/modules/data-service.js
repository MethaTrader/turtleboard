// resources/js/modules/data-service.js

import { NetworkUtils } from './utils.js';

/**
 * Data service for handling API calls and data management
 */
export class DataService {
    constructor(dataUrl, createUrl) {
        this.dataUrl = dataUrl;
        this.createUrl = createUrl;
        this.csrfToken = NetworkUtils.getCsrfToken();
    }

    /**
     * Load network data from API
     * @param {string} period - Optional period filter
     * @returns {Promise<Object>}
     */
    async loadNetworkData(period = null) {
        try {
            const url = period && period !== 'all'
                ? `${this.dataUrl}?period=${period}`
                : this.dataUrl;

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Failed to load network data');
            }

            return {
                nodes: data.nodes || [],
                edges: data.edges || [],
                stats: data.stats || {}
            };
        } catch (error) {
            console.error('Error loading network data:', error);
            throw new Error(`Failed to load network data: ${error.message}`);
        }
    }

    /**
     * Create a new referral connection
     * @param {number} inviterId
     * @param {number} inviteeId
     * @param {string} referralLink
     * @returns {Promise<Object>}
     */
    async createReferralConnection(inviterId, inviteeId, referralLink = null) {
        try {
            const payload = {
                inviter_account_id: inviterId,
                invitee_account_id: inviteeId
            };

            if (referralLink) {
                payload.referral_link = referralLink;
            }

            const response = await fetch(this.createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || `HTTP ${response.status}: Failed to create connection`);
            }

            return result.referral;
        } catch (error) {
            console.error('Error creating referral connection:', error);
            throw new Error(`Failed to create referral connection: ${error.message}`);
        }
    }

    /**
     * Delete a referral connection
     * @param {number} referralId
     * @returns {Promise<boolean>}
     */
    async deleteReferralConnection(referralId) {
        try {
            const response = await fetch(`${this.createUrl}/${referralId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || `HTTP ${response.status}: Failed to delete connection`);
            }

            return true;
        } catch (error) {
            console.error('Error deleting referral connection:', error);
            throw new Error(`Failed to delete referral connection: ${error.message}`);
        }
    }

    /**
     * Update referral status
     * @param {number} referralId
     * @param {string} status
     * @returns {Promise<Object>}
     */
    async updateReferralStatus(referralId, status) {
        try {
            const response = await fetch(`${this.createUrl}/${referralId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || `HTTP ${response.status}: Failed to update status`);
            }

            return result.referral;
        } catch (error) {
            console.error('Error updating referral status:', error);
            throw new Error(`Failed to update referral status: ${error.message}`);
        }
    }

    /**
     * Get account details
     * @param {number} accountId
     * @returns {Promise<Object>}
     */
    async getAccountDetails(accountId) {
        try {
            const response = await fetch(`/referrals/account/${accountId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || `HTTP ${response.status}: Failed to get account details`);
            }

            return result.account;
        } catch (error) {
            console.error('Error getting account details:', error);
            throw new Error(`Failed to get account details: ${error.message}`);
        }
    }

    /**
     * Validate API endpoints
     * @returns {boolean}
     */
    validateEndpoints() {
        if (!this.dataUrl || !this.createUrl) {
            console.error('Missing required API endpoints');
            return false;
        }

        if (!this.csrfToken) {
            console.error('Missing CSRF token');
            return false;
        }

        return true;
    }

    /**
     * Get statistics
     * @returns {Promise<Object>}
     */
    async getStatistics() {
        try {
            const response = await fetch(`${this.dataUrl}/stats`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            return data.stats || {};
        } catch (error) {
            console.warn('Failed to load statistics:', error);
            return {};
        }
    }
}