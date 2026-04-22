<?php

use App\Livewire\Developers\DevelopersIndex;
use Illuminate\Support\Facades\Route;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;
use App\Livewire\Cobros\Create as CobrosCreate;
use App\Livewire\Cobros\Index as CobrosIndex;
use App\Livewire\Cobros\Show as CobrosShow;
use App\Models\PaymentFlow;

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
    Route::view('/cobros/crear', "Cobros.create")->name('cobros.create');
    Route::get('/cobros/{paymentFlow}', function(PaymentFlow $paymentFlow){
        return view('Cobros.show',compact('paymentFlow'));
    })
    ->name('cobros.show');
});

require __DIR__.'/auth.php';
