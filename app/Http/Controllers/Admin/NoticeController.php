<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::with('category')->latest('created_at');

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('notice_category_id', $categoryId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $notices = $query->paginate(10)->withQueryString();
        $categories = NoticeCategory::orderBy('name')->get();

        return view('admin.notices.index', compact('notices', 'categories'));
    }

    public function create()
    {
        $categories = NoticeCategory::where('status', true)->orderBy('name')->get();
        $notice = new Notice(['status' => 'draft']);

        return view('admin.notices.form', [
            'notice'     => $notice,
            'categories' => $categories,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateNotice($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('notices', 'public');
        }

        $validated['author_id'] = session('alumni_id');
        $validated['published_at'] = $this->resolvePublishedAt($request, null);

        Notice::create($validated);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    public function edit(Notice $notice)
    {
        $categories = NoticeCategory::orderBy('name')->get();

        return view('admin.notices.form', [
            'notice'     => $notice,
            'categories' => $categories,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $this->validateNotice($request, $notice->id);

        if ($request->hasFile('image')) {
            if ($notice->image) {
                Storage::disk('public')->delete($notice->image);
            }
            $validated['image'] = $request->file('image')->store('notices', 'public');
        } elseif ($request->input('remove_image') === '1' && $notice->image) {
            Storage::disk('public')->delete($notice->image);
            $validated['image'] = null;
        }

        if ($validated['title'] !== $notice->title) {
            $validated['slug'] = Notice::uniqueSlug($validated['title'], $notice->id);
        }

        $validated['published_at'] = $this->resolvePublishedAt($request, $notice);

        $notice->update($validated);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        if ($notice->image) {
            Storage::disk('public')->delete($notice->image);
        }

        $title = $notice->title;
        $notice->delete();

        return redirect()->route('admin.notices.index')
            ->with('success', "\"{$title}\" has been deleted.");
    }

    public function toggleStatus(Notice $notice)
    {
        $notice->status = $notice->status === 'published' ? 'draft' : 'published';

        if ($notice->status === 'published' && !$notice->published_at) {
            $notice->published_at = now();
        }

        $notice->save();

        return back()->with('success', "\"{$notice->title}\" is now " . $notice->status . '.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function validateNotice(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'               => 'required|string|max:255',
            'notice_category_id'  => 'nullable|exists:notice_categories,id',
            'description'         => 'required|string',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'status'              => 'required|in:draft,published',
            'published_at'        => 'nullable|date',
        ]);
    }

    private function resolvePublishedAt(Request $request, ?Notice $existing): ?string
    {
        if ($request->status !== 'published') {
            return $existing?->published_at;
        }

        if ($request->filled('published_at')) {
            return \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->published_at, 'Asia/Kolkata')
                ->setTimezone(config('app.timezone'));
        }

        return $existing?->published_at ?: now();
    }
}