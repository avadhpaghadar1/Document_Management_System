<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_image extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable=[
        'document_id',
        'name',
    ];
    public function document()
    {
        return $this->belongsTo(Document_main::class);
    }
}
