@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Modifier le créneau - ' . $class->name;
@endphp

<div class="container-fluid p-3 p-md-4 bg-light min-h-screen">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Modifier un créneau
                        </h2>
                        <span class="badge bg-light text-dark">
                            {{ $class->name }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <!-- Messages de statut -->
                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3 fs-5"></i>
                                <div>
                                    <strong class="d-block">Erreurs de validation :</strong>
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

                    <!-- Informations actuelles -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading fw-semibold mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Créneau actuel à modifier
                        </h6>
                        <div class="row small">
                            <div class="col-md-6 mb-2">
                                <strong><i class="fas fa-book me-1"></i> Matière:</strong><br>
                                {{ $timetable->subject->name }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong><i class="fas fa-user-tie me-1"></i> Enseignant:</strong><br>
                                {{ $timetable->teacher->name }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong><i class="fas fa-calendar-day me-1"></i> Jour:</strong><br>
                                {{ $timetable->day }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong><i class="fas fa-clock me-1"></i> Début:</strong><br>
                                {{ date('H:i', strtotime($timetable->start_time)) }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong><i class="fas fa-clock me-1"></i> Fin:</strong><br>
                                {{ date('H:i', strtotime($timetable->end_time)) }}
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de modification -->
                    <form action="{{ route('censeur.timetables.update', [$class->id, $timetable->id]) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Enseignant -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-user-tie me-1"></i>
                                    Enseignant
                                </label>
                                <select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner un enseignant --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" 
                                            {{ old('teacher_id', $timetable->teacher_id) == $teacher->id ? 'selected' : '' }}
                                            data-teacher="{{ $teacher->name }}">
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Matière -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-book me-1"></i>
                                    Matière
                                </label>
                                <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner une matière --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" 
                                            {{ old('subject_id', $timetable->subject_id) == $subject->id ? 'selected' : '' }}
                                            data-subject="{{ $subject->name }}">
                                            {{ $subject->name }} (Coefficient: {{ $subject->coefficient ?? 1 }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Jour -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    Jour
                                </label>
                                <select name="day" class="form-select @error('day') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner un jour --</option>
                                    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                                        <option value="{{ $day }}" 
                                            {{ old('day', $timetable->day) == $day ? 'selected' : '' }}>
                                            {{ $day }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Heure de début -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-clock me-1"></i>
                                    Heure de début
                                </label>
                                <input type="time" name="start_time" 
                                       class="form-control @error('start_time') is-invalid @enderror" 
                                       value="{{ old('start_time', date('H:i', strtotime($timetable->start_time))) }}" 
                                       required
                                       min="07:00" max="18:00">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Heure de fin -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-clock me-1"></i>
                                    Heure de fin
                                </label>
                                <input type="time" name="end_time" 
                                       class="form-control @error('end_time') is-invalid @enderror" 
                                       value="{{ old('end_time', date('H:i', strtotime($timetable->end_time))) }}" 
                                       required
                                       min="07:00" max="18:00">
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Boutons d'action -->
                            <div class="col-12 mt-4 pt-3 border-top">
                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-3">
                                    <div class="d-flex flex-column flex-sm-row gap-2 order-2 order-sm-1">
                                        <button type="submit" 
                                                class="btn btn-primary px-4 py-2 d-flex align-items-center justify-content-center gap-2">
                                            <i class="fas fa-save"></i>
                                            <span>Enregistrer les modifications</span>
                                        </button>
                                        
                                        <button type="button" onclick="resetForm()"
                                                class="btn btn-outline-secondary px-4 py-2 d-flex align-items-center justify-content-center gap-2">
                                            <i class="fas fa-undo"></i>
                                            <span>Réinitialiser</span>
                                        </button>
                                    </div>
                                    
                                    <a href="{{ route('censeur.timetables.index', $class->id) }}" 
                                       class="btn btn-outline-dark px-4 py-2 d-flex align-items-center justify-content-center gap-2 order-1 order-sm-2">
                                        <i class="fas fa-arrow-left"></i>
                                        <span>Retour à l'emploi du temps</span>
                                    </a>
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
    .min-h-screen {
        min-height: 100vh;
    }
    
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }
    
    .form-label {
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-control, .form-select {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 0.65rem 0.75rem;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
        padding: 0.65rem 1.5rem;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .alert-info {
        background-color: #e7f3ff;
        border-color: #a3d0ff;
        color: #0c5460;
        border-radius: 8px;
    }
    
    @media (max-width: 768px) {
        .card-body {
            padding: 1.25rem !important;
        }
        
        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .card-body {
            padding: 1rem !important;
        }
        
        .form-label {
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            font-size: 16px; /* Empêche le zoom sur iOS */
            padding: 0.75rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation côté client
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    }

    // Validation des heures
    const startTime = document.querySelector('input[name="start_time"]');
    const endTime = document.querySelector('input[name="end_time"]');
    
    function validateTimes() {
        if (startTime && endTime && startTime.value && endTime.value) {
            const start = new Date('2000-01-01T' + startTime.value);
            const end = new Date('2000-01-01T' + endTime.value);
            
            if (end <= start) {
                endTime.setCustomValidity('L\'heure de fin doit être après l\'heure de début');
                return false;
            }
            
            // Vérifier que la durée n'excède pas 4 heures
            const duration = (end - start) / (1000 * 60 * 60);
            if (duration > 4) {
                endTime.setCustomValidity('La durée d\'un cours ne peut excéder 4 heures');
                return false;
            }
            
            endTime.setCustomValidity('');
            return true;
        }
        return true;
    }
    
    if (startTime && endTime) {
        startTime.addEventListener('change', validateTimes);
        endTime.addEventListener('change', validateTimes);
    }

    // Empêcher la double soumission
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    if (submitBtn) {
        form.addEventListener('submit', function() {
            if (validateTimes()) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Modification en cours...';
            }
        });
    }

    // Fonction de réinitialisation
    window.resetForm = function() {
        if (confirm('Voulez-vous réinitialiser le formulaire ? Toutes les modifications seront perdues.')) {
            form.reset();
            form.classList.remove('was-validated');
            
            // Réinitialiser les valeurs originales
            startTime.value = '{{ date("H:i", strtotime($timetable->start_time)) }}';
            endTime.value = '{{ date("H:i", strtotime($timetable->end_time)) }}';
            form.querySelector('select[name="day"]').value = '{{ $timetable->day }}';
            form.querySelector('select[name="teacher_id"]').value = '{{ $timetable->teacher_id }}';
            form.querySelector('select[name="subject_id"]').value = '{{ $timetable->subject_id }}';
            
            // Réinitialiser la validation
            if (startTime && endTime) {
                startTime.setCustomValidity('');
                endTime.setCustomValidity('');
            }
        }
    };

    // Détection de changement dans le formulaire
    let initialFormData = {};
    if (form) {
        // Sauvegarder l'état initial
        Array.from(form.elements).forEach(element => {
            if (element.name) {
                initialFormData[element.name] = element.value;
            }
        });
    }

    // Vérification des conflits en temps réel (optionnel)
    const daySelect = document.querySelector('select[name="day"]');
    const teacherSelect = document.querySelector('select[name="teacher_id"]');
    
    async function checkForConflicts() {
        if (!daySelect.value || !startTime.value || !endTime.value) return;
        
        // Ici vous pourriez implémenter une vérification AJAX
        // des conflits d'horaire en temps réel
    }
    
    if (daySelect && startTime && endTime) {
        daySelect.addEventListener('change', checkForConflicts);
        startTime.addEventListener('change', checkForConflicts);
        endTime.addEventListener('change', checkForConflicts);
    }
});
</script>
@endsection