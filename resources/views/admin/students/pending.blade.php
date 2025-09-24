@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Élèves en attente de validation</h1>

    @if(!$activeYear)
        <div class="alert alert-warning">
            {{ $message }}
        </div>
    @else
            <div class="mb-4 flex justify-end">
                <a href="{{ route('admin.students.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Inscrire un élève</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            


            <table class="min-w-full bg-white border rounded-lg">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm font-semibold">
                        <th class="border px-4 py-2">N°</th>
                        <th class="border px-4 py-2" rowspan="2">N° Éduc Master</th>
                        <th class="border px-4 py-2">Nom</th>
                        <th class="border px-4 py-2">Prénoms</th>
                        <th class="border px-4 py-2" rowspan="2">Sexe</th>
                        <th class="border px-4 py-2" rowspan="2">Niveau</th>
                        <th class="border px-4 py-2" rowspan="2">Classe</th>
                        <th class="border px-4 py-2" rowspan="2">Date de naissance</th>
                        <th class="border px-4 py-2" rowspan="2">Parents/Tuteurs</th>
                        <th class="border px-4 py-2" rowspan="2">Date d'inscription</th>
                        <th class="border px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="border px-4 py-2">{{ $student->num_educ ?? '- -' }}</td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="text-blue-600 hover:underline">
                                    {{ $student->last_name }}
                                </a>
                            </td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="text-blue-600 hover:underline">
                                    {{ $student->first_name }}
                                </a>
                            </td>
                            <td class="border px-4 py-2">{{ $student->gender ?? '- -' }}</td>
                            <td class="border px-4 py-2">{{ $student->entity->name ?? '-' }}</td>
                            <td class="border px-4 py-2">{{ $student->classe->name ?? '' }}</td>
                            <td class="border px-4 py-2">{{ $student->birth_date }}</td>
                            <td class="border px-4 py-2">{{ $student->parent_full_name ?? ' - - ' }} <br> {{ $student->parent_phone ?? ' - - ' }}</td>
                            <td class="border px-4 py-2">{{ $student->created_at }}</td>
                            <td class="border px-4 py-2">
                                <form method="POST" action="{{ route('admin.students.validate', $student) }}" class="flex space-x-2">
                                    @csrf
                                    <input type="number" name="amount_paid" class="border rounded p-1 w-32" placeholder="Montant payé" required>
                                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Valider</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-4">Aucun élève en attente</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


    @endif

    
@endsection
