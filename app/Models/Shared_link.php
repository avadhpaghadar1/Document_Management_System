<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shared_link extends Model
{
    use HasFactory;

    protected $table = 'shared_links';

    public $timestamps = false;

    protected $fillable = [
        'token',
        'document_id',
        'file_name',
        'created_by',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document_main::class, 'document_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
