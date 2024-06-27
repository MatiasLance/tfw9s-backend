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
use App\Modules\Field\FieldService;
use App\Modules\Field\FieldServiceInterface;
use App\Modules\Manager\ManagerService;
use App\Modules\Manager\ManagerServiceInterface;
use App\Modules\Guideline\GuidelineService;
use App\Modules\Guideline\GuidelineServiceInterface;
use App\Modules\AgeGroup\AgeGroupService;
use App\Modules\AgeGroup\AgeGroupServiceInterface;
use App\Modules\Team\TeamService;
use App\Modules\Team\TeamServiceInterface;
use App\Modules\TeamFolder\TeamFolderService;
use App\Modules\TeamFolder\TeamFolderServiceInterface;
use App\Modules\Series\SeriesService;
use App\Modules\Series\SeriesServiceInterface;
use App\Modules\Event\EventService;
use App\Modules\Event\EventServiceInterface;
use App\Modules\TeamLimit\TeamLimitService;
use App\Modules\TeamLimit\TeamLimitServiceInterface;
use App\Modules\News\NewsService;
use App\Modules\News\NewsServiceInterface;
use App\Modules\TeamPosition\TeamPositionService;
use App\Modules\TeamPosition\TeamPositionServiceInterface;
use App\Modules\EventMatch\EventMatchService;
use App\Modules\EventMatch\EventMatchServiceInterface;
use App\Modules\PartnerSponsor\PartnerSponsorService;
use App\Modules\PartnerSponsor\PartnerSponsorServiceInterface;
use App\Modules\IndividualRegistration\IndividualRegistrationService;
use App\Modules\IndividualRegistration\IndividualRegistrationServiceInterface;
use App\Modules\TeamRegistration\TeamRegistrationService;
use App\Modules\TeamRegistration\TeamRegistrationServiceInterface;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\Eloquent\Base\EloquentRepositoryInterface;
use App\Repository\Eloquent\ItemRepository;
use App\Repository\Eloquent\RegionRepository;
use App\Repository\Eloquent\FieldRepository;
use App\Repository\Eloquent\ManagerRepository;
use App\Repository\Eloquent\GuidelineRepository;
use App\Repository\Eloquent\AgeGroupRepository;
use App\Repository\Eloquent\TeamRepository;
use App\Repository\Eloquent\TeamFolderRepository;
use App\Repository\Eloquent\SeriesRepository;
use App\Repository\Eloquent\EventRepository;
use App\Repository\Eloquent\TeamLimitRepository;
use App\Repository\Eloquent\NewsRepository;
use App\Repository\Eloquent\TeamPositionRepository;
use App\Repository\Eloquent\PartnerSponsorRepository;
use App\Repository\Eloquent\EventMatchRepository;
use App\Repository\Eloquent\ShippingRepository;
use App\Repository\Eloquent\StateShippingRepository;
use App\Repository\Eloquent\CityShippingRepository;
use App\Repository\Eloquent\OtherCountryShippingRepository;
use App\Repository\Eloquent\OtherStateShippingRepository;
use App\Repository\Eloquent\OtherCityShippingRepository;
use App\Repository\Eloquent\IndividualRegistrationRepository;
use App\Repository\Eloquent\TeamRegistrationRepository;
use App\Repository\ItemRepositoryInterface;
use App\Repository\RegionRepositoryInterface;
use App\Repository\FieldRepositoryInterface;
use App\Repository\ManagerRepositoryInterface;
use App\Repository\GuidelineRepositoryInterface;
use App\Repository\AgeGroupRepositoryInterface;
use App\Repository\TeamRepositoryInterface;
use App\Repository\TeamFolderRepositoryInterface;
use App\Repository\SeriesRepositoryInterface;
use App\Repository\EventRepositoryInterface;
use App\Repository\TeamLimitRepositoryInterface;
use App\Repository\NewsRepositoryInterface;
use App\Repository\TeamPositionRepositoryInterface;
use App\Repository\PartnerSponsorRepositoryInterface;
use App\Repository\EventMatchRepositoryInterface;
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
use App\Repository\IndividualRegistrationRepositoryInterface;
use App\Repository\TeamRegistrationRepositoryInterface;

use App\Modules\Players\PlayersService;
use App\Modules\Players\PlayersServiceInterface;
use App\Repository\Eloquent\PlayersRepository;
use App\Repository\PlayersRepositoryInterface;

