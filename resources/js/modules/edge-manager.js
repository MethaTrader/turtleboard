// resources/js/modules/edge-manager.js

import { NetworkUtils } from './utils.js';
import { DataSet } from 'vis-data';

/**
 * Edge manager for handling edge operations and styling
 */
export class EdgeManager {
    constructor() {
        this.edges = null;
    }

    /**
     * Initialize edges dataset
     * @param {Array} edgeData
     * @returns {DataSet}
     */
    initializeEdges(edgeData) {
        const processedEdges = edgeData.map(edge => this.processEdge(edge));
        this.edges = new DataSet(processedEdges);
        return this.edges;
    }

    /**
     * Process individual edge data
     * @param {Object} edge
     * @returns {Object}
     */
    processEdge(edge) {
        const status = edge.data?.status || 'pending';
        const statusColor = NetworkUtils.getStatusColor(status);

        return {
            id: edge.id,
            from: edge.from,
            to: edge.to,
            title: this.generateEdgeTooltip(edge),
            color: {
                color: statusColor,
                opacity: 0.8,
                inherit: false,
                highlight: statusColor,
                hover: statusColor
            },
            width: 3,
            selectionWidth: 5,
            hoverWidth: 4,
            arrows: {
                to: {
                    enabled: true,
                    scaleFactor: 1.3,
                    type: 'arrow'
                }
            },
            smooth: {
                type: 'continuous',
                roundness: 0.3
            },
            shadow: {
                enabled: true,
                color: 'rgba(0,0,0,0.1)',
                size: 5,
                x: 1,
                y: 1
            },
            data: {
                ...edge.data,
                status: status,
                originalColor: statusColor
            },
            chosen: {
                edge: (values, id, selected, hovering) => {
                    this.handleEdgeSelection(values, id, selected, hovering);
                }
            }
        };
    }

    /**
     * Generate tooltip for edge
     * @param {Object} edge
     * @returns {string}
     */
    generateEdgeTooltip(edge) {
        const data = edge.data || {};
        const tooltipData = {
            'Referral Connection': `Account ${edge.from} → Account ${edge.to}`,
            Status: this.getStatusLabel(data.status || 'pending'),
            'Created': data.created_at || 'Unknown'
        };

        if (data.referral_link) {
            tooltipData['Referral Link'] = data.referral_link;
        }

        return NetworkUtils.formatTooltip(tooltipData);
    }

    /**
     * Get human-readable status label
     * @param {string} status
     * @returns {string}
     */
    getStatusLabel(status) {
        const labels = {
            'pending': 'Pending',
            'completed': 'Completed',
            'cancelled': 'Cancelled'
        };
        return labels[status] || 'Unknown';
    }

    /**
     * Handle edge selection styling
     * @param {Object} values
     * @param {number} id
     * @param {boolean} selected
     * @param {boolean} hovering
     */
    handleEdgeSelection(values, id, selected, hovering) {
        if (selected) {
            values.width = 5;
            values.opacity = 1;
            values.shadow = {
                enabled: true,
                color: 'rgba(0,0,0,0.3)',
                size: 8,
                x: 2,
                y: 2
            };
        } else if (hovering) {
            values.width = 4;
            values.opacity = 0.9;
        } else {
            values.width = 3;
            values.opacity = 0.8;
        }
    }

    /**
     * Add new edge
     * @param {Object} edgeData
     */
    addEdge(edgeData) {
        if (!this.edges) return;

        const processedEdge = this.processEdge(edgeData);
        this.edges.add(processedEdge);

        // Add animation for new edges
        setTimeout(() => {
            this.updateEdge(edgeData.id, {
                color: {
                    ...processedEdge.color,
                    opacity: 0.8
                }
            });
        }, 100);
    }

    /**
     * Remove edge
     * @param {number} edgeId
     */
    removeEdge(edgeId) {
        if (!this.edges) return;

        // Add fade out animation
        this.updateEdge(edgeId, {
            color: {
                opacity: 0
            }
        });

        setTimeout(() => {
            this.edges.remove(edgeId);
        }, 300);
    }

    /**
     * Update edge
     * @param {number} edgeId
     * @param {Object} updates
     */
    updateEdge(edgeId, updates) {
        if (!this.edges) return;

        this.edges.update({
            id: edgeId,
            ...updates
        });
    }

