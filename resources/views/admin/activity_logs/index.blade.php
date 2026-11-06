@extends('layouts.app')

@section('content')
@php $pageTitle = "Historique des activités"; @endphp

<div class="min-h-screen bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- En-tête --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center shadow">
                <i class="fas fa-history text-white text-base"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Historique des activités</h1>
                <p class="text-sm text-slate-500">Toutes les actions effectuées sur la plateforme</p>
            </div>
        </div>
        <a href="{{ route('admin.activity_logs.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm rounded-xl transition">
            <i class="fas fa-sync text-xs"></i> Actualiser
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Total actions</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Aujourd'hui</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $stats['today'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Suppressions</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['deletes'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Tentatives bloquées</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['blocked'] }}</p>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('admin.activity_logs.index') }}"
          class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Description, utilisateur, IP..."
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Utilisateur</label>
                <select name="user_id" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tous</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Action</label>
                <select name="action" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Toutes</option>
                    @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Appareil</label>
                <select name="device" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tous</option>
                    <option value="Desktop" {{ request('device') == 'Desktop' ? 'selected' : '' }}>🖥️ Desktop</option>
                    <option value="Mobile"  {{ request('device') == 'Mobile' ? 'selected' : '' }}>📱 Mobile</option>
                    <option value="Tablet"  {{ request('device') == 'Tablet' ? 'selected' : '' }}>📟 Tablette</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Du</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Au</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Modèle</label>
                <input type="text" name="model" value="{{ request('model') }}" placeholder="Classe, Student..."
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition">
                    <i class="fas fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.activity_logs.index') }}"
                   class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition text-center">
                    Réinitialiser
                </a>
            </div>
        </div>
    </form>

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">
                {{ $logs->total() }} entrée(s)
                @if(request()->hasAny(['search','user_id','action','device','date_from','date_to','model']))
                    <span class="ml-2 text-xs font-normal text-indigo-600">(filtré)</span>
                @endif
            </h2>
            <span class="text-xs text-slate-400">Page {{ $logs->currentPage() }} / {{ $logs->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date & heure</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Utilisateur</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Rôle</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">IP / Appareil</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Détail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                    @php
                        $colors = [
                            'create'   => 'bg-green-100 text-green-700',
                            'update'   => 'bg-blue-100 text-blue-700',
                            'delete'   => 'bg-red-100 text-red-700',
                            'login'    => 'bg-indigo-100 text-indigo-700',
                            'logout'   => 'bg-gray-100 text-gray-600',
                            'export'   => 'bg-purple-100 text-purple-700',
                            'validate' => 'bg-teal-100 text-teal-700',
                        ];
                        $color = $colors[$log->action] ?? 'bg-slate-100 text-slate-600';
                        $isBlocked = str_contains($log->description, 'BLOQUÉE');
                        $rowBg = $isBlocked ? 'bg-orange-50' : '';
                    @endphp
                    <tr class="hover:bg-slate-50 transition {{ $rowBg }}">
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-600">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-800 text-xs">{{ $log->user_name ?? 'Anonyme' }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $log->user_role ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $color }}">
                                {{ ucfirst($log->action) }}
                            </span>
                            @if($isBlocked)
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                    ⛔ BLOQUÉ
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-700 max-w-xs">
                            <div class="truncate" title="{{ $log->description }}">{{ $log->description }}</div>
                            @if($log->model)
                            <div class="text-slate-400 mt-0.5">{{ $log->model }} #{{ $log->model_id }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            <div>{{ $log->ip_address }}</div>
                            <div class="text-slate-400">
                                {{ match($log->device) { 'Mobile' => '📱', 'Tablet' => '📟', 'Desktop' => '🖥️', default => '🌐' } }}
                                {{ $log->device ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.activity_logs.show', $log->id) }}"
                               class="inline-flex items-center justify-center w-7 h-7 bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 rounded-lg transition">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            <i class="fas fa-history text-3xl mb-3 block"></i>
                            Aucune activité trouvée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
