<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerlofAanvraagMail extends Mailable
{
    use Queueable, SerializesModels;

    public $aanvraag;

    public function __construct($aanvraag)
    {
        $this->aanvraag = $aanvraag;
    }

    public function build()
    {
        return $this->subject('Je verlofaanvraag is ingediend')
            ->view('emails.verlof_aanvraag');
    }
}
