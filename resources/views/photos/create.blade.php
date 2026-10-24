@extends('layouts.app')

@section('title', 'Add photo')

@section('content')
    <div x-data="photoForm({ mode: 'create' })">
        <h1 class="mb-6 text-2xl font-semibold">Add a photo</h1>

        <form class="space-y-4 rounded border bg-white p-6" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700" for="image">
                    Image <span class="text-red-500">*</span>
                </label>

                <!-- Hidden file input -->
                <input id="image" type="file" accept="image/jpeg,image/png,image/webp" @change="onFile" class="sr-only">

                <!-- Drag & Drop / Select Box -->
                <div
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="onDrop($event)"
                    :class="isDragging ? 'border-gray-900 bg-gray-100' : 'border-gray-300 bg-gray-50 hover:bg-gray-100/75'"
                    class="relative rounded-lg border-2 border-dashed transition text-center"
                >
                    <!-- Prompt when no image selected -->
                    <div x-show="!previewUrl" class="w-full">
                        <label for="image" class="flex flex-col items-center justify-center p-8 cursor-pointer w-full">
                            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="mt-3 inline-block rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Select Image
                            </span>
                            <span class="mt-2 text-sm text-gray-600 font-medium">or drag and drop photo here</span>
                            <span class="mt-1 text-xs text-gray-400">JPG, JPEG, PNG, WEBP (Max 5MB)</span>
                        </label>
                    </div>

                    <!-- Preview when image is selected -->
                    <div x-show="previewUrl" x-cloak class="p-6 flex flex-col items-center text-center">
                        <img :src="previewUrl" alt="Selected photo preview" class="max-h-64 rounded-lg object-contain shadow-sm border border-gray-200">
                        <div class="mt-3 flex items-center gap-3">
                            <span class="text-xs font-medium text-gray-700" x-text="image?.name"></span>
                            <label for="image" class="cursor-pointer text-xs font-semibold text-gray-900 hover:underline">
                                Change image
                            </label>
                            <span class="text-gray-300">|</span>
                            <button type="button" @click="removeImage" class="text-xs font-semibold text-red-600 hover:text-red-800">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="name">
                    Name <span class="text-red-500">*</span>
                </label>
                <input id="name" type="text" x-model="name" required maxlength="120"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="location">
                    Location <span class="text-red-500">*</span>
                </label>
                <input id="location" type="text" x-model="location" required maxlength="160"
                       class="w-full rounded border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium" for="description">
                    Description <span class="text-red-500">*</span>
                </label>
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
                <span x-text="loading ? 'Saving...' : 'Create photo'"></span>
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="/assets/js/pages/photos-form.js?v={{ time() }}"></script>
@endpush
