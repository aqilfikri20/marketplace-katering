<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Support\MarketplaceCache;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MenuController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150', 'description' => 'required|string|max:1500',
            'price' => 'required|numeric|min:1000|max:100000000', 'category' => 'nullable|string|max:80',
            'photo' => 'required|image|max:3072', 'status' => 'required|in:available,preorder,sold_out',
        ], ['status.required' => 'Status menu wajib dipilih.', 'status.in' => 'Status menu yang dipilih tidak valid.']);
        $photoPath = $this->storeMenuPhoto($request->file('photo'), $request->user()->company_name ?: $request->user()->name);
        $data['photo'] = $photoPath;

        try {
            $request->user()->menus()->create($data);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($photoPath);
            throw $exception;
        }

        MarketplaceCache::invalidate();
        return back()->with('success', 'Menu berhasil ditambahkan.');
    }
    public function update(Request $request, Menu $menu)
    {
        abort_unless($menu->merchant_id === $request->user()->id, 403);
        $data = $request->validate([
            'name' => 'required|string|max:150', 'description' => 'required|string|max:1500',
            'price' => 'required|numeric|min:1000|max:100000000', 'category' => 'nullable|string|max:80',
            'photo' => 'nullable|image|max:3072', 'status' => 'required|in:available,preorder,sold_out',
        ], ['status.required' => 'Status menu wajib dipilih.', 'status.in' => 'Status menu yang dipilih tidak valid.']);
        $previousPhoto = $menu->photo;
        $newPhotoPath = null;

        if ($request->hasFile('photo')) {
            $newPhotoPath = $this->storeMenuPhoto($request->file('photo'), $request->user()->company_name ?: $request->user()->name);
            $data['photo'] = $newPhotoPath;
        }

        try {
            $menu->update($data);
        } catch (\Throwable $exception) {
            if ($newPhotoPath) Storage::disk('public')->delete($newPhotoPath);
            throw $exception;
        }

        if ($newPhotoPath && $previousPhoto) Storage::disk('public')->delete($previousPhoto);

        MarketplaceCache::invalidate();
        return back()->with('success', 'Menu berhasil diperbarui.');
    }
    public function destroy(Request $request, Menu $menu)
    {
        abort_unless($menu->merchant_id === $request->user()->id, 403);
        if ($menu->photo) Storage::disk('public')->delete($menu->photo);
        $menu->delete();
        MarketplaceCache::invalidate();
        return back()->with('success', 'Menu berhasil dihapus.');
    }

    private function storeMenuPhoto(UploadedFile $photo, string $shopName): string
    {
        $shopSlug = Str::slug($shopName) ?: 'katering';
        $extension = strtolower($photo->guessExtension() ?: $photo->extension() ?: 'jpg');
        $disk = Storage::disk('public');

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $randomDigits = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $filename = "menu_{$shopSlug}_{$randomDigits}.{$extension}";
            $path = "menus/{$filename}";

            if ($disk->exists($path)) continue;

            $storedPath = $photo->storeAs('menus', $filename, 'public');
            if ($storedPath !== false) return $storedPath;
        }

        throw ValidationException::withMessages([
            'photo' => 'Foto menu gagal disimpan. Silakan coba unggah kembali.',
        ]);
    }
}
