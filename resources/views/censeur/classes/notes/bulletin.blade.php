@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- En-tête du bulletin --}}
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div class="text-center border-b pb-4 mb-4">
            <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">Bulletin du Trimestre {{ $trimestre }}</h1>
            <p class="text-gray-600">Année académique : <span class="font-semibold">{{ $classe->academicYear->name ?? '' }}</span></p>
        </div>

        {{-- Informations élève --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-gray-700">
            <p><strong>Nom :</strong> {{ strtoupper($student->last_name) }}</p>
            <p><strong>Prénom :</strong> {{ ucfirst($student->first_name) }}</p>
            <p><strong>Sexe :</strong> {{ strtoupper($student->gender ?? '-') }}</p>
            <p><strong>Matricule :</strong> {{ $student->num_educ ?? '—' }}</p>
            <p><strong>Classe :</strong> {{ $classe->name }}</p>
            <p><strong>Effectif :</strong> {{ $effectif->students->count() }} élèves</p>
        </div>
    </div>

    {{-- Tableau des notes --}}
    <div class="bg-white rounded-2xl shadow-lg overflow-x-auto">
        <table class="min-w-full border-collapse text-sm text-center">
            <thead class="bg-gradient-to-r from-blue-700 to-indigo-600 text-white">
                <tr>
                    <th class="py-3 px-2 border">Matière</th>
                    <th class="py-3 px-2 border">Coef</th>
                    <th colspan="5" class="py-3 px-2 border">Interrogations</th>
                    <th colspan="2" class="py-3 px-2 border">Devoirs</th>
                    <th class="py-3 px-2 border">Moyenne</th>
                    <th class="py-3 px-2 border">Moy x Coef</th>
                    <th class="py-3 px-2 border">Appréciation</th>
                </tr>
                <tr class="bg-blue-50 text-gray-800 text-xs">
                    <th colspan="2"></th>
                    <th class="border py-1">I1</th>
                    <th class="border py-1">I2</th>
                    <th class="border py-1">I3</th>
                    <th class="border py-1">I4</th>
                    <th class="border py-1">I5</th>
                    <th class="border py-1">D1</th>
                    <th class="border py-1">D2</th>
                    <th colspan="3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bulletin as $ligne)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="font-semibold py-2 border px-2 text-left">{{ $ligne['subject'] }}</td>
                        <td class="border py-2">{{ $ligne['coef'] }}</td>

                        {{-- Interrogations I1 à I5 --}}
                        @for ($i = 1; $i <= 5; $i++)
                            <td class="border py-2">{{ $ligne['interros'][$i] ?? '-' }}</td>
                        @endfor

                        {{-- Devoirs D1 et D2 --}}
                        <td class="border py-2">{{ $ligne['devoirs'][1] ?? '-' }}</td>
                        <td class="border py-2">{{ $ligne['devoirs'][2] ?? '-' }}</td>

                        <td class="border py-2 font-semibold text-blue-700">{{ $ligne['moyenne'] ?? '-' }}</td>
                        <td class="border py-2">{{ $ligne['moyCoeff'] ?? '-' }}</td>
                        <td class="border py-2 italic">{{ $ligne['appreciation'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>

            {{-- Pied de tableau --}}
            <tfoot class="bg-gray-100 font-semibold">
                <tr>
                    <td colspan="9" class="text-right py-2 pr-4">Conduite :</td>
                    <td colspan="3">{{ $conduiteFinale ?? '-' }}</td>
                </tr>
                <tr>
                    <td colspan="9" class="text-right py-2 pr-4">Moyenne générale :</td>
                    <td colspan="3" class="text-blue-700">{{ $moyenneGenerale ?? '-' }}</td>
                </tr>
                <tr>
                    <td colspan="9" class="text-right py-2 pr-4">Appréciation générale :</td>
                    <td colspan="3">{{ $appreciationGenerale ?? '-' }}</td>
                </tr>
                <tr>
                    <td colspan="9" class="text-right py-2 pr-4">Rang général :</td>
                    <td colspan="3">{{ $rang ?? '-' }} / {{ $effectif->students->count() }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- 🖨️ Boutons d’action --}}
    <div class="flex flex-wrap justify-end gap-3 mt-6">
        <a href="{{ route('censeur.classes.notes.bulletin.pdf', [$classe->id, $student->id, $trimestre]) }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-md flex items-center gap-2 transition">
            <i class="fas fa-file-pdf"></i> Télécharger PDF
        </a>
    </div>
</div>
@endsection
