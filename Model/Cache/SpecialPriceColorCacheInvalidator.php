<?php

declare(strict_types=1);

namespace LeanCommerce\CustomSpecialPriceColor\Model\Cache;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Cache\Type\Block as BlockCache;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Indexer\CacheContextFactory;
use Magento\PageCache\Model\Cache\Type as PageCache;

class SpecialPriceColorCacheInvalidator
{
    private TypeListInterface $cacheTypeList;

    private CacheInterface $cache;

    private CacheContextFactory $cacheContextFactory;

    private EventManagerInterface $eventManager;

    public function __construct(
        TypeListInterface $cacheTypeList,
        CacheInterface $cache,
        CacheContextFactory $cacheContextFactory,
        EventManagerInterface $eventManager
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->cache = $cache;
        $this->cacheContextFactory = $cacheContextFactory;
        $this->eventManager = $eventManager;
    }

    public function cleanGlobalConfigCaches(): void
    {
        $this->cacheTypeList->cleanType(ConfigCache::TYPE_IDENTIFIER);
        $this->cacheTypeList->cleanType(BlockCache::TYPE_IDENTIFIER);
        $this->cacheTypeList->cleanType(PageCache::TYPE_IDENTIFIER);
    }

    public function cleanProductCache(Product $product): void
    {
        $productId = (int) $product->getId();
        if ($productId <= 0) {
            return;
        }

        $tags = array_unique(array_merge(
            [Product::CACHE_TAG . '_' . $productId],
            $product->getIdentities()
        ));

        $this->cleanEntityTags(Product::CACHE_TAG, [$productId], $tags);
    }

    public function cleanCategoryCache(Category $category): void
    {
        $categoryId = (int) $category->getId();
        if ($categoryId <= 0) {
            return;
        }

        $tags = array_unique(array_merge(
            [Category::CACHE_TAG . '_' . $categoryId],
            $category->getIdentities()
        ));

        $this->cleanEntityTags(Category::CACHE_TAG, [$categoryId], $tags);
    }

    /**
     * @param int[] $entityIds
     * @param string[] $tags
     */
    private function cleanEntityTags(string $cacheTag, array $entityIds, array $tags): void
    {
        $cacheContext = $this->cacheContextFactory->create();
        $cacheContext->registerEntities($cacheTag, $entityIds);
        $cacheContext->registerTags($tags);

        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);
        $this->cache->clean($tags);
    }
}
