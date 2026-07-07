<?php

use App\Livewire\Actions\Logout;
use App\Models\Module;
use App\Models\Peralatan;
use App\Models\Personel;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Public Routes
Route::redirect('/', '/login');

// Logout Route (must be authenticated)
Route::post('/logout', function (Request $request, Logout $logout) {
    $logout();
    return redirect('/');
})->middleware('auth')->name('logout');

// Authenticated Routes
Route::middleware(['auth', 'verified', 'active'])->group(function () {
    
    // Dashboard
    Route::view('/dashboard', 'pages.dashboard')->name('dashboard');

    // Profile
    Route::view('profile', 'profile')->name('profile');

    // Master Data Routes
    Route::prefix('master-data')->name('master-data.')->group(function () {
        // Companies
        Route::view('/companies', 'master-data.companies')->middleware('can:companies_view')->name('companies');

        // Modules
        Route::prefix('modules')->name('modules.')->group(function () {
            Route::view('/', 'master-data.modules')->middleware('can:modules_view')->name('index');

            Route::view('/create', 'master-data.module-create')->middleware('can:modules_create')->name('create');

            Route::get('/{module}/edit', function (Module $module) {
                return view('master-data.module-edit', ['module' => $module]);
            })->middleware('can:modules_update')->name('edit');

            Route::get('/{module}', function (Module $module) {
                return view('master-data.module-show', ['module' => $module]);
            })->middleware('can:modules_show')->name('show');
        });

        // Competencies
        Route::view('/competencies', 'master-data.competencies')->middleware('can:competencies_view')->name('competencies');

        // Personels
        Route::prefix('personels')->name('personels.')->group(function () {
            Route::view('/', 'master-data.personels')->middleware('can:personels_view')->name('index');

            Route::view('/create', 'master-data.personels-create')->middleware('can:personels_create')->name('create');

            Route::get('/{personel}/edit', function (Personel $personel) {
                return view('master-data.personels-edit', ['personel' => $personel]);
            })->middleware('can:personels_update')->name('edit');
        });

        // Peralatan
        Route::prefix('peralatan')->name('peralatan.')->group(function () {
            Route::view('/', 'master-data.peralatan')->middleware('can:peralatan_view')->name('index');

            Route::view('/create', 'master-data.peralatan-create')->middleware('can:peralatan_create')->name('create');

            Route::get('/{peralatan}/edit', function (Peralatan $peralatan) {
                return view('master-data.peralatan-edit', ['peralatan' => $peralatan]);
            })->middleware('can:peralatan_update')->name('edit');

            Route::get('/{peralatan}', function (Peralatan $peralatan) {
                return view('master-data.peralatan-show', ['peralatan' => $peralatan]);
            })->middleware('can:peralatan_show')->name('show');
        });
    });

    // Projects
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::view('/', 'projects.index')->middleware('can:projects_view')->name('index');

        Route::view('/create', 'projects.create')->middleware('can:projects_create')->name('create');

        Route::get('/{project}', function (Project $project) {
            return view('projects.show', ['project' => $project]);
        })->middleware('can:projects_show')->name('show');

        Route::get('/{project}/edit', function (Project $project) {
            return view('projects.edit', ['project' => $project]);
        })->middleware('can:projects_update')->name('edit');

        Route::get('/{project}/work-order', function (Project $project) {
            return view('projects.work-order', ['project' => $project]);
        })->middleware('can:projects_show')->name('work-order');

        Route::get('/{project}/deliverables', function (Project $project) {
            return view('projects.deliverables', ['project' => $project]);
        })->middleware('can:projects_show')->name('deliverables');
    });

    // Notifications
    Route::view('/notifications', 'notifications.index')->middleware('can:notifications_view')->name('notifications.index');
    Route::view('/notifications/send', 'notifications.send')->middleware('can:notifications_send')->name('notifications.send');

    // Chat
    Route::view('/chat', 'chat.index')->middleware('can:chat_view')->name('chat.index');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::view('/system', 'settings.system')->middleware('can:configuration_view')->name('system');
        Route::view('/users', 'settings.users')->middleware('can:users_view')->name('users');
        Route::view('/roles', 'settings.roles')->middleware('can:roles_view')->name('roles');
    });
});

require __DIR__.'/auth.php';