    /**
     * Update edge status
     * @param {number} edgeId
     * @param {string} status
     */
    updateEdgeStatus(edgeId, status) {
        const edge = this.getEdge(edgeId);
        if (!edge) return;

        const statusColor = NetworkUtils.getStatusColor(status);

        this.updateEdge(edgeId, {
            color: {
                color: statusColor,
                opacity: 0.8,
                inherit: false,
                highlight: statusColor,
                hover: statusColor
            },
            data: {
                ...edge.data,
                status: status
            },
            title: this.generateEdgeTooltip({
                ...edge,
                data: {
                    ...edge.data,
                    status: status
                }
            })
        });
    }

    /**
     * Get edge by ID
     * @param {number} edgeId
     * @returns {Object|null}
     */
    getEdge(edgeId) {
        return this.edges ? this.edges.get(edgeId) : null;
    }

    /**
     * Get edges by node
     * @param {number} nodeId
     * @param {string} direction - 'from', 'to', or 'both'
     * @returns {Array}
     */
    getEdgesByNode(nodeId, direction = 'both') {
        if (!this.edges) return [];

        return this.edges.get({
            filter: (edge) => {
                switch (direction) {
                    case 'from':
                        return edge.from === nodeId;
                    case 'to':
                        return edge.to === nodeId;
                    case 'both':
                    default:
                        return edge.from === nodeId || edge.to === nodeId;
                }
            }
        });
    }

    /**
     * Check if connection exists between nodes
     * @param {number} fromNodeId
     * @param {number} toNodeId
     * @returns {boolean}
     */
    connectionExists(fromNodeId, toNodeId) {
        if (!this.edges) return false;

        const existingEdges = this.edges.get({
            filter: (edge) => edge.from === fromNodeId && edge.to === toNodeId
        });

        return existingEdges.length > 0;
    }

    /**
     * Get all edges with specific status
     * @param {string} status
     * @returns {Array}
     */
    getEdgesByStatus(status) {
        if (!this.edges) return [];

        return this.edges.get({
            filter: (edge) => edge.data?.status === status
        });
    }

    /**
     * Get edge statistics
     * @returns {Object}
     */
    getStatistics() {
        if (!this.edges) {
            return {
                total: 0,
                pending: 0,
                completed: 0,
                cancelled: 0
            };
        }

        const allEdges = this.edges.get();

        return {
            total: allEdges.length,
            pending: allEdges.filter(edge => edge.data?.status === 'pending').length,
            completed: allEdges.filter(edge => edge.data?.status === 'completed').length,
            cancelled: allEdges.filter(edge => edge.data?.status === 'cancelled').length
        };
    }

    /**
     * Highlight edges connected to node
     * @param {number} nodeId
     */
    highlightConnectedEdges(nodeId) {
        if (!this.edges) return;

        const connectedEdges = this.getEdgesByNode(nodeId);
        const updates = [];

        connectedEdges.forEach(edge => {
            updates.push({
                id: edge.id,
                width: 5,
                color: {
                    ...edge.color,
                    opacity: 1
                }
            });
        });

        if (updates.length > 0) {
            this.edges.update(updates);
        }
    }

    /**
     * Clear edge highlights
     */
    clearHighlights() {
        if (!this.edges) return;

        const allEdges = this.edges.get();
        const updates = [];

        allEdges.forEach(edge => {
            updates.push({
                id: edge.id,
                width: 3,
                color: {
                    ...edge.color,
                    opacity: 0.8
                }
            });
        });

        if (updates.length > 0) {
            this.edges.update(updates);
        }
    }

    /**
     * Filter edges by criteria
     * @param {Function} filterFn
     * @returns {Array}
     */
    filterEdges(filterFn) {
        if (!this.edges) return [];

        return this.edges.get({
            filter: filterFn
        });
    }

    /**
     * Get all edges
     * @returns {Array}
     */
    getAllEdges() {
        return this.edges ? this.edges.get() : [];
    }

    /**
     * Clear all edges
     */
    clear() {
        if (this.edges) {
            this.edges.clear();
        }
    }

    /**
     * Get edges dataset
     * @returns {DataSet}
     */
    getDataSet() {
        return this.edges;
    }

    /**
     * Animate edge creation
     * @param {number} edgeId
     */
    animateEdgeCreation(edgeId) {
        const edge = this.getEdge(edgeId);
        if (!edge) return;

        // Start with transparent edge
        this.updateEdge(edgeId, {
            color: {
                ...edge.color,
                opacity: 0
            },
            width: 1
        });

        // Animate to full visibility
        setTimeout(() => {
            this.updateEdge(edgeId, {
                color: {
                    ...edge.color,
                    opacity: 0.8
                },
                width: 3
            });
        }, 100);
    }
}