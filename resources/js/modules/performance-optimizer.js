// resources/js/modules/performance-optimizer.js

/**
 * Performance optimization utilities for large networks
 */
export class PerformanceOptimizer {
    constructor() {
        this.isOptimized = false;
        this.originalOptions = null;
        this.thresholds = {
            nodeCount: 100,
            edgeCount: 200,
            complexAnimations: 50
        };
    }

    /**
     * Analyze network size and recommend optimizations
     * @param {number} nodeCount
     * @param {number} edgeCount
     * @returns {Object}
     */
    analyzeNetwork(nodeCount, edgeCount) {
        const analysis = {
            needsOptimization: false,
            recommendations: [],
            performance: 'good'
        };

        if (nodeCount > this.thresholds.nodeCount || edgeCount > this.thresholds.edgeCount) {
            analysis.needsOptimization = true;
            analysis.performance = nodeCount > 200 || edgeCount > 400 ? 'poor' : 'moderate';

            analysis.recommendations.push(
                'Consider enabling performance mode for better responsiveness',
                'Reduce physics calculations for smoother interactions',
                'Simplify visual effects for large networks'
            );
        }

        if (nodeCount > this.thresholds.complexAnimations) {
            analysis.recommendations.push(
                'Disable complex animations to improve frame rate'
            );
        }

        return analysis;
    }

    /**
     * Get optimized network options for large networks
     * @param {Object} baseOptions
     * @param {number} nodeCount
     * @param {number} edgeCount
     * @returns {Object}
     */
    getOptimizedOptions(baseOptions, nodeCount, edgeCount) {
        if (!this.shouldOptimize(nodeCount, edgeCount)) {
            return baseOptions;
        }

        const optimizedOptions = JSON.parse(JSON.stringify(baseOptions));

        // Optimize physics for large networks
        if (nodeCount > this.thresholds.nodeCount) {
            optimizedOptions.physics = {
                ...optimizedOptions.physics,
                enabled: true,
                barnesHut: {
                    ...optimizedOptions.physics.barnesHut,
                    gravitationalConstant: -8000,
                    centralGravity: 0.1,
                    springLength: 200,
                    springConstant: 0.02,
                    damping: 0.1,
                    avoidOverlap: 0.1
                },
                maxVelocity: 15,
                timestep: 0.8,
                adaptiveTimestep: true,
                stabilization: {
                    enabled: true,
                    iterations: 400,
                    updateInterval: 200,
                    onlyDynamicEdges: true,
                    fit: false
                }
            };
        }

        // Simplify nodes for performance
        optimizedOptions.nodes = {
            ...optimizedOptions.nodes,
            shadow: {
                enabled: false
            },
            scaling: {
                min: 15,
                max: 30
            },
            font: {
                ...optimizedOptions.nodes.font,
                size: 12,
                strokeWidth: 0
            }
        };

        // Simplify edges for performance
        optimizedOptions.edges = {
            ...optimizedOptions.edges,
            shadow: {
                enabled: false
            },
            smooth: {
                enabled: false
            },
            width: 2,
            selectionWidth: 3,
            hoverWidth: 3
        };

        // Disable expensive interactions
        optimizedOptions.interaction = {
            ...optimizedOptions.interaction,
            hover: nodeCount < this.thresholds.nodeCount,
            tooltipDelay: 1000,
            zoomSpeed: 1
        };

        return optimizedOptions;
    }

    /**
     * Check if optimization is needed
     * @param {number} nodeCount
     * @param {number} edgeCount
     * @returns {boolean}
     */
    shouldOptimize(nodeCount, edgeCount) {
        return nodeCount > this.thresholds.nodeCount || edgeCount > this.thresholds.edgeCount;
    }

    /**
     * Enable performance mode
     * @param {Network} network
     * @param {Object} options
     */
    enablePerformanceMode(network, options) {
        if (this.isOptimized) return;

        this.originalOptions = network.getOptionsFromConfigurator();

        const optimizedOptions = this.getOptimizedOptions(
            options,
            network.body.data.nodes.length,
            network.body.data.edges.length
        );

        network.setOptions(optimizedOptions);
        network.redraw();

        // Add performance CSS class
        const container = network.canvas.frame.canvas.parentElement;
        if (container) {
            container.classList.add('performance-mode');
        }

        this.isOptimized = true;
        console.log('Performance mode enabled');
    }

