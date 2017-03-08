<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QueryPropelRule\Persistence\QueryBuilder\TransferMapper;

use Generated\Shared\Transfer\PropelQueryBuilderRuleSetTransfer;
use Spryker\Zed\QueryPropelRule\Dependency\Service\QueryPropelRuleToUtilEncodingInterface;

class RuleTransferMapper implements RuleTransferMapperInterface
{

    /**
     * @var \Spryker\Zed\QueryPropelRule\Dependency\Service\QueryPropelRuleToUtilEncodingInterface
     */
    protected $utilEncodingService;

    /**
     * @param \Spryker\Zed\QueryPropelRule\Dependency\Service\QueryPropelRuleToUtilEncodingInterface $utilEncodingService
     */
    public function __construct(QueryPropelRuleToUtilEncodingInterface $utilEncodingService)
    {
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param string $json
     *
     * @return \Generated\Shared\Transfer\PropelQueryBuilderRuleSetTransfer
     */
    public function createRuleQuerySetFromJson($json)
    {
        $json = trim($json);

        $querySetTransfer = new PropelQueryBuilderRuleSetTransfer();
        $querySetTransfer->fromArray(
            $this->utilEncodingService->decodeJson($json, true)
        );

        return $querySetTransfer;
    }

}
