<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';
    protected $description = 'Встановлює webhook для Telegram-бота';

    /**
     * Виконання команди.
     */
    public function handle(): int
    {
        $url = route('telegram.webhook');
        $token = config('telegram.bots.mybot.token');

        if (!$token || $token === 'YOUR-BOT-TOKEN') {
            $this->error('❌ У .env не вказано TELEGRAM_BOT_TOKEN');
            return self::FAILURE;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $url,
        ]);

        if ($response->successful()) {
            $this->info("✅ Webhook встановлено: {$url}");
        } else {
            $this->error("❌ Помилка: " . $response->body());
        }

        return self::SUCCESS;
    }
}
