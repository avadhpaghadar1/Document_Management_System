<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_type_field extends Model
{
    use HasFactory;

    protected $fillable = ['document_type_id', 'field_name', 'field_type'];
    public $timestamps = false;

    public function documentType(){
        return $this->belongsTo(Document_type::class);
    }
}
