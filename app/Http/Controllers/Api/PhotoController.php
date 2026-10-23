<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Photo\StorePhotoRequest;
use App\Http\Requests\Photo\UpdatePhotoRequest;
use App\Http\Resources\PhotoResource;
use App\Services\PhotoService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function index(Request $request, PhotoService $photos): JsonResponse
    {
        $paginator = $photos->paginateForUser($request->user());

        return ApiResponse::success([
            'items' => PhotoResource::collection($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], 'Photos retrieved.');
    }

    public function store(StorePhotoRequest $request, PhotoService $photos): JsonResponse
    {
        $photo = $photos->create($request->user(), $request->validated(), $request->file('image'));

        return ApiResponse::success(new PhotoResource($photo), 'Photo created.', 201);
    }

    public function show(Request $request, int $id, PhotoService $photos): JsonResponse
    {
        $photo = $photos->findForUser($request->user(), $id);

        $this->authorize('view', $photo);

        return ApiResponse::success(new PhotoResource($photo), 'Photo retrieved.');
    }

    public function update(UpdatePhotoRequest $request, int $id, PhotoService $photos): JsonResponse
    {
        $photo = $photos->findForUser($request->user(), $id);

        $this->authorize('update', $photo);

        $photo = $photos->update($photo, $request->validated());

        return ApiResponse::success(new PhotoResource($photo), 'Photo updated.');
    }

    public function destroy(Request $request, int $id, PhotoService $photos): JsonResponse
    {
        $photo = $photos->findForUser($request->user(), $id);

        $this->authorize('delete', $photo);

        $photos->delete($photo);

        return ApiResponse::success(null, 'Photo deleted.');
    }
}
