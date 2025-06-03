<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KPI Gamification System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the KPI gamification
    | system, including turtle progression, rewards, and customization options.
    |
    */

    // Turtle configuration
    'turtle' => [
        // Base experience required for each level
        'base_level_exp' => 100,

        // Experience scaling factor (level^power)
        'level_exp_power' => 1.3,

        // Maximum level a turtle can reach
        'max_level' => 100,

        // Default turtle name
        'default_name' => 'Shelly',

        // Happiness decay settings
        'happiness' => [
            // How much happiness decays per day without interaction (percentage)
            'daily_decay' => 10,

            // How much happiness decays per day without feeding (percentage)
            'feed_decay' => 7,

            // Happiness boost per level (percentage)
            'level_boost' => 2,

            // Maximum happiness boost from level (percentage)
            'max_level_boost' => 20,
        ],

        // Mood thresholds based on happiness percentage
        'moods' => [
            'ecstatic' => 90, // 90-100%
            'happy' => 75,    // 75-89%
            'content' => 50,  // 50-74%
            'unhappy' => 25,  // 25-49%
            'miserable' => 0, // 0-24%
        ],
    ],

    // Task configuration
    'tasks' => [
        // Types of tasks
        'types' => [
            'daily' => 'Resets daily at midnight',
            'weekly' => 'Resets weekly on Monday',
            'recurring' => 'Can be completed multiple times',
            'one-time' => 'Can only be completed once',
        ],

        // Categories of tasks
        'categories' => [
            'account_creation' => 'Creating new accounts',
            'email_creation' => 'Creating new email accounts',
            'proxy_creation' => 'Adding new proxies',
            'wallet_creation' => 'Creating new Web3 wallets',
            'engagement' => 'User engagement tasks',
            'achievement' => 'Special achievements',
            'turtle_care' => 'Caring for your turtle',
        ],
    ],

    // Rewards configuration
    'rewards' => [
        // Love points earned for different actions
        'love_points' => [
            'mexc_account_creation' => 20,
            'email_account_creation' => 20,
            'proxy_addition' => 5,
            'web3_wallet_creation' => 8,
            'daily_login' => 5,
            'feed_turtle' => 3,
        ],

        // Experience points earned for different actions
        'experience_points' => [
            'mexc_account_creation' => 15,
            'email_account_creation' => 8,
            'proxy_addition' => 8,
            'web3_wallet_creation' => 12,
            'daily_login' => 5,
            'feed_turtle' => 10,
        ],
    ],

    // Shop configuration
    'shop' => [
        // Item types
        'item_types' => [
            'shell' => 'Turtle shells',
            'background' => 'Background environments',
            'accessory' => 'Accessories for your turtle',
        ],

        // Accessory slots
        'accessory_slots' => [
            'head' => 'Head accessories',
            'face' => 'Face accessories',
            'neck' => 'Neck accessories',
            'back' => 'Back accessories',
        ],
    ],

    // Leaderboard configuration
    'leaderboard' => [
        // Types of leaderboards
        'types' => [
            'level' => 'Ranked by turtle level',
            'love' => 'Ranked by total love points earned',
            'achievements' => 'Ranked by number of achievements',
        ],

        // Default number of entries to show
        'default_limit' => 10,

        // Refresh interval (in minutes)
        'refresh_interval' => 30,
    ],

    // Achievement configuration
    'achievements' => [
        // Achievement categories
        'categories' => [
            'account_milestones' => 'Account creation milestones',
            'turtle_milestones' => 'Turtle growth milestones',
            'task_milestones' => 'Task completion milestones',
            'special' => 'Special achievements',
        ],
    ],
];