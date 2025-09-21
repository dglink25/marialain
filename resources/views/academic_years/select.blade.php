@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white shadow p-6 rounded">
    <h2 class="text-xl font-bold mb-4">Sélectionner une année académique</h2>

    @if(session('error'))
        <div class="bg-red-100 text-red-600 p-2 mb-3 rounded">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('academic_year.activate') }}">
        @csrf
        <select name="year_id" class="w-full border rounded p-2 mb-4" required>
            @foreach($years as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>

        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Activer
        </button>
    </form>
</div>
@endsection
