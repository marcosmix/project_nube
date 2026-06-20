<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Models\OpportunityAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class OpportunityAttachmentController extends Controller
{
    public function preview(Opportunity $opportunity, OpportunityAttachment $attachment): Response
    {
        abort_unless($attachment->opportunity_id === $opportunity->getKey(), 404);
        abort_unless($attachment->isImage() || $attachment->isPdf(), 404);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->path), 404);

        return $disk->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type ?: ($disk->mimeType($attachment->path) ?: 'application/octet-stream'),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Opportunity $opportunity, OpportunityAttachment $attachment): Response
    {
        abort_unless($attachment->opportunity_id === $opportunity->getKey(), 404);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->path), 404);

        return response()->download(
            $disk->path($attachment->path),
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type ?: 'application/octet-stream']
        );
    }
}
