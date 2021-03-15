<?php

namespace lbs\command\models;

class Command extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "commande";
    protected $primareyKey = "id";
    protected $fillable = ["id", "nom", "mail", "livraison", "token"];
    protected $hidden = ["created_at", "modified_at"];

    public $incrementing = false;
    public $keyType = "string";
}
