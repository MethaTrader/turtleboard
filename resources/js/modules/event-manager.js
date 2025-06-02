// resources/js/modules/event-manager.js

import { NetworkUtils } from './utils.js';

/**
 * Event manager for handling all network and UI events
 */
export class EventManager {
    constructor() {
        this.network = null;
        this.nodeManager = null;
        this.edgeManager = null;
        this.uiComponents = null;
        this.dataService = null;
        this.isConnectionMode = false;
        this.physicsEnabled = true;
        this.eventListeners = new Map();
    }

    /**
     * Initialize event manager with dependencies
     * @param {Object} dependencies
     */
    initialize(dependencies) {
        this.network = dependencies.network;
        this.nodeManager = dependencies.nodeManager;
        this.edgeManager = dependencies.edgeManager;
        this.uiComponents = dependencies.uiComponents;
        this.dataService = dependencies.dataService;

        // Set network reference in node manager for tooltip management
        this.nodeManager.setNetwork(this.network);

        this.setupNetworkEvents();
        this.setupControlPanelEvents();
        this.setupGlobalEvents();
    }

    /**
     * Setup network-specific events
     */
    setupNetworkEvents() {
        if (!this.network) return;

        // Node click events
        this.network.on("click", (params) => {
            this.handleNetworkClick(params);
        });

        // Double-click events
        this.network.on("doubleClick", (params) => {
            this.handleDoubleClick(params);
        });

        // Context menu events
        this.network.on("oncontext", (params) => {
            this.handleContextMenu(params);
        });

        // Hover events
        this.network.on("hoverNode", (params) => {
            this.handleNodeHover(params);
        });

        this.network.on("blurNode", (params) => {
            this.handleNodeBlur(params);
        });

        // Selection events
        this.network.on("selectNode", (params) => {
            this.handleNodeSelection(params);
        });

        this.network.on("selectEdge", (params) => {
            this.handleEdgeSelection(params);
        });

        // Stabilization events
        this.network.on("stabilizationIterationsDone", () => {
            this.handleStabilizationComplete();
        });

        // Physics events
        this.network.on("stabilizationProgress", (params) => {
            this.handleStabilizationProgress(params);
        });
    }

    /**
     * Setup control panel events
     */
    setupControlPanelEvents() {
        this.addEventListenerSafe('addConnectionMode', 'click', () => {
            this.toggleConnectionMode();
        });

        this.addEventListenerSafe('resetView', 'click', () => {
            this.resetNetworkView();
        });

        this.addEventListenerSafe('togglePhysics', 'click', () => {
            this.togglePhysics();
        });
    }

    /**
     * Setup global events
     */
    setupGlobalEvents() {
        // Window resize
        const resizeHandler = NetworkUtils.debounce(() => {
            if (this.network) {
                this.network.redraw();
            }
        }, 300);

        window.addEventListener('resize', resizeHandler);
        this.eventListeners.set('window-resize', { element: window, event: 'resize', handler: resizeHandler });

        // Keyboard shortcuts
        const keyHandler = (e) => {
            this.handleKeyboardShortcuts(e);
        };

        document.addEventListener('keydown', keyHandler);
        this.eventListeners.set('document-keydown', { element: document, event: 'keydown', handler: keyHandler });

        // Period filter change
        this.addEventListenerSafe('promotion-period', 'change', (e) => {
            this.handlePeriodFilterChange(e.target.value);
        });
    }

    /**
     * Handle network click events
     * @param {Object} params
     */
    handleNetworkClick(params) {
        // Hide any visible tooltips when clicking anywhere
        this.nodeManager.hideTooltip();

        if (params.nodes.length > 0) {
            this.handleNodeClick(params.nodes[0]);
        } else if (params.edges.length > 0) {
            this.handleEdgeClick(params.edges[0]);
        } else {
            this.clearSelection();
        }
    }

    /**
     * Handle node click
     * @param {number} nodeId
     */
    handleNodeClick(nodeId) {
        // Always hide tooltip when clicking on a node
        this.nodeManager.hideTooltip();

        if (this.isConnectionMode) {
            this.handleConnectionModeClick(nodeId);
        }
    }

