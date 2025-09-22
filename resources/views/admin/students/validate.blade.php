{{-- resources/views/admin/students/validate.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white shadow p-6 rounded">
    <h1 class="text-xl font-bold mb-4">Validation inscription : {{ $student->first_name }} {{ $student->last_name }}</h1>

    <form action="{{ route('students.validate', $student->id) }}" method="POST">
        @csrf
        <label class="block mb-2">Montant payé à l'inscription</label>
        <input type="number" step="0.01" name="registration_fee" class="border rounded p-2 w-full mb-4" required>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Valider</button>
    </form>
</div>
@endsection
