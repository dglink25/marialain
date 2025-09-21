@extends('layouts.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">

    <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">Emploi du temps - {{ $class->name }}</h1>

    <!-- Bouton pour afficher le formulaire -->
    <div class="flex justify-center mb-8">
        <button id="toggleFormBtn" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700 transition duration-300 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Ajouter un cours
        </button>
    </div>

    <!-- Formulaire d'ajout (caché par défaut) -->
    <div id="addFormContainer" class="hidden mb-8 bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300">
        <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Ajouter un nouveau cours</h2>
        
        <form action="{{ route('censeur.timetables.store', $class->id) }}" method="POST" 
              class="grid md:grid-cols-6 gap-4 mb-4">
            @csrf
            
            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-600 mb-1">Enseignant</label>
                <select name="teacher_id" class="border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-600 mb-1">Matière</label>
                <select name="subject_id" class="border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-600 mb-1">Jour</label>
                <select name="day" class="border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-600 mb-1">Heure début</label>
                <input type="time" name="start_time" class="border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div class="flex flex-col">
                <label class="text-sm font-medium text-gray-600 mb-1">Heure fin</label>
                <input type="time" name="end_time" class="border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300 w-full flex justify-center items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Ajouter
                </button>
            </div>
        </form>
        
        <div class="flex justify-end">
            <button id="closeFormBtn" class="text-gray-500 hover:text-gray-700 text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Fermer
            </button>
        </div>
    </div>

    <!-- Actions (PDF et Retour) -->
    <div class="flex justify-between mb-6">
        <a href="{{ route('censeur.classes.index') }}" 
           class="bg-gray-600 text-white px-5 py-2.5 rounded-lg shadow hover:bg-gray-700 transition duration-300 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour
        </a>
        
        <a href="{{ route('censeur.timetables.download', $class->id) }}"
           class="bg-green-600 text-white px-5 py-2.5 rounded-lg shadow hover:bg-green-700 transition duration-300 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Télécharger PDF
        </a>
    </div>

    <!-- Tableau avec colonnes fixes et alignement corrigé -->
    <div class="overflow-x-auto border border-gray-200 rounded-xl shadow-lg bg-white timetable-container">
        <table class="w-full border-collapse text-sm md:text-base timetable-fixed">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-center font-semibold text-gray-700 timetable-hour">Heure</th>
                    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                        <th class="p-3 text-center font-semibold text-gray-700 timetable-day">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($hours as $hourSlot)
                    @php
                        [$startHour, $endHour] = explode('-', $hourSlot);
                        $startHourFormatted = str_replace('h', ':00', $startHour);
                    @endphp

                    <tr class="hover:bg-gray-50 transition duration-150 timetable-row">
                        <td class="p-3 text-center font-semibold bg-gray-50 text-gray-700 timetable-hour align-middle">{{ $hourSlot }}</td>

                        @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                            @php
                                $course = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                    return $t->day === $day && date('H:i', strtotime($t->start_time)) == $startHourFormatted;
                                });

                                $overlap = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                    return $t->day === $day 
                                           && strtotime($t->start_time) < strtotime($startHourFormatted) 
                                           && strtotime($t->end_time) > strtotime($startHourFormatted);
                                });
                            @endphp

                            @if($course)
                                @php
                                    // Calcul du rowspan en nombre de créneaux (1 créneau = 1h)
                                    $duration = max(1, round((strtotime($course->end_time) - strtotime($course->start_time)) / 3600));
                                @endphp
                                <td class="p-2 align-middle bg-blue-50 text-blue-800 hover:bg-blue-100 transition duration-150 timetable-day timetable-course"
                                    rowspan="{{ $duration }}">
                                    <div class="flex flex-col items-center justify-center h-full p-1 text-center">
                                        <div class="font-bold text-sm mb-1 leading-tight">{{ $course->subject->name }}</div>
                                        <div class="text-xs mb-1">{{ $course->teacher->name }}</div>
                                        <div class="text-xs bg-blue-200 rounded-full px-2 py-1 inline-block mb-2">
                                            {{ date('H:i', strtotime($course->start_time)) }} - {{ date('H:i', strtotime($course->end_time)) }}
                                        </div>
                                        <div class="mt-auto">
                                            <a href="{{ route('censeur.timetables.edit', [$class->id, $course->id]) }}"
                                               class="bg-yellow-500 text-white px-2 py-1 rounded text-xs transition duration-200 inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Modifier
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            @elseif($overlap)
                                {{-- Cellule fusionnée --}}
                            @else
                                <td class="p-3 hover:bg-gray-50 timetable-day align-middle"></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleFormBtn = document.getElementById('toggleFormBtn');
        const closeFormBtn = document.getElementById('closeFormBtn');
        const addFormContainer = document.getElementById('addFormContainer');
        
        // Afficher le formulaire
        toggleFormBtn.addEventListener('click', function() {
            addFormContainer.classList.toggle('hidden');
            addFormContainer.classList.toggle('animate-fadeIn');
        });
        
        // Cacher le formulaire
        closeFormBtn.addEventListener('click', function() {
            addFormContainer.classList.add('hidden');
            addFormContainer.classList.remove('animate-fadeIn');
        });
        
        // Animation CSS pour l'apparition du formulaire
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fadeIn {
                animation: fadeIn 0.3s ease-out;
            }
        `;
        document.head.appendChild(style);
    });
</script>

<style>
    /* Styles supplémentaires pour améliorer l'apparence */
    select, input[type="time"] {
        transition: all 0.3s ease;
    }
    
    select:focus, input[type="time"]:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    
    .bg-blue-50 {
        background-color: #eff6ff;
    }
    
    .bg-blue-100 {
        background-color: #dbeafe;
    }
    
    /* Styles pour le tableau à colonnes fixes */
    .timetable-fixed {
        table-layout: fixed;
        width: 100%;
    }
    
    .timetable-hour {
        width: 90px !important;
        min-width: 90px;
        max-width: 90px;
    }
    
    .timetable-day {
        width: calc((100% - 90px) / 6) !important;
        min-width: 130px;
    }
    
    /* Bordures pour le tableau */
    .timetable-fixed th,
    .timetable-fixed td {
        border: 1px solid #e5e7eb;
    }
    
    /* Hauteur fixe pour les lignes */
    .timetable-row {
        height: 80px;
    }
    
    /* Alignement vertical pour toutes les cellules */
    .timetable-fixed td {
        vertical-align: middle;
    }
    
    /* Centrage parfait du contenu */
    .timetable-course > div {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
        text-align: center;
    }
    
    /* Assurer que le texte ne dépasse pas */
    .timetable-day div {
        overflow: hidden;
    }
    
    .timetable-day .font-bold {
        white-space: normal;
        word-wrap: break-word;
        line-height: 1.2;
        max-height: 2.4em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    /* Container avec défilement horizontal si nécessaire */
    .timetable-container {
        max-width: 100%;
        overflow-x: auto;
    }
    
    /* Alignement spécifique pour les cellules de cours */
    .timetable-course {
        vertical-align: top;
        padding: 8px !important;
    }
</style>
@endsection