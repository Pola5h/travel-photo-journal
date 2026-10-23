@extends('layouts.app')

@section('title', 'Photos')

@section('content')
    <div x-data="photosIndex()" x-init="load()">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">My photos</h1>
            <a href="/photos/create" class="rounded bg-gray-900 px-4 py-2 text-sm text-white">Add photo</a>
        </div>

        <div class="space-y-4">
            @include('partials.alert')
        </div>

        <p x-show="loading" class="mt-4 text-sm text-gray-600">Loading...</p>

        <div x-show="!loading" class="mt-4 space-y-4">
            <template x-for="photo in photos" :key="photo.id">
                <article class="flex gap-4 rounded border bg-white p-4">
                    <img :src="photo.image_url" :alt="photo.name" class="h-32 w-32 rounded object-cover">

                    <div class="flex-1">
                        <h2 class="text-lg font-semibold" x-text="photo.name"></h2>
                        <p class="text-sm text-gray-600" x-text="photo.location"></p>
                        <p class="mt-2 text-sm text-gray-700" x-text="photo.summary || 'No summary available.'"></p>

                        <div class="mt-3 flex gap-4 text-sm">
                            <a :href="'/photos/' + photo.id + '/edit'" class="underline">Edit</a>
                            <button type="button" class="text-red-600 underline" @click="remove(photo.id)">Delete</button>
                        </div>
                    </div>
                </article>
            </template>

            <p x-show="!photos.length" class="rounded border bg-white p-6 text-sm text-gray-600">
                You have not added any photos yet.
            </p>
        </div>

        <div x-show="meta && meta.last_page > 1" x-cloak class="mt-6 flex items-center gap-4 text-sm">
            <button type="button" class="rounded border px-3 py-1 disabled:opacity-40"
                    :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">
                Previous
            </button>
            <span x-text="'Page ' + meta.current_page + ' of ' + meta.last_page"></span>
            <button type="button" class="rounded border px-3 py-1 disabled:opacity-40"
                    :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">
                Next
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/assets/js/pages/photos-index.js"></script>
@endpush
