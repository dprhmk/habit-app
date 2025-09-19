<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Habit;
use Carbon\Carbon;

class HabitService
{
    public function getAll(int $chatId)
    {
        return Habit::where('chat_id', $chatId)->get();
    }

    public function add(int $chatId, string $name, string $date)
    {
        return Habit::create([
            'chat_id'   => $chatId,
            'name'      => $name,
            'last_date' => Carbon::parse($date),
        ]);
    }

    public function reset(Habit $habit): void
    {
        $habit->last_date = Carbon::now();
        $habit->save();
    }

    public function getStatus(int $chatId): string
    {
        $habits = $this->getAll($chatId);
        if ($habits->isEmpty()) return "У тебе ще немає звичок!";

        $text = "\n";
        foreach ($habits as $habit) {
            $text .= "{$habit->name}: {$habit->duration()}\n";
        }

        return $text;
    }

    public function findByName(int $chatId, string $name)
    {
        return Habit::where('chat_id', $chatId)->where('name', $name)->first();
    }

    public function delete(Habit $habit)
    {
        $habit->delete();
    }
}