    /**
     * Disable performance mode
     * @param {Network} network
     */
    disablePerformanceMode(network) {
        if (!this.isOptimized || !this.originalOptions) return;

        network.setOptions(this.originalOptions);
        network.redraw();

        // Remove performance CSS class
        const container = network.canvas.frame.canvas.parentElement;
        if (container) {
            container.classList.remove('performance-mode');
        }

        this.isOptimized = false;
        this.originalOptions = null;
        console.log('Performance mode disabled');
    }

    /**
     * Get memory usage statistics
     * @returns {Object}
     */
    getMemoryStats() {
        if (performance.memory) {
            return {
                used: Math.round(performance.memory.usedJSHeapSize / 1048576),
                total: Math.round(performance.memory.totalJSHeapSize / 1048576),
                limit: Math.round(performance.memory.jsHeapSizeLimit / 1048576)
            };
        }
        return null;
    }

    /**
     * Monitor frame rate
     * @param {Function} callback
     */
    monitorFrameRate(callback) {
        let frames = 0;
        let lastTime = performance.now();

        const monitor = () => {
            frames++;
            const currentTime = performance.now();

            if (currentTime - lastTime >= 1000) {
                const fps = Math.round((frames * 1000) / (currentTime - lastTime));
                callback(fps);
                frames = 0;
                lastTime = currentTime;
            }

            requestAnimationFrame(monitor);
        };

        requestAnimationFrame(monitor);
    }

    /**
     * Optimize node rendering for large datasets
     * @param {Array} nodes
     * @returns {Array}
     */
    optimizeNodeRendering(nodes) {
        if (nodes.length < this.thresholds.nodeCount) {
            return nodes;
        }

        return nodes.map(node => ({
            ...node,
            // Simplify visual properties for performance
            shadow: undefined,
            borderWidth: Math.min(node.borderWidth || 3, 2),
            font: {
                ...node.font,
                size: Math.max((node.font?.size || 14) - 2, 10),
                strokeWidth: 0
            }
        }));
    }

    /**
     * Optimize edge rendering for large datasets
     * @param {Array} edges
     * @returns {Array}
     */
    optimizeEdgeRendering(edges) {
        if (edges.length < this.thresholds.edgeCount) {
            return edges;
        }

        return edges.map(edge => ({
            ...edge,
            // Simplify visual properties for performance
            shadow: undefined,
            smooth: false,
            width: Math.min(edge.width || 3, 2)
        }));
    }

    /**
     * Batch update nodes/edges for better performance
     * @param {DataSet} dataset
     * @param {Array} updates
     * @param {number} batchSize
     */
    batchUpdate(dataset, updates, batchSize = 50) {
        const batches = [];
        for (let i = 0; i < updates.length; i += batchSize) {
            batches.push(updates.slice(i, i + batchSize));
        }

        batches.forEach((batch, index) => {
            setTimeout(() => {
                dataset.update(batch);
            }, index * 10);
        });
    }

    /**
     * Debounced network redraw
     * @param {Network} network
     * @param {number} delay
     * @returns {Function}
     */
    getDebouncedRedraw(network, delay = 100) {
        let timeoutId;

        return () => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                network.redraw();
            }, delay);
        };
    }

    /**
     * Get performance recommendations
     * @param {Object} stats
     * @returns {Array}
     */
    getPerformanceRecommendations(stats) {
        const recommendations = [];

        if (stats.nodes > 150) {
            recommendations.push({
                type: 'warning',
                message: 'Large number of nodes detected. Consider enabling performance mode.',
                action: 'enablePerformanceMode'
            });
        }

        if (stats.edges > 300) {
            recommendations.push({
                type: 'warning',
                message: 'Large number of edges detected. Consider simplifying edge styles.',
                action: 'simplifyEdges'
            });
        }

        const memoryStats = this.getMemoryStats();
        if (memoryStats && memoryStats.used > 100) {
            recommendations.push({
                type: 'error',
                message: 'High memory usage detected. Consider reducing visual complexity.',
                action: 'reduceComplexity'
            });
        }

        return recommendations;
    }

    /**
     * Auto-optimize based on network size
     * @param {Network} network
     * @param {Object} options
     * @param {Object} stats
     */
    autoOptimize(network, options, stats) {
        const analysis = this.analyzeNetwork(stats.nodes, stats.edges);

        if (analysis.needsOptimization) {
            console.log('Auto-optimizing network for better performance...');
            this.enablePerformanceMode(network, options);

            // Show user notification
            const event = new CustomEvent('networkOptimized', {
                detail: {
                    analysis,
                    optimized: true
                }
            });
            document.dispatchEvent(event);
        }
    }
}