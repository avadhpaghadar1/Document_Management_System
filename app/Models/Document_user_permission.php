<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_user_permission extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'document_id',
        'user_id',
        'permissions',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
