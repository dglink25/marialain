@extends('layouts.app')

@section('content')
<div class="container-fluid p-4 bg-light min-h-screen">

    <h2 class="text-center text-primary mb-4"><strong>Emploi du temps - {{ $class->name }}</strong></h2>

    <!-- Bouton pour afficher le modal -->
    <div class="d-flex justify-content-center mb-4">
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="bi bi-plus-circle me-2"></i>Ajouter un cours
        </button>
    </div>

    <!-- Modal Bootstrap pour l'ajout de cours -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addCourseModalLabel">Ajouter un nouveau cours</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('censeur.timetables.store', $class->id) }}" method="POST" 
                          class="row g-3">
                        @csrf
                        
                        <div class="col-md-4">
                            <label class="form-label">Enseignant</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Matière</label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jour</label>
                            <select name="day" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Heure début</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Heure fin</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-lg me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau d'emploi du temps -->
    <div class="table-responsive border rounded-3 shadow-sm bg-white mb-4">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 90px;">Heure</th>
                    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                        <th class="text-center">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($hours as $hourSlot)
                    @php
                        [$startHour, $endHour] = explode('-', $hourSlot);
                        $startHourFormatted = str_replace('h', ':00', $startHour);
                    @endphp

                    <tr>
                        <td class="text-center fw-bold bg-light">{{ $hourSlot }}</td>

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
                                    $duration = max(1, round((strtotime($course->end_time) - strtotime($course->start_time)) / 3600));
                                @endphp
                                <td class="bg-info bg-opacity-10 text-center" rowspan="{{ $duration }}">
                                    <div class="course-cell">
                                        <div class="fw-bold">{{ $course->subject->name }}</div>
                                        <div class="small">{{ $course->teacher->name }}</div>
                                        <div class="badge bg-info text-dark">
                                            {{ date('H:i', strtotime($course->start_time)) }} - {{ date('H:i', strtotime($course->end_time)) }}
                                        </div>
                                        <a href="{{ route('censeur.timetables.edit', [$class->id, $course->id]) }}"
                                           class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil me-1"></i>Modifier
                                        </a>
                                    </div>
                                </td>
                            @elseif($overlap)
                                {{-- Cellule fusionnée --}}
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Actions (PDF et Retour) -->
    <div class="d-flex justify-content-between">
        <a href="{{ route('censeur.classes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Retour
        </a>
        
        <a href="{{ route('censeur.timetables.download', $class->id) }}" class="btn btn-success">
            <i class="bi bi-download me-2"></i>Télécharger PDF
        </a>
    </div>

</div>

<!-- Inclusion de Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    h2.text-center.text-primary {
    font-size: 2.1rem;  /* augmente la taille */
}

    /* Styles personnalisés pour compléter Bootstrap */
    .table {
        table-layout: fixed;
        width: 100%;
    }

    .table th,
    .table td {
        width: calc(100% / 7); /* 1 colonne heure + 6 jours */
        text-align: center;
        vertical-align: middle;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        padding: 8px;
    }

    .table td {
        height: 80px;
    }

    .bg-info-opacity-10 {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    .min-h-screen {
        min-height: 100vh;
    }

    .course-cell {
        display: flex;
        flex-direction: column;
        justify-content: center;   /* centre vertical */
        align-items: center;       /* centre horizontal */
        height: 100%;
        gap: 4px;
    }
</style>
@endsection
