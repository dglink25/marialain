@extends('layouts.app')

@section('content')
<div class="container">
    <div class="bg-white shadow-lg rounded-xl p-8 border border-gray-200">
        <h1 class="text-2xl font-bold mb-6">Annéee académique : {{ $annee_academique -> name}} </h1>
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-1xl font-bold text-gray-800">Liste des élèves - Primaire</h1>
             
            <a href="{{ route('primaire.ecoliers.liste.pdf') }}" 
               class="bg-green-600 text-white px-5 py-2 rounded-lg shadow hover:bg-green-700 transition">
               Télécharger la liste
            </a>
           
        </div>
        
<div>
    <!-- Options de tri -->
        
        <div class="flex flex-wrap items-center gap-4 mb-4 text-gray-700">
           <span>Trier par :</span>
            <form action="" method="GET" class="flex items-center">
                @csrf
                <select name="sort" class="border border-gray-300 rounded-lg ">
                    <option value=""></option>
                    <option value="classe"> <a href="{{ route('primaire.ecoliers.liste', ['sort' => 'classe']) }}" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">Classe</a></option>
                    <option value="last_name"> <a href="{{ route('primaire.ecoliers.liste', ['sort' => 'last_name']) }}" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">Nom</a></option>
                    <option value="first_name"> <a href="{{ route('primaire.ecoliers.liste', ['sort' => 'first_name']) }}" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">Prénom</a></option>
                    <option value="age"> <a href="{{ route('primaire.ecoliers.liste', ['sort' => 'age']) }}" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">Âge</a></option>
                </select>
                <button type="submit" class="ml-2 bg-blue-600 text-white px-4 rounded shadow hover:bg-blue-700 transition">Trier</button>
            </form>
            
            
         </div>
         
       <div class="flex flex-wrap items-center gap- mb-4 text-gray-700">
           <span>Filtrer par :</span>
            <form action="" method="GET" class="flex items-center">
                @csrf
                <select name="classe" class="border border-gray-300 rounded ">
                    <option value=""> --Classe-- </option>
                    @foreach ($classes as $classe)
                    <option value="{{ $classe -> name }}" {{ request('classe') == $classe-> name ? 'selected' : ''}} > {{ $classe -> name }} </option>
                  
                    @endforeach
                </select>
                <select name="gender" id="">
                    <option value=""> --Sexe--</option>
                    <option value="M"  {{request('gender') == 'M' ? 'selected' : ''}}> Masculin</option>
                    <option value="F" {{request('gender') == 'F' ? 'selected' : ''}} >Féminin</option>
                </select>
                <button type="submit" class="ml-2 bg-blue-600 text-white px-4 rounded shadow hover:bg-blue-700 transition">filtrer</button>
                </form>
            
           
         </div>
         <div>
            <form action="" method="GET" class="flex items-center">
                @csrf
                <input name="search" class="border border-gray-300 rounded " type="text" value=" {{ request ('search') }} " placeholder="Rechercher un élève" >
                <button type="submit" class="ml-2 bg-blue-600 text-white px-4 rounded shadow hover:bg-blue-700 transition"> Rechercher</button>
            
            </form>
         </div>
         <a href="{{ route('primaire.ecoliers.liste') }}" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 mt-5 mb-5"> x Voir tout</a>
</div>
        

        <!-- Table des élèves -->
        @if($students->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-center">N°</th>
                        <th class="px-4 py-3 text-left">N° Educ master</th>
                        <th class="px-4 py-3 text-left">Nom</th>
                        <th class="px-4 py-3 text-left">Prénom</th>
                        <th class="px-4 py-3 text-left">Classe</th>
                        <th class="px-4 py-3 text-center">Sexe</th>
                        <th class="px-4 py-3 text-center">Date de naissance</th>
                        <th class="px-4 py-3 text-left">Lieu de naissance</th>
                        <th class="px-4 py-3 text-center">Tuteur</th>
                        <th class="px-4 py-3 text-left">Email Parent</th>
                        <th class="px-4 py-3 text-left">Contact</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($students as $student)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-center">{{ $student->num_educ }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800"> <a href="{{ route('primaire.ecoliers.show', $student-> id) }}">{{ $student->last_name }}</a></td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $student->first_name }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $student->classe?->name ?? 'Non assignée' }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $student-> gender }}</td>
                         <td class="px-4 py-3 text-center text-gray-600">{{ $student-> birth_date ?? '-' }}</td>
                         <td class="px-4 py-3 text-center text-gray-600">{{ $student-> birth_place ?? '-' }}</td>
                     
                        <td class="px-4 py-3 text-gray-600">{{ $student->parent_full_name ?? '-' }} {{ $student->parent_phone ?? '-' }}</td>
                
                        <td class="px-4 py-3 text-gray-600">{{ $student->parent_email ?? '-' }} {{ $student->parent_phone ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $student->parent_phone ?? '-' }} {{ $student->parent_phone ?? '-' }}</td>
                        
                    </tr>
                    @endforeach
                </tbody>
                
            </table>
        </div>
        @else
        <p class="text-gray-500 italic mt-4">Aucun élève inscrit</p>
        @endif
    </div>
</div>
@endsection
