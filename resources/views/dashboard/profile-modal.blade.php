@php($profile = auth()->user())
<dialog id="profile-modal" class="order-dialog">
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')
        <div class="dialog-head">
            <div>
                <span class="eyebrow eyebrow-muted">PROFIL {{ $profile->role === 'merchant' ? 'KATERING' : 'KANTOR' }}</span>
                <h2>Edit profil</h2>
            </div>
            <button type="button" class="dialog-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button>
        </div>
        <label>Nama kontak<input name="name" value="{{ $profile->name }}" required maxlength="100"></label>
        <label>Nama {{ $profile->role === 'merchant' ? 'perusahaan katering' : 'kantor' }}<input name="company_name" value="{{ $profile->company_name }}" required maxlength="150"></label>
        <label>Email<input type="email" name="email" value="{{ $profile->email }}" required maxlength="255"></label>
        <label>Nomor telepon<input name="phone" value="{{ $profile->phone }}" required maxlength="30"></label>
        @if($profile->role === 'merchant')
            <label>Jenis makanan<input name="food_type" value="{{ $profile->food_type }}" maxlength="80"></label>
        @endif
        <label>Alamat<textarea name="address" required rows="2" maxlength="1000">{{ $profile->address }}</textarea></label>
        @if($profile->role === 'merchant')
            <label>Deskripsi<textarea name="description" rows="3" maxlength="1500">{{ $profile->description }}</textarea></label>
        @endif
        <button class="button button-primary button-wide">Simpan profil</button>
    </form>
</dialog>
