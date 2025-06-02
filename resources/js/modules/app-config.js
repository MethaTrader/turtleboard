// resources/js/modules/app-config.js

/**
 * Application configuration and constants
 */
export const AppConfig = {
    // Network settings
    network: {
        container: {
            id: 'interactive-network-visualization',
            minHeight: 500,
            defaultHeight: 700
        },
        performance: {
            autoOptimize: true,
            nodeThreshold: 100,
            edgeThreshold: 200,
            memoryThreshold: 100, // MB
            fpsThreshold: 30
        },
        stabilization: {
            iterations: 1000,
            updateInterval: 100,
            timeout: 30000 // 30 seconds
        }
    },

    // UI settings
    ui: {
        toast: {
            duration: {
                success: 3000,
                error: 5000,
                warning: 4000,
                info: 0 // Manual dismiss
            },
            position: 'top-center',
            maxVisible: 3
        },
        modal: {
            closeOnEscape: true,
            closeOnBackdrop: true,
            animation: true
        },
        controlPanel: {
            position: 'top-right',
            collapsible: true,
            shortcuts: true
        }
    },

    // Data settings
    data: {
        maxRetries: 3,
        retryDelay: 1000,
        cacheTimeout: 5 * 60 * 1000, // 5 minutes
        batchSize: 50
    },

    // Animation settings
    animation: {
        enabled: true,
        duration: {
            short: 200,
            medium: 500,
            long: 1000
        },
        easing: {
            standard: 'ease-out',
            bounce: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
            smooth: 'cubic-bezier(0.4, 0.0, 0.2, 1)'
        },
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches
    },

    // Validation rules
    validation: {
        connection: {
            maxInvitations: 5,
            allowSelfInvite: false,
            preventDuplicates: true
        },
        referralLink: {
            required: false,
            maxLength: 255,
            patterns: [
                /^https?:\/\/.+$/i // Basic URL validation
            ]
        }
    },

    // Debug settings
    debug: {
        enabled: process.env.NODE_ENV === 'development',
        logLevel: 'info', // error, warn, info, debug
        showStats: false,
        showFrameRate: false
    },

    // Provider icons (embedded SVGs for reliability)
    providers: {
        Gmail: {
            name: 'Gmail',
            color: '#EA4335',
            icon: '/images/provides/google.png'
        },
        Outlook: {
            name: 'Outlook',
            color: '#0078D4',
            icon: '/images/provides/microsoft.png'
        },
        Yahoo: {
            name: 'Yahoo',
            color: '#7B00FF',
            icon: '/images/providers/yahoo.png'
        },
        iCloud: {
            name: 'iCloud',
            color: '#007AFF',
            icon: '/images/providers/apple.png'
        }
    },

    // Status colors and labels
    status: {
        referral: {
            pending: {
                color: '#F59E0B',
                label: 'Pending',
                icon: 'fas fa-clock'
            },
            completed: {
                color: '#00DEA3',
                label: 'Completed',
                icon: 'fas fa-check-circle'
            },
            cancelled: {
                color: '#F56565',
                label: 'Cancelled',
                icon: 'fas fa-times-circle'
            }
        },
        account: {
            active: {
                color: '#00DEA3',
                label: 'Active',
                icon: 'fas fa-check-circle'
            },
            inactive: {
                color: '#6B7280',
                label: 'Inactive',
                icon: 'fas fa-pause-circle'
            },
            suspended: {
                color: '#F56565',
                label: 'Suspended',
                icon: 'fas fa-ban'
            }
        }
    },

    // Keyboard shortcuts
    shortcuts: {
        toggleConnectionMode: 'c',
        resetView: 'r',
        togglePhysics: ' ', // spacebar
        escapeMode: 'Escape',
        showHelp: '?',
        toggleDebug: 'd'
    },

    // Error messages
    messages: {
        errors: {
            networkInit: 'Failed to initialize network visualization',
            dataLoad: 'Failed to load network data',
            connectionCreate: 'Failed to create referral connection',
            connectionDelete: 'Failed to delete referral connection',
            validation: 'Validation failed',
            network: 'Network error occurred',
            timeout: 'Request timed out'
        },
        success: {
            connectionCreated: 'Referral connection created successfully',
            connectionDeleted: 'Referral connection deleted successfully',
            dataRefreshed: 'Network data refreshed',
            settingsSaved: 'Settings saved successfully'
        },
        info: {
            connectionMode: 'Connection mode active - click two nodes to create a referral',
            firstNodeSelected: 'First node selected - now click the node to be invited',
            performanceMode: 'Performance mode enabled for better responsiveness',
            stabilizing: 'Organizing network layout...'
        }
    }
};

/**
 * Get configuration value by path
 * @param {string} path - Dot notation path (e.g., 'network.performance.autoOptimize')
 * @param {*} defaultValue - Default value if path not found
 * @returns {*}
 */
export function getConfig(path, defaultValue = null) {
    const keys = path.split('.');
    let current = AppConfig;

    for (const key of keys) {
        if (current && typeof current === 'object' && key in current) {
            current = current[key];
        } else {
            return defaultValue;
        }
    }

    return current;
}

/**
 * Set configuration value by path
 * @param {string} path - Dot notation path
 * @param {*} value - Value to set
 */
export function setConfig(path, value) {
    const keys = path.split('.');
    const lastKey = keys.pop();
    let current = AppConfig;

    for (const key of keys) {
        if (!(key in current) || typeof current[key] !== 'object') {
            current[key] = {};
        }
        current = current[key];
    }

    current[lastKey] = value;
}

/**
 * Check if debug mode is enabled
 * @returns {boolean}
 */
export function isDebugMode() {
    return getConfig('debug.enabled', false);
}

/**
 * Get provider configuration by name
 * @param {string} providerName
 * @returns {Object|null}
 */
export function getProviderConfig(providerName) {
    return getConfig(`providers.${providerName}`, null);
}

/**
 * Get status configuration by type and status
 * @param {string} type - 'referral' or 'account'
 * @param {string} status - The status name
 * @returns {Object|null}
 */
export function getStatusConfig(type, status) {
    return getConfig(`status.${type}.${status}`, null);
}

/**
 * Get animation duration by type
 * @param {string} type - 'short', 'medium', or 'long'
 * @returns {number}
 */
export function getAnimationDuration(type) {
    if (getConfig('animation.reducedMotion', false)) {
        return 0;
    }
    return getConfig(`animation.duration.${type}`, 500);
}

/**
 * Check if animations are enabled
 * @returns {boolean}
 */
export function areAnimationsEnabled() {
    return getConfig('animation.enabled', true) && !getConfig('animation.reducedMotion', false);
}

/**
 * Export configuration as JSON (for debugging)
 * @returns {string}
 */
export function exportConfig() {
    return JSON.stringify(AppConfig, null, 2);
}

/**
 * Reset configuration to defaults
 */
export function resetConfig() {
    // This would reset to default values - implementation depends on requirements
    console.warn('Config reset not implemented - would require default value storage');
}