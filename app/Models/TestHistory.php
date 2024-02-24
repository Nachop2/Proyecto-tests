<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestHistory extends Model
{
    protected $table = 'user_test_plays'; // Specify the table name if it doesn't follow Laravel's naming convention

    protected $fillable = ['user_id', 'test_id', 'played_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}
