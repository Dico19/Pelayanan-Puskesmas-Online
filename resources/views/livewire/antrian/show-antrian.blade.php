<section id="antrian" class="antrian-wrap">
    <div class="container" style="margin-top: 110px">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- HERO / HEADER --}}
        <div class="antrian-hero">
            <div>
                <h3 class="antrian-title mb-1">Ambil Antrian</h3>
                <div class="antrian-sub">Pilih poli, pilih tanggal, lalu isi data pasien.</div>
            </div>

            <button type="button"
                    class="btn antrian-cta"
                    data-bs-toggle="modal"
                    data-bs-target="#createAntrian"
                    wire:click="openCreate">
                <i class="bi bi-file-plus me-2"></i> Ambil Antrian Disini
            </button>
        </div>

        {{-- MAIN CARD --}}
        <div class="antrian-card">

            {{-- TOP BAR (title + filter) --}}
            <div class="antrian-card__top">
                <div class="label">
                    <i class="bi bi-list-check me-2"></i> Daftar Antrian
                </div>

                <div class="antrian-filter">
                    <select class="form-select" wire:model="filterPoli">
                        <option value="">Sortir Berdasarkan Poli</option>
                        <option value="umum">Poli Umum</option>
                        <option value="gigi">Poli Gigi</option>
                        <option value="tht">Poli THT</option>
                        <option value="lansia & disabilitas">Lansia & Disabilitas</option>
                        <option value="balita">Balita</option>
                        <option value="kia & kb">KIA & KB</option>
                        <option value="nifas/pnc">Nifas / PNC</option>
                    </select>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="antrian-table-wrap">
                <div class="table-responsive antrian-scroll">
                    <table class="table antrian-table mb-0" id="table_id">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>No Antrian</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>Jenis Kelamin</th>
                                <th>Nomor HP</th>
                                <th>Nomor KTP</th>
                                <th>Tgl Lahir</th>
                                <th>Pekerjaan</th>
                                <th>Poli</th>
                                <th>Tgl Antrian</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($antrian as $item)
                                <tr class="text-center">
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <span class="badge-poli">
                                            <i class="bi bi-hash"></i> {{ $item->no_antrian }}
                                        </span>
                                    </td>

                                    <td class="text-start">{{ $item->nama }}</td>
                                    <td class="text-start">{{ $item->alamat }}</td>

                                    <td>{{ $item->jenis_kelamin }}</td>

                                    <td>{{ $item->no_hp }}</td>

                                    <td>{{ substr_replace($item->no_ktp, '******', 4, 6) }}</td>

                                    <td>{{ $item->tgl_lahir }}</td>

                                    <td>{{ $item->pekerjaan }}</td>

                                    <td>
                                        <span class="badge-poli">
                                            <i class="bi bi-hospital"></i> {{ $item->poli }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge-date">
                                            <i class="bi bi-calendar-check"></i> {{ $item->tanggal_antrian }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">
                                        Belum ada data antrian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINATION --}}
            <div class="px-3 py-3">
                {{ $antrian->links() }}
            </div>
        </div>

        {{-- MODALS --}}
        @include('livewire.antrian.createAntrian')
        @include('livewire.antrian.editAntrian')
        @include('livewire.antrian.deleteAntrian')

    </div>
</section>
