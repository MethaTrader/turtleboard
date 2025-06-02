// resources/js/interactive-referral-network.js
import { DataSet } from 'vis-data';
import { Network } from 'vis-network';


class InteractiveReferralNetwork {
    constructor(containerId, dataUrl, createUrl) {
        this.container = document.getElementById(containerId);
        this.dataUrl = dataUrl;
        this.createUrl = createUrl;
        this.network = null;
        this.nodes = null;
        this.edges = null;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (this.container) {
            this.initialize();
        }
    }

    initialize() {
        console.log('Initializing network visualization...');

        // Show a loading indicator
        this.showLoading();

        // Load the data with a small delay to ensure the DOM is ready
        setTimeout(() => {
            this.loadData(this.dataUrl);
            this.setupEventListeners();
        }, 100);
    }

    showLoading() {
        if (!this.container) return;

        this.container.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-secondary mx-auto mb-4"></div>
                <p class="text-text-secondary">Loading network visualization...</p>
            </div>
        </div>
    `;
    }

    loadData(url) {
        console.log('Loading data from URL:', url);

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data loaded successfully:', data);

                if (!data || !data.nodes || !data.edges) {
                    console.error('Invalid data format received:', data);
                    this.showError('Invalid data format received from server');
                    return;
                }

                this.renderNetwork(data);
            })
            .catch(error => {
                console.error('Error fetching network data:', error);
                this.showError(`Error loading visualization data: ${error.message}`);
            });
    }

    renderNetwork(data) {
        // Clear the container
        this.container.innerHTML = '';

        // Clear existing network if it exists
        if (this.network) {
            this.network.destroy();
        }

        // If no nodes, show empty state
        if (data.nodes.length === 0) {
            this.container.innerHTML = `
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
            return;
        }

        // Check if we have provider icons for each node
        // If any node doesn't have a proper image, fallback to standard nodes
        const useStandardNodes = data.nodes.some(node => !node.image || node.image.includes('default.png'));

        // Create data sets with appropriate node styling
        if (useStandardNodes) {
            // Fallback to color-based nodes if images aren't available
            this.nodes = new DataSet(data.nodes.map(node => ({
                ...node,
                shape: 'dot', // Use standard circular node
                image: undefined, // Remove image property
                color: {
                    background: node.group === 'invitee' ? 'rgba(0,222,163,0.3)' : 'rgba(90,85,210,0.3)',
                    border: '#ffffff',
                    highlight: {
                        background: node.group === 'invitee' ? '#00DEA3' : '#7A76E6',
                        border: '#ffffff'
                    },
                    hover: {
                        background: node.group === 'invitee' ? '#00DEA3' : '#7A76E6',
                        border: '#ffffff'
                    }
                },
                borderWidth: 3,
                borderWidthSelected: 4,
                chosen: {
                    node: (values, id, selected, hovering) => {
                        values.borderColor = selected ? '#00DEA3' : (hovering ? '#7A76E6' : '#ffffff');
                    }
                }
            })));
        } else {
            // Use email provider icons for nodes
            this.nodes = new DataSet(data.nodes.map(node => ({
                ...node,
                size: node.group === 'invitee' ? 30 : 35, // Slightly larger for root nodes
                borderWidth: 3,
                borderWidthSelected: 4,
                color: {
                    border: '#ffffff',
                    background: node.group === 'invitee' ? '#00DEA3' : '#5A55D2',
                    highlight: {
                        border: '#00DEA3',
                        background: node.group === 'invitee' ? '#00DEA3' : '#5A55D2'
                    }
                },
                chosen: {
                    node: (values, id, selected, hovering) => {
                        values.borderColor = selected ? '#00DEA3' : (hovering ? '#7A76E6' : '#ffffff');
                    }
                }
            })));
        }

        this.edges = new DataSet(data.edges.map(edge => ({
            ...edge,
            width: 3,
            selectionWidth: 4,
            hoverWidth: 4
        })));

