<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UploadController extends ResponseApiController
{

    public function store(Request $request)
    {
        $request->validate([
            'images.*' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'object' => 'required|in:category,post,article,user'
        ]);

        $images = $request->images;
        $object = $request->object;
        $dirUpload = "public/upload/$object/" . date('Y/m/d');
        $type = $request->type;
        $option_sizes = [
            '100x2000',
            '200x2000',
            '300x2000',
            '330x2000',
            '480x2000',
            '720x2000',
            '1280x2000'
        ];

        foreach ($images as $image) {
            $upload = new Upload;
            $title = Str::random(10);
            $size = '300x2000';
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
            if ($type === 'avatar') {
                $size = '300x300';
            }
            if ($type === 'cover_photo') {
                $size = '1400x500';
            }
            list($crop_width, $crop_height) = explode('x', $size);
            Image::make($image)->resize($crop_width, $crop_height)->save(storage_path('app/' . $dirUpload . '/' . $imageName));
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $upload->url = $imageUrl;
            $upload->user_id = Auth::id();
            $upload->width = $crop_width;
            $upload->height = $crop_height;
            $upload->save();
            $data[] = $upload;
        }

        return $this->handleSuccess($data, "upload image $object");
    }

    public function video(Request $request)
    {
        $request->validate([
            'object' => 'required|in:category,post,article,user'
        ]);

        $video = $request->video;
        $object = $request->object;
        $dirUpload = "public/upload/video/$object/" . date('Y/m/d');
        $upload = new Upload;
        $title = Str::random(10);

        if (!Storage::exists($dirUpload)) {
            Storage::makeDirectory($dirUpload, 0755, true);
        }
        $videoName = $title . '.' . $video->extension();
        $video->storeAs($dirUpload, $videoName);
        $videoUrl = asset(Storage::url($dirUpload . '/' . $videoName));
        $upload->url = $videoUrl;
        $upload->user_id = Auth::id();
        $upload->save();

        return $this->handleSuccess($upload, 'upload video success');
    }
}
