// resources/js/modules/node-manager.js

import { NetworkUtils } from './utils.js';
import { DataSet } from 'vis-data';

/**
 * Node manager for handling node operations and styling
 */
export class NodeManager {
    constructor() {
        this.nodes = null;
        this.selectedNodes = [];
        this.highlightedNodes = [];
        this.currentPeriod = 'all';
        this.network = null;
    }

    /**
     * Set network reference for tooltip management
     * @param {Network} network
     */
    setNetwork(network) {
        this.network = network;
    }

    /**
     * Set current period for filtering
     * @param {string} period
     */
    setCurrentPeriod(period) {
        this.currentPeriod = period;
        this.updateNodesForPeriod();
    }

    /**
     * Initialize nodes dataset
     * @param {Array} nodeData
     * @returns {DataSet}
     */
    initializeNodes(nodeData) {
        const processedNodes = nodeData.map(node => this.processNode(node));
        this.nodes = new DataSet(processedNodes);
        return this.nodes;
    }

    /**
     * Process individual node data
     * @param {Object} node
     * @returns {Object}
     */
    processNode(node) {
        const isInvited = node.group === 'invitee';
        const providerIcon = NetworkUtils.getProviderIcon(node.data?.provider);
        const isInCurrentPeriod = this.isNodeInCurrentPeriod(node);

        return {
            id: node.id,
            label: node.label,
            title: this.generateNodeTooltip(node),
            image: providerIcon,
            shape: 'circularImage',
            size: this.calculateNodeSize(node),
            borderWidth: 3,
            borderWidthSelected: 5,
            group: node.group,
            color: {
                border: '#5A55D2', // Secondary purple color
                background: 'rgba(0,0,0,0)',
                highlight: {
                    border: isInvited ? '#4F46E5' : '#4338CA', // Darker purple shades
                    background: 'rgba(90,85,210,0.1)'
                },
                hover: {
                    border: '#6366F1', // Medium purple shade
                    background: 'rgba(99,102,241,0.1)'
                }
            },
            font: {
                size: 12,
                color: isInCurrentPeriod ? '#2D3748' : '#9CA3AF',
                face: 'Poppins',
                background: isInCurrentPeriod ? 'rgba(255,255,255,0.8)' : 'rgba(255,255,255,0.5)',
                strokeWidth: 1,
                strokeColor: '#ffffff'
            },
            shadow: {
                enabled: isInCurrentPeriod,
                color: 'rgba(0,0,0,0.15)',
                size: 8,
                x: 2,
                y: 2
            },
            opacity: isInCurrentPeriod ? 1 : 0.4,
            data: {
                ...node.data,
                originalSize: this.calculateNodeSize(node),
                inCurrentPeriod: isInCurrentPeriod
            },
            chosen: {
                node: (values, id, selected, hovering) => {
                    this.handleNodeSelection(values, id, selected, hovering);
                }
            }
        };
    }

    /**
     * Check if node is in current period
     * @param {Object} node
     * @returns {boolean}
     */
    isNodeInCurrentPeriod(node) {
        if (this.currentPeriod === 'all' || !this.currentPeriod) {
            return true;
        }

        // Check if node was created within the period
        const nodeDate = new Date(node.data?.created_at || node.created_at);
        const periodDate = new Date(this.currentPeriod);

        // For half-month periods, check if it's in the correct half
        const periodDay = parseInt(this.currentPeriod.split('-')[2]);
        const isSecondHalf = periodDay === 16;

        const nodeMonth = nodeDate.getFullYear() + '-' + String(nodeDate.getMonth() + 1).padStart(2, '0');
        const periodMonth = this.currentPeriod.substring(0, 7);

        if (nodeMonth !== periodMonth) {
            return false;
        }

        if (isSecondHalf) {
            return nodeDate.getDate() >= 16;
        } else {
            return nodeDate.getDate() < 16;
        }
    }