    /**
     * Handle edge click
     * @param {number} edgeId
     */
    handleEdgeClick(edgeId) {
        if (!this.isConnectionMode) {
            this.edgeManager.highlightConnectedEdges(edgeId);
        }
    }

    /**
     * Handle double-click events
     * @param {Object} params
     */
    handleDoubleClick(params) {
        // Hide tooltip before showing modal
        this.nodeManager.hideTooltip();

        if (params.nodes.length > 0) {
            this.showNodeDetails(params.nodes[0]);
        }
    }

    /**
     * Handle context menu events
     * @param {Object} params
     */
    handleContextMenu(params) {
        params.event.preventDefault();

        // Hide tooltip before showing context menu
        this.nodeManager.hideTooltip();

        if (params.edges.length > 0) {
            this.showEdgeContextMenu(params.edges[0]);
        } else if (params.nodes.length > 0) {
            this.showNodeContextMenu(params.nodes[0], params.pointer.DOM);
        }
    }

    /**
     * Handle node hover
     * @param {Object} params
     */
    handleNodeHover(params) {
        if (!this.isConnectionMode) {
            this.edgeManager.highlightConnectedEdges(params.node);
        }
    }

    /**
     * Handle node blur
     * @param {Object} params
     */
    handleNodeBlur(params) {
        if (!this.isConnectionMode) {
            this.edgeManager.clearHighlights();
        }

        // Hide tooltip when mouse leaves node
        setTimeout(() => {
            this.nodeManager.hideTooltip();
        }, 100);
    }

    /**
     * Handle node selection
     * @param {Object} params
     */
    handleNodeSelection(params) {
        // Hide tooltip when node is selected
        this.nodeManager.hideTooltip();
    }

    /**
     * Handle edge selection
     * @param {Object} params
     */
    handleEdgeSelection(params) {
        // Additional logic for edge selection if needed
    }

    /**
     * Handle stabilization complete
     */
    handleStabilizationComplete() {
        console.log('Network stabilization complete');

        // Enable interaction after stabilization
        if (this.network) {
            this.network.setOptions({
                interaction: {
                    dragNodes: true,
                    dragView: true,
                    zoomView: true
                }
            });
        }
    }

    /**
     * Handle stabilization progress
     * @param {Object} params
     */
    handleStabilizationProgress(params) {
        // Optional: Show progress indicator
        const progress = Math.round((params.iterations / params.total) * 100);
        console.log(`Stabilization progress: ${progress}%`);
    }

    /**
     * Handle keyboard shortcuts
     * @param {KeyboardEvent} e
     */
    handleKeyboardShortcuts(e) {
        // Escape key to exit connection mode or close modals
        if (e.key === 'Escape') {
            this.nodeManager.hideTooltip();
            if (this.isConnectionMode) {
                this.exitConnectionMode();
            }
        }

        // Space key to toggle physics
        if (e.key === ' ' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            this.togglePhysics();
        }

        // R key to reset view
        if (e.key === 'r' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            this.resetNetworkView();
        }

        // C key to toggle connection mode
        if (e.key === 'c' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            this.toggleConnectionMode();
        }
    }

    /**
     * Handle period filter change
     * @param {string} period
     */
    async handlePeriodFilterChange(period) {
        try {
            this.uiComponents.showToast('Loading...', 'Filtering data by period', 'info');

            // Set current period in node manager
            this.nodeManager.setCurrentPeriod(period === 'all' ? 'all' : period);

            // Load filtered data
            const data = await this.dataService.loadNetworkData(period === 'all' ? null : period);

            // Clear existing data
            this.nodeManager.clear();
            this.edgeManager.clear();

            // Reinitialize with filtered data
            const nodes = this.nodeManager.initializeNodes(data.nodes);
            const edges = this.edgeManager.initializeEdges(data.edges);

            // Update network
            this.network.setData({ nodes, edges });

            this.uiComponents.hideToast();
        } catch (error) {
            this.uiComponents.showToast('Error', error.message, 'error');
        }
    }

