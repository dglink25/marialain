@extends('layouts.app')

@section('content')
@php $pageTitle = "Détail de l'activité #" . $activityLog->id; @endphp

<div class="min-h-screen bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.activity_logs.index') }}"
               class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                <i class="fas fa-arrow-left text-slate-600 text-sm"></i>
            </a>
            <h1 class="text-xl font-bold text-slate-800">Détail de l'activité #{{ $activityLog->id }}</h1>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                @php
                    $colors = ['create'=>'bg-green-100 text-green-700','update'=>'bg-blue-100 text-blue-700','delete'=>'bg-red-100 text-red-700','login'=>'bg-indigo-100 text-indigo-700','logout'=>'bg-gray-100 text-gray-600'];
                    $color = $colors[$activityLog->action] ?? 'bg-slate-100 text-slate-600';
                    $isBlocked = str_contains($activityLog->description, 'BLOQUÉE');
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $color }}">
                    {{ ucfirst($activityLog->action) }}
                </span>
                @if($isBlocked)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-orange-100 text-orange-700">
                        ⛔ Tentative bloquée
                    </span>
                @endif
                <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($activityLog->created_at)->format('d/m/Y à H:i:s') }}</span>
            </div>

            <div class="p-6 space-y-5">

                {{-- Infos utilisateur --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Utilisateur</p>
                        <p class="font-bold text-slate-800">{{ $activityLog->user_name ?? 'Anonyme' }}</p>
                        <p class="text-xs text-slate-500">Rôle : {{ $activityLog->user_role ?? '—' }}</p>
                        @if($activityLog->user_id)
                        <p class="text-xs text-slate-400">ID : {{ $activityLog->user_id }}</p>
                        @endif
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Appareil & Réseau</p>
                        <p class="font-semibold text-slate-800">
                            {{ match($activityLog->device) { 'Mobile' => '📱', 'Tablet' => '📟', 'Desktop' => '🖥️', default => '🌐' } }}
                            {{ $activityLog->device ?? 'Inconnu' }}
                        </p>
                        <p class="text-xs text-slate-500">IP : {{ $activityLog->ip_address ?? '—' }}</p>
                    </div>
                </div>

                {{-- Description --}}
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Description</p>
                    <p class="text-slate-800 font-medium">{{ $activityLog->description }}</p>
                </div>

                {{-- URL & Méthode --}}
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Requête HTTP</p>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded">{{ $activityLog->method }}</span>
                        <span class="text-xs text-slate-600 font-mono break-all">{{ $activityLog->url }}</span>
                    </div>
                </div>

                {{-- Modèle concerné --}}
                @if($activityLog->model)
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Objet concerné</p>
                    <p class="text-slate-800"><strong>{{ $activityLog->model }}</strong> #{{ $activityLog->model_id }}</p>
                </div>
                @endif

                {{-- Données avant --}}
                @if($activityLog->old_data)
                <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                    <p class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-2">Données avant modification</p>
                    <pre class="text-xs text-slate-700 overflow-x-auto">{{ json_encode($activityLog->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                @endif

                {{-- Données après --}}
                @if($activityLog->new_data)
                <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                    <p class="text-xs font-semibold text-green-500 uppercase tracking-wide mb-2">Données après modification</p>
                    <pre class="text-xs text-slate-700 overflow-x-auto">{{ json_encode($activityLog->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                @endif

                {{-- User Agent --}}
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">User Agent</p>
                    <p class="text-xs text-slate-500 font-mono break-all">{{ $activityLog->user_agent ?? '—' }}</p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
