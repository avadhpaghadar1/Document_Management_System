<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_notification extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    public $primaryKey = null;
    protected $fillable = [ 'name','document_id', 'day'];
    public function document()
    {
        return $this->belongsTo(Document_main::class);
    }
}
