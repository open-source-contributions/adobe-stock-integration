<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeStockAsset\Model;

use Magento\AdobeStockAsset\Model\ResourceModel\Asset as ResourceModel;
use Magento\AdobeStockAsset\Model\ResourceModel\Asset\Collection as AssetCollection;
use Magento\AdobeStockAsset\Model\ResourceModel\Asset\CollectionFactory as AssetCollectionFactory;
use Magento\AdobeStockAsset\Model\ResourceModel\Asset\Command\Save;
use Magento\AdobeStockAssetApi\Api\AssetRepositoryInterface;
use Magento\AdobeStockAssetApi\Api\Data\AssetInterface;
use Magento\AdobeStockAssetApi\Api\Data\AssetSearchResultsInterface;
use Magento\AdobeStockAssetApi\Api\Data\AssetSearchResultsInterfaceFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AssetRepository
 */
class AssetRepository implements AssetRepositoryInterface
{
    /**
     * @var Save
     */
    private $assetSaveService;

    /**
     * @var ResourceModel
     */
    private $resource;

    /**
     * @var AssetFactory
     */
    private $factory;

    /**
     * @var AssetCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var AssetSearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * AssetRepository constructor.
     *
     * @param ResourceModel $resource
     * @param AssetCollectionFactory $collectionFactory
     * @param AssetFactory $factory
     * @param JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param AssetSearchResultsInterfaceFactory $searchResultFactory
     * @param Save $commandSave
     */
    public function __construct(
        ResourceModel $resource,
        AssetCollectionFactory $collectionFactory,
        AssetFactory $factory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        AssetSearchResultsInterfaceFactory $searchResultFactory,
        Save $commandSave
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->factory = $factory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultFactory = $searchResultFactory;
        $this->assetSaveService = $commandSave;
    }

    /**
     * Save asset
     *
     * @param AssetInterface $asset
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(AssetInterface $asset): void
    {
        $this->assetSaveService->execute($asset);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AssetSearchResultsInterface
    {
        try {
            /** @var AssetCollection $collection */
            $collection = $this->collectionFactory->create();
            $this->joinProcessor->process(
                $collection,
                AssetInterface::class
            );

            $this->collectionProcessor->process($searchCriteria, $collection);

            $items = [];
            /** @var AssetInterface $item */
            foreach ($collection->getItems() as $item) {
                $items[] = $item;
            }

            /** @var AssetSearchResultsInterface $searchResults */
            $searchResults = $this->searchResultFactory->create();
            $searchResults->setItems($items);
            $searchResults->setSearchCriteria($searchCriteria);
            $searchResults->setTotalCount($collection->getSize());

            return $searchResults;
        } catch (\Exception $exception) {
            $message = __('An error occurred during get asset list: %1', $exception->getMessage());
            throw new IntegrationException($message, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): AssetInterface
    {
        /** @var AssetInterface $asset */
        $asset = $this->factory->create();
        $this->resource->load($asset, $id);
        if (!$asset->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }

        return $asset;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): void
    {
        $this->delete($this->getById($id));
    }
}
