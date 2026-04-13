<?php

namespace App\Services;

use App\Models\Story;
use App\Mail\MetsReadyMail;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Mail;

final class SendMetsReadyNotification
{
    public function send(?string $email, Story $story, string $status, ?Batch $batch): void
    {
        if (!$email) {
            return;
        }

        Mail::to($email)->send(new MetsReadyMail(
            story: $story,
            status: $status,
            total: $batch ? $batch->totalJobs : 0,
            processed: $batch ? $batch->processedJobs() : 0,
            failed: $batch ? $batch->failedJobs : 0,
        ));
    }
}
