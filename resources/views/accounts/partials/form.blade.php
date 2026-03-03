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