    /**
     * Toggle connection mode
     */
    toggleConnectionMode() {
        this.isConnectionMode = !this.isConnectionMode;

        if (this.isConnectionMode) {
            this.enterConnectionMode();
        } else {
            this.exitConnectionMode();
        }
    }

    /**
     * Enter connection mode
     */
    enterConnectionMode() {
        this.isConnectionMode = true;
        this.nodeManager.clearHighlights();
        this.nodeManager.highlightConnectableNodes();
        this.nodeManager.hideTooltip();

        const button = document.getElementById('addConnectionMode');
        if (button) {
            button.innerHTML = '<i class="fas fa-times mr-2"></i>Cancel';
            button.className = 'w-full bg-danger hover:bg-danger/90 text-white px-3 py-2 rounded text-sm transition-colors flex items-center justify-center';
        }

        this.uiComponents.showToast(
            'Connection Mode Active',
            'Click two nodes to create a referral connection',
            'info'
        );
    }

    /**
     * Exit connection mode
     */
    exitConnectionMode() {
        this.isConnectionMode = false;
        this.nodeManager.clearHighlights();
        this.nodeManager.resetConnectionMode();

        const button = document.getElementById('addConnectionMode');
        if (button) {
            button.innerHTML = '<i class="fas fa-link mr-2"></i>Add Connection';
            button.className = 'w-full bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded text-sm transition-colors flex items-center justify-center';
        }

        this.uiComponents.hideToast();
    }

    /**
     * Handle connection mode click
     * @param {number} nodeId
     */
    async handleConnectionModeClick(nodeId) {
        const success = this.nodeManager.selectNodeForConnection(nodeId);
        if (!success) {
            // Show error if trying to select node outside current period
            const node = this.nodeManager.getNode(nodeId);
            if (node && !node.data?.inCurrentPeriod && this.nodeManager.currentPeriod !== 'all') {
                this.uiComponents.showToast('Invalid Selection', 'You can only connect accounts from the current period', 'error');
            }
            return;
        }

        const selectedNodes = this.nodeManager.getSelectedNodes();

        if (selectedNodes.length === 1) {
            this.uiComponents.showToast('First Node Selected', 'Now click the node to be invited', 'info');
        } else if (selectedNodes.length === 2) {
            await this.createConnection(selectedNodes[0], selectedNodes[1]);
        }
    }

    /**
     * Create connection between nodes
     * @param {number} inviterId
     * @param {number} inviteeId
     */
    async createConnection(inviterId, inviteeId) {
        try {
            // Validate connection
            const existingEdges = this.edgeManager.getAllEdges();
            const validation = NetworkUtils.validateConnection(inviterId, inviteeId, existingEdges);

            if (!validation.valid) {
                this.uiComponents.showToast('Invalid Connection', validation.message, 'error');
                this.resetConnectionMode();
                return;
            }

            // Get node data for confirmation
            const inviterNode = this.nodeManager.getNode(inviterId);
            const inviteeNode = this.nodeManager.getNode(inviteeId);

            // Show confirmation modal
            const { confirmed, referralLink } = await this.uiComponents.showConnectionConfirmation(
                inviterNode.label,
                inviteeNode.label
            );

            if (confirmed) {
                await this.submitConnection(inviterId, inviteeId, referralLink);
            }
        } catch (error) {
            this.uiComponents.showToast('Error', error.message, 'error');
        } finally {
            this.resetConnectionMode();
        }
    }

    /**
     * Submit connection to server
     * @param {number} inviterId
     * @param {number} inviteeId
     * @param {string} referralLink
     */
    async submitConnection(inviterId, inviteeId, referralLink) {
        try {
            this.uiComponents.showToast('Creating Connection...', 'Please wait', 'info');

            const referral = await this.dataService.createReferralConnection(inviterId, inviteeId, referralLink);

            // Add new edge to network
            const newEdge = {
                id: referral.id,
                from: inviterId,
                to: inviteeId,
                data: {
                    status: referral.status,
                    created_at: referral.created_at,
                    referral_link: referral.referral_link
                }
            };

            this.edgeManager.addEdge(newEdge);
            this.edgeManager.animateEdgeCreation(referral.id);

            // Update invitee node group
            this.nodeManager.updateNodeGroup(inviteeId, 'invitee');

            this.uiComponents.showToast('Success!', 'Referral connection created successfully', 'success');
        } catch (error) {
            this.uiComponents.showToast('Error', error.message, 'error');
        }
    }

