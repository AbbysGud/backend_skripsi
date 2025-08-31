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
            'ganti_botol' => 'boolean',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'device_id' => 'nullable|string',
        ]);

        $user = User::where('rfid_tag', $request->rfid_tag)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User dengan RFID Tag ini tidak ditemukan.',
                'input_rfid' => $request->rfid_tag,
                'database_rfid' => null,
            ], 404);
        }
        
        if ($request->filled('device_id')) {
            $user->device_id = $request->device_id;
            $user->save();
        }

        $currentTime = Carbon::now('Asia/Jakarta');
        $startOfDay = $currentTime->copy()->subDay()->startOfDay();
        $endOfDay = $currentTime->copy()->endOfDay();

        $lastSensorData = SensorData::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($request->ganti_botol) {
            $previousWeight = 0;
        } else {
            $previousWeight = $lastSensorData ? $lastSensorData->weight : 0;
        }

        $sensorData = SensorData::create([
            'user_id' => $user->id,
            'rfid_tag' => $request->rfid_tag,
            'weight' => $request->weight,
            'previous_weight' => $previousWeight,
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'device_id' => $request->device_id,
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
            'waktu_mulai' => 'nullable|date_format:H:i:s',
            'waktu_selesai' => 'nullable|date_format:H:i:s',
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        if ($request->filled('waktu_mulai')) {
            $fromDate->setTimeFromTimeString($request->waktu_mulai);
        }
    
        if ($request->filled('waktu_selesai')) {
            $toDate->setTimeFromTimeString($request->waktu_selesai);
        }

        $query = SensorData::whereBetween('created_at', [$fromDate, $toDate]);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $sensorData = $query->get();

        if ($sensorData->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data untuk rentang tanggal ini.',
                'from_date' => $fromDate->format('Y-m-d H:i:s'),
                'to_date' => $toDate->format('Y-m-d H:i:s'),
                'user_id' => $request->user_id,
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'from_date' => $fromDate->format('Y-m-d H:i:s'),
            'to_date' => $toDate->format('Y-m-d H:i:s'),
            'user_id' => $request->user_id,
            'data' => $sensorData,
        ], 200);
    }
    
    /**
     * Mengambil data hidrasi terakhir untuk hari ini, termasuk pengisian botol
     * dan peristiwa minum terakhir dengan detail waktu.
     */
    public function getBottleEventToday(Request $request)
    {
        $request->validate([
            'user_id' => 'required|int',
        ]);

        $userId = $request->user_id;
        $currentTime = Carbon::now('Asia/Jakarta');
        $startOfDay = $currentTime->copy()->startOfDay();
        $endOfDay = $currentTime->copy()->endOfDay();
    
        $lastBottleFill = SensorData::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where(function ($query) {
                $query->whereRaw('previous_weight < weight')
                      ->orWhere('previous_weight', 0);
            })
            ->orderBy('created_at', 'desc')
            ->first();

        $lastDrinkEvent = SensorData::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereRaw('weight < previous_weight')
            ->whereRaw('previous_weight - weight >= 20')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $previousDrinkEvent = null;
        if ($lastDrinkEvent) {
            $previousDrinkEvent = SensorData::where('user_id', $userId)
                ->where('created_at', '<', $lastDrinkEvent->created_at)
                ->orderBy('created_at', 'desc')
                ->first();
        }
    
        if (!$lastBottleFill && !$lastDrinkEvent) {
            return response()->json([
                'message' => 'Tidak ada data hidrasi yang valid ditemukan untuk pengguna ini hari ini.',
                'user_id' => $userId,
            ], 404);
        }
    
        return response()->json([
            'message' => 'Data hidrasi terakhir untuk hari ini berhasil ditemukan.',
            'user_id' => $userId,
            'last_bottle_fill_or_new' => $lastBottleFill,
            'last_drink_event' => $lastDrinkEvent,
            'previous_drink_event' => $previousDrinkEvent,
        ], 200);
    }
}
