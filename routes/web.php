<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PengampuController;
use App\Http\Controllers\Eselon2Controller;
use App\Http\Controllers\Eselon3Controller;

use App\Http\Controllers\ExecutiveController;
use App\Http\Controllers\AdminController;

// ── Public: Auth ──
Route::get('/login',   [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/switch-role', [AuthController::class, 'switchRole'])->name('switch.role');

// ── Root redirect ──
Route::get('/', function () {
    if (!auth()->check()) return redirect()->route('login');
    $role = session('active_role', auth()->user()->role ?? '');
    return match($role) {
        'staff'        => redirect()->route('pegawai.dashboard'),
        'pengampuSDM'  => redirect()->route('pengampu.demandPool'),
        'eselon2'      => redirect()->route($role . '.dashboard'),
        'eselon3'      => redirect()->route($role . '.dashboard'),
        'karoSDM'      => redirect()->route('executive.insights'),
        'kombinasi'    => redirect()->route('executive.insights'),
        'bangkom'      => redirect()->route('executive.ncc'),
        'deputi'       => redirect()->route('executive.insights'),
        'sesma'        => redirect()->route('executive.insights'),
        'admin'        => redirect()->route('admin.users'),
        default        => redirect()->route('pegawai.dashboard'),
    };
})->name('home')->middleware('auth');

// ── Protected: Pegawai ──
Route::middleware(['auth', 'atlas.permission:pegawai'])->group(function () {
    Route::get('/dashboard',              [PegawaiController::class, 'dashboard'])->name('pegawai.dashboard');
    Route::get('/profil-360',             [PegawaiController::class, 'profil360'])->name('pegawai.profil360');
    Route::get('/susun-idp',              [PegawaiController::class, 'susunIdp'])->name('pegawai.susunIdp');
    Route::post('/susun-idp/store',       [PegawaiController::class, 'storeIdp'])->name('pegawai.storeIdp');
    Route::post('/susun-idp/submit-batch', [PegawaiController::class, 'submitIdpBatch'])->name('pegawai.submitIdpBatch');
    Route::post('/susun-idp/{id}/submit', [PegawaiController::class, 'submitIdp'])->name('pegawai.submitIdp');
    Route::delete('/susun-idp/{id}', [PegawaiController::class, 'deleteIdp'])->name('pegawai.deleteIdp');
    Route::put('/susun-idp/{id}', [PegawaiController::class, 'updateIdp'])->name('pegawai.updateIdp');
    Route::get('/kalender',               [PegawaiController::class, 'kalender'])->name('pegawai.kalender');

});

// ── Protected: Pengampu SDM ──
Route::middleware(['auth', 'atlas.permission:pengampu'])->group(function () {
    Route::get('/pengampu/demand-pool',              [PengampuController::class, 'demandPool'])->name('pengampu.demandPool');
    Route::get('/pengampu/strategic-direction',      [PengampuController::class, 'strategicDirection'])->name('pengampu.strategicDirection');
    Route::get('/pengampu/rencana-bangkom',           [PengampuController::class, 'rencanaBangkom'])->name('pengampu.rencanaBangkom');
    Route::post('/pengampu/rencana-bangkom/store',     [PengampuController::class, 'storeRencanaBangkom'])->name('pengampu.storeRencanaBangkom');
    Route::post('/pengampu/rencana-bangkom/{id}/update', [PengampuController::class, 'updateRencanaBangkom'])->name('pengampu.updateRencanaBangkom');
    Route::post('/pengampu/rencana-bangkom/{id}/submit',[PengampuController::class, 'submitRencanaBangkom'])->name('pengampu.submitRencanaBangkom');
    Route::post('/pengampu/rencana-bangkom/{id}/cancel',[PengampuController::class, 'cancelRencanaBangkom'])->name('pengampu.cancelRencanaBangkom');
    Route::get('/pengampu/realisasi-bangkom',         [PengampuController::class, 'realisasiBangkom'])->name('pengampu.realisasiBangkom');
    Route::post('/pengampu/realisasi-bangkom/{id}/store',[PengampuController::class, 'storeRealisasiBangkom'])->name('pengampu.storeRealisasiBangkom');
    Route::post('/pengampu/realisasi-bangkom/{id}/submit', [PengampuController::class, 'submitPersetujuanRealisasi'])->name('pengampu.submitPersetujuanRealisasi');
    Route::post('/pengampu/realisasi-bangkom/{id}/submit-persetujuan', [PengampuController::class, 'submitPersetujuanRealisasi']);
    Route::post('/pengampu/realisasi-bangkom/{id}/approve', [PengampuController::class, 'approveRealisasi'])->name('pengampu.approveRealisasi');
    Route::post('/pengampu/realisasi-bangkom/{id}/reject', [PengampuController::class, 'rejectRealisasi'])->name('pengampu.rejectRealisasi');
    Route::get('/pengampu/certification',             [PengampuController::class, 'certification'])->name('pengampu.certification');
    Route::post('/pengampu/certification/map',        [PengampuController::class, 'mapCertification'])->name('pengampu.mapCertification');
    Route::get('/pengampu/certification/{kode}/peserta', [PengampuController::class, 'getPesertaSertifikasi'])->name('pengampu.certification.peserta');
    Route::get('/pengampu/profil-360',                [PengampuController::class, 'profil360'])->name('pengampu.profil360');
});

// ── Protected: Kepala Unit Eselon II ──
Route::middleware(['auth', 'atlas.permission:kepalaUnit'])->prefix('eselon2')->name('eselon2.')->group(function () {
    Route::get('/dashboard',              [Eselon2Controller::class, 'dashboard'])->name('dashboard');
    Route::get('/arahan-strategis',       [Eselon2Controller::class, 'arahanStrategis'])->name('arahanStrategis');
    Route::post('/arahan-strategis/store', [Eselon2Controller::class, 'storeArahan'])->name('storeArahan');
    Route::get('/review-idp',             [Eselon2Controller::class, 'reviewIdp'])->name('reviewIdp');
    Route::post('/review-idp/agree-batch', [Eselon2Controller::class, 'agreeBatchIdp'])->name('agreeBatchIdp');
    Route::post('/review-idp/{id}/agree',  [Eselon2Controller::class, 'agreeIdp'])->name('agreeIdp');
    Route::post('/review-idp/agree-all',  [Eselon2Controller::class, 'agreeAllIdp'])->name('agreeAllIdp');
    Route::post('/review-idp/{id}/revise', [Eselon2Controller::class, 'reviseIdp'])->name('reviseIdp');
    Route::get('/penetapan-bangkom',       [Eselon2Controller::class, 'penetapanBangkom'])->name('penetapanBangkom');
    Route::post('/penetapan-bangkom/{id}/approve', [Eselon2Controller::class, 'approveBangkom'])->name('approveBangkom');
    Route::post('/penetapan-bangkom/{id}/reject',  [Eselon2Controller::class, 'rejectBangkom'])->name('rejectBangkom');
    Route::post('/penetapan-bangkom/{id}/approve-realisasi', [Eselon2Controller::class, 'approveRealisasi'])->name('approveRealisasi');
    Route::post('/penetapan-bangkom/{id}/reject-realisasi',  [Eselon2Controller::class, 'rejectRealisasi'])->name('rejectRealisasi');
    Route::get('/pembatalan-bangkom',      [Eselon2Controller::class, 'pembatalanBangkom'])->name('pembatalanBangkom');
    Route::post('/pembatalan-bangkom/{id}/approve', [Eselon2Controller::class, 'approvePembatalan'])->name('approvePembatalan');
    Route::post('/pembatalan-bangkom/{id}/reject',  [Eselon2Controller::class, 'rejectPembatalan'])->name('rejectPembatalan');
    Route::get('/talent-finder',          [Eselon2Controller::class, 'talentFinder'])->name('talentFinder');
    Route::get('/profil-360',             [Eselon2Controller::class, 'profil360'])->name('profil360');
});

// ── Protected: Kepala Unit Eselon III ──
Route::middleware(['auth', 'atlas.permission:kepalaUnit'])->prefix('eselon3')->name('eselon3.')->group(function () {
    Route::get('/dashboard',              [Eselon3Controller::class, 'dashboard'])->name('dashboard');
    Route::get('/arahan-strategis',       [Eselon3Controller::class, 'arahanStrategis'])->name('arahanStrategis');
    Route::post('/arahan-strategis/store', [Eselon3Controller::class, 'storeArahan'])->name('storeArahan');
    Route::get('/review-idp',             [Eselon3Controller::class, 'reviewIdp'])->name('reviewIdp');
    Route::post('/review-idp/agree-batch', [Eselon3Controller::class, 'agreeBatchIdp'])->name('agreeBatchIdp');
    Route::post('/review-idp/{id}/agree',  [Eselon3Controller::class, 'agreeIdp'])->name('agreeIdp');
    Route::post('/review-idp/agree-all',  [Eselon3Controller::class, 'agreeAllIdp'])->name('agreeAllIdp');
    Route::post('/review-idp/{id}/revise', [Eselon3Controller::class, 'reviseIdp'])->name('reviseIdp');
    Route::get('/penetapan-bangkom',       [Eselon3Controller::class, 'penetapanBangkom'])->name('penetapanBangkom');
    Route::post('/penetapan-bangkom/{id}/approve', [Eselon3Controller::class, 'approveBangkom'])->name('approveBangkom');
    Route::post('/penetapan-bangkom/{id}/reject',  [Eselon3Controller::class, 'rejectBangkom'])->name('rejectBangkom');
    Route::post('/penetapan-bangkom/{id}/approve-realisasi', [Eselon3Controller::class, 'approveRealisasi'])->name('approveRealisasi');
    Route::post('/penetapan-bangkom/{id}/reject-realisasi',  [Eselon3Controller::class, 'rejectRealisasi'])->name('rejectRealisasi');
    Route::get('/pembatalan-bangkom',      [Eselon3Controller::class, 'pembatalanBangkom'])->name('pembatalanBangkom');
    Route::post('/pembatalan-bangkom/{id}/approve', [Eselon3Controller::class, 'approvePembatalan'])->name('approvePembatalan');
    Route::post('/pembatalan-bangkom/{id}/reject',  [Eselon3Controller::class, 'rejectPembatalan'])->name('rejectPembatalan');
    Route::get('/talent-finder',          [Eselon3Controller::class, 'talentFinder'])->name('talentFinder');
    Route::get('/profil-360',             [Eselon3Controller::class, 'profil360'])->name('profil360');
});

// ── Protected: Executive Dashboard ──
Route::middleware(['auth', 'atlas.permission:executive'])->group(function () {
    Route::get('/executive/insights',                 [ExecutiveController::class, 'insights'])->name('executive.insights');
    Route::get('/executive/bangkom-nasional',         [ExecutiveController::class, 'bangkomNasional'])->name('executive.bangkomNasional');
    Route::get('/executive/national-control-centre',   [ExecutiveController::class, 'nationalControlCentre'])->name('executive.ncc');
    Route::get('/executive/talent-finder',            [ExecutiveController::class, 'talentFinder'])->name('executive.talentFinder');
    Route::get('/executive/report-generator',         [ExecutiveController::class, 'reportGenerator'])->name('executive.reportGenerator');
    Route::post('/executive/report-generator/generate',[ExecutiveController::class, 'generateBrief'])->name('executive.generateBrief');
    Route::get('/executive/report-generator/export',   [ExecutiveController::class, 'exportXlsx'])->name('executive.exportXlsx');
    Route::get('/executive/sestama-brief',            [ExecutiveController::class, 'sestamaBrief'])->name('executive.sestamaBrief');
    Route::post('/executive/sestama-brief/generate',  [ExecutiveController::class, 'generateSestamaBrief'])->name('executive.generateSestamaBrief');
    Route::get('/executive/review-eselon2',           [ExecutiveController::class, 'reviewEselon2'])->name('executive.reviewEselon2');
    Route::post('/executive/review-eselon2/{id}/approve', [ExecutiveController::class, 'approveEselon2'])->name('executive.approveEselon2');
    Route::post('/executive/review-eselon2/agree-batch', [ExecutiveController::class, 'agreeBatchEselon2'])->name('executive.agreeBatchEselon2');
    Route::post('/executive/review-eselon2/agree-all', [ExecutiveController::class, 'agreeAllEselon2'])->name('executive.agreeAllEselon2');
    Route::post('/executive/review-eselon2/{id}/revise', [ExecutiveController::class, 'reviseEselon2'])->name('executive.reviseEselon2');
    Route::get('/executive/profil-360',                [ExecutiveController::class, 'profil360'])->name('executive.profil360');
});

// ── Protected: Admin Sistem ──
Route::middleware(['auth', 'atlas.permission:admin'])->group(function () {
    Route::get('/admin/users',                         [AdminController::class, 'usersIndex'])->name('admin.users');
    Route::post('/admin/users/store',                  [AdminController::class, 'usersStore'])->name('admin.users.store');
    Route::post('/admin/users/{id}/update',            [AdminController::class, 'usersUpdate'])->name('admin.users.update');
    Route::post('/admin/users/{id}/delete',            [AdminController::class, 'usersDelete'])->name('admin.users.delete');
    Route::get('/admin/setara-integration',            [AdminController::class, 'setaraIndex'])->name('admin.setara');
    Route::post('/admin/setara-integration/{dataset}/refresh', [AdminController::class, 'setaraRefresh'])->name('admin.setara.refresh');
    Route::get('/admin/audit-trail',                   [AdminController::class, 'auditTrailIndex'])->name('admin.auditTrail');
    Route::get('/admin/reference-parameter',           [AdminController::class, 'referenceIndex'])->name('admin.reference');
    Route::post('/admin/reference-parameter/store',    [AdminController::class, 'referenceStore'])->name('admin.reference.store');
    Route::post('/admin/reference-parameter/{id}/delete', [AdminController::class, 'referenceDelete'])->name('admin.reference.delete');
    
    // Uji Coba API SMILE
    Route::get('/admin/test-api-smile',                [AdminController::class, 'testApiSmile'])->name('admin.testApiSmile');
});





