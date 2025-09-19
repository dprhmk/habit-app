<?php

use App\Http\Controllers\TelegramController;

Route::post('telegram/webhook', [TelegramController::class, 'webhook'])->name('telegram.webhook');