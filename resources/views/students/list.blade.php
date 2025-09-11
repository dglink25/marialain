@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 bg-white rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Liste alphabétique des étudiants</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.students.export.pdf') }}" 
               class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Télécharger PDF
            </a>
            <a href="{{ route('admin.students.export.excel') }}" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Télécharger Excel
            </a>
        </div>
    </div>

    @foreach($entities as $entity)
        <h2 class="text-xl font-semibold text-blue-700 mt-6">{{ $entity->name }}</h2>

        @foreach($entity->classes as $classe)
            <h3 class="text-lg font-medium mt-4">{{ $classe->name }}</h3>

            <table class="w-full border mt-2 mb-6">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Nom</th>
                        <th class="px-4 py-2 text-left">Prénoms</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classe->students as $student)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $student->last_name }}</td>
                            <td class="px-4 py-2">{{ $student->first_name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-2 text-gray-500">Aucun étudiant</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endforeach
</div>
@endsection
