// resources/js/onboarding.js

import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

document.addEventListener('DOMContentLoaded', () => {
    // Add button to manually start the tour
    const tourButton = document.getElementById('start-tour-button');
    if (tourButton) {
        tourButton.addEventListener('click', initTour);
    }

    // Add event listener for the tour button in dropdown menu
    const tourMenuButton = document.getElementById('start-tour-menu');
    if (tourMenuButton) {
        tourMenuButton.addEventListener('click', initTour);
    }

    // Listen for a custom event to start the tour
    document.addEventListener('start-tour', initTour);
});

// Make the function globally accessible
window.initTour = initTour;

function initTour() {
    // Close any existing tour first
    if (window.tour) {
        window.tour.complete();
    }

    // Create a new tour
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

    // Welcome step
    tour.addStep({
        id: 'welcome',
        text: `
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-wave-square text-secondary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-text-primary">Welcome to TurtleBoard!</h3>
                <p class="text-text-secondary">Let's take a quick tour of the dashboard features.</p>
            </div>
        `,
        buttons: [
            {
                text: 'Skip Tour',
                action: tour.complete,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Start Tour',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Dashboard overview
    tour.addStep({
        id: 'dashboard-overview',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Dashboard Overview</h3>
            <p class="text-text-secondary mb-2">This is your central hub for managing all your accounts.</p>
            <p class="text-text-secondary">The cards above show your account statistics at a glance.</p>
        `,
        attachTo: {
            element: '.account-card:first-child',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Sidebar navigation
    tour.addStep({
        id: 'sidebar',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Navigation</h3>
            <p class="text-text-secondary">Use the sidebar to navigate between different sections:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li>MEXC Accounts</li>
                <li>Email Accounts</li>
                <li>Proxies</li>
                <li>Web3 Wallets</li>
            </ul>
        `,
        attachTo: {
            element: '#sidebar',
            on: 'right'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Account management
    tour.addStep({
        id: 'account-management',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Account Management</h3>
            <p class="text-text-secondary">Each account type can be managed separately:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li>Create new accounts with the Add button</li>
                <li>View, edit, or delete existing accounts</li>
                <li>Filter accounts by status or search for specific ones</li>
            </ul>
        `,
        attachTo: {
            element: '.flex.justify-between.items-center:first-child',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Add new accounts
    tour.addStep({
        id: 'add-new',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Adding New Accounts</h3>
            <p class="text-text-secondary">Click the "Add" button in any section to create new accounts.</p>
            <p class="text-text-secondary mt-2">The system will guide you through a step-by-step process.</p>
        `,
        attachTo: {
            element: '.bg-secondary.hover\\:bg-secondary\\/90',
            on: 'left'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Filtering and search
    tour.addStep({
        id: 'filtering',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Filtering & Search</h3>
            <p class="text-text-secondary">Use the filter options to narrow down your accounts:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li>Filter by status (active, inactive, etc.)</li>
                <li>Search by email, address, or other properties</li>
                <li>Clear filters to see all accounts again</li>
            </ul>
        `,
        attachTo: {
            element: '.bg-card.p-4.rounded-card.shadow-card',
            on: 'top'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Statistics and data
    tour.addStep({
        id: 'statistics',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Statistics</h3>
            <p class="text-text-secondary">Review your account statistics and metrics:</p>
            <ul class="list-disc pl-5 text-text-secondary space-y-1 mt-2">
                <li>Total accounts by type</li>
                <li>Active vs. inactive accounts</li>
                <li>Connection status between accounts</li>
            </ul>
            <p class="text-text-secondary mt-2">These metrics help you maintain an overview of your account ecosystem.</p>
        `,
        attachTo: {
            element: '.grid.grid-cols-1.md\\:grid-cols-4.gap-4',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // User profile
    tour.addStep({
        id: 'user-profile',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Your Profile</h3>
            <p class="text-text-secondary">Access your profile settings, take this tour again, or log out from this menu.</p>
        `,
        attachTo: {
            element: '.flex.items-center.space-x-2',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Help button
    tour.addStep({
        id: 'help-button',
        text: `
            <h3 class="text-lg font-semibold text-text-primary mb-2">Need Help?</h3>
            <p class="text-text-secondary">Click this help button anytime to restart the tour.</p>
            <p class="text-text-secondary mt-2">You can also find the tour option in your profile dropdown menu.</p>
        `,
        attachTo: {
            element: '#start-tour-button',
            on: 'bottom'
        },
        buttons: [
            {
                text: 'Back',
                action: tour.back,
                classes: 'shepherd-button-secondary'
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Finishing step
    tour.addStep({
        id: 'finish',
        text: `
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-text-primary">You're All Set!</h3>
                <p class="text-text-secondary">You can start by adding your first accounts.</p>
                <p class="text-text-secondary mt-2">If you need this tour again, click the help button <i class="fas fa-question text-primary"></i> in the header.</p>
            </div>
        `,
        buttons: [
            {
                text: 'Finish',
                action: tour.complete,
                classes: 'shepherd-button-primary'
            }
        ]
    });

    // Start the tour
    tour.start();
}