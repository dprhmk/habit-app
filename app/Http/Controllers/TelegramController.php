<?php

namespace App\Http\Controllers;

use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function webhook(Request $request, TelegramBotService $bot)
    {
        try {
            $bot->handleWebhook($request->all());
        } catch (\Throwable $e) {
            Log::error('Telegram handler error: '.$e->getMessage(), ['ex' => $e]);
        }
        return response()->json(['ok' => true]);
    }
}
