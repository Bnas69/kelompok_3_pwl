<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class HrAccountService
{
    private const ACCOUNT_FILE = 'app/private/hr_account.json';

    public function allAccounts(): array
    {
        return array_map(fn (array $a) => Arr::except($a, ['password_hash']), $this->accounts());
    }

    public function publicAccount(?string $username = null): array
    {
        if ($username) {
            foreach ($this->accounts() as $account) {
                if ($account['username'] === $username) {
                    return Arr::except($account, ['password_hash']);
                }
            }
            return $this->defaults();
        }
        return $this->currentAccount();
    }

    public function attempt(string $username, string $password): ?array
    {
        foreach ($this->accounts() as $account) {
            if (! hash_equals((string) $account['username'], $username)) {
                continue;
            }

            if ($this->passwordMatches($account, $password)) {
                if ($this->needsPasswordMigration($account, $password)) {
                    $account['password_hash'] = Hash::make($password);
                    $this->saveAccount($account);
                }

                return $this->recordLogin($account);
            }
        }

        return null;
    }

    public function updateProfile(array $data): array
    {
        $account = $this->currentAccount();
        $account['display_name'] = $data['display_name'];
        $account['email'] = $data['email'] ?? null;

        $this->saveAccount($account);

        return Arr::except($account, ['password_hash']);
    }

    public function updatePassword(string $currentPassword, string $newPassword): array
    {
        $account = $this->currentAccount();

        if (! $this->passwordMatches($account, $currentPassword)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password lama tidak sesuai.',
            ]);
        }

        $account['password_hash'] = Hash::make($newPassword);
        $this->saveAccount($account);

        return Arr::except($account, ['password_hash']);
    }

    public function sessionPayload(array $account): array
    {
        return [
            'hr_logged_in' => true,
            'hr_username' => $account['username'],
            'hr_display_name' => $account['display_name'],
            'hr_email' => $account['email'],
            'hr_role' => $account['role'],
            'hr_theme' => $account['theme'],
            'hr_theme_color' => $account['theme_color'],
            'hr_last_login_at' => $account['last_login_at'],
        ];
    }

    private function currentAccount(): array
    {
        $username = session('hr_username', 'admin');
        foreach ($this->accounts() as $account) {
            if ($account['username'] === $username) {
                return $this->normalize($account);
            }
        }
        return $this->normalize(array_replace($this->defaults(), ['username' => $username]));
    }

    private function recordLogin(array $account): array
    {
        $account['last_login_at'] = now()->format('Y-m-d H:i:s');
        $account['last_login_ip'] = request()->ip();
        $this->saveAccount($account);
        return Arr::except($account, ['password_hash']);
    }

    private function accounts(): array
    {
        $data = $this->read();
        if (empty($data)) {
            return [$this->normalize($this->defaults())];
        }
        if (isset($data['username'])) {
            return [$this->normalize($data)];
        }
        return array_map(fn (array $a) => $this->normalize($a), $data);
    }

    private function defaults(): array
    {
        return [
            'username' => (string) config('app.login_username', 'admin'),
            'password_hash' => (string) config('app.login_password_hash', ''),
            'role' => (string) config('app.login_role', 'admin'),
            'display_name' => (string) config('app.login_display_name', 'Admin Kelompok 3'),
            'email' => config('app.login_email'),
            'theme' => 'light',
            'theme_color' => 'blue',
            'last_login_at' => null,
            'last_login_ip' => null,
        ];
    }

    private function normalize(array $account): array
    {
        $account['role'] = in_array($account['role'] ?? 'admin', ['admin', 'hrd', 'owner', 'karyawan'], true)
            ? $account['role']
            : 'karyawan';
        $account['theme'] = in_array($account['theme'] ?? 'light', ['light', 'dark'], true)
            ? $account['theme']
            : 'light';
        $account['theme_color'] = in_array($account['theme_color'] ?? 'blue', ['blue', 'green', 'amber'], true)
            ? $account['theme_color']
            : 'blue';
        return $account;
    }

    private function saveAccount(array $updated): void
    {
        $all = $this->read();
        if (isset($all['username'])) {
            $all = [$all];
        }
        if (! is_array($all)) {
            $all = [];
        }

        $found = false;
        foreach ($all as &$acc) {
            if ($acc['username'] === $updated['username']) {
                $acc = $updated;
                $found = true;
                break;
            }
        }
        unset($acc);
        if (! $found) {
            $all[] = $updated;
        }

        $this->write($all);
    }

    private function passwordMatches(array $account, string $password): bool
    {
        $hash = (string) ($account['password_hash'] ?? '');

        if ($hash === '') {
            return false;
        }

        return Hash::check($password, $hash);
    }

    private function needsPasswordMigration(array $account, string $password): bool
    {
        $hash = (string) ($account['password_hash'] ?? '');

        if ($hash === '') {
            return true;
        }

        return Hash::check($password, $hash) && Hash::needsRehash($hash);
    }

    private function read(): array
    {
        $path = $this->path();

        if (! File::exists($path)) {
            return [];
        }

        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : [];
    }

    private function write(array $data): void
    {
        $path = $this->path();
        File::ensureDirectoryExists(dirname($path), 0750);
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), true);
        @chmod($path, 0640);
    }

    private function path(): string
    {
        return storage_path(self::ACCOUNT_FILE);
    }
}
