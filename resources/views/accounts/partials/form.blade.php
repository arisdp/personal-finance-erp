@if(isset($account))
<div class="form-group mb-3">
    <label>Category</label>
    <select name="category" class="form-control" required>
        @foreach(['asset','liability','equity','income','expense'] as $cat)
        <option value="{{ $cat }}" @if(old('category', $account->category ?? '') == $cat) selected @endif>
            {{ ucfirst($cat) }}
        </option>
        @endforeach
    </select>
</div>
@endif

<div class="form-group mb-3">
    <label>Code</label>
    <input type="text" name="code"
        value="{{ old('code', $account->code ?? '') }}"
        class="form-control" required>
</div>

<div class="form-group mb-3">
    <label>Name</label>
    <input type="text" name="name"
        value="{{ old('name', $account->name ?? '') }}"
        class="form-control" required>
</div>

<div class="form-group mb-3">
    <label>Type</label>
    <select name="type" class="form-control" required>
        @foreach(['asset','liability','equity','income','expense'] as $type)
        <option value="{{ $type }}"
            @if(old('type', $account->type ?? '') == $type) selected @endif>
            {{ ucfirst($type) }}
        </option>
        @endforeach
    </select>
</div>

<div class="form-group mb-3">
    <label>Parent Account</label>
    <select name="parent_id" class="form-control">
        <option value="">-- None --</option>
        @foreach($parents as $parent)
        <option value="{{ $parent->id }}"
            @if(old('parent_id', $account->parent_id ?? '') == $parent->id) selected @endif>
            {{ $parent->code }} - {{ $parent->name }}
        </option>
        @endforeach
    </select>
</div>

<div class="card bg-light mt-4">
    <div class="card-body">
        <div class="custom-control custom-switch mb-3">
            <input type="checkbox" class="custom-control-input" id="track_limit" name="track_limit" value="1" 
                @if(old('track_limit', $account->track_limit ?? false)) checked @endif>
            <label class="custom-control-label" for="track_limit">Lacak Batas Kredit (Untuk Kartu Kredit / Paylater)</label>
        </div>

        <div class="form-group mb-0" id="credit_limit_container" style="display: {{ old('track_limit', $account->track_limit ?? false) ? 'block' : 'none' }};">
            <label>Batas Kredit (Credit Limit)</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Rp</span>
                </div>
                <input type="text" name="credit_limit" class="form-control autonumeric" 
                    value="{{ old('credit_limit', $account->credit_limit ?? 0) }}">
            </div>
            <small class="text-muted">Masukkan limit maksimal yang diberikan oleh bank/penyedia layanan.</small>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trackLimitSwitch = document.getElementById('track_limit');
        const creditLimitContainer = document.getElementById('credit_limit_container');

        trackLimitSwitch.addEventListener('change', function() {
            if (this.checked) {
                creditLimitContainer.style.display = 'block';
            } else {
                creditLimitContainer.style.display = 'none';
            }
        });

        // Initialize AutoNumeric if available
        if (typeof AutoNumeric !== 'undefined') {
            new AutoNumeric.multiple('.autonumeric', {
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 0,
                unformatOnSubmit: true
            });
        }
    });
</script>