    /**
     * Update nodes for current period
     */
    updateNodesForPeriod() {
        if (!this.nodes) return;

        const updates = [];
        this.nodes.forEach(node => {
            const isInCurrentPeriod = this.isNodeInCurrentPeriod(node);
            updates.push({
                id: node.id,
                opacity: isInCurrentPeriod ? 1 : 0.4,
                font: {
                    ...node.font,
                    color: isInCurrentPeriod ? '#2D3748' : '#9CA3AF',
                    background: isInCurrentPeriod ? 'rgba(255,255,255,0.8)' : 'rgba(255,255,255,0.5)'
                },
                shadow: {
                    ...node.shadow,
                    enabled: isInCurrentPeriod
                },
                data: {
                    ...node.data,
                    inCurrentPeriod: isInCurrentPeriod
                }
            });
        });

        this.nodes.update(updates);
    }

    /**
     * Calculate node size based on activity
     * @param {Object} node
     * @returns {number}
     */
    calculateNodeSize(node) {
        const baseSize = 35;
        const sentInvitations = node.data?.sentInvitations || 0;
        const activityBonus = Math.min(15, sentInvitations * 3);
        return baseSize + activityBonus;
    }

    /**
     * Generate tooltip for node
     * @param {Object} node
     * @returns {string}
     */
    generateNodeTooltip(node) {
        const data = node.data || {};
        const sentInvitations = data.sentInvitations || 0;
        const remainingSlots = data.remainingSlots || (5 - sentInvitations);
        const isInvited = data.isInvited || false;

        return NetworkUtils.formatTooltip({
            Email: node.label,
            Provider: data.provider || 'Unknown',
            Type: isInvited ? 'Invited Account' : 'Root Account',
            'Sent Invitations': `${sentInvitations}/5`,
            'Remaining Slots': remainingSlots,
            Status: 'Active'
        });
    }

    /**
     * Handle node selection styling
     * @param {Object} values
     * @param {number} id
     * @param {boolean} selected
     * @param {boolean} hovering
     */
    handleNodeSelection(values, id, selected, hovering) {
        const node = this.nodes ? this.nodes.get(id) : null;
        const isInCurrentPeriod = node?.data?.inCurrentPeriod !== false;

        if (selected) {
            values.borderColor = '#4338CA'; // Dark purple for selection
            values.borderWidth = 5;
            values.size = values.size * 1.1;

            // Hide tooltip when node is selected/clicked
            this.hideTooltip();
        } else if (hovering && isInCurrentPeriod) {
            values.borderColor = '#6366F1'; // Medium purple for hover
            values.borderWidth = 4;
            values.size = values.size * 1.05;
        } else {
            values.borderColor = '#5A55D2'; // Default purple
            values.borderWidth = 3;
        }
    }

    /**
     * Hide tooltip
     */
    hideTooltip() {
        if (this.network && this.network.tooltip) {
            this.network.tooltip.hide();
        }

        // Also hide any vis-tooltip elements
        const tooltips = document.querySelectorAll('.vis-tooltip');
        tooltips.forEach(tooltip => {
            tooltip.style.visibility = 'hidden';
            tooltip.style.opacity = '0';
        });
    }

    /**
     * Highlight connectable nodes
     */
    highlightConnectableNodes() {
        if (!this.nodes) return;

        const updates = [];
        this.nodes.forEach(node => {
            const isInCurrentPeriod = node.data?.inCurrentPeriod !== false;
            updates.push({
                id: node.id,
                borderWidth: 4,
                borderColor: isInCurrentPeriod ? '#00DEA3' : '#9CA3AF', // Only highlight current period nodes
                color: {
                    ...node.color,
                    border: isInCurrentPeriod ? '#00DEA3' : '#9CA3AF'
                }
            });
        });

        this.nodes.update(updates);
        this.highlightedNodes = updates.map(u => u.id);
    }

    /**
     * Clear node highlights
     */
    clearHighlights() {
        if (!this.nodes || this.highlightedNodes.length === 0) return;

        const updates = [];
        this.highlightedNodes.forEach(nodeId => {
            const node = this.nodes.get(nodeId);
            if (node) {
                updates.push({
                    id: nodeId,
                    borderWidth: 3,
                    borderColor: '#5A55D2', // Back to purple
                    color: {
                        ...node.color,
                        border: '#5A55D2'
                    }
                });
            }
        });

        this.nodes.update(updates);
        this.highlightedNodes = [];
    }

