<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Utilisateurs', User::count())
                ->description('Total inscrits')
                ->color('primary')
                ->icon('heroicon-o-users'),

            Stat::make('Transactions', Transaction::count())
                ->description('Total transactions')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make('Retraits en attente',
                WithdrawalRequest::where('status', 'pending')->count()
            )
                ->description('À traiter')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Total wallet distribué',
                '$' . number_format(User::sum('wallet'), 2)
            )
                ->description('Cumul des wallets')
                ->color('info')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}