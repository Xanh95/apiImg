<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyPin extends Mailable
{
    use Queueable, SerializesModels;

    protected $pin;

    public function __construct($pin)
    {
        $this->pin = $pin;
    }

    public function build()
    {
        return $this->subject('Authentication your account')
            ->view('auth.verify_pin')
            ->with([
                'pin' => $this->pin,
            ]);
    }
}
