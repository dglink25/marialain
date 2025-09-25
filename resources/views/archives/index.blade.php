@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Archives des années académiques</h1>

    @if($archives->isEmpty())
        <p class="text-gray-600">Aucune archive disponible.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($archives as $year)
                <a href="{{ route('archives.show', $year->id) }}" 
                   class="block p-4 border rounded-lg shadow hover:shadow-md transition bg-gray-50 hover:bg-gray-100">
                    <h2 class="text-lg font-semibold text-blue-700">{{ $year->name }}</h2>
                    <p class="text-sm text-gray-500">Cliquez pour consulter les détails</p>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
