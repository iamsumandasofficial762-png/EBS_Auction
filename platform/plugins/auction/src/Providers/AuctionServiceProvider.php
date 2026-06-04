<?php

namespace Botble\Auction\Providers;

use Botble\Auction\Commands\SelectAuctionWinnersCommand;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class AuctionServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce') || ! is_plugin_active('marketplace')) {
            return;
        }

        $this
            ->setNamespace('plugins/auction')
            ->loadAndPublishConfigurations(['auction'])
            ->loadMigrations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'vendor']);

        DashboardMenu::for('customer')->beforeRetrieving(function (): void {
            DashboardMenu::make()->registerItem([
                'id' => 'auction.customer.index',
                'priority' => 55,
                'name' => __('Auction'),
                'url' => function () {
                    $customer = auth('customer')->user();

                    if ($customer && $customer->is_vendor) {
                        return route('marketplace.vendor.auctions.index');
                    }

                    return route('auction.customer.index');
                },
                'icon' => 'ti ti-gavel',
            ]);
        });

        DashboardMenu::for('vendor')->beforeRetrieving(function (): void {
            DashboardMenu::make()->registerItem([
                'id' => 'marketplace.vendor.auctions.index',
                'priority' => 3,
                'name' => __('Auction'),
                'url' => fn () => route('marketplace.vendor.auctions.index'),
                'icon' => 'ti ti-gavel',
            ]);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SelectAuctionWinnersCommand::class,
            ]);
        }

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('auction:select-winners')->everyFiveMinutes();
        });
    }
}
