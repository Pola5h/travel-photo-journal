<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class PhotoService
{
    public function __construct(
        private readonly ImageStorageService $images,
        private readonly SummaryService $summaries,
    ) {}

    public function paginateForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->photos()->latest()->paginate($perPage);
    }

    public function findForUser(User $user, int $id): Photo
    {
        return $user->photos()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data, UploadedFile $image): Photo
    {
        $stored = $this->images->store($image);

        try {
            $photo = DB::transaction(function () use ($user, $data, $stored): Photo {
                return $user->photos()->create([
                    'name' => $data['name'],
                    'location' => $data['location'],
                    'description' => $data['description'],
                    'image_path' => $stored['path'],
                    'image_original_name' => $stored['original_name'],
                ]);
            });
        } catch (Throwable $e) {
            $this->images->delete($stored['path']);

            throw $e;
        }

        $summary = $this->summaries->summarize($photo->description);

        if ($summary !== null) {
            $photo->forceFill(['summary' => $summary])->save();
        }

        return $photo->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Photo $photo, array $data): Photo
    {
        $descriptionChanged = $photo->description !== $data['description'];

        DB::transaction(function () use ($photo, $data): void {
            $photo->fill($data)->save();
        });

        if ($descriptionChanged) {
            $photo->forceFill([
                'summary' => $this->summaries->summarize($photo->description),
            ])->save();
        }

        return $photo->refresh();
    }

    public function delete(Photo $photo): void
    {
        $path = $photo->image_path;

        DB::transaction(fn () => $photo->delete());

        $this->images->delete($path);
    }
}
