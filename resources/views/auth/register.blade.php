@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <div x-data="registerPage()">
        <h1 class="mb-6 text-2xl font-semibold">Create an account</h1>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium" for="name">Name</label>
                <input id="name" type="text" x-model="name" required autocomplete="name"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="email">Email</label>
                <input id="email" type="email" x-model="email" required autocomplete="email"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="password">Password</label>
                <input id="password" type="password" x-model="password" required autocomplete="new-password"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" x-model="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            @include('partials.alert')

            <button type="submit" :disabled="loading"
                    class="w-full rounded bg-gray-900 px-4 py-2 text-white disabled:opacity-50">
                <span x-text="loading ? 'Creating account...' : 'Register'"></span>
            </button>
        </form>

        <p class="mt-4 text-sm">
            Already have an account?
            <a href="/login" class="underline">Log in</a>
        </p>
    </div>
@endsection

@push('scripts')
    <script src="/assets/js/pages/register.js"></script>
@endpush
