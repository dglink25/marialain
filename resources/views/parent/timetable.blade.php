{{-- resources/views/parent/timetable.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.parent')

@section('title', 'Emploi du temps - ' . $student->full_name)

@section('page-title', 'Emploi du temps')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $student->full_name }}</li>
@endsection

@section('content')

@php
    $emploiDuTemps = [];
    $joursListe = array_keys($joursSemaine);
    
    foreach ($joursListe as $jour) {
        $emploiDuTemps[$jour] = array_fill(0, count($hours), null);
        
        if (isset($sortedTimetables[$jour])) {
            foreach ($sortedTimetables[$jour] as $cours) {
                $heureDebut = date('H', strtotime($cours->start_time));
                $heureFin = date('H', strtotime($cours->end_time));
                $duration = $heureFin - $heureDebut;
                
                // Trouver l'index dans les heures
                for ($i = 0; $i < count($hours); $i++) {
                    $heureSlot = intval(substr($hours[$i]['start'], 0, 2));
                    if ($heureSlot == $heureDebut) {
                        $emploiDuTemps[$jour][$i] = [
                            'cours' => $cours,
                            'duration' => $duration,
                            'rowspan' => $duration
                        ];
                        break;
                    }
                }
            }
        }
    }
@endphp

<div class="container-fluid p-3 p-md-4 bg-light min-vh-100">
    <!-- En-tête -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="text-success mb-2 fw-bold">
                <i class="fas fa-calendar-alt me-2"></i>
                Emploi du temps - {{ $student->full_name }}
            </h2>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge bg-success">
                    <i class="fas fa-graduation-cap me-1"></i>
                    {{ $student->classe->name ?? 'Classe non assignée' }}
                </span>
                <span class="badge bg-info text-dark">
                    <i class="fas fa-calendar me-1"></i>
                    {{ $activeYear->name ?? 'Année en cours' }}
                </span>
                <span class="badge bg-secondary">
                    <i class="fas fa-clock me-1"></i>
                    {{ count($hours) }} créneaux horaires
                </span>
            </div>
        </div>
        
        <!-- Bouton retour simplifié -->
        <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-success px-4 py-2 d-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            <span>Retour au tableau de bord</span>
        </a>
    </div>

    <!-- Messages -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fs-4"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Vérification s'il y a des cours -->
    @if($timetables->isEmpty())
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-3 fs-4"></i>
                <div>
                    <strong>Aucun cours programmé</strong>
                    <p class="mb-0">L'emploi du temps pour cette classe n'est pas encore disponible.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Tableau emploi du temps -->
        <div class="card border-0 shadow-lg mb-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" style="min-width: 800px;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center align-middle" style="width: 100px; background-color: #f8f9fa;">
                                    <div class="fw-bold text-success">Heure</div>
                                </th>
                                @foreach($joursListe as $jour)
                                    <th class="text-center align-middle" style="min-width: 150px; background-color: #f8f9fa;">
                                        <div class="fw-bold text-success">{{ $jour }}</div>
                                        <div class="small text-muted">{{ now()->startOfWeek($loop->index + 1)->format('d/m') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hours as $index => $hour)
                                <tr>
                                    <!-- Cellule Heure -->
                                    <td class="text-center align-middle fw-bold bg-light" 
                                        style="background-color: #f8f9fa !important;">
                                        <div class="text-success">{{ $hour['slot'] }}</div>
                                    </td>
                                    
                                    <!-- Cellules pour chaque jour -->
                                    @foreach($joursListe as $jour)
                                        @php
                                            $cellule = $emploiDuTemps[$jour][$index] ?? null;
                                            $estDebutCours = $cellule !== null;
                                            $rowspan = $cellule['rowspan'] ?? 1;
                                            $cours = $cellule['cours'] ?? null;
                                            $skipRow = false;
                                            
                                            // Vérifier si cette cellule doit être sautée (cours sur plusieurs créneaux)
                                            for ($i = 0; $i < $index; $i++) {
                                                $previousCell = $emploiDuTemps[$jour][$i] ?? null;
                                                if ($previousCell && isset($previousCell['rowspan'])) {
                                                    $previousRowspan = $previousCell['rowspan'] ?? 1;
                                                    if ($index - $i < $previousRowspan) {
                                                        $skipRow = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        @if($skipRow)
                                            {{-- Cellule déjà remplie par un cours précédent --}}
                                        @elseif($estDebutCours)
                                            <td class="align-middle text-center p-2 course-cell"
                                                rowspan="{{ $rowspan }}"
                                                data-course-id="{{ $cours->id }}"
                                                style="background-color: rgba(25, 135, 84, 0.1); border: 2px solid rgba(25, 135, 84, 0.2);">
                                                <div class="d-flex flex-column justify-content-between h-100">
                                                    <div class="mb-2">
                                                        <div class="fw-bold text-truncate mb-1" 
                                                             style="color: #198754; font-size: 1rem;"
                                                             title="{{ $cours->subject->name }}">
                                                            <i class="fas fa-book me-1"></i>
                                                            {{ $cours->subject->name }}
                                                        </div>
                                                        <div class="text-muted small text-truncate mb-2"
                                                             title="{{ $cours->teacher->name }}">
                                                            <i class="fas fa-chalkboard-teacher me-1"></i>
                                                            {{ $cours->teacher->name }}
                                                        </div>
                                                        <div class="badge bg-light text-dark border mb-2" style="font-size: 0.75rem;">
                                                            <i class="far fa-clock me-1"></i>
                                                            {{ date('H:i', strtotime($cours->start_time)) }} - {{ date('H:i', strtotime($cours->end_time)) }}
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Information supplémentaire pour les parents -->
                                                    <div class="mt-auto small text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Durée: {{ $rowspan }}h
                                                    </div>
                                                </div>
                                            </td>
                                        @else
                                            <td class="empty-cell" style="min-height: 80px;">
                                                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                    <small>Aucun cours</small>
                                                </div>
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Légende et informations -->
    <div class="row g-3 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">
                        <i class="fas fa-info-circle text-success me-2"></i>
                        Informations
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Élève:</strong> {{ $student->full_name }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Classe:</strong> {{ $student->classe->name ?? 'Non assignée' }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Année académique:</strong> {{ $activeYear->name }}
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Total cours:</strong> {{ $timetables->flatten()->count() }} cours dans la semaine
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">
                        <i class="fas fa-clock text-success me-2"></i>
                        Horaires
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-sun text-warning me-2"></i>
                            <strong>Matin:</strong> 08h00 - 12h00
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-utensils text-danger me-2"></i>
                            <strong>Pause déjeuner:</strong> 12h00 - 14h00
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-moon text-primary me-2"></i>
                            <strong>Après-midi:</strong> 14h00 - 17h00
                        </li>
                        <li>
                            <i class="fas fa-calendar-week text-info me-2"></i>
                            <strong>Semaine:</strong> Lundi au Samedi
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styles généraux */
    .min-vh-100 {
        min-height: 100vh;
    }

    /* Tableau */
    .table {
        --bs-table-bg: transparent;
    }

    .course-cell {
        position: relative;
        transition: all 0.3s ease;
        vertical-align: middle !important;
        cursor: default;
    }

    .course-cell:hover {
        background-color: rgba(25, 135, 84, 0.15) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 1;
    }

    .empty-cell {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
        text-align: center;
        vertical-align: middle;
    }

    .empty-cell:hover {
        background-color: #e9ecef;
    }

    /* Badges */
    .badge.bg-success {
        background: linear-gradient(135deg, #198754, #157347) !important;
    }

    .badge.bg-info {
        background: linear-gradient(135deg, #0dcaf0, #0b9ed8) !important;
    }

    /* Animation pour les cellules de cours */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .course-cell {
        animation: fadeIn 0.3s ease-out;
    }

    /* Tooltip personnalisé */
    [title] {
        position: relative;
        cursor: help;
    }

    [title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* Scrollbar personnalisée pour le tableau */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .card-body {
            padding: 0.5rem !important;
        }
        
        .table {
            font-size: 0.85rem;
        }
        
        .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .course-cell .badge {
            font-size: 0.7rem !important;
        }
        
        .course-cell .small {
            font-size: 0.75rem !important;
        }
    }

    @media (max-width: 576px) {
        .table {
            font-size: 0.8rem;
        }
        
        .course-cell {
            padding: 0.5rem !important;
        }
        
        .course-cell i {
            font-size: 0.8rem;
        }
        
        .container-fluid {
            padding: 0.5rem !important;
        }
    }

    /* Impression */
    @media print {
        .btn, .alert, .card:last-child {
            display: none !important;
        }
        
        .table {
            border: 1px solid #000;
        }
        
        .course-cell {
            background-color: #f0f0f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endsection