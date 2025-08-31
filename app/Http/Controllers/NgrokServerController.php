<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NgrokServer;

class NgrokServerController extends Controller
{
        public function update(Request $request)
        {
            $request->validate([
                'http_url' => 'required',
                'websocket_url' => 'required',
                'websocket_port' => 'required'
            ]);

            // Simpan atau update record pertama
            $ngrok = NgrokServer::firstOrNew(['id' => 1]);
            $ngrok->http_url = $request->http_url;
            $ngrok->websocket_url = $request->websocket_url;
            $ngrok->websocket_port = $request->websocket_port;
            $ngrok->save();

            return response()->json(['message' => 'Ngrok URLs updated successfully']);
        }

        public function get()
        {
            $ngrok = NgrokServer::first();
            return response()->json($ngrok);
        }
}
