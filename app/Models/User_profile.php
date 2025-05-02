<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_profile extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    public $primaryKey = null;
    protected $fillable = [
        'user_id',
        'image',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
