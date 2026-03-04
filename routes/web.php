<?php

use App\Livewire\Developers\DevelopersIndex;
use Illuminate\Support\Facades\Route;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectsShow;

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


require __DIR__.'/auth.php';
