<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OtpMail extends Mailable 
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }
    public function build()
    {
        return $this->view('emails.otp')
                    ->subject('Your One-Time Password')
                    ->with('otp', $this->otp);
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PPDBRIEFER | Your OTP for Login',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}

