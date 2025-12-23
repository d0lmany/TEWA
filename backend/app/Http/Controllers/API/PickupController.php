<?php

namespace App\Http\Controllers\API;

use App\Models\Pickup;
use App\Http\Controllers\Controller;

class PickupController extends Controller
{
    public function index()
    {
        return Pickup::paginate(20);
    }

    /*public function store(Request $request)
    {
        //
    }

    public function show(Pickup $pickup)
    {
        //
    }

    public function update(Request $request, Pickup $pickup)
    {
        //
    }

    public function destroy(Pickup $pickup)
    {
        //
    }*/
}
