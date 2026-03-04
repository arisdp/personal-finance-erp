@extends('adminlte::page')

@section('title', 'Tambah Aset Investasi')

@section('content_header')
    <h1>Tambah Aset Investasi</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Input Aset Baru</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('investments.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label>Akun Terkait (COA)</label>
                            <select name="account_id" class="form-control select2" required>
                                <option value="">-- Pilih Akun Aset --</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih akun asset (misal: Emas, Portofolio Saham) yang sesuai di COA.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Nama Aset</label>
                                    <input type="text" name="asset_name" class="form-control" placeholder="Contoh: Emas Antam 10g / Saham BBCA" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label>Tipe Aset</label>
                                    <select name="asset_type" class="form-control" required>
                                        <option value="gold">Emas</option>
                                        <option value="stock">Saham</option>
                                        <option value="crypto">Crypto</option>
                                        <option value="property">Properti</option>
                                        <option value="mutual_fund">Reksadana</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label>Ticker (Opsional)</label>
                                    <input type="text" name="ticker" class="form-control" placeholder="BBCA, BTC, dll">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Jumlah (Quantity)</label>
                                    <input type="number" step="0.000001" name="quantity" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Harga Beli Rata-rata (Avg Price)</label>
                                    <input type="text" id="avg_buy_price_display" class="form-control numeric-input" placeholder="0" required>
                                    <input type="hidden" name="avg_buy_price" id="avg_buy_price_hidden">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Harga Pasar Saat Ini</label>
                                    <input type="text" id="current_price_display" class="form-control numeric-input" placeholder="0" required>
                                    <input type="hidden" name="current_price" id="current_price_hidden">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <a href="{{ route('investments.index') }}" class="btn btn-secondary px-4 mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary px-5">Simpan Aset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    });
</script>
@stop
