<?php

namespace Oro\Bundle\EntityConfigBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveRedundantEntityConfigIndexes implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new ParametrizedSqlMigrationQuery(
                'DELETE FROM oro_entity_config_index_value WHERE scope=:scope and code=:code',
                ['scope' => 'entity', 'code' => 'label'],
                ['scope' => 'string', 'code' => 'string']
            )
        );
    }
}
