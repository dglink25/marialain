@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">

    <h1 class="text-2xl font-bold mb-6 text-indigo-700">
        Liste des enseignants – Année académique : 
        <span class="text-gray-700">{{ $academicYear->name ?? '---' }}</span>
    </h1>

    @if($teachers->isEmpty())
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded">
            Aucun enseignant trouvé pour cette année académique.
        </div>
    @else
        <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-indigo-600 text-white">
                        <th class="px-4 py-2 text-left">N°</th>
                        <th class="px-4 py-2 text-left">Nom complet</th>
                        <th class="px-4 py-2 text-left">Contacts</th>
                        <th class="px-4 py-2 text-left">Sexe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teachers as $index => $teacher)
                        <tr class="border-b hover:bg-gray-100">
                            <td class="px-4 py-2">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 font-semibold text-gray-800">
                                {{ $teacher->name ?? ($teacher->name . ' ' . $teacher->name) }}
                            </td>
                            <td class="px-4 py-2">{{ $teacher->email }} <br> {{ $teacher->phone }} </td>
                            <td class="px-4 py-2 capitalize">{{ $teacher->gender ?? '---' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
