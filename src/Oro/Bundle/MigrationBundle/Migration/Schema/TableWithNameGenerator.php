<?php

namespace Oro\Bundle\MigrationBundle\Migration\Schema;

use Oro\Bundle\MigrationBundle\Tools\DbIdentifierNameGenerator;

class TableWithNameGenerator extends Table
{
    /**
     * @var DbIdentifierNameGenerator
     */
    protected $nameGenerator;

    public function __construct(array $args)
    {
        $this->nameGenerator = $args['nameGenerator'];

        parent::__construct($args);
    }

    #[\Override]
    public function addIndex(array $columnNames, $indexName = null, array $flags = [], array $options = [])
    {
        if (!$indexName) {
            $indexName = $this->nameGenerator->generateIndexName(
                $this->getName(),
                $columnNames
            );
        }

        return parent::addIndex($columnNames, $indexName, $flags, $options);
    }

    #[\Override]
    public function addUniqueIndex(array $columnNames, $indexName = null, array $options = [])
    {
        if (!$indexName) {
            $indexName = $this->nameGenerator->generateIndexName(
                $this->getName(),
                $columnNames,
                true
            );
        }

        return parent::addUniqueIndex($columnNames, $indexName, $options);
    }

    #[\Override]
    public function addForeignKeyConstraint(
        $foreignTable,
        array $localColumnNames,
        array $foreignColumnNames,
        array $options = [],
        $constraintName = null
    ) {
        if (!$constraintName) {
            $constraintName = $this->nameGenerator->generateForeignKeyConstraintName(
                $this->getName(),
                $localColumnNames
            );
        }

        return parent::addForeignKeyConstraint(
            $foreignTable,
            $localColumnNames,
            $foreignColumnNames,
            $options,
            $constraintName
        );
    }
}
