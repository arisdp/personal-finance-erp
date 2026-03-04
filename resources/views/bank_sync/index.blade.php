@extends('adminlte::page')

@section('title', 'Bank Sync')

@section('content_header')
    <h1><i class="fas fa-university mr-2"></i> Bank Statement Sync (Beta)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-outline card-primary shadow">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Impor Mutasi Rekening</h3>
            </div>
            <form action="{{ route('bank_sync.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Petunjuk</h5>
                        Unggah file CSV mutasi dari e-banking Anda. Sistem akan mencoba memetakan transaksi secara otomatis.
                    </div>

                    <div class="form-group">
                        <label for="account_id">Pilih Rekening Bank (COA)</label>
                        <select name="account_id" id="account_id" class="form-control select2" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bank_type">Format Bank</label>
                        <select name="bank_type" id="bank_type" class="form-control">
                            <option value="bca">BCA (CSV)</option>
                            <option value="mandiri">Mandiri (CSV)</option>
                            <option value="other">Format Umum (CSV)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bank_file">File Mutasi (CSV/TXT)</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="bank_file" id="bank_file" accept=".csv,.txt" required>
                                <label class="custom-file-label" for="bank_file">Pilih file...</label>
                            </div>
                        </div>
                        <small class="text-muted">Pastikan file dalam format CSV yang benar.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i> Preview Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@stop
