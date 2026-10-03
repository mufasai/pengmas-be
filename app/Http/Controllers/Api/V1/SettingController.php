<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->map(function ($setting) {
            $data = [
                'key' => $setting->key,
                'value' => $setting->value,
            ];

            if ($setting->key === 'qris_image' && $setting->value) {
                $data['url'] = asset('storage/' . $setting->value);
            }

            return $data;
        });

        return response()->json(['data' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable',
        ]);

        $value = $request->value;

        if ($request->key === 'qris_image' && $request->hasFile('value')) {
            $setting = Setting::where('key', 'qris_image')->first();
            if ($setting && $setting->value) {
                Storage::disk('public')->delete($setting->value);
            }

            $value = $request->file('value')->store('qris', 'public');
        }

        $setting = Setting::updateOrCreate(
            ['key' => $request->key],
            ['value' => $value],
        );

        $response = [
            'key' => $setting->key,
            'value' => $setting->value,
        ];

        if ($setting->key === 'qris_image' && $setting->value) {
            $response['url'] = asset('storage/' . $setting->value);
        }

        return response()->json([
            'message' => 'Setting berhasil diperbarui.',
            'data' => $response,
        ]);
    }
}