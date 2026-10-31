
<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.header')
    <title>@yield('title', 'Dashboard Admin | ' . ($settingweb?->nama_website ?? 'ARMASO'))</title>
    @include('partials.css')
    <!-- CSS -->
</head>

<body id="page-top">
    @include('sweetalert::alert')

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">

                <div class="sidebar-brand-icon">
                    <img
                        src="{{ asset('img/armaso-logo.png') }}"
                        alt="Logo ARMASO 2027"
                        style="width: 75px; height: 45px; object-fit: contain;"
                    >
                </div>

                <div class="sidebar-brand-text mx-2">
                    {{ $settingweb?->nama_website ?? 'ARMASO' }}
                </div>

            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <hr class="sidebar-divider">

            @if (auth()->user()->role == 'superadmin')

                {{-- Dashboard --}}
                <li class="nav-item active">
                    <a class="nav-link" href="/admin/dashboard">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <hr class="sidebar-divider">

                <div class="sidebar-heading">
                    Menu Utama
                </div>

                {{-- User Akun --}}
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#"
                        data-toggle="collapse"
                        data-target="#collapsePages4"
                        aria-expanded="true"
                        aria-controls="collapsePages4">

                        <i class="fa-solid fa-users-viewfinder"></i>
                        <span>User Akun</span>

                    </a>

                    <div id="collapsePages4"
                        class="collapse"
                        aria-labelledby="headingPages"
                        data-parent="#accordionSidebar">

                        <div class="bg-white py-2 collapse-inner rounded">

                            <a class="collapse-item" href="/admin/dafatarakun">
                                Daftar Akun
                            </a>

                            <a class="collapse-item" href="/admin/password-requests">
                                Requests Reset Password
                            </a>

                        </div>

                    </div>
                </li>

                {{-- Peserta --}}
                <li class="nav-item">

                    <a class="nav-link collapsed"
                        href="#"
                        data-toggle="collapse"
                        data-target="#collapsePages1"
                        aria-expanded="true">

                        <i class="fa-solid fa-users"></i>
                        <span>Peserta</span>

                    </a>

                    <div id="collapsePages1"
                        class="collapse"
                        data-parent="#accordionSidebar">

                        <div class="bg-white py-2 collapse-inner rounded">

                            <a class="collapse-item" href="/admin/peserta/semuadata">
                                Semua Data
                            </a>

                            <a class="collapse-item" href="/admin/peserta/online">
                                Daftar Online
                            </a>

                            <a class="collapse-item" href="/admin/peserta/offline">
                                Daftar Offline
                            </a>

                            <a class="collapse-item" href="/admin/peserta/kolektif">
                                Daftar Kolektif
                            </a>

                            <a class="collapse-item" href="/admin/peserta/futsal">
                                Futsal
                            </a>

                        </div>

                    </div>
                </li>

                {{-- Pembayaran --}}
                <li class="nav-item">

                    <a class="nav-link collapsed"
                        href="#"
                        data-toggle="collapse"
                        data-target="#collapsePages2"
                        aria-expanded="true">

                        <i class="fa-solid fa-money-bills"></i>
                        <span>Pembayaran</span>

                    </a>

                    <div id="collapsePages2"
                        class="collapse"
                        data-parent="#accordionSidebar">

                        <div class="bg-white py-2 collapse-inner rounded">

                            <a class="collapse-item" href="/admin/tagihan">
                                Tagihan
                            </a>

                            <a class="collapse-item" href="/admin/pembayaran">
                                Pembayaran
                            </a>

                        </div>

                    </div>
                </li>

                <hr class="sidebar-divider">

                <div class="sidebar-heading">
                    Informasi
                </div>

                {{-- Persyaratan --}}
                <li class="nav-item">
                    <a class="nav-link" href="/admin/persyaratan">
                        <i class="fa-solid fa-address-book"></i>
                        <span>Persyaratan</span>
                    </a>
                </li>

                {{-- Pengumuman --}}
                <li class="nav-item">
                    <a class="nav-link" href="/admin/pengumuman">
                        <i class="fa-solid fa-bullhorn"></i>
                        <span>Pengumuman</span>
                    </a>
                </li>

                {{-- Kontak Admin --}}
                <li class="nav-item">
                    <a class="nav-link" href="/admin/kontakadmin">
                        <i class="fa-solid fa-user-tie"></i>
                        <span>Kontak Admin</span>
                    </a>
                </li>

                <hr class="sidebar-divider">

                <div class="sidebar-heading">
                    Setting
                </div>

                {{-- Website --}}
                <li class="nav-item">

                    <a class="nav-link collapsed"
                        href="#"
                        data-toggle="collapse"
                        data-target="#collapsePages3"
                        aria-expanded="true">

                        <i class="fa-solid fa-globe"></i>
                        <span>Website</span>

                    </a>

                    <div id="collapsePages3"
                        class="collapse"
                        data-parent="#accordionSidebar">

                        <div class="bg-white py-2 collapse-inner rounded">

                            <a class="collapse-item" href="/admin/rekening">
                                Info Pembayaran
                            </a>

                            <a class="collapse-item" href="/admin/infolomba">
                                Info Lomba
                            </a>

                        </div>

                    </div>
                </li>

                {{-- Apps --}}
                <li class="nav-item">
                    <a class="nav-link" href="/admin/settingweb">
                        <i class="fa-solid fa-gear"></i>
                        <span>Apps</span>
                    </a>
                </li>

            @endif

            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <button id="sidebarToggleTop"
                        class="btn btn-link d-md-none rounded-circle mr-3">

                        <i class="fa fa-bars"></i>

                    </button>


                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <div class="topbar-divider d-none d-sm-block"></div>


                        <!-- User Information -->
                        <li class="nav-item dropdown no-arrow">

                            <a class="nav-link dropdown-toggle"
                                href="#"
                                id="userDropdown1"
                                role="button"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false">

                                <span class="mr-2 d-none d-lg-inline text-gray-600">
                                    {{ auth()->user()->name }}
                                </span>

                                <i class="fa-regular fa-circle-user img-profile rounded-circle"></i>

                            </a>


                            <!-- Dropdown -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown1">

                                <a class="dropdown-item" href="/admin/profil">

                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>

                                    Profile

                                </a>


                                <div class="dropdown-divider"></div>


                                <form action="{{ route('auth.logoutadmin') }}" method="POST">

                                    @csrf

                                    <button type="submit"
                                        class="dropdown-item text-danger">

                                        <i class="fa-solid fa-right-from-bracket"></i>

                                        Logout

                                    </button>

                                </form>

                            </div>

                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->


                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">

                        <h1 class="h3 mb-0 text-gray-800">
                            Dashboard Admin
                        </h1>

                    </div>


                    <!-- Content -->
                    @yield('content')

                </div>
                <!-- End Container -->


                <!-- Footer -->
                @include('components.footer2')

            </div>
            <!-- End Main Content -->

        </div>
        <!-- End Content Wrapper -->

    </div>
    <!-- End Page Wrapper -->


    @include('partials.scripts')
    <!-- JavaScript -->

</body>

</html>


<style>
.gradient-separator {
    height: 2px;
    background: gray;
    margin: 10px 0;
}
</style>
