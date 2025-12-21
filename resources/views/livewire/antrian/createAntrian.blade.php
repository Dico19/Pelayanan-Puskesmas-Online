<!-- Modal -->
<div wire:ignore.self class="modal fade" id="createAntrian" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                {{-- judul beda tergantung step --}}
                <h1 class="modal-title fs-5" id="exampleModalLabel">
                    @if ($step == 1)
                        Pilih Poli
                    @elseif($step == 2)
                        Pilih Tanggal
                    @else
                        Form Ambil Antrian
                    @endif
                </h1>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        wire:click="close_modal"
                        aria-label="Close"></button>
            </div>

            <form wire:submit.prevent="save">
                <div class="modal-body">

                    {{-- ===================== STEP 1 : PILIH POLI ===================== --}}
                    @if ($step == 1)
                        <div class="text-center mb-3">
                            <h5 class="fw-bold mb-1">Pilih Poli / Klinik</h5>
                            <div class="text-muted" style="font-size: 13px;">
                                Silakan klik salah satu poli untuk mengambil antrian.
                            </div>
                        </div>

                        <div class="poli-grid">
                            @foreach ($listPoli as $kode => $namaPoli)
                                <button type="button"
                                        wire:key="poli-{{ md5($kode) }}"
                                        class="poli-card {{ $poli === $kode ? 'is-selected' : '' }}"
                                        wire:click="pilihPoli(@js($kode))"
                                        wire:loading.attr="disabled">

                                    <div class="poli-icon">
                                        @switch($kode)
                                            @case('umum') <i class="fas fa-user-md"></i> @break
                                            @case('gigi') <i class="fas fa-tooth"></i> @break
                                            @case('tht') <i class="fas fa-ear-listen"></i> @break
                                            @case('balita') <i class="fas fa-child"></i> @break
                                            @case('lansia & disabilitas') <i class="fas fa-person-cane"></i> @break
                                            @case('kia & kb') <i class="fas fa-baby"></i> @break
                                            @case('nifas/pnc') <i class="fas fa-hospital-user"></i> @break
                                            @default <i class="fas fa-clinic-medical"></i>
                                        @endswitch
                                    </div>

                                    <div class="poli-label">
                                        {{ strtoupper($namaPoli) }}
                                    </div>

                                </button>
                            @endforeach
                        </div>

                    {{-- ===================== STEP 2 : PILIH TANGGAL ===================== --}}
                    @elseif($step == 2)
                        <div class="mb-3">
                            <div class="fw-semibold">Pilih Tanggal Layanan</div>
                            <small class="text-muted">
                                Maksimal 6 hari ke depan. Hari Minggu libur dan tidak bisa dipilih.
                            </small>
                        </div>

                        <div class="tanggal-grid">
                            @foreach ($tanggalPilihan as $item)
                                @php
                                    $isSelected = $tanggal_antrian === $item['date'];
                                @endphp

                                <button type="button"
                                        wire:key="tgl-{{ $item['date'] }}"
                                        class="tanggal-card
                                            {{ $item['is_libur'] ? 'is-libur' : 'is-aktif' }}
                                            {{ $isSelected ? 'is-selected' : '' }}"
                                        @if(!$item['is_libur'])
                                            wire:click="pilihTanggal(@js($item['date']))"
                                        @endif
                                        @if($item['is_libur']) disabled @endif
                                        wire:loading.attr="disabled">

                                    <div class="hari">{{ $item['hari'] }}</div>
                                    <div class="tanggal">{{ $item['tanggal'] }}</div>
                                    <div class="bulan">{{ $item['bulan_tahun'] }}</div>

                                    @if(!$item['is_libur'])
                                        <div class="jumlah">Antrian: {{ $item['jumlah'] }}</div>
                                    @else
                                        <div class="jumlah">Libur</div>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-3 d-flex justify-content-between">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    wire:click="kembaliKePilihPoli"
                                    wire:loading.attr="disabled">
                                &larr; Ganti Poli
                            </button>
                        </div>

                    {{-- ===================== STEP 3 : FORM ANTRIAN ===================== --}}
                    @else
                        {{-- Header ringkas poli & tanggal --}}
                        <div class="pk-form-head mb-3">
                            <div class="pk-form-head__left">
                                <div class="pk-head-label">Poli</div>
                                <div class="pk-head-value">
                                    <span class="pk-badge pk-badge--blue">
                                        <i class="bi bi-hospital me-1"></i>
                                        {{ $listPoli[$poli] ?? '-' }}
                                    </span>
                                </div>

                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm pk-btn-soft"
                                            wire:click="kembaliKePilihTanggal"
                                            wire:loading.attr="disabled">
                                        <i class="bi bi-calendar2-week me-1"></i> Ganti Tanggal
                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm pk-btn-soft"
                                            wire:click="kembaliKePilihPoli"
                                            wire:loading.attr="disabled">
                                        <i class="bi bi-arrow-left-circle me-1"></i> Ganti Poli
                                    </button>
                                </div>
                            </div>

                            <div class="pk-form-head__right text-end">
                                <div class="pk-head-label">Tanggal Layanan</div>
                                <div class="pk-head-value">
                                    <span class="pk-badge pk-badge--green">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        {{ \Carbon\Carbon::parse($tanggal_antrian)->format('d-m-Y') }}
                                    </span>
                                </div>
                            </div>

                            <input type="hidden" wire:model="poli">
                            <input type="hidden" wire:model="tanggal_antrian">

                            @error('poli') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            @error('tanggal_antrian') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        {{-- Form fields --}}
                        <div class="pk-form-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label pk-label">Nama Lengkap</label>
                                    <input type="text" wire:model.defer="nama" class="form-control pk-input" placeholder="Contoh: Budi Santoso">
                                    @error('nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label pk-label">Jenis Kelamin</label>
                                    <select class="form-select pk-input" wire:model.defer="jenis_kelamin">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="laki-laki">Laki-laki</option>
                                        <option value="perempuan">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label pk-label">Alamat</label>
                                    <textarea class="form-control pk-input" wire:model.defer="alamat" rows="2" placeholder="Alamat lengkap..."></textarea>
                                    @error('alamat') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label pk-label">Nomor HP</label>
                                    <input type="text"
                                           wire:model.lazy="no_hp"
                                           class="form-control pk-input"
                                           placeholder="08xxxxxxxxxx"
                                           inputmode="numeric"
                                           autocomplete="off">
                                    @error('no_hp') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label pk-label">NIK / Nomor KTP</label>
                                    <input type="text"
                                           wire:model.lazy="no_ktp"
                                           class="form-control pk-input"
                                           placeholder="16 digit (contoh: 3201xxxxxxxxxxxx)"
                                           inputmode="numeric"
                                           maxlength="16"
                                           autocomplete="off">
                                    <div class="pk-help">Wajib 16 digit angka.</div>
                                    @error('no_ktp') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label pk-label">Tanggal Lahir</label>
                                    <input type="date" wire:model.defer="tgl_lahir" class="form-control pk-input">
                                    @error('tgl_lahir') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label pk-label">Pekerjaan</label>
                                    <input type="text" wire:model.defer="pekerjaan" class="form-control pk-input" placeholder="Contoh: Pelajar / Wiraswasta">
                                    @error('pekerjaan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="pk-note mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Pastikan data benar. Setelah disimpan, Anda akan mendapatkan nomor antrian.
                            </div>

                            {{-- ✅ TOMBOL AMBIL ANTRIAN (SUBMIT) --}}
                            <div class="mt-3 d-grid gap-2">
                                <button type="submit"
                                        class="btn btn-primary btn-lg pk-submit"
                                        wire:loading.attr="disabled"
                                        wire:target="save">
                                    <i class="bi bi-ticket-perforated me-2"></i>
                                    Ambil Antrian Sekarang
                                </button>

                                <div class="text-center small text-muted" wire:loading wire:target="save">
                                    Memproses data...
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                            wire:click="close_modal"
                            wire:loading.attr="disabled">
                        Keluar
                    </button>

                    {{-- Optional: kalau mau tombol submit juga muncul di footer, aktifkan ini --}}
                    {{-- @if ($step == 3)
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            Simpan
                        </button>
                    @endif --}}
                </div>
            </form>

        </div>
    </div>
</div>
