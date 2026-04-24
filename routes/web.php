<?php

use App\Livewire\Developers\DevelopersIndex;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;
use App\Models\PaymentFlow;
use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('/proyectos', ProjectsIndex::class)->name('proyectos.index');
    Route::get('/proyectos/{project}', ProjectsShow::class)->name('proyectos.show');
});

Route::view('/clientes', 'clientes.index')
    ->middleware(['auth', 'verified'])
    ->name('clientes.index');

Route::get('/developers', DevelopersIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('developers.index');

Route::middleware(['auth'])->group(function () {
    Route::view('/cobros', 'Cobros.index')->name('cobros.index');
    Route::view('/cobros/crear', 'Cobros.create')->name('cobros.create');
    Route::get('/cobros/{paymentFlow}', function (PaymentFlow $paymentFlow) {
        return view('Cobros.show', compact('paymentFlow'));
    })
        ->name('cobros.show');

    Route::get('/cobros/{paymentFlow}/comprobantes/{receipt}/preview', function (PaymentFlow $paymentFlow, PaymentReceipt $receipt) {
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
    })->name('cobros.receipts.preview');

    Route::get('/cobros/{paymentFlow}/comprobantes/{receipt}/download', function (PaymentFlow $paymentFlow, PaymentReceipt $receipt) {
        $receipt->loadMissing('payment.installment');

        abort_unless($receipt->payment?->installment?->payment_flow_id === $paymentFlow->getKey(), 404);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($receipt->disk);

        abort_unless($disk->exists($receipt->path), 404);

        return $disk->download($receipt->path, $receipt->original_name, [
            'Content-Type' => $receipt->mime_type ?: ($disk->mimeType($receipt->path) ?: 'application/octet-stream'),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    })->name('cobros.receipts.download');
});

require __DIR__.'/auth.php';
