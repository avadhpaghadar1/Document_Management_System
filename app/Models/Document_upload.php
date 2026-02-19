<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document_upload extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'file_name',
        'mime_type',
        'file_size',
        'sha256',
        'pdf_page_count',
        'image_width',
        'image_height',
        'exif',
        'analyzed_at',
        'ocr_text',
        'ocr_error',
        'ocr_engine',
        'ocr_language',
        'ocr_completed_at',
        'created_at',
    ];

    protected $casts = [
        'exif' => 'array',
        'analyzed_at' => 'datetime',
        'ocr_completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
