<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageStorageService
{
    /**
     * @return array{path: string, original_name: string}
     */
    public function store(UploadedFile $file): array
    {
        $directory = public_path(config('photos.dir'));

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->extension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;

        $file->move($directory, $filename);

        return [
            'path' => config('photos.dir').'/'.$filename,
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    public function delete(?string $path): void
    {
        if ($path === null) {
            return;
        }

        $base = realpath(public_path(config('photos.dir')));
        $target = realpath(public_path($path));

        if ($base === false || $target === false) {
            return;
        }

        if (str_starts_with($target, $base.DIRECTORY_SEPARATOR) && is_file($target)) {
            unlink($target);
        }
    }
}
