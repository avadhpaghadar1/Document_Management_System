<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_group_permission extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'document_id',
        'group_id',
        'permissions',
    ];
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
