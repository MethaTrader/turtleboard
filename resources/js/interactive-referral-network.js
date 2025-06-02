// resources/js/interactive-referral-network.js

import { Network } from 'vis-network';
import { NetworkConfig } from './modules/network-config.js';
import { DataService } from './modules/data-service.js';
import { UIComponents } from './modules/ui-components.js';
import { NodeManager } from './modules/node-manager.js';
import { EdgeManager } from './modules/edge-manager.js';
import { EventManager } from './modules/event-manager.js';

/**
 * Main orchestrator class for the Interactive Referral Network
 * Follows SOLID principles and modular architecture
 */
class InteractiveReferralNetwork {
    constructor(containerId, dataUrl, createUrl) {
        // Core properties
        this.container = document.getElementById(containerId);
        this.network = null;

        // Service modules
        this.dataService = new DataService(dataUrl, createUrl);
        this.uiComponents = new UIComponents();
        this.nodeManager = new NodeManager();
        this.edgeManager = new EdgeManager();
        this.eventManager = new EventManager();

        // State
        this.isInitialized = false;
        this.currentPeriod = 'all';

        // Initialize if container exists
        if (this.container) {
            this.initialize();
        } else {
            console.error(`Container with ID '${containerId}' not found`);
        }
    }

    /**
     * Initialize the network visualization
     */
    async initialize() {
        try {
            // Validate setup
            if (!this.validateSetup()) {
                return;
            }

            // Show loading state
            this.uiComponents.showLoading(this.container);

            // Load initial data
            await this.loadData();

            // Setup network
            this.setupNetwork();

            // Setup UI components
            this.setupUI();

            // Initialize event handling
            this.initializeEvents();

            this.isInitialized = true;
            console.log('Interactive Referral Network initialized successfully');

        } catch (error) {
            console.error('Failed to initialize network:', error);
            this.uiComponents.showError(this.container, error.message);
        }
    }

    /**
     * Validate initial setup
     * @returns {boolean}
     */
    validateSetup() {
        if (!this.container) {
            console.error('Network container not found');
            return false;
        }

        if (!this.dataService.validateEndpoints()) {
            this.uiComponents.showError(this.container, 'Invalid API configuration');
            return false;
        }

        return true;
    }

    /**
     * Load network data
     * @param {string} period - Optional period filter
     */
    async loadData(period = null) {
        try {
            const data = await this.dataService.loadNetworkData(period);

            // Check if we have data
            if (!data.nodes || data.nodes.length === 0) {
                this.uiComponents.showEmptyState(this.container);
                return;
            }

            // Initialize data managers
            this.nodeManager.initializeNodes(data.nodes);
            this.edgeManager.initializeEdges(data.edges);

            this.currentPeriod = period || 'all';

        } catch (error) {
            throw new Error(`Failed to load network data: ${error.message}`);
        }
    }

    /**
     * Setup the vis-network instance
     */
    setupNetwork() {
        // Clear container
        this.container.innerHTML = '';

        // Get network configuration
        const options = NetworkConfig.getNetworkOptions();

        // Get data from managers
        const nodes = this.nodeManager.getDataSet();
        const edges = this.edgeManager.getDataSet();

        // Create network
        this.network = new Network(this.container, { nodes, edges }, options);

        // Store network reference globally for debugging
        window.referralNetwork = this;
    }

    /**
     * Setup UI components
     */
    setupUI() {
        // Create control panel
        const controlPanel = this.uiComponents.createControlPanel(this.container);

        // Show tutorial after stabilization
        this.network.once('stabilizationIterationsDone', () => {
            this.showTutorial();
        });
    }

    /**
     * Initialize event handling
     */
    initializeEvents() {
        this.eventManager.initialize({
            network: this.network,
            nodeManager: this.nodeManager,
            edgeManager: this.edgeManager,
            uiComponents: this.uiComponents,
            dataService: this.dataService
        });
    }

    /**
     * Show tutorial
     */
    showTutorial() {
        setTimeout(() => {
            this.uiComponents.showToast(
                'Interactive Network Ready!',
                'Click "Add Connection" to create referral links between accounts. Use right-click for more options.',
                'info',
                7000
            );
        }, 1000);
    }

