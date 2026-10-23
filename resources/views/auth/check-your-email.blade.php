@extends('layouts.guest')

@section('title', 'Check your email')

@section('content')
    <div class="rounded border bg-white p-6">
        <h1 class="mb-3 text-2xl font-semibold">Check your email</h1>
        <p class="text-sm text-gray-700">
            We sent you a verification link. Open it to activate your account, then log in.
        </p>
        <p class="mt-4 text-sm">
            <a href="/login" class="underline">Go to login</a>
        </p>
    </div>
@endsection
