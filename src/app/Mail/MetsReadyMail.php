<?php

namespace App\Mail;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MetsReadyMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly Story $story,
    ) {}

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.mets-ready',
            with: [
                'downloadUrl' => url("/v2/stories/{$this->story->StoryId}/items/export/mets"),
                'storyTitle'  => $this->story->Dc['Title'] ?? "Story #{$this->story->StoryId}",
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your requested METS export is ready",
        );
    }
}
