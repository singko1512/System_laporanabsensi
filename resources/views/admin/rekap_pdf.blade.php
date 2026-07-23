<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi {{ $namaBulan }} {{ $year }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px double #333;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
            font-size: 16pt;
            font-weight: bold;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 10pt;
            color: #555;
        }

        .meta-info {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9.5pt;
        }

        .meta-info td {
            padding: 3px 0;
        }

        .table-rekap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9.5pt;
        }

        .table-rekap th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            text-align: center;
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table-rekap td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .signature-container {
            margin-top: 50px;
            width: 100%;
        }

        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
            font-size: 10pt;
        }

        .signature-space {
            height: 70px;
        }
    </style>
</head>
<body>

    <!-- Report Header -->
    <div class="header">
        <h2>Laporan Rekapitulasi Absensi Pegawai</h2>
        <p>Sistem Laporan Absensi Harian Terintegrasi - AbsensiKita</p>
    </div>

    <!-- Metadata Info -->
    <table class="meta-info">
        <tr>
            <td width="15%"><strong>Bulan / Tahun</strong></td>
            <td width="3%">:</td>
            <td>{{ $namaBulan }} {{ $year }}</td>
            <td width="20%" class="text-right"><strong>Hari Kerja Efektif</strong></td>
            <td width="3%" class="text-right">:</td>
            <td width="12%" class="text-right">{{ $totalWorkdays }} Hari Kerja</td>
        </tr>
        <tr>
            <td><strong>Tanggal Cetak</strong></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</td>
            <td colspan="3"></td>
        </tr>
    </table>

    <!-- Main Data Table -->
    <table class="table-rekap">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Nama Pegawai</th>
                <th width="20%">Email</th>
                <th width="8%">Hadir</th>
                <th width="8%">WFH</th>
                <th width="8%">Sakit</th>
                <th width="8%">Izin</th>
                <th width="13%">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekapData as $index => $data)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $data->user->nama }}</td>
                    <td>{{ $data->user->email ?? '-' }}</td>
                    <td class="text-center">{{ $data->hadir }}</td>
                    <td class="text-center">{{ $data->wfh }}</td>
                    <td class="text-center">{{ $data->sakit }}</td>
                    <td class="text-center">{{ $data->izin }}</td>
                    <td class="text-center font-bold" style="background-color: #fafafa;">{{ $data->persentase }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="signature-container">
        <div class="signature-box">
            <p>Mengetahui,</p>
            <p><strong>Penanggung Jawab</strong></p>
            <div class="signature-space"></div>
            <p style="border-bottom: 1px solid #333; padding-bottom: 2px; display: inline-block; width: 100%;"></p>
            <p style="margin-top: 3px;">Sistem AbsensiKita</p>
        </div>
    </div>

</body>
</html>
