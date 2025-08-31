<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use App\Models\Kota;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    // Mengambil semua data provinsi
    public function getAllProvinsi()
    {
        $provinsi = Provinsi::all();
        return response()->json($provinsi);
    }

    // Mengambil data kota berdasarkan id_provinsi
    public function getKotaByProvinsi($id_provinsi)
    {
        $kota = Kota::where('id_provinsi', $id_provinsi)->get();
        return response()->json($kota);
    }

    // Mengambil data kecamatan berdasarkan id_kota
    public function getKecamatanByKota($id_kota)
    {
        $kecamatan = Kecamatan::where('id_kota', $id_kota)->get();
        return response()->json($kecamatan);
    }

    // Mengambil data kelurahan berdasarkan id_kecamatan
    public function getKelurahanByKecamatan($id_kecamatan)
    {
        $kelurahan = Kelurahan::where('id_kecamatan', $id_kecamatan)->get();
        return response()->json($kelurahan);
    }
    
    public function getKodeKelurahanLengkap($id_kelurahan)
    {
        $kelurahan = Kelurahan::find($id_kelurahan);

        if (!$kelurahan) {
            return response()->json(['message' => 'Kelurahan not found.'], 404);
        }

        // Ambil data kecamatan, kota, dan provinsi secara berurutan
        $kecamatan = $kelurahan->kecamatan; // Relasi ke Kecamatan
        $kota = $kecamatan ? $kecamatan->kota : null; // Relasi Kecamatan ke Kota
        $provinsi = $kota ? $kota->provinsi : null; // Relasi Kota ke Provinsi

        // Pastikan semua level data ditemukan sebelum membentuk kode lengkap
        if (!$provinsi || !$kota || !$kecamatan) {
            return response()->json(['message' => 'Data wilayah tidak lengkap untuk kelurahan ini.'], 400);
        }

        $kode_lengkap = sprintf(
            "%s.%s.%s.%s",
            $provinsi->kode_provinsi,
            $kota->kode_kota,
            $kecamatan->kode_kecamatan,
            $kelurahan->kode_kelurahan
        );

        return response()->json(['kode_kelurahan_lengkap' => $kode_lengkap]);
    }
    
    public function getRegionByKelurahanId($id_kelurahan)
    {
        // Temukan kelurahan berdasarkan ID dan eager load relasi ke atasnya
        $kelurahan = Kelurahan::with([
            'kecamatan.kota.provinsi'
        ])->find($id_kelurahan);

        if (!$kelurahan) {
            return response()->json([
                'message' => 'Kelurahan tidak ditemukan.',
            ], 404);
        }

        $daerah = [
            'id_kelurahan' => $kelurahan->id,
            'nama_kelurahan' => $kelurahan->nama,
            'kode_kelurahan' => $kelurahan->kode_kelurahan,
        ];

        // Periksa dan tambahkan data kecamatan
        if ($kelurahan->kecamatan) {
            $daerah['id_kecamatan'] = $kelurahan->kecamatan->id;
            $daerah['nama_kecamatan'] = $kelurahan->kecamatan->nama;
            $daerah['kode_kecamatan'] = $kelurahan->kecamatan->kode_kecamatan;

            // Periksa dan tambahkan data kota
            if ($kelurahan->kecamatan->kota) {
                $daerah['id_kota'] = $kelurahan->kecamatan->kota->id;
                $daerah['nama_kota'] = $kelurahan->kecamatan->kota->nama;
                $daerah['kode_kota'] = $kelurahan->kecamatan->kota->kode_kota;

                // Periksa dan tambahkan data provinsi
                if ($kelurahan->kecamatan->kota->provinsi) {
                    $daerah['id_provinsi'] = $kelurahan->kecamatan->kota->provinsi->id;
                    $daerah['nama_provinsi'] = $kelurahan->kecamatan->kota->provinsi->nama;
                    $daerah['kode_provinsi'] = $kelurahan->kecamatan->kota->provinsi->kode_provinsi;
                }
            }
        }

        // Buat kode lengkap wilayah jika semua komponen kode tersedia
        if (isset($daerah['kode_provinsi']) && isset($daerah['kode_kota']) && isset($daerah['kode_kecamatan']) && isset($daerah['kode_kelurahan'])) {
            $daerah['kode_lengkap_wilayah'] = sprintf(
                "%s.%s.%s.%s",
                $daerah['kode_provinsi'],
                $daerah['kode_kota'],
                $daerah['kode_kecamatan'],
                $daerah['kode_kelurahan']
            );
        }

        return response()->json([
            'message' => 'Data daerah ditemukan.',
            'data' => $daerah,
        ], 200);
    }

}