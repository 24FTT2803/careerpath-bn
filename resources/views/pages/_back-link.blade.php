@php
    /*
     * Send people back to where they came from (sign-up, the home page,
     * their dashboard...) instead of always to registration.
     */
    $previousUrl = url()->previous();
    $previousPath = '/' . trim((string) parse_url($previousUrl, PHP_URL_PATH), '/');
    $sameSite = str_starts_with($previousUrl, url('/'));
    $legalPage = in_array($previousPath, ['/privacy', '/terms'], true);

    if ($sameSite && ! $legalPage && $previousUrl !== url()->current()) {
        $backUrl = $previousUrl;
        $backLabel = match (true) {
            $previousPath === '/register' => 'Back to Registration',
            $previousPath === '/login' => 'Back to Log In',
            $previousPath === '/' => 'Back to Home',
            str_contains($previousPath, 'dashboard') => 'Back to Dashboard',
            default => 'Back',
        };
    } elseif (auth()->check()) {
        $backUrl = route('dashboard');
        $backLabel = 'Back to Dashboard';
    } else {
        $backUrl = url('/');
        $backLabel = 'Back to Home';
    }
@endphp

<a href="{{ $backUrl }}" class="back-link">← {{ $backLabel }}</a>
