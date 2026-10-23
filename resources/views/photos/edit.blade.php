@extends('layouts.app')

@section('title', 'Edit photo')

@section('content')
    <div x-data="photoForm({ mode: 'edit', photoId: {{ (int) $photoId }} })">
        <h1 class="mb-6 text-2xl font-semibold">Edit photo</h1>

        <form class="space-y-4 rounded border bg-white p-6" @submit.prevent="submit">
            <div x-show="imageUrl" x-cloak>
                <img :src="imageUrl" alt="Current photo" class="h-48 w-48 rounded object-cover">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="name">Name</label>
                <input id="name" type="text" x-model="name" required maxlength="120"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="location">Location</label>
                <input id="location" type="text" x-model="location" required maxlength="160"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="description">Description</label>
                <textarea id="description" x-model="description" required rows="5"
                          class="w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>

            <div x-show="summary" x-cloak class="rounded bg-gray-50 p-3 text-sm text-gray-700">
                <span class="font-medium">AI summary: </span>
                <span x-text="summary"></span>
            </div>

            @include('partials.alert')

            <button type="submit" :disabled="loading"
                    class="rounded bg-gray-900 px-4 py-2 text-white disabled:opacity-50">
                <span x-text="loading ? 'Saving...' : 'Save changes'"></span>
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="/assets/js/pages/photos-form.js"></script>
@endpush
