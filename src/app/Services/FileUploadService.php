<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload user file
     *
     * @return string
     */
    public function uploadFile($file, $userID)
    {
        $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
        Storage::disk('storage')->putFileAs($userID, $file, $fileName);

        return $userID . '/' . $fileName;
    }
}
