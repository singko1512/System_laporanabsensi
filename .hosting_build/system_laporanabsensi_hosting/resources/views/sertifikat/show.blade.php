<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sertifikat - {{ data_get($certificate ?? [], 'nama', $user->nama) }}</title>
<style>
  @page { size: 1600px 1131px; margin: 0; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { min-height: 100%; }
  body {
    background: #eef2f7;
    font-family: Georgia, "Times New Roman", serif;
    color: #f6ecd6;
  }
  body.pdf-mode {
    width: 1600px;
    height: 1131px;
    background: #050914;
  }
  .preview-shell {
    min-height: 100vh;
    padding: 28px;
    overflow: auto;
  }
  .pdf-mode .preview-shell {
    min-height: 0;
    padding: 0;
    overflow: hidden;
  }
  .cert {
    position: relative;
    width: 1600px;
    height: 1131px;
    overflow: hidden;
    background: #080d24;
    background-image:
      radial-gradient(circle at 50% 38%, #16224f 0%, #0d1638 45%, #080d24 100%);
    color: #f6ecd6;
    box-shadow: 0 24px 60px rgba(15, 23, 42, .24);
  }
  .pdf-mode .cert { box-shadow: none; }
  .weave {
    position: absolute;
    inset: 0;
    opacity: .05;
    background-image:
      repeating-linear-gradient(45deg, #c9a24a 0 1px, transparent 1px 26px),
      repeating-linear-gradient(-45deg, #c9a24a 0 1px, transparent 1px 26px);
  }
  .frame-outer {
    position: absolute;
    inset: 26px;
    border: 2px solid #c9a24a;
    z-index: 3;
  }
  .frame-inner {
    position: absolute;
    inset: 38px;
    border: 1px solid #8a6a28;
    z-index: 3;
  }
  .rosette {
    position: absolute;
    top: 12px;
    left: 50%;
    width: 520px;
    height: 520px;
    margin-left: -260px;
    opacity: .15;
  }
  .hexgrid {
    position: absolute;
    width: 420px;
    height: 420px;
    opacity: .10;
  }
  .hexgrid.tr { top: -40px; right: -40px; }
  .hexgrid.bl { bottom: -40px; left: -40px; }
  .corner {
    position: absolute;
    width: 150px;
    height: 150px;
    z-index: 4;
  }
  .corner.tl { top: 26px; left: 26px; }
  .corner.tr { top: 26px; right: 26px; transform: scaleX(-1); }
  .corner.bl { bottom: 26px; left: 26px; transform: scaleY(-1); }
  .corner.br { bottom: 26px; right: 26px; transform: scale(-1, -1); }
  .content {
    position: relative;
    z-index: 5;
    height: 100%;
    padding: 56px 150px 42px;
    text-align: center;
  }
  .logos-row {
    width: 100%;
    display: table;
    table-layout: fixed;
  }
  .logo-cell {
    display: table-cell;
    width: 50%;
    vertical-align: middle;
  }
  .logo-cell.left { text-align: left; }
  .logo-cell.right { text-align: right; }
  .logo-diskominfo svg {
    width: 250px;
    height: auto;
  }
  .logo-kabupaten {
    height: 74px;
    width: auto;
    object-fit: contain;
  }
  .fallback-logo {
    display: inline-block;
    border: 1px solid #c9a24a;
    padding: 10px 14px;
    color: #f0d98c;
    font: 700 18px Arial, sans-serif;
    letter-spacing: 2px;
  }
  .rule-thin {
    width: 1000px;
    height: 1px;
    margin: 21px auto 0;
    background: linear-gradient(90deg, transparent, #c9a24a 15%, #c9a24a 85%, transparent);
  }
  .eyebrow {
    margin-top: 26px;
    font: 700 15px Arial, sans-serif;
    letter-spacing: 6px;
    color: #f0d98c;
  }
  .dept {
    margin-top: 8px;
    font: 600 21px Arial, sans-serif;
    letter-spacing: 5px;
  }
  .title {
    margin-top: 14px;
    font: 800 78px Arial, sans-serif;
    letter-spacing: 16px;
    color: #f0d98c;
    text-shadow: 0 1px 0 #f8e9bd, 0 3px 10px rgba(0, 0, 0, .55);
  }
  .cert-code {
    margin-top: 10px;
    font: 700 15px "Courier New", monospace;
    letter-spacing: 3px;
    color: #2fb8ac;
  }
  .cert-code .br { color: #c9a24a; }
  .given-to {
    margin-top: 30px;
    font-size: 22px;
    font-style: italic;
    letter-spacing: 3px;
    color: #f8e9bd;
  }
  .name-row {
    margin-top: 14px;
    width: 100%;
    display: table;
    table-layout: fixed;
  }
  .name-ornament,
  .name-value {
    display: table-cell;
    vertical-align: middle;
  }
  .name-ornament { width: 205px; }
  .ornament {
    width: 150px;
    height: 40px;
  }
  .ornament.right { transform: scaleX(-1); }
  .participant-name {
    max-width: 900px;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 72px;
    line-height: 1.08;
    font-style: italic;
    color: #f8e9bd;
    text-shadow: 0 2px 0 rgba(0, 0, 0, .35), 0 0 26px rgba(201, 162, 74, .35);
  }
  .body-text {
    max-width: 1080px;
    margin: 26px auto 0;
    font-size: 27px;
    line-height: 1.6;
  }
  .body-text b {
    color: #f0d98c;
    font-weight: 700;
  }
  .footer-block {
    position: absolute;
    left: 150px;
    right: 150px;
    bottom: 42px;
    z-index: 5;
  }
  .place-date {
    font: 700 18px Arial, sans-serif;
    letter-spacing: 5px;
    color: #f0d98c;
    text-align: center;
  }
  .verify-row {
    width: 1120px;
    margin: 26px auto 0;
    display: table;
    table-layout: fixed;
  }
  .verify-cell {
    display: table-cell;
    vertical-align: middle;
  }
  .verify-cell.badge { width: 144px; text-align: center; }
  .verify-cell.sign { width: 640px; text-align: left; }
  .verify-cell.divider { width: 1px; }
  .verify-cell.qr { width: 210px; text-align: center; }
  .hexbadge-wrap {
    position: relative;
    width: 112px;
    height: 128px;
    margin: 0 auto;
  }
  .hexbadge-wrap svg,
  .hexbadge-wrap img {
    position: absolute;
  }
  .hexbadge-wrap svg { inset: 0; }
  .hexbadge-wrap img {
    left: 50%;
    top: 50%;
    width: 62px;
    height: 78px;
    margin-left: -31px;
    margin-top: -39px;
    object-fit: contain;
  }
  .sign-text {
    font-size: 15.5px;
    line-height: 1.6;
  }
  .sig-name {
    display: block;
    margin-top: 6px;
    font: 700 18px Arial, sans-serif;
    letter-spacing: 1px;
    color: #f0d98c;
  }
  .sig-role {
    display: block;
    color: #c9d0e6;
    font-size: 14px;
  }
  .verify-divider {
    width: 1px;
    height: 104px;
    background: #8a6a28;
    opacity: .85;
  }
  .qr-code {
    display: inline-block;
  }
  .qr-label {
    margin-top: 6px;
    font: 700 11.5px "Courier New", monospace;
    letter-spacing: 2px;
    color: #2fb8ac;
  }
  .print-actions {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 20;
  }
  .print-actions button {
    border: 0;
    border-radius: 8px;
    background: #0d1638;
    color: #fff;
    padding: 11px 16px;
    font: 700 14px Arial, sans-serif;
    box-shadow: 0 12px 28px rgba(15, 23, 42, .24);
    cursor: pointer;
  }
  @media print {
    body { background: #050914; }
    .preview-shell { padding: 0; overflow: hidden; }
    .cert { box-shadow: none; }
    .print-actions { display: none; }
  }
</style>
</head>
@php
    $certificate = $certificate ?? [];
    $assets = $assets ?? [];
    $pdfMode = (bool) ($pdfMode ?? false);
@endphp
<body class="{{ $pdfMode ? 'pdf-mode' : '' }}">
  <div class="preview-shell">
    <div class="cert">
      <div class="weave"></div>

      <svg class="hexgrid tr" viewBox="0 0 420 420">
        <defs>
          <pattern id="hexpat1" width="46" height="80" patternUnits="userSpaceOnUse">
            <path d="M23 0 L46 13 L46 40 L23 53 L0 40 L0 13 Z" fill="none" stroke="#c9a24a" stroke-width="1"/>
            <path d="M23 40 L46 53 L46 80 L23 93 L0 80 L0 53 Z" fill="none" stroke="#c9a24a" stroke-width="1"/>
          </pattern>
        </defs>
        <rect width="420" height="420" fill="url(#hexpat1)"/>
      </svg>
      <svg class="hexgrid bl" viewBox="0 0 420 420">
        <rect width="420" height="420" fill="url(#hexpat1)"/>
      </svg>

      <svg class="rosette" viewBox="0 0 520 520">
        <g fill="none" stroke="#e8cd7a" stroke-width="1">
          <circle cx="260" cy="260" r="240"/>
          <circle cx="260" cy="260" r="215"/>
          <circle cx="260" cy="260" r="190"/>
          <ellipse cx="260" cy="260" rx="240" ry="120"/>
          <ellipse cx="260" cy="260" rx="120" ry="240"/>
          <ellipse cx="260" cy="260" rx="240" ry="120" transform="rotate(45 260 260)"/>
          <ellipse cx="260" cy="260" rx="240" ry="120" transform="rotate(-45 260 260)"/>
          <ellipse cx="260" cy="260" rx="240" ry="120" transform="rotate(22.5 260 260)"/>
          <ellipse cx="260" cy="260" rx="240" ry="120" transform="rotate(-22.5 260 260)"/>
          <ellipse cx="260" cy="260" rx="240" ry="120" transform="rotate(67.5 260 260)"/>
          <ellipse cx="260" cy="260" rx="240" ry="120" transform="rotate(-67.5 260 260)"/>
        </g>
      </svg>

      <div class="frame-outer"></div>
      <div class="frame-inner"></div>

      @foreach (['tl', 'tr', 'bl', 'br'] as $corner)
        <svg class="corner {{ $corner }}" viewBox="0 0 150 150" fill="none">
          <path d="M6 60 V16 Q6 6 16 6 H60" stroke="#c9a24a" stroke-width="2"/>
          @if (in_array($corner, ['tl', 'tr'], true))
            <path d="M6 90 H40 V115" stroke="#2fb8ac" stroke-width="2"/>
            <path d="M34 6 V34 H6" stroke="#c9a24a" stroke-width="1.4" opacity=".7"/>
            <circle cx="40" cy="115" r="4" fill="#2fb8ac"/>
            <path d="M60 6 L95 6" stroke="#c9a24a" stroke-width="1" opacity=".55"/>
            <path d="M6 60 L6 95" stroke="#c9a24a" stroke-width="1" opacity=".55"/>
          @endif
          <circle cx="60" cy="6" r="4.5" fill="#c9a24a"/>
          <circle cx="6" cy="60" r="4.5" fill="#c9a24a"/>
          <polygon points="16,6 24,1 32,6 24,11" fill="none" stroke="#e8cd7a" stroke-width="1.2"/>
        </svg>
      @endforeach

      <div class="content">
        <div class="logos-row">
          <div class="logo-cell left">
            @if (! empty($assets['logo_diskominfo_svg']))
              <span class="logo-diskominfo">{!! $assets['logo_diskominfo_svg'] !!}</span>
            @else
              <span class="fallback-logo">DISKOMINFO</span>
            @endif
          </div>
          <div class="logo-cell right">
            @if (! empty($assets['logo_kabupaten']))
              <img class="logo-kabupaten" src="{{ $assets['logo_kabupaten'] }}" alt="Lambang Kabupaten Bogor Tegar Beriman">
            @else
              <span class="fallback-logo">TEGAR BERIMAN</span>
            @endif
          </div>
        </div>
        <div class="rule-thin"></div>

        <div class="eyebrow">PEMERINTAH KABUPATEN BOGOR</div>
        <div class="dept">DINAS KOMUNIKASI DAN INFORMATIKA</div>
        <div class="title">SERTIFIKAT</div>
        <div class="cert-code"><span class="br">[</span> NO. {{ data_get($certificate, 'nomor_sertifikat', '-') }} <span class="br">]</span></div>

        <div class="given-to">Dengan bangga diberikan kepada</div>

        <div class="name-row">
          <div class="name-ornament">
            <svg class="ornament" viewBox="0 0 150 40" fill="none">
              <path d="M0 20 H95" stroke="#c9a24a" stroke-width="1.2"/>
              <polygon points="95,10 115,20 95,30 103,20" fill="none" stroke="#e8cd7a" stroke-width="1.4"/>
              <circle cx="132" cy="20" r="4" fill="#2fb8ac"/>
              <path d="M115 20 H150" stroke="#2fb8ac" stroke-width="1.2"/>
            </svg>
          </div>
          <div class="name-value">
            <div class="participant-name">{{ data_get($certificate, 'nama', $user->nama) }}</div>
          </div>
          <div class="name-ornament">
            <svg class="ornament right" viewBox="0 0 150 40" fill="none">
              <path d="M0 20 H95" stroke="#c9a24a" stroke-width="1.2"/>
              <polygon points="95,10 115,20 95,30 103,20" fill="none" stroke="#e8cd7a" stroke-width="1.4"/>
              <circle cx="132" cy="20" r="4" fill="#2fb8ac"/>
              <path d="M115 20 H150" stroke="#2fb8ac" stroke-width="1.2"/>
            </svg>
          </div>
        </div>

        <div class="body-text">
          Telah melaksanakan Program Magang sebagai <b>{{ data_get($certificate, 'posisi', 'Peserta Magang') }}</b>
          di <b>{{ data_get($certificate, 'bidang', '-') }}</b>, Dinas Komunikasi dan Informatika Kabupaten Bogor,
          terhitung sejak {{ data_get($certificate, 'tanggal_mulai', '-') }} sampai dengan
          {{ data_get($certificate, 'tanggal_selesai', '-') }}, dengan predikat
          <b>{{ data_get($certificate, 'predikat', 'Baik') }}</b>.
        </div>
      </div>

      <div class="footer-block">
        <div class="place-date">{{ data_get($certificate, 'tempat', 'Cibinong') }} &nbsp;&middot;&nbsp; {{ data_get($certificate, 'tanggal_terbit', '-') }}</div>

        <div class="verify-row">
          <div class="verify-cell badge">
            <div class="hexbadge-wrap">
              <svg viewBox="0 0 112 128" fill="none">
                <polygon points="56,4 106,32 106,96 56,124 6,96 6,32" fill="rgba(201,162,74,0.08)" stroke="#c9a24a" stroke-width="2"/>
                <polygon points="56,16 96,38 96,90 56,112 16,90 16,38" fill="none" stroke="#e8cd7a" stroke-width="1"/>
              </svg>
              @if (! empty($assets['logo_kabupaten']))
                <img src="{{ $assets['logo_kabupaten'] }}" alt="Lambang Kabupaten Bogor">
              @endif
            </div>
          </div>
          <div class="verify-cell sign">
            <div class="sign-text">
              Ditandatangani secara elektronik oleh:<br>
              {{ data_get($certificate, 'jabatan_penandatangan', '-') }}
              <span class="sig-name">{{ data_get($certificate, 'nama_penandatangan', '(menyusul)') }}</span>
              <span class="sig-role">{{ data_get($certificate, 'unit_penandatangan', 'Pembimbing Magang') }}</span>
            </div>
          </div>
          <div class="verify-cell divider">
            <div class="verify-divider"></div>
          </div>
          <div class="verify-cell qr">
            <div class="qr-code">
              <svg width="66" height="66" viewBox="0 0 66 66">
                <g fill="#e8cd7a">
                  <rect x="0" y="0" width="18" height="18"/><rect x="4" y="4" width="10" height="10" fill="#0d1638"/>
                  <rect x="48" y="0" width="18" height="18"/><rect x="52" y="4" width="10" height="10" fill="#0d1638"/>
                  <rect x="0" y="48" width="18" height="18"/><rect x="4" y="52" width="10" height="10" fill="#0d1638"/>
                  <rect x="24" y="0" width="6" height="6"/><rect x="34" y="6" width="6" height="6"/>
                  <rect x="24" y="24" width="6" height="6"/><rect x="34" y="24" width="6" height="6"/>
                  <rect x="44" y="24" width="6" height="6"/><rect x="24" y="34" width="6" height="6"/>
                  <rect x="54" y="34" width="6" height="6"/><rect x="44" y="44" width="6" height="6"/>
                  <rect x="24" y="54" width="6" height="6"/><rect x="34" y="54" width="6" height="6"/>
                  <rect x="54" y="54" width="6" height="6"/>
                </g>
              </svg>
              <div class="qr-label">ID&middot;{{ data_get($certificate, 'nomor_sertifikat', '-') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (! $pdfMode)
    <div class="print-actions">
      <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
  @endif
</body>
</html>
