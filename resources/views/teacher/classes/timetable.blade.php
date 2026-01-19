@extends('layouts.app')

@section('content')
@php
    $joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    
    // Créer une structure de données pour faciliter l'affichage
    $emploiDuTemps = [];
    
    foreach ($joursSemaine as $jour) {
        $emploiDuTemps[$jour] = array_fill(0, count($hours), null);
        
        if (isset($sortedTimetables[$jour])) {
            foreach ($sortedTimetables[$jour] as $cours) {
                $heureDebut = date('H:i', strtotime($cours->start_time));
                $heureFin = date('H:i', strtotime($cours->end_time));
                
                // Trouver l'index du créneau de début
                $indexDebut = null;
                foreach ($hours as $index => $hour) {
                    if ($hour['start'] == $heureDebut) {
                        $indexDebut = $index;
                        break;
                    }
                }
                
                // Calculer la durée en nombre de créneaux
                if ($indexDebut !== null) {
                    $duration = 0;
                    $currentTime = $heureDebut;
                    
                    while ($currentTime < $heureFin) {
                        $duration++;
                        // Trouver le prochain créneau
                        $found = false;
                        foreach ($hours as $hour) {
                            if ($hour['start'] == $currentTime) {
                                // Convertir l'heure en minutes, ajouter 60 minutes, puis reconvertir
                                [$h, $m] = explode(':', $currentTime);
                                $totalMinutes = ($h * 60 + $m) + 60;
                                $newH = floor($totalMinutes / 60);
                                $newM = $totalMinutes % 60;
                                $currentTime = sprintf('%02d:%02d', $newH, $newM);
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) break;
                    }
                    
                    if ($duration > 0) {
                        $emploiDuTemps[$jour][$indexDebut] = [
                            'cours' => $cours,
                            'duration' => $duration,
                            'rowspan' => $duration
                        ];
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
            <h2 class="text-primary mb-2 fw-bold">
                <i class="fas fa-calendar-alt me-2"></i>
                Emploi du temps - {{ $class->name }}
            </h2>
            <div class="d-flex flex-wrap gap-2 align-items-center">
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
        
        <!-- Bouton Retour -->
        <a href="{{ route('teacher.classes') }}" 
           class="btn btn-primary px-4 py-2 fw-semibold d-flex align-items-center gap-2">
            <i class="fas fa-arrow-left me-2"></i>
            Retour aux classes
        </a>
    </div>

    <!-- Tableau emploi du temps -->
    <div class="card border-0 shadow-lg mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="min-width: 800px;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center align-middle" style="width: 100px; background-color: #f8f9fa;">
                                <div class="fw-bold text-primary">Heure</div>
                            </th>
                            @foreach($joursSemaine as $jour)
                                <th class="text-center align-middle" style="min-width: 150px; background-color: #f8f9fa;">
                                    <div class="fw-bold text-primary">{{ $jour }}</div>
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
                                    <div class="text-primary">{{ $hour['slot'] }}</div>
                                </td>
                                
                                <!-- Cellules pour chaque jour -->
                                @foreach($joursSemaine as $jour)
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
                                        
                                        // Déterminer si c'est le cours de l'enseignant connecté
                                        $isMyCourse = $cours && $cours->teacher_id == auth()->id();
                                        $bgColor = $isMyCourse ? 'rgba(13, 110, 253, 0.1)' : 'rgba(108, 117, 125, 0.1)';
                                        $borderColor = $isMyCourse ? 'rgba(13, 110, 253, 0.2)' : 'rgba(108, 117, 125, 0.2)';
                                        $textColor = $isMyCourse ? '#0d6efd' : '#6c757d';
                                    @endphp
                                    
                                    @if($skipRow)
                                        {{-- Cellule déjà remplie par un cours précédent --}}
                                    @elseif($estDebutCours)
                                        <td class="align-middle text-center p-2 course-cell"
                                            rowspan="{{ $rowspan }}"
                                            data-course-id="{{ $cours->id }}"
                                            style="background-color: {{ $bgColor }}; border: 2px solid {{ $borderColor }};">
                                            <div class="d-flex flex-column justify-content-center h-100">
                                                @if($isMyCourse)
                                                    {{-- Afficher les détails du cours si c'est le cours de l'enseignant --}}
                                                    <div class="mb-2">
                                                        <div class="fw-bold text-truncate mb-1" 
                                                             style="color: {{ $textColor }}; font-size: 0.9rem;"
                                                             title="{{ $cours->subject->name ?? 'Cours' }}">
                                                            {{ $cours->subject->name ?? 'Cours' }}
                                                        </div>
                                                        <div class="text-muted small text-truncate mb-2"
                                                             title="{{ $cours->teacher->name ?? 'Enseignant' }}">
                                                            <i class="fas fa-user-tie me-1"></i>
                                                            {{ $cours->teacher->name ?? 'Enseignant' }}
                                                        </div>
                                                        <div class="badge bg-info text-dark mb-2" style="font-size: 0.75rem;">
                                                            {{ date('H:i', strtotime($cours->start_time)) }} - {{ date('H:i', strtotime($cours->end_time)) }}
                                                        </div>
                                                    </div>
                                                    <div class="mt-auto">
                                                        <span class="badge bg-success" style="font-size: 0.7rem;">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            Votre cours
                                                        </span>
                                                    </div>
                                                @else
                                                    {{-- Afficher "Réservé" avec icône de cadenas si ce n'est pas le cours de l'enseignant --}}
                                                    <div class="d-flex flex-column align-items-center justify-content-center h-100 py-3">
                                                        <div class="mb-3">
                                                            <i class="fas fa-lock fa-2x text-muted"></i>
                                                        </div>
                                                        <div class="fw-bold text-muted mb-2">
                                                            Réservé
                                                        </div>
                                                        <div class="badge bg-secondary text-light mb-2" style="font-size: 0.75rem;">
                                                            {{ date('H:i', strtotime($cours->start_time)) }} - {{ date('H:i', strtotime($cours->end_time)) }}
                                                        </div>
                                                        <div class="small text-muted mt-2 text-center">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Cours d'un autre enseignant
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        <td class="empty-cell" style="min-height: 80px;"></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Légende -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Légende</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="me-2" style="width: 20px; height: 20px; background-color: rgba(13, 110, 253, 0.1); border: 2px solid rgba(13, 110, 253, 0.2);"></div>
                            <span class="small">Vos cours</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="me-2" style="width: 20px; height: 20px; background-color: rgba(108, 117, 125, 0.1); border: 2px solid rgba(108, 117, 125, 0.2);"></div>
                            <span class="small">Cours réservés</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .min-vh-100 {
        min-height: 100vh;
    }

    .table {
        --bs-table-bg: transparent;
    }

    .course-cell {
        position: relative;
        transition: all 0.3s ease;
        vertical-align: middle !important;
    }

    .course-cell:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1;
    }

    .empty-cell {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
        min-height: 80px;
    }

    .empty-cell:hover {
        background-color: #e9ecef;
    }

    /* Animation pour les cellules de cours */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .course-cell {
        animation: fadeIn 0.3s ease-out;
    }

    /* Style spécifique pour les cellules réservées */
    .course-cell:not(.my-course) .fa-lock {
        color: #6c757d;
        transition: transform 0.3s ease;
    }

    .course-cell:not(.my-course):hover .fa-lock {
        transform: scale(1.1);
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
        
        .course-cell .fa-lock {
            font-size: 1.5rem !important;
        }
    }

    @media (max-width: 576px) {
        .table {
            font-size: 0.8rem;
        }
        
        .course-cell .badge {
            font-size: 0.7rem !important;
        }
        
        .course-cell .fa-lock {
            font-size: 1.2rem !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Highlight les cellules de cours au survol
    const courseCells = document.querySelectorAll('.course-cell');
    courseCells.forEach(cell => {
        const isMyCourse = cell.style.backgroundColor.includes('rgba(13, 110, 253');
        
        cell.addEventListener('mouseenter', function() {
            if (isMyCourse) {
                this.style.backgroundColor = 'rgba(13, 110, 253, 0.15)';
            } else {
                this.style.backgroundColor = 'rgba(108, 117, 125, 0.15)';
            }
            this.style.transform = 'translateY(-2px)';
        });
        
        cell.addEventListener('mouseleave', function() {
            if (isMyCourse) {
                this.style.backgroundColor = 'rgba(13, 110, 253, 0.1)';
            } else {
                this.style.backgroundColor = 'rgba(108, 117, 125, 0.1)';
            }
            this.style.transform = 'translateY(0)';
        });
    });

    // Vérification du rendu
    console.log('Emploi du temps chargé avec succès');
    
    const myCourses = document.querySelectorAll('.course-cell');
    console.log(`Nombre total de cours: ${myCourses.length}`);
});
</script>
@endsection