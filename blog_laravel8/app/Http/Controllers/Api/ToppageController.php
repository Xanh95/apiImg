<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TopPageRequest;
use App\Http\Requests\TopPageStatusRequest;
use App\Models\Toppage;
use Illuminate\Http\Request;
use App\Models\Upload;
use Illuminate\Support\Facades\Storage;
use App\Models\TopPageDetail;
use App\Models\User;


class ToppageController extends ResponseApiController
{
    public $languages;

    public function __construct()
    {
        $this->languages = config('app.languages');
    }

    public function store(TopPageRequest $request)

    {
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

        $top_page->name = $name;
        $top_page->area = $area;
        $top_page->video = $video;
        $top_page->about = $about;
        $top_page->summary = $summary;
        $top_page->cover_photo = $cover_photo;
        if ($video || $cover_photo || $avatar) {
            $ids = [$video, $cover_photo, $avatar];
            $ids = array_filter($ids);
            CheckUsed($ids); // kiểm tra những ảnh,video tạo ra đã dùng và xóa những ảnh hoặc video không dùng đi
        }
        $top_page->avatar = $avatar;
        $top_page->website = $website;
        $top_page->facebook = $facebook;
        $top_page->instagram = $instagram;
        $top_page->user_id = $user_id;
        $top_page->save();
        foreach ($this->languages as $language) {
            $top_page_detail = new TopPageDetail;
            $top_page_detail->name = translate($language, $name);
            $top_page_detail->area = translate($language, $area);
            $top_page_detail->about = translate($language, $about);
            $top_page_detail->summary = translate($language, $summary);
            $top_page_detail->toppage_id = $top_page->id;
            $top_page_detail->language = $language;
            $top_page_detail->save();
        }

        return $this->handleSuccess($top_page, 'success');
    }

    public function update(TopPageRequest $request, User $user)
    {
        if (!$request->user()->hasPermission('update') && $request->user()->id != $user->id) {
            return $this->handleError('Unauthorized edit top page', 403);
        }

        $top_page = $user->topPage()->first();
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

        $top_page->name = $name;
        $top_page->area = $area;
        $top_page->about = $about;
        $top_page->summary = $summary;

        if ($video || $cover_photo || $avatar) {
            $ids = [$video, $cover_photo, $avatar];
            $ids = array_filter($ids);
            CheckUsed($ids); // kiểm tra những ảnh,video tạo ra đã dùng và xóa những ảnh hoặc video không dùng đi
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
        $top_page->user_id = $user->id;
        $top_page->save();
        $top_page->topPageDetail()->delete();
        foreach ($this->languages as $language) {
            $top_page_detail = new TopPageDetail;
            $top_page_detail->name = translate($language, $name);
            $top_page_detail->area = translate($language, $area);
            $top_page_detail->about = translate($language, $about);
            $top_page_detail->summary = translate($language, $summary);
            $top_page_detail->toppage_id = $top_page->id;
            $top_page_detail->language = $language;
            $top_page_detail->save();
        }

        return $this->handleSuccess($top_page, 'success');
    }

    public function edit(Request $request, User $user)
    {
        $language = $request->language;
        $top_page = $user->topPage()->first();

        if ($top_page) {
            if ($top_page->avatar) {
                $top_page->avatar = Upload::find($top_page->avatar)->url;
            }
            if ($top_page->cover_photo) {
                $top_page->cover_photo = Upload::find($top_page->cover_photo)->url;
            }
            if ($top_page->video) {
                $top_page->video = Upload::find($top_page->video)->url;
            }
            if ($language) {
                $top_page->top_page_detail = $top_page->topPageDetail()->where('language', $language)->get();
            }
            return $this->handleSuccess($top_page, 'get success toppage');
        }

        return $this->handleError('do not have top page', 404);
    }

    public function changeStatus(TopPageStatusRequest $request)
    {
        $user = $request->user();
        $top_page = $user->topPage()->first();
        $status = $request->status;

        $top_page->status = $status;

        return $this->handleSuccess($top_page, "change status to $status");
    }

    public function updateDetails(TopPageRequest $request, User $user)
    {
        if (!$request->user()->hasPermission('update') && $request->user()->id != $user->id) {
            return $this->handleError('Unauthorized edit top page', 403);
        }

        $language = $request->language;
        $top_page = $user->topPage()->first();

        if (!($language && in_array($language, config('app.languages')))) {
            return $this->handleResponse([], 'Not Found Language');
        }
        $top_page_detail = $top_page->topPageDetail()->where('language', $language)->first();
        $top_page_detail->name = $request->name;
        $top_page_detail->area = $request->area;
        $top_page_detail->about = $request->about;
        $top_page_detail->summary = $request->summary;
        $top_page_detail->save();

        return $this->handleResponse($top_page_detail, 'Top page detail updated successfully');
    }
}
