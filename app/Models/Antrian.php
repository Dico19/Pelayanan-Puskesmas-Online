<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    use HasFactory;

    protected $table = 'antrians';

    protected $fillable = [
        'patient_id',
        'user_id',
        'no_antrian',
        'nama',
        'alamat',
        'jenis_kelamin',
        'no_hp',
        'no_ktp',
        'poli',
        'tgl_lahir',
        'pekerjaan',
        'is_call',
        'tanggal_antrian',

        // ✅ tambahan flow
        'status',
        'skip_count',
        'skipped_at',
        'absent_at',
    ];

    protected $casts = [
        'tanggal_antrian' => 'date',
        'skipped_at'      => 'datetime',
        'absent_at'       => 'datetime',
    ];

    // Relasi ke patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relasi ke user (kalau dipakai)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hook: setiap kali Antrian dibuat, otomatis buat / pakai Patient
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Antrian $antrian) {
            // Kalau sudah ada patient_id atau tidak ada NIK, lewati
            if ($antrian->patient_id || ! $antrian->no_ktp) {
                return;
            }

            // Cari pasien berdasarkan NIK
            $patient = Patient::where('no_ktp', $antrian->no_ktp)->first();

            if (! $patient) {
                // Buat pasien baru dari data antrian
                $patient = Patient::create([
                    'nama'          => $antrian->nama,
                    'alamat'        => $antrian->alamat,
                    'jenis_kelamin' => $antrian->jenis_kelamin,
                    'no_hp'         => $antrian->no_hp,
                    'no_ktp'        => $antrian->no_ktp,
                    'tgl_lahir'     => $antrian->tgl_lahir,
                    'pekerjaan'     => $antrian->pekerjaan,
                ]);
            }

            // Isi patient_id di antrian
            $antrian->patient_id = $patient->id;
        });
    }
}
