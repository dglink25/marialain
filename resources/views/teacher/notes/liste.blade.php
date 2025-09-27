@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">
        Cahier de Notes - {{ $classe->name }} ({{ $activeYear->name }})
    </h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">Nom</th>
                    <th class="px-4 py-2">Prénoms</th>
                    <th class="px-4 py-2">Sexe</th>
                    <th class="px-4 py-2">Notes déjà saisies</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classe->students as $student)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $student->last_name }}</td>
                    <td class="px-4 py-2">{{ $student->first_name }}</td>
                    <td class="px-4 py-2">{{ $student->gender }}</td>
                    <td class="px-4 py-2 text-center">
                        <!-- Affichage résumé -->
                        {{ $student->grades->count() ?? 0 }} notes
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
