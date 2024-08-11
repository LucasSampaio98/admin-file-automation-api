<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $table = 'folders';

    public $timestamps = false;

    protected $fillable = ['client_id', 'folder_name'];
}