    /**
     * Select a node for connection mode
     * @param {number} nodeId
     */
    selectNodeForConnection(nodeId) {
        if (this.selectedNodes.includes(nodeId)) {
            return false;
        }

        const node = this.nodes.get(nodeId);
        const isInCurrentPeriod = node?.data?.inCurrentPeriod !== false;

        // Only allow selection of nodes in current period
        if (!isInCurrentPeriod && this.currentPeriod !== 'all') {
            return false;
        }

        this.selectedNodes.push(nodeId);

        // Highlight selected node with orange color
        this.nodes.update({
            id: nodeId,
            borderColor: '#F59E0B',
            borderWidth: 5,
            color: {
                border: '#F59E0B'
            }
        });

        // Hide tooltip when selecting nodes
        this.hideTooltip();

        return true;
    }

    /**
     * Reset connection mode
     */
    resetConnectionMode() {
        if (this.selectedNodes.length === 0) return;

        // Reset selected nodes highlighting
        this.selectedNodes.forEach(nodeId => {
            const node = this.nodes.get(nodeId);
            if (node) {
                const isInvited = node.group === 'invitee';
                this.nodes.update({
                    id: nodeId,
                    borderColor: '#5A55D2', // Back to purple
                    borderWidth: 3,
                    color: {
                        border: '#5A55D2',
                        background: 'rgba(0,0,0,0)',
                        highlight: {
                            border: isInvited ? '#4F46E5' : '#4338CA',
                            background: 'rgba(90,85,210,0.1)'
                        }
                    }
                });
            }
        });

        this.selectedNodes = [];
    }

    /**
     * Get selected nodes
     * @returns {Array}
     */
    getSelectedNodes() {
        return [...this.selectedNodes];
    }

    /**
     * Get node by ID
     * @param {number} nodeId
     * @returns {Object|null}
     */
    getNode(nodeId) {
        return this.nodes ? this.nodes.get(nodeId) : null;
    }

    /**
     * Update node data
     * @param {number} nodeId
     * @param {Object} updates
     */
    updateNode(nodeId, updates) {
        if (!this.nodes) return;

        this.nodes.update({
            id: nodeId,
            ...updates
        });
    }

    /**
     * Add new node
     * @param {Object} nodeData
     */
    addNode(nodeData) {
        if (!this.nodes) return;

        const processedNode = this.processNode(nodeData);
        this.nodes.add(processedNode);
    }

    /**
     * Remove node
     * @param {number} nodeId
     */
    removeNode(nodeId) {
        if (!this.nodes) return;

        this.nodes.remove(nodeId);
    }

    /**
     * Update node group (root/invitee)
     * @param {number} nodeId
     * @param {string} group
     */
    updateNodeGroup(nodeId, group) {
        const node = this.getNode(nodeId);
        if (!node) return;

        const isInvited = group === 'invitee';
        const isInCurrentPeriod = node.data?.inCurrentPeriod !== false;

        this.updateNode(nodeId, {
            group: group,
            color: {
                border: '#5A55D2', // Purple border
                background: 'rgba(0,0,0,0)',
                highlight: {
                    border: isInvited ? '#4F46E5' : '#4338CA',
                    background: 'rgba(90,85,210,0.1)'
                },
                hover: {
                    border: '#6366F1',
                    background: 'rgba(99,102,241,0.1)'
                }
            },
            opacity: isInCurrentPeriod ? 1 : 0.4
        });
    }

    /**
     * Filter nodes by criteria
     * @param {Function} filterFn
     * @returns {Array}
     */
    filterNodes(filterFn) {
        if (!this.nodes) return [];

        return this.nodes.get({
            filter: filterFn
        });
    }

    /**
     * Get all nodes
     * @returns {Array}
     */
    getAllNodes() {
        return this.nodes ? this.nodes.get() : [];
    }

    /**
     * Clear all nodes
     */
    clear() {
        if (this.nodes) {
            this.nodes.clear();
        }
        this.selectedNodes = [];
        this.highlightedNodes = [];
    }

    /**
     * Get nodes dataset
     * @returns {DataSet}
     */
    getDataSet() {
        return this.nodes;
    }
}