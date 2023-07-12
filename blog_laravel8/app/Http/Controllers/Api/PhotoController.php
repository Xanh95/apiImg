<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ApiImgrequest;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\ResponseApiController;
use Illuminate\Support\Facades\File;

class PhotoController extends ResponseApiController
{

    public function index()
    {
        //
        $posts = Photo::all();
        return $this->handleSuccess($posts, 'get success');
    }
    public function store(ApiImgrequest $request)
    {
        //

        $dirUpload = 'public/upload/' . date('Y/m/d');
        $image = $request->image;
        $title = Str::random(10) . '.';
        if (!Storage::exists($dirUpload)) {
            Storage::makeDirectory($dirUpload, 0755, true);
        }
        if ($image) {
            $imageName = $title . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $photo = new Photo;
            $photo->name = $imageName;
            $photo->path = $dirUpload;
            $photo->url = $imageUrl;
            $photo->save();
            return $this->handleSuccess($photo, 'upload success');
        }
        return $this->handleError('upload fail', 404);
    }

    public function show(Photo $photo)
    {
        //
        return $this->handleSuccess($photo->url, 'success');
    }

    public function edit(Photo $photo)
    {
        //
        return $this->handleSuccess($photo, 'success');
    }


    public function update(ApiImgrequest $request, Photo $photo)
    {
        //
        $dirUpload = 'public/upload/' . date('Y/m/d');
        $title =  Str::random(10);
        $name = Str::slug($request->name);
        $image = $request->image;
        if ($image) {
            $imageNewName =  $title . '.' . $image->extension();
            if ($name) {
                $imageNewName = $name . '-' . $title . '.' . $image->extension();
            }
            $image->storeAs($dirUpload, $imageNewName);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageNewName));
            Storage::delete($photo->path . "/" . $photo->name);
            $photo->update([
                'path' => $dirUpload,
                'url' => $imageUrl,
                'name' => $imageNewName
            ]);
            return $this->handleSuccess($photo, 'edit success');
        }
        if ($name && $image == '') {
            $imageNewName =  $name  . '-' . $title . '.' . File::extension($photo->name);
            Storage::move($photo->path . "/" . $photo->name, $photo->path . "/" . $imageNewName);
            $imageUrl = asset(Storage::url($photo->path . '/' . $imageNewName));
            $photo->update([
                'url' => $imageUrl,
                'name' => $imageNewName,
            ]);

            return $this->handleSuccess($photo, 'edit success');
        }
        return $this->handleError('edit fail', 404);
    }


    public function destroy(Photo $photo)
    {
        //

        if (Storage::delete($photo->path . "/" . $photo->name)) {
            $photo->delete();
            return $this->handleSuccess($photo, 'delete success');
        }
        return $this->handleError('delete fail', 404);
    }
}
