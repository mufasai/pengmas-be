<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('category')->get()->map(function ($menu) {
            $menu->image_url = $menu->image
                ? asset('storage/' . $menu->image)
                : null;
            $menu->makeHidden('image');
            return $menu;
        });

        return response()->json(['data' => $menus]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|integer',
            'sku' => 'nullable|string|max:100|unique:menus,sku',
            'description' => 'nullable|string',
            'is_active' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menus', 'public');
        }

        $menu = Menu::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'sku' => $request->sku,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'image' => $imagePath,
        ]);

        $menu->load('category');
        $menu->image_url = $menu->image
            ? asset('storage/' . $menu->image)
            : null;
        $menu->makeHidden('image');

        return response()->json([
            'message' => 'Menu berhasil ditambahkan.',
            'data' => $menu
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'sometimes|integer',
            'sku' => 'nullable|string|max:100|unique:menus,sku,' . $menu->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $menu->image = $request->file('image')->store('menus', 'public');
        }

        $menu->fill($request->only([
            'name', 'category_id', 'price', 'sku', 'description',
        ]));
        if ($request->has('is_active')) {
            $menu->is_active = $request->boolean('is_active');
        }
        $menu->save();

        $menu->load('category');
        $menu->image_url = $menu->image
            ? asset('storage/' . $menu->image)
            : null;
        $menu->makeHidden('image');

        return response()->json([
            'message' => 'Menu berhasil diperbarui.',
            'data' => $menu
        ]);
    }

    public function show($id)
    {
        $menu = Menu::with('category')->findOrFail($id);
        $menu->image_url = $menu->image
            ? asset('storage/' . $menu->image)
            : null;
        $menu->makeHidden('image');

        return response()->json(['data' => $menu]);
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return response()->json(['message' => 'Menu berhasil dihapus.']);
    }
}
