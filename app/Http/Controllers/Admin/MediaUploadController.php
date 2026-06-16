<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaUploadController extends Controller
{
    public function __construct(private readonly UniqueUploadNamer $uploadNamer) {}

    public function upload(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:4096', 'mimes:jpg,jpeg,png,webp,gif'],
        ]);

        $file = $data['file'];
        $disk = 'public';
        $path = $this->uploadNamer->makePathForUploadedFile(
            $disk,
            'uploads/editor',
            'editor_media',
            $file,
            'jpg',
        );
        $stream = fopen($file->getRealPath(), 'rb');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) fclose($stream);

        $url = Storage::disk($disk)->url($path);
        return response()->json(['url' => $url]);
    }
}
