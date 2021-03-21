<?php

namespace lbs\command\models;

class Item extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "item";
    protected $primareyKey = "id";
    protected $fillable = ["id", "uri", "libelle", "tarif", "quantite", "command_id"];
    public $timestamps = false;
    public $incrementing = false;
    public $keyType = "string";
}
