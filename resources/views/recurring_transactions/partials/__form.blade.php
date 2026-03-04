<div class="form-group">
    <label for="name">Nama Tagihan / Transaksi Berulang</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
           value="{{ old('name', $recurring->name ?? '') }}" required placeholder="Contoh: Tagihan Listrik PLN">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="description">Keterangan (Opsional)</label>
    <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $recurring->description ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="amount_display">Nominal Pembayaran (Rp)</label>
            <input type="text" id="amount_display" class="form-control numeric-input @error('amount') is-invalid @enderror" 
                   value="{{ old('amount', $recurring->amount ?? '') }}" required>
            <input type="hidden" name="amount" id="amount_hidden" value="{{ old('amount', $recurring->amount ?? '') }}">
            @error('amount')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="frequency">Frekuensi</label>
            <select name="frequency" id="frequency" class="form-control @error('frequency') is-invalid @enderror" required>
                <option value="monthly" {{ (old('frequency', $recurring->frequency ?? '') == 'monthly') ? 'selected' : '' }}>Bulanan (Monthly)</option>
                <option value="weekly" {{ (old('frequency', $recurring->frequency ?? '') == 'weekly') ? 'selected' : '' }}>Mingguan (Weekly)</option>
                <option value="yearly" {{ (old('frequency', $recurring->frequency ?? '') == 'yearly') ? 'selected' : '' }}>Tahunan (Yearly)</option>
            </select>
            @error('frequency')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="debit_account_id">Akun Beban / Kategori (Debit)</label>
            <select name="debit_account_id" id="debit_account_id" class="form-control select2 @error('debit_account_id') is-invalid @enderror" required>
                <option value="">-- Pilih Akun --</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ (old('debit_account_id', $recurring->debit_account_id ?? '') == $account->id) ? 'selected' : '' }}>
                        {{ $account->code }} - {{ $account->name }}
                    </option>
                @endforeach
            </select>
            @error('debit_account_id')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="credit_account_id">Sumber Dana / Pembayaran (Credit)</label>
            <select name="credit_account_id" id="credit_account_id" class="form-control select2 @error('credit_account_id') is-invalid @enderror" required>
                <option value="">-- Pilih Akun --</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ (old('credit_account_id', $recurring->credit_account_id ?? '') == $account->id) ? 'selected' : '' }}>
                        {{ $account->code }} - {{ $account->name }}
                    </option>
                @endforeach
            </select>
            @error('credit_account_id')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="next_due_date">Tanggal Jatuh Tempo Berikutnya</label>
            <input type="date" name="next_due_date" id="next_due_date" class="form-control @error('next_due_date') is-invalid @enderror" 
                   value="{{ old('next_due_date', isset($recurring) ? $recurring->next_due_date->format('Y-m-d') : '') }}" required>
            @error('next_due_date')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="day_of_month">Tanggal Rutin (1-31)</label>
            <input type="number" name="day_of_month" id="day_of_month" class="form-control @error('day_of_month') is-invalid @enderror" 
                   value="{{ old('day_of_month', $recurring->day_of_month ?? '') }}" placeholder="Kosongkan jika tidak tetap">
            @error('day_of_month')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
               {{ old('is_active', $recurring->is_active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_active">Status Aktif</label>
    </div>
</div>
