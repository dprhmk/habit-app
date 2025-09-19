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

    public function findByName(int $chatId, string $name)
    {
        return Habit::where('chat_id', $chatId)->where('name', $name)->first();
    }

    public function delete(Habit $habit)
    {
        $habit->delete();
    }
}
