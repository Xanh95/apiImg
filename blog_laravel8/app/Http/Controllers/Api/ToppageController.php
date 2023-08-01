<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toppage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Upload;
use Illuminate\Support\Facades\Storage;


class ToppageController extends ResponseApiController
{
    //
    public function store(Request $request)
    {
        $request->validate([
            'area' => 'required|regex:/^[a-zA-Z0-9]+\/[a-zA-Z0-9]+$/',
            'about' => 'required|string|max:200',
            'summary' => 'required|string|max:1000',
            'name' => 'required',
            'facebook' => 'url|starts_with:https://www.facebook.com/',
            'instagram' => 'url|starts_with:https://www.instagram.com/',
            'website' => 'url',
            'status' => 'in:published,unpublished'
        ]);
        if ($request->user()->topPage()->exists()) {
            return $this->handleError('had toppage', 422);
        }

        $name = $request->name;
        $area = $request->area;
        $about = $request->about;
        $summary = $request->summary;
        $cover_photo = $request->cover_photo;
        $avatar = $request->avatar;
        $website = $request->website;
        $facebook = $request->facebook;
        $instagram = $request->instagram;
        $video = $request->video;
        $user = $request->user();
        $user_id = $user->id;
        $toppage = new Toppage;

        $toppage->name = $name;
        $toppage->area = $area;
        $toppage->video = $video;
        $toppage->about = $about;
        $toppage->summary = $summary;
        $toppage->cover_photo = $cover_photo;
        if ($video || $cover_photo || $avatar) {
            $ids = [$video, $cover_photo, $avatar];
            $ids = array_filter($ids);
            CheckUsed($ids);
        }
        $toppage->avatar = $avatar;
        $toppage->website = $website;
        $toppage->facebook = $facebook;
        $toppage->instagram = $instagram;
        $toppage->user_id = $user_id;
        $toppage->save();
        foreach ($languages as $language) {
            $top_page_detail = new TopPageDetail;
            $top_page_detail->name = translate($request->name, $language);
            $top_page_detail->area = translate($request->area, $language);
            $top_page_detail->about = translate($request->about, $language);
            $top_page_detail->summary = translate($request->summary, $language);
            $top_page_detail->top_page_id = $top_page->id;
            $top_page_detail->lang = $language;
            $top_page_detail->save();
        }


        return $this->handleSuccess($toppage, 'success');
    }

    public function update(Request $request)
    {
        $request->validate([
            'area' => 'required|regex:/^[a-zA-Z0-9]+\/[a-zA-Z0-9]+$/',
            'about' => 'required|string|max:200',
            'summary' => 'required|string|max:1000',
            'name' => 'required',
            'facebook' => 'url|starts_with:https://www.facebook.com/',
            'instagram' => 'url|starts_with:https://www.instagram.com/',
            'website' => 'url',
            'status' => 'in:published,unpublished'
        ]);

        $user_id = Auth::id();
        $toppage = Toppage::where('user_id', $user_id)->first();
        $name = $request->name;
        $area = $request->area;
        $about = $request->about;
        $summary = $request->summary;
        $cover_photo = $request->cover_photo;
        $avatar = $request->avatar;
        $website = $request->website;
        $facebook = $request->facebook;
        $instagram = $request->instagram;
        $video = $request->video;
        $current_video = $toppage->video;
        $current_cover_photo = $toppage->cover_photo;
        $current_avatar = $toppage->avatar;

        $toppage->name = $name;
        $toppage->area = $area;
        $toppage->about = $about;
        $toppage->summary = $summary;

        if ($video || $cover_photo || $avatar) {
            $ids = [$video, $cover_photo, $avatar];
            $ids = array_filter($ids);
            CheckUsed($ids);
            if ($video) {
                $toppage->video = $video;
                $old_video = Upload::find($current_video);
                $path = str_replace(url('/') . '/storage', 'public', $old_video->url);
                Storage::delete($path);
                $old_video->delete();
            }
            if ($avatar) {
                $toppage->avatar = $avatar;
                $old_avatar = Upload::find($current_avatar);
                $path = str_replace(url('/') . '/storage', 'public', $old_avatar->url);
                Storage::delete($path);
                $old_avatar->delete();
            }
            if ($cover_photo) {
                $toppage->cover_photo = $cover_photo;
                $old_cover_photo = Upload::find($current_cover_photo);
                $path = str_replace(url('/') . '/storage', 'public', $old_cover_photo->url);
                Storage::delete($path);
                $old_cover_photo->delete();
            }
        }
        $toppage->website = $website;
        $toppage->facebook = $facebook;
        $toppage->instagram = $instagram;
        $toppage->user_id = $user_id;

        $toppage->save();

        return $this->handleSuccess($toppage, 'success');
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $toppage = $user->topPage()->first();
        $toppage->url_avatar = Upload::find($toppage->avatar)->url;
        $toppage->url_cover_photo = Upload::find($toppage->cover_photo)->url;
        $toppage->url_video = Upload::find($toppage->video)->url;

        return $this->handleSuccess($toppage, 'get success toppage');
    }
    public function changeStatus(Request $request)
    {
        $request->validate([
            'status' => 'in:published,unpublished',
        ]);

        $user = $request->user();
        $toppage = $user->topPage()->first();
        $status = $request->status;

        $toppage->status = $status;

        return $this->handleSuccess($toppage, "change status to $status");
    }
    public function updateDetails(Request $request, TopPage $top_page)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleResponse([], 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'area' => 'required|regex:/^[a-zA-Z0-9]+\/[a-zA-Z0-9]+$/',
            'about' => 'required|string|max:200',
            'summary' => 'required|string|max:1000',
            'name' => 'required',
        ]);

        $language = $request->language;

        if (!($language && in_array($language, config('app.languages')))) {
            return $this->handleResponse([], 'Not Found Language');
        }
        $top_page_detail = $top_page->topPageDetail()->where('lang', $language)->first();
        $top_page_detail->name = $request->name;
        $top_page_detail->area = $request->area;
        $top_page_detail->about = $request->about;
        $top_page_detail->summary = $request->summary;
        $top_page_detail->save();

        return $this->handleResponse($top_page_detail, 'Top page detail updated successfully');
    }
}
