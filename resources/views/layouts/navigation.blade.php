<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="menu-text font-semibold">
            @auth
                <div class="flex">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="h-9 w-auto fill-current text-gray-800" />
                    </a>
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            @endauth
        </div>

        <button class="toggle-btn" id="toggleSidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div class="sidebar-menu">

        @can('users_view')
            <div class="menu-group">
                <a href="{{ route('users.index') }}"
                    class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="menu-icon"> <i class="bi bi-person-lines-fill me-2"></i></span>
                    <span class="menu-text">Users</span>
                </a>
            </div>    
        @endcan

        @can('roles_view')
            <div class="menu-group">
                <a href="{{ route('roles.index') }}"
                    class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <span class="menu-icon"> <i class="bi bi-shield-lock-fill me-2"></i></span>
                    <span class="menu-text">Roles</span>
                </a>
            </div>
        @endcan

        @can('client_view')
            <div class="menu-group">
                <a href="{{ route('clients.index') }}"
                    class="menu-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <span class="menu-icon"> <i class="bi bi-people me-2"></i></span>
                    <span class="menu-text">Clients</span>
                </a>
            </div>
        @endcan

        @can('invoice_view')
            <div class="menu-group">
                <a href="{{ route('invoices.index') }}"
                    class="menu-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <span class="menu-icon"> <i class="bi bi-file-earmark-text me-2"></i></span>
                    <span class="menu-text">Invoices</span>
                </a>
            </div>
        @endcan


        <div class="menu-group">
            <a href="{{ route('profile.edit') }}"
                class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <span class="menu-icon"> <i class="bi bi-person me-1"></i> </span>
                <span class="menu-text">Profile</span>
            </a>
        </div>


        <div class="menu-group">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" class="menu-item"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    <span class="menu-icon"><i class="bi bi-box-arrow-right me-1"></i></span>
                    <span class="menu-text">Log Out</span>
                </a>
            </form>
        </div>
    </div>
</div>


<style>
    .sidebar {
        width: 250px;
        min-height: calc(100vh - 4rem);
        background-color: #f8fafc;
        border-right: 1px solid #e2e8f0;
        transition: all 0.3s;
    }

    .sidebar.collapsed {
        width: 64px;
    }

    .sidebar-header {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar-menu {
        padding: 0;
    }

    .menu-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #4a5568;
        text-decoration: none;
        transition: all 0.2s;
    }

    .menu-item:hover {
        background-color: #edf2f7;
        color: #2d3748;
    }

    .menu-item.active {
        background-color: #e2e8f0;
        color: #2d3748;
        border-right: 3px solid #4299e1;
    }

    .menu-icon {
        margin-right: 0.75rem;
        width: 20px;
        text-align: center;
    }

    .menu-text {
        white-space: nowrap;
        overflow: hidden;
    }

    .sidebar.collapsed .menu-text {
        display: none;
    }

    .menu-group {
        margin-top: 1rem;
    }

    .menu-group-title {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .sidebar.collapsed .menu-group-title {
        display: none;
    }

    .toggle-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #718096;
        padding: 0.25rem;
        border-radius: 0.25rem;
    }

    .toggle-btn:hover {
        background-color: #e2e8f0;
        color: #4a5568;
    }

    .content-area {
        flex: 1;
        padding: 1rem;
    }

    .layout-container {
        display: flex;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: fixed;
            z-index: 40;
            height: 100vh;
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
        }

        .mobile-overlay.open {
            display: block;
        }

        .mobile-menu-btn {
            display: block;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 50;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.25rem;
            padding: 0.5rem;
        }
    }

    @media (min-width: 769px) {

        .mobile-menu-btn,
        .mobile-overlay {
            display: none;
        }
    }
</style>
