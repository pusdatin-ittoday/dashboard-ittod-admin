<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFeedbackController extends Controller
{
    public function index(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa', 'panitia_lomba'], true), 403);

        $feedbacks = UserFeedback::with(['user', 'user.identity'])
            ->latest('created_at')
            ->get();

        return view('admin.feedback.index', [
            'feedbacks' => $feedbacks,
        ]);
    }

    public function updateStatus(Request $request, UserFeedback $feedback): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa', 'panitia_lomba'], true), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,reviewed,resolved'],
        ]);

        $feedback->update(['status' => $validated['status']]);

        return back()->with('status', 'Status feedback berhasil diperbarui.');
    }

    public function destroy(UserFeedback $feedback): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $feedback->delete();

        return back()->with('status', 'Data feedback berhasil dihapus.');
    }
}
