<?php

namespace App\Providers;

use App\Modules\Shipping\ShippingService;
use App\Modules\Shipping\ShippingServiceInterface;
use App\Modules\Shipping\CityShippingService;
use App\Modules\Shipping\CityShippingServiceInterface;
use App\Modules\Shipping\StateShippingService;
use App\Modules\Shipping\StateShippingServiceInterface;
use App\Modules\Shipping\OtherCountryShippingService;
use App\Modules\Shipping\OtherCountryShippingServiceInterface;
use App\Modules\Shipping\OtherStateShippingService;
use App\Modules\Shipping\OtherStateShippingServiceInterface;
use App\Modules\Shipping\OtherCityShippingService;
use App\Modules\Shipping\OtherCityShippingServiceInterface;
use App\Modules\Item\ItemService;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Payment\PaymentService;
use App\Modules\Payment\PaymentServiceInterface;
use App\Modules\User\UserService;
use App\Modules\User\UserServiceInterface;
use App\Modules\Auth\AuthService;
use App\Modules\Auth\AuthServiceInterface;
use App\Modules\Categories\CategoryService;
use App\Modules\Categories\CategoryServiceInterface;
use App\Modules\Mail\MailService;
use App\Modules\Mail\MailServiceInterface;
use App\Modules\Order\OrderService;
use App\Modules\Order\OrderServiceInterface;
use App\Modules\Storage\Storage;
use App\Modules\Storage\StorageInterface;
use App\Modules\Tags\TagService;
use App\Modules\Tags\TagServiceInterface;
use App\Modules\Upload\LocalDisk;
use App\Modules\Upload\UploadInterface;
use App\Modules\Region\RegionService;
use App\Modules\Region\RegionServiceInterface;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\Eloquent\Base\EloquentRepositoryInterface;
use App\Repository\Eloquent\ItemRepository;
use App\Repository\Eloquent\RegionRepository;
use App\Repository\Eloquent\ShippingRepository;
use App\Repository\Eloquent\StateShippingRepository;
use App\Repository\Eloquent\CityShippingRepository;
use App\Repository\Eloquent\OtherCountryShippingRepository;
use App\Repository\Eloquent\OtherStateShippingRepository;
use App\Repository\Eloquent\OtherCityShippingRepository;
use App\Repository\ItemRepositoryInterface;
use App\Repository\RegionRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\OrderRepository;
use App\Repository\Eloquent\TagRepository;
use App\Repository\Eloquent\UserRepository;
use App\Repository\OrderRepositoryInterface;
use App\Repository\TagRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Repository\ShippingRepositoryInterface;
use App\Repository\StateShippingRepositoryInterface;
use App\Repository\CityShippingRepositoryInterface;
use App\Repository\OtherCountryShippingRepositoryInterface;
use App\Repository\OtherStateShippingRepositoryInterface;
use App\Repository\OtherCityShippingRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Services
         */
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(ItemServiceInterface::class, ItemService::class);
        $this->app->bind(RegionServiceInterface::class, RegionService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(TagServiceInterface::class, TagService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(StorageInterface::class, Storage::class);
        $this->app->bind(UploadInterface::class, LocalDisk::class);
        $this->app->bind(MailServiceInterface::class, MailService::class);
        $this->app->bind(ShippingServiceInterface::class, ShippingService::class);
        $this->app->bind(StateShippingServiceInterface::class, StateShippingService::class);
        $this->app->bind(CityShippingServiceInterface::class, CityShippingService::class);
        $this->app->bind(OtherCountryShippingServiceInterface::class, OtherCountryShippingService::class);
        $this->app->bind(OtherStateShippingServiceInterface::class, OtherStateShippingService::class);
        $this->app->bind(OtherCityShippingServiceInterface::class, OtherCityShippingService::class);

        /**
         * Repositories
         */
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(RegionRepositoryInterface::class, RegionRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ShippingRepositoryInterface::class, ShippingRepository::class);
        $this->app->bind(StateShippingRepositoryInterface::class, StateShippingRepository::class);
        $this->app->bind(CityShippingRepositoryInterface::class, CityShippingRepository::class);
        $this->app->bind(OtherCountryShippingRepositoryInterface::class, OtherCountryShippingRepository::class);
        $this->app->bind(OtherStateShippingRepositoryInterface::class, OtherStateShippingRepository::class);
        $this->app->bind(OtherCityShippingRepositoryInterface::class, OtherCityShippingRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
