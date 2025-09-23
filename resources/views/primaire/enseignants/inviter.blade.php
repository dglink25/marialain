@extends('layouts.app')
@section('content')
<div id="ajouter">
     <h1 class="text-2xl font-bold mb-6">Inviter un enseignant</h1>
    <form action="{{  route('primaire.enseignants.inviter.store')  }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nom de l'enseignant</label>
            <input type="text" name="name" id="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
            <label for="name" class="block text-sm font-medium text-gray-700">Classe</label>
            <select name="classe" id="" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" >
                
            <option value=""></option>
            @foreach($classes as $class)
                    <option value="">{{ $class-> name }}</option>
                @endforeach
            </select>
             
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="text" name="name" id="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
        
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Inviter</button>
    </form>

</div>

@endsection