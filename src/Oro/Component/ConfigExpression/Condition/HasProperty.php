<?php

namespace Oro\Component\ConfigExpression\Condition;

use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Checks whether the property exists in the context.
 */
class HasProperty extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    /** @var string */
    protected $object;

    /** @var string */
    protected $property;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    public function setPropertyAccesor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    #[\Override]
    public function toArray()
    {
        $params = [$this->object, $this->property];
        return $this->convertToArray($params);
    }

    #[\Override]
    public function compile($factoryAccessor)
    {
        $params = [$this->object, $this->property];
        return $this->convertToPhpCode($params, $factoryAccessor);
    }

    #[\Override]
    protected function isConditionAllowed($context)
    {
        $object = $this->resolveValue($context, $this->object);
        $property = $this->resolveValue($context, $this->property);

        return (
            $this->propertyAccessor->isReadable($object, $property)
            && $this->propertyAccessor->isWritable($object, $property)
        );
    }

    /**
     * Returns the expression name.
     *
     * @return string
     */
    #[\Override]
    public function getName()
    {
        return 'has_property';
    }

    #[\Override]
    public function initialize(array $options)
    {
        if (2 !== count($options)) {
            throw new Exception\InvalidArgumentException(
                sprintf('Options must have 2 elements, but %d given.', count($options))
            );
        }

        if (isset($options['object'])) {
            $this->object = $options['object'];
        } elseif (isset($options[0])) {
            $this->object = $options[0];
        } else {
            throw new Exception\InvalidArgumentException('Option "object" is required.');
        }

        if (isset($options['property'])) {
            $this->property = $options['property'];
        } elseif (isset($options[1])) {
            $this->property = $options[1];
        } else {
            throw new Exception\InvalidArgumentException('Option "property" is required.');
        }

        return $this;
    }
}
