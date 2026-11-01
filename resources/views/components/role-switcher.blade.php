@props(['variant' => 'sidebar'])

{{--
    For an account that can be more than one role (a special
    account, or any admin trying the student side): a way to each
    of its other roles, as items in the menu it already has. Each
    role shows the account's own data, as it would for anyone of
    that role.

    variant "sidebar": links in the admin and lecturer side menu.
    variant "menu": items in the student account menu.
--}}
@auth
    @php
        $switcher = auth()->user();
        $switchRoles = array_values(array_diff($switcher->switchableRoles(), [$switcher->role]));
        $switchIcons = ['student' => 'fa-user-graduate', 'lecturer' => 'fa-chalkboard-user', 'admin' => 'fa-user-shield'];
    @endphp

    @foreach($switchRoles as $switchRole)
        @php
            $switchLabel = $switchRole === $switcher->homeRole()
                ? 'Back to '.ucfirst($switchRole)
                : 'View as '.ucfirst($switchRole);
        @endphp

        <form method="POST" action="{{ route('role.switch') }}" class="role-switch-form" data-unsaved-ignore>
            @csrf
            <input type="hidden" name="role" value="{{ $switchRole }}">

            @if($variant === 'menu')
                <button type="submit" class="dropdown-item role-switch-item">
                    <i class="fas {{ $switchIcons[$switchRole] ?? 'fa-user' }}" style="color:var(--accent);"></i>
                    {{ $switchLabel }}
                </button>
            @else
                <button type="submit" class="sidebar-link role-switch-item" title="{{ $switchLabel }}">
                    <i class="fas {{ $switchIcons[$switchRole] ?? 'fa-user' }}"></i>
                    <span>{{ $switchLabel }}</span>
                </button>
            @endif
        </form>
    @endforeach

    @once
        <style>
            .role-switch-form {
                margin: 0;
            }

            .role-switch-item {
                width: 100%;
                border: 0;
                background: transparent;
                font-family: inherit;
                text-align: left;
                cursor: pointer;
            }
        </style>
    @endonce
@endauth
