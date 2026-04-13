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
        private readonly string $status,
        private readonly int $total,
        private readonly int $processed,
        private readonly int $failed,
    ) {}

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.mets-ready',
            with: [
                'downloadUrl' => url("/v2/stories/{$this->story->StoryId}/items/export/mets"),
                'storyTitle' => $this->story->Dc['Title'] ?? "Story #{$this->story->StoryId}",
                'storyId' => $this->story->StoryId,
                'introText' => $this->buildIntroText(),
                'status' => $this->status,
                'total' => $this->total,
                'processed' => $this->processed,
                'failed' => $this->failed,
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->resolveSubject(),
        );
    }

    private function resolveSubject(): string
    {
        return match ($this->status) {
            'ready' => 'Your requested METS export is ready',
            'finished_with_failures' => 'Your METS export finished with issues',
            'failed' => 'Your METS export could not be completed',
            'cancelled' => 'Your METS export was cancelled',
            default => 'Update on your METS export request',
        };
    }

    private function buildIntroText(): string
    {
        return match ($this->status) {
            'ready' => 'The METS export has been prepared successfully and is now ready to download.',
            'finished_with_failures' => 'The METS export process finished, but some item conversions failed or were skipped.',
            'failed' => 'The METS export process could not be completed successfully.',
            'cancelled' => 'The METS export process was cancelled before completion.',
            default => 'There is an update for your requested METS export.',
        };
    }
}
