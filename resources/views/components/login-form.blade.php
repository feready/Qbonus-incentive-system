<div class="w-full lg:w-1/2 flex items-center justify-center p-10 bg-slate-100">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-10">

        <div class="text-center mb-8">

            <img src="{{ asset('images/logoqbonus.png') }}"
                 class="w-20 mx-auto mb-4"
                 alt="QBonus">

            <h2 class="text-3xl font-bold text-slate-800">
                Selamat Datang
            </h2>

            <p class="text-slate-500 mt-2">
                Masuk ke akun Anda untuk melanjutkan
            </p>

        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="mb-5">

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:ring focus:ring-blue-200">

                @error('email')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror

            </div>

            <!-- Password -->
            <div class="mb-5">

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-600 focus:ring focus:ring-blue-200">

                @error('password')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror

            </div>

            <div class="flex justify-between items-center mb-6">

                <label class="flex items-center">

                    <input type="checkbox"
                           name="remember"
                           class="rounded border-slate-300">

                    <span class="ml-2 text-sm text-slate-600">
                        Ingat Saya
                    </span>

                </label>

                @if (Route::has('password.request'))

                    <a href="{{ route('password.request') }}"
                       class="text-blue-700 text-sm hover:underline">

                        Lupa Password?

                    </a>

                @endif

            </div>

            <button
                class="w-full bg-green-600 hover:bg-blue-800 transition text-white font-bold py-3 rounded-xl">

                LOGIN

            </button>

        </form>

        <div class="text-center mt-8 text-xs text-slate-400">

            QBonus v1.0.0

            <br>

            Mitra10

        </div>

    </div>

</div>