<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document_main extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;
    protected $fillable = [
        'document_type_id',
        'note',
        'expiry',
        'user_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejected_reason'
    ];

    protected $casts = [
        'expiry' => 'date',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'document_owners');
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'groups');
    }
    public function documentType()
    {
        return $this->belongsTo(Document_type::class);
    }
    public function group_document_permission()
    {
        return $this->belongsToMany(Group::class, 'document_group_permissions')
            ->withPivot('view', 'edit', 'delete');
    }
    public function user_document_permission()
    {
        return $this->belongsToMany(User::class, 'document_user_permissions', 'document_main_id')
            ->withPivot('view', 'edit', 'delete');
    }

    public function documentDetail()
    {
        return $this->hasMany(Document_detail::class, 'document_id');
    }
    public function document_notifications(){
        return $this->hasMany(Document_notification::class,'document_id');
    }
    public function documentImages()
{
    return $this->hasMany(Document_image::class,'document_id');
}

    public function versions()
    {
        return $this->hasMany(Document_version::class, 'document_id');
    }

    public function sharedLinks()
    {
        return $this->hasMany(Shared_link::class, 'document_id');
    }

        public function fileAnalyses()
        {
            return $this->hasMany(Document_file_analysis::class, 'document_id');
        }
}
