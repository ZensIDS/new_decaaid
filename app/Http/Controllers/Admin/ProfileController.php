<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** BA3: profil sendiri. Semua peran hanya boleh mengubah nama (email dan peran dikelola Super Admin). */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pages.admin.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $user = $request->user();
        $user->forceFill(['name' => $data['name']])->save();

        ActivityLog::record('profile.updated', 'mengubah nama profilnya', $user);

        return redirect()->route('admin.profile.edit')->with('status', 'Profil disimpan.');
    }
}