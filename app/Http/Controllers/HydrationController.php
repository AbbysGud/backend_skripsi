<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\HydrationUpdated;

class HydrationController extends Controller
{
    public function updateHydration(Request $request)
    {
        $data = $request->input('data');

        event(new HydrationUpdated($data));

        return response()->json(['message' => 'Data berhasil diperbarui', 'data' => $data]);
    }
}
