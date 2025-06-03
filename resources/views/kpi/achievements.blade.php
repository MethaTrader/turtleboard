@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Achievements</h2>
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
                        <img src="{{ $turtleData['turtle']['equipped_items']['shell']['image_path'] ?? '/images/turtles/shells/green.png' }}" alt="Turtle" class="w-10 h-10">
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">{{ $turtleData['turtle']['name'] }}</h3>
                        <p class="text-sm text-gray-600">Level {{ $turtleData['turtle']['level'] }} Turtle</p>
                    </div>
                </div>
                <div>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <span>Achievements Completed:</span>
                        <span class="font-bold text-primary">{{ count(array_filter($achievements->toArray(), function($achievement) { return $achievement['earned']; })) }}</span>
                        <span>/</span>
                        <span>{{ $achievements->count() }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-primary h-2.5 rounded-full" style="width: {{ (count(array_filter($achievements->toArray(), function($achievement) { return $achievement['earned']; })) / max(1, $achievements->count())) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Achievements Grid -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800">All Achievements</h3>
                <p class="text-sm text-gray-600 mt-1">Complete achievements to earn rewards and showcase your progress.</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($achievements as $achievement)
                    <div class="border rounded-lg overflow-hidden transition-all hover:shadow-lg {{ $achievement['earned'] ? 'border-primary' : 'border-gray-200 opacity-75' }}">
                        <div class="p-4 flex items-start">
                            <div class="h-14 w-14 rounded-full {{ $achievement['color'] }} flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas {{ $achievement['icon'] }} text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">
                                    {{ $achievement['name'] }}
                                    @if($achievement['earned'])
                                        <i class="fas fa-check-circle text-primary ml-1"></i>
                                    @endif
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $achievement['description'] }}</p>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                            @if($achievement['earned'])
                                <span class="text-xs text-gray-500">Earned on {{ \Carbon\Carbon::parse($achievement['awarded_at'])->format('M d, Y') }}</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                    <i class="fas fa-trophy mr-1"></i> Completed
                                </span>
                            @else
                                <span class="text-xs text-gray-500">Not yet earned</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-lock mr-1"></i> Locked
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Upcoming Achievements -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800">Earn More Achievements</h3>
                <p class="text-sm text-gray-600 mt-1">Here are some achievements you can work towards.</p>
            </div>

            <div class="p-6 space-y-4">
                @php
                    $unearnedAchievements = $achievements->filter(function($achievement) {
                        return !$achievement['earned'];
                    })->take(3);
                @endphp

                @if($unearnedAchievements->count() > 0)
                    @foreach($unearnedAchievements as $achievement)
                        <div class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="h-10 w-10 rounded-full {{ $achievement['color'] }} flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas {{ $achievement['icon'] }} text-white"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-800">{{ $achievement['name'] }}</h4>
                                <p class="text-sm text-gray-600">{{ $achievement['description'] }}</p>
                            </div>
                            <div class="ml-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-secondary/10 text-secondary">
                                    <i class="fas fa-star mr-1"></i> In Progress
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-trophy text-2xl text-gray-400"></i>
                        </div>
                        <h4 class="text-lg font-medium text-gray-700">All achievements completed!</h4>
                        <p class="text-sm text-gray-500 mt-1">Congratulations on your impressive accomplishment!</p>
                    </div>
                @endif

                <div class="flex justify-center mt-6">
                    <a href="{{ route('kpi.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-md transition-colors">
                        <i class="fas fa-tasks mr-2"></i>
                        View Available Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection