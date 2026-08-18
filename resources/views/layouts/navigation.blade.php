<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo - CareerPath BN -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-900 to-blue-700 rounded-lg flex items-center justify-center">
                            <i class="fas fa-compass text-white text-xl"></i>
                        </div>
                        <span class="text-xl font-bold text-blue-900 hidden sm:block">
                            CareerPath <span class="text-amber-500">BN</span>
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                        <i class="fas fa-chart-pie mr-1"></i> {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('student.profile')" :active="request()->routeIs('student.profile*')">
                        <i class="fas fa-user mr-1"></i> {{ __('Profile') }}
                    </x-nav-link>
                    <x-nav-link :href="route('student.milestones')" :active="request()->routeIs('student.milestones*')">
                        <i class="fas fa-flag-checkered mr-1"></i> {{ __('Milestones') }}
                    </x-nav-link>
                    @auth
                        @if(Auth::user()->role === 'admin' || Auth::user()->role === 'lecturer')
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                <i class="fas fa-crown text-amber-500 mr-1"></i> {{ __('Admin') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right Side Navigation -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Notifications Bell -->
                <div class="relative mr-4">
                    <a href="{{ route('student.notifications') }}" class="text-gray-500 hover:text-gray-700 relative">
                        <i class="fas fa-bell text-xl"></i>
                        @auth
                            @if(Auth::user()->unreadNotifications()->count() > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ Auth::user()->unreadNotifications()->count() }}
                                </span>
                            @endif
                        @endauth
                    </a>
                </div>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-bold">
                                    @auth
                                        {{ substr(Auth::user()->first_name ?? Auth::user()->name, 0, 1) }}
                                    @endauth
                                </div>
                                <span class="hidden md:inline">
                                    @auth
                                        {{ Auth::user()->first_name ?? Auth::user()->name }}
                                    @endauth
                                </span>
                            </div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profile -->
                        <x-dropdown-link :href="route('student.profile')">
                            <i class="fas fa-user mr-2 text-gray-500"></i> {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Settings -->
                        <x-dropdown-link :href="route('student.settings')">
                            <i class="fas fa-cog mr-2 text-gray-500"></i> {{ __('Settings') }}
                        </x-dropdown-link>

                        <!-- Notifications -->
                        <x-dropdown-link :href="route('student.notifications')">
                            <i class="fas fa-bell mr-2 text-gray-500"></i> {{ __('Notifications') }}
                            @auth
                                @if(Auth::user()->unreadNotifications()->count() > 0)
                                    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full ml-1">
                                        {{ Auth::user()->unreadNotifications()->count() }}
                                    </span>
                                @endif
                            @endauth
                        </x-dropdown-link>

                        <!-- Divider -->
                        <div class="border-t border-gray-200 my-1"></div>

                        <!-- Milestones -->
                        <x-dropdown-link :href="route('student.milestones')">
                            <i class="fas fa-flag-checkered mr-2 text-gray-500"></i> {{ __('Milestones') }}
                        </x-dropdown-link>

                        <!-- Divider -->
                        <div class="border-t border-gray-200 my-1"></div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt mr-2 text-gray-500"></i> {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 20 20">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                <i class="fas fa-chart-pie mr-2"></i> {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('student.profile')" :active="request()->routeIs('student.profile*')">
                <i class="fas fa-user mr-2"></i> {{ __('Profile') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('student.milestones')" :active="request()->routeIs('student.milestones*')">
                <i class="fas fa-flag-checkered mr-2"></i> {{ __('Milestones') }}
            </x-responsive-nav-link>
            @auth
                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'lecturer')
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                        <i class="fas fa-crown text-amber-500 mr-2"></i> {{ __('Admin') }}
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">
                    @auth
                        {{ Auth::user()->first_name ?? Auth::user()->name }}
                    @endauth
                </div>
                <div class="font-medium text-sm text-gray-500">
                    @auth
                        {{ Auth::user()->email }}
                    @endauth
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('student.profile')">
                    <i class="fas fa-user mr-2"></i> {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('student.settings')">
                    <i class="fas fa-cog mr-2"></i> {{ __('Settings') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('student.notifications')">
                    <i class="fas fa-bell mr-2"></i> {{ __('Notifications') }}
                    @auth
                        @if(Auth::user()->unreadNotifications()->count() > 0)
                            <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full ml-1">
                                {{ Auth::user()->unreadNotifications()->count() }}
                            </span>
                        @endif
                    @endauth
                </x-responsive-nav-link>

                <div class="border-t border-gray-200 my-1"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>