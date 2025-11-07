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
            @hasanyrole('spv|admin')
            <li>
                <a href="{{ route('index') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>  
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">Membership / GYM</li>

            <li>
                <a href="{{ route('anggota.index') }}">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Member</span>
                </a>
            </li>   
            @endhasanyrole
            
            @hasanyrole('spv|admin')
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
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="ri-open-arm-fill menu-icon mr-0-custom"></i>
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
            @endhasanyrole
            
            @hasanyrole('spv|admin')
            <li>
                <a href="{{ route('alat_gym.index') }}">
                    <iconify-icon icon="simple-line-icons:vector" class="menu-icon"></iconify-icon>
                    <span>Alat Gym</span>
                </a>
            </li>  
            @endhasanyrole

            @hasanyrole('guest|admin')
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
            @endhasanyrole

            @hasanyrole('spv|admin')
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
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">POS</li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="ri-store-2-line menu-icon mr-0-custom"></i>
                    <span >Mesin Kasir</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('kasir.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>POS</a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text" href="{{ route('kasir.riwayat') }}"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Penjualan</a>
                    </li>
                </ul>
            </li>   
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="ri-inbox-2-line menu-icon mr-0-custom"></i>
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
            @endhasanyrole
            
            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">Parameter</li>
            
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Users</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('usersList') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Users List</a>
                    </li>
                </ul>
            </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">Keuangan</li>
            
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <i class="ri-gradienter-line menu-icon mr-0-custom"></i>
                    <span>Neraca</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('neraca.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Neraca</a>
                    </li>
                </ul>
            </li>
            @endhasanyrole

            @hasanyrole('trainer')
            <li class="sidebar-menu-group-title">Trainer Dashboard</li>
            
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('trainer.dashboard') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Dashboard</a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
        
        </ul>
    </div>
</aside>