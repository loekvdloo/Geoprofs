<?php

namespace Tests\Unit\Mail;

use Tests\TestCase;
use App\Mail\VerlofAanvraagMail;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;

class VerlofAanvraagMailTest extends TestCase
{
    #[Test]
    public function it_sends_verlofaanvraag_mail()
    {
        Mail::fake();

        $aanvraag = (object) [
            'id' => 1,
            'user_name' => 'Jan Jansen',
        ];

        Mail::send(new VerlofAanvraagMail($aanvraag));

        Mail::assertSent(VerlofAanvraagMail::class);
    }
}
