<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    public function job(){    //define relation
        return $this->belongsTo(Job::class);
    }

    public function user(){    //define relation
        return $this->belongsTo(User::class);
    }

    public function employer(){    
        return $this->belongsTo(User::class,'employer_id');
    }
}
