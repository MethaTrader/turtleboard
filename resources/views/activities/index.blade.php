@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-text-primary">My Activities</h2>
                <p class="text-text-secondary">Complete history of your account activities</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-card p-4 rounded-card shadow-card">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select name="entity_type" class="border-gray-300 rounded-md">
                    <option value="">All Entity Types</option>
                    @foreach($entityTypes as $key => $name)
                        <option value="{{ $key }}" {{ request('entity_type') == $key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>

                <select name="action_type" class="border-gray-300 rounded-md">
                    <option value="">All Actions</option>
                    @foreach($actionTypes as $key => $name)
                        <option value="{{ $key }}" {{ request('action_type') == $key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-gray-300 rounded-md" placeholder="From Date">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-gray-300 rounded-md" placeholder="To Date">

                <div class="flex gap-2">
                    <button type="submit" class="bg-secondary text-white px-4 py-2 rounded-md hover:bg-secondary/90">
                        Filter
                    </button>
                    <a href="{{ route('activities.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Activities List -->
        <div class="bg-card rounded-card shadow-card">
            @if($activities->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($activities as $activity)
                        <div class="p-4 flex items-center">
                            <div class="h-10 w-10 rounded-lg {{ $activity->getColorClasses() }} flex items-center justify-center mr-4">
                                <i class="{{ $activity->getIcon() }}"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <span class="font-medium">{{ $activity->description }}</span>
                                    <span class="text-sm text-text-secondary">{{ $activity->getFormattedTime() }}</span>
                                </div>
                                @if($activity->metadata)
                                    <div class="text-sm text-text-secondary mt-1">
                                        @if(isset($activity->metadata['email_address']))
                                            {{ $activity->metadata['email_address'] }}
                                        @elseif(isset($activity->metadata['ip_address']))
                                            {{ $activity->metadata['ip_address'] }}:{{ $activity->metadata['port'] }}
                                        @elseif(isset($activity->metadata['address']))
                                            {{ substr($activity->metadata['address'], 0, 6) }}...{{ substr($activity->metadata['address'], -4) }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t">
                    {{ $activities->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-8 text-center">
                    <i class="fas fa-history text-gray-400 text-3xl mb-4"></i>
                    <h3 class="text-lg font-medium text-text-primary mb-2">No Activities Found</h3>
                    <p class="text-text-secondary">No activities match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
@endsection