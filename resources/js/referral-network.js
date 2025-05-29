// resources/js/referral-network.js
import { DataSet } from 'vis-data/peer/esm/vis-data';
import { Network } from 'vis-network/peer/esm/vis-network';

class ReferralNetwork {
    constructor(containerId, dataUrl, periodFilterId) {
        this.container = document.getElementById(containerId);
        this.dataUrl = dataUrl;
        this.periodFilter = document.getElementById(periodFilterId);
        this.network = null;
        this.nodes = null;
        this.edges = null;

        // Initialize the network if container exists
        if (this.container) {
            this.initialize();
        }
    }

    initialize() {
        // Set up event listeners
        if (this.periodFilter) {
            this.periodFilter.addEventListener('change', () => {
                this.loadData(this.getFilteredDataUrl());
            });
        }

        // Initial data load
        this.loadData(this.dataUrl);
    }

    getFilteredDataUrl() {
        const period = this.periodFilter.value;
        return period === 'all' ? this.dataUrl : `${this.dataUrl}?promotion_period=${period}`;
    }

    loadData(url) {
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.renderNetwork(data);
            })
            .catch(error => {
                console.error('Error fetching network data:', error);
                this.showError('Error loading visualization data');
            });
    }

    renderNetwork(data) {
        // Clear existing network if it exists
        if (this.network) {
            this.network.destroy();
        }

        // Create data sets
        this.nodes = new DataSet(data.nodes);
        this.edges = new DataSet(data.edges);

        // Network configuration options
        const options = {
            nodes: {
                shape: 'dot',
                size: 20,
                font: {
                    size: 14,
                    color: '#2D3748'
                },
                borderWidth: 2,
                shadow: true,
                color: {
                    border: '#ffffff',
                    background: '#5A55D2',
                    highlight: {
                        border: '#ffffff',
                        background: '#00DEA3'
                    },
                    hover: {
                        border: '#ffffff',
                        background: '#7A76E6'
                    }
                }
            },
            edges: {
                width: 2,
                shadow: true,
                smooth: {
                    type: 'continuous'
                }
            },
            physics: {
                barnesHut: {
                    gravitationalConstant: -5000,
                    centralGravity: 0.5,
                    springLength: 150,
                    springConstant: 0.04,
                    damping: 0.09
                },
                maxVelocity: 50,
                minVelocity: 0.1,
                solver: 'barnesHut',
                stabilization: {
                    enabled: true,
                    iterations: 1000,
                    updateInterval: 100,
                    onlyDynamicEdges: false,
                    fit: true
                },
                timestep: 0.5
            },
            groups: {
                root: {
                    color: {
                        background: '#5A55D2',
                        border: '#ffffff'
                    }
                },
                invitee: {
                    color: {
                        background: '#00DEA3',
                        border: '#ffffff'
                    }
                }
            },
            interaction: {
                hover: true,
                tooltipDelay: 200,
                zoomView: true,
                dragView: true
            },
            layout: {
                hierarchical: {
                    enabled: false
                }
            }
        };

        // Create network
        this.network = new Network(this.container, { nodes: this.nodes, edges: this.edges }, options);

        // Handle node clicks to show details
        this.network.on("click", (params) => {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = this.nodes.get(nodeId);

                // You could show a modal with details here
                console.log("Node clicked:", node);

                // Example of how to display node details in a tooltip or modal
                this.showNodeDetails(node);
            }
        });
    }

    showNodeDetails(node) {
        // You could implement a modal or tooltip here to show node details
        // For now, just log to console
        console.log(`Account: ${node.label}`);
        console.log(`Total Rewards: $${node.data.totalRewards}`);
        console.log(`Remaining Invitation Slots: ${node.data.remainingSlots}/5`);
    }

    showError(message) {
        // Display error message in the container
        if (this.container) {
            this.container.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <p class="text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${message}
                    </p>
                </div>
            `;
        }
    }
}

// Initialize the network when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the referrals page with visualization
    const networkContainer = document.getElementById('network-visualization');

    if (networkContainer) {
        // The data URL should match your route
        const dataUrl = networkContainer.dataset.url || '/referrals/network-data';

        // Initialize the network
        new ReferralNetwork('network-visualization', dataUrl, 'visualization-period');
    }
});

export default ReferralNetwork;