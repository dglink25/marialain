@extends('layouts.app')

@section('content')

        <h1>Informations sur l'élève {{ $student-> last_name }} {{ $student-> first_name }} </h1> 

        <!-- Table des élèves -->
      
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                    
                     <tr>
                        <th class="px-4 py-3 text-left">N° Educ master</th>
                        <th class="px-4 py-3 text-center">{{ $student->num_educ }}</th>
                     </tr>
                     <tr>
                         <th class="px-4 py-3 text-left">Nom</th>
                         <th class="px-4 py-3 font-medium text-gray-800"> {{ $student->last_name }}</a></td>
                     </tr>
                     <tr>
                        <th class="px-4 py-3 text-left">Prénom</th>
                        <th class="px-4 py-3 font-medium text-gray-800">{{ $student->first_name }}</th>
                     </tr>
                    <tr>
                        <th class="px-4 py-3 text-left">Classe</th>
                         <th class="px-4 py-3 text-gray-800">{{ $student->classe?->name ?? 'Non assignée' }}</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-center">Sexe</th>
                        <th class="px-4 py-3 text-gray-800">{{ $student-> gender }}</th>
                    </tr>
                    <tr>
                         <th class="px-4 py-3 text-center">Date de naissance</th>
                         <th class="px-4 py-3 text-center text-gray-600">{{ $student-> birth_date ?? '-' }}</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left">Lieu de naissance</th>
                        <th class="px-4 py-3 text-center text-gray-600">{{ $student-> birth_place ?? '-' }}</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-center">Tuteur</th>
                        <th class="px-4 py-3 text-gray-600">{{ $student->parent_full_name ?? '-' }} {{ $student->parent_phone ?? '-' }}</th>
                    </tr>    
                    <tr>
                        <th class="px-4 py-3 text-left">Email Parent</th>
                        <th class="px-4 py-3 text-gray-600">{{ $student->parent_email ?? '-' }} {{ $student->parent_phone ?? '-' }}</th>
                       
                    </tr>   
                    <tr>
                        <th class="px-2 py-3 text-left">Contact</th>
                        <th class="px-2 py-3 text-gray-600">{{ $student->parent_phone ?? '-' }} {{ $student->parent_phone ?? '-' }}</th>
                         
                    </tr>
                     
                        
                            
                        
                   
                </thead>
                <tbody class="divide-y divide-gray-200">
                    
                    <tr class="hover:bg-gray-50 transition">
                        
                       
                        
                        
                        
                         
                        
                    </tr>
                   
                </tbody>
                
            </table>
        </div>
        
    </div>
</div>
@endsection
