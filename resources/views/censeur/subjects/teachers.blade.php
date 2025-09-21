@extends('layouts.app')

@section('title', 'Enseignants de '.$subject->name)

@section('content')
<div class="bg-white shadow-md rounded-lg p-6 overflow-x-auto">
    <h1 class="text-2xl font-bold mb-4">Enseignants de la matière : {{ $subject->name }}</h1>

    @if($subject->teachers->count() > 0)
        <table class="min-w-full border border-gray-300 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-4 py-2">N°</th>
                    <th class="border px-4 py-2 text-left">Nom & Prénoms</th>
                    <th class="border px-4 py-2">Sexe</th>
                    <th class="border px-4 py-2 text-left">Email</th>
                    <th class="border px-4 py-2">Téléphone</th>
                    <th class="border px-4 py-2 text-left">Classes</th>
                    <th class="border px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subject->teachers as $teacher)
                    <tr class="hover:bg-gray-50">
                        {{-- Numéro auto avec $loop --}}
                        <td class="border px-4 py-2 text-center">{{ $loop->iteration }}</td>

                        {{-- Nom complet --}}
                        <td class="border px-4 py-2 font-semibold text-gray-700">
                            {{ $teacher->name }}
                        </td>

                        {{-- Sexe --}}
                        <td class="border px-4 py-2 text-gray-600">
                            {{ $teacher->gendre ?? '--' }}
                        </td>

                        {{-- Email --}}
                        <td class="border px-4 py-2 text-gray-600">
                            {{ $teacher->email ?? '--' }}
                        </td>

                        {{-- Téléphone --}}
                        <td class="border px-4 py-2 text-gray-600">
                            {{ $teacher->phone ?? '--' }}
                        </td>

                        {{-- Classes assignées --}}
                        <td class="border px-4 py-2 text-gray-600">
                            @if($teacher->classes && $teacher->classes->count())
                                {{ $teacher->classes->pluck('name')->join(', ') }}
                            @else
                                --
                            @endif
                        </td>

                        {{-- Action --}}
                        <td class="border px-4 py-2 text-center">
                            <a href="{{ route('enseignants.show', $teacher->id) }}" 
                               class="text-blue-600 underline hover:text-blue-800">
                               Voir le profil
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-red-500 mt-4">Aucun enseignant assigné à cette matière.</p>
    @endif
</div>
@endsection
