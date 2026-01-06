@php
    use Illuminate\Support\Str;
@endphp

@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Emploi du temps';
    $joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    
    // Créer un tableau structuré pour faciliter l'affichage
    $emploiDuTemps = [];
    foreach ($joursSemaine as $jour) {
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
        
        <!-- Bouton Ajouter -->
        <button type="button" class="btn btn-primary px-4 py-2 fw-semibold d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="fas fa-plus-circle fs-5"></i>
            <span class="d-none d-md-inline">Ajouter un cours</span>
            <span class="d-inline d-md-none">Ajouter</span>
        </button>
    </div>

    <!-- Messages d'erreur -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                <div>
                    <strong>Erreurs de validation :</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                                    <div class="small text-muted">{{ date('d/m') }}</div>
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
                                    @endphp
                                    
                                    @if($skipRow)
                                        {{-- Cellule déjà remplie par un cours précédent --}}
                                    @elseif($estDebutCours)
                                        <td class="align-middle text-center p-2 course-cell"
                                            rowspan="{{ $rowspan }}"
                                            data-course-id="{{ $cours->id }}"
                                            style="background-color: rgba(13, 110, 253, 0.1); border: 2px solid rgba(13, 110, 253, 0.2);">
                                            <div class="d-flex flex-column justify-content-between h-100">
                                                <div class="mb-2">
                                                    <div class="fw-bold text-truncate mb-1" 
                                                         style="color: #0d6efd; font-size: 0.9rem;"
                                                         title="{{ $cours->subject->name }}">
                                                        {{ Str::limit($cours->subject->name, 20) }}
                                                    </div>
                                                    <div class="text-muted small text-truncate mb-2"
                                                         title="{{ $cours->teacher->name }}">
                                                        <i class="fas fa-user-tie me-1"></i>
                                                        {{ Str::limit($cours->teacher->name, 18) }}
                                                    </div>
                                                    <div class="badge bg-info text-dark mb-2" style="font-size: 0.75rem;">
                                                        {{ date('H:i', strtotime($cours->start_time)) }} - {{ date('H:i', strtotime($cours->end_time)) }}
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-auto">
                                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                        <a href="{{ route('censeur.timetables.edit', [$class->id, $cours->id]) }}"
                                                           class="btn btn-sm btn-outline-warning py-1 px-2 d-flex align-items-center"
                                                           style="font-size: 0.75rem;">
                                                            <i class="fas fa-edit me-1"></i>
                                                            <span class="d-none d-lg-inline">Modifier</span>
                                                        </a>
                                                        <form action="{{ route('censeur.timetables.delete', [$class->id, $cours->id]) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    onclick="return confirm('Voulez-vous vraiment supprimer ce cours ?')"
                                                                    class="btn btn-sm btn-outline-danger py-1 px-2 d-flex align-items-center"
                                                                    style="font-size: 0.75rem;">
                                                                <i class="fas fa-trash me-1"></i>
                                                                <span class="d-none d-lg-inline">Supprimer</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
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

    <!-- Actions -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mt-4">
        <a href="{{ route('censeur.classes.trimestres', $class->id) }}" 
           class="btn btn-outline-secondary px-4 py-2 d-flex align-items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Retour
        </a>
        
        <div class="d-flex gap-2">
            <a href="{{ route('censeur.timetables.download', $class->id) }}" 
               class="btn btn-success px-4 py-2 d-flex align-items-center gap-2">
                <i class="fas fa-file-pdf me-2"></i>
                <span class="d-none d-sm-inline">Télécharger PDF</span>
                <span class="d-inline d-sm-none">PDF</span>
            </a>
            <button type="button" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="fas fa-plus me-2"></i>
                Nouveau cours
            </button>
        </div>
    </div>

    <!-- Modal pour ajouter un cours -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="addCourseModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>
                        Ajouter un nouveau cours
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('censeur.timetables.store', $class->id) }}" method="POST" id="addCourseForm">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Enseignant</label>
                                <select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner un enseignant --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Matière</label>
                                <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner une matière --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }} (Coef: {{ $subject->coefficient }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jour</label>
                                <select name="day" class="form-select @error('day') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner un jour --</option>
                                    @foreach($joursSemaine as $jour)
                                        <option value="{{ $jour }}" {{ old('day') == $jour ? 'selected' : '' }}>
                                            {{ $jour }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Heure de début</label>
                                <input type="time" name="start_time" 
                                       class="form-control @error('start_time') is-invalid @enderror"
                                       value="{{ old('start_time') }}" 
                                       min="07:00" max="18:00" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Heure de fin</label>
                                <input type="time" name="end_time" 
                                       class="form-control @error('end_time') is-invalid @enderror"
                                       value="{{ old('end_time') }}" 
                                       min="07:00" max="18:00" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                        Annuler
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4 d-flex align-items-center gap-2">
                                        <i class="fas fa-save"></i>
                                        Enregistrer le cours
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
    }

    .course-cell:hover {
        background-color: rgba(13, 110, 253, 0.15) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 1;
    }

    .empty-cell {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }

    .empty-cell:hover {
        background-color: #e9ecef;
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
        
        .modal-dialog {
            margin: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .table {
            font-size: 0.8rem;
        }
        
        .course-cell .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .modal-body {
            padding: 1rem !important;
        }
        
        .form-select, .form-control {
            font-size: 16px !important; /* Empêche le zoom sur iOS */
        }
    }

    /* Animation pour les cellules de cours */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .course-cell {
        animation: fadeIn 0.3s ease-out;
    }

    /* Badge d'heure */
    .badge.bg-info {
        background: linear-gradient(135deg, #0dcaf0, #0b9ed8) !important;
        border: none;
    }

    /* Tooltip personnalisé */
    [title] {
        position: relative;
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

    /* Styles pour les boutons d'action */
    .btn-outline-warning {
        border-color: #ffc107;
        color: #ffc107;
    }

    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #000;
    }

    .btn-outline-danger {
        border-color: #dc3545;
        color: #dc3545;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire d'ajout de cours
    const addCourseForm = document.getElementById('addCourseForm');
    if (addCourseForm) {
        addCourseForm.addEventListener('submit', function(e) {
            const startTime = this.querySelector('input[name="start_time"]').value;
            const endTime = this.querySelector('input[name="end_time"]').value;
            
            if (startTime && endTime) {
                const start = new Date('2000-01-01T' + startTime);
                const end = new Date('2000-01-01T' + endTime);
                
                if (end <= start) {
                    e.preventDefault();
                    alert('L\'heure de fin doit être après l\'heure de début.');
                    return false;
                }
                
                // Vérifier que la durée n'excède pas 4 heures
                const duration = (end - start) / (1000 * 60 * 60);
                if (duration > 4) {
                    e.preventDefault();
                    alert('La durée d\'un cours ne peut excéder 4 heures.');
                    return false;
                }
            }
        });
    }

    // Gestion des suppressions
    const deleteButtons = document.querySelectorAll('form button[type="submit"]');
    deleteButtons.forEach(button => {
        if (button.closest('form').action.includes('delete')) {
            button.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir supprimer ce cours ? Cette action est irréversible.')) {
                    e.preventDefault();
                } else {
                    // Afficher un indicateur de chargement
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Suppression...';
                    this.disabled = true;
                    
                    // Restaurer après 3 secondes si la suppression échoue
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                    }, 3000);
                }
            });
        }
    });

    // Ouvrir le modal s'il y a des erreurs
    @if($errors->any())
        const modal = new bootstrap.Modal(document.getElementById('addCourseModal'));
        modal.show();
    @endif

    // Gestion du responsive
    function handleResponsive() {
        const table = document.querySelector('.table-responsive');
        const screenWidth = window.innerWidth;
        
        if (screenWidth < 768) {
            // Sur mobile, ajuster la taille des cellules
            const courseCells = document.querySelectorAll('.course-cell');
            courseCells.forEach(cell => {
                const buttons = cell.querySelectorAll('.btn');
                buttons.forEach(btn => {
                    btn.innerHTML = btn.innerHTML.replace('Modifier', '<i class="fas fa-edit"></i>');
                    btn.innerHTML = btn.innerHTML.replace('Supprimer', '<i class="fas fa-trash"></i>');
                });
            });
        }
    }

    window.addEventListener('resize', handleResponsive);
    handleResponsive();

    // Highlight les cellules de cours au survol
    const courseCells = document.querySelectorAll('.course-cell');
    courseCells.forEach(cell => {
        cell.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.01)';
            this.style.boxShadow = '0 6px 12px rgba(0,0,0,0.15)';
        });
        
        cell.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        });
    });

    // Empêcher la double soumission
    let formSubmitting = false;
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            if (formSubmitting) {
                event.preventDefault();
                return false;
            }
            formSubmitting = true;
            
            // Désactiver le bouton de soumission
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Traitement...';
            }
            
            return true;
        });
    });
});
</script>
@endsection