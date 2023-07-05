<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiImgrequest;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ApiImg extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        //
        $posts = Photo::latest()->paginate(10);
        return response()->json($posts, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ApiImgrequest $request)
    {
        //

        $dirUpload = 'public/upload/' . date('Y/m/d');
        $image = $request->image;
        $title = date('Y-m-d') . '_' . Str::random(10) . '.';
        if (!Storage::exists($dirUpload)) {
            Storage::makeDirectory($dirUpload, 0755, true);
        }
        if ($request->hasFile('image')) {
            $imageName = $title . $image->extension();
            $image->storeAs($dirUpload, $imageName);
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageName));
            $photo = new Photo;
            $photo->name = $imageName;
            $photo->path = $dirUpload;
            $photo->url = $imageUrl;
            $photo->save();
            return response()->json($imageUrl, 200);
        }
        return response()->json('upload fail', 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Photo $photo)
    {
        //


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Photo $photo)
    {
        //
        return response()->json($photo, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ApiImgrequest $request, Photo $photo)
    {
        //
        $dirUpload = 'public/upload/' . date('Y/m/d');
        $title = date('Y-m-d') . '_' . Str::random(10) . '.';
        $name = $request->name;
        $image = $request->image;
        if ($image) {
            $imageNewName =  $title . '.' . $image->extension();
            if ($name) {
                $imageNewName = $name . '.' . $image->extension();
            }
            $imageUrl = asset(Storage::url($dirUpload . '/' . $imageNewName));
            Storage::delete($photo->path . "/" . $photo->name);
            $image->storeAs($dirUpload, $imageNewName);
            $photo->update([
                'path' => $dirUpload,
                'url' => $imageUrl,
                'name' => $imageNewName
            ]);
            return response()->json('edit success', 200);
        }
        if ($name && $image == '') {
            $imageNewName =  $name . '.' . pathinfo($photo->name, PATHINFO_EXTENSION);
            Storage::move($photo->path . "/" . $photo->name, $photo->path . "/" . $imageNewName);
            $imageUrl = asset(Storage::url($photo->path . '/' . $imageNewName));
            $photo->update([
                'url' => $imageUrl,
                'name' => $imageNewName,
            ]);
            
            return response()->json($imageUrl, 200);
        }
        return response()->json('edit fail', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Photo $photo)
    {
        //

        if (Storage::delete($photo->path . "/" . $photo->name)) {
            $photo->delete();
            return response()->json('delete success', 200);
        }
        return response()->json('fail delete', 404);
    }
}