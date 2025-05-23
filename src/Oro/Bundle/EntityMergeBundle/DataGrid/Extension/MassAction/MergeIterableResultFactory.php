<?php

namespace Oro\Bundle\EntityMergeBundle\DataGrid\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResult;
use Oro\Bundle\DataGridBundle\Extension\MassAction\IterableResultFactory;
use Oro\Bundle\EntityMergeBundle\Datasource\Orm\MergeIterableResult;

class MergeIterableResultFactory extends IterableResultFactory
{
    #[\Override]
    protected function getIterableResult($qb): IterableResult
    {
        return new MergeIterableResult($qb);
    }
}
