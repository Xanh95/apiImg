<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Upload;

class UploadController extends ResponseApiController
{
    //
    public function store(Request $request)
    {
        $request->validate([
            'images.*' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'id_object' => 'required',
            'object' => 'required'
        ]);

        $images = $request->images;
        $object = $request->object;
        $id_object = $request->id_object;
        $dirUpload = "public/upload/$object/" . date('Y/m/d');
        $urls = [];
        $upload = new Upload;

        foreach ($images as $image) {
            $imageUrl = uploadImage($image, $dirUpload);
            $urls[] = $imageUrl;
        }
        $upload->urls = implode('-', $urls);

        $upload->save();
        return $this->handleSuccess($upload, "upload image $object");
    }
}
