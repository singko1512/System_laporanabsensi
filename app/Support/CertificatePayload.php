<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class CertificatePayload
{
    public static function forUser(User $user): array
    {
        $now = now(config('app.timezone'));
        $bidang = $user->bidang?->nama ?: $user->bidang_magang ?: '-';
        $pembimbing = $user->pembimbingMagang?->nama ?: $user->pembimbing_magang;

        return [
            'nomor_sertifikat' => sprintf('SKM/DISKOMINFO-KAB-BOGOR/%s/%04d', $now->format('Y'), $user->id),
            'nama' => $user->nama,
            'posisi' => 'Peserta Magang',
            'bidang' => $bidang,
            'tanggal_mulai' => self::formatDate($user->tanggal_mulai_magang),
            'tanggal_selesai' => self::formatDate($user->tanggal_selesai_magang),
            'predikat' => 'Baik',
            'tempat' => 'Cibinong',
            'tanggal_terbit' => $now->translatedFormat('d F Y'),
            'jabatan_penandatangan' => 'Pembimbing Magang',
            'nama_penandatangan' => $pembimbing ?: '(pembimbing belum diisi)',
            'unit_penandatangan' => 'Pembimbing Magang · '.$bidang,
        ];
    }

    public static function assets(): array
    {
        return [
            'logo_diskominfo_svg' => self::fileContents(public_path('assets/certificate/logo_diskominfo.svg')),
            'logo_diskominfo' => self::dataUri(public_path('assets/certificate/logo_diskominfo.svg'), 'image/svg+xml'),
            'logo_kabupaten' => self::dataUri(public_path('assets/certificate/lambang_kabupaten_bogor.svg'), 'image/svg+xml')
                ?: (extension_loaded('gd') ? self::dataUri(public_path('assets/certificate/lambang_kabupaten_bogor.png'), 'image/png') : null),
        ];
    }

    public static function fileName(User $user): string
    {
        return 'sertifikat_'.Str::slug($user->nama ?: 'peserta').'.pdf';
    }

    private static function formatDate($date): string
    {
        return $date ? $date->translatedFormat('d F Y') : '-';
    }

    private static function fileContents(string $path): string
    {
        return is_file($path) ? file_get_contents($path) : '';
    }

    private static function dataUri(string $path, string $mime): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
