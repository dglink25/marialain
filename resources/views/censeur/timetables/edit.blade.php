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
                    <h2 class="h4 mb-0 text-center">
                        <i class="fas fa-edit me-2"></i>
                        Modifier un créneau - {{ $class->name }}
                    </h2>
                </div>
                
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Erreur:</strong> Veuillez corriger les erreurs ci-dessous.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('censeur.timetables.update', [$class->id, $timetable->id]) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Enseignant:</label>
                                <select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner un enseignant --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}" 
                                            {{ old('teacher_id', $timetable->teacher_id) == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Matière:</label>
                                <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner une matière --</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" 
                                            {{ old('subject_id', $timetable->subject_id) == $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Jour:</label>
                                <select name="day" class="form-select @error('day') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner un jour --</option>
                                    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                                        <option value="{{ $d }}" 
                                            {{ old('day', $timetable->day) == $d ? 'selected' : '' }}>
                                            {{ $d }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Heure de début:</label>
                                <input type="time" name="start_time" 
                                       class="form-control @error('start_time') is-invalid @enderror" 
                                       value="{{ old('start_time', date('H:i', strtotime($timetable->start_time))) }}" 
                                       required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Heure de fin:</label>
                                <input type="time" name="end_time" 
                                       class="form-control @error('end_time') is-invalid @enderror" 
                                       value="{{ old('end_time', date('H:i', strtotime($timetable->end_time))) }}" 
                                       required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Informations actuelles -->
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading fw-semibold">Informations actuelles:</h6>
                                    <div class="small">
                                        <strong>Matière:</strong> {{ $timetable->subject->name }}<br>
                                        <strong>Enseignant:</strong> {{ $timetable->teacher->name }}<br>
                                        <strong>Jour:</strong> {{ $timetable->day }}<br>
                                        <strong>Horaire:</strong> {{ date('H:i', strtotime($timetable->start_time)) }} - {{ date('H:i', strtotime($timetable->end_time)) }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 pt-3">
                                    <button type="submit" 
                                            class="btn btn-primary px-4 py-2 d-flex align-items-center justify-content-center gap-2 order-2 order-sm-1">
                                        <i class="fas fa-save"></i>
                                        Enregistrer les modifications
                                    </button>
                                    
                                    <a href="{{ route('censeur.timetables.index', $class->id) }}" 
                                       class="btn btn-outline-secondary px-4 py-2 d-flex align-items-center justify-content-center gap-2 order-1 order-sm-2">
                                        <i class="fas fa-arrow-left"></i>
                                        Retour à l'emploi du temps
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
        border-radius: 0.5rem;
    }
    
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    
    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .btn {
        transition: all 0.2s ease;
        font-weight: 500;
    }
    
    @media (max-width: 576px) {
        .card-body {
            padding: 1rem !important;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation côté client
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });

    // Empêcher la double soumission
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Modification en cours...';
        });
    }

    // Validation des heures
    const startTime = document.querySelector('input[name="start_time"]');
    const endTime = document.querySelector('input[name="end_time"]');
    
    function validateTimes() {
        if (startTime.value && endTime.value) {
            if (startTime.value >= endTime.value) {
                endTime.setCustomValidity('L\'heure de fin doit être après l\'heure de début');
            } else {
                endTime.setCustomValidity('');
            }
        }
    }
    
    if (startTime && endTime) {
        startTime.addEventListener('change', validateTimes);
        endTime.addEventListener('change', validateTimes);
    }
});
</script>
@endsection