// resources/js/onboarding.js

import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

// Main onboarding system
document.addEventListener('DOMContentLoaded', () => {
    // Add button to manually start the tour
    const tourButton = document.getElementById('start-tour-button');
    if (tourButton) {
        tourButton.addEventListener('click', () => startTour());
    }

    // Add event listener for the tour button in dropdown menu
    const tourMenuButton = document.getElementById('start-tour-menu');
    if (tourMenuButton) {
        tourMenuButton.addEventListener('click', () => startTour());
    }

    // Listen for a custom event to start the tour
    document.addEventListener('start-tour', () => startTour());
});

// Make the function globally accessible
window.startTour = startTour;

/**
 * Start the appropriate tour based on the current page
 */
function startTour() {
    // Close any existing tour first
    if (window.tour) {
        window.tour.complete();
    }

    // Detect current page based on URL or body data attribute
    const currentPage = detectCurrentPage();

    // Create the appropriate tour
    switch(currentPage) {
        case 'dashboard':
            initDashboardTour();
            break;
        case 'mexc-accounts':
            initMexcAccountsTour();
            break;
        case 'email-accounts':
            initEmailAccountsTour();
            break;
        case 'proxies':
            initProxiesTour();
            break;
        case 'web3-wallets':
            initWeb3WalletsTour();
            break;
        default:
            // Fallback to dashboard tour if page can't be detected
            initDashboardTour();
    }
}

/**
 * Detect the current page based on URL or data attributes
 */
function detectCurrentPage() {
    // Check for data attribute on body
    const bodyPageAttr = document.body.dataset.page;
    if (bodyPageAttr) {
        return bodyPageAttr;
    }

    // Check URL patterns
    const currentPath = window.location.pathname;

    if (currentPath.includes('/dashboard')) {
        return 'dashboard';
    } else if (currentPath.includes('/accounts/mexc')) {
        return 'mexc-accounts';
    } else if (currentPath.includes('/accounts/email')) {
        return 'email-accounts';
    } else if (currentPath.includes('/accounts/proxy')) {
        return 'proxies';
    } else if (currentPath.includes('/accounts/web3')) {
        return 'web3-wallets';
    }

    // Try to detect based on heading text
    const pageHeadings = document.querySelectorAll('h2');
    for (const heading of pageHeadings) {
        const headingText = heading.textContent.toLowerCase();

        if (headingText.includes('mexc')) {
            return 'mexc-accounts';
        } else if (headingText.includes('email')) {
            return 'email-accounts';
        } else if (headingText.includes('proxy')) {
            return 'proxies';
        } else if (headingText.includes('web3') || headingText.includes('wallet')) {
            return 'web3-wallets';
        }
    }

    // Default to dashboard if unable to detect
    return 'dashboard';
}

/**
 * Create a new tour with common settings
 */
function createTour() {
    const tour = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            classes: 'shepherd-theme-custom',
            scrollTo: true,
            cancelIcon: {
                enabled: true
            },
            highlightClass: 'shepherd-highlighted'
        }
    });

    // Store the tour instance globally for access
    window.tour = tour;

    return tour;
}

/**
 * Helper to add common tour navigation buttons
 */
function addTourButtons(step, tour, isFirstStep = false, isLastStep = false) {
    const buttons = [];

    // First step may have skip option
    if (isFirstStep) {
        buttons.push({
            text: 'Skip Tour',
            action: tour.complete,
            classes: 'shepherd-button-secondary'
        });
    } else {
        // Not first step, add back button
        buttons.push({
            text: 'Back',
            action: tour.back,
            classes: 'shepherd-button-secondary'
        });
    }

    // Last step has finish button
    if (isLastStep) {
        buttons.push({
            text: 'Finish',
            action: tour.complete,
            classes: 'shepherd-button-primary'
        });
    } else {
        // Not last step, add next button
        buttons.push({
            text: 'Next',
            action: tour.next,
            classes: 'shepherd-button-primary'
        });
    }

    return buttons;
}

/* ---------------- PAGE-SPECIFIC TOURS ---------------- */

/**
 * Dashboard page tour
 */
