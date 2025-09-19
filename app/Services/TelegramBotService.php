<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramBotService
{
    protected Api $telegram;
    protected HabitService $habitService;

    /**
     * @throws TelegramSDKException
     */
    public function __construct(HabitService $habitService)
    {
        $this->habitService = $habitService;
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    /**
     * Основна обробка webhook від Telegram
     */
    public function handleWebhook(array $update): void
    {
        try {
            if (isset($update['message'])) {
                $chatId = $update['message']['chat']['id'];
                $text   = $update['message']['text'] ?? '';

                $this->handleMessage($chatId, $text);
            }

            if (isset($update['callback_query'])) {
                $this->handleCallback($update['callback_query']);
            }
        } catch (\Throwable $e) {
            Log::error('TelegramBotService error: ' . $e->getMessage());
        }
    }

    /**
     * Обробка звичайних повідомлень користувача
     */
    protected function handleMessage(int $chatId, string $text): void
    {
        if ($state = Cache::get("state:$chatId")) {

            // FSM: додавання назви звички
            if ($state['step'] === 'adding_name') {
                Cache::put("state:$chatId", ['step' => 'adding_date', 'name' => $text], 3600);
                $this->sendMainMenu($chatId, "✍️ Введи дату у форматі YYYY-MM-DD HH:MM");
                return;
            }

            // FSM: додавання дати звички
            if ($state['step'] === 'adding_date') {
                $name = $state['name'];
                $dateTimeInput = $text ?: now()->format('Y-m-d H:i');

                try {
                    $this->habitService->add($chatId, $name, $dateTimeInput);
                    Cache::forget("state:$chatId");
                    $this->sendMainMenu($chatId, "✅ Звичка '$name' додана з датою $dateTimeInput!");
                } catch (\Throwable) {
                    $this->sendMainMenu($chatId, "⚠️ Невірний формат дати $dateTimeInput");
                }
                return;
            }

            // FSM: видалення звички
            if ($state['step'] === 'deleting') {
                $habit = $this->habitService->findByName($chatId, $text);
                if ($habit) {
                    $this->habitService->delete($habit);
                    $this->sendMainMenu($chatId, "✅ Звичка '$text' видалена!");
                } else {
                    $this->sendMainMenu($chatId, "⚠️ Звичка '$text' не знайдена");
                }
                Cache::forget("state:$chatId");
                return;
            }
        }

        // Команда /start
        if ($text === '/start') {
            $this->sendMainMenu($chatId, "Привіт! Я твій бот звичок 🚀");
            return;
        }

        // Повідомлення не впізнане
        $this->sendMainMenu($chatId, "Я не розумію. Доступні дії 👇");
    }

    /**
     * Обробка натискань inline-кнопок
     */
    protected function handleCallback(array $callback): void
    {
        $chatId = $callback['message']['chat']['id'];
        $data   = $callback['data'];

        if ($data === 'status') {
            $text = $this->habitService->getStatus($chatId);
        } elseif ($data === 'add') {
            Cache::put("state:$chatId", ['step' => 'adding_name'], 3600);
            $text = "✍️ Введи назву нової звички";
        } elseif ($data === 'delete') {
            Cache::put("state:$chatId", ['step' => 'deleting'], 3600);
            $text = "⚠️ Введи назву звички, яку хочеш видалити";
        } else {
            $text = "Невідома дія";
        }

        $this->telegram->answerCallbackQuery(['callback_query_id' => $callback['id']]);


        $this->sendMainMenu($chatId, $text);
    }

    /**
     * Відправка головного меню користувачу
     */
    public function sendMainMenu(int $chatId, string $text): void
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📊 Мої звички', 'callback_data' => 'status'],
                    ['text' => '➕ Додати', 'callback_data' => 'add'],
                    ['text' => '❌ Видалити', 'callback_data' => 'delete'],
                ],
            ],
        ];

        $this->telegram->sendMessage([
            'chat_id'      => $chatId,
            'text'         => $text,
            'parse_mode'   => 'HTML',
            'reply_markup' => json_encode($keyboard),
        ]);
    }
}
