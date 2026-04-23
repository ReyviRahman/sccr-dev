<?php

namespace App\Providers;

use App\Models\Auth\AuthPersonalAccessToken;
use App\Services\AuthorizationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(AuthPersonalAccessToken::class);

        // Alias x-sccr-* → x-ui.sccr-* for backward compatibility
        $aliases = [
            'sccr-button' => 'ui.sccr-button',
            'sccr-card' => 'ui.sccr-card',
            'sccr-breadcrumb' => 'ui.sccr-breadcrumb',
            'sccr-select' => 'ui.sccr-select',
            'sccr-input' => 'ui.sccr-input',
            'sccr-toast' => 'ui.sccr-toast',
            'sccr-modal-layout' => 'ui.sccr-modal-layout',
            'sccr-modal' => 'ui.sccr-modal',
            'sccr-date' => 'ui.sccr-date',
            'sccr-tab' => 'ui.sccr-tab',
            'sccr-dropdown' => 'ui.sccr-dropdown',
            'sccr-dropdown-link' => 'ui.sccr-dropdown-link',
            'sccr-company-name' => 'ui.sccr-company-name',
            'sccr-text-area' => 'ui.sccr-text-area',
            'sccr-radio' => 'ui.sccr-radio',
            'sccr-file' => 'ui.sccr-file',
            'sccr-export' => 'ui.sccr-export',
            'sccr-chart' => 'ui.sccr-chart',
            'sccr-stat' => 'ui.sccr-stat',
            'sccr-badge' => 'ui.sccr-badge',
            'sccr-alert' => 'ui.sccr-alert',
            'sccr-pagination' => 'ui.sccr-pagination',
            'sccr-toggle' => 'ui.sccr-toggle',
            'sccr-icon' => 'ui.sccr-icon',
            'sccr-confirm-modal' => 'ui.sccr-confirm-modal',
            'sccr-import' => 'ui.sccr-import',
            'sccr-filter' => 'ui.sccr-filter',
            'sccr-navigation' => 'ui.sccr-navigation',
            'sccr-auth-layout' => 'ui.sccr-auth-layout',
            'sccr-text-input' => 'text-input',
            'sccr-primary-button' => 'primary-button',
            'sccr-secondary-button' => 'secondary-button',
            'sccr-danger-button' => 'danger-button',
            'sccr-input-label' => 'input-label',
            'sccr-input-error' => 'input-error',
        ];

        foreach ($aliases as $alias => $component) {
            Blade::component($component, $alias);
        }

        /**
         * MODULE DIRECTIVE
         *
         * @module('01005')
         */
        Blade::if('module', function (string $code) {
            if (! auth()->check()) {
                return false;
            }

            return app(AuthorizationService::class)
                ->canAccessModule(auth()->user(), $code);
        });

        /**
         * PERMISSION DIRECTIVE
         *
         * @permission('INV_DELETE')
         */
        Blade::if('permission', function (string $code) {
            if (! auth()->check()) {
                return false;
            }

            return app(AuthorizationService::class)
                ->hasPermission(auth()->user(), $code);
        });
    }
}
