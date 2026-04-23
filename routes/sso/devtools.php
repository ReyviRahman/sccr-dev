<?php

use Illuminate\Support\Facades\Route;

Route::prefix('sso/dev-tools')
    ->name('sso.devtools.')
    ->middleware([
        'auth',
        'force.password.change',
        \App\Http\Middleware\AuthorizeModule::class.':00000',
        \App\Http\Middleware\AuthorizePermission::class.':SSO_DEVTOOLS_VIEW',
    ])
    ->group(function () {
        Route::get('/', \App\Livewire\Auth\Sso\DevTools\SsoDevToolsIndex::class)
            ->name('index');

        Route::get('/device-sync', \App\Livewire\Auth\Sso\DevTools\SsoDeviceSyncPage::class)
            ->name('device-sync')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_DEVTOOLS_DEVICE_SYNC');

        Route::get('/provision-queue', \App\Livewire\Auth\Sso\DevTools\SsoProvisionQueuePage::class)
            ->name('provision-queue')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_DEVTOOLS_QUEUE_VIEW');

        Route::get('/person-registry', \App\Livewire\Auth\Sso\DevTools\SsoDeviceRegistryPage::class)
            ->name('person-registry')
            ->middleware(\App\Http\Middleware\AuthorizePermission::class.':SSO_DEVTOOLS_REGISTRY_VIEW');
    });