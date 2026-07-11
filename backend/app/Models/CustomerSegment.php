<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/** A saved customer segment defined by rules. */
class CustomerSegment extends Model
{
    use BelongsToStore;

    protected $fillable = ['store_id', 'name', 'rules'];

    protected function casts(): array
    {
        return ['rules' => 'array'];
    }
}
