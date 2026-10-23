@extends('layouts.guest')

@section('title', 'Verify email')

@section('content')
    <div class="rounded border bg-white p-6" x-data="verifyEmailPage()">
        <h1 class="mb-3 text-2xl font-semibold">Email verification</h1>

        <p x-show="status === 'verifying'" class="text-sm text-gray-700">Verifying your email address...</p>

        <div x-show="status === 'done'" x-cloak>
            <p class="text-sm text-green-700">Your email address is verified.</p>
            <p class="mt-4 text-sm">
                <a href="/login" class="underline">Continue to login</a>
            </p>
        </div>

        <div x-show="status === 'error'" x-cloak>
            <p class="text-sm text-red-700" x-text="error"></p>
            <p class="mt-4 text-sm">
                <a href="/login" class="underline">Back to login</a>
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/assets/js/pages/verify-email.js"></script>
@endpush
