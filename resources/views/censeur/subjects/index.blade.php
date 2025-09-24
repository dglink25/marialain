@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Matières</h1>
    @if(isset($error))
        <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            {{ $error }}
        </div>
    @endif


    <form method="POST" action="{{ route('censeur.subjects.store') }}" class="flex space-x-2 mb-6">
        @csrf
        <input type="text" name="name" placeholder="Nom de la matière" class="border p-2 rounded">
        <button class="px-4 py-2 bg-green-600 text-white rounded">Ajouter</button>
    </form>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($subjects as $subject)

        <div class="flex flex-col items-center bg-white shadow rounded-lg p-6">
            <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                <span class="text-gray-700 font-semibold">{{ $loop->iteration }}</span>
            </div>
            <a href="{{ route('subjects.teachers', $subject->id) }}" 
            class="bg-green-600 text-white px-4 py-2 rounded text-center hover:bg-green-700 transition">
                {{ $subject->name }}
            </a>
        </div>

        @endforeach
    </div>
@endsection
