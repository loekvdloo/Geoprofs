<?php

namespace Tests\Unit\Mail;

use Tests\TestCase;
use App\Mail\VerlofStatusMail;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;

class VerlofStatusMailTest extends TestCase
{
    #[Test]
    public function it_sends_verlof_status_mail_when_accepted()
    {
        Mail::fake();

        $aanvraag = (object) ['id' => 1];
        $status = 'accepted';

        Mail::send(new VerlofStatusMail($aanvraag, $status));

        Mail::assertSent(VerlofStatusMail::class);
    }

    #[Test]
    public function it_sends_verlof_status_mail_when_rejected()
    {
        Mail::fake();

        $aanvraag = (object) ['id' => 1];
        $status = 'rejected';

        Mail::send(new VerlofStatusMail($aanvraag, $status));

        Mail::assertSent(VerlofStatusMail::class);
    }
}
