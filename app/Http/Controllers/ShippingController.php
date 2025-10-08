<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    public function getProvinces()
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        return response()->json($response->json()['data']);
    }

    public function getCities(Request $request)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$request->province}");

        return response()->json($response->json()['data'] ?? []);
    }

    public function getDistricts(Request $request)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$request->city}");

        return response()->json($response->json()['data'] ?? []);
    }

    public function getSubDistricts(Request $request)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get("https://rajaongkir.komerce.id/api/v1/destination/sub-district/{$request->district}");

        return response()->json($response->json()['data'] ?? []);
    }

    public function getCost(Request $request)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY'),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost', [
            'origin' => $request->origin,
            'destination' => $request->destination,
            'weight' => $request->weight,
            'courier' => $request->courier,
            'price' => 'lowest'
        ]);

        $json = $response->json();

        if (isset($json['meta']['status']) && $json['meta']['status'] === 'success' && !empty($json['data'])) {
            // ambil semua layanan pengiriman
            return response()->json([
                'status' => 'success',
                'data' => $json['data']
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => $json['meta']['message'] ?? 'Data ongkir tidak ditemukan',
            ], 400);
        }
    }
}
