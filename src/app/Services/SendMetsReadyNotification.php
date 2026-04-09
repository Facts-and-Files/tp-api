<?php

namespace App\Services;

use App\Models\Story;
use App\Mail\MetsReadyMail;
use Illuminate\Support\Facades\Mail;

final class SendMetsReadyNotification
{
    public function send(?string $email, Story $story): void
    {
        if (!$email) {
            return;
        }

        Mail::to($email)->send(new MetsReadyMail($story));
    }
}
