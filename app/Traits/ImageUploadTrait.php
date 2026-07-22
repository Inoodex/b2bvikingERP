<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

trait ImageUploadTrait
{

    // // /** handle slider image file */

    // public function sliderImage(Request $request, $inputName, $path)
    // {
    //     if ($request->hasFile($inputName)) {
    //         $image = $request->{$inputName};
    //         $ext = $image->getClientOriginalExtension();
    //         $imageName = 'media_' . uniqid() . '.' . $ext;
    //         $image->move(public_path($path), $imageName);
    //         return $path . '/' . $imageName;
    //     }
    // }

    // /** image upload handle with intervention */
    // public function uploadImage($request, $imageField, $directory, $width = 400, $height = 500)
    // {
    //     if ($request->file($imageField)) {
    //         $image = $request->file($imageField);

    //         $width = $width ?: 400;
    //         $height = $height ?: 500;

    //         $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
    //         $manager = new ImageManager(new Driver());
    //         $img = $manager->read($image);
    //         $img->resize($width, $height)->save($directory . '/' . $name_gen);
    //         return $directory . '/' . $name_gen;
    //     }
    //     return null;
    // }

    // public function deleteImage($path)
    // {
    //     if (file_exists(public_path($path))) {
    //         unlink(public_path($path));
    //     }
    // }

    // /** mulitple image upload */
    // public function uploadMultiImage(Request $request, $inputName, $directory, $width = 400, $height = 500)
    // {
    //     $imagepaths = [];
    //     if ($request->hasFile($inputName)) {
    //         $images = $request->{$inputName};
    //         foreach ($images as $image) {
    //             $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
    //             $manager = new ImageManager(new Driver());
    //             $img = $manager->read($image);
    //             $img->resize($width, $height)->save($directory . '/' . $name_gen);
    //             $imagepaths[] = $directory . '/' . $name_gen;
    //         }
    //         return $imagepaths;
    //     }
    // }
    // /** update image (handles optional width/height like uploadImage) */
    // public function updateImage($request, $imageField, $directory, $oldImage = null, $width = 400, $height = 500)
    // {
    //     if ($request->file($imageField)) {
    //         // delete old image if exists
    //         if ($oldImage) {
    //             $this->deleteImage($oldImage);
    //         }

    //         // fallback if null passed
    //         $width = $width ?: 400;
    //         $height = $height ?: 500;

    //         // upload and return new image
    //         return $this->uploadImage($request, $imageField, $directory, $width, $height);
    //     }

    //     // return old image if no new file uploaded
    //     return $oldImage;
    // }

    // //check of svg/webp/gif 
    // public function uploadSpecialImage($request, $imageField, $directory, $oldImage = null)
    // {
    //     if ($request->hasFile($imageField)) {
    //         $file = $request->file($imageField);
    //         $extension = strtolower($file->getClientOriginalExtension());

    //         // Only allow svg, webp, or gif
    //         if (in_array($extension, ['svg', 'webp', 'gif'])) {

    //             // Delete old image if exists
    //             if ($oldImage && file_exists(public_path($oldImage))) {
    //                 unlink(public_path($oldImage));
    //             }

    //             $name = hexdec(uniqid()) . '.' . $extension;
    //             $file->move(public_path($directory), $name);

    //             return $directory . '/' . $name;
    //         }
    //     }

    //     // return $oldImage; // Return old image if no new upload
    //     if ($request->hasFile($imageField)) {
    //         dd('No file uploaded for ' . $imageField);
    //         $file = $request->file($imageField);
    //         $extension = strtolower($file->getClientOriginalExtension());

    //         // অনুমোদিত সব ইমেজ এক্সটেনশন
    //         $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'ico', 'bmp', 'tiff'];

    //         if (in_array($extension, $allowed)) {

    //             // পুরনো ইমেজ মুছে ফেলো যদি থাকে
    //             if ($oldImage && file_exists(public_path($oldImage))) {
    //                 unlink(public_path($oldImage));
    //             }

    //             $name = hexdec(uniqid()) . '.' . $extension;
    //             $file->move(public_path($directory), $name);

    //             return $directory . '/' . $name;
    //         }
    //     }

    //     return $oldImage; // নতুন আপলোড না থাকলে পুরনো ইমেজই ফেরত দাও
    // }
    // public function uploadSpecialImage($request, $imageField, $directory, $oldImage = null)
    // {
    //     if ($request->hasFile($imageField)) {
    //         $file = $request->file($imageField);

    //         // Get extension from original file name
    //         $extension = strtolower($file->getClientOriginalExtension());

    //         $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'ico', 'bmp', 'tiff'];

    //         if (!in_array($extension, $allowed)) {
    //             // যদি original extension allowed না হয়, force use original extension
    //             $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
    //             $extension = strtolower($extension);
    //             if (!in_array($extension, $allowed)) {
    //                 return $oldImage; // reject file
    //             }
    //         }

    //         if (!file_exists(public_path($directory))) {
    //             mkdir(public_path($directory), 0777, true);
    //         }

    //         if ($oldImage && file_exists(public_path($oldImage))) {
    //             unlink(public_path($oldImage));
    //         }

    //         $name = hexdec(uniqid()) . '.' . $extension;
    //         $file->move(public_path($directory), $name);

    //         return $directory . '/' . $name;
    //     }

    //     return $oldImage;
    // }








    // normal way handle image 
    public function upload_image(Request $request, $inputName, $path)
    {
        if ($request->hasFile($inputName)) {
            $image = $request->file($inputName);
            $ext = $image->getClientOriginalExtension();
            $imageName = 'media_' . uniqid() . '.' . $ext;

            $image->storeAs($path, $imageName, 'public');

            return $path . '/' . $imageName;
        }
        return null;
    }

    /** handle multi image file */
    public function upload_multiImage(Request $request, $inputName, $path)
    {
        $imagepaths = [];
        if ($request->hasFile($inputName)) {
            $images = $request->{$inputName};
            foreach ($images as $image) {
                $ext = $image->getClientOriginalExtension();
                $imageName = 'media_' . uniqid() . '.' . $ext;

                $image->storeAs($path, $imageName, 'public');

                $imagepaths[] = $path . '/' . $imageName;
            }
            return $imagepaths;
        }
        return null;
    }

    /** handle single image update file  */
    public function update_image(Request $request, $inputName, $path, $oldPath = null)
    {
        if ($request->hasFile($inputName)) {
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $image = $request->file($inputName);
            $ext = $image->getClientOriginalExtension();
            $imageName = 'media_' . uniqid() . '.' . $ext;

            $image->storeAs($path, $imageName, 'public');

            return $path . '/' . $imageName;
        }
        return null;
    }

    /** handle delete file */
    public function delete_image(?string $path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
