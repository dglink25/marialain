@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6 px-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Élèves de la classe</h1>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded-lg shadow-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-700">Nom</th>
                    <th class="px-4 py-3 text-left text-gray-700">Prénom</th>
                    <th class="px-4 py-3 text-left text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($students as $student)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $student->last_name }}</td>
                    <td class="px-4 py-3">{{ $student->first_name }}</td>
                    <td class="px-4 py-3 flex flex-col md:flex-row gap-2 md:gap-3">
                        <!-- Formulaire punition -->
                        <form method="POST" action="{{ route('surveillant.students.punish', $student->id) }}" class="flex flex-col sm:flex-row gap-2">
                            @csrf
                            <input type="text" name="reason" placeholder="Motif" class="border rounded p-2 flex-1" required>
                            <input type="number" name="hours" placeholder="Heures" min="1" class="border rounded p-2 w-24" required>
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded transition">Punir</button>
                        </form>

                        <!-- Historique -->
                        <a href="{{ route('surveillant.students.history', $student->id) }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition text-center">
                           Historique
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ url()->previous() }}" 
           class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded transition">
           Retour
        </a>
    </div>
</div>
@endsection
