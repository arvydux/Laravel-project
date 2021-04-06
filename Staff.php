<?php

namespace App\Models;

use App\Models\Generic\Resoursable;
use App\Models\Generic\Groupable;
use App\Models\Generic\Userable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Resoursable
{
    use Groupable, Userable;


}