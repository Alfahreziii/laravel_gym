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
            <li class="{{ request()->routeIs('index') || request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('index') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>  
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">Membership / GYM</li>

            <li class="{{ request()->routeIs('anggota.*') && !request()->routeIs('laporan.anggota') ? 'active' : '' }}">
                <a href="{{ route('anggota.index') }}">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Member</span>
                </a>
            </li>   
            @endhasanyrole
            
            @hasanyrole('spv|admin')
            <li class="dropdown {{ request()->routeIs('kategori_paket_membership.*') || request()->routeIs('paket_membership.*') || (request()->routeIs('anggota_membership.*') && !request()->routeIs('laporan.membership')) ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="fe:vector" class="menu-icon"></iconify-icon>
                    <span>Paket Member</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('kategori_paket_membership.*') ? 'active-page' : '' }}" href="{{ route('kategori_paket_membership.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Kategori Paket
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('paket_membership.*') ? 'active-page' : '' }}" href="{{ route('paket_membership.index') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Paket Member
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('anggota_membership.*') && !request()->routeIs('laporan.membership') ? 'active-page' : '' }}" href="{{ route('anggota_membership.index') }}">
                            <i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Anggota Member
                        </a>
                    </li>
                </ul>
            </li>    
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="dropdown {{ request()->routeIs('specialisasi.*') || request()->routeIs('paket_personal_trainer.*') || (request()->routeIs('trainer.*') && !request()->routeIs('laporan.trainer') && !request()->routeIs('trainer.dashboard') && !request()->routeIs('trainer.waiting.approval')) || (request()->routeIs('membertrainer.*') && !request()->routeIs('laporan.membertrainer')) ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-open-arm-fill menu-icon mr-0-custom"></i>
                    <span>Personal Trainer</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('specialisasi.*') ? 'active-page' : '' }}" href="{{ route('specialisasi.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Specialisasi
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('paket_personal_trainer.*') ? 'active-page' : '' }}" href="{{ route('paket_personal_trainer.index') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Paket Trainer
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('trainer.*') && !request()->routeIs('laporan.trainer') && !request()->routeIs('trainer.dashboard') && !request()->routeIs('trainer.waiting.approval') ? 'active-page' : '' }}" href="{{ route('trainer.index') }}">
                            <i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Trainers
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('membertrainer.*') && !request()->routeIs('laporan.membertrainer') ? 'active-page' : '' }}" href="{{ route('membertrainer.index') }}">
                            <i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i> Member Trainer
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
            
            @hasanyrole('spv|admin')
            <li class="{{ request()->routeIs('alat_gym.*') && !request()->routeIs('laporan.alat_gym') ? 'active' : '' }}">
                <a href="{{ route('alat_gym.index') }}">
                    <iconify-icon icon="simple-line-icons:vector" class="menu-icon"></iconify-icon>
                    <span>Alat Gym</span>
                </a>
            </li>  
            @endhasanyrole

            @hasanyrole('guest|admin')
            <li class="dropdown {{ (request()->routeIs('kehadiranmember.*') && !request()->routeIs('laporan.kehadiran')) || request()->routeIs('kehadirantrainer.*') ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                    <span>Kehadiran</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('kehadiranmember.*') && !request()->routeIs('laporan.kehadiran') ? 'active-page' : '' }}" href="{{ route('kehadiranmember.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Member
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('kehadirantrainer.*') ? 'active-page' : '' }}" href="{{ route('kehadirantrainer.index') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Trainer
                        </a>
                    </li>
                </ul>
            </li>   
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="dropdown {{ request()->routeIs('pembayaran_membership.*') || request()->routeIs('pembayaran_trainer.*') ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Pembayaran</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('pembayaran_membership.*') ? 'active-page' : '' }}" href="{{ route('pembayaran_membership.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Membership
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('pembayaran_trainer.*') ? 'active-page' : '' }}" href="{{ route('pembayaran_trainer.index') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Trainer
                        </a>
                    </li>
                </ul>
            </li>   
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="dropdown {{ request()->routeIs('laporan.*') ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-file-line menu-icon mr-0-custom"></i>
                    <span>Laporan</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('laporan.anggota') ? 'active-page' : '' }}" href="{{ route('laporan.anggota') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Anggota GYM
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('laporan.membership') ? 'active-page' : '' }}" href="{{ route('laporan.membership') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Membership
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('laporan.trainer') ? 'active-page' : '' }}" href="{{ route('laporan.trainer') }}">
                            <i class="ri-circle-fill circle-icon text-info-600 w-auto"></i>Trainer
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('laporan.membertrainer') ? 'active-page' : '' }}" href="{{ route('laporan.membertrainer') }}">
                            <i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i>Member Trainer
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('laporan.alat_gym') ? 'active-page' : '' }}" href="{{ route('laporan.alat_gym') }}">
                            <i class="ri-circle-fill circle-icon text-success-600 w-auto"></i>Alat GYM
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('laporan.kehadiran') ? 'active-page' : '' }}" href="{{ route('laporan.kehadiran') }}">
                            <i class="ri-circle-fill circle-icon text-purple-600 w-auto"></i>Absensi
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('laporan.penjualan') ? 'active-page' : '' }}" href="{{ route('laporan.penjualan') }}">
                            <i class="ri-circle-fill circle-icon text-info-600 w-auto"></i>Penjualan Product
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('laporan.products') ? 'active-page' : '' }}" href="{{ route('laporan.products') }}">
                            <i class="ri-circle-fill circle-icon text-danger-600 w-auto"></i>Product
                        </a>
                    </li>
                </ul>
            </li>   
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">POS</li>
            <li class="dropdown {{ (request()->routeIs('kasir.*') && !request()->routeIs('laporan.penjualan')) ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-store-2-line menu-icon mr-0-custom"></i>
                    <span >Mesin Kasir</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('kasir.index') ? 'active-page' : '' }}" href="{{ route('kasir.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>POS
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('kasir.riwayat') && !request()->routeIs('laporan.penjualan') ? 'active-page' : '' }}" href="{{ route('kasir.riwayat') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Penjualan
                        </a>
                    </li>
                </ul>
            </li>   
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="dropdown {{ request()->routeIs('kategori_products.*') || (request()->routeIs('products.*') && !request()->routeIs('laporan.products')) ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-inbox-2-line menu-icon mr-0-custom"></i>
                    <span>Product</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('kategori_products.*') ? 'active-page' : '' }}" href="{{ route('kategori_products.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Kategori
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('products.*') && !request()->routeIs('laporan.products') ? 'active-page' : '' }}" href="{{ route('products.index') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i>Product
                        </a>
                    </li>
                </ul>
            </li>   
            @endhasanyrole
            
            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">Parameter</li>
            
            <li class="dropdown {{ request()->routeIs('usersList') ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Users</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('usersList') ? 'active-page' : '' }}" href="{{ route('usersList') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Users List
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">Payroll</li>

            <li class="dropdown {{ request()->routeIs('gaji_trainer.*') || request()->routeIs('level_trainer.*') ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Gaji Trainer</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('level_trainer') ? 'active-page' : '' }}" href="{{ route('level_trainer.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Level Trainer
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('gaji_trainer') ? 'active-page' : '' }}" href="{{ route('gaji_trainer.index') }}">
                            <i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> Parameter Gaji
                        </a>
                    </li>
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('riwayat_gaji_trainer') ? 'active-page' : '' }}" href="{{ route('riwayat_gaji_trainer.index') }}">
                            <i class="ri-circle-fill circle-icon text-success-600 w-auto"></i> Riwayat Gaji
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
            <li class="sidebar-menu-group-title">Keuangan</li>
            
            <li class="dropdown {{ request()->routeIs('neraca.*') ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <i class="ri-gradienter-line menu-icon mr-0-custom"></i>
                    <span>Neraca</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('neraca.*') ? 'active-page' : '' }}" href="{{ route('neraca.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Neraca
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
            @hasanyrole('trainer')
            <li class="sidebar-menu-group-title">Trainer Dashboard</li>

            <li class="{{ request()->routeIs('trainer.dashboard') || request()->routeIs('trainer.session.logs') || request()->routeIs('trainer.waiting.approval') ? 'active' : '' }}">
                <a href="{{ route('trainer.dashboard') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>  

            <li class="{{ request()->routeIs('trainer.monitoring') ? 'active' : '' }}">
                <a href="{{ route('trainer.monitoring') }}">
                    <i class="ri-open-arm-fill menu-icon mr-0-custom"></i>
                    <span>Monitoring</span>
                </a>
            </li>  
            
            <li class="dropdown {{ request()->routeIs('trainerlistmember.*') ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Member</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('trainerlistmember.*') ? 'active-page' : '' }}" href="{{ route('trainerlistmember.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> List Member
                        </a>
                    </li>
                </ul>
            </li>

            <li class="dropdown {{ request()->routeIs('trainerplaylist.*') ? 'open' : '' }}">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:document-text-outline" class="menu-icon"></iconify-icon>
                    <span>PlayList</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a class="sidebar-menu-text {{ request()->routeIs('trainerplaylist.*') ? 'active-page' : '' }}" href="{{ route('trainerplaylist.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> Playlist Trainer
                        </a>
                    </li>
                </ul>
            </li>
            @endhasanyrole
        
        </ul>
    </div>
</aside>