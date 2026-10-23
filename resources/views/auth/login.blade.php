@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <div x-data="loginPage()">
        <h1 class="mb-6 text-2xl font-semibold">Log in</h1>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium" for="email">Email</label>
                <input id="email" type="email" x-model="email" required autocomplete="email"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="password">Password</label>
                <input id="password" type="password" x-model="password" required autocomplete="current-password"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div x-show="twoFactorRequired" x-cloak>
                <label class="mb-1 block text-sm font-medium" for="otp">Authenticator code</label>
                <input id="otp" type="text" x-model="otp" inputmode="numeric" maxlength="6" autocomplete="one-time-code"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            @include('partials.alert')

            <button type="submit" :disabled="loading"
                    class="w-full rounded bg-gray-900 px-4 py-2 text-white disabled:opacity-50">
                <span x-text="loading ? 'Logging in...' : 'Log in'"></span>
            </button>
        </form>

        <p class="mt-4 text-sm">
            Need an account?
            <a href="/register" class="underline">Register</a>
        </p>
    </div>
@endsection

@push('scripts')
    <script src="/assets/js/pages/login.js"></script>
@endpush
