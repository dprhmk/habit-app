<?php

namespace App\Console\Commands;

use App\Models\Habit;
use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class SendHabitsCommand extends Command
{
    protected $signature = 'habits:send-daily';
    protected $description = 'Надсилає щоденний список звичок кожному користувачу, якщо є';

    protected TelegramBotService $bot;

    public function __construct(TelegramBotService $bot)
    {
        parent::__construct();
        $this->bot = $bot;
    }

    public function handle(): void
    {
        $chatIds = Habit::distinct()->pluck('chat_id');

        foreach ($chatIds as $chatId) {
            $text = $this->bot->getStatus($chatId);

            if (str_contains($text, 'немає звичок')) {
                continue;
            }

            $this->bot->sendMainMenu($chatId, $text);
        }

        $this->info('Щоденні нагадування успішно надіслані!');
    }
}
