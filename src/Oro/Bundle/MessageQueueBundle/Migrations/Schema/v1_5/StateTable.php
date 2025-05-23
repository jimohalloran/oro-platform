<?php

namespace Oro\Bundle\MessageQueueBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class StateTable implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        if (!$schema->hasTable('oro_message_queue_state')) {
            $table = $schema->createTable('oro_message_queue_state');
            $table->addColumn('id', 'string', ['length' => 15, 'notnull' => true]);
            $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
            $table->setPrimaryKey(['id']);

            $queries->addPostQuery(
                new ParametrizedSqlMigrationQuery(
                    'INSERT INTO oro_message_queue_state (id, updated_at) VALUES (:id, :updated_at)',
                    ['id' => 'cache', 'updated_at' => null],
                    ['id' => Types::STRING, 'updated_at' => Types::DATETIME_MUTABLE]
                )
            );
        }
    }
}
