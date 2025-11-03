<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    // protected $fillable = [
    //     'parent_id', 'user_id', 'message', // adjust fields
    // ];
    protected $fillable = [
    'name', 'email', 'message', 'parent_id', 'created_by', // etc.
];


// Contact.php
public function creator() {
    return $this->belongsTo(User::class, 'created_by');
}

public function receiver() {
    return $this->belongsTo(User::class, 'parent_id');
}

public function replies() {
    return $this->hasMany(Contact::class, 'parent_id'); // adjust if replies are linked differently
}


}
