<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Espace Parent
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Connectez-vous pour suivre la scolarité de vos enfants
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST" action="{{ route('parent.login') }}">
            @csrf
            
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="phone" class="sr-only">Numéro de téléphone</label>
                    <input id="phone" name="phone" type="tel" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="01XXXXXXXX" 
                           value="{{ old('phone') }}"
                           pattern="01[0-9]{8}"
                           title="Format: 01XXXXXXXX">
                </div>
                <div>
                    <label for="password" class="sr-only">Mot de passe</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Mot de passe">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Se connecter
                </button>
            </div>

            <div class="text-sm text-center">
                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Mot de passe oublié ?
                </a>
            </div>
        </form>

        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-4">
                <div class="text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif
    </div>
</div>