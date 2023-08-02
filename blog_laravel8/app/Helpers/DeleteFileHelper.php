<?php

use App\Models\Upload;
use Illuminate\Support\Facades\Storage;

function deleteFile($data)
{

    if ($data) {
        $data = explode('-', $data);
        foreach ($data as $current_url_id) {
            $image = Upload::find($current_url_id);
            $path = str_replace(url('/') . '/storage', 'public', $image->url);
            Storage::delete($path);
            $image->delete();
        }
    }
}
