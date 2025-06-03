<?php

namespace Database\Seeders;

use App\Models\KpiTask;
use App\Models\KpiTarget;
use App\Models\KpiTurtleItem;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class KpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding KPI tasks...');
        $this->seedTasks();

        $this->command->info('Seeding KPI targets...');
        $this->seedTargets();

        $this->command->info('Seeding KPI turtle items...');
        $this->seedTurtleItems();
    }

    /**
     * Seed KPI tasks.
     */
    private function seedTasks(): void
    {
        $tasks = [
            // Account creation tasks
            [
                'name' => 'Create MEXC Account',
                'description' => 'Create a new MEXC account to earn love points',
                'type' => 'recurring',
                'category' => 'account_creation',
                'love_reward' => 10,
                'experience_reward' => 15,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => false,
            ],
            [
                'name' => 'Create 3 MEXC Accounts',
                'description' => 'Create 3 new MEXC accounts to earn bonus love points',
                'type' => 'daily',
                'category' => 'account_creation',
                'love_reward' => 35,
                'experience_reward' => 50,
                'requirements' => ['count' => 3],
                'active' => true,
                'is_milestone' => false,
            ],
            [
                'name' => 'Create 5 MEXC Accounts',
                'description' => 'Create 5 new MEXC accounts to earn major love points',
                'type' => 'daily',
                'category' => 'account_creation',
                'love_reward' => 70,
                'experience_reward' => 100,
                'requirements' => ['count' => 5],
                'active' => true,
                'is_milestone' => true,
            ],

            // Email account tasks
            [
                'name' => 'Create Email Account',
                'description' => 'Create a new email account to earn love points',
                'type' => 'recurring',
                'category' => 'email_creation',
                'love_reward' => 5,
                'experience_reward' => 8,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => false,
            ],

            // Proxy tasks
            [
                'name' => 'Add Proxy',
                'description' => 'Add a new proxy to earn love points',
                'type' => 'recurring',
                'category' => 'proxy_creation',
                'love_reward' => 5,
                'experience_reward' => 8,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => false,
            ],

            // Web3 wallet tasks
            [
                'name' => 'Create Web3 Wallet',
                'description' => 'Create a new Web3 wallet to earn love points',
                'type' => 'recurring',
                'category' => 'wallet_creation',
                'love_reward' => 8,
                'experience_reward' => 12,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => false,
            ],

            // Daily tasks
            [
                'name' => 'Daily Login',
                'description' => 'Log in to the system to earn daily love points',
                'type' => 'daily',
                'category' => 'engagement',
                'love_reward' => 5,
                'experience_reward' => 5,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => false,
            ],
            [
                'name' => 'Feed Your Turtle',
                'description' => 'Feed your turtle to keep it happy and earn experience points',
                'type' => 'daily',
                'category' => 'turtle_care',
                'love_reward' => 3,
                'experience_reward' => 10,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => false,
            ],

            // Weekly tasks
            [
                'name' => 'Complete Account Chain',
                'description' => 'Create a full account chain (proxy, email, MEXC account, Web3 wallet)',
                'type' => 'weekly',
                'category' => 'account_creation',
                'love_reward' => 50,
                'experience_reward' => 80,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => true,
            ],
            [
                'name' => 'Complete All Daily Tasks',
                'description' => 'Complete all daily tasks in a single day',
                'type' => 'weekly',
                'category' => 'engagement',
                'love_reward' => 25,
                'experience_reward' => 40,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => false,
            ],

            // One-time achievements
            [
                'name' => 'First MEXC Account',
                'description' => 'Create your first MEXC account',
                'type' => 'one-time',
                'category' => 'achievement',
                'love_reward' => 20,
                'experience_reward' => 30,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => true,
            ],
            [
                'name' => 'Customize Your Turtle',
                'description' => 'Customize your turtle for the first time',
                'type' => 'one-time',
                'category' => 'achievement',
                'love_reward' => 15,
                'experience_reward' => 20,
                'requirements' => ['count' => 1],
                'active' => true,
                'is_milestone' => false,
            ],
        ];

        foreach ($tasks as $task) {
            KpiTask::create($task);
        }
    }

    /**
     * Seed KPI targets.
     */
    private function seedTargets(): void
    {
        // Get current month start and end dates
        $currentMonth = Carbon::now();
        $monthStart = $currentMonth->copy()->startOfMonth();
        $monthEnd = $currentMonth->copy()->endOfMonth();

        // Get current week start and end dates
        $weekStart = $currentMonth->copy()->startOfWeek();
        $weekEnd = $currentMonth->copy()->endOfWeek();

        $targets = [
            // Weekly targets
            [
                'name' => 'Weekly MEXC Accounts',
                'description' => 'Create 10 MEXC accounts this week',
                'metric_type' => 'mexc_accounts',
                'target_value' => 10,
                'love_reward' => 100,
                'experience_reward' => 150,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'period_type' => 'weekly',
                'active' => true,
                'metadata' => null,
            ],
            [
                'name' => 'Weekly Email Accounts',
                'description' => 'Create 15 email accounts this week',
                'metric_type' => 'email_accounts',
                'target_value' => 15,
                'love_reward' => 75,
                'experience_reward' => 100,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'period_type' => 'weekly',
                'active' => true,
                'metadata' => null,
            ],
            [
                'name' => 'Weekly Proxies',
                'description' => 'Add 20 proxies this week',
                'metric_type' => 'proxies',
                'target_value' => 20,
                'love_reward' => 75,
                'experience_reward' => 100,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'period_type' => 'weekly',
                'active' => true,
                'metadata' => null,
            ],
            [
                'name' => 'Weekly Web3 Wallets',
                'description' => 'Create 5 Web3 wallets this week',
                'metric_type' => 'web3_wallets',
                'target_value' => 5,
                'love_reward' => 50,
                'experience_reward' => 75,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'period_type' => 'weekly',
                'active' => true,
                'metadata' => null,
            ],

            // Monthly targets
            [
                'name' => 'Monthly MEXC Accounts',
                'description' => 'Create 40 MEXC accounts this month',
                'metric_type' => 'mexc_accounts',
                'target_value' => 40,
                'love_reward' => 300,
                'experience_reward' => 500,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
                'metadata' => null,
            ],
            [
                'name' => 'Monthly Email Accounts',
                'description' => 'Create 50 email accounts this month',
                'metric_type' => 'email_accounts',
                'target_value' => 50,
                'love_reward' => 200,
                'experience_reward' => 350,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
                'metadata' => null,
            ],
            [
                'name' => 'Monthly Proxies',
                'description' => 'Add 60 proxies this month',
                'metric_type' => 'proxies',
                'target_value' => 60,
                'love_reward' => 200,
                'experience_reward' => 350,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
                'metadata' => null,
            ],
            [
                'name' => 'Monthly Web3 Wallets',
                'description' => 'Create 20 Web3 wallets this month',
                'metric_type' => 'web3_wallets',
                'target_value' => 20,
                'love_reward' => 150,
                'experience_reward' => 250,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
                'metadata' => null,
            ],
            [
                'name' => 'Task Completion Champion',
                'description' => 'Complete 30 tasks this month',
                'metric_type' => 'completed_tasks',
                'target_value' => 30,
                'love_reward' => 150,
                'experience_reward' => 250,
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'period_type' => 'monthly',
                'active' => true,
                'metadata' => null,
            ],
        ];

        foreach ($targets as $target) {
            KpiTarget::create($target);
        }
    }

    /**
     * Seed KPI turtle items.
     */
    private function seedTurtleItems(): void
    {
        $items = [
            // Shells
            [
                'name' => 'Green Shell',
                'description' => 'A basic green turtle shell',
                'type' => 'shell',
                'love_cost' => 0, // Free default item
                'image_path' => '/images/turtles/shells/green.png',
                'attributes' => ['color' => 'green'],
                'available' => true,
                'required_level' => 1,
            ],
            [
                'name' => 'Blue Shell',
                'description' => 'A vibrant blue turtle shell',
                'type' => 'shell',
                'love_cost' => 50,
                'image_path' => '/images/turtles/shells/blue.png',
                'attributes' => ['color' => 'blue'],
                'available' => true,
                'required_level' => 3,
            ],
            [
                'name' => 'Red Shell',
                'description' => 'A striking red turtle shell',
                'type' => 'shell',
                'love_cost' => 75,
                'image_path' => '/images/turtles/shells/red.png',
                'attributes' => ['color' => 'red'],
                'available' => true,
                'required_level' => 5,
            ],
            [
                'name' => 'Gold Shell',
                'description' => 'A luxurious gold turtle shell',
                'type' => 'shell',
                'love_cost' => 200,
                'image_path' => '/images/turtles/shells/gold.png',
                'attributes' => ['color' => 'gold'],
                'available' => true,
                'required_level' => 10,
            ],
            [
                'name' => 'Rainbow Shell',
                'description' => 'A magical rainbow turtle shell',
                'type' => 'shell',
                'love_cost' => 500,
                'image_path' => '/images/turtles/shells/rainbow.png',
                'attributes' => ['color' => 'rainbow', 'animated' => true],
                'available' => true,
                'required_level' => 20,
            ],

            // Backgrounds
            [
                'name' => 'Beach',
                'description' => 'A sunny beach background',
                'type' => 'background',
                'love_cost' => 0, // Free default
                'image_path' => '/images/turtles/backgrounds/beach.png',
                'attributes' => ['theme' => 'beach'],
                'available' => true,
                'required_level' => 1,
            ],
            [
                'name' => 'Ocean',
                'description' => 'A deep blue ocean background',
                'type' => 'background',
                'love_cost' => 30,
                'image_path' => '/images/turtles/backgrounds/ocean.png',
                'attributes' => ['theme' => 'ocean'],
                'available' => true,
                'required_level' => 2,
            ],
            [
                'name' => 'Coral Reef',
                'description' => 'A colorful coral reef background',
                'type' => 'background',
                'love_cost' => 75,
                'image_path' => '/images/turtles/backgrounds/coral.png',
                'attributes' => ['theme' => 'coral'],
                'available' => true,
                'required_level' => 5,
            ],
            [
                'name' => 'Tropical Island',
                'description' => 'A beautiful tropical island background',
                'type' => 'background',
                'love_cost' => 120,
                'image_path' => '/images/turtles/backgrounds/island.png',
                'attributes' => ['theme' => 'island'],
                'available' => true,
                'required_level' => 8,
            ],
            [
                'name' => 'Deep Sea',
                'description' => 'A mysterious deep sea background',
                'type' => 'background',
                'love_cost' => 200,
                'image_path' => '/images/turtles/backgrounds/deepsea.png',
                'attributes' => ['theme' => 'deepsea', 'animated' => true],
                'available' => true,
                'required_level' => 12,
            ],
            [
                'name' => 'Space',
                'description' => 'An out-of-this-world space background',
                'type' => 'background',
                'love_cost' => 350,
                'image_path' => '/images/turtles/backgrounds/space.png',
                'attributes' => ['theme' => 'space', 'animated' => true],
                'available' => true,
                'required_level' => 15,
            ],

            // Accessories
            [
                'name' => 'Sunglasses',
                'description' => 'Cool sunglasses for your turtle',
                'type' => 'accessory',
                'love_cost' => 25,
                'image_path' => '/images/turtles/accessories/sunglasses.png',
                'attributes' => ['slot' => 'face'],
                'available' => true,
                'required_level' => 2,
            ],
            [
                'name' => 'Bow Tie',
                'description' => 'A fancy bow tie for your turtle',
                'type' => 'accessory',
                'love_cost' => 35,
                'image_path' => '/images/turtles/accessories/bowtie.png',
                'attributes' => ['slot' => 'neck'],
                'available' => true,
                'required_level' => 3,
            ],
            [
                'name' => 'Top Hat',
                'description' => 'A sophisticated top hat for your turtle',
                'type' => 'accessory',
                'love_cost' => 50,
                'image_path' => '/images/turtles/accessories/tophat.png',
                'attributes' => ['slot' => 'head'],
                'available' => true,
                'required_level' => 4,
            ],
            [
                'name' => 'Flower Crown',
                'description' => 'A beautiful flower crown for your turtle',
                'type' => 'accessory',
                'love_cost' => 60,
                'image_path' => '/images/turtles/accessories/flowercrown.png',
                'attributes' => ['slot' => 'head'],
                'available' => true,
                'required_level' => 5,
            ],
            [
                'name' => 'Scarf',
                'description' => 'A cozy scarf for your turtle',
                'type' => 'accessory',
                'love_cost' => 45,
                'image_path' => '/images/turtles/accessories/scarf.png',
                'attributes' => ['slot' => 'neck'],
                'available' => true,
                'required_level' => 4,
            ],
            [
                'name' => 'Backpack',
                'description' => 'A cute backpack for your turtle',
                'type' => 'accessory',
                'love_cost' => 70,
                'image_path' => '/images/turtles/accessories/backpack.png',
                'attributes' => ['slot' => 'back'],
                'available' => true,
                'required_level' => 6,
            ],
            [
                'name' => 'Crown',
                'description' => 'A royal crown for your turtle',
                'type' => 'accessory',
                'love_cost' => 150,
                'image_path' => '/images/turtles/accessories/crown.png',
                'attributes' => ['slot' => 'head'],
                'available' => true,
                'required_level' => 10,
            ],
            [
                'name' => 'Superhero Cape',
                'description' => 'A superhero cape for your turtle',
                'type' => 'accessory',
                'love_cost' => 100,
                'image_path' => '/images/turtles/accessories/cape.png',
                'attributes' => ['slot' => 'back'],
                'available' => true,
                'required_level' => 8,
            ],
        ];

        foreach ($items as $item) {
            KpiTurtleItem::create($item);
        }
    }
}