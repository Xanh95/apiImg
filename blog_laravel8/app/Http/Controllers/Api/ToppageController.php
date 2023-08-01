<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toppage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Upload;
use Illuminate\Support\Facades\Storage;
use App\Models\TopPageDetail;


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
            return $this->handleError('had top page', 422);
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
        $top_page = new Toppage;
        $languages = config('app.languages');

        $top_page->name = $name;
        $top_page->area = $area;
        $top_page->video = $video;
        $top_page->about = $about;
        $top_page->summary = $summary;
        $top_page->cover_photo = $cover_photo;
        if ($video || $cover_photo || $avatar) {
            $ids = [$video, $cover_photo, $avatar];
            $ids = array_filter($ids);
            CheckUsed($ids);
        }
        $top_page->avatar = $avatar;
        $top_page->website = $website;
        $top_page->facebook = $facebook;
        $top_page->instagram = $instagram;
        $top_page->user_id = $user_id;
        $top_page->save();
        foreach ($languages as $language) {
            $top_page_detail = new TopPageDetail;
            $top_page_detail->name = translate($language, $name);
            $top_page_detail->area = translate($language, $area);
            $top_page_detail->about = translate($language, $about);
            $top_page_detail->summary = translate($language, $summary);
            $top_page_detail->top_page_id = $top_page->id;
            $top_page_detail->language = $language;
            $top_page_detail->save();
        }


        return $this->handleSuccess($top_page, 'success');
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
        $top_page = Toppage::where('user_id', $user_id)->first();
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
        $current_video = $top_page->video;
        $current_cover_photo = $top_page->cover_photo;
        $current_avatar = $top_page->avatar;
        $languages = config('app.languages');

        $top_page->name = $name;
        $top_page->area = $area;
        $top_page->about = $about;
        $top_page->summary = $summary;

        if ($video || $cover_photo || $avatar) {
            $ids = [$video, $cover_photo, $avatar];
            $ids = array_filter($ids);
            CheckUsed($ids);
            if ($video) {
                $top_page->video = $video;
                $old_video = Upload::find($current_video);
                $path = str_replace(url('/') . '/storage', 'public', $old_video->url);
                Storage::delete($path);
                $old_video->delete();
            }
            if ($avatar) {
                $top_page->avatar = $avatar;
                $old_avatar = Upload::find($current_avatar);
                $path = str_replace(url('/') . '/storage', 'public', $old_avatar->url);
                Storage::delete($path);
                $old_avatar->delete();
            }
            if ($cover_photo) {
                $top_page->cover_photo = $cover_photo;
                $old_cover_photo = Upload::find($current_cover_photo);
                $path = str_replace(url('/') . '/storage', 'public', $old_cover_photo->url);
                Storage::delete($path);
                $old_cover_photo->delete();
            }
        }
        $top_page->website = $website;
        $top_page->facebook = $facebook;
        $top_page->instagram = $instagram;
        $top_page->user_id = $user_id;
        $top_page->save();
        $top_page->topPageDetail()->delete();
        foreach ($languages as $language) {
            $top_page_detail = new TopPageDetail;
            $top_page_detail->name = translate($language, $name);
            $top_page_detail->area = translate($language, $area);
            $top_page_detail->about = translate($language, $about);
            $top_page_detail->summary = translate($language, $summary);
            $top_page_detail->top_page_id = $top_page->id;
            $top_page_detail->language = $language;
            $top_page_detail->save();
        }

        return $this->handleSuccess($top_page, 'success');
    }

    public function edit(Request $request)
    {

        $language = $request->language;
        $user = $request->user();
        $top_page = $user->topPage()->first();

        $top_page->url_avatar = Upload::find($top_page->avatar)->url;
        $top_page->url_cover_photo = Upload::find($top_page->cover_photo)->url;
        $top_page->url_video = Upload::find($top_page->video)->url;
        if ($language) {
            $top_page->top_page_detail = $top_page->topPageDetail()->where('language', $language)->get();
        }
        return $this->handleSuccess($top_page, 'get success toppage');
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
        if ($request->user()->hasPermission('update')) {
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
