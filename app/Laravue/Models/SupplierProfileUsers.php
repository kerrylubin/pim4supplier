<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplierProfileUsers extends Model
{
    protected $table = 'supplier_profile_users';

    public function user()
    {
      return $this->hasOne('App\Models\User');
    }
}