    /**
     * Show node details modal
     * @param {number} nodeId
     */
    showNodeDetails(nodeId) {
        const node = this.nodeManager.getNode(nodeId);
        if (!node) return;

        const sentInvitations = this.edgeManager.getEdgesByNode(nodeId, 'from');
        const receivedInvitation = this.edgeManager.getEdgesByNode(nodeId, 'to');

        this.uiComponents.showNodeDetails(node.data, sentInvitations, receivedInvitation);
    }

    /**
     * Show edge context menu
     * @param {number} edgeId
     */
    async showEdgeContextMenu(edgeId) {
        const edge = this.edgeManager.getEdge(edgeId);
        if (!edge) return;

        const fromNode = this.nodeManager.getNode(edge.from);
        const toNode = this.nodeManager.getNode(edge.to);

        const confirmed = await this.uiComponents.showDeleteConfirmation(
            fromNode.label,
            toNode.label
        );

        if (confirmed) {
            await this.deleteConnection(edgeId);
        }
    }

    /**
     * Delete connection
     * @param {number} edgeId
     */
    async deleteConnection(edgeId) {
        try {
            this.uiComponents.showToast('Deleting Connection...', 'Please wait', 'info');

            await this.dataService.deleteReferralConnection(edgeId);

            const edge = this.edgeManager.getEdge(edgeId);

            // Remove edge from network
            this.edgeManager.removeEdge(edgeId);

            // Check if invitee node should become root again
            if (edge) {
                const remainingInvitations = this.edgeManager.getEdgesByNode(edge.to, 'to');
                if (remainingInvitations.length === 0) {
                    this.nodeManager.updateNodeGroup(edge.to, 'root');
                }
            }

            this.uiComponents.showToast('Success!', 'Referral connection deleted successfully', 'success');
        } catch (error) {
            this.uiComponents.showToast('Error', error.message, 'error');
        }
    }

    /**
     * Reset network view
     */
    resetNetworkView() {
        if (!this.network) return;

        this.network.fit({
            animation: {
                duration: 1000,
                easingFunction: 'easeInOutQuad'
            }
        });
    }

    /**
     * Toggle physics
     */
    togglePhysics() {
        this.physicsEnabled = !this.physicsEnabled;

        if (this.network) {
            this.network.setOptions({ physics: { enabled: this.physicsEnabled } });
        }

        const button = document.getElementById('togglePhysics');
        if (button) {
            if (this.physicsEnabled) {
                button.innerHTML = '<i class="fas fa-pause mr-2"></i>Freeze Layout';
            } else {
                button.innerHTML = '<i class="fas fa-play mr-2"></i>Enable Physics';
            }
        }
    }

    /**
     * Clear selection
     */
    clearSelection() {
        if (this.network) {
            this.network.unselectAll();
        }

        this.edgeManager.clearHighlights();
        this.nodeManager.hideTooltip();
    }

    /**
     * Reset connection mode
     */
    resetConnectionMode() {
        this.nodeManager.resetConnectionMode();
    }

    /**
     * Safely add event listener
     * @param {string} elementId
     * @param {string} event
     * @param {Function} handler
     */
    addEventListenerSafe(elementId, event, handler) {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener(event, handler);
            this.eventListeners.set(`${elementId}-${event}`, { element, event, handler });
        }
    }

    /**
     * Cleanup all event listeners
     */
    cleanup() {
        for (const [key, { element, event, handler }] of this.eventListeners) {
            element.removeEventListener(event, handler);
        }
        this.eventListeners.clear();

        // Clean up network events
        if (this.network) {
            this.network.off();
        }
    }
}