<?php

use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

function CheckUsed($url_ids)
{
    foreach ($url_ids as $url_id) {
        $upload = Upload::find($url_id);
        $upload->status = 'true';
        $upload->save();
    }
    $falsies = Upload::where('status', 'false')->where('user_id', Auth::id())->get();
    foreach ($falsies as $false) {
        $path = str_replace(url('/') . '/storage', 'public', $false->url);
        Storage::delete($path);
        $false->delete();
    }
}
