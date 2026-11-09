<?php

namespace App\Http\Controllers;

use App\Models\LaundrySetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PengaturanController extends Controller
{
    public function index(): View
    {
        $settings = LaundrySetting::query()->first() ?? new LaundrySetting([
            'opening_time' => '07:00',
            'break_start' => '12:00',
            'break_end' => '13:00',
            'closing_time' => '21:00',
            'notify_browser' => true,
            'notify_message' => true,
            'date_format' => 'd-m-Y',
            'confirm_logout' => true,
        ]);

        return view('pengaturan.index', [
            'settings' => $settings,
            'admin' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'laundry_name' => ['nullable', 'string', 'max:120'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'opening_time' => ['required', 'date_format:H:i'],
            'break_start' => ['required', 'date_format:H:i', 'after:opening_time'],
            'break_end' => ['required', 'date_format:H:i', 'after:break_start'],
            'closing_time' => ['required', 'date_format:H:i', 'after:break_end'],
            'completion_message' => ['nullable', 'string', 'max:3000'],
            'notify_browser' => ['required', 'boolean'],
            'notify_message' => ['required', 'boolean'],
            'date_format' => ['required', Rule::in(['d-m-Y', 'Y-m-d', 'd/m/Y'])],
            'confirm_logout' => ['required', 'boolean'],
            'current_password' => ['required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'string', Password::default(), 'confirmed'],
        ], [
            'opening_time.required' => 'Jam buka wajib diisi.',
            'break_start.required' => 'Jam mulai istirahat wajib diisi.',
            'break_start.after' => 'Jam mulai istirahat harus setelah jam buka.',
            'break_end.required' => 'Jam selesai istirahat wajib diisi.',
            'break_end.after' => 'Jam selesai istirahat harus setelah jam mulai istirahat.',
            'closing_time.required' => 'Jam tutup wajib diisi.',
            'closing_time.after' => 'Jam tutup harus setelah jam selesai istirahat.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        DB::transaction(function () use ($validated) {
            $settings = LaundrySetting::query()->first() ?? new LaundrySetting;
            $settings->fill(Arr::except($validated, [
                'current_password',
                'new_password',
            ]));
            $settings->save();

            if (! empty($validated['new_password'])) {
                auth()->user()->forceFill([
                    'password' => Hash::make($validated['new_password']),
                ])->save();
            }
        });

        return redirect()
            ->route('settings.index')
            ->with('status', 'Perubahan pengaturan berhasil disimpan.');
    }
}