        // Enhanced network configuration for interactivity
        const options = {
            nodes: {
                shape: useStandardNodes ? 'dot' : 'circularImage',
                size: 35,
                font: {
                    size: 14,
                    color: '#2D3748',
                    face: 'Poppins'
                },
                borderWidth: 3,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.1)',
                    size: 10,
                    x: 2,
                    y: 2
                },
                scaling: {
                    min: 20,
                    max: 40
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
            groups: {
                root: {
                    color: {
                        background: '#5A55D2',
                        border: '#ffffff'
                    },
                    size: 35
                },
                invitee: {
                    color: {
                        background: '#00DEA3',
                        border: '#ffffff'
                    },
                    size: 30
                }
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
            manipulation: {
                enabled: false,
                addEdge: (data, callback) => {
                    this.handleAddConnection(data, callback);
                }
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

        // Store network instance globally for access from outside
        window.referralNetwork = this;
    }

    setupNetworkEvents() {
        // Handle node selection for creating connections
        this.network.on("click", (params) => {
            if (params.nodes.length > 0) {
                this.handleNodeClick(params.nodes[0]);
            } else if (params.edges.length > 0) {
                this.handleEdgeClick(params.edges[0]);
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

        // Handle context menu for edges (for deletion)
        this.network.on("oncontext", (params) => {
            params.event.preventDefault();

            if (params.edges.length > 0) {
                // Edge context menu (for deletion)
                this.showEdgeContextMenu(params.edges[0], params.pointer.DOM);
            } else if (params.nodes.length > 0) {
                // Node context menu (for adding connections)
                this.showNodeContextMenu(params.nodes[0], params.pointer.DOM);
            }
        });
    }

    enableInteraction() {
        // Add control panel
        this.createControlPanel();
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

    handleEdgeClick(edgeId) {
        // Select the edge
        this.network.selectEdges([edgeId]);
    }

    handleConnectionModeClick(nodeId) {
        if (!this.selectedNodes.includes(nodeId)) {
            this.selectedNodes.push(nodeId);

            // Highlight selected node
            this.nodes.update({
                id: nodeId,
                color: {
                    background: '#F59E0B',
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

        // Show confirmation modal
        const confirmed = await this.showConnectionConfirmation(inviterNode.label, inviteeNode.label);

        if (confirmed) {
            await this.submitConnection(inviterId, inviteeId);
        }

        this.resetConnectionMode();
    }

    resetConnectionMode() {
        // Reset selected nodes highlighting
        this.selectedNodes.forEach(nodeId => {
            const node = this.nodes.get(nodeId);
            this.nodes.update({
                id: nodeId,
                color: node.group === 'invitee' ? {
                    background: '#00DEA3',
                    border: '#ffffff'
                } : {
                    background: '#5A55D2',
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
                    <p class="text-text-secondary mb-4">
                        <strong>${inviterEmail}</strong> will invite <strong>${inviteeEmail}</strong>
                    </p>
                    
                    <!-- Add referral link field -->
                    <div class="mb-4">
                        <label for="referral-link-input" class="block text-sm font-medium text-text-secondary text-left mb-1">
                            Referral Link (optional)
                        </label>
                        <input type="text" id="referral-link-input" placeholder="https://www.mexc.com/register?ref=..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-secondary">
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
                resolve(false);
            });

            document.getElementById('confirmConnection')?.addEventListener('click', () => {
                const referralLink = document.getElementById('referral-link-input')?.value || null;
                this.closeModal(modal);
                resolve({ confirmed: true, referralLink });
            });
        });
    }

    async submitConnection(inviterId, inviteeId, referralData) {
        try {
            this.showToast('Creating Connection...', 'Please wait', 'info');

            const referralLink = referralData?.referralLink || null;

            const response = await fetch(this.createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    inviter_account_id: inviterId,
                    invitee_account_id: inviteeId,
                    referral_link: referralLink
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Add new edge to the network
                const newEdge = {
                    id: result.referral.id,
                    from: inviterId,
                    to: inviteeId,
                    color: { color: '#F59E0B' }, // Pending status color
                    title: this.generateEdgeTooltip(result.referral),
                    arrows: 'to',
                    data: {
                        status: result.referral.status,
                        created_at: result.referral.created_at,
                        referral_link: result.referral.referral_link
                    }
                };

                this.edges.add(newEdge);

                // Update invitee node to show it's now invited
                this.nodes.update({
                    id: inviteeId,
                    group: 'invitee',
                    color: {
                        background: '#00DEA3',
                        border: '#ffffff'
                    }
                });

                this.showToast('Success!', 'Referral connection created successfully', 'success');
            } else {
                throw new Error(result.message || 'Failed to create connection');
            }
        } catch (error) {
            console.error('Error creating connection:', error);
            this.showToast('Error', error.message || 'Failed to create referral connection', 'error');
        }
    }

    generateEdgeTooltip(referral) {
        let tooltip = `Referral: ${referral.inviter_email} → ${referral.invitee_email}\nStatus: ${referral.status}\nCreated: ${referral.created_at}`;

        if (referral.referral_link) {
            tooltip += `\nReferral Link: ${referral.referral_link}`;
        }

        return tooltip;
    }

    showNodeDetails(nodeId) {
        const node = this.nodes.get(nodeId);
        const sentInvitations = this.edges.get({
            filter: (edge) => edge.from === nodeId
        }).length;

        const receivedInvitation = this.edges.get({
            filter: (edge) => edge.to === nodeId
        });

        const isInvited = receivedInvitation.length > 0;
        let referralLink = '';

        if (isInvited && receivedInvitation[0].data && receivedInvitation[0].data.referral_link) {
            referralLink = receivedInvitation[0].data.referral_link;
        }

        const providerIcon = this.getProviderIcon(node.data.provider);

        const modal = this.createModal(`
            <div class="text-center">
                <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <img src="${providerIcon}" alt="${node.data.provider}" class="w-12 h-12 rounded-full" />
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
                        <span class="font-medium ml-2 ${isInvited ? 'text-success' : 'text-secondary'}">
                            ${isInvited ? 'Invited Account' : 'Root Account'}
                        </span>
                    </div>
                    <div>
                        <span class="text-text-secondary">Sent Invitations:</span>
                        <span class="font-medium text-text-primary ml-2">${sentInvitations}/5</span>
                    </div>
                    <div>
                        <span class="text-text-secondary">Remaining Slots:</span>
                        <span class="font-medium ml-2 ${5 - sentInvitations > 0 ? 'text-success' : 'text-danger'}">
                            ${5 - sentInvitations}
                        </span>
                    </div>
                    ${referralLink ? `
                    <div class="mt-2">
                        <span class="text-text-secondary block">Referral Link:</span>
                        <div class="flex items-center mt-1">
                            <input type="text" readonly value="${referralLink}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50">
                            <button id="copyReferralLink" class="ml-2 bg-secondary hover:bg-secondary/90 text-white px-3 py-2 rounded-md text-sm">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    ` : ''}
                </div>
                <button id="closeNodeDetails" class="mt-6 bg-secondary hover:bg-secondary/90 text-white px-4 py-2 rounded">
                    Close
                </button>
            </div>
        `);

        document.getElementById('closeNodeDetails')?.addEventListener('click', () => {
            this.closeModal(modal);
        });

        document.getElementById('copyReferralLink')?.addEventListener('click', () => {
            const linkInput = modal.querySelector('input[readonly]');
            if (linkInput) {
                linkInput.select();
                document.execCommand('copy');
                this.showToast('Copied!', 'Referral link copied to clipboard', 'success');
            }
        });
    }

    showEdgeContextMenu(edgeId, position) {
        // Create a context menu for the edge
        const contextMenu = document.createElement('div');
        contextMenu.id = 'edge-context-menu';
        contextMenu.className = 'absolute bg-white rounded-md shadow-lg z-20 py-1 w-36';
        contextMenu.style.left = `${position.x}px`;
        contextMenu.style.top = `${position.y}px`;

        contextMenu.innerHTML = `
            <button class="w-full text-left px-4 py-2 text-sm text-danger hover:bg-gray-100">
                <i class="fas fa-trash mr-2"></i> Delete Connection
            </button>
        `;

        document.body.appendChild(contextMenu);

        // Add event listener for the delete button
        const deleteButton = contextMenu.querySelector('button');
        deleteButton.addEventListener('click', () => {
            this.confirmDeleteEdge(edgeId);
            this.removeContextMenu();
        });

        // Remove the context menu when clicking outside
        document.addEventListener('click', this.removeContextMenu);
        document.addEventListener('contextmenu', this.removeContextMenu);
    }

    removeContextMenu = () => {
        const menu = document.getElementById('edge-context-menu');
        if (menu) {
            menu.remove();
            document.removeEventListener('click', this.removeContextMenu);
            document.removeEventListener('contextmenu', this.removeContextMenu);
        }
    }

    async confirmDeleteEdge(edgeId) {
        const edge = this.edges.get(edgeId);
        if (!edge) return;

        const fromNode = this.nodes.get(edge.from);
        const toNode = this.nodes.get(edge.to);

        if (!fromNode || !toNode) return;

        const confirmed = await this.showConfirmation(
            'Delete Connection',
            `Are you sure you want to delete the referral connection between ${fromNode.label} and ${toNode.label}?`,
            'This action cannot be undone.'
        );

        if (confirmed) {
            this.deleteEdge(edgeId);
        }
    }

    async deleteEdge(edgeId) {
        try {
            this.showToast('Deleting Connection...', 'Please wait', 'info');

            const response = await fetch(`${this.createUrl}/${edgeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Remove the edge from the network
                this.edges.remove(edgeId);

                // Update the invitee node if needed
                const edge = this.edges.get(edgeId);
                if (edge) {
                    const inviteeId = edge.to;

                    // Check if this node is still an invitee in any other connection
                    const isStillInvitee = this.edges.get({
                        filter: e => e.to === inviteeId
                    }).length > 0;

                    if (!isStillInvitee) {
                        // Update the node to be a root node again
                        this.nodes.update({
                            id: inviteeId,
                            group: 'root',
                            color: {
                                background: '#5A55D2',
                                border: '#ffffff'
                            }
                        });
                    }
                }

                this.showToast('Success!', 'Referral connection deleted successfully', 'success');
            } else {
                throw new Error(result.message || 'Failed to delete connection');
            }
        } catch (error) {
            console.error('Error deleting connection:', error);
            this.showToast('Error', error.message || 'Failed to delete referral connection', 'error');
        }
    }

    showConfirmation(title, message, description = '') {
        return new Promise((resolve) => {
            const modal = this.createModal(`
                <div class="text-center">
                    <div class="w-16 h-16 bg-danger/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-danger text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-text-primary mb-2">${title}</h3>
                    <p class="text-text-secondary mb-2">${message}</p>
                    ${description ? `<p class="text-text-secondary text-sm mb-4">${description}</p>` : ''}
                    
                    <div class="flex justify-center space-x-3 mt-6">
                        <button id="cancelConfirmation" class="bg-gray-200 hover:bg-gray-300 text-text-primary px-4 py-2 rounded">
                            Cancel
                        </button>
                        <button id="confirmAction" class="bg-danger hover:bg-danger/90 text-white px-4 py-2 rounded">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </div>
                </div>
            `);

            document.getElementById('cancelConfirmation')?.addEventListener('click', () => {
                this.closeModal(modal);
                resolve(false);
            });

            document.getElementById('confirmAction')?.addEventListener('click', () => {
                this.closeModal(modal);
                resolve(true);
            });
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
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="${icons[type]} mr-3"></i>
                <div>
                    <div class="font-semibold">${title}</div>
                    ${message ? `<div class="text-sm opacity-90">${message}</div>` : ''}
                </div>
                <button class="ml-6 text-white/80 hover:text-white" id="closeToast">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(toast);

        // Add close button functionality
        document.getElementById('closeToast')?.addEventListener('click', () => {
            this.hideToast();
        });

        // Auto-hide toast after 5 seconds
        if (type === 'success' || type === 'error' || type === 'info') {
            setTimeout(() => this.hideToast(), 5000);
        }
    }

    hideToast() {
        const toast = document.getElementById('networkToast');
        if (toast) {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }
    }

    getProviderIcon(provider) {
        if (!provider) return '/images/providers/default.png';

        return `/images/providers/${provider.toLowerCase()}.png`;
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

    clearSelection() {
        this.network.unselectAll();
    }
}

// Initialize when DOM is ready with better error handling
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded and parsed');

    const networkContainer = document.getElementById('interactive-network-visualization');

    if (networkContainer) {
        console.log('Network container found:', networkContainer);

        const dataUrl = networkContainer.dataset.dataUrl;
        const createUrl = networkContainer.dataset.createUrl;

        console.log('URLs:', { dataUrl, createUrl });

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
            }, 200);
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