<?php

use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

function uploadImage($image, $dirUpload)
{
    $title = Str::random(10);
    $option_sizes = config('app.size_img');
    $size = '300x300';
    $minDistance = PHP_INT_MAX;

    list($current_width, $current_height) = getimagesize($image);
    foreach ($option_sizes as $option_size) {
        [$width, $height] = explode('x', $option_size);
        $distance = sqrt(pow($current_width - $width, 2) + pow($current_height - $height, 2));

        if ($distance < $minDistance) {
            $minDistance = $distance;
            $size = $option_size;
        }
    }
    if (!Storage::exists($dirUpload)) {
        Storage::makeDirectory($dirUpload, 0755, true);
    }
    $imageName = $title . '.' . $image->extension();
    list($crop_width, $crop_height) = explode('x', $size);
    Image::make($image)->crop($crop_width, $crop_height)->save(storage_path('app/' . $dirUpload . '/' . $imageName));

    return asset(Storage::url($dirUpload . '/' . $imageName));
}
