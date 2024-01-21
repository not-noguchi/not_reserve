<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- User No -->
        <div>
            <x-input-label for="user_no" :value="__('会員No')" />
            <x-text-input id="user_no" class="block mt-1 w-full" type="number" name="user_no" :value="old('user_no')" required autofocus autocomplete="user_no" />
            <x-input-error :messages="$errors->get('user_no')" class="mt-2" />
        </div>

        <!-- plan -->
        <div class="mt-4">
            <x-input-label for="plan_id" :value="__('プラン')" />
            <select name="plan_id" id="plan_id" class="form-control @error('plan_id') is-invalid @enderror">
                <option value="">-- 選択してください --</option>
                <option value="1">{{ config('const.m_plan')[1] }}会員</option>
                <option value="2">{{ config('const.m_plan')[2] }}会員</option>
                <option value="3">{{ config('const.m_plan')[3] }}会員</option>
            </select>
            <x-input-error :messages="$errors->get('plan_id')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('名前')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" placeholder="山田 太郎" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="aaaa@bbb.ccc" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('パスワード')" />

            <x-text-input id="password" class="block mt-1 w-full" placeholder="8文字以上必須"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('パスワード　確認')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('既に登録済みですか?') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('登録') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
