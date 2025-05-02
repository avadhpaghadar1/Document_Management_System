<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'user_id'
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_group');
    }
    public function documents()
    {
        return $this->belongsToMany(Document_main::class, 'document_owners');
    }
    public function document_main()
    {
        return $this->belongsToMany(Document_main::class, 'document_group_permissions')
            ->withPivot('view', 'edit', 'delete');
    }
}
