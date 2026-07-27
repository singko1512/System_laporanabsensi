<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sertifikat Magang - {{ $user->nama }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .font-serif-display { font-family: 'Playfair Display', serif; }
  @media print {
    body { background: #fff !important; padding: 0 !important; }
    .print-actions { display: none !important; }
    .certificate-wrap { box-shadow: none !important; max-width: none !important; width: 100% !important; }
  }
</style>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4 md:p-10">
  @php
      $tanggalMulai = $user->tanggal_mulai_magang
          ? $user->tanggal_mulai_magang->translatedFormat('j F Y')
          : '-';
      $tanggalSelesai = $user->tanggal_selesai_magang->translatedFormat('j F Y');
  @endphp

  <div class="certificate-wrap relative w-full max-w-3xl bg-white border-[3px] border-blue-900 rounded-md p-3 shadow-xl">
    <div class="border border-slate-300 rounded-sm px-8 py-10 md:px-12 md:py-12">

      <div class="flex items-center justify-center mb-10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-12 mt-3 rounded-sm border border-blue-900 text-blue-900 flex items-center justify-center font-serif-display font-bold text-sm leading-none">
            DB
          </div>
          <span class="font-serif-display text-lg md:text-xl font-bold tracking-wide text-blue-900">Diskominfo Kabupaten Bogor</span>
        </div>
      </div>

      <div class="text-center">
        <p class="text-amber-600 tracking-[0.2em] text-xs md:text-sm font-semibold mb-2">SERTIFIKAT PENGHARGAAN MAGANG</p>
        <h1 class="font-serif-display text-4xl md:text-5xl font-bold text-slate-900 mb-6">Certificate of Completion</h1>

        <p class="text-slate-500 text-sm md:text-base mb-2">Sertifikat ini dengan bangga diberikan kepada:</p>
        <h2 class="font-serif-display text-3xl md:text-4xl font-bold text-blue-900 mb-2">{{ $user->nama }}</h2>
        <div class="w-72 md:w-96 h-px bg-slate-200 mx-auto mb-6"></div>

        <p class="text-slate-500 text-sm md:text-base leading-relaxed max-w-xl mx-auto mb-10">
          Selamat kepada <span class="font-semibold text-slate-700">{{ $user->nama }}</span> atas kelulusan Program Magang Industri (Internship Program) di
          <span class="font-semibold text-slate-700">Diskominfo Kabupaten Bogor</span>, pada team
          <span class="font-semibold text-slate-700">DoodleScript</span>. Dedikasi, semangat belajar, dan
          kontribusi yang telah diberikan selama masa magang, dari tanggal {{ $tanggalMulai }} hingga
          {{ $tanggalSelesai }}, patut diapresiasi. Semoga pengalaman ini menjadi bekal berharga untuk langkah
          karier selanjutnya.
        </p>
      </div>

      <div class="flex flex-col items-center pt-4">
        <div class="w-52 border-b border-slate-300 mb-2"></div>
        <p class="font-semibold text-slate-800 text-sm">Dr. Hendra Wijaya</p>
        <p class="text-xs text-slate-400">Head of Engineering</p>
      </div>

    </div>
  </div>

  <div class="print-actions fixed right-5 bottom-5">
    <button type="button" onclick="window.print()" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow-lg">
      Cetak / Simpan PDF
    </button>
  </div>
</body>
</html>
