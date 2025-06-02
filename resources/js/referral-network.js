// resources/js/interactive-referral-network.js
import { DataSet } from 'vis-data/peer/esm/vis-data';
import { Network } from 'vis-network/peer/esm/vis-network';

class InteractiveReferralNetwork {
    constructor(containerId, dataUrl, createUrl) {
        this.container = document.getElementById(containerId);
        this.dataUrl = dataUrl;
        this.createUrl = createUrl;
        this.network = null;
        this.nodes = null;
        this.edges = null;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.currentPeriod = 'all'; // Track current filter period

        if (this.container) {
            this.initialize();
        }
    }

    initialize() {
        this.loadData(this.dataUrl);
        this.setupEventListeners();
        this.setupPeriodFilter();
    }

    loadData(url) {
        // Add period filter to URL if not 'all'
        const fullUrl = this.currentPeriod !== 'all' ?
            `${url}?period=${this.currentPeriod}` : url;

        fetch(fullUrl)
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

    setupPeriodFilter() {
        const periodSelect = document.getElementById('period-filter');
        if (periodSelect) {
            periodSelect.addEventListener('change', (e) => {
                this.currentPeriod = e.target.value;
                this.loadData(this.dataUrl);
            });
        }
    }

    renderNetwork(data) {
        // Clear existing network if it exists
        if (this.network) {
            this.network.destroy();
        }

        // Create data sets with email provider icons
        this.nodes = new DataSet(data.nodes.map(node => ({
            ...node,
            borderWidth: 3,
            borderWidthSelected: 4,
            image: this.getProviderIcon(node.data.provider),
            shape: 'circularImage',
            size: 30,
            chosen: {
                node: (values, id, selected, hovering) => {
                    values.borderColor = selected ? '#00DEA3' : (hovering ? '#7A76E6' : '#ffffff');
                }
            }
        })));

        this.edges = new DataSet(data.edges.map(edge => ({
            ...edge,
            width: 3,
            selectionWidth: 4,
            hoverWidth: 4
        })));

        // Enhanced network configuration
        const options = {
            nodes: {
                shape: 'circularImage',
                size: 30,
                font: {
                    size: 12,
                    color: '#2D3748',
                    face: 'Poppins'
                },
                borderWidth: 3,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.1)',
                    size: 8,
                    x: 2,
                    y: 2
                },
                color: {
                    border: '#ffffff',
                    background: 'rgba(0,0,0,0)',
                    highlight: {
                        border: '#00DEA3',
                        background: '#7A76E6'
                    },
                    hover: {
                        border: '#00DEA3',
                        background: '#7A76E6'
                    }
                }
            },
            edges: {
                width: 3,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.1)',
                    size: 5,
                    x: 1,
                    y: 1
                },
                smooth: {
                    type: 'continuous',
                    roundness: 0.3
                },
                arrows: {
                    to: {
                        enabled: true,
                        scaleFactor: 1.2,
                        type: 'arrow'
                    }
                },
                color: {
                    inherit: false
                }
            },
            physics: {
                enabled: true,
                barnesHut: {
                    gravitationalConstant: -3000,
                    centralGravity: 0.3,
                    springLength: 120,
                    springConstant: 0.05,
                    damping: 0.09,
                    avoidOverlap: 0.2
                },
                maxVelocity: 30,
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
            interaction: {
                hover: true,
                hoverConnectedEdges: true,
                selectConnectedEdges: false,
                tooltipDelay: 200,
                zoomView: true,
                dragView: true,
                dragNodes: true,
                multiselect: false
            },
            layout: {
                randomSeed: 2,
                hierarchical: {
                    enabled: false
                }
            }
        };

        // Create network
        this.network = new Network(this.container, { nodes: this.nodes, edges: this.edges }, options);

        // Set up event handlers
        this.setupNetworkEvents();

        // Enable manipulation after network is stable
        this.network.once('stabilizationIterationsDone', () => {
            this.enableInteraction();
        });
    }

    getProviderIcon(provider) {
        const icons = {
            'Gmail': 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg',
            'Outlook': 'https://img.icons8.com/color/48/microsoft-outlook-2019.png',
            'Yahoo': 'https://img.icons8.com/color/48/yahoo.png',
            'iCloud': 'https://img.icons8.com/color/48/email.png'
        };
        return icons[provider] || 'https://img.icons8.com/color/48/email.png';
    }

    setupNetworkEvents() {
        // Handle node selection for creating connections
        this.network.on("click", (params) => {
            if (params.nodes.length > 0) {
                this.handleNodeClick(params.nodes[0]);
            } else {
                this.clearSelection();
            }
        });

        // Handle double-click to show node details
        this.network.on("doubleClick", (params) => {
            if (params.nodes.length > 0) {
                this.showNodeDetails(params.nodes[0]);
            }
        });

        // Handle edge selection with delete option
        this.network.on("selectEdge", (params) => {
            if (params.edges.length > 0) {
                this.showEdgeDetails(params.edges[0]);
            }
        });

        // Handle right-click on edges for delete option
        this.network.on("oncontext", (params) => {
            params.event.preventDefault();
            if (params.edges.length > 0) {
                this.showDeleteConfirmation(params.edges[0]);
            } else if (params.nodes.length > 0) {
                this.showContextMenu(params.nodes[0], params.pointer.DOM);
            }
        });
    }

    enableInteraction() {
        // Add control panel
        this.createControlPanel();

        // Show tutorial tooltip with auto-hide and close button
        this.showTutorial();
    }

    createControlPanel() {
        const controlPanel = document.createElement('div');
        controlPanel.className = 'absolute top-4 right-4 bg-white rounded-lg shadow-lg p-4 z-10';
        controlPanel.innerHTML = `
            <div class="space-y-3">
                <h4 class="font-semibold text-text-primary text-sm">Network Controls</h4>
                <div class="space-y-2">
                    <button id="addConnectionMode" class="w-full bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded text-sm transition-colors">
                        <i class="fas fa-link mr-2"></i>Add Connection
                    </button>
                    <button id="resetView" class="w-full bg-gray-200 hover:bg-gray-300 text-text-primary px-3 py-2 rounded text-sm transition-colors">
                        <i class="fas fa-expand-arrows-alt mr-2"></i>Reset View
                    </button>
                    <button id="togglePhysics" class="w-full bg-gray-200 hover:bg-gray-300 text-text-primary px-3 py-2 rounded text-sm transition-colors">
                        <i class="fas fa-pause mr-2"></i>Freeze Layout
                    </button>
                </div>
            </div>
        `;

        this.container.style.position = 'relative';
        this.container.appendChild(controlPanel);

        this.setupControlPanelEvents();
    }

    setupControlPanelEvents() {
        const addConnectionBtn = document.getElementById('addConnectionMode');
        const resetViewBtn = document.getElementById('resetView');
        const togglePhysicsBtn = document.getElementById('togglePhysics');

        let physicsEnabled = true;
        let connectionMode = false;

        addConnectionBtn?.addEventListener('click', () => {
            connectionMode = !connectionMode;
            if (connectionMode) {
                this.enterConnectionMode();
                addConnectionBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Cancel';
                addConnectionBtn.className = 'w-full bg-danger hover:bg-danger/90 text-white px-3 py-2 rounded text-sm transition-colors';
            } else {
                this.exitConnectionMode();
                addConnectionBtn.innerHTML = '<i class="fas fa-link mr-2"></i>Add Connection';
                addConnectionBtn.className = 'w-full bg-primary hover:bg-primary/90 text-white px-3 py-2 rounded text-sm transition-colors';
            }
        });

        resetViewBtn?.addEventListener('click', () => {
            this.network.fit({
                animation: {
                    duration: 1000,
                    easingFunction: 'easeInOutQuad'
                }
            });
        });

        togglePhysicsBtn?.addEventListener('click', () => {
            physicsEnabled = !physicsEnabled;
            this.network.setOptions({ physics: { enabled: physicsEnabled } });

            if (physicsEnabled) {
                togglePhysicsBtn.innerHTML = '<i class="fas fa-pause mr-2"></i>Freeze Layout';
            } else {
                togglePhysicsBtn.innerHTML = '<i class="fas fa-play mr-2"></i>Enable Physics';
            }
        });
    }

    enterConnectionMode() {
        this.selectedNodes = [];
        this.showToast('Connection Mode Active', 'Click two nodes to create a referral connection', 'info');

        // Change cursor
        this.container.style.cursor = 'crosshair';

        // Highlight available nodes
        this.highlightConnectableNodes();
    }

    exitConnectionMode() {
        this.selectedNodes = [];
        this.container.style.cursor = 'default';
        this.clearHighlights();
        this.hideToast();
    }

    highlightConnectableNodes() {
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
    }

    clearHighlights() {
        const updates = [];
        this.nodes.forEach(node => {
            updates.push({
                id: node.id,
                borderWidth: 3,
                borderColor: '#ffffff',
                color: {
                    ...node.color,
                    border: '#ffffff'
                }
            });
        });
        this.nodes.update(updates);
    }

    handleNodeClick(nodeId) {
        if (!this.selectedNodes) return;

        const addConnectionBtn = document.getElementById('addConnectionMode');
        const isConnectionMode = addConnectionBtn?.textContent.includes('Cancel');

        if (isConnectionMode) {
            this.handleConnectionModeClick(nodeId);
        }
    }

    handleConnectionModeClick(nodeId) {
        if (!this.selectedNodes.includes(nodeId)) {
            this.selectedNodes.push(nodeId);

            // Highlight selected node
            this.nodes.update({
                id: nodeId,
                borderColor: '#F59E0B',
                color: {
                    border: '#F59E0B'
                }
            });

            if (this.selectedNodes.length === 1) {
                this.showToast('First Node Selected', 'Now click the node to be invited', 'info');
            } else if (this.selectedNodes.length === 2) {
                this.createConnection(this.selectedNodes[0], this.selectedNodes[1]);
            }
        }
    }

    async createConnection(inviterId, inviteeId) {
        const inviterNode = this.nodes.get(inviterId);
        const inviteeNode = this.nodes.get(inviteeId);

        // Validate connection
        if (inviterId === inviteeId) {
            this.showToast('Invalid Connection', 'A node cannot invite itself', 'error');
            this.resetConnectionMode();
            return;
        }

        // Check if connection already exists
        const existingEdge = this.edges.get({
            filter: (edge) => edge.from === inviterId && edge.to === inviteeId
        });

        if (existingEdge.length > 0) {
            this.showToast('Connection Exists', 'This referral connection already exists', 'warning');
            this.resetConnectionMode();
            return;
        }

        // Show confirmation modal with referral link option
        const { confirmed, referralLink } = await this.showConnectionConfirmation(inviterNode.label, inviteeNode.label);

        if (confirmed) {
            await this.submitConnection(inviterId, inviteeId, referralLink);
        }

        this.resetConnectionMode();
    }

    resetConnectionMode() {
        // Reset selected nodes highlighting
        this.selectedNodes.forEach(nodeId => {
            const node = this.nodes.get(nodeId);
            this.nodes.update({
                id: nodeId,
                borderColor: '#ffffff',
                color: {
                    border: '#ffffff'
                }
            });
        });

        this.selectedNodes = [];
    }

    showConnectionConfirmation(inviterEmail, inviteeEmail) {
        return new Promise((resolve) => {
            const modal = this.createModal(`
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-link text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">Create Referral Connection</h3>
                    <p class="text-text-secondary mb-6">
                        <strong>${inviterEmail}</strong> will invite <strong>${inviteeEmail}</strong>
                    </p>
                    
                    <!-- Referral Link Field -->
                    <div class="mb-6 text-left">
                        <label for="referralLink" class="block text-sm font-medium text-text-secondary mb-2">
                            Referral Link (Optional)
                        </label>
                        <input type="url" id="referralLink" placeholder="https://example.com/ref/..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:border-secondary focus:ring focus:ring-secondary/20">
                        <p class="text-xs text-text-secondary mt-1">This link will be visible when viewing account details</p>
                    </div>
                    
                    <div class="flex justify-center space-x-3">
                        <button id="cancelConnection" class="bg-gray-200 hover:bg-gray-300 text-text-primary px-4 py-2 rounded">
                            Cancel
                        </button>
                        <button id="confirmConnection" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded">
                            <i class="fas fa-check mr-2"></i>Create Connection
                        </button>
                    </div>
                </div>
            `);

            document.getElementById('cancelConnection')?.addEventListener('click', () => {
                this.closeModal(modal);
                resolve({ confirmed: false, referralLink: null });
            });

            document.getElementById('confirmConnection')?.addEventListener('click', () => {
                const referralLink = document.getElementById('referralLink').value || null;
                this.closeModal(modal);
                resolve({ confirmed: true, referralLink });
            });
        });
    }

    async submitConnection(inviterId, inviteeId, referralLink = null) {
        try {
            this.showToast('Creating Connection...', 'Please wait', 'info');

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
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Add new edge to the network
                const newEdge = {
                    id: result.referral.id,
                    from: inviterId,
                    to: inviteeId,
                    color: { color: '#00DEA3' }, // Completed status color
                    title: `Referral created on ${new Date().toLocaleDateString()}`,
                    arrows: 'to'
                };

                this.edges.add(newEdge);

                this.showToast('Success!', 'Referral connection created successfully', 'success');
            } else {
                throw new Error(result.message || 'Failed to create connection');
            }
        } catch (error) {
            console.error('Error creating connection:', error);
            this.showToast('Error', error.message || 'Failed to create referral connection', 'error');
        }
    }

    showDeleteConfirmation(edgeId) {
        const edge = this.edges.get(edgeId);
        const inviterNode = this.nodes.get(edge.from);
        const inviteeNode = this.nodes.get(edge.to);

        const modal = this.createModal(`
            <div class="text-center">
                <div class="w-16 h-16 bg-danger/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-danger text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-text-primary mb-2">Delete Referral Connection</h3>
                <p class="text-text-secondary mb-6">
                    Are you sure you want to delete the referral connection from 
                    <strong>${inviterNode.label}</strong> to <strong>${inviteeNode.label}</strong>?
                </p>
                <div class="flex justify-center space-x-3">
                    <button id="cancelDelete" class="bg-gray-200 hover:bg-gray-300 text-text-primary px-4 py-2 rounded">
                        Cancel
                    </button>
                    <button id="confirmDelete" class="bg-danger hover:bg-danger/90 text-white px-4 py-2 rounded">
                        <i class="fas fa-trash mr-2"></i>Delete Connection
                    </button>
                </div>
            </div>
        `);

        document.getElementById('cancelDelete')?.addEventListener('click', () => {
            this.closeModal(modal);
        });

        document.getElementById('confirmDelete')?.addEventListener('click', () => {
            this.deleteConnection(edgeId);
            this.closeModal(modal);
        });
    }

    async deleteConnection(edgeId) {
        try {
            this.showToast('Deleting Connection...', 'Please wait', 'info');

            const response = await fetch(`/referrals/${edgeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Remove edge from network
                this.edges.remove(edgeId);
                this.showToast('Success!', 'Referral connection deleted successfully', 'success');
            } else {
                throw new Error(result.message || 'Failed to delete connection');
            }
        } catch (error) {
            console.error('Error deleting connection:', error);
            this.showToast('Error', error.message || 'Failed to delete referral connection', 'error');
        }
    }

    showNodeDetails(nodeId) {
        const node = this.nodes.get(nodeId);
        const sentInvitations = this.edges.get({
            filter: (edge) => edge.from === nodeId
        });

        const receivedInvitation = this.edges.get({
            filter: (edge) => edge.to === nodeId
        });

        // Get referral link if this node was invited
        let referralLinkHtml = '';
        if (receivedInvitation.length > 0) {
            const invitation = receivedInvitation[0];
            if (invitation.data && invitation.data.referral_link) {
                referralLinkHtml = `
                    <div>
                        <span class="text-text-secondary">Referral Link:</span>
                        <a href="${invitation.data.referral_link}" target="_blank" class="font-medium text-primary ml-2 hover:underline">
                            ${invitation.data.referral_link.length > 30 ?
                    invitation.data.referral_link.substring(0, 30) + '...' :
                    invitation.data.referral_link}
                        </a>
                    </div>
                `;
            }
        }

        const modal = this.createModal(`
            <div class="text-center">
                <div class="w-16 h-16 ${receivedInvitation.length > 0 ? 'bg-success/10' : 'bg-secondary/10'} rounded-full flex items-center justify-center mx-auto mb-4">
                    <img src="${this.getProviderIcon(node.data.provider)}" alt="${node.data.provider}" class="w-8 h-8 rounded-full">
                </div>
                <h3 class="text-lg font-semibold text-text-primary mb-2">Account Details</h3>
                <div class="space-y-3 text-left">
                    <div>
                        <span class="text-text-secondary">Email:</span>
                        <span class="font-medium text-text-primary ml-2">${node.label}</span>
                    </div>
                    <div>
                        <span class="text-text-secondary">Provider:</span>
                        <span class="font-medium text-text-primary ml-2">${node.data.provider}</span>
                    </div>
                    <div>
                        <span class="text-text-secondary">Type:</span>
                        <span class="font-medium ml-2 ${receivedInvitation.length > 0 ? 'text-success' : 'text-secondary'}">
                            ${receivedInvitation.length > 0 ? 'Invited Account' : 'Root Account'}
                        </span>
                    </div>
                    <div>
                        <span class="text-text-secondary">Sent Invitations:</span>
                        <span class="font-medium text-text-primary ml-2">${sentInvitations.length}/5</span>
                    </div>
                    <div>
                        <span class="text-text-secondary">Remaining Slots:</span>
                        <span class="font-medium ml-2 ${5 - sentInvitations.length > 0 ? 'text-success' : 'text-danger'}">
                            ${5 - sentInvitations.length}
                        </span>
                    </div>
                    ${referralLinkHtml}
                </div>
                <button id="closeNodeDetails" class="mt-6 bg-secondary hover:bg-secondary/90 text-white px-4 py-2 rounded">
                    Close
                </button>
            </div>
        `);

        document.getElementById('closeNodeDetails')?.addEventListener('click', () => {
            this.closeModal(modal);
        });
    }

    createModal(content) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                ${content}
            </div>
        `;

        document.body.appendChild(modal);
        return modal;
    }

    closeModal(modal) {
        if (modal && modal.parentNode) {
            modal.parentNode.removeChild(modal);
        }
    }

    showToast(title, message, type = 'info') {
        const existingToast = document.getElementById('networkToast');
        if (existingToast) {
            existingToast.remove();
        }

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
        toast.className = `fixed top-4 left-1/2 transform -translate-x-1/2 ${colors[type]} px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;

        // Add close button for persistent toasts
        const closeButton = type === 'info' ? `
            <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        ` : '';

        toast.innerHTML = `
            <div class="flex items-center">
                <i class="${icons[type]} mr-3"></i>
                <div>
                    <div class="font-semibold">${title}</div>
                    ${message ? `<div class="text-sm opacity-90">${message}</div>` : ''}
                </div>
                ${closeButton}
            </div>
        `;

        document.body.appendChild(toast);

        if (type === 'success' || type === 'error') {
            setTimeout(() => this.hideToast(), 3000);
        }
    }

    hideToast() {
        const toast = document.getElementById('networkToast');
        if (toast) {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }
    }

    showTutorial() {
        setTimeout(() => {
            this.showToast(
                'Interactive Network Ready!',
                'Click "Add Connection" to create referral links between accounts',
                'info'
            );

            // Auto-hide after 5 seconds if user doesn't close it
            setTimeout(() => {
                this.hideToast();
            }, 5000);
        }, 1000);
    }

    setupEventListeners() {
        // Handle window resize
        window.addEventListener('resize', () => {
            if (this.network) {
                this.network.redraw();
            }
        });
    }

    showError(message) {
        if (this.container) {
            this.container.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-danger text-4xl mb-4"></i>
                        <p class="text-danger text-lg">${message}</p>
                        <button onclick="location.reload()" class="mt-4 bg-secondary hover:bg-secondary/90 text-white px-4 py-2 rounded">
                            Retry
                        </button>
                    </div>
                </div>
            `;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const networkContainer = document.getElementById('interactive-network-visualization');

    if (networkContainer) {
        const dataUrl = networkContainer.dataset.dataUrl;
        const createUrl = networkContainer.dataset.createUrl;

        new InteractiveReferralNetwork('interactive-network-visualization', dataUrl, createUrl);
    }
});

export default InteractiveReferralNetwork;