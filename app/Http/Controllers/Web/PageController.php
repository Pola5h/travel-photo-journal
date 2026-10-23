<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function register(): View
    {
        return view('auth.register');
    }

    public function login(): View
    {
        return view('auth.login');
    }

    public function checkYourEmail(): View
    {
        return view('auth.check-your-email');
    }

    public function verifyEmail(): View
    {
        return view('auth.verify-email');
    }

    public function photos(): View
    {
        return view('photos.index');
    }

    public function createPhoto(): View
    {
        return view('photos.create');
    }

    public function editPhoto(int $id): View
    {
        return view('photos.edit', ['photoId' => $id]);
    }

    public function security(): View
    {
        return view('settings.security');
    }
}
