<?php

namespace lbs\fidelisation\models;

class Carte_fidelite extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "carte_fidelite";
    protected $primareyKey = "id";
    public $incrementing = false;
    public $keyType = "string";
}