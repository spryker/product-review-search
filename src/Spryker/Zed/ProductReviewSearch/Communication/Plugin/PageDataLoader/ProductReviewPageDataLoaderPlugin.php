<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductReviewSearch\Communication\Plugin\PageDataLoader;

use Generated\Shared\Transfer\ProductPageLoadTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductPageDataLoaderPluginInterface;

/**
 * @method \Spryker\Zed\ProductReviewSearch\Persistence\ProductReviewSearchRepositoryInterface getRepository()
 * @method \Spryker\Zed\ProductReviewSearch\Business\ProductReviewSearchFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductReviewSearch\Communication\ProductReviewSearchCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductReviewSearch\ProductReviewSearchConfig getConfig()
 * @method \Spryker\Zed\ProductReviewSearch\Persistence\ProductReviewSearchQueryContainerInterface getQueryContainer()
 */
class ProductReviewPageDataLoaderPlugin extends AbstractPlugin implements ProductPageDataLoaderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductPageLoadTransfer $productPageLoadTransfer
     *
     * @return \Generated\Shared\Transfer\ProductPageLoadTransfer
     */
    public function expandProductPageDataTransfer(ProductPageLoadTransfer $productPageLoadTransfer)
    {
        $productReviews = $this->getRepository()
            ->getProductReviewRatingByIdAbstractProductIn($productPageLoadTransfer->getProductAbstractIds());

        $updatedPayloadTransfers = $this->updatePayloadTransfers($productPageLoadTransfer->getPayloadTransfers(), $productReviews);
        $productPageLoadTransfer->setPayloadTransfers($updatedPayloadTransfers);

        return $productPageLoadTransfer;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductPayloadTransfer> $payloadTransfers
     * @param array $productReviewsList
     *
     * @return array<\Generated\Shared\Transfer\ProductPayloadTransfer>
     */
    protected function updatePayloadTransfers(array $payloadTransfers, array $productReviewsList): array
    {
        foreach ($payloadTransfers as $payloadTransfer) {
            $productReviews = $productReviewsList[$payloadTransfer->getIdProductAbstract()] ?? [];

            $payloadTransfer->fromArray($productReviews, true);
        }

        return $payloadTransfers;
    }
}
