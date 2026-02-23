<div class="mb-3">
    <label>Name</label>
    <input type="text" name="name"
        value="{{ old('name', $user->name ?? '') }}"
        class="form-control" required>
</div>

<div class="mb-3">
    <label>Email</label>
    <input type="email" name="email"
        value="{{ old('email', $user->email ?? '') }}"
        class="form-control" required>
</div>

<div class="mb-3">
    <label>Password</label>
    <input type="password" name="password"
        class="form-control"
        {{ isset($user) ? '' : 'required' }}>
</div>

<div class="mb-3">
    <label>Confirm Password</label>
    <input type="password" name="password_confirmation"
        class="form-control"
        {{ isset($user) ? '' : 'required' }}>
</div>

<div class="mb-3">
    <label>Role</label>
    <select name="role" class="form-control" required>
        <option value="user"
            @if(old('role', $user->role ?? '') == 'user') selected @endif>
            User
        </option>
        <option value="super_admin"
            @if(old('role', $user->role ?? '') == 'super_admin') selected @endif>
            Super Admin
        </option>
    </select>
</div>