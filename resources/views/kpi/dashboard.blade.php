@extends('layouts.app')

@section('content')
    <div x-data="kpiDashboard()" class="space-y-6">
        <!-- Turtle overview and KPI summary cards -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Turtle card -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 bg-primary/10 flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white">
                            <i class="fas fa-turtle text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="font-bold text-gray-800 group flex items-center">
                                <span x-text="turtleName"></span>
                                <button @click="openRenameModal" class="ml-2 text-gray-400 hover:text-gray-600 text-sm">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </h3>
                            <div class="text-sm text-gray-600">Level <span x-text="turtleLevel"></span> Turtle</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Mood</div>
                        <div class="font-medium" :class="{
                        'text-green-600': turtleHappiness >= 75,
                        'text-yellow-600': turtleHappiness >= 50 && turtleHappiness < 75,
                        'text-orange-500': turtleHappiness >= 25 && turtleHappiness < 50,
                        'text-red-500': turtleHappiness < 25
                    }" x-text="turtleMood"></div>
                    </div>
                </div>

                <div class="p-6 flex justify-between items-center">
                    <!-- Turtle Visualization -->
                    <div class="relative w-32 h-32 flex items-center justify-center">
                        <!-- Background -->
                        <div class="absolute inset-0 rounded-full" :style="`background-image: url('${turtleBackground}'); background-size: cover;`"></div>

                        <!-- Turtle -->
                        <div class="relative z-10">
                            <img :src="turtleShell" alt="Turtle" class="w-24 h-24 object-contain">

                            <!-- Accessories (if equipped) -->
                            <template x-for="accessory in equippedAccessories" :key="accessory.id">
                                <img :src="accessory.image_path" alt="Accessory" class="absolute top-0 left-0 w-24 h-24 object-contain">
                            </template>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="flex-1 ml-6 space-y-4">
                        <!-- Love Points -->
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Love Points</span>
                                <span class="text-sm font-medium text-primary" x-text="turtleLovePoints"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-primary h-2.5 rounded-full" :style="`width: ${Math.min(100, (turtleLovePoints / maxLovePoints) * 100)}%`"></div>
                            </div>
                        </div>

                        <!-- Experience -->
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Experience</span>
                                <span class="text-sm font-medium text-secondary" x-text="`${turtleExperience}/${nextLevelExp}`"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-secondary h-2.5 rounded-full" :style="`width: ${expPercentage}%`"></div>
                            </div>
                        </div>

                        <!-- Happiness -->
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Happiness</span>
                                <span class="text-sm font-medium" :class="{
                                'text-green-600': turtleHappiness >= 75,
                                'text-yellow-600': turtleHappiness >= 50 && turtleHappiness < 75,
                                'text-orange-500': turtleHappiness >= 25 && turtleHappiness < 50,
                                'text-red-500': turtleHappiness < 25
                            }" x-text="`${turtleHappiness}%`"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full" :class="{
                                'bg-green-500': turtleHappiness >= 75,
                                'bg-yellow-500': turtleHappiness >= 50 && turtleHappiness < 75,
                                'bg-orange-500': turtleHappiness >= 25 && turtleHappiness < 50,
                                'bg-red-500': turtleHappiness < 25
                            }" :style="`width: ${turtleHappiness}%`"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="px-6 pb-6 flex space-x-3">
                    <button @click="openFeedModal" class="flex-1 bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center justify-center">
                        <i class="fas fa-apple-alt mr-2"></i> Feed
                    </button>
                    <a href="{{ route('kpi.customize') }}" class="flex-1 bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center justify-center">
                        <i class="fas fa-palette mr-2"></i> Customize
                    </a>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="lg:col-span-2 grid grid-cols-2 gap-4">
                <!-- MEXC Accounts KPI -->
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-3">
                            <i class="fas fa-wallet text-sm"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">MEXC Accounts</h3>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $accountStats['mexc_accounts'] }}</div>
                    <div class="mt-2">
                        @foreach($turtleData['targets'] as $target)
                            @if($target['metric_type'] === 'mexc_accounts' && $target['period_type'] === 'monthly')
                                <div class="text-xs text-gray-500 mb-1">Monthly Goal: {{ $target['current_value'] }}/{{ $target['target_value'] }}</div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $target['percentage'] }}%"></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Email Accounts KPI -->
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mr-3">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Email Accounts</h3>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $accountStats['email_accounts'] }}</div>
                    <div class="mt-2">
                        @foreach($turtleData['targets'] as $target)
                            @if($target['metric_type'] === 'email_accounts' && $target['period_type'] === 'monthly')
                                <div class="text-xs text-gray-500 mb-1">Monthly Goal: {{ $target['current_value'] }}/{{ $target['target_value'] }}</div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $target['percentage'] }}%"></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Proxies KPI -->
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                            <i class="fas fa-server text-sm"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Proxies</h3>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $accountStats['proxies'] }}</div>
                    <div class="mt-2">
                        @foreach($turtleData['targets'] as $target)
                            @if($target['metric_type'] === 'proxies' && $target['period_type'] === 'monthly')
                                <div class="text-xs text-gray-500 mb-1">Monthly Goal: {{ $target['current_value'] }}/{{ $target['target_value'] }}</div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $target['percentage'] }}%"></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Web3 Wallets KPI -->
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 mr-3">
                            <i class="fas fa-link text-sm"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Web3 Wallets</h3>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $accountStats['web3_wallets'] }}</div>
                    <div class="mt-2">
                        @foreach($turtleData['targets'] as $target)
                            @if($target['metric_type'] === 'web3_wallets' && $target['period_type'] === 'monthly')
                                <div class="text-xs text-gray-500 mb-1">Monthly Goal: {{ $target['current_value'] }}/{{ $target['target_value'] }}</div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $target['percentage'] }}%"></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks and Leaderboard -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tasks -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 bg-secondary/10 border-b border-gray-200">
                    <h3 class="font-bold text-gray-800 flex items-center">
                        <i class="fas fa-tasks mr-2 text-secondary"></i>
                        <span>Daily Tasks</span>
                    </h3>
                </div>

                <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
                    <template x-for="task in dailyTasks" :key="task.id">
                        <div class="border border-gray-200 rounded-lg p-4 transition-all" :class="{'opacity-50': task.completed}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-800" x-text="task.name"></h4>
                                    <p class="text-sm text-gray-600 mt-1" x-text="task.description"></p>
                                </div>

                                <div class="flex flex-col items-end">
                                    <div class="flex items-center text-primary font-medium mb-2">
                                        <i class="fas fa-heart mr-1"></i>
                                        <span x-text="task.love_reward"></span>
                                    </div>

                                    <template x-if="!task.completed">
                                        <button @click="completeTask(task.id)"
                                                class="bg-secondary hover:bg-secondary/90 text-white text-sm py-1 px-3 rounded-md transition-colors"
                                                :disabled="task.progress < task.target">
                                            <span x-text="task.progress < task.target ? `${task.progress}/${task.target}` : 'Complete'"></span>
                                        </button>
                                    </template>

                                    <template x-if="task.completed">
                                        <div class="bg-green-100 text-green-800 text-sm py-1 px-3 rounded-md flex items-center">
                                            <i class="fas fa-check mr-1"></i>
                                            <span>Completed</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <template x-if="!task.completed && task.progress > 0">
                                <div class="mt-3">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-secondary h-2 rounded-full" :style="`width: ${task.percentage}%`"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="dailyTasks.length === 0">
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-check-circle text-2xl text-gray-400"></i>
                            </div>
                            <h4 class="text-lg font-medium text-gray-700">All tasks completed!</h4>
                            <p class="text-sm text-gray-500 mt-1">Come back tomorrow for new tasks</p>
                        </div>
                    </template>
                </div>

                <div class="px-6 pb-6 flex justify-between">
                    <a href="{{ route('kpi.achievements') }}" class="text-secondary hover:text-secondary/80 transition-colors flex items-center">
                        <i class="fas fa-trophy mr-1"></i>
                        <span>View Achievements</span>
                    </a>

                    <a href="{{ route('accounts.mexc.create') }}" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        <span>Create New Account</span>
                    </a>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 bg-primary/10 border-b border-gray-200">
                    <h3 class="font-bold text-gray-800 flex items-center">
                        <i class="fas fa-crown mr-2 text-yellow-500"></i>
                        <span>Leaderboard</span>
                    </h3>
                </div>

                <div class="p-4">
                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 mb-4">
                        <button @click="setLeaderboardTab('level')"
                                class="py-2 px-4 text-sm font-medium transition-colors"
                                :class="leaderboardTab === 'level' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700'">
                            Level
                        </button>
                        <button @click="setLeaderboardTab('love')"
                                class="py-2 px-4 text-sm font-medium transition-colors"
                                :class="leaderboardTab === 'love' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700'">
                            Love
                        </button>
                    </div>

                    <!-- Level Leaderboard -->
                    <div x-show="leaderboardTab === 'level'" class="space-y-3">
                        @foreach($levelLeaderboard as $rank => $entry)
                            <div class="flex items-center p-2 rounded-lg {{ $entry['user_id'] === auth()->id() ? 'bg-primary/10' : 'hover:bg-gray-50' }}">
                                <div class="w-8 h-8 flex items-center justify-center font-bold {{ $rank < 3 ? 'text-yellow-500' : 'text-gray-500' }}">
                                    {{ $rank + 1 }}
                                </div>
                                <div class="ml-2 flex-1">
                                    <div class="font-medium text-gray-800">{{ $entry['user_name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $entry['turtle_name'] }}</div>
                                </div>
                                <div class="bg-secondary/10 text-secondary font-medium px-2 py-1 rounded-md text-sm">
                                    Lvl {{ $entry['level'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Love Leaderboard -->
                    <div x-show="leaderboardTab === 'love'" class="space-y-3" style="display: none;">
                        @foreach($loveLeaderboard as $rank => $entry)
                            <div class="flex items-center p-2 rounded-lg {{ $entry['user_id'] === auth()->id() ? 'bg-primary/10' : 'hover:bg-gray-50' }}">
                                <div class="w-8 h-8 flex items-center justify-center font-bold {{ $rank < 3 ? 'text-yellow-500' : 'text-gray-500' }}">
                                    {{ $rank + 1 }}
                                </div>
                                <div class="ml-2 flex-1">
                                    <div class="font-medium text-gray-800">{{ $entry['user_name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $entry['turtle_name'] }}</div>
                                </div>
                                <div class="text-primary font-medium text-sm flex items-center">
                                    <i class="fas fa-heart mr-1"></i>
                                    {{ $entry['total_love'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ route('kpi.leaderboard') }}" class="text-secondary hover:text-secondary/80 text-sm transition-colors">
                            View Full Leaderboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feed Turtle Modal -->
        <div x-show="feedModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
            <div @click.outside="feedModalOpen = false" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md transform transition-all" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Feed {{ $turtleData['turtle']['name'] }}</h3>
                    <button @click="feedModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <p class="text-gray-600 mb-4">
                    Feed your turtle love points to increase its experience and happiness!
                </p>

                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm text-gray-600">Available Love Points:</div>
                    <div class="text-primary font-medium flex items-center">
                        <i class="fas fa-heart mr-1"></i>
                        <span x-text="turtleLovePoints"></span>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="lovePoints" class="block text-sm font-medium text-gray-700 mb-1">Love Points to Feed</label>
                    <input type="range" id="lovePoints" name="lovePoints" min="1" :max="Math.min(turtleLovePoints, 50)" x-model="feedAmount" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-gray-500">1</span>
                        <span class="text-sm font-medium text-primary" x-text="feedAmount"></span>
                        <span class="text-xs text-gray-500" x-text="Math.min(turtleLovePoints, 50)"></span>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button @click="feedModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button @click="feedTurtle" class="px-4 py-2 text-white bg-primary rounded-md hover:bg-primary/90 transition-colors flex items-center" :disabled="turtleLovePoints < 1">
                        <i class="fas fa-apple-alt mr-2"></i>
                        Feed
                    </button>
                </div>
            </div>
        </div>

        <!-- Rename Turtle Modal -->
        <div x-show="renameModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
            <div @click.outside="renameModalOpen = false" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md transform transition-all" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Rename Your Turtle</h3>
                    <button @click="renameModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mb-6">
                    <label for="turtleNewName" class="block text-sm font-medium text-gray-700 mb-1">New Name</label>
                    <input type="text" id="turtleNewName" name="turtleNewName" x-model="newTurtleName" class="w-full p-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary" placeholder="Enter a new name">
                    <p class="text-xs text-gray-500 mt-1">2-20 characters</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button @click="renameModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button @click="renameTurtle" class="px-4 py-2 text-white bg-primary rounded-md hover:bg-primary/90 transition-colors" :disabled="!isValidName">
                        Save
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Toast Notification -->
        <div x-show="showToast" x-transition.opacity class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center z-50" style="display: none;">
            <i class="fas fa-check-circle mr-2"></i>
            <span x-text="toastMessage"></span>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function kpiDashboard() {
            return {
                // Turtle data
                turtleName: "{{ $turtleData['turtle']['name'] }}",
                turtleLevel: {{ $turtleData['turtle']['level'] }},
                turtleLovePoints: {{ $turtleData['turtle']['love_points'] }},
                turtleExperience: {{ $turtleData['turtle']['experience'] }},
                nextLevelExp: {{ $turtleData['turtle']['next_level_exp'] }},
                expPercentage: {{ $turtleData['turtle']['exp_percentage'] }},
                turtleHappiness: {{ $turtleData['turtle']['happiness'] }},
                turtleMood: "{{ $turtleData['turtle']['mood'] }}",

                // Visualization
                turtleShell: "/images/turtles/shells/green.png", // Default shell
                turtleBackground: "/images/turtles/backgrounds/beach.png", // Default background
                equippedAccessories: [],

                // Tasks
                dailyTasks: @json($turtleData['tasks']->filter(function($task) {
    return $task['type'] === 'daily' && !$task['completed'];
})->values()),

                // Modals
                feedModalOpen: false,
                renameModalOpen: false,
                feedAmount: 5,
                newTurtleName: "",

                // Leaderboard
                leaderboardTab: 'level',

                // Toast notification
                showToast: false,
                toastMessage: "",

                // Constants
                maxLovePoints: 1000,

                // Computed properties
                get isValidName() {
                    return this.newTurtleName.length >= 2 && this.newTurtleName.length <= 20;
                },

                // Methods
                init() {
                    // Load equipped items
                    this.loadEquippedItems();

                    // Listen for account creation events
                    this.listenForAccountCreation();
                },

                loadEquippedItems() {
                    // Initialize with defaults
                    this.turtleShell = "/images/turtles/shells/green.png";
                    this.turtleBackground = "/images/turtles/backgrounds/beach.png";
                    this.equippedAccessories = [];

                    // Load from data if available
                    const equipped = @json($turtleData['turtle']['equipped_items'] ?? []);

                    // Set background if available
                    if (equipped.background) {
                        this.turtleBackground = equipped.background.image_path;
                    }

                    // Set shell if available
                    if (equipped.shell) {
                        this.turtleShell = equipped.shell.image_path;
                    }

                    // Set accessories if available
                    if (equipped.accessory) {
                        this.equippedAccessories.push(equipped.accessory);
                    }
                },

                setLeaderboardTab(tab) {
                    this.leaderboardTab = tab;
                },

                openFeedModal() {
                    this.feedAmount = Math.min(5, this.turtleLovePoints);
                    this.feedModalOpen = true;
                },

                openRenameModal() {
                    this.newTurtleName = this.turtleName;
                    this.renameModalOpen = true;
                },

                async feedTurtle() {
                    if (this.turtleLovePoints < this.feedAmount) {
                        this.showToastMessage("Not enough love points!");
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('kpi.turtle.feed') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                love_points: this.feedAmount
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update turtle data
                            this.turtleLovePoints = data.love_points_remaining;
                            this.turtleExperience = data.experience_gained;
                            this.turtleHappiness = data.happiness;
                            this.turtleMood = data.mood;

                            // If level up occurred
                            if (data.level_up) {
                                this.turtleLevel = data.new_level;
                                this.showToastMessage("Level Up! Your turtle is now level " + data.new_level);
                            } else {
                                this.showToastMessage("Your turtle has been fed!");
                            }

                            // Close modal
                            this.feedModalOpen = false;

                            // Refresh turtle data
                            this.refreshTurtleData();
                        } else {
                            this.showToastMessage(data.message || "Failed to feed turtle");
                        }
                    } catch (error) {
                        console.error("Error feeding turtle:", error);
                        this.showToastMessage("Error feeding turtle");
                    }
                },

                async renameTurtle() {
                    if (!this.isValidName) {
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('kpi.turtle.rename') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                name: this.newTurtleName
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.turtleName = data.name;
                            this.renameModalOpen = false;
                            this.showToastMessage("Your turtle has been renamed!");
                        } else {
                            this.showToastMessage(data.message || "Failed to rename turtle");
                        }
                    } catch (error) {
                        console.error("Error renaming turtle:", error);
                        this.showToastMessage("Error renaming turtle");
                    }
                },

                async completeTask(taskId) {
                    try {
                        const response = await fetch(`{{ url('kpi/tasks') }}/${taskId}/complete`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update task list
                            this.dailyTasks = this.dailyTasks.map(task => {
                                if (task.id === taskId) {
                                    return {
                                        ...task,
                                        completed: true,
                                        completed_at: new Date().toISOString()
                                    };
                                }
                                return task;
                            });

                            // Update turtle data with rewards
                            if (data.rewards) {
                                this.turtleLovePoints += data.rewards.love;

                                // If level up occurred
                                if (data.level_up) {
                                    this.showToastMessage(`Task completed! You earned ${data.rewards.love} love points and leveled up!`);
                                } else {
                                    this.showToastMessage(`Task completed! You earned ${data.rewards.love} love points`);
                                }

                                // Refresh turtle data
                                this.refreshTurtleData();
                            } else {
                                this.showToastMessage("Task progress updated");
                            }
                        } else {
                            this.showToastMessage(data.message || "Failed to complete task");
                        }
                    } catch (error) {
                        console.error("Error completing task:", error);
                        this.showToastMessage("Error completing task");
                    }
                },

                async refreshTurtleData() {
                    try {
                        const response = await fetch("{{ route('kpi.turtle.details') }}");
                        const data = await response.json();

                        if (data.success) {
                            // Update turtle data
                            this.turtleName = data.turtle.name;
                            this.turtleLevel = data.turtle.level;
                            this.turtleLovePoints = data.turtle.love_points;
                            this.turtleExperience = data.turtle.experience;
                            this.nextLevelExp = data.turtle.next_level_exp;
                            this.expPercentage = data.turtle.exp_percentage;
                            this.turtleHappiness = data.turtle.happiness;
                            this.turtleMood = data.turtle.mood;

                            // Update tasks
                            this.dailyTasks = data.tasks.filter(task => task.type === 'daily' && !task.completed);

                            // Load equipped items
                            this.loadEquippedItems();
                        }
                    } catch (error) {
                        console.error("Error refreshing turtle data:", error);
                    }
                },

                showToastMessage(message) {
                    this.toastMessage = message;
                    this.showToast = true;

                    // Auto-hide after 3 seconds
                    setTimeout(() => {
                        this.showToast = false;
                    }, 3000);
                },

                listenForAccountCreation() {
                    // Listen for the account creation custom event
                    document.addEventListener('account-created', () => {
                        this.checkAccountCreation();
                    });

                    // For testing: Listen for page visibility to trigger check
                    // when returning from account creation page
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') {
                            // Check if we're coming back from account creation
                            const prevPage = document.referrer;
                            if (prevPage.includes('/accounts/mexc/create')) {
                                this.checkAccountCreation();
                            }
                        }
                    });
                },

                async checkAccountCreation() {
                    try {
                        const response = await fetch("{{ route('kpi.check-account-creation') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // If a task was completed
                            if (data.task_result && data.task_result.success) {
                                this.showToastMessage(`You earned ${data.task_result.rewards.love} love points for creating an account!`);

                                // Refresh data
                                this.refreshTurtleData();
                            }

                            // If a target was completed
                            if (data.target_result && data.target_result.completed_targets && data.target_result.completed_targets.length > 0) {
                                const target = data.target_result.completed_targets[0];
                                this.showToastMessage(`Target achieved: ${target.name}! You earned ${target.love_reward} love points!`);

                                // Refresh data
                                this.refreshTurtleData();
                            }
                        }
                    } catch (error) {
                        console.error("Error checking account creation:", error);
                    }
                }
            };
        }
    </script>
@endpush