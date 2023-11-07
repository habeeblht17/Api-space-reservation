<?php
namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait FileUploads {

    /**
     * Image Upload logic
     */
    private function handleImageUpload($request, $directory)
    {
        if($request->hasFile('image')) {

            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $uniqueImageName = time() . '_' . $imageName;
            $imagePath = $image->storeAs($directory, $uniqueImageName, 'public');

            return $imagePath;

        }

    }

    /**
     * Image Delete logic
     */
    private function deleteImage($imagePath)
    {
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
