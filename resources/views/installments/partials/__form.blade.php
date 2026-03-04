<div class="form-group">
    <label for="name">Nama Pinjaman / Cicilan</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
           value="{{ old('name', $installment->name ?? '') }}" required placeholder="Contoh: Cicilan KPR Rumah, Kredit HP">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="account_id">Akun Kontra (Hutang)</label>
    <select name="account_id" id="account_id" class="form-control select2 @error('account_id') is-invalid @enderror" required>
        <option value="">-- Pilih Akun --</option>
        @foreach($accounts as $account)
            <option value="{{ $account->id }}" {{ (old('account_id', $installment->account_id ?? '') == $account->id) ? 'selected' : '' }}>
                {{ $account->code }} - {{ $account->name }} ({{ strtoupper($account->category) }})
            </option>
        @endforeach
    </select>
    <small class="text-muted">Biasanya akun kategori 'Liability'.</small>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="total_amount_display">Total Pokok Pinjaman (Rp)</label>
            <input type="text" id="total_amount_display" class="form-control numeric-input @error('total_amount') is-invalid @enderror" 
                   value="{{ old('total_amount', $installment->total_amount ?? '') }}" required>
            <input type="hidden" name="total_amount" id="total_amount_hidden" value="{{ old('total_amount', $installment->total_amount ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="monthly_amount_display">Angsuran per Bulan (Rp)</label>
            <input type="text" id="monthly_amount_display" class="form-control numeric-input @error('monthly_amount') is-invalid @enderror" 
                   value="{{ old('monthly_amount', $installment->monthly_amount ?? '') }}" required>
            <input type="hidden" name="monthly_amount" id="monthly_amount_hidden" value="{{ old('monthly_amount', $installment->monthly_amount ?? '') }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="total_periods">Total Tenor (Bulan)</label>
            <input type="number" name="total_periods" id="total_periods" class="form-control @error('total_periods') is-invalid @enderror" 
                   value="{{ old('total_periods', $installment->total_periods ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="remaining_periods">Sisa Tenor (Bulan)</label>
            <input type="number" name="remaining_periods" id="remaining_periods" class="form-control @error('remaining_periods') is-invalid @enderror" 
                   value="{{ old('remaining_periods', $installment->remaining_periods ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="interest_rate">Bunga per Tahun (%)</label>
            <input type="number" name="interest_rate" id="interest_rate" class="form-control @error('interest_rate') is-invalid @enderror" 
                   value="{{ old('interest_rate', $installment->interest_rate ?? 0) }}" step="0.01">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="start_date">Tanggal Mulai</label>
            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                   value="{{ old('start_date', isset($installment) ? $installment->start_date->format('Y-m-d') : date('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="active" {{ (old('status', $installment->status ?? '') == 'active') ? 'selected' : '' }}>Aktif (Running)</option>
                <option value="completed" {{ (old('status', $installment->status ?? '') == 'completed') ? 'selected' : '' }}>Lunas (Completed)</option>
                <option value="cancelled" {{ (old('status', $installment->status ?? '') == 'cancelled') ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
            </select>
        </div>
    </div>
</div>
