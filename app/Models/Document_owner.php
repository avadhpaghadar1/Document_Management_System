<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_owner extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable=[
        'document_id',
        'owner_id',
    ];
    public function users(){
        return $this->hasMany(Document_owner::class,'id');
    }
    public function documents()
    {
        return $this->belongsToMany(Document_main::class, 'document_owners', 'user_id', 'document_main_id');
    }
}
