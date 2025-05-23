<?php

namespace Oro\Bundle\EntityBundle\DataCollector;

use Doctrine\DBAL\Logging\DebugStack;
use Oro\Bundle\EntityBundle\DataCollector\Analyzer\DuplicateQueryAnalyzer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects identical and similar queries number
 */
class DuplicateQueriesDataCollector extends DataCollector
{
    /**
     * @var array
     */
    protected $loggers = [];

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Adds the stack logger for a doctrine connection.
     *
     * @param string $name
     * @param DebugStack $logger
     */
    public function addLogger($name, DebugStack $logger)
    {
        $this->loggers[$name] = $logger;
    }

    #[\Override]
    public function collect(Request $request, Response $response, ?\Throwable $exception = null)
    {
        foreach ($this->loggers as $name => $logger) {
            $queryAnalyser = new DuplicateQueryAnalyzer();
            foreach ($logger->queries as $query) {
                $queryAnalyser->addQuery($query['sql'], (array)$query['params']);
            }

            $this->data['queriesCount'][$name] = $queryAnalyser->getQueriesCount();
            $this->data['identical'][$name] = $queryAnalyser->getIdenticalQueries();
            $this->data['similar'][$name] = $queryAnalyser->getSimilarQueries();
        }
    }

    #[\Override]
    public function getName(): string
    {
        return 'duplicate_queries';
    }

    /**
     * @return mixed
     */
    public function getQueriesCount()
    {
        return array_sum($this->data['queriesCount']);
    }

    /**
     * @return mixed
     */
    public function getIdenticalQueries()
    {
        return $this->data['identical'];
    }

    /**
     * @return number
     */
    public function getIdenticalQueriesCount()
    {
        return $this->countGroupedQueries($this->data['identical']);
    }

    /**
     * @return mixed
     */
    public function getSimilarQueries()
    {
        return $this->data['similar'];
    }

    /**
     * @return number
     */
    public function getSimilarQueriesCount()
    {
        return $this->countGroupedQueries($this->data['similar']);
    }

    /**
     * @param array $queries
     * @return number
     */
    protected function countGroupedQueries(array $queries)
    {
        return array_sum(array_map('count', $queries));
    }

    #[\Override]
    public function reset()
    {
        $this->data = [
            'queriesCount' => [],
            'identical' => [],
            'similar' => [],
        ];
    }
}
