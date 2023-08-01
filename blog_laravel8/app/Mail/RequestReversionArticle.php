<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestReversionArticle extends Mailable
{
    use Queueable, SerializesModels;
    public $article;


    public function __construct($article)
    {
        $this->article = $article;
    }

    public function build()
    {
        $subject = 'request reversion article';

        return $this->view('notification.request_update_article')
            ->subject($subject)
            ->with([
                'article' => $this->article,
            ]);
    }
}
