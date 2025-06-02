// resources/js/modules/network-config.js

/**
 * Network configuration and options for vis-network
 */
export class NetworkConfig {
    /**
     * Get default node options
     * @returns {Object}
     */
    static getNodeOptions() {
        return {
            shape: 'circularImage',
            size: 35,
            font: {
                size: 14,
                color: '#2D3748',
                face: 'Poppins',
                background: 'rgba(255,255,255,0.7)',
                strokeWidth: 2,
                strokeColor: '#ffffff'
            },
            borderWidth: 3,
            borderWidthSelected: 5,
            shadow: {
                enabled: true,
                color: 'rgba(0,0,0,0.15)',
                size: 8,
                x: 2,
                y: 2
            },
            scaling: {
                min: 20,
                max: 50
            },
            color: {
                border: '#ffffff',
                background: 'rgba(0,0,0,0)',
                highlight: {
                    border: '#4300de',
                    background: 'rgba(0,222,163,0.1)'
                },
                hover: {
                    border: '#7A76E6',
                    background: 'rgba(122,118,230,0.1)'
                }
            },
            chosen: {
                node: (values, id, selected, hovering) => {
                    values.borderColor = selected ? '#4600de' : (hovering ? '#7A76E6' : '#ffffff');
                    values.borderWidth = selected ? 5 : (hovering ? 4 : 3);
                }
            }
        };
    }

    /**
     * Get default edge options
     * @returns {Object}
     */
    static getEdgeOptions() {
        return {
            width: 3,
            selectionWidth: 5,
            hoverWidth: 4,
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
                    scaleFactor: 1.3,
                    type: 'arrow'
                }
            },
            color: {
                inherit: false,
                opacity: 0.8
            },
            chosen: {
                edge: (values, id, selected, hovering) => {
                    values.width = selected ? 5 : (hovering ? 4 : 3);
                    values.opacity = selected || hovering ? 1 : 0.8;
                }
            }
        };
    }

    /**
     * Get physics configuration
     * @returns {Object}
     */
    static getPhysicsOptions() {
        return {
            enabled: true,
            barnesHut: {
                gravitationalConstant: -2500,
                centralGravity: 0.3,
                springLength: 150,
                springConstant: 0.04,
                damping: 0.09,
                avoidOverlap: 0.2
            },
            maxVelocity: 25,
            minVelocity: 0.1,
            solver: 'barnesHut',
            stabilization: {
                enabled: true,
                iterations: 800,
                updateInterval: 100,
                onlyDynamicEdges: false,
                fit: true
            },
            timestep: 0.5,
            adaptiveTimestep: true
        };
    }

    /**
     * Get interaction configuration
     * @returns {Object}
     */
    static getInteractionOptions() {
        return {
            hover: true,
            hoverConnectedEdges: true,
            selectConnectedEdges: false,
            tooltipDelay: 300,
            zoomView: true,
            dragView: true,
            dragNodes: true,
            multiselect: false,
            keyboard: {
                enabled: false
            },
            navigationButtons: false,
            zoomSpeed: 0.8
        };
    }

    /**
     * Get layout configuration
     * @returns {Object}
     */
    static getLayoutOptions() {
        return {
            randomSeed: 42, // Fixed seed for consistent layout
            improvedLayout: true,
            clusterThreshold: 150,
            hierarchical: {
                enabled: false,
                levelSeparation: 150,
                nodeSpacing: 100,
                treeSpacing: 200,
                blockShifting: true,
                edgeMinimization: true,
                parentCentralization: true,
                direction: 'UD',
                sortMethod: 'hubsize'
            }
        };
    }

    /**
     * Get complete network options
     * @returns {Object}
     */
    static getNetworkOptions() {
        return {
            nodes: this.getNodeOptions(),
            edges: this.getEdgeOptions(),
            physics: this.getPhysicsOptions(),
            interaction: this.getInteractionOptions(),
            layout: this.getLayoutOptions(),
            groups: {
                root: {
                    color: {
                        background: '#5A55D2',
                        border: '#ffffff'
                    },
                    size: 40
                },
                invitee: {
                    color: {
                        background: '#5A55D2',
                        border: '#ffffff'
                    },
                    size: 35
                }
            },
            manipulation: {
                enabled: false
            }
        };
    }

    /**
     * Get animation options for smooth transitions
     * @returns {Object}
     */
    static getAnimationOptions() {
        return {
            fit: {
                animation: {
                    duration: 1000,
                    easingFunction: 'easeInOutQuad'
                }
            },
            focus: {
                animation: {
                    duration: 800,
                    easingFunction: 'easeInOutCubic'
                }
            }
        };
    }
}