<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $fillable = ['username', 'password', 'role'];

    // O Laravel/Lumen por padrão espera uma coluna 'id' como chave primária.
    protected $hidden = ['password']; // Oculta a senha quando o modelo é convertido para array ou JSON.
}
