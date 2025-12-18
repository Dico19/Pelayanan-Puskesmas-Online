<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekamMedik extends Model
{
    protected $table = 'rekam_mediks';

    protected $fillable = [
        'antrian_id',
        'dokter_id',
        'no_ktp',
        'poli',
        'tanggal_kunjungan',
        'diagnosa',
        'catatan',
        'resep',
    ];
}
