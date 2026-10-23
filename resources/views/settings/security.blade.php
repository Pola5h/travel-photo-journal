@extends('layouts.app')

@section('title', 'Security')

@section('content')
    <div x-data="securityPage()">
        <h1 class="mb-6 text-2xl font-semibold">Security</h1>

        <div class="space-y-4 rounded border bg-white p-6">
            <p x-show="enabled" x-cloak class="text-sm text-green-700">
                Two-factor authentication is enabled for your account.
            </p>

            <div x-show="!enabled" x-cloak>
                <p class="text-sm text-gray-700">
                    Add an extra layer of security with an authenticator app such as Google Authenticator or Aegis.
                </p>
                <button type="button" class="mt-4 rounded bg-gray-900 px-4 py-2 text-sm text-white disabled:opacity-50"
                        :disabled="loading" @click="setup">
                    Set up two-factor authentication
                </button>
            </div>

            <div x-show="qrSrc" x-cloak class="space-y-4 border-t pt-4">
                <p class="text-sm text-gray-700">
                    Scan this QR code, then enter the 6-digit code from your app.
                </p>

                <img :src="qrSrc" alt="Two-factor authentication QR code" class="h-48 w-48">

                <p class="text-sm text-gray-700">
                    Secret key: <span class="font-mono" x-text="secret"></span>
                </p>

                <form class="flex items-end gap-3" @submit.prevent="verify">
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="otp">Code</label>
                        <input id="otp" type="text" x-model="otp" inputmode="numeric" maxlength="6" required
                               class="w-32 rounded border border-gray-300 px-3 py-2">
                    </div>
                    <button type="submit" :disabled="loading"
                            class="rounded bg-gray-900 px-4 py-2 text-sm text-white disabled:opacity-50">
                        Verify and enable
                    </button>
                </form>
            </div>

            @include('partials.alert')
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/assets/js/pages/security.js"></script>
@endpush
