<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\Campaigns\CampaignList;
use App\Livewire\Campaigns\CampaignForm;
use App\Livewire\Campaigns\CampaignDetail;
use App\Livewire\Templates\TemplateList;
use App\Livewire\Templates\TemplateForm;
use App\Livewire\EmailLogs;
use App\Livewire\Settings;
use App\Livewire\Letters\LetterTemplateList;
use App\Livewire\Letters\LetterTemplateForm;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/campaigns', CampaignList::class)->name('campaigns.index');
    Route::get('/campaigns/create', CampaignForm::class)->name('campaigns.create');
    Route::get('/campaigns/{campaign}/edit', CampaignForm::class)->name('campaigns.edit');
    Route::get('/campaigns/{campaign}', CampaignDetail::class)->name('campaigns.show');
    
    // Download recipient templates
    Route::get('/download/recipient-template', function () {
        $path = storage_path('app/templates/recipients-template.csv');
        return response()->download($path, 'template-recipients.csv');
    })->name('download.recipient-template');
    
    Route::get('/download/recipient-template-excel', function () {
        $path = storage_path('app/templates/recipients-template.xlsx');
        return response()->download($path, 'template-recipients.xlsx');
    })->name('download.recipient-template-excel');

    Route::get('/templates', TemplateList::class)->name('templates.index');
    Route::get('/templates/create', TemplateForm::class)->name('templates.create');
    Route::get('/templates/{emailTemplate}/edit', TemplateForm::class)->name('templates.edit');

    // Letter Templates (PDF)
    Route::get('/letters', LetterTemplateList::class)->name('letters.index');
    Route::get('/letters/create', LetterTemplateForm::class)->name('letters.create');
    Route::get('/letters/{letterTemplate}/edit', LetterTemplateForm::class)->name('letters.edit');

    Route::get('/logs', EmailLogs::class)->name('logs.index');
    Route::get('/settings', Settings::class)->name('settings.index');
});
