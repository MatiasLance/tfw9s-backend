<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Item\ItemServiceInterface;
use App\Modules\Categories\CategoryServiceInterface;
use App\Modules\News\NewsServiceInterface;
use App\Modules\PartnerSponsor\PartnerSponsorServiceInterface;
use Illuminate\Http\Request;

class TotalController extends Controller
{
    protected ItemServiceInterface $itemsService;
    protected CategoryServiceInterface $categoryService;
    protected NewsServiceInterface $newsService;
    protected PartnerSponsorServiceInterface $partnerSponsorService;

    public function __construct(ItemServiceInterface $itemsService, CategoryServiceInterface $categoryService, NewsServiceInterface $newsService, PartnerSponsorServiceInterface $partnerSponsorService)
    {
        $this->itemsService = $itemsService;
        $this->categoryService = $categoryService;
        $this->newsService = $newsService;
        $this->partnerSponsorService = $partnerSponsorService;
    }
    
    public function retrieve(Message $message)
    {
        $products = $this->itemsService->countItems();
        $discountcode = $this->itemsService->countDiscountCode();
        $category = $this->categoryService->countCategory();
        $news = $this->newsService->countNews();
        $partnerSponsor = $this->partnerSponsorService->countPartnerSponsor();
    
        $message->setContent(200, 'Total count retrieved', '', [
            'products' => $products,
            'discountcode' => $discountcode,
            'category' => $category,
            'news' => $news,
            'partner_sponsor' => $partnerSponsor,
        ]);
    
        return $message->render();
    }    
}
