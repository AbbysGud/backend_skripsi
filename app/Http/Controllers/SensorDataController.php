<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SensorDataController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rfid_tag' => 'required|string',
            'weight' => 'required|numeric',
        ]);

        $user = User::where('rfid_tag', $request->rfid_tag)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User dengan RFID Tag ini tidak ditemukan.',
                'input_rfid' => $request->rfid_tag,
                'database_rfid' => null,
            ], 404);
        }

        $currentTime = Carbon::now('Asia/Jakarta');
        $startOfDay = $currentTime->copy()->startOfDay();
        $endOfDay = $currentTime->copy()->endOfDay();

        $lastSensorData = SensorData::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('created_at', 'desc')
            ->first();

        $previousWeight = $lastSensorData ? $lastSensorData->weight : 0;

        $sensorData = SensorData::create([
            'user_id' => $user->id,
            'rfid_tag' => $request->rfid_tag,
            'weight' => $request->weight,
            'previous_weight' => $previousWeight,
            'created_at' => $currentTime,
            'updated_at' => $currentTime,
        ]);

        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data' => $sensorData,
        ], 201);
    }

    public function getByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->date)->format('Y-m-d');

        $sensorData = SensorData::whereDate('created_at', $date)->get();

        if ($sensorData->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data untuk tanggal ini.',
                'date' => $date,
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'date' => $date,
            'data' => $sensorData,
        ], 200);
    }

    public function getSensorDataHistoryByUserId(Request $request)
    {
        $request->validate([
            'user_id' => 'required|int',
            'today_date' => 'required|date',
        ]);

        // Filter data sebelum today_date
        $sensorData = SensorData::where('user_id', $request->user_id)
            ->where('created_at', '<', $request->today_date)
            ->get();

        if ($sensorData->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan untuk User ini sebelum tanggal tersebut.',
                'user_id' => $request->user_id,
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'user_id' => $request->user_id,
            'data' => $sensorData,
        ], 200);
    }

    public function getSensorDataByUserId(Request $request)
    {
        $request->validate([
            'user_id' => 'required|int',
        ]);

        $sensorData = SensorData::where('user_id', $request->user_id)->get();

        if ($sensorData->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan untuk User ini.',
                'user_id' => $request->user_id,
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'user_id' => $request->user_id,
            'data' => $sensorData,
        ], 200);
    }

    public function getSensorDataByDate(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'user_id' => 'nullable|int',
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        $query = SensorData::whereBetween('created_at', [$fromDate, $toDate]);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $sensorData = $query->get();

        if ($sensorData->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data untuk rentang tanggal ini.',
                'from_date' => $fromDate->format('Y-m-d'),
                'to_date' => $toDate->format('Y-m-d'),
                'user_id' => $request->user_id,
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'user_id' => $request->user_id,
            'data' => $sensorData,
        ], 200);
    }
}
