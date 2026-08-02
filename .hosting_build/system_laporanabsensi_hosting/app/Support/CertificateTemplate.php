<?php

namespace App\Support;

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateTemplate
{
    private const PATH_KEY = 'certificate_template_path';
    private const NAME_KEY = 'certificate_template_name';

    public static function current(): array
    {
        $path = Pengaturan::where('kunci', self::PATH_KEY)->value('nilai');
        $name = Pengaturan::where('kunci', self::NAME_KEY)->value('nilai');

        if (! $path || ! Storage::disk('local')->exists($path)) {
            return [
                'uploaded' => false,
                'name' => 'Template Default Sistem',
                'path' => null,
            ];
        }

        return [
            'uploaded' => true,
            'name' => $name ?: basename($path),
            'path' => $path,
        ];
    }

    public static function storeUploaded($file): void
    {
        $filename = 'template_' . now(config('app.timezone'))->format('Ymd_His') . '_' . Str::random(6) . '.html';
        $path = $file->storeAs('certificate_templates', $filename, 'local');

        $previousPath = Pengaturan::where('kunci', self::PATH_KEY)->value('nilai');
        if ($previousPath && $previousPath !== $path) {
            Storage::disk('local')->delete($previousPath);
        }

        Pengaturan::updateOrCreate(['kunci' => self::PATH_KEY], ['nilai' => $path]);
        Pengaturan::updateOrCreate(['kunci' => self::NAME_KEY], ['nilai' => $file->getClientOriginalName()]);
    }

    public static function renderUploaded(User $user): ?string
    {
        $template = self::current();
        if (! $template['uploaded']) {
            return null;
        }

        $html = Storage::disk('local')->get($template['path']);
        $html = self::fillPlaceholders($html, CertificatePayload::forUser($user));

        return self::replaceLocalAssets($html);
    }

    private static function fillPlaceholders(string $html, array $data): string
    {
        return preg_replace_callback('/{{\s*(\w+)\s*}}/', function (array $matches) use ($data): string {
            $key = $matches[1];
            $value = $data[$key] ?? '';

            if ($value === '' && $key === 'nama_penandatangan') {
                $value = '(menyusul)';
            }

            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        }, $html);
    }

    private static function replaceLocalAssets(string $html): string
    {
        $assets = CertificatePayload::assets();
        $diskominfoLogo = $assets['logo_diskominfo'] ?? '';
        $kabupatenLogo = $assets['logo_kabupaten'] ?? '';

        return str_replace([
            'assets/logo_diskominfo.png',
            'assets/logo_diskominfo.svg',
            'assets/logo_kabupaten_bogor.png',
            'assets/logo_kabupaten_bogor.svg',
            'assets/shield_only.png',
            'assets/shield_only.svg',
        ], [
            $diskominfoLogo,
            $diskominfoLogo,
            $kabupatenLogo,
            $kabupatenLogo,
            $kabupatenLogo,
            $kabupatenLogo,
        ], $html);
    }
}
