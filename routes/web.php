<?php

use App\Http\Controllers\Cobros\PaymentReceiptController;
use App\Http\Controllers\Attachments\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WhatsApp\WebhookController;
use App\Livewire\Developers\DevelopersIndex;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;
use App\Livewire\Sales\Show as SalesShow;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Settings\Whatsapp as WhatsappSettings;
use App\Models\PaymentFlow;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::bind('paymentFlow', fn (string $value) => PaymentFlow::withTrashed()->findOrFail($value));

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/webhooks/whatsapp', [WebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/webhooks/whatsapp', [WebhookController::class, 'handle'])->name('whatsapp.webhook.handle');

Route::middleware(['auth'])->group(function () {
    Route::get('/configuracion', SettingsIndex::class)->name('settings.index');
    Route::get('/configuracion/whatsapp', WhatsappSettings::class)->name('settings.whatsapp');
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
    Route::view('/ventas', 'Ventas.index')->name('ventas.index');
    Route::get('/ventas/{opportunity}', SalesShow::class)->name('ventas.show');
    Route::get('/adjuntos/{attachment}/preview', [AttachmentController::class, 'preview'])
        ->name('attachments.preview');
    Route::get('/adjuntos/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');
});

Route::middleware(['auth'])->group(function () {
    Route::view('/cobros', 'Cobros.index')->name('cobros.index');
    Route::view('/cobros/crear', 'Cobros.create')->name('cobros.create');
    Route::view('/cobros/programados/crear', 'Cobros.scheduled-create')->name('cobros.scheduled.create');
    Route::get('/cobros/{paymentFlow}', function (\App\Models\PaymentFlow $paymentFlow) {
        return view('Cobros.show', compact('paymentFlow'));
    })
        ->name('cobros.show');

    Route::get('/cobros/{paymentFlow}/comprobantes/{receipt}/preview', [PaymentReceiptController::class, 'preview'])
        ->name('cobros.receipts.preview');

    Route::get('/cobros/{paymentFlow}/comprobantes/{receipt}/download', [PaymentReceiptController::class, 'download'])
        ->name('cobros.receipts.download');
});

require __DIR__.'/auth.php';
