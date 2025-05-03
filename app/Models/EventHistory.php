<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventHistory extends Model
{
    protected $table = 'event_history';
    public $timestamps = false;
    protected $guarded = [];

    public function getTable()
    {
        return $this->table;
    }
}
