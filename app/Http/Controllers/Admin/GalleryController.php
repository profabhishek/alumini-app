<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $items = GalleryItem::orderBy('sort_order')->orderByDesc('created_at')->get();

        return view('admin.gallery.index', compact('items'));
    }

    public function create()
    {
        $item = new GalleryItem(['status' => 'published']);

        return view('admin.gallery.form', ['item' => $item, 'isEdit' => false]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'nullable|string|max:255',
            'country'    => 'nullable|string|max:100',
            'event_name' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
            'image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'status'     => 'required|in:draft,published',
        ]);

        $validated['image'] = $request->file('image')->store('gallery', 'public');
        $validated['author_id'] = session('alumni_id');
        $validated['sort_order'] = (GalleryItem::max('sort_order') ?? 0) + 1;

        GalleryItem::create($validated);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Gallery image added successfully.');
    }

    public function edit(GalleryItem $galleryItem)
    {
        return view('admin.gallery.form', ['item' => $galleryItem, 'isEdit' => true]);
    }

    public function update(Request $request, GalleryItem $galleryItem)
    {
        $validated = $request->validate([
            'title'      => 'nullable|string|max:255',
            'country'    => 'nullable|string|max:100',
            'event_name' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'status'     => 'required|in:draft,published',
        ]);

        if ($request->hasFile('image')) {
            if ($galleryItem->image) {
                Storage::disk('public')->delete($galleryItem->image);
            }
            $validated['image'] = $request->file('image')->store('gallery', 'public');
        }

        $galleryItem->update($validated);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Gallery image updated successfully.');
    }

    public function destroy(GalleryItem $galleryItem)
    {
        if ($galleryItem->image) {
            Storage::disk('public')->delete($galleryItem->image);
        }

        $galleryItem->delete();

        return back()->with('success', 'Gallery image deleted.');
    }

    public function toggleStatus(GalleryItem $galleryItem)
    {
        $galleryItem->status = $galleryItem->status === 'published' ? 'draft' : 'published';
        $galleryItem->save();

        return back()->with('success', "Image is now {$galleryItem->status}.");
    }

    public function moveUp(GalleryItem $galleryItem)
    {
        $prev = GalleryItem::where('sort_order', '<', $galleryItem->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($prev) {
            [$galleryItem->sort_order, $prev->sort_order] = [$prev->sort_order, $galleryItem->sort_order];
            $galleryItem->save();
            $prev->save();
        }

        return back();
    }

    public function moveDown(GalleryItem $galleryItem)
    {
        $next = GalleryItem::where('sort_order', '>', $galleryItem->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            [$galleryItem->sort_order, $next->sort_order] = [$next->sort_order, $galleryItem->sort_order];
            $galleryItem->save();
            $next->save();
        }

        return back();
    }
}