    /**
     * Refresh network data
     * @param {string} period - Optional period filter
     */
    async refresh(period = null) {
        try {
            this.uiComponents.showToast('Refreshing...', 'Loading latest data', 'info');

            await this.loadData(period);

            // Update network data
            const nodes = this.nodeManager.getDataSet();
            const edges = this.edgeManager.getDataSet();
            this.network.setData({ nodes, edges });

            this.uiComponents.hideToast();
            this.uiComponents.showToast('Success!', 'Network data refreshed', 'success');

        } catch (error) {
            this.uiComponents.showToast('Error', error.message, 'error');
        }
    }

    /**
     * Filter network by period
     * @param {string} period
     */
    async filterByPeriod(period) {
        if (period === this.currentPeriod) return;

        try {
            await this.loadData(period);

            // Clear and reinitialize network
            this.network.setData({
                nodes: this.nodeManager.getDataSet(),
                edges: this.edgeManager.getDataSet()
            });

        } catch (error) {
            this.uiComponents.showToast('Error', `Failed to filter by period: ${error.message}`, 'error');
        }
    }

    /**
     * Focus on specific node
     * @param {number} nodeId
     */
    focusOnNode(nodeId) {
        if (!this.network) return;

        this.network.focus(nodeId, {
            animation: NetworkConfig.getAnimationOptions().focus
        });
    }

    /**
     * Fit network to view
     */
    fitToView() {
        if (!this.network) return;

        this.network.fit(NetworkConfig.getAnimationOptions().fit);
    }

    /**
     * Get network statistics
     * @returns {Object}
     */
    getStatistics() {
        return {
            nodes: this.nodeManager.getAllNodes().length,
            edges: this.edgeManager.getAllEdges().length,
            ...this.edgeManager.getStatistics()
        };
    }

    /**
     * Export network data
     * @returns {Object}
     */
    exportData() {
        return {
            nodes: this.nodeManager.getAllNodes(),
            edges: this.edgeManager.getAllEdges(),
            period: this.currentPeriod,
            statistics: this.getStatistics()
        };
    }

    /**
     * Destroy the network and cleanup
     */
    destroy() {
        // Cleanup event listeners
        this.eventManager.cleanup();

        // Destroy network
        if (this.network) {
            this.network.destroy();
            this.network = null;
        }

        // Clear managers
        this.nodeManager.clear();
        this.edgeManager.clear();

        // Clear UI
        if (this.container) {
            this.container.innerHTML = '';
        }

        // Clear global reference
        if (window.referralNetwork === this) {
            delete window.referralNetwork;
        }

        this.isInitialized = false;
        console.log('Interactive Referral Network destroyed');
    }

    /**
     * Get network instance (for debugging)
     * @returns {Network}
     */
    getNetwork() {
        return this.network;
    }

    /**
     * Check if network is initialized
     * @returns {boolean}
     */
    isReady() {
        return this.isInitialized && this.network !== null;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, initializing Interactive Referral Network...');

    const networkContainer = document.getElementById('interactive-network-visualization');

    if (networkContainer) {
        const dataUrl = networkContainer.dataset.dataUrl;
        const createUrl = networkContainer.dataset.createUrl;

        console.log('Network container found, URLs:', { dataUrl, createUrl });

        if (!dataUrl || !createUrl) {
            console.error('Missing required data attributes on network container');
            networkContainer.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-red-500 mb-2">Configuration Error</h4>
                        <p class="text-gray-600 mb-4">Missing required data attributes for network visualization.</p>
                    </div>
                </div>
            `;
            return;
        }

        try {
            // Create network with a slight delay to ensure DOM is fully ready
            setTimeout(() => {
                new InteractiveReferralNetwork('interactive-network-visualization', dataUrl, createUrl);
            }, 100);
        } catch (error) {
            console.error('Error initializing network:', error);
            networkContainer.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                        <h4 class="text-lg font-medium text-red-500 mb-2">Initialization Error</h4>
                        <p class="text-gray-600 mb-4">${error.message}</p>
                    </div>
                </div>
            `;
        }
    } else {
        console.warn('Network container not found with ID: interactive-network-visualization');
    }
});

export default InteractiveReferralNetwork;