<?php

namespace Oro\Bundle\ApiBundle\Collection\QueryVisitorExpression;

use Oro\Bundle\ApiBundle\Collection\QueryExpressionVisitor;

/**
 * Represents NOT EQUAL TO comparison expression.
 */
class NeqComparisonExpression implements ComparisonExpressionInterface
{
    #[\Override]
    public function walkComparisonExpression(
        QueryExpressionVisitor $visitor,
        string $field,
        string $expression,
        string $parameterName,
        mixed $value
    ): mixed {
        if (null === $value) {
            return $visitor->getExpressionBuilder()->isNotNull($expression);
        }

        $visitor->addParameter($parameterName, $value);

        return $visitor->getExpressionBuilder()
            ->neq($expression, $visitor->buildParameterExpression($parameterName, $value));
    }
}
