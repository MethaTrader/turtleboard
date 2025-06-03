@extends('layouts.app')

@section('content')
    <div x-data="turtleCare()" class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Turtle Care</h2>
            <a href="{{ route('kpi.dashboard') }}" class="text-secondary hover:text-secondary/80 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Turtle Care Card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Turtle Display -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-800 mb-4">{{ $turtleData['turtle']['name'] }}</h3>

                    <div class="relative w-full h-64 flex items-center justify-center overflow-hidden rounded-lg mb-4">
                        <!-- Background -->
                        <div class="absolute inset-0" :style="`background-image: url('${turtleBackground}'); background-size: cover;`"></div>

                        <!-- Turtle -->
                        <div class="relative z-10 transform scale-150">
                            <img :src="turtleShell" alt="Turtle" class="w-48 h-48 object-contain">

                            <!-- Accessories (if equipped) -->
                            <template x-for="accessory in equippedAccessories" :key="accessory.id">
                                <img :src="accessory.image_path" alt="Accessory" class="absolute top-0 left-0 w-48 h-48 object-contain">
                            </template>
                        </div>
                    </div>

                    <div class="space-y-3">
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
                    </div>

                    <div class="mt-4 p-3 rounded-lg" :class="{
                        'bg-green-100 text-green-800': turtleHappiness >= 75,
                        'bg-yellow-100 text-yellow-800': turtleHappiness >= 50 && turtleHappiness < 75,
                        'bg-orange-100 text-orange-800': turtleHappiness >= 25 && turtleHappiness < 50,
                        'bg-red-100 text-red-800': turtleHappiness < 25
                    }">
                        <div class="flex items-start">
                            <i class="fas fa-comment-alt mt-1 mr-2"></i>
                            <div>
                                <div class="font-medium" x-text="turtleMood"></div>
                                <div class="text-sm mt-1" x-text="getMoodMessage()"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Care Options -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Care Options</h3>

                    <div class="space-y-6">
                        <!-- Feed Your Turtle -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-800">Feed Your Turtle</h4>
                                    <p class="text-sm text-gray-600 mt-1">Convert love points into experience to help your turtle level up.</p>
                                </div>

                                <button @click="openFeedModal" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center">
                                    <i class="fas fa-apple-alt mr-2"></i> Feed
                                </button>
                            </div>

                            <div class="mt-4 text-sm">
                                <div class="flex items-center justify-between text-gray-600 mb-2">
                                    <span>Last fed:</span>
                                    <span x-text="lastFedFormatted"></span>
                                </div>
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Feeding converts love points to experience</span>
                                    <span class="text-primary font-medium">1:1 ratio</span>
                                </div>
                            </div>
                        </div>

                        <!-- Customize Your Turtle -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-800">Customize Your Turtle</h4>
                                    <p class="text-sm text-gray-600 mt-1">Change your turtle's appearance with shells, backgrounds, and accessories.</p>
                                </div>

                                <a href="{{ route('kpi.customize') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center">
                                    <i class="fas fa-palette mr-2"></i> Customize
                                </a>
                            </div>

                            <div class="mt-4 text-sm">
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="text-center">
                                        <div class="text-gray-600 mb-1">Shells</div>
                                        <div class="font-medium text-gray-800" x-text="ownedItems.filter(item => item.type === 'shell').length"></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-gray-600 mb-1">Backgrounds</div>
                                        <div class="font-medium text-gray-800" x-text="ownedItems.filter(item => item.type === 'background').length"></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-gray-600 mb-1">Accessories</div>
                                        <div class="font-medium text-gray-800" x-text="ownedItems.filter(item => item.type === 'accessory').length"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visit the Shop -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-800">Visit the Shop</h4>
                                    <p class="text-sm text-gray-600 mt-1">Spend your love points on new items for your turtle.</p>
                                </div>

                                <a href="{{ route('kpi.shop') }}" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center">
                                    <i class="fas fa-shopping-bag mr-2"></i> Shop
                                </a>
                            </div>

                            <div class="mt-4 text-sm">
                                <div class="flex items-center justify-between text-gray-600 mb-2">
                                    <span>Available love points:</span>
                                    <span class="text-primary font-medium" x-text="turtleLovePoints"></span>
                                </div>
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Next level items unlock at:</span>
                                    <span class="text-secondary font-medium">Level <span x-text="turtleLevel + 1"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Rename Your Turtle -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-800">Rename Your Turtle</h4>
                                    <p class="text-sm text-gray-600 mt-1">Give your turtle a new name.</p>
                                </div>

                                <button @click="openRenameModal" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center">
                                    <i class="fas fa-edit mr-2"></i> Rename
                                </button>
                            </div>

                            <div class="mt-4 text-sm">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Current name:</span>
                                    <span class="font-medium text-gray-800" x-text="turtleName"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Rewards -->
                <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                    <h3 class="font-bold text-gray-800 mb-4">Recent Rewards</h3>

                    <div class="space-y-3">
                        <template x-for="(reward, index) in recentRewards" :key="index">
                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                                <i class="fas fa-gift"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="font-medium text-gray-800" x-text="reward.reason"></div>
                                            <div class="text-xs text-gray-500" x-text="formatRewardDate(reward.created_at)"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center text-primary" x-show="reward.love_points > 0">
                                            <i class="fas fa-heart mr-1"></i>
                                            <span class="font-medium" x-text="reward.love_points"></span>
                                        </div>
                                        <div class="flex items-center text-secondary" x-show="reward.experience_points > 0">
                                            <i class="fas fa-star mr-1"></i>
                                            <span class="font-medium" x-text="reward.experience_points"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="recentRewards.length === 0">
                            <div class="text-center py-8">
                                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-gift text-2xl text-gray-400"></i>
                                </div>
                                <h4 class="text-lg font-medium text-gray-700">No Recent Rewards</h4>
                                <p class="text-sm text-gray-500 mt-1">Complete tasks to earn rewards for your turtle.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Happiness Guide -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Happiness Guide</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3 text-green-600">
                            <i class="fas fa-grin-beam"></i>
                        </div>
                        <h4 class="font-medium text-green-800">Ecstatic (90-100%)</h4>
                    </div>
                    <p class="text-sm text-green-700">Your turtle is thriving! You're taking excellent care of them with regular feeding and interaction.</p>
                </div>

                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3 text-green-600">
                            <i class="fas fa-smile"></i>
                        </div>
                        <h4 class="font-medium text-green-800">Happy (75-89%)</h4>
                    </div>
                    <p class="text-sm text-green-700">Your turtle is happy and healthy. Regular attention keeps their spirits high.</p>
                </div>

                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3 text-yellow-600">
                            <i class="fas fa-meh"></i>
                        </div>
                        <h4 class="font-medium text-yellow-800">Content (50-74%)</h4>
                    </div>
                    <p class="text-sm text-yellow-700">Your turtle is doing okay but could use more attention. Try feeding them and completing more tasks.</p>
                </div>

                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3 text-red-600">
                            <i class="fas fa-frown"></i>
                        </div>
                        <h4 class="font-medium text-red-800">Unhappy (0-49%)</h4>
                    </div>
                    <p class="text-sm text-red-700">Your turtle is feeling neglected. Feed them regularly and complete more tasks to improve their happiness.</p>
                </div>
            </div>

            <div class="mt-6 text-sm text-gray-600">
                <p><strong>Tip:</strong> Your turtle's happiness affects their experience gain rate. Happier turtles gain more experience from feeding and task completion!</p>
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
        function turtleCare() {
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
                lastFedAt: "{{ $turtleData['turtle']['last_fed_at'] }}",

                // Visualization
                turtleShell: "/images/turtles/shells/green.png", // Default shell
                turtleBackground: "/images/turtles/backgrounds/beach.png", // Default background
                equippedAccessories: [],

                // Owned items
                ownedItems: [],

                // Rewards
                recentRewards: [],

                // Modals
                feedModalOpen: false,
                renameModalOpen: false,
                feedAmount: 5,
                newTurtleName: "",

                // Toast notification
                showToast: false,
                toastMessage: "",

                // Constants
                maxLovePoints: 1000,

                // Computed properties
                get lastFedFormatted() {
                    if (!this.lastFedAt) return 'Never';

                    const date = new Date(this.lastFedAt);
                    const now = new Date();

                    // If today
                    if (date.toDateString() === now.toDateString()) {
                        return `Today at ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                    }

                    // If yesterday
                    const yesterday = new Date(now);
                    yesterday.setDate(now.getDate() - 1);
                    if (date.toDateString() === yesterday.toDateString()) {
                        return `Yesterday at ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                    }

                    // Otherwise
                    return date.toLocaleDateString() + ' at ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                },

                get isValidName() {
                    return this.newTurtleName.length >= 2 && this.newTurtleName.length <= 20;
                },

                // Methods
                init() {
                    this.loadEquippedItems();
                    this.loadInventory();
                    this.loadRecentRewards();
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

                loadInventory() {
                    // Get owned items
                    this.ownedItems = @json($turtleData['inventory'] ?? []);
                },

                loadRecentRewards() {
                    // Get recent rewards
                    this.recentRewards = @json($turtleData['recent_rewards'] ?? []);
                },

                formatRewardDate(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();

                    // If today
                    if (date.toDateString() === now.toDateString()) {
                        return `Today at ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                    }

                    // If yesterday
                    const yesterday = new Date(now);
                    yesterday.setDate(now.getDate() - 1);
                    if (date.toDateString() === yesterday.toDateString()) {
                        return `Yesterday at ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                    }

                    // Otherwise
                    return date.toLocaleDateString() + ' at ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                },

                getMoodMessage() {
                    if (this.turtleHappiness >= 90) {
                        return "I'm having the best time ever! Thank you for taking such good care of me!";
                    } else if (this.turtleHappiness >= 75) {
                        return "I'm feeling great! I love when you complete tasks and feed me!";
                    } else if (this.turtleHappiness >= 50) {
                        return "I'm doing okay, but I could use some more attention. Maybe complete some tasks?";
                    } else if (this.turtleHappiness >= 25) {
                        return "I'm feeling a bit neglected. Please feed me and complete more tasks.";
                    } else {
                        return "I'm really sad. Please don't forget about me! I need your attention.";
                    }
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
                            this.lastFedAt = new Date().toISOString();

                            // If level up occurred
                            if (data.level_up) {
                                this.turtleLevel = data.new_level;
                                this.showToastMessage("Level Up! Your turtle is now level " + data.new_level);
                            } else {
                                this.showToastMessage("Your turtle has been fed!");
                            }

                            // Close modal
                            this.feedModalOpen = false;
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

                showToastMessage(message) {
                    this.toastMessage = message;
                    this.showToast = true;

                    // Auto-hide after 3 seconds
                    setTimeout(() => {
                        this.showToast = false;
                    }, 3000);
                }
            };
        }
    </script>
@endpush