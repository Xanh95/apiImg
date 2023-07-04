<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiImgrequest;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;


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

        $directory = public_path('upload/' . date('Y') . '/' . date('n') . '/' . date('d'));
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0777, true, true);
        }
        if ($request->hasFile('image')) {
            $imageName =  date('Y-m-d') . '_' . Str::random(10) . '.' . $request->file('image')->getClientOriginalName();
            $request->image->move(public_path('upload/' . date('Y') . '/' . date('n') . '/' . date('d')), $imageName);
            $imageUrl = asset('upload/' . date('Y') . '/' . date('n') . '/' . date('d') . '/' . $imageName);
        }
        $photo = new Photo;
        $photo->name = $imageName;
        $photo->path = 'upload/' . date('Y') . '/' . date('n') . '/' . date('d');
        $photo->url = $imageUrl;
        $photo->save();
        return response()->json($photo);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ApiImgrequest $request, $photo)
    {
        //

        try {

            $obj_photo = Photo::findOrFail($photo);

            if ($request->hasFile('image')) {
                $imageNewName =  date('Y-m-d') . '_' . Str::random(10) . '.' . $request->file('image')->getClientOriginalName();
                File::delete(public_path($obj_photo->path . "/" . $obj_photo->name));
                $obj_photo->name = $imageNewName;
                $obj_photo->update();
                $request->image->move(public_path($obj_photo->path), $imageNewName);
                return response()->json('sua anh thanh cong', $obj_photo);
            }
            return response()->json('sua anh that bai');
        } catch (ModelNotFoundException $e) {

            return response()->json(['error' => 'Không tìm thấy đối tượng'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($photo)
    {
        //
        try {
            $obj_photo = Photo::findOrFail($photo);
            if (File::delete(public_path($obj_photo->path . "/" . $obj_photo->name))) {
                $obj_photo->delete();
                return response()->json('xóa thành công');
            }
            return response()->json('xóa thất bại');
        } catch (ModelNotFoundException $e) {
            return response()->json('khong tim thay doi tuong');
        }
    }
}