function initDashboardTour() {
    const tour = createTour();

    // Welcome step
    tour.addStep({
        id: 'dashboard-welcome',
        text: `
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-wave-square text-secondary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-text-primary">Welcome to TurtleBoard!</h3>
                <p class="text-text-secondary">Let's explore the main features of your dashboard.</p>
            </div>
        `,
        buttons: addTourButtons(this, tour, true)
    });

    // Dashboard stats cards
    tour.addStep({
        id: 'dashboard-stats',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Account Statistics</h3>
            <p class="text-text-secondary">These cards show you a quick overview of all your accounts.</p>
            <p class="text-text-secondary mt-2">You can see totals for each account type and their statuses at a glance.</p>
        `,
        attachTo: {
            element: '.account-card:first-child',
            on: 'bottom'
        },
        buttons: addTourButtons(this, tour)
    });

    // Sidebar navigation
    tour.addStep({
        id: 'dashboard-sidebar',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Main Navigation</h3>
            <p class="text-text-secondary">Use the sidebar to access different areas:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li><strong>MEXC Accounts</strong> - Manage exchange accounts</li>
                <li><strong>Email Accounts</strong> - Manage linked email accounts</li>
                <li><strong>Proxies</strong> - Configure proxy servers</li>
                <li><strong>Web3 Wallets</strong> - Manage blockchain wallets</li>
            </ul>
        `,
        attachTo: {
            element: '#sidebar',
            on: 'right'
        },
        buttons: addTourButtons(this, tour)
    });

    // User profile
    tour.addStep({
        id: 'dashboard-profile',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Your Profile</h3>
            <p class="text-text-secondary">Click here to access your profile settings, view the tutorial again, or log out.</p>
        `,
        attachTo: {
            element: '.flex.items-center.space-x-2',
            on: 'bottom'
        },
        buttons: addTourButtons(this, tour)
    });

    // Help button
    tour.addStep({
        id: 'dashboard-help',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Need Help?</h3>
            <p class="text-text-secondary">Click this help button anytime to see a page-specific tutorial.</p>
            <p class="text-text-secondary mt-2">Each page has its own custom guide to help you use that feature.</p>
        `,
        attachTo: {
            element: '#start-tour-button',
            on: 'bottom'
        },
        buttons: addTourButtons(this, tour, false, true)
    });

    // Start the tour
    tour.start();
}

/**
 * MEXC Accounts page tour
 */
function initMexcAccountsTour() {
    const tour = createTour();

    // MEXC Accounts overview
    tour.addStep({
        id: 'mexc-welcome',
        text: `
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-wallet text-secondary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-text-primary">MEXC Account Management</h3>
                <p class="text-text-secondary">This section helps you manage your cryptocurrency exchange accounts.</p>
            </div>
        `,
        buttons: addTourButtons(this, tour, true)
    });

    // Add new MEXC account
    tour.addStep({
        id: 'mexc-add-new',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Adding MEXC Accounts</h3>
            <p class="text-text-secondary">Click this button to add a new MEXC account.</p>
            <p class="text-text-secondary mt-2">You'll be guided through linking an email account and optional Web3 wallet.</p>
        `,
        attachTo: {
            element: '.bg-secondary.hover\\:bg-secondary\\/90',
            on: 'left'
        },
        buttons: addTourButtons(this, tour)
    });

    // Filtering and search
    tour.addStep({
        id: 'mexc-filtering',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Find Accounts Quickly</h3>
            <p class="text-text-secondary">Use these filters to find specific accounts:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li>Filter by status (active, inactive, suspended)</li>
                <li>Search by email address</li>
                <li>Reset filters to see all accounts</li>
            </ul>
        `,
        attachTo: {
            element: '.bg-card.p-4.rounded-card.shadow-card',
            on: 'top'
        },
        buttons: addTourButtons(this, tour)
    });

    // Account actions
    tour.addStep({
        id: 'mexc-actions',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Account Actions</h3>
            <p class="text-text-secondary">For each account, you can:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li><i class="fas fa-eye text-primary"></i> View account credentials</li>
                <li><i class="fas fa-edit text-secondary"></i> Edit account details</li>
                <li><i class="fas fa-trash text-danger"></i> Delete the account</li>
            </ul>
        `,
        attachTo: {
            element: '.whitespace-nowrap.text-right.text-sm.font-medium',
            on: 'left'
        },
        buttons: addTourButtons(this, tour, false, true)
    });

    // Start the tour
    tour.start();
}

/**
 * Email Accounts page tour
 */
function initEmailAccountsTour() {
    const tour = createTour();

    // Email Accounts overview
    tour.addStep({
        id: 'email-welcome',
        text: `
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-envelope text-secondary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-text-primary">Email Account Management</h3>
                <p class="text-text-secondary">This section helps you manage the email accounts linked to your MEXC accounts.</p>
            </div>
        `,
        buttons: addTourButtons(this, tour, true)
    });

    // Add new email account
    tour.addStep({
        id: 'email-add-new',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Adding Email Accounts</h3>
            <p class="text-text-secondary">Click this button to add a new email account.</p>
            <p class="text-text-secondary mt-2">You can choose from Gmail, Outlook, Yahoo, or Rambler providers.</p>
        `,
        attachTo: {
            element: '.bg-secondary.hover\\:bg-secondary\\/90',
            on: 'left'
        },
        buttons: addTourButtons(this, tour)
    });

    // Provider filters
    tour.addStep({
        id: 'email-providers',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Email Providers</h3>
            <p class="text-text-secondary">You can filter accounts by email provider:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li>Gmail - Google mail accounts</li>
                <li>Outlook - Microsoft email accounts</li>
                <li>Yahoo - Yahoo mail accounts</li>
                <li>Rambler - Rambler mail accounts</li>
            </ul>
        `,
        attachTo: {
            element: '#provider',
            on: 'bottom'
        },
        buttons: addTourButtons(this, tour)
    });

    // Email credentials
    tour.addStep({
        id: 'email-credentials',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Email Security</h3>
            <p class="text-text-secondary">Your email credentials are securely encrypted in the database.</p>
            <p class="text-text-secondary mt-2">Use the eye icon to view passwords when needed.</p>
        `,
        attachTo: {
            element: '.whitespace-nowrap.text-right.text-sm.font-medium',
            on: 'left'
        },
        buttons: addTourButtons(this, tour, false, true)
    });

    // Start the tour
    tour.start();
}

