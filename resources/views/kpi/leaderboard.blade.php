@extends('layouts.app')

@section('content')
    <div x-data="leaderboard()" class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Team Leaderboard</h2>
            <a href="{{ route('kpi.dashboard') }}" class="text-secondary hover:text-secondary/80 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Leaderboard Category Tabs -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="flex border-b border-gray-200">
                <a href="{{ route('kpi.leaderboard', ['type' => 'level']) }}"
                   class="py-3 px-6 text-sm font-medium transition-colors relative {{ $type === 'level' ? 'text-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-star mr-1"></i>
                    <span>Level</span>
                    @if($type === 'level')
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-primary"></div>
                    @endif
                </a>
                <a href="{{ route('kpi.leaderboard', ['type' => 'love']) }}"
                   class="py-3 px-6 text-sm font-medium transition-colors relative {{ $type === 'love' ? 'text-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-heart mr-1"></i>
                    <span>Love Points</span>
                    @if($type === 'love')
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-primary"></div>
                    @endif
                </a>
                <a href="{{ route('kpi.leaderboard', ['type' => 'achievements']) }}"
                   class="py-3 px-6 text-sm font-medium transition-colors relative {{ $type === 'achievements' ? 'text-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-trophy mr-1"></i>
                    <span>Achievements</span>
                    @if($type === 'achievements')
                        <div class="absolute bottom-0 left-0 w-full h-0.5 bg-primary"></div>
                    @endif
                </a>
            </div>

            <!-- Top 3 Winners -->
            <div class="p-6 pb-0">
                <div class="flex flex-col md:flex-row justify-center items-end gap-4 md:gap-8 mb-8">
                    <!-- 2nd Place -->
                    @if(isset($leaderboardData['leaderboard'][1]))
                        <div class="flex flex-col items-center order-1 md:order-0">
                            <div class="relative">
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-secondary text-white w-8 h-8 rounded-full flex items-center justify-center text-lg font-bold">
                                    2
                                </div>
                                <div class="w-20 h-20 bg-secondary/10 rounded-full flex items-center justify-center overflow-hidden border-4 border-secondary">
                                    <div class="text-2xl">🐢</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <div class="font-semibold text-gray-800">{{ $leaderboardData['leaderboard'][1]['turtle_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $leaderboardData['leaderboard'][1]['user_name'] }}</div>
                                <div class="mt-1 font-bold">
                                    @if($type === 'level')
                                        <span class="text-secondary">Level {{ $leaderboardData['leaderboard'][1]['level'] }}</span>
                                    @elseif($type === 'love')
                                        <span class="text-primary flex items-center justify-center">
                                            <i class="fas fa-heart mr-1"></i>
                                            {{ $leaderboardData['leaderboard'][1]['total_love'] }}
                                        </span>
                                    @else
                                        <span class="text-secondary flex items-center justify-center">
                                            <i class="fas fa-trophy mr-1"></i>
                                            {{ $leaderboardData['leaderboard'][1]['achievements_count'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 1st Place -->
                    @if(isset($leaderboardData['leaderboard'][0]))
                        <div class="flex flex-col items-center order-0 md:order-1 scale-110 mb-4">
                            <div class="relative">
                                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                    <i class="fas fa-crown text-yellow-500 text-2xl"></i>
                                </div>
                                <div class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center overflow-hidden border-4 border-primary mt-2">
                                    <div class="text-3xl">🐢</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <div class="font-bold text-gray-800 text-lg">{{ $leaderboardData['leaderboard'][0]['turtle_name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $leaderboardData['leaderboard'][0]['user_name'] }}</div>
                                <div class="mt-1 font-bold text-lg">
                                    @if($type === 'level')
                                        <span class="text-primary">Level {{ $leaderboardData['leaderboard'][0]['level'] }}</span>
                                    @elseif($type === 'love')
                                        <span class="text-primary flex items-center justify-center">
                                            <i class="fas fa-heart mr-1"></i>
                                            {{ $leaderboardData['leaderboard'][0]['total_love'] }}
                                        </span>
                                    @else
                                        <span class="text-primary flex items-center justify-center">
                                            <i class="fas fa-trophy mr-1"></i>
                                            {{ $leaderboardData['leaderboard'][0]['achievements_count'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 3rd Place -->
                    @if(isset($leaderboardData['leaderboard'][2]))
                        <div class="flex flex-col items-center order-2">
                            <div class="relative">
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-orange-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-lg font-bold">
                                    3
                                </div>
                                <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center overflow-hidden border-4 border-orange-500">
                                    <div class="text-2xl">🐢</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <div class="font-semibold text-gray-800">{{ $leaderboardData['leaderboard'][2]['turtle_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $leaderboardData['leaderboard'][2]['user_name'] }}</div>
                                <div class="mt-1 font-bold">
                                    @if($type === 'level')
                                        <span class="text-orange-500">Level {{ $leaderboardData['leaderboard'][2]['level'] }}</span>
                                    @elseif($type === 'love')
                                        <span class="text-primary flex items-center justify-center">
                                            <i class="fas fa-heart mr-1"></i>
                                            {{ $leaderboardData['leaderboard'][2]['total_love'] }}
                                        </span>
                                    @else
                                        <span class="text-orange-500 flex items-center justify-center">
                                            <i class="fas fa-trophy mr-1"></i>
                                            {{ $leaderboardData['leaderboard'][2]['achievements_count'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Full Leaderboard Table -->
            <div class="px-6 pb-6">
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rank
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Turtle
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                @if($type === 'level')
                                    Level
                                @elseif($type === 'love')
                                    Love Points
                                @else
                                    Achievements
                                @endif
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($leaderboardData['leaderboard'] as $entry)
                            <tr class="{{ $entry['user_id'] === auth()->id() ? 'bg-primary/5' : '' }} hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($entry['rank'] <= 3)
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold
                                                            {{ $entry['rank'] == 1 ? 'bg-primary/10 text-primary' :
                                                              ($entry['rank'] == 2 ? 'bg-secondary/10 text-secondary' :
                                                               'bg-orange-100 text-orange-500') }}">
                                                {{ $entry['rank'] }}
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-medium">
                                                {{ $entry['rank'] }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $entry['turtle_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $entry['user_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($type === 'level')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                                Level {{ $entry['level'] }}
                                            </span>
                                    @elseif($type === 'love')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                                <i class="fas fa-heart mr-1"></i> {{ $entry['total_love'] }}
                                            </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary/10 text-secondary">
                                                <i class="fas fa-trophy mr-1"></i> {{ $entry['achievements_count'] }}
                                            </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- How to Climb the Leaderboard -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">How to Climb the Leaderboard</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-primary/5 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-primary/20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-tasks text-primary"></i>
                        </div>
                        <h4 class="font-medium text-gray-800">Complete Tasks</h4>
                    </div>
                    <p class="text-sm text-gray-600">Complete daily and weekly tasks to earn love points and experience. The more tasks you complete, the faster your turtle will level up.</p>
                </div>

                <div class="bg-secondary/5 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-secondary/20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-wallet text-secondary"></i>
                        </div>
                        <h4 class="font-medium text-gray-800">Create MEXC Accounts</h4>
                    </div>
                    <p class="text-sm text-gray-600">Create MEXC accounts to earn love points. Each new account earns you points, and completing account creation milestones grants special rewards.</p>
                </div>

                <div class="bg-success/5 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-success/20 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-bullseye text-success"></i>
                        </div>
                        <h4 class="font-medium text-gray-800">Reach Targets</h4>
                    </div>
                    <p class="text-sm text-gray-600">Complete monthly and weekly targets to earn bonus love points and experience. Higher targets offer greater rewards to help you climb the leaderboard.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function leaderboard() {
            return {
                init() {
                    // Initialize any leaderboard-specific functionality here
                }
            };
        }
    </script>
@endpush