<!-- PROFILE MODAL -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4">

            <div class="modal-header">
                <h5 class="modal-title text-danger" id="profileModalTitle">My Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="profileModalBody">

                <!-- SUCCESS -->
                @if (session('profile_success'))
                    <div id="profile-success-alert" class="alert alert-success">
                        {{ session('profile_success') }}
                    </div>
                @endif

                <!-- ERRORS -->
                @if ($errors->any() && session('profile_error'))
                    <div id="profile-error-alert" class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- PROFILE FORM -->
                <form method="POST" action="{{ route('admin.profile.update') }}" id="profileForm">
                    @csrf
                    @method('PUT')

                    <!-- NAME -->
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                            class="form-control form-control-lg">
                    </div>

                    <!-- EMAIL -->
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                            class="form-control form-control-lg">
                    </div>

                    <hr>
                    <h6 class="text-danger">Change Password</h6>

                    <!-- CURRENT PASSWORD -->
                    <div class="mb-3">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control form-control-lg">
                    </div>

                    <!-- NEW PASSWORD -->
                    <div class="mb-3">
                        <label>New Password</label>
                        <input type="password" name="password" class="form-control form-control-lg">
                    </div>

                    <!-- CONFIRM -->
                    <div class="mb-3">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-lg">
                    </div>

                    <button class="btn btn-danger w-100 rounded-pill">
                        Update Profile
                    </button>

                    <!-- FORGOT PASSWORD -->
                    <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="forgotPasswordBtn">
                        Forgot Password?
                    </button>
                </form>

                <!-- FORGOT PASSWORD FORM (HIDDEN BY DEFAULT) -->
                <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" class="d-none">
                    @csrf
                    <p>Enter your email to reset your password:</p>

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    
                    <div class="mb-3">
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                            class="form-control form-control-lg" placeholder="Email" required>
                    </div>
                    <button class="btn btn-danger w-100 rounded-pill">Send Password Reset Link</button>
                    <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="backToProfileBtn">
                        Back to Profile
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var profileModalEl = document.getElementById('profileModal');
        var profileModal = new bootstrap.Modal(profileModalEl);
        var profileForm = document.getElementById('profileForm');
        var forgotForm = document.getElementById('forgotPasswordForm');
        var forgotBtn = document.getElementById('forgotPasswordBtn');
        var backBtn = document.getElementById('backToProfileBtn');
        var modalTitle = document.getElementById('profileModalTitle');
        var modalBody = document.getElementById('profileModalBody');

        // Show modal if there are errors or success messages
        @if (session('profile_success') || ($errors->any() && session('profile_error')))
            profileModal.show();
        @endif

        @if (session('status'))
            profileModal.show();

            // automatically show forgot password form
            profileForm.classList.add('d-none');
            forgotForm.classList.remove('d-none');
            modalTitle.textContent = 'Forgot Password';
        @endif

        // Toggle to Forgot Password form
        forgotBtn.addEventListener('click', function() {
            profileForm.classList.add('d-none');
            forgotForm.classList.remove('d-none');
            modalTitle.textContent = 'Forgot Password';
        });

        // Back to Profile form
        backBtn.addEventListener('click', function() {
            forgotForm.classList.add('d-none');
            profileForm.classList.remove('d-none');
            modalTitle.textContent = 'My Profile';
        });

        // Auto-hide alerts
        ['profile-success-alert', 'profile-error-alert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) setTimeout(() => el.remove(), id === 'profile-success-alert' ? 3000 : 5000);
        });
    });
</script>
