@extends('layouts.app')

@section('content')
@if(session('showPasswordForm') || $errors->has('old_password') || $errors->has('password'))
<script>
    document.getElementById('passwordForm').classList.remove('hidden');
</script>
@endif

<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow animate__animated animate__fadeIn">
    <h1 class="text-2xl font-bold mb-6 text-center">Compléter mon Profil</h1>

        <!-- Photo -->
        <div class="flex flex-col items-center">
            <form action="{{ route('profile.updatePhoto') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="cursor-pointer">
                    <input type="file" name="profile_photo" class="hidden" onchange="previewImage(event); this.form.submit()">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-gray-300 flex items-center justify-center">
                        <img id="preview"
                        src="{{ asset('storage/'.$user->profile_photo ?? 'default-avatar.png') }}"
                        onerror="this.src='{{ asset('default-avatar.png') }}';"
                        class="w-32 h-32 rounded-full object-cover object-center border-2 border-gray-300"
                        alt="Photo de profil">
                    </div>
                    <span class="text-sm text-gray-500">Cliquer pour changer</span>
                </label>
                @if($user->profile_photo)
                    <button type="submit" name="remove_photo" value="1" 
                            class="mt-2 px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                        Supprimer la photo
                    </button>
                @endif
            </form>
        </div>

        <script>
        function previewImage(event) {
            let reader = new FileReader();
            reader.onload = function(){
                document.getElementById('preview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
        </script>


        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4 mt-6">
        @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Infos basiques -->
                <input type="text" name="name" value="{{ old('name',$user->name) }}" placeholder="Nom" class="border p-2 rounded">
                <input type="email" name="email" value="{{ old('email',$user->email) }}" placeholder="Email" class="border p-2 rounded">

                <select name="gender" class="border p-2 rounded">
                    <option value="">-- Sexe --</option>
                    <option value="M" @selected($user->gender=='M')>Masculin</option>
                    <option value="F" @selected($user->gender=='F')>Féminin</option>
                </select>
                <input type="text" name="phone" value="{{ old('phone',$user->phone) }}" placeholder="Téléphone" class="border p-2 rounded">

                <input type="text" name="marital_status" value="{{ old('marital_status',$user->marital_status) }}" placeholder="Situation matrimoniale" class="border p-2 rounded">
                <input type="text" name="address" value="{{ old('address',$user->address) }}" placeholder="Adresse" class="border p-2 rounded">

                <input type="date" name="birth_date" value="{{ old('birth_date',$user->birth_date) }}" class="border p-2 rounded">
                <input type="text" name="birth_place" value="{{ old('birth_place',$user->birth_place) }}" placeholder="Lieu de naissance" class="border p-2 rounded">

                <input type="text" name="nationality" value="{{ old('nationality',$user->nationality) }}" placeholder="Nationalité" class="border p-2 rounded">
            </div>

            <!-- Fichiers PDF -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label>Carte d'identité (PDF)</label>
                    <input type="file" name="id_card_file" accept="application/pdf" class="w-full border p-2 rounded">
                    @if($user->id_card_file)
                        <a href="{{ asset('storage/'.$user->id_card_file) }}" target="_blank" class="text-blue-600 underline text-sm">Ouvrir</a>
                    @endif
                </div>

                <div>
                    <label>Acte de naissance (PDF)</label>
                    <input type="file" name="birth_certificate_file" accept="application/pdf" class="w-full border p-2 rounded">
                    @if($user->birth_certificate_file)
                        <a href="{{ asset('storage/'.$user->birth_certificate_file) }}" target="_blank" class="text-blue-600 underline text-sm">Ouvrir</a>
                    @endif
                </div>

                <div>
                    <label>Diplôme (PDF)</label>
                    <input type="file" name="diploma_file" accept="application/pdf" class="w-full border p-2 rounded">
                    @if($user->diploma_file)
                        <a href="{{ asset('storage/'.$user->diploma_file) }}" target="_blank" class="text-blue-600 underline text-sm">Ouvrir</a>
                    @endif
                </div>

                <div>
                    <label>IFU (PDF)</label>
                    <input type="file" name="ifu_file" accept="application/pdf" class="w-full border p-2 rounded">
                    @if($user->ifu_file)
                        <a href="{{ asset('storage/'.$user->ifu_file) }}" target="_blank" class="text-blue-600 underline text-sm">Ouvrir</a>
                        <p class="text-sm text-gray-500">N° extrait : {{ $user->ifu_number }}</p>
                    @endif
                </div>

                <div>
                    <label>RIB (PDF)</label>
                    <input type="file" name="rib_file" accept="application/pdf" class="w-full border p-2 rounded">
                    @if($user->rib_file)
                        <a href="{{ asset('storage/'.$user->rib_file) }}" target="_blank" class="text-blue-600 underline text-sm">Ouvrir</a>
                    @endif
                </div>
            </div>

            <div class="text-center mt-6">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>

    <!-- Bouton mot de passe -->
    <div class="text-center mt-6">
        <button id="btnPassword" class="bg-yellow-600 text-white px-4 py-2 rounded">
            Modifier mon mot de passe
        </button>
    </div>

    <!-- Formulaire mot de passe -->
    <div id="passwordForm" class="hidden mt-6">
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4 max-w-md mx-auto bg-gray-50 p-4 rounded-lg shadow">
            @csrf
            <div>
                <label>Ancien mot de passe</label>
                <input type="password" name="old_password" class="w-full border rounded p-2">
                @error('old_password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label>Nouveau mot de passe</label>
                <input type="password" name="password" class="w-full border rounded p-2">
                @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label>Confirmer mot de passe</label>
                <input type="password" name="password_confirmation" class="w-full border rounded p-2">
            </div>
            <button class="bg-green-600 text-white px-4 py-2 rounded">Changer</button>
        </form>
    </div>
</div>

<script>
document.getElementById('btnPassword').addEventListener('click', () => {
    document.getElementById('passwordForm').classList.toggle('hidden');
});

function previewImage(event) {
    let reader = new FileReader();
    reader.onload = function(){
        document.getElementById('preview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endsection
