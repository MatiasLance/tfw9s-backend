<?php

namespace App\Providers;

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
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\Eloquent\Base\EloquentRepositoryInterface;
use App\Repository\Eloquent\ItemRepository;
use App\Repository\ItemRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\OrderRepository;
use App\Repository\Eloquent\TagRepository;
use App\Repository\Eloquent\UserRepository;
use App\Repository\OrderRepositoryInterface;
use App\Repository\TagRepositoryInterface;
use App\Repository\UserRepositoryInterface;
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
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(TagServiceInterface::class, TagService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(StorageInterface::class, Storage::class);
        $this->app->bind(UploadInterface::class, LocalDisk::class);
        $this->app->bind(MailServiceInterface::class, MailService::class);

        /**
         * Repositories
         */
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
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
