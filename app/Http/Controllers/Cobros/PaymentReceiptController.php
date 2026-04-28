<?php

namespace App\Http\Controllers\Cobros;

use App\Http\Controllers\Controller;
use App\Models\PaymentFlow;
use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentReceiptController extends Controller
{
    public function preview(PaymentFlow $paymentFlow, PaymentReceipt $receipt): BinaryFileResponse
    {
        $receipt->loadMissing('payment.installment');

        abort_unless($receipt->payment?->installment?->payment_flow_id === $paymentFlow->getKey(), 404);
        abort_unless($receipt->isImage(), 404);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($receipt->disk);

        abort_unless($disk->exists($receipt->path), 404);

        return $disk->response($receipt->path, $receipt->original_name, [
            'Content-Type' => $receipt->mime_type ?: ($disk->mimeType($receipt->path) ?: 'application/octet-stream'),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(PaymentFlow $paymentFlow, PaymentReceipt $receipt): BinaryFileResponse
    {
        $receipt->loadMissing('payment.installment');

        abort_unless($receipt->payment?->installment?->payment_flow_id === $paymentFlow->getKey(), 404);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($receipt->disk);

        abort_unless($disk->exists($receipt->path), 404);

        return $disk->download($receipt->path, $receipt->original_name, [
            'Content-Type' => $receipt->mime_type ?: ($disk->mimeType($receipt->path) ?: 'application/octet-stream'),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
