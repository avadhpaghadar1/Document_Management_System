<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_file_analysis extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'file_name',
        'mime_type',
        'file_size',
        'sha256',
        'pdf_page_count',
        'image_width',
        'image_height',
        'exif',
        'ocr_text',
        'ocr_error',
        'ocr_engine',
        'ocr_language',
        'ocr_completed_at',
        'analyzed_at',
    ];

    protected $casts = [
        'exif' => 'array',
        'analyzed_at' => 'datetime',
        'ocr_completed_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document_main::class, 'document_id');
    }
}
