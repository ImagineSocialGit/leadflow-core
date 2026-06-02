<?php

use App\Http\Controllers\CRM\ContactController;
use App\Http\Controllers\CRM\ContactNoteController;
use App\Http\Controllers\CRM\ContactTaskController;
use App\Http\Controllers\CRM\WebinarController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('crm.index');

    Route::get('/webinars', [WebinarController::class, 'index'])
        ->name('crm.webinar-series.index');

    Route::post('/webinar-series', [WebinarController::class, 'storeSeries'])
        ->name('crm.webinar-series.store');

    Route::post('/webinar-series/sync', [WebinarController::class, 'syncSeries'])
        ->name('crm.webinar-series.sync');

    Route::post('/webinar-series/{series}/fix-active', [WebinarController::class, 'fixActive'])
        ->name('crm.webinar-series.fix-active');

    Route::prefix(config('contacts.routes.plural'))
        ->name('crm.contacts.')
        ->group(function () {

        Route::get('/', [ContactController::class, 'index'])
            ->name('index');

        Route::get('/{contact}', [ContactController::class, 'show'])
            ->name('show');

        Route::post('/{contact}/notes', [ContactNoteController::class, 'store'])
            ->name('notes.store');

        Route::post('/{contact}/tasks', [ContactTaskController::class, 'store'])
            ->name('tasks.store');

        Route::patch('/{contact}/notes/{note}', [ContactNoteController::class, 'update'])
            ->name('notes.update');

        Route::delete('/{contact}/notes/{note}', [ContactNoteController::class, 'destroy'])
            ->name('notes.destroy');

        Route::patch(
            '/{contact}/tasks/{task}/complete',
            [ContactTaskController::class, 'complete']
        )->name('tasks.complete');

        Route::patch(
            '/{contact}/tasks/{task}/reopen',
            [ContactTaskController::class, 'reopen']
        )->name('tasks.reopen');

        Route::patch(
            '/{contact}/registrations/{registration}/convert',
            [ContactController::class, 'markConverted']
        )->name('registrations.convert');
    });

});
