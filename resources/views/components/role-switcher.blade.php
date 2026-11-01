{{--
    For an account that can be more than one role (a special
    account, or any admin trying the student side): which role it
    is using, and buttons to the others. Each role shows the
    account's own data, as it would for anyone of that role.
--}}
@auth
    @php
        $switcher = auth()->user();
        $switchRoles = $switcher->switchableRoles();
        $switchIcons = ['student' => 'fa-user-graduate', 'lecturer' => 'fa-chalkboard-user', 'admin' => 'fa-user-shield'];
    @endphp

    @if(count($switchRoles) > 1)
        <div class="role-switcher" role="region" aria-label="Switch role">
            <span class="role-switcher-now">
                <i class="fas {{ $switchIcons[$switcher->role] ?? 'fa-user' }}" aria-hidden="true"></i>
                <span>Viewing as <strong>{{ ucfirst($switcher->role) }}</strong></span>
            </span>

            @foreach($switchRoles as $switchRole)
                @continue($switchRole === $switcher->role)

                <form method="POST" action="{{ route('role.switch') }}" data-unsaved-ignore>
                    @csrf
                    <input type="hidden" name="role" value="{{ $switchRole }}">
                    <button type="submit" title="Use the site as {{ $switchRole === $switcher->homeRole() ? 'yourself' : 'a '.$switchRole }}">
                        <i class="fas {{ $switchIcons[$switchRole] ?? 'fa-user' }}" aria-hidden="true"></i>
                        <span>{{ ucfirst($switchRole) }}</span>
                    </button>
                </form>
            @endforeach
        </div>

        @once
            <style>
                .role-switcher {
                    position: fixed;
                    left: 16px;
                    bottom: 16px;
                    z-index: 1070;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    max-width: calc(100vw - 110px);
                    padding: 6px 6px 6px 12px;
                    border-radius: 999px;
                    background: #0d1f33;
                    color: #f5f1e6;
                    font-family: 'Inter', -apple-system, sans-serif;
                    font-size: 12.5px;
                    box-shadow: 0 6px 18px rgba(13, 31, 51, 0.3);
                }

                .role-switcher-now {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    margin-right: 4px;
                    white-space: nowrap;
                }

                .role-switcher-now i {
                    color: #c9a84c;
                }

                .role-switcher form {
                    margin: 0;
                }

                .role-switcher button {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 6px 12px;
                    border: 1px solid rgba(201, 168, 76, 0.5);
                    border-radius: 999px;
                    background: transparent;
                    color: #f5f1e6;
                    font: inherit;
                    cursor: pointer;
                    white-space: nowrap;
                }

                .role-switcher button:hover,
                .role-switcher button:focus-visible {
                    background: #c9a84c;
                    color: #0d1f33;
                }

                @media (max-width: 480px) {
                    .role-switcher-now span {
                        display: none;
                    }

                    .role-switcher button span {
                        display: none;
                    }
                }
            </style>
        @endonce
    @endif
@endauth
