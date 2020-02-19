<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Queue_item extends Model
{
    //
    protected $fillable = ['submission_id', 'type', 'processid'];
    public function submission()
    {
        return $this->belongsTo('App\Submission', 'foreign_key', 'other_key');
    }
}
