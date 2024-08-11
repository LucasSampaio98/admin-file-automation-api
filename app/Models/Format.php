<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
    // Especifica a tabela associada a este modelo
    protected $table = 'formats';

    // Especifica os campos que podem ser atribuídos em massa
    protected $fillable = ['format_name'];

    // Relacionamento com ClientFormat
    public function clientFormats()
    {
        return $this->hasMany(ClientFormat::class, 'format_id');
    }
}