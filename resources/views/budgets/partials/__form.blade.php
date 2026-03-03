<div class="form-group">
    <label for="account_id">Akun Pengeluaran</label>
    <select name="account_id" id="account_id" class="form-control select2 @error('account_id') is-invalid @enderror" required>
        <option value="">-- Pilih Akun --</option>
        @foreach($accounts as $account)
            <option value="{{ $account->id }}" {{ (old('account_id', $budget->account_id ?? '') == $account->id) ? 'selected' : '' }}>
                {{ $account->code }} - {{ $account->name }}
            </option>
        @endforeach
    </select>
    @error('account_id')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="amount">Alokasi Anggaran (Rp)</label>
    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
           value="{{ old('amount', $budget->amount ?? '') }}" required min="0" step="0.01">
    @error('amount')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="month">Bulan</label>
            <select name="month" id="month" class="form-control @error('month') is-invalid @enderror" required>
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ (old('month', $budget->month ?? date('n')) == $m) ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endforeach
            </select>
            @error('month')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="year">Tahun</label>
            <input type="number" name="year" id="year" class="form-control @error('year') is-invalid @enderror" 
                   value="{{ old('year', $budget->year ?? date('Y')) }}" required>
            @error('year')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
