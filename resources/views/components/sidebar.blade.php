<aside class="sidebar">
    <button type="button" class="sidebar-close-btn !mt-4">
        <x-icon.close class="text-sm" />
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
                        <x-icon.dashboard class="menu-icon" />
                        <span>Dashboard</span>
                    </a>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li class="sidebar-menu-group-title">Membership / GYM</li>

                <li class="{{ request()->routeIs('anggota.*') && !request()->routeIs('laporan.anggota') ? 'active' : '' }}">
                    <a href="{{ route('anggota.index') }}">
                        <x-icon.member class="menu-icon" />
                        <span>Member</span>
                    </a>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li
                    class="dropdown {{ request()->routeIs('kategori_paket_membership.*') || request()->routeIs('paket_membership.*') || (request()->routeIs('anggota_membership.*') && !request()->routeIs('laporan.membership')) ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.paket-member class="menu-icon" />
                        <span>Paket Member</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('kategori_paket_membership.*') ? 'active-page' : '' }}"
                                href="{{ route('kategori_paket_membership.index') }}">
                                <x-icon.dot class="text-primary-600" /> Kategori Paket
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('paket_membership.*') ? 'active-page' : '' }}"
                                href="{{ route('paket_membership.index') }}">
                                <x-icon.dot class="text-warning-600" /> Paket Member
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('anggota_membership.*') && !request()->routeIs('laporan.membership') ? 'active-page' : '' }}"
                                href="{{ route('anggota_membership.index') }}">
                                <x-icon.dot class="text-success-600" /> Anggota Member
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li
                    class="dropdown {{ request()->routeIs('specialisasi.*') || request()->routeIs('paket_personal_trainer.*') || (request()->routeIs('trainer.*') && !request()->routeIs('laporan.trainer') && !request()->routeIs('trainer.dashboard') && !request()->routeIs('trainer.waiting.approval')) || (request()->routeIs('membertrainer.*') && !request()->routeIs('laporan.membertrainer')) ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.personal-trainer class="menu-icon" />
                        <span>Personal Trainer</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('specialisasi.*') ? 'active-page' : '' }}"
                                href="{{ route('specialisasi.index') }}">
                                <x-icon.dot class="text-primary-600" /> Specialisasi
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('paket_personal_trainer.*') ? 'active-page' : '' }}"
                                href="{{ route('paket_personal_trainer.index') }}">
                                <x-icon.dot class="text-warning-600" /> Paket Trainer
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('trainer.*') && !request()->routeIs('laporan.trainer') && !request()->routeIs('trainer.dashboard') && !request()->routeIs('trainer.waiting.approval') ? 'active-page' : '' }}"
                                href="{{ route('trainer.index') }}">
                                <x-icon.dot class="text-success-600" /> Trainers
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('membertrainer.*') && !request()->routeIs('laporan.membertrainer') ? 'active-page' : '' }}"
                                href="{{ route('membertrainer.index') }}">
                                <x-icon.dot class="text-danger-600" /> Member Trainer
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li
                    class="{{ request()->routeIs('alat_gym.*') && !request()->routeIs('laporan.alat_gym') ? 'active' : '' }}">
                    <a href="{{ route('alat_gym.index') }}">
                        <x-icon.alat-gym class="menu-icon" />
                        <span>Alat Gym</span>
                    </a>
                </li>
            @endhasanyrole

            @hasanyrole('guest|admin')
                <li
                    class="dropdown {{ (request()->routeIs('kehadiranmember.*') && !request()->routeIs('laporan.kehadiran')) || request()->routeIs('kehadirantrainer.*') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.kehadiran class="menu-icon" />
                        <span>Kehadiran</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('kehadiranmember.*') && !request()->routeIs('laporan.kehadiran') ? 'active-page' : '' }}"
                                href="{{ route('kehadiranmember.index') }}">
                                <x-icon.dot class="text-primary-600" /> Member
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('kehadirantrainer.*') ? 'active-page' : '' }}"
                                href="{{ route('kehadirantrainer.index') }}">
                                <x-icon.dot class="text-warning-600" /> Trainer
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li
                    class="dropdown {{ request()->routeIs('pembayaran_membership.*') || request()->routeIs('pembayaran_trainer.*') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.pembayaran class="menu-icon" />
                        <span>Pembayaran</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('pembayaran_membership.*') ? 'active-page' : '' }}"
                                href="{{ route('pembayaran_membership.index') }}">
                                <x-icon.dot class="text-primary-600" /> Membership
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('pembayaran_trainer.*') ? 'active-page' : '' }}"
                                href="{{ route('pembayaran_trainer.index') }}">
                                <x-icon.dot class="text-warning-600" /> Trainer
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li class="dropdown {{ request()->routeIs('laporan.*') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.laporan class="menu-icon" />
                        <span>Laporan</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('laporan.anggota') ? 'active-page' : '' }}"
                                href="{{ route('laporan.anggota') }}">
                                <x-icon.dot class="text-primary-600" /> Anggota GYM
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('laporan.membership') ? 'active-page' : '' }}"
                                href="{{ route('laporan.membership') }}">
                                <x-icon.dot class="text-warning-600" /> Membership
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('laporan.trainer') ? 'active-page' : '' }}"
                                href="{{ route('laporan.trainer') }}">
                                <x-icon.dot class="text-info-600" /> Trainer
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('laporan.membertrainer') ? 'active-page' : '' }}"
                                href="{{ route('laporan.membertrainer') }}">
                                <x-icon.dot class="text-danger-600" /> Member Trainer
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('laporan.alat_gym') ? 'active-page' : '' }}"
                                href="{{ route('laporan.alat_gym') }}">
                                <x-icon.dot class="text-success-600" /> Alat GYM
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('laporan.kehadiran') ? 'active-page' : '' }}"
                                href="{{ route('laporan.kehadiran') }}">
                                <x-icon.dot class="text-purple-600" /> Absensi
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('laporan.penjualan') ? 'active-page' : '' }}"
                                href="{{ route('laporan.penjualan') }}">
                                <x-icon.dot class="text-info-600" /> Penjualan Product
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('laporan.products') ? 'active-page' : '' }}"
                                href="{{ route('laporan.products') }}">
                                <x-icon.dot class="text-danger-600" /> Product
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li class="sidebar-menu-group-title">POS</li>
                <li
                    class="dropdown {{ request()->routeIs('kasir.*') && !request()->routeIs('laporan.penjualan') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.mesin-kasir class="menu-icon" />
                        <span>Mesin Kasir</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('kasir.index') ? 'active-page' : '' }}"
                                href="{{ route('kasir.index') }}">
                                <x-icon.dot class="text-primary-600" /> POS
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('kasir.riwayat') && !request()->routeIs('laporan.penjualan') ? 'active-page' : '' }}"
                                href="{{ route('kasir.riwayat') }}">
                                <x-icon.dot class="text-warning-600" /> Penjualan
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li
                    class="dropdown {{ request()->routeIs('kategori_products.*') || (request()->routeIs('products.*') && !request()->routeIs('laporan.products')) ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.product class="menu-icon" />
                        <span>Product</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('kategori_products.*') ? 'active-page' : '' }}"
                                href="{{ route('kategori_products.index') }}">
                                <x-icon.dot class="text-primary-600" /> Kategori
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('products.*') && !request()->routeIs('laporan.products') ? 'active-page' : '' }}"
                                href="{{ route('products.index') }}">
                                <x-icon.dot class="text-warning-600" /> Product
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('spv|admin')
                <li class="sidebar-menu-group-title">Parameter</li>

                <li class="dropdown {{ request()->routeIs('usersList') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.users class="menu-icon" />
                        <span>Users</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('usersList') ? 'active-page' : '' }}"
                                href="{{ route('usersList') }}">
                                <x-icon.dot class="text-primary-600" /> Users List
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('admin')
                <li class="sidebar-menu-group-title">Payroll</li>

                <li
                    class="dropdown {{ request()->routeIs('gaji_trainer.*') || request()->routeIs('level_trainer.*') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.gaji-trainer class="menu-icon" />
                        <span>Gaji Trainer</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('level_trainer') ? 'active-page' : '' }}"
                                href="{{ route('level_trainer.index') }}">
                                <x-icon.dot class="text-primary-600" /> Level Trainer
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('gaji_trainer') ? 'active-page' : '' }}"
                                href="{{ route('gaji_trainer.index') }}">
                                <x-icon.dot class="text-warning-600" /> Parameter Gaji
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('riwayat_gaji_trainer') ? 'active-page' : '' }}"
                                href="{{ route('riwayat_gaji_trainer.index') }}">
                                <x-icon.dot class="text-success-600" /> Riwayat Gaji
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-menu-group-title">Keuangan</li>

                <li
                    class="dropdown {{ request()->routeIs('neraca.*') || request()->routeIs('keuangan.transaksi.*') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.neraca class="menu-icon" />
                        <span>Neraca</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('neraca.*') ? 'active-page' : '' }}"
                                href="{{ route('neraca.index') }}">
                                <x-icon.dot class="text-primary-600" /> Neraca
                            </a>
                        </li>
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('keuangan.transaksi.*') ? 'active-page' : '' }}"
                                href="{{ route('keuangan.transaksi.index') }}">
                                <x-icon.dot class="text-warning-600" /> Transaksi Keuangan
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('trainer')
                <li class="sidebar-menu-group-title">Trainer Dashboard</li>

                <li
                    class="{{ request()->routeIs('trainer.dashboard') || request()->routeIs('trainer.session.logs') || request()->routeIs('trainer.waiting.approval') ? 'active' : '' }}">
                    <a href="{{ route('trainer.dashboard') }}">
                        <x-icon.dashboard class="menu-icon" />
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('trainer.monitoring') ? 'active' : '' }}">
                    <a href="{{ route('trainer.monitoring') }}">
                        <x-icon.personal-trainer class="menu-icon" />
                        <span>Monitoring</span>
                    </a>
                </li>

                <li class="dropdown {{ request()->routeIs('trainerlistmember.*') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.member class="menu-icon" />
                        <span>Member</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('trainerlistmember.*') ? 'active-page' : '' }}"
                                href="{{ route('trainerlistmember.index') }}">
                                <x-icon.dot class="text-primary-600" /> List Member
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown {{ request()->routeIs('trainerplaylist.*') ? 'open' : '' }}">
                    <a href="javascript:void(0)">
                        <x-icon.laporan class="menu-icon" />
                        <span>Buku Jurnal Trainer</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a class="sidebar-menu-text {{ request()->routeIs('trainerplaylist.*') ? 'active-page' : '' }}"
                                href="{{ route('trainerplaylist.index') }}">
                                <x-icon.dot class="text-primary-600" /> Program Member
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole

            @hasanyrole('member')
                <li class="sidebar-menu-link {{ request()->routeIs('member.profile') ? 'active' : '' }}">
                    <a href="{{ route('member.profile') }}">
                        <x-icon.users class="menu-icon" />
                        <span>Profile Saya</span>
                    </a>
                </li>
            @endhasanyrole
        </ul>
    </div>
</aside>
