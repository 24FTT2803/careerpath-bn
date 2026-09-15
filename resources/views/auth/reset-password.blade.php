<main class="auth-main">
    <div class="container">
        <div class="auth-card">
            <a href="{{ route('login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Log In
            </a>

            <div class="badge"><i class="fas fa-key"></i> Account Recovery</div>
            <h1>Choose a New Password</h1>
            <p class="subtitle">Make it something you haven't used before.</p>

            @if ($errors->any())
                <div class="status status-error">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="field">
                    <label for="email">Email <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            placeholder="you@pb.edu.bn"
                            required
                            autofocus
                            class="{{ $errors->has('email') ? 'error-input' : '' }}"
                        >
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    @error('email')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password">New Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            class="{{ $errors->has('password') ? 'error-input' : '' }}"
                        >
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    @error('password')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm New Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Reset Password
                </button>
            </form>
        </div>
    </div>
</main>