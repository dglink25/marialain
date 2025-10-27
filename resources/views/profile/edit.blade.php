@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Profil';
@endphp
    {{-- Messages flash --}}
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
<div class="max-w-5xl mx-auto bg-white p-4 sm:p-6 lg:p-8 rounded-lg">
    <!-- En-tête -->
    <div class="text-center mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Compléter mon Profil</h1>
        <p class="text-gray-600">Mettez à jour vos informations personnelles</p>
    </div>

    <!-- Photo de profil -->
    <div class="flex flex-col items-center mb-8">
        <form action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data" class="text-center">
            @csrf
            <label class="cursor-pointer inline-block">
                <input type="file" name="profile_photo" class="hidden" onchange="previewImage(event); this.form.submit()">
                 <div class="relative">
                   <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden border-2 border-gray-200 flex items-center justify-center">
                        <img id="preview"
                            src="{{ $user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('logo.png') }}"
                            onerror="this.src='{{ asset('logo.png') }}';"
                            class="w-full h-full object-cover"
                            alt="Photo de profil">
                    </div> 
                    <div class="absolute bottom-0 right-0 bg-blue-100 rounded-full p-1">
                        <i class="fas fa-camera text-blue-600 text-xs"></i>
                    </div>
                </div>  
                <span class="text-sm text-gray-500 mt-2 block">Cliquer pour changer</span>
            </label> 
            @if($user->profile_photo)
                <button type="submit" name="remove_photo" value="1" 
                        class="mt-3 px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600 transition duration-200 flex items-center gap-1 mx-auto">
                    <i class="fas fa-trash"></i>
                    Supprimer
                </button>
            @endif
        </form>
    </div> 

    <!-- Formulaire principal -->
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- Informations personnelles -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-user-circle"></i>
                Informations personnelles
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sexe</label>
                    <select name="gender" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                        <option value="">Sélectionner</option>
                        <option value="M" @selected($user->gender=='M')>Masculin</option>
                        <option value="F" @selected($user->gender=='F')>Féminin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Situation matrimoniale</label>
                    <input type="text" name="marital_status" value="{{ old('marital_status', $user->marital_status) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lieu de naissance</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $user->birth_place) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nationalité</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $user->nationality) }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-file-alt"></i>
                Documents administratifs
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Carte d'identité -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card"></i>
                        Carte d'identité (PDF)
                    </label>
                    <input type="file" name="id_card_file" accept="application/pdf" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @if($user->id_card_file)
                        <div class="mt-2 flex items-center gap-2">
                            <a href="{{ asset('storage/'.$user->id_card_file) }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                                <i class="fas fa-eye"></i>
                                Voir le document
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Acte de naissance -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-birthday-cake"></i>
                        Acte de naissance (PDF)
                    </label>
                    <input type="file" name="birth_certificate_file" accept="application/pdf" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @if($user->birth_certificate_file)
                        <div class="mt-2 flex items-center gap-2">
                            <a href="{{ asset('storage/'.$user->birth_certificate_file) }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                                <i class="fas fa-eye"></i>
                                Voir le document
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Diplôme -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap"></i>
                        Diplôme (PDF)
                    </label>
                    <input type="file" name="diploma_file" accept="application/pdf" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @if($user->diploma_file)
                        <div class="mt-2 flex items-center gap-2">
                            <a href="{{ asset('storage/'.$user->diploma_file) }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                                <i class="fas fa-eye"></i>
                                Voir le document
                            </a>
                        </div>
                    @endif
                </div>

                <!-- IFU -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-invoice-dollar"></i>
                        IFU (PDF)
                    </label>
                    <input type="file" name="ifu_file" accept="application/pdf" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @if($user->ifu_file)
                        <div class="mt-2">
                            <a href="{{ asset('storage/'.$user->ifu_file) }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1 mb-1">
                                <i class="fas fa-eye"></i>
                                Voir le document
                            </a>
                            <p class="text-xs text-gray-600">N° extrait : {{ $user->ifu_number }}</p>
                        </div>
                    @endif
                </div>

                <!-- RIB -->
                <div class="border border-gray-200 rounded-lg p-4 lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-university"></i>
                        RIB (PDF)
                    </label>
                    <input type="file" name="rib_file" accept="application/pdf" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @if($user->rib_file)
                        <div class="mt-2 flex items-center gap-2">
                            <a href="{{ asset('storage/'.$user->rib_file) }}" target="_blank" 
                               class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                                <i class="fas fa-eye"></i>
                                Voir le document
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bouton d'enregistrement -->
        <div class="text-center">
            <button type="submit" 
                    class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition duration-200 font-medium flex items-center gap-2 mx-auto">
                <i class="fas fa-save"></i>
                Enregistrer les modifications
            </button>
        </div>
    </form>

    <!-- Section mot de passe -->
    <div class="mt-8 border-t border-gray-200 pt-8">
        <div class="text-center">
            <button id="btnPassword" 
                    class="bg-yellow-500 text-white px-6 py-2 rounded-lg hover:bg-yellow-600 transition duration-200 font-medium flex items-center gap-2 mx-auto">
                <i class="fas fa-key"></i>
                Modifier mon mot de passe
            </button>
        </div>

        <!-- Formulaire mot de passe -->
        <div id="passwordForm" class="hidden mt-6 max-w-md mx-auto">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-lock"></i>
                    Changer le mot de passe
                </h3>
                
                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ancien mot de passe</label>
                        <input type="password" name="old_password" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                        @error('old_password') 
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                        <input type="password" name="password" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                        @error('password') 
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                    </div>
                    
                    <button type="submit" 
                            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-200 w-full font-medium">
                        Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnPassword = document.getElementById('btnPassword');
    const passwordForm = document.getElementById('passwordForm');

    if (btnPassword && passwordForm) {
        btnPassword.addEventListener('click', () => {
            passwordForm.classList.toggle('hidden');
        });
    }
});

function previewImage(event) {
    let reader = new FileReader();
    reader.onload = function(){
        document.getElementById('preview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<style>
/* Adaptation mobile */
@media (max-width: 768px) {
    .max-w-5xl {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .grid-cols-1 > div {
        width: 100%;
    }
}

/* Animation douce */
#passwordForm {
    transition: all 0.3s ease-in-out;
}
</style>
@endsection