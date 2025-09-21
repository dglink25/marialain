@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Archives des années académiques</h1>

    @if($archives->isEmpty())
        <p class="text-gray-600">Aucune archive disponible.</p>
    @else
        <ul class="space-y-2">
            @foreach($archives as $year)
                <li>
                    <a href="{{ route('archives.show', $year->id) }}" 
                       class="text-blue-600 hover:underline">
                       {{ $year->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
