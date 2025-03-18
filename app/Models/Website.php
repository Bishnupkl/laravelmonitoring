<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Website extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 'url', 'is_down', 'last_checked_at', 'retry_count'];

    public function client(): BelongsTo {
        return $this->belongsTo(Client::class);
    }
}
