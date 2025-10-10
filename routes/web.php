<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AiapplicationController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ComponentspageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CryptocurrencyController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\KategoriPaketController;
use App\Http\Controllers\PaketMembershipController;
use App\Http\Controllers\AnggotaMembershipController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\SpecialisasiController;
use App\Http\Controllers\PaketPersonalTrainerController;
use App\Http\Controllers\MemberTrainerController;
use App\Http\Controllers\AlatGymController;
use App\Http\Controllers\KehadiranMemberController;
use App\Http\Controllers\KehadiranTrainerController;
use App\Http\Controllers\PembayaranMembershipController;
use App\Http\Controllers\PembayaranTrainerController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\KategoriProductController;

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
    });

    // Route untuk Produk
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products', 'index')->name('products.index');
        Route::get('/products/create', 'create')->name('products.create');
        Route::post('/products', 'store')->name('products.store');
        Route::get('/products/{product}/edit', 'edit')->name('products.edit');
        Route::put('/products/{product}', 'update')->name('products.update');
        Route::delete('/products/{product}', 'destroy')->name('products.destroy');
        Route::post('/products/{product}/adjust','adjustQuantity')->name('products.adjust');
        Route::get('/products/{product}/logs','logs')->name('products.logs');

    });

    // Route untuk Kategori Produk
    Route::controller(KategoriProductController::class)->group(function () {
        Route::get('/kategori-products', 'index')->name('kategori_products.index');
        Route::post('/kategori-products', 'store')->name('kategori_products.store');
        Route::put('/kategori-products/{kategori_product}', 'update')->name('kategori_products.update');
        Route::delete('/kategori-products/{kategori_product}', 'destroy')->name('kategori_products.destroy');
    });

    Route::controller(AnggotaController::class)->group(function () {
        Route::get('/anggota', 'index')->name('anggota.index');
        Route::get('/anggota/create', 'create')->name('anggota.create');
        Route::post('/anggota', 'store')->name('anggota.store');
        Route::get('/anggota/{anggota}/edit', 'edit')->name('anggota.edit');
        Route::put('/anggota/{anggota}', 'update')->name('anggota.update');
        Route::delete('/anggota/{anggota}', 'destroy')->name('anggota.destroy');
    });

    Route::controller(KategoriPaketController::class)->group(function () {
        Route::get('/kategori-paket', 'index')->name('kategori_paket_membership.index');
        Route::post('/kategori-paket', 'store')->name('kategori_paket_membership.store');
        Route::put('/kategori-paket/{kategori_paket_membership}', 'update')->name('kategori_paket_membership.update');
        Route::delete('/kategori-paket/{kategori_paket_membership}', 'destroy')->name('kategori_paket_membership.destroy');
    });
    
    Route::controller(PaketMembershipController::class)->group(function () {
        Route::get('/paket-membership', 'index')->name('paket_membership.index');
        Route::get('/paket-membership/create', 'create')->name('paket_membership.create');
        Route::post('/paket-membership', 'store')->name('paket_membership.store');
        Route::get('/paket-membership/{paket_membership}/edit', 'edit')->name('paket_membership.edit');
        Route::put('/paket-membership/{paket_membership}', 'update')->name('paket_membership.update');
        Route::delete('/paket-membership/{paket_membership}', 'destroy')->name('paket_membership.destroy');
    });
    Route::controller(KasirController::class)->group(function () {
        Route::get('/kasir', 'index')->name('kasir.index');
        Route::post('/kasir/hold', 'hold')->name('kasir.hold');
        Route::get('/held-transactions', 'getHeldTransactions')->name('getHeldTransactions');
    });

    Route::controller(PaketPersonalTrainerController::class)->group(function () {
        Route::get('/paket-personal-trainer', 'index')->name('paket_personal_trainer.index');
        Route::get('/paket-personal-trainer/create', 'create')->name('paket_personal_trainer.create');
        Route::post('/paket-personal-trainer', 'store')->name('paket_personal_trainer.store');
        Route::get('/paket-personal-trainer/{paket_personal_trainer}/edit', 'edit')->name('paket_personal_trainer.edit');
        Route::put('/paket-personal-trainer/{paket_personal_trainer}', 'update')->name('paket_personal_trainer.update');
        Route::delete('/paket-personal-trainer/{paket_personal_trainer}', 'destroy')->name('paket_personal_trainer.destroy');
    });

    Route::controller(SpecialisasiController::class)->group(function () {
        Route::get('/specialisasi', 'index')->name('specialisasi.index');
        Route::post('/specialisasi', 'store')->name('specialisasi.store');
        Route::put('/specialisasi/{specialisasi}', 'update')->name('specialisasi.update');
        Route::delete('/specialisasi/{specialisasi}', 'destroy')->name('specialisasi.destroy');
    });
    
    Route::controller(MemberTrainerController::class)->group(function () {
        Route::get('/member-trainer', 'index')->name('membertrainer.index');
        Route::get('/member-trainer/create', 'create')->name('membertrainer.create');
        Route::post('/member-trainer', 'store')->name('membertrainer.store');
        Route::get('/member-trainer/{id}/edit', 'edit')->name('membertrainer.edit');
        Route::put('/member-trainer/{id}', 'update')->name('membertrainer.update');
        Route::delete('/member-trainer/{id}', 'destroy')->name('membertrainer.destroy');

        Route::post('/member-trainer/{id}/tambah-pembayaran', 'tambahPembayaran')->name('membertrainer.tambahPembayaran');
        Route::delete('/pembayaran-trainer/{id}', 'destroyPembayaran')->name('pembayaran_trainer.destroy');
    });

    Route::controller(AnggotaMembershipController::class)->group(function () {
        Route::get('/anggota-membership', 'index')->name('anggota_membership.index');
        Route::get('/anggota-membership/create', 'create')->name('anggota_membership.create');
        Route::post('/anggota-membership', 'store')->name('anggota_membership.store');
        Route::get('/anggota-membership/{id}/edit', 'edit')->name('anggota_membership.edit');
        Route::put('/anggota-membership/{id}', 'update')->name('anggota_membership.update');
        Route::delete('/anggota-membership/{id}', 'destroy')->name('anggota_membership.destroy');

        Route::post('/anggota-membership/{id}/tambah-pembayaran', 'tambahPembayaran')->name('anggota_membership.tambahPembayaran');
        Route::delete('/pembayaran-membership/{id}', 'destroyPembayaran')->name('pembayaran_membership.destroy');
    });

    Route::controller(PembayaranMembershipController::class)->group(function () {
        Route::get('/pembayaran-membership', 'index')->name('pembayaran_membership.index');
        Route::put('/pembayaran-membership/{id}', 'tambahPembayaran')->name('pembayaran_membership.tambahPembayaran');
    });

    Route::controller(PembayaranTrainerController::class)->group(function () {
        Route::get('/pembayaran-trainer', 'index')->name('pembayaran_trainer.index');
        Route::put('/pembayaran-trainer/{id}', 'tambahPembayaran')->name('pembayaran_trainer.tambahPembayaran');
    });

    Route::controller(TrainerController::class)->group(function () {
        Route::get('/trainer', 'index')->name('trainer.index');
        Route::get('/trainer/create', 'create')->name('trainer.create');
        Route::post('/trainer', 'store')->name('trainer.store');
        Route::get('/trainer/{trainer}/edit', 'edit')->name('trainer.edit');
        Route::put('/trainer/{trainer}', 'update')->name('trainer.update');
        Route::delete('/trainer/{trainer}', 'destroy')->name('trainer.destroy');
    });

    Route::controller(AlatGymController::class)->group(function () {
        Route::get('/alat-gym', 'index')->name('alat_gym.index');
        Route::get('/alat-gym/create', 'create')->name('alat_gym.create');
        Route::post('/alat-gym', 'store')->name('alat_gym.store');
        Route::get('/alat-gym/{alatgym}/edit', 'edit')->name('alat_gym.edit');
        Route::put('/alat-gym/{alatgym}', 'update')->name('alat_gym.update');
        Route::delete('/alat-gym/{alatgym}', 'destroy')->name('alat_gym.destroy');
    });

    Route::controller(KehadiranMemberController::class)->group(function () {
        Route::get('/kehadiran-member', 'index')->name('kehadiranmember.index');
        Route::get('/kehadiran-member/create', 'create')->name('kehadiranmember.create');
        Route::post('/kehadiran-member', 'store')->name('kehadiranmember.store');
        Route::delete('/kehadiran-member/{kehadiranmember}', 'destroy')->name('kehadiranmember.destroy');
    });

    Route::controller(KehadiranTrainerController::class)->group(function () {
        Route::get('/kehadiran-trainer', 'index')->name('kehadirantrainer.index');
        Route::get('/kehadiran-trainer/create', 'create')->name('kehadirantrainer.create');
        Route::post('/kehadiran-trainer', 'store')->name('kehadirantrainer.store');
        Route::delete('/kehadiran-trainer/{kehadirantrainer}', 'destroy')->name('kehadirantrainer.destroy');
    });
    

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('index');
    });

    Route::controller(HomeController::class)->group(function () {
        Route::get('calendar-Main','calendarMain')->name('calendarMain');
        Route::get('chatempty','chatempty')->name('chatempty');
        Route::get('chat-message','chatMessage')->name('chatMessage');
        Route::get('chat-profile','chatProfile')->name('chatProfile');
        Route::get('email','email')->name('email');
        Route::get('faq','faq')->name('faq');
        Route::get('gallery','gallery')->name('gallery');
        Route::get('image-upload','imageUpload')->name('imageUpload');
        Route::get('kanban','kanban')->name('kanban');
        Route::get('page-error','pageError')->name('pageError');
        Route::get('pricing','pricing')->name('pricing');
        Route::get('starred','starred')->name('starred');
        Route::get('terms-condition','termsCondition')->name('termsCondition');
        Route::get('veiw-details','veiwDetails')->name('veiwDetails');
        Route::get('widgets','widgets')->name('widgets');

        });

        // aiApplication
    Route::prefix('aiapplication')->group(function () {
        Route::controller(AiapplicationController::class)->group(function () {
            Route::get('/code-generator', 'codeGenerator')->name('codeGenerator');
            Route::get('/code-generatornew', 'codeGeneratorNew')->name('codeGeneratorNew');
            Route::get('/image-generator','imageGenerator')->name('imageGenerator');
            Route::get('/text-generator','textGenerator')->name('textGenerator');
            Route::get('/text-generatornew','textGeneratorNew')->name('textGeneratorNew');
            Route::get('/video-generator','videoGenerator')->name('videoGenerator');
            Route::get('/voice-generator','voiceGenerator')->name('voiceGenerator');
        });
    });

    // chart
    Route::prefix('chart')->group(function () {
        Route::controller(ChartController::class)->group(function () {
            Route::get('/column-chart', 'columnChart')->name('columnChart');
            Route::get('/line-chart', 'lineChart')->name('lineChart');
            Route::get('/pie-chart', 'pieChart')->name('pieChart');
        });
    });

    // Componentpage
    Route::prefix('componentspage')->group(function () {
        Route::controller(ComponentspageController::class)->group(function () {
            Route::get('/alert', 'alert')->name('alert');
            Route::get('/avatar', 'avatar')->name('avatar');
            Route::get('/badges', 'badges')->name('badges');
            Route::get('/button', 'button')->name('button');
            Route::get('/calendar', 'calendar')->name('calendar');
            Route::get('/card', 'card')->name('card');
            Route::get('/carousel', 'carousel')->name('carousel');
            Route::get('/colors', 'colors')->name('colors');
            Route::get('/dropdown', 'dropdown')->name('dropdown');
            Route::get('/imageupload', 'imageUpload')->name('imageUpload');
            Route::get('/list', 'list')->name('list');
            Route::get('/pagination', 'pagination')->name('pagination');
            Route::get('/progress', 'progress')->name('progress');
            Route::get('/radio', 'radio')->name('radio');
            Route::get('/star-rating', 'starRating')->name('starRating');
            Route::get('/switch', 'switch')->name('switch');
            Route::get('/tabs', 'tabs')->name('tabs');
            Route::get('/tags', 'tags')->name('tags');
            Route::get('/tooltip', 'tooltip')->name('tooltip');
            Route::get('/typography', 'typography')->name('typography');
            Route::get('/videos', 'videos')->name('videos');
        });
    });

    // Dashboard
    Route::prefix('cryptocurrency')->group(function () {
        Route::controller(CryptocurrencyController::class)->group(function () {
            Route::get('/wallet','wallet')->name('wallet');
        });
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/index-2', 'index2')->name('index2');
            Route::get('/index-3', 'index3')->name('index3');
            Route::get('/index-4', 'index4')->name('index4');
            Route::get('/index-5','index5')->name('index5');
            Route::get('/index-6','index6')->name('index6');
            Route::get('/index-7','index7')->name('index7');
            Route::get('/index-8','index8')->name('index8');
            Route::get('/index-9','index9')->name('index9');
        });
    });

    // Forms
    Route::prefix('forms')->group(function () {
        Route::controller(FormsController::class)->group(function () {
            Route::get('/form', 'form')->name('form');
            Route::get('/form-layout', 'formLayout')->name('formLayout');
            Route::get('/form-validation', 'formValidation')->name('formValidation');
            Route::get('/wizard', 'wizard')->name('wizard');
        });
    });

    // invoice/invoiceList
    Route::prefix('invoice')->group(function () {
        Route::controller(InvoiceController::class)->group(function () {
            Route::get('/invoice-add', 'invoiceAdd')->name('invoiceAdd');
            Route::get('/invoice-edit', 'invoiceEdit')->name('invoiceEdit');
            Route::get('/invoice-list', 'invoiceList')->name('invoiceList');
            Route::get('/invoice-preview', 'invoicePreview')->name('invoicePreview');
        });
    });

    // Settings
    Route::prefix('settings')->group(function () {
        Route::controller(SettingsController::class)->group(function () {
            Route::get('/company', 'company')->name('company');
            Route::get('/currencies', 'currencies')->name('currencies');
            Route::get('/language', 'language')->name('language');
            Route::get('/notification', 'notification')->name('notification');
            Route::get('/notification-alert', 'notificationAlert')->name('notificationAlert');
            Route::get('/payment-gateway', 'paymentGateway')->name('paymentGateway');
            Route::get('/theme', 'theme')->name('theme');
        });
    });

    // Table
    Route::prefix('table')->group(function () {
        Route::controller(TableController::class)->group(function () {
            Route::get('/table-basic', 'tableBasic')->name('tableBasic');
            Route::get('/table-data', 'tableData')->name('tableData');
        });
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::controller(UsersController::class)->group(function () {
            Route::get('/add-user', 'addUser')->name('addUser');
            Route::get('/users-grid', 'usersGrid')->name('usersGrid');
            Route::get('/users-list', 'usersList')->name('usersList');
            Route::get('/view-profile', 'viewProfile')->name('viewProfile');
        });
    });
});



require __DIR__.'/auth.php';