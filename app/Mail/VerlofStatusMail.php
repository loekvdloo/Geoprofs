<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerlofStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $aanvraag;
    public $status;

    public function __construct($aanvraag, $status)
    {
        $this->aanvraag = $aanvraag;
        $this->status = $status; 
    }

    public function build()
    {
        $subject = $this->status === 'accepted' 
            ? 'Je verlofaanvraag is goedgekeurd' 
            : 'Je verlofaanvraag is afgekeurd';

        return $this->subject($subject)
                    ->view('emails.verlof_status');
    }
}
