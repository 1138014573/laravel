<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class User extends Controller
{
    public function Index()
    {
        var_dump(config("view.env"));
        echo 666666;die;
    }
}
