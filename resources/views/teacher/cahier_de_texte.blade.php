@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6 max-w-4xl">
    <h1 class="text-2xl font-bold mb-1 text-slate-800">Cahier de texte</h1>
    <p class="text-sm text-slate-500 mb-6">{{ \Carbon\Carbon::now()->isoFormat('dddd D MMMM YYYY') }}</p>

    @if(session('error'))
        <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-xl mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-xl mb-4">
            {{ session('success') }}
        </div>
    @endif

    @forelse($timetable as $slot)
    <div class="mb-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- En-tête du créneau --}}
        <div class="px-5 py-4 bg-indigo-600 flex items-center justify-between">
            <div>
                <p class="text-white font-bold text-base">{{ $slot->subject->name ?? 'Matière non assignée' }}</p>
                <p class="text-indigo-200 text-sm mt-0.5">
                    <i class="fas fa-clock mr-1"></i>
                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}
                    —
                    {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                </p>
            </div>
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-book-open text-white"></i>
            </div>
        </div>

        {{-- Formulaire --}}
        <form action="{{ route('teacher.cahier.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="class_id"     value="{{ $slot->class_id }}">
            <input type="hidden" name="subject_id"   value="{{ $slot->subject_id }}">
            <input type="hidden" name="teacher_id"   value="{{ $slot->teacher_id }}">
            <input type="hidden" name="timetable_id" value="{{ $slot->id }}">
            <input type="hidden" name="day"          value="{{ $slot->day }}">

            {{-- Dates début / fin --}}
            @php
                $today    = \Carbon\Carbon::today()->format('Y-m-d');
                $startStr = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                $endStr   = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        <i class="fas fa-calendar-alt text-indigo-500 mr-1"></i>
                        Début du cours
                    </label>
                    <input type="datetime-local"
                           name="course_start_date"
                           value="{{ $today }}T{{ $startStr }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           required>
                    @error('course_start_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        <i class="fas fa-calendar-check text-indigo-500 mr-1"></i>
                        Fin du cours
                    </label>
                    <input type="datetime-local"
                           name="course_end_date"
                           value="{{ $today }}T{{ $endStr }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           required>
                    @error('course_end_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Contenu du cours --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    <i class="fas fa-pen text-indigo-500 mr-1"></i>
                    Contenu du cours <span class="text-red-500">*</span>
                </label>
                <textarea name="content" rows="4"
                          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition resize-none"
                          placeholder="Décrivez le contenu du cours ici..." required></textarea>
                @error('content')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow transition-all duration-200 active:scale-95">
                    <i class="fas fa-save text-xs"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
    @empty
    <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-calendar-times text-indigo-300 text-2xl"></i>
        </div>
        <h3 class="text-base font-bold text-slate-700 mb-1">Aucun cours aujourd'hui</h3>
        <p class="text-sm text-slate-400">Vous n'avez pas de créneau programmé pour ce jour.</p>
    </div>
    @endforelse
</div>
@endsection
