<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Index extends Model
{
    public $table = 'news';

    public $pk = 'id';

    public function test()
    {
        $res=DB::table("user")->select("uid")->first();
        return get_object_vars($res);
    }
}
