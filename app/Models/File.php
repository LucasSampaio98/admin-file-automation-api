<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'files';

    public $timestamps = false;

    protected $fillable = ['folder_id', 'file_name', 'file_path', 'uploaded_by'];
}
