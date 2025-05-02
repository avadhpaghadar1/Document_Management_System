<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_audit extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable=[
        'document_id',
        'document_type_id',
        'user_id',
        'action',
    ];
    public function documentType()
{
    return $this->belongsTo(Document_type::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
}
