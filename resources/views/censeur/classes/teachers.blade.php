@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">
        Enseignants de la classe : {{ $class->name }}
    </h1>

    <div class="bg-white shadow-md rounded-lg p-6 overflow-x-auto">
        @if($teachers->count() > 0)
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">Nom de l'enseignant</th>
                        <th class="border px-4 py-2 text-left">Email</th>
                        <th class="border px-4 py-2 text-left">Matières enseignées</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $data)
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2 font-semibold text-gray-700">
                                {{ $data['teacher']->name }}
                            </td>
                            <td class="border px-4 py-2 text-gray-600">
                                {{ $data['teacher']->email ?? 'Non disponible' }}
                            </td>
                            <td class="border px-4 py-2 text-gray-600">
                                {{ $data['subjects']->join(', ') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500">Aucun enseignant trouvé pour cette classe.</p>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('censeur.classes.index') }}" 
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
           Retour
        </a>
    </div>
</div>
@endsection
