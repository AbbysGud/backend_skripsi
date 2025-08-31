<?php

namespace App\Http\Controllers;

use App\Models\SuhuData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SuhuDataController extends Controller
{
    /**
     * Menyimpan data suhu dan kelembaban.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi data input
        $validator = Validator::make($request->all(), [
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simpan data ke database
        $suhuData = SuhuData::create([
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'device_id' => $request->device_id,
            'created_at' => Carbon::now('Asia/Jakarta'),
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return response()->json([
            'message' => 'Data suhu dan kelembaban berhasil disimpan',
            'data' => $suhuData,
        ], 201);
    }

    /**
     * Mengambil data suhu dan kelembaban untuk rentang tanggal tertentu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByDateRange(Request $request)
    {
        // Validasi input tanggal
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        // Ambil data dari database
        $suhuData = SuhuData::whereBetween('created_at', [$fromDate, $toDate])
                            ->get();

        if ($suhuData->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data suhu dan kelembaban untuk rentang tanggal ini.',
                'from_date' => $fromDate->format('Y-m-d H:i:s'),
                'to_date' => $toDate->format('Y-m-d H:i:s'),
            ], 404);
        }

        return response()->json([
            'message' => 'Data suhu dan kelembaban ditemukan',
            'from_date' => $fromDate->format('Y-m-d H:i:s'),
            'to_date' => $toDate->format('Y-m-d H:i:s'),
            'data' => $suhuData,
        ], 200);
    }

    /**
     * Mengambil semua data suhu dan kelembaban.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $suhuData = SuhuData::all();

        if ($suhuData->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data suhu dan kelembaban.',
            ], 404);
        }

        return response()->json([
            'message' => 'Data suhu dan kelembaban ditemukan',
            'data' => $suhuData,
        ], 200);
    }
    
    /**
     * Mengambil data suhu dan kelembaban terakhir berdasarkan device_id.
     *
     * @param  string  $deviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLastData($deviceId)
    {
        $suhuData = SuhuData::where('device_id', $deviceId)
            ->latest() // Mengambil data terakhir berdasarkan kolom 'created_at'
            ->first();

        if (!$suhuData) {
            return response()->json([
                'message' => 'Tidak ada data suhu dan kelembaban untuk perangkat ini.',
                'device_id' => $deviceId,
            ], 404);
        }

        return response()->json([
            'message' => 'Data suhu dan kelembaban terakhir ditemukan',
            'data' => $suhuData,
        ], 200);
    }
}