use App\Modules\Variant\VariantService;
use App\Modules\Variant\VariantServiceInterface;
use App\Repository\Eloquent\VariantRepository;
use App\Repository\VariantRepositoryInterface;

use App\Modules\Discount\DiscountService;
use App\Modules\Discount\DiscountServiceInterface;
use App\Repository\Eloquent\DiscountRepository;
use App\Repository\DiscountRepositoryInterface;

use App\Modules\Faq\FaqService;
use App\Modules\Faq\FaqServiceInterface;
use App\Repository\Eloquent\FaqRepository;
use App\Repository\FaqRepositoryInterface;

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
        $this->app->bind(FieldServiceInterface::class, FieldService::class);
        $this->app->bind(ManagerServiceInterface::class, ManagerService::class);
        $this->app->bind(GuidelineServiceInterface::class, GuidelineService::class);
        $this->app->bind(AgeGroupServiceInterface::class, AgeGroupService::class);
        $this->app->bind(TeamServiceInterface::class, TeamService::class);
        $this->app->bind(TeamFolderServiceInterface::class, TeamFolderService::class);
        $this->app->bind(SeriesServiceInterface::class, SeriesService::class);
        $this->app->bind(EventServiceInterface::class, EventService::class);
        $this->app->bind(TeamLimitServiceInterface::class, TeamLimitService::class);
        $this->app->bind(TeamPositionServiceInterface::class, TeamPositionService::class);
        $this->app->bind(NewsServiceInterface::class, NewsService::class);
        $this->app->bind(PartnerSponsorServiceInterface::class, PartnerSponsorService::class);
        $this->app->bind(EventMatchServiceInterface::class, EventMatchService::class);
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
        $this->app->bind(IndividualRegistrationServiceInterface::class, IndividualRegistrationService::class);
        $this->app->bind(TeamRegistrationServiceInterface::class, TeamRegistrationService::class);
        $this->app->bind(PlayersServiceInterface::class,PlayersService::class);
        $this->app->bind(VariantServiceInterface::class,VariantService::class);
        $this->app->bind(DiscountServiceInterface::class,DiscountService::class);
        $this->app->bind(FaqServiceInterface::class,FaqService::class);

        /**
         * Repositories
         */
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(RegionRepositoryInterface::class, RegionRepository::class);
        $this->app->bind(FieldRepositoryInterface::class, FieldRepository::class);
        $this->app->bind(ManagerRepositoryInterface::class, ManagerRepository::class);
        $this->app->bind(GuidelineRepositoryInterface::class, GuidelineRepository::class);
        $this->app->bind(AgeGroupRepositoryInterface::class, AgeGroupRepository::class);
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(TeamFolderRepositoryInterface::class, TeamFolderRepository::class);
        $this->app->bind(SeriesRepositoryInterface::class, SeriesRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(TeamLimitRepositoryInterface::class, TeamLimitRepository::class);
        $this->app->bind(TeamPositionRepositoryInterface::class, TeamPositionRepository::class);
        $this->app->bind(NewsRepositoryInterface::class, NewsRepository::class);
        $this->app->bind(PartnerSponsorRepositoryInterface::class, PartnerSponsorRepository::class);
        $this->app->bind(EventMatchRepositoryInterface::class, EventMatchRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ShippingRepositoryInterface::class, ShippingRepository::class);
        $this->app->bind(StateShippingRepositoryInterface::class, StateShippingRepository::class);
        $this->app->bind(CityShippingRepositoryInterface::class, CityShippingRepository::class);
        $this->app->bind(OtherCountryShippingRepositoryInterface::class, OtherCountryShippingRepository::class);
        $this->app->bind(OtherStateShippingRepositoryInterface::class, OtherStateShippingRepository::class);
        $this->app->bind(OtherCityShippingRepositoryInterface::class, OtherCityShippingRepository::class);
        $this->app->bind(IndividualRegistrationRepositoryInterface::class, IndividualRegistrationRepository::class);
        $this->app->bind(TeamRegistrationRepositoryInterface::class, TeamRegistrationRepository::class);
        $this->app->bind(PlayersRepositoryInterface::class, PlayersRepository::class);
        $this->app->bind(VariantRepositoryInterface::class, VariantRepository::class);
        $this->app->bind(DiscountRepositoryInterface::class, DiscountRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
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
