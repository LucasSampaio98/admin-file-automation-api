<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientFormat extends Model
{
    // Especifica a tabela associada a este modelo
    protected $table = 'client_formats';

    // Especifica os campos que podem ser atribuídos em massa
    protected $fillable = ['client_id', 'format_id'];

    // Relacionamento com Format
    public function format()
    {
        return $this->belongsTo(Format::class, 'format_id');
    }

    // Relacionamento com Client (assumindo que você tem um modelo Client)
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}