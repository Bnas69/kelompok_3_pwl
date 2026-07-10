<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHrPasswordRequest;
use App\Http\Requests\UpdateHrProfileRequest;
use App\Services\HrAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HrSettingsController extends Controller
{
    public function __construct(private readonly HrAccountService $accounts)
    {
    }

    public function index(): View
    {
        return view('hr.settings', [
            'account' => $this->accounts->publicAccount(),
            'appInfo' => [
                'name' => config('app.name'),
                'version' => config('app.version'),
                'group' => 'Kelompok 3 - Human Resource Analytics',
                'university' => 'Universitas Dian Nusantara',
                'study_program' => config('app.study_program'),
                'academic_year' => config('app.academic_year'),
            ],
        ]);
    }

    public function updateProfile(UpdateHrProfileRequest $request): RedirectResponse
    {
        $account = $this->accounts->updateProfile($request->validated());
        $request->session()->put($this->accounts->sessionPayload($account));

        return back()->with('success', 'Profil akun berhasil diperbarui.');
    }

    public function updatePassword(UpdateHrPasswordRequest $request): RedirectResponse
    {
        $account = $this->accounts->updatePassword(
            $request->string('current_password')->toString(),
            $request->string('password')->toString(),
        );

        $request->session()->put($this->accounts->sessionPayload($account));

        return back()->with('success', 'Password berhasil diperbarui.');
    }

}
