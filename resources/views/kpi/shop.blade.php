@extends('layouts.app')

@section('content')
    <div x-data="turtleShop()" class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Turtle Shop</h2>
            <a href="{{ route('kpi.dashboard') }}" class="text-secondary hover:text-secondary/80 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Turtle Stats Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mr-4">
                        <img :src="turtleShell" alt="Turtle" class="w-10 h-10">
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800" x-text="turtleName"></h3>
                        <p class="text-sm text-gray-600">Level <span x-text="turtleLevel"></span> Turtle</p>
                    </div>
                </div>
                <div class="flex items-center space-x-8">
                    <div class="text-center">
                        <div class="text-sm text-gray-600 mb-1">Love Points</div>
                        <div class="text-xl font-bold text-primary flex items-center justify-center">
                            <i class="fas fa-heart mr-2"></i>
                            <span x-text="turtleLovePoints"></span>
                        </div>
                    </div>
                    <a href="{{ route('kpi.customize') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-md transition-colors">
                        <i class="fas fa-tshirt mr-2"></i>
                        My Items
                    </a>
                </div>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="flex border-b border-gray-200">
                <button @click="activeTab = 'shells'"
                        class="py-3 px-6 text-sm font-medium transition-colors relative"
                        :class="activeTab === 'shells' ? 'text-primary' : 'text-gray-500 hover:text-gray-700'">
                    <span>Shells</span>
                    <div x-show="activeTab === 'shells'" class="absolute bottom-0 left-0 w-full h-0.5 bg-primary"></div>
                </button>
                <button @click="activeTab = 'backgrounds'"
                        class="py-3 px-6 text-sm font-medium transition-colors relative"
                        :class="activeTab === 'backgrounds' ? 'text-primary' : 'text-gray-500 hover:text-gray-700'">
                    <span>Backgrounds</span>
                    <div x-show="activeTab === 'backgrounds'" class="absolute bottom-0 left-0 w-full h-0.5 bg-primary"></div>
                </button>
                <button @click="activeTab = 'accessories'"
                        class="py-3 px-6 text-sm font-medium transition-colors relative"
                        :class="activeTab === 'accessories' ? 'text-primary' : 'text-gray-500 hover:text-gray-700'">
                    <span>Accessories</span>
                    <div x-show="activeTab === 'accessories'" class="absolute bottom-0 left-0 w-full h-0.5 bg-primary"></div>
                </button>
            </div>

            <!-- Shells Tab -->
            <div x-show="activeTab === 'shells'" class="p-6" x-transition>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="(item, index) in shellItems" :key="item.id">
                        <div class="border rounded-lg overflow-hidden transition-all hover:shadow-lg"
                             :class="userOwnsItem(item) ? 'border-green-300 bg-green-50' : 'border-gray-200'">
                            <div class="p-4">
                                <div class="w-full h-36 flex items-center justify-center mb-4">
                                    <img :src="item.image_path" alt="Shell" class="max-h-full object-contain transform transition-transform hover:scale-110">
                                </div>
                                <h4 class="font-semibold text-gray-800 text-center" x-text="item.name"></h4>
                                <p class="text-sm text-gray-600 text-center mt-1" x-text="item.description"></p>
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <i class="fas fa-heart text-primary mr-1"></i>
                                        <span class="text-primary font-medium" x-text="item.love_cost"></span>
                                    </div>
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                                        <span>Level <span x-text="item.required_level"></span>+</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <template x-if="userOwnsItem(item)">
                                        <span class="w-full block text-center py-2 bg-green-100 text-green-800 rounded-md">
                                            <i class="fas fa-check-circle mr-1"></i> Owned
                                        </span>
                                    </template>
                                    <template x-if="!userOwnsItem(item)">
                                        <button @click="purchaseItem(item)"
                                                class="w-full py-2 px-4 bg-primary hover:bg-primary/90 text-white rounded-md transition-colors"
                                                :disabled="!canPurchase(item)"
                                                :class="{'opacity-50 cursor-not-allowed': !canPurchase(item)}">
                                            <template x-if="canPurchase(item)">
                                                <span>Purchase</span>
                                            </template>
                                            <template x-if="!canPurchase(item) && turtleLevel < item.required_level">
                                                <span>Level <span x-text="item.required_level"></span> Required</span>
                                            </template>
                                            <template x-if="!canPurchase(item) && turtleLevel >= item.required_level">
                                                <span>Not Enough Love</span>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Backgrounds Tab -->
            <div x-show="activeTab === 'backgrounds'" class="p-6" x-transition style="display: none;">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="(item, index) in backgroundItems" :key="item.id">
                        <div class="border rounded-lg overflow-hidden transition-all hover:shadow-lg"
                             :class="userOwnsItem(item) ? 'border-green-300 bg-green-50' : 'border-gray-200'">
                            <div class="p-4">
                                <div class="w-full h-36 rounded-lg overflow-hidden mb-4">
                                    <div class="w-full h-full" :style="`background-image: url('${item.image_path}'); background-size: cover; background-position: center;`"></div>
                                </div>
                                <h4 class="font-semibold text-gray-800 text-center" x-text="item.name"></h4>
                                <p class="text-sm text-gray-600 text-center mt-1" x-text="item.description"></p>
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <i class="fas fa-heart text-primary mr-1"></i>
                                        <span class="text-primary font-medium" x-text="item.love_cost"></span>
                                    </div>
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                                        <span>Level <span x-text="item.required_level"></span>+</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <template x-if="userOwnsItem(item)">
                                        <span class="w-full block text-center py-2 bg-green-100 text-green-800 rounded-md">
                                            <i class="fas fa-check-circle mr-1"></i> Owned
                                        </span>
                                    </template>
                                    <template x-if="!userOwnsItem(item)">
                                        <button @click="purchaseItem(item)"
                                                class="w-full py-2 px-4 bg-primary hover:bg-primary/90 text-white rounded-md transition-colors"
                                                :disabled="!canPurchase(item)"
                                                :class="{'opacity-50 cursor-not-allowed': !canPurchase(item)}">
                                            <template x-if="canPurchase(item)">
                                                <span>Purchase</span>
                                            </template>
                                            <template x-if="!canPurchase(item) && turtleLevel < item.required_level">
                                                <span>Level <span x-text="item.required_level"></span> Required</span>
                                            </template>
                                            <template x-if="!canPurchase(item) && turtleLevel >= item.required_level">
                                                <span>Not Enough Love</span>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Accessories Tab -->
            <div x-show="activeTab === 'accessories'" class="p-6" x-transition style="display: none;">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="(item, index) in accessoryItems" :key="item.id">
                        <div class="border rounded-lg overflow-hidden transition-all hover:shadow-lg"
                             :class="userOwnsItem(item) ? 'border-green-300 bg-green-50' : 'border-gray-200'">
                            <div class="p-4">
                                <div class="w-full h-36 flex items-center justify-center mb-4">
                                    <img :src="item.image_path" alt="Accessory" class="max-h-full object-contain transform transition-transform hover:scale-110">
                                </div>
                                <h4 class="font-semibold text-gray-800 text-center" x-text="item.name"></h4>
                                <p class="text-sm text-gray-600 text-center mt-1" x-text="item.description"></p>
                                <div class="mt-2 flex justify-center">
                                    <span x-show="item.attributes && item.attributes.slot"
                                          class="text-xs px-2 py-1 bg-secondary/10 text-secondary rounded-full">
                                        <i class="fas fa-tag mr-1"></i>
                                        <span x-text="capitalizeFirstLetter(item.attributes.slot)"></span> Slot
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <i class="fas fa-heart text-primary mr-1"></i>
                                        <span class="text-primary font-medium" x-text="item.love_cost"></span>
                                    </div>
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                                        <span>Level <span x-text="item.required_level"></span>+</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <template x-if="userOwnsItem(item)">
                                        <span class="w-full block text-center py-2 bg-green-100 text-green-800 rounded-md">
                                            <i class="fas fa-check-circle mr-1"></i> Owned
                                        </span>
                                    </template>
                                    <template x-if="!userOwnsItem(item)">
                                        <button @click="purchaseItem(item)"
                                                class="w-full py-2 px-4 bg-primary hover:bg-primary/90 text-white rounded-md transition-colors"
                                                :disabled="!canPurchase(item)"
                                                :class="{'opacity-50 cursor-not-allowed': !canPurchase(item)}">
                                            <template x-if="canPurchase(item)">
                                                <span>Purchase</span>
                                            </template>
                                            <template x-if="!canPurchase(item) && turtleLevel < item.required_level">
                                                <span>Level <span x-text="item.required_level"></span> Required</span>
                                            </template>
                                            <template x-if="!canPurchase(item) && turtleLevel >= item.required_level">
                                                <span>Not Enough Love</span>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- How to Earn Love Points Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">How to Earn Love Points</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-primary/5 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-primary/20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-tasks text-primary"></i>
                        </div>
                        <h4 class="font-medium text-gray-800">Complete Tasks</h4>
                    </div>
                    <p class="text-sm text-gray-600">Complete daily and weekly tasks to earn love points. Check your dashboard for available tasks.</p>
                </div>

                <div class="bg-secondary/5 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-secondary/20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-wallet text-secondary"></i>
                        </div>
                        <h4 class="font-medium text-gray-800">Create MEXC Accounts</h4>
                    </div>
                    <p class="text-sm text-gray-600">Earn <span class="text-primary font-medium">10</span> love points for each new MEXC account you create.</p>
                </div>

                <div class="bg-success/5 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-success/20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-bullseye text-success"></i>
                        </div>
                        <h4 class="font-medium text-gray-800">Reach Targets</h4>
                    </div>
                    <p class="text-sm text-gray-600">Complete monthly and weekly targets to earn bonus love points and special rewards.</p>
                </div>
            </div>
        </div>

        <!-- Purchase Confirmation Modal -->
        <div x-show="purchaseModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
            <div @click.outside="purchaseModalOpen = false" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md transform transition-all" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Confirm Purchase</h3>
                    <button @click="purchaseModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="flex items-center justify-center mb-6">
                    <img :src="selectedItem?.image_path" alt="Item" class="max-h-32 object-contain">
                </div>

                <p class="text-center text-gray-700 mb-4">
                    Are you sure you want to purchase <span class="font-medium text-gray-900" x-text="selectedItem?.name"></span> for <span class="font-medium text-primary" x-text="selectedItem?.love_cost"></span> love points?
                </p>

                <div class="flex items-center justify-between mb-6">
                    <div class="text-sm text-gray-600">Current love points:</div>
                    <div class="text-primary font-medium flex items-center">
                        <i class="fas fa-heart mr-1"></i>
                        <span x-text="turtleLovePoints"></span>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button @click="purchaseModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button @click="confirmPurchase" class="px-4 py-2 text-white bg-primary rounded-md hover:bg-primary/90 transition-colors flex items-center">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Confirm Purchase
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
        function turtleShop() {
            return {
                // Turtle data
                turtleName: "{{ $turtleData['turtle']['name'] }}",
                turtleLevel: {{ $turtleData['turtle']['level'] }},
                turtleLovePoints: {{ $turtleData['turtle']['love_points'] }},
                turtleShell: "/images/turtles/shells/green.png",

                // Tab state
                activeTab: 'shells',

                // Items by category
                shellItems: [],
                backgroundItems: [],
                accessoryItems: [],

                // Owned items
                ownedItems: [],

                // Purchase modal
                purchaseModalOpen: false,
                selectedItem: null,

                // Toast notification
                showToast: false,
                toastMessage: "",

                // Initialize
                init() {
                    this.loadItems();
                    this.loadInventory();
                    this.loadTurtleAppearance();
                },

                loadTurtleAppearance() {
                    const equipped = @json($turtleData['turtle']['equipped_items'] ?? []);

                    // Set shell if available
                    if (equipped.shell) {
                        this.turtleShell = equipped.shell.image_path;
                    }
                },

                loadItems() {
                    // Get all available items grouped by type
                    const shopItems = @json($shopItems ?? []);

                    this.shellItems = shopItems.shell || [];
                    this.backgroundItems = shopItems.background || [];
                    this.accessoryItems = shopItems.accessory || [];

                    // Sort items by required level and cost
                    this.shellItems.sort((a, b) => a.required_level - b.required_level || a.love_cost - b.love_cost);
                    this.backgroundItems.sort((a, b) => a.required_level - b.required_level || a.love_cost - b.love_cost);
                    this.accessoryItems.sort((a, b) => a.required_level - b.required_level || a.love_cost - b.love_cost);
                },

                loadInventory() {
                    // Get owned items
                    this.ownedItems = @json($turtleData['inventory'] ?? []);
                },

                userOwnsItem(item) {
                    return this.ownedItems.some(owned => owned.id === item.id);
                },

                canPurchase(item) {
                    return !this.userOwnsItem(item) &&
                        this.turtleLovePoints >= item.love_cost &&
                        this.turtleLevel >= item.required_level;
                },

                purchaseItem(item) {
                    this.selectedItem = item;
                    this.purchaseModalOpen = true;
                },

                async confirmPurchase() {
                    if (!this.selectedItem || !this.canPurchase(this.selectedItem)) {
                        return;
                    }

                    try {
                        const response = await fetch(`{{ url('kpi/items') }}/${this.selectedItem.id}/buy`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update love points
                            this.turtleLovePoints = data.love_points_remaining;

                            // Add to owned items
                            this.ownedItems.push(this.selectedItem);

                            // Show success message
                            this.showToastMessage(`${this.selectedItem.name} purchased successfully!`);

                            // Close modal
                            this.purchaseModalOpen = false;
                        } else {
                            this.showToastMessage(data.message || "Failed to purchase item");
                            this.purchaseModalOpen = false;
                        }
                    } catch (error) {
                        console.error("Error purchasing item:", error);
                        this.showToastMessage("Error purchasing item");
                        this.purchaseModalOpen = false;
                    }
                },

                capitalizeFirstLetter(string) {
                    if (!string) return '';
                    return string.charAt(0).toUpperCase() + string.slice(1);
                },

                // Show toast message
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