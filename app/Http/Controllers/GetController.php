<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;

class GetController extends Controller
{
    //
    public function salonList(){
        $provider = Provider::all();
        return ResponseMethod('Salon List',$provider);
    }
}
