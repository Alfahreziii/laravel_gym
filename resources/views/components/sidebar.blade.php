<aside class="sidebar">
    <button type="button" class="sidebar-close-btn !mt-4">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('index') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li>
                <a href="{{ route('index') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>  
            <li class="sidebar-menu-group-title">Membership / GYM</li>
            <li>
                <a href="{{ route('anggota.index') }}">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Member</span>
                </a>
            </li>   
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="fe:vector" class="menu-icon"></iconify-icon>
                    <span>Paket Member</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('kategori_paket_membership.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Kategori Paket</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('paket_membership.index') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Paket Member</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('anggota_membership.index') }}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Anggota Member</a>
                    </li>
                </ul>
            </li>    
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                    <span>Personal Trainer</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('specialisasi.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Specialisasi</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('paket_personal_trainer.index') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Paket Trainer</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('trainer.index') }}"><i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Trainers</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('membertrainer.index') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Member Trainer</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ route('alat_gym.index') }}">
                    <iconify-icon icon="simple-line-icons:vector" class="menu-icon"></iconify-icon>
                    <span>Alat Gym</span>
                </a>
            </li>  
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                    <span>Kehadiran</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('kehadiranmember.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Member</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('kehadirantrainer.index') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Trainer</a>
                    </li>
                </ul>
            </li>   
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Pembayaran</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('pembayaran_membership.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Membership</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('pembayaran_trainer.index') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Trainer</a>
                    </li>
                </ul>
            </li>   
            <li class="sidebar-menu-group-title">POS</li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="ri-store-2-line menu-icon"></i>
                    <span>Mesin Kasir</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('kasir.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>POS</a>
                    </li>
                </ul>
            </li>   
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="ri-inbox-2-line menu-icon"></i>
                    <span>Product</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('kategori_products.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Kategori</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('products.index') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Product</a>
                    </li>
                </ul>
            </li>   

            <li class="sidebar-menu-group-title">Application</li>

            <li>
                <a href="{{ route('pageError') }}">
                    <iconify-icon icon="streamline:straight-face" class="menu-icon"></iconify-icon>
                    <span>404</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Users</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('usersList') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Users List</a>
                    </li>
                    <li>
                        <a href="{{ route('usersGrid') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Users Grid</a>
                    </li>
                    <li>
                        <a href="{{ route('addUser') }}"><i class="ri-circle-fill circle-icon text-info-600 w-auto"></i> Add User</a>
                    </li>
                    <li>
                        <a href="{{ route('viewProfile') }}"><i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> View Profile</a>
                    </li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon"></iconify-icon>
                    <span>Settings</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="#"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> TEst</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>