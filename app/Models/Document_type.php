<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_type extends Model
{
    use HasFactory;

    protected $fillable = ['name','user_id'];

    public $timestamps = false;
    public function documentFields()
    {
        return $this->hasMany(Document_type_field::class);
    }
    public function documents()
    {
        return $this->hasMany(Document_main::class, 'document_type_id');
    }
    public function documentAudits()
    {
        return $this->hasMany(Document_audit::class);
    }
}
