@extends('layouts.app')

@section('content')
@if(isset($error))
        <div class="p-4 mb-6 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg">
            {{ $error }}
        </div>
    @endif
    <div class="container-fluid py-4">

        <div class="flex justify-end mb-3 space-x-2">
            <a href="{{ route('censeur.classes.notes.pdf', [$classe->id, $trimestre]) }}"
            class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 transition">
                📄 Télécharger PDF
            </a>

            <a href="{{ route('censeur.classes.notes.excel', [$classe->id, $trimestre]) }}"
            class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
                📊 Télécharger Excel
            </a>
        </div>


    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white text-center">
            <h4>
                Récapitulatif du {{ $trimestre }}ᵉ trimestre - {{ $classe->name }}
                <br>
                <small>{{ $activeYear->name }}</small>
            </h4>
        </div>

        <div class="card-body table-responsive">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2">N°</th>
                        <th rowspan="2">Numéro Educ Master</th>
                        <th rowspan="2">Nom</th>
                        <th rowspan="2">Prénoms</th>
                        <th rowspan="2">Sexe</th>

                        @foreach($subjects as $subject)
                            <th colspan="6">{{ $subject->name }}</th>
                        @endforeach

                        <th rowspan="2">Conduite</th>
                        <th rowspan="2">Moy. Gén.</th>
                        <th rowspan="2">Rang</th>
                        <th rowspan="2">Action</th>
                    </tr>
                    <tr>
                        @foreach($subjects as $subject)
                            <th>Int.</th>
                            <th>D1</th>
                            <th>D2</th>
                            <th>Moy./20</th>
                            <th>Coeff.</th>
                            <th>Moy.Coeff.</th>
                        @endforeach
                    </tr>
                    
                </thead>

                <tbody>
                    @foreach($classe->students as $index => $student)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $student->num_educ ?? '-' }}</td>
                            <td>{{ strtoupper($student->last_name) }}</td>
                            <td>{{ ucfirst($student->first_name) }}</td>
                            <td>{{ strtoupper($student->gender ?? '-') }}</td>

                            {{-- Notes par matière --}}
                            @foreach($subjects as $subject)
                                @php
                                    $s = $gradesData[$student->id][$subject->id] ?? [];
                                @endphp
                                <td>{{ $s['moyenneInterro'] ?? '-' }}</td>
                                <td>{{ $s['devoirs'][1] ?? '-' }}</td>
                                <td>{{ $s['devoirs'][2] ?? '-' }}</td>
                                <td>{{ $s['moyenne'] ?? '-' }}</td>
                                <td>{{ $s['coef'] ?? 1 }}</td>
                                <td>{{ $s['moyenneMat'] ?? '-' }}</td>
                            @endforeach

                            <td>{{ $conductData[$student->id] ?? '-' }}</td>
                            <td>{{ $gradesData[$student->id]['moyenne_generale'] ?? '-' }}</td>
                            <td>{{ $gradesData[$student->id]['rang_general'] ?? '-' }}</td>
                            <td>
                                <a href="{{ route('teacher.classes.students.bulletin', [$classe->id, $student->id, $trimestre]) }}"
                                class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                    Afficher bulletin
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
</div>

<style>
    table th, table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    @media (max-width: 768px) {
        table {
            font-size: 0.8rem;
        }
    }
</style>
@endsection