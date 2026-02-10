<?php

use App\Livewire\Developers\DevelopersIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('/proyectos', 'proyectos.index')
    ->middleware(['auth', 'verified'])
    ->name('proyectos.index');

Route::view('/clientes', 'clientes.index')
    ->middleware(['auth', 'verified'])
    ->name('clientes.index');

Route::get('/developers', DevelopersIndex::class)
    ->middleware(['auth', 'verified'])
    ->name('developers.index');

require __DIR__.'/auth.php';
