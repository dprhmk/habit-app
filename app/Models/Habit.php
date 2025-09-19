<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Habit extends Model
{
    protected $fillable = [
        'chat_id', 'name', 'last_date',
    ];

    public function duration(): string
    {
        $diff = Carbon::now()->diff($this->last_date);
        return $diff->format('%a дн, %h год, %i хв, %s сек');
    }

    protected function casts(): array
    {
        return [
            'last_date' => 'datetime',
        ];
    }
}


