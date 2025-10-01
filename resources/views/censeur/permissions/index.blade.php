@extends('layouts.app')

@section('content')
@php
    $pageTitle = "Gestion des autorisations";
@endphp
<div class="container py-6">
    <h1 class="text-2xl font-bold mb-6">Gestion des autorisations - {{ $classe->name }}</h1>
    

    <table class="w-full border-collapse border">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Trimestre</th>
                <th class="border px-4 py-2">État</th>
                <th class="border px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permissions as $perm)
            <tr>
                <td class="border px-4 py-2">Trimestre {{ $perm->trimestre }}</td>
                <td class="border px-4 py-2">
                    @if($perm->is_open)
                        <span class="text-green-600 font-semibold">Ouvert</span>
                    @else
                        <span class="text-red-600 font-semibold">Fermé</span>
                    @endif
                </td>
                <td class="border px-4 py-2">
                    <form action="{{ route('censeur.permissions.toggle', [$classe->id, $perm->trimestre]) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded text-white
                                {{ $perm->is_open ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                            {{ $perm->is_open ? 'Révoquer' : 'Autoriser' }}
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
