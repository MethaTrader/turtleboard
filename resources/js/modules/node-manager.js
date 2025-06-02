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
                border: '#ffffff',
                background: 'rgba(0,0,0,0)',
                highlight: {
                    border: isInvited ? '#00DEA3' : '#5A55D2',
                    background: 'rgba(0,0,0,0.1)'
                },
                hover: {
                    border: '#7A76E6',
                    background: 'rgba(122,118,230,0.1)'
                }
            },
            font: {
                size: 12,
                color: '#2D3748',
                face: 'Poppins',
                background: 'rgba(255,255,255,0.8)',
                strokeWidth: 1,
                strokeColor: '#ffffff'
            },
            shadow: {
                enabled: true,
                color: 'rgba(0,0,0,0.15)',
                size: 8,
                x: 2,
                y: 2
            },
            data: {
                ...node.data,
                originalSize: this.calculateNodeSize(node)
            },
            chosen: {
                node: (values, id, selected, hovering) => {
                    this.handleNodeSelection(values, id, selected, hovering);
                }
            }
        };
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
        if (selected) {
            values.borderColor = '#00DEA3';
            values.borderWidth = 5;
            values.size = values.size * 1.1;
        } else if (hovering) {
            values.borderColor = '#7A76E6';
            values.borderWidth = 4;
            values.size = values.size * 1.05;
        } else {
            values.borderColor = '#ffffff';
            values.borderWidth = 3;
        }
    }

    /**
     * Highlight connectable nodes
     */
    highlightConnectableNodes() {
        if (!this.nodes) return;

        const updates = [];
        this.nodes.forEach(node => {
            updates.push({
                id: node.id,
                borderWidth: 4,
                borderColor: '#00DEA3',
                color: {
                    ...node.color,
                    border: '#00DEA3'
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
                    borderColor: '#ffffff',
                    color: {
                        ...node.color,
                        border: '#ffffff'
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

        this.selectedNodes.push(nodeId);

        // Highlight selected node
        this.nodes.update({
            id: nodeId,
            borderColor: '#F59E0B',
            borderWidth: 5,
            color: {
                border: '#F59E0B'
            }
        });

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
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    color: {
                        border: '#ffffff',
                        background: 'rgba(0,0,0,0)',
                        highlight: {
                            border: isInvited ? '#00DEA3' : '#5A55D2',
                            background: 'rgba(0,0,0,0.1)'
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

        this.updateNode(nodeId, {
            group: group,
            color: {
                border: '#ffffff',
                background: 'rgba(0,0,0,0)',
                highlight: {
                    border: isInvited ? '#00DEA3' : '#5A55D2',
                    background: 'rgba(0,0,0,0.1)'
                },
                hover: {
                    border: '#7A76E6',
                    background: 'rgba(122,118,230,0.1)'
                }
            }
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