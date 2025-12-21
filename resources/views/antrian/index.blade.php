@extends('layouts.main')

@section('title', 'Ambil Antrian')

@section('content')
    <div style="margin-top: 150px; min-height: 600px;">
        <livewire:antrian.show-antrian />
    </div>
@endsection

@push('scripts')
<script>
    window.addEventListener('closeModal', () => {
        const ids = ['createAntrian', 'editAntrian', 'deleteAntrian'];
        ids.forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;

            const instance = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            instance.hide();
        });
    });
</script>
@endpush
