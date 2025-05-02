<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_detail extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable=[
        'document_id',
        'field_name',
        'field_type',
        'field_value',
    ];
    public function documents(){
        return $this->belongsTo(Document_main::class,'document_id');
    }
}
