<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $guarded = ['id'];

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
