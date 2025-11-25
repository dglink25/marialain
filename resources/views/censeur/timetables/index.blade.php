@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Emploi du temps';
@endphp

<div class="container-fluid p-3 p-md-4 bg-light min-h-screen">

    <!-- En-tête responsive -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-3">
        <h2 class="text-primary mb-0 fs-2 fs-md-1">
            <strong>Emploi du temps - {{ $class->name }}</strong>
        </h2>

         @if ($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
            </div>
        @endif
        
        <!-- Bouton pour afficher le modal -->
        <div class="d-flex justify-content-end w-100 w-md-auto">
            <button type="button" class="btn btn-primary border-blue-600 border-2 px-3 px-md-4 py-2 d-flex align-items-center gap-2" 
                    data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="fas fa-plus-circle"></i>
                <span class="d-none d-sm-inline">Ajouter un cours</span>
                <span class="d-inline d-sm-none">Ajouter</span>
            </button>
        </div>
    </div>

    <!-- Modal Bootstrap pour l'ajout de cours -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-5" id="addCourseModalLabel">Ajouter un nouveau cours</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('censeur.timetables.store', $class->id) }}" method="POST" class="row g-3">
                        @csrf
                        
                        <div class="col-12">
                            <label class="form-label"><strong>Enseignant:</strong></label>
                            <select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label"><strong>Matière:</strong></label>
                            <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label"><strong>Jour:</strong></label>
                            <select name="day" class="form-select @error('day') is-invalid @enderror" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                                    <option value="{{ $d }}" {{ old('day') == $d ? 'selected' : '' }}>
                                        {{ $d }}
                                    </option>
                                @endforeach
                            </select>
                            @error('day')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label"><strong>Heure début:</strong></label>
                            <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label"><strong>Heure fin:</strong></label>
                            <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-success w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                <i class="fas fa-check"></i>
                                Ajouter le cours
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages d'alerte -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Erreur:</strong> Veuillez corriger les erreurs ci-dessous.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tableau d'emploi du temps -->
    <div class="table-responsive border rounded-3 shadow-sm bg-white mb-4 timetable-container">
        <table class="table table-bordered table-hover mb-0 timetable-table">
            <thead class="table-light">
                <tr>
                    <th class="text-center time-column">Heure</th>
                    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                        <th class="text-center day-column">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($hours as $hourSlot)
                    @php
                        [$startHour, $endHour] = explode('-', $hourSlot);
                        $startHourFormatted = str_replace('h', ':00', $startHour);
                        $currentHourProcessed = false;
                    @endphp

                    <tr class="timetable-row">
                        <td class="text-center fw-bold bg-light time-cell">
                            <span class="d-none d-md-inline">{{ $hourSlot }}</span>
                            <span class="d-inline d-md-none">{{ str_replace('h', 'h-', $startHour) }}{{ $endHour }}</span>
                        </td>

                        @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                            @php
                                // Vérifier si cette cellule a déjà été remplie par un rowspan
                                if ($currentHourProcessed) {
                                    $currentHourProcessed = false;
                                    continue;
                                }

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
                                    $duration = max(1, round((strtotime($course->end_time) - strtotime($course->start_time)) / 3600));
                                    if ($duration > 1) {
                                        $currentHourProcessed = true;
                                    }
                                @endphp
                                <td class="bg-info bg-opacity-10 text-center course-cell" 
                                    rowspan="{{ $duration }}"
                                    data-course-id="{{ $course->id }}"
                                    data-duration="{{ $duration }}">
                                    <div class="course-content">
                                        <div class="course-subject fw-bold text-truncate" title="{{ $course->subject->name }}">
                                            {{ $course->subject->name }}
                                        </div>
                                        <div class="course-teacher small text-muted text-truncate" title="{{ $course->teacher->name }}">
                                            {{ $course->teacher->name }}
                                        </div>
                                        <div class="course-time badge bg-info text-dark mt-1">
                                            <small>{{ date('H:i', strtotime($course->start_time)) }}-{{ date('H:i', strtotime($course->end_time)) }}</small>
                                        </div>
                                        <div class="course-actions mt-2 d-flex flex-wrap justify-content-center gap-1">
                                            <a href="{{ route('censeur.timetables.edit', [$class->id, $course->id]) }}"
                                               class="btn btn-warning btn-sm px-2 py-1 edit-btn">
                                                <i class="fas fa-edit me-1"></i>
                                                <span class="d-none d-sm-inline">Modifier</span>
                                                <span class="d-inline d-sm-none">Modif</span>
                                            </a>

                                            <form action="{{ route('censeur.timetables.delete', [$class->id, $course->id]) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Voulez-vous vraiment supprimer ce cours ?')"
                                                        class="btn btn-danger btn-sm px-2 py-1 delete-btn">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="d-none d-sm-inline">Supprimer</span>
                                                    <span class="d-inline d-sm-none">Supp</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            @elseif($overlap)
                                {{-- Cellule déjà remplie par un cours précédent --}}
                            @else
                                <td class="empty-cell"></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Actions (PDF et Retour) -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3">
        <button onclick="window.history.back()" 
            class="btn btn-outline-secondary px-4 py-2 d-flex align-items-center justify-content-center gap-2 order-2 order-sm-1">
            <i class="fas fa-arrow-left"></i>
            Retour
        </button>
        
        <div class="d-flex gap-2 order-1 order-sm-2">
            <a href="{{ route('censeur.timetables.download', $class->id) }}" 
               class="btn btn-success px-4 py-2 d-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-download"></i>
                <span class="d-none d-sm-inline">Télécharger PDF</span>
                <span class="d-inline d-sm-none">PDF</span>
            </a>
        </div>
    </div>

</div>

<style>
    /* Styles améliorés pour le responsive */
    .min-h-screen {
        min-height: 100vh;
    }

    /* Tableau responsive */
    .timetable-container {
        overflow-x: auto;
        border-radius: 0.5rem;
    }

    .timetable-table {
        table-layout: fixed;
        width: 100%;
        font-size: 0.875rem;
        min-width: 600px;
    }

    @media (min-width: 768px) {
        .timetable-table {
            font-size: 1rem;
            min-width: auto;
        }
    }

    .time-column {
        width: 80px;
        min-width: 80px;
    }

    .day-column {
        min-width: 120px;
    }

    @media (max-width: 767.98px) {
        .time-column {
            width: 60px;
            min-width: 60px;
        }
        
        .day-column {
            min-width: 100px;
            font-size: 0.8rem;
        }
        
        .timetable-table th,
        .timetable-table td {
            padding: 4px 2px;
        }
    }

    @media (max-width: 575.98px) {
        .time-column {
            width: 50px;
            min-width: 50px;
        }
        
        .day-column {
            min-width: 85px;
            font-size: 0.75rem;
        }
        
        .timetable-table {
            font-size: 0.75rem;
        }
    }

    /* Cellules de cours */
    .course-cell {
        position: relative;
        height: 100%;
        min-height: 90px;
        transition: all 0.2s ease;
        border: 1px solid #dee2e6;
    }

    .course-cell:hover {
        background-color: rgba(13, 110, 253, 0.15) !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .course-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
        padding: 4px;
        gap: 2px;
    }

    .course-subject {
        font-size: 0.875rem;
        line-height: 1.2;
        max-width: 100%;
    }

    .course-teacher {
        font-size: 0.75rem;
        line-height: 1.2;
        max-width: 100%;
    }

    .course-time {
        font-size: 0.7rem;
        padding: 2px 4px;
    }

    .course-actions {
        width: 100%;
    }

    .course-actions .btn {
        font-size: 0.75rem;
        padding: 2px 6px;
        white-space: nowrap;
    }

    .edit-btn {
        background: #ffc107;
        border: none;
        color: #000;
    }

    .edit-btn:hover {
        background: #e0a800;
        color: #000;
    }

    .delete-btn {
        background: #dc3545;
        border: none;
        color: #fff;
    }

    .delete-btn:hover {
        background: #c82333;
        color: #fff;
    }

    @media (min-width: 768px) {
        .course-cell {
            min-height: 110px;
        }
        
        .course-content {
            padding: 8px;
            gap: 4px;
        }
        
        .course-subject {
            font-size: 1rem;
        }
        
        .course-teacher {
            font-size: 0.875rem;
        }
        
        .course-time {
            font-size: 0.8rem;
        }
        
        .course-actions .btn {
            font-size: 0.875rem;
            padding: 4px 8px;
        }
    }

    /* Cellules vides */
    .empty-cell {
        background-color: #f8f9fa;
        min-height: 90px;
    }

    @media (min-width: 768px) {
        .empty-cell {
            min-height: 110px;
        }
    }

    /* En-tête responsive */
    h2.text-primary {
        font-size: clamp(1.5rem, 4vw, 2.1rem);
        line-height: 1.2;
    }

    /* Boutons responsive */
    .btn {
        white-space: nowrap;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    /* Modal responsive */
    @media (max-width: 575.98px) {
        .modal-dialog {
            margin: 10px;
        }
        
        .modal-content {
            border-radius: 8px;
        }
        
        .modal-body {
            padding: 15px;
        }
        
        .form-select, .form-control {
            font-size: 16px;
        }
    }

    /* Amélioration de l'accessibilité */
    @media (prefers-reduced-motion: reduce) {
        .course-cell, .btn {
            transition: none;
        }
    }

    /* Styles pour les très petits écrans */
    @media (max-width: 400px) {
        .timetable-table {
            min-width: 550px;
        }
        
        .course-actions {
            flex-direction: column;
        }
        
        .course-actions .btn {
            width: 100%;
            margin-bottom: 2px;
        }
    }

    /* Loading state */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des formulaires de suppression
    const deleteForms = document.querySelectorAll('.delete-form');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce cours ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
    });

    // Amélioration de l'accessibilité du tableau
    const courseCells = document.querySelectorAll('.course-cell');
    
    courseCells.forEach(cell => {
        cell.setAttribute('tabindex', '0');
        cell.addEventListener('focus', function() {
            this.style.outline = '2px solid #0d6efd';
            this.style.outlineOffset = '-2px';
        });
        
        cell.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });

    // Gestion du redimensionnement
    function handleResize() {
        const table = document.querySelector('.timetable-table');
        if (window.innerWidth < 576) {
            table.classList.add('small-screen');
        } else {
            table.classList.remove('small-screen');
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize();

    // Tooltips pour les textes tronqués
    const truncatedElements = document.querySelectorAll('.text-truncate');
    truncatedElements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            if (this.offsetWidth < this.scrollWidth) {
                this.setAttribute('title', this.textContent);
            } else {
                this.removeAttribute('title');
            }
        });
    });

    // Gestion des erreurs de formulaire dans le modal
    const addCourseModal = document.getElementById('addCourseModal');
    if (addCourseModal && @json($errors->any())) {
        const modal = new bootstrap.Modal(addCourseModal);
        modal.show();
    }

    // Empêcher la double soumission des formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            }
        });
    });
});
</script>
@endsection