/**
 * Proxies page tour
 */
function initProxiesTour() {
    const tour = createTour();

    // Proxies overview
    tour.addStep({
        id: 'proxies-welcome',
        text: `
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-server text-secondary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-text-primary">Proxy Management</h3>
                <p class="text-text-secondary">This section helps you manage proxy servers for your accounts.</p>
            </div>
        `,
        buttons: addTourButtons(this, tour, true)
    });

    // Add new proxies
    tour.addStep({
        id: 'proxies-add-new',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Adding Proxies</h3>
            <p class="text-text-secondary">Click this button to add new proxy servers.</p>
            <p class="text-text-secondary mt-2">You can add individual proxies or bulk import them from a text file.</p>
        `,
        attachTo: {
            element: '.bg-secondary.hover\\:bg-secondary\\/90',
            on: 'left'
        },
        buttons: addTourButtons(this, tour)
    });

    // Validate proxies
    tour.addStep({
        id: 'proxies-validate',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Proxy Validation</h3>
            <p class="text-text-secondary">This button checks if your proxies are working properly.</p>
            <p class="text-text-secondary mt-2">The system will test connectivity and measure response times.</p>
        `,
        attachTo: {
            element: '#validateAllBtn',
            on: 'bottom'
        },
        buttons: addTourButtons(this, tour)
    });

    // Proxy status
    tour.addStep({
        id: 'proxies-status',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Proxy Status</h3>
            <p class="text-text-secondary">Proxies can have different statuses:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li><span class="text-success">Valid</span> - Working correctly</li>
                <li><span class="text-warning">Pending</span> - Not yet validated</li>
                <li><span class="text-danger">Invalid</span> - Not working</li>
            </ul>
        `,
        attachTo: {
            element: '.grid.grid-cols-1.md\\:grid-cols-3.gap-4',
            on: 'bottom'
        },
        buttons: addTourButtons(this, tour, false, true)
    });

    // Start the tour
    tour.start();
}

/**
 * Web3 Wallets page tour
 */
function initWeb3WalletsTour() {
    const tour = createTour();

    // Web3 Wallets overview
    tour.addStep({
        id: 'web3-welcome',
        text: `
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-link text-secondary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-text-primary">Web3 Wallet Management</h3>
                <p class="text-text-secondary">This section helps you manage cryptocurrency wallets for your accounts.</p>
            </div>
        `,
        buttons: addTourButtons(this, tour, true)
    });

    // Add new wallet
    tour.addStep({
        id: 'web3-add-new',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Adding Wallets</h3>
            <p class="text-text-secondary">Click this button to add a new Web3 wallet.</p>
            <p class="text-text-secondary mt-2">You can generate a new wallet or import an existing one.</p>
        `,
        attachTo: {
            element: '.bg-secondary.hover\\:bg-secondary\\/90',
            on: 'left'
        },
        buttons: addTourButtons(this, tour)
    });

    // Wallet networks
    tour.addStep({
        id: 'web3-networks',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Blockchain Networks</h3>
            <p class="text-text-secondary">The system supports different blockchain networks:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li><span class="text-blue-500">Ethereum</span> - ERC-20 tokens</li>
                <li><span class="text-yellow-500">Binance Smart Chain</span> - BEP-20 tokens</li>
            </ul>
        `,
        attachTo: {
            element: '.px-2.py-1.inline-flex.text-xs.leading-5.font-semibold.rounded-full',
            on: 'right'
        },
        buttons: addTourButtons(this, tour)
    });

    // Wallet security
    tour.addStep({
        id: 'web3-security',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Wallet Security</h3>
            <p class="text-text-secondary">Your wallet seed phrases are securely encrypted.</p>
            <p class="text-text-secondary mt-2">Remember to keep your seed phrases safe - they provide full access to your wallet.</p>
        `,
        attachTo: {
            element: '.whitespace-nowrap.text-right.text-sm.font-medium',
            on: 'left'
        },
        buttons: addTourButtons(this, tour, false, true)
    });

    // Start the tour
    tour.start();
}