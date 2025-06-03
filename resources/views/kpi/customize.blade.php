@extends('layouts.app')

@section('content')
    <div x-data="turtleCustomize()" class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Customize Your Turtle</h2>
            <a href="{{ route('kpi.dashboard') }}" class="text-secondary hover:text-secondary/80 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Turtle Preview -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Preview</h3>

                    <div class="relative w-full h-64 flex items-center justify-center overflow-hidden rounded-lg mb-4">
                        <!-- Background -->
                        <div class="absolute inset-0" :style="`background-image: url('${selectedBackground}'); background-size: cover;`"></div>

                        <!-- Turtle -->
                        <div class="relative z-10 transform scale-150">
                            <img :src="selectedShell" alt="Turtle" class="w-48 h-48 object-contain">

                            <!-- Accessories (if equipped) -->
                            <template x-for="accessory in selectedAccessories" :key="accessory.id">
                                <img :src="accessory.image_path" alt="Accessory" class="absolute top-0 left-0 w-48 h-48 object-contain">
                            </template>
                        </div>
                    </div>

                    <div class="text-center mb-6">
                        <h4 class="font-semibold text-gray-800" x-text="turtleName"></h4>
                        <p class="text-sm text-gray-600">Level <span x-text="turtleLevel"></span></p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Love Points</span>
                            <span class="text-primary font-medium flex items-center">
                            <i class="fas fa-heart mr-1"></i>
                            <span x-text="turtleLovePoints"></span>
                        </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Happiness</span>
                            <span :class="{
                            'text-green-600': turtleHappiness >= 75,
                            'text-yellow-600': turtleHappiness >= 50 && turtleHappiness < 75,
                            'text-orange-500': turtleHappiness >= 25 && turtleHappiness < 50,
                            'text-red-500': turtleHappiness < 25
                        }" x-text="`${turtleHappiness}%`"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Mood</span>
                            <span x-text="turtleMood" :class="{
                            'text-green-600': turtleHappiness >= 75,
                            'text-yellow-600': turtleHappiness >= 50 && turtleHappiness < 75,
                            'text-orange-500': turtleHappiness >= 25 && turtleHappiness < 50,
                            'text-red-500': turtleHappiness < 25
                        }"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customization Options -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 mb-6">
                        <button @click="activeTab = 'shells'"
                                class="py-2 px-4 text-sm font-medium transition-colors"
                                :class="activeTab === 'shells' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700'">
                            Shells
                        </button>
                        <button @click="activeTab = 'backgrounds'"
                                class="py-2 px-4 text-sm font-medium transition-colors"
                                :class="activeTab === 'backgrounds' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700'">
                            Backgrounds
                        </button>
                        <button @click="activeTab = 'accessories'"
                                class="py-2 px-4 text-sm font-medium transition-colors"
                                :class="activeTab === 'accessories' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700'">
                            Accessories
                        </button>
                    </div>

                    <!-- Shell Options -->
                    <div x-show="activeTab === 'shells'" x-transition>
                        <h3 class="font-bold text-gray-800 mb-4">Turtle Shells</h3>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <template x-for="item in ownedShells" :key="item.id">
                                <div @click="selectShell(item)"
                                     class="border rounded-lg p-4 cursor-pointer transition-all transform hover:scale-105"
                                     :class="isSelectedShell(item) ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="w-full h-32 flex items-center justify-center mb-2">
                                        <img :src="item.image_path" alt="Shell" class="max-h-full object-contain">
                                    </div>
                                    <div class="text-center">
                                        <h4 class="font-medium text-gray-800" x-text="item.name"></h4>
                                        <div class="flex justify-center mt-2">
                                            <template x-if="isSelectedShell(item)">
                                                <span class="text-xs bg-primary text-white px-2 py-1 rounded-full">Equipped</span>
                                            </template>
                                            <template x-if="!isSelectedShell(item)">
                                                <button @click.stop="equipItem(item.id)" class="text-xs bg-secondary text-white px-2 py-1 rounded-full hover:bg-secondary/90">
                                                    Equip
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="ownedShells.length === 0">
                                <div class="col-span-full text-center py-8">
                                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-shopping-bag text-2xl text-gray-400"></i>
                                    </div>
                                    <h4 class="text-lg font-medium text-gray-700">No shells owned yet</h4>
                                    <p class="text-sm text-gray-500 mt-1">Visit the shop to purchase shells</p>
                                    <a href="{{ route('kpi.shop') }}" class="inline-block mt-4 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-md">
                                        Go to Shop
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Background Options -->
                    <div x-show="activeTab === 'backgrounds'" x-transition style="display: none;">
                        <h3 class="font-bold text-gray-800 mb-4">Backgrounds</h3>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <template x-for="item in ownedBackgrounds" :key="item.id">
                                <div @click="selectBackground(item)"
                                     class="border rounded-lg p-4 cursor-pointer transition-all transform hover:scale-105"
                                     :class="isSelectedBackground(item) ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="w-full h-32 rounded-lg overflow-hidden mb-2">
                                        <div class="w-full h-full" :style="`background-image: url('${item.image_path}'); background-size: cover; background-position: center;`"></div>
                                    </div>
                                    <div class="text-center">
                                        <h4 class="font-medium text-gray-800" x-text="item.name"></h4>
                                        <div class="flex justify-center mt-2">
                                            <template x-if="isSelectedBackground(item)">
                                                <span class="text-xs bg-primary text-white px-2 py-1 rounded-full">Equipped</span>
                                            </template>
                                            <template x-if="!isSelectedBackground(item)">
                                                <button @click.stop="equipItem(item.id)" class="text-xs bg-secondary text-white px-2 py-1 rounded-full hover:bg-secondary/90">
                                                    Equip
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="ownedBackgrounds.length === 0">
                                <div class="col-span-full text-center py-8">
                                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-shopping-bag text-2xl text-gray-400"></i>
                                    </div>
                                    <h4 class="text-lg font-medium text-gray-700">No backgrounds owned yet</h4>
                                    <p class="text-sm text-gray-500 mt-1">Visit the shop to purchase backgrounds</p>
                                    <a href="{{ route('kpi.shop') }}" class="inline-block mt-4 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-md">
                                        Go to Shop
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Accessory Options -->
                    <div x-show="activeTab === 'accessories'" x-transition style="display: none;">
                        <h3 class="font-bold text-gray-800 mb-4">Accessories</h3>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <template x-for="item in ownedAccessories" :key="item.id">
                                <div @click="selectAccessory(item)"
                                     class="border rounded-lg p-4 cursor-pointer transition-all transform hover:scale-105"
                                     :class="isSelectedAccessory(item) ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="w-full h-32 flex items-center justify-center mb-2">
                                        <img :src="item.image_path" alt="Accessory" class="max-h-full object-contain">
                                    </div>
                                    <div class="text-center">
                                        <h4 class="font-medium text-gray-800" x-text="item.name"></h4>
                                        <div class="flex justify-center mt-2">
                                            <template x-if="isSelectedAccessory(item)">
                                                <span class="text-xs bg-primary text-white px-2 py-1 rounded-full">Equipped</span>
                                            </template>
                                            <template x-if="!isSelectedAccessory(item)">
                                                <button @click.stop="equipItem(item.id)" class="text-xs bg-secondary text-white px-2 py-1 rounded-full hover:bg-secondary/90">
                                                    Equip
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="ownedAccessories.length === 0">
                                <div class="col-span-full text-center py-8">
                                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-shopping-bag text-2xl text-gray-400"></i>
                                    </div>
                                    <h4 class="text-lg font-medium text-gray-700">No accessories owned yet</h4>
                                    <p class="text-sm text-gray-500 mt-1">Visit the shop to purchase accessories</p>
                                    <a href="{{ route('kpi.shop') }}" class="inline-block mt-4 bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-md">
                                        Go to Shop
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-between">
                    <a href="{{ route('kpi.shop') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Visit Shop
                    </a>

                    <a href="{{ route('kpi.dashboard') }}" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center">
                        <i class="fas fa-check mr-2"></i>
                        Done
                    </a>
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
        function turtleCustomize() {
            return {
                // Turtle data
                turtleName: "{{ $turtleData['turtle']['name'] }}",
                turtleLevel: {{ $turtleData['turtle']['level'] }},
                turtleLovePoints: {{ $turtleData['turtle']['love_points'] }},
                turtleHappiness: {{ $turtleData['turtle']['happiness'] }},
                turtleMood: "{{ $turtleData['turtle']['mood'] }}",

                // Selected items
                selectedShell: "/images/turtles/shells/green.png", // Default
                selectedBackground: "/images/turtles/backgrounds/beach.png", // Default
                selectedAccessories: [],

                // Owned items
                ownedShells: [],
                ownedBackgrounds: [],
                ownedAccessories: [],

                // Active tab
                activeTab: 'shells',

                // Toast notification
                showToast: false,
                toastMessage: "",

                // Initialize
                init() {
                    this.loadInventory();
                    this.loadSelectedItems();
                },

                loadInventory() {
                    const inventory = @json($turtleData['inventory'] ?? []);

                    // Group items by type
                    this.ownedShells = inventory.filter(item => item.type === 'shell');
                    this.ownedBackgrounds = inventory.filter(item => item.type === 'background');
                    this.ownedAccessories = inventory.filter(item => item.type === 'accessory');
                },

                loadSelectedItems() {
                    const equipped = @json($turtleData['turtle']['equipped_items'] ?? []);

                    // Set background if available
                    if (equipped.background) {
                        this.selectedBackground = equipped.background.image_path;
                    }

                    // Set shell if available
                    if (equipped.shell) {
                        this.selectedShell = equipped.shell.image_path;
                    }

                    // Set accessories if available
                    if (equipped.accessory) {
                        this.selectedAccessories = [equipped.accessory];
                    }
                },

                // Methods for checking if an item is selected
                isSelectedShell(item) {
                    return this.selectedShell === item.image_path;
                },

                isSelectedBackground(item) {
                    return this.selectedBackground === item.image_path;
                },

                isSelectedAccessory(item) {
                    return this.selectedAccessories.some(acc => acc.id === item.id);
                },

                // Methods for selecting items (preview only)
                selectShell(item) {
                    this.selectedShell = item.image_path;
                },

                selectBackground(item) {
                    this.selectedBackground = item.image_path;
                },

                selectAccessory(item) {
                    // For accessories, we might want to toggle
                    if (this.isSelectedAccessory(item)) {
                        this.selectedAccessories = this.selectedAccessories.filter(acc => acc.id !== item.id);
                    } else {
                        // Replace any accessory of the same slot
                        if (item.attributes && item.attributes.slot) {
                            this.selectedAccessories = this.selectedAccessories.filter(acc =>
                                !acc.attributes || acc.attributes.slot !== item.attributes.slot
                            );
                        }
                        this.selectedAccessories.push(item);
                    }
                },

                // Equip an item (save to server)
                async equipItem(itemId) {
                    try {
                        const response = await fetch(`{{ url('kpi/items') }}/${itemId}/equip`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showToastMessage(`${data.item.name} equipped successfully!`);

                            // Update the selected items
                            if (data.item.type === 'shell') {
                                this.selectedShell = data.item.image_path;
                            } else if (data.item.type === 'background') {
                                this.selectedBackground = data.item.image_path;
                            } else if (data.item.type === 'accessory') {
                                // For accessories, we need to check if we're replacing one in the same slot
                                const newAccessory = data.item;
                                if (newAccessory.attributes && newAccessory.attributes.slot) {
                                    this.selectedAccessories = this.selectedAccessories.filter(acc =>
                                        !acc.attributes || acc.attributes.slot !== newAccessory.attributes.slot
                                    );
                                }
                                this.selectedAccessories.push(newAccessory);
                            }

                            // Mark as equipped in the inventory
                            this.updateInventoryEquipped(itemId);
                        } else {
                            this.showToastMessage(data.message || "Failed to equip item");
                        }
                    } catch (error) {
                        console.error("Error equipping item:", error);
                        this.showToastMessage("Error equipping item");
                    }
                },

                // Update the inventory to reflect equipped status
                updateInventoryEquipped(itemId) {
                    const updateItemList = (itemList) => {
                        return itemList.map(item => {
                            // First unequip all items
                            item.equipped = false;
                            // Then equip the selected one
                            if (item.id === itemId) {
                                item.equipped = true;
                            }
                            return item;
                        });
                    };

                    this.ownedShells = updateItemList(this.ownedShells);
                    this.ownedBackgrounds = updateItemList(this.ownedBackgrounds);
                    this.ownedAccessories = updateItemList(this.ownedAccessories);
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