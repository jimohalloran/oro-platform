<?php

namespace Oro\Bundle\SecurityBundle\Acl\Voter;

use Oro\Bundle\SecurityBundle\Acl\Domain\DomainObjectWrapper;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Extension\ObjectIdentityHelper;
use Oro\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Voter\AclVoter as BaseAclVoter;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * This voter uses ACL to determine whether the access to the particular resource is granted or not.
 */
class AclVoter extends BaseAclVoter implements AclVoterInterface
{
    /**
     * @var AclExtensionSelector
     */
    protected $extensionSelector;

    /**
     * An object which is the subject of the current voting operation
     *
     * @var mixed
     */
    private $object;

    /**
     * The security token of the current voting operation
     *
     * @var mixed
     */
    private $securityToken;

    /**
     * An ACL extension responsible to process an object of the current voting operation
     *
     * @var AclExtensionInterface
     */
    private $extension;

    /**
     * @var OneShotIsGrantedObserver|OneShotIsGrantedObserver[]
     */
    protected $oneShotIsGrantedObserver;

    /**
     * @var AclGroupProviderInterface
     */
    protected $groupProvider;

    /**
     * Sets the ACL extension selector
     */
    public function setAclExtensionSelector(AclExtensionSelector $selector)
    {
        $this->extensionSelector = $selector;
    }

    public function setAclGroupProvider(AclGroupProviderInterface $provider)
    {
        $this->groupProvider = $provider;
    }

    /**
     * Adds an observer is used to inform a caller about IsGranted operation details
     */
    #[\Override]
    public function addOneShotIsGrantedObserver(OneShotIsGrantedObserver $observer): void
    {
        if (null !== $this->oneShotIsGrantedObserver) {
            if (!is_array($this->oneShotIsGrantedObserver)) {
                $this->oneShotIsGrantedObserver = [$this->oneShotIsGrantedObserver];
            }
            $this->oneShotIsGrantedObserver[] = $observer;
        } else {
            $this->oneShotIsGrantedObserver = $observer;
        }
    }

    #[\Override]
    public function vote(TokenInterface $token, $object, array $attributes): int
    {
        $extension = $this->extensionSelector->select($object, false);
        if (null === $extension) {
            return self::ACCESS_ABSTAIN;
        }

        $this->securityToken = $token;
        $this->extension = $extension;
        try {
            $attributes = $this->updateAttributes($attributes);
            $group = $this->setObject($object);
            $result = $this->checkAclGroup($attributes, $group);
            if (self::ACCESS_DENIED !== $result) {
                $result = parent::vote($token, $this->getObjectToVote($object), $attributes);
            }
        } finally {
            $this->securityToken = null;
            $this->extension = null;
            $this->object = null;
            $this->oneShotIsGrantedObserver = null;
        }

        return $result;
    }

    #[\Override]
    public function getObject()
    {
        return $this->object;
    }

    #[\Override]
    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    #[\Override]
    public function getAclExtension()
    {
        return $this->extension;
    }

    #[\Override]
    public function setTriggeredMask($mask, $accessLevel)
    {
        if (null !== $this->oneShotIsGrantedObserver) {
            if (is_array($this->oneShotIsGrantedObserver)) {
                /** @var OneShotIsGrantedObserver $observer */
                foreach ($this->oneShotIsGrantedObserver as $observer) {
                    $observer->setAccessLevel($accessLevel);
                }
            } else {
                $this->oneShotIsGrantedObserver->setAccessLevel($accessLevel);
            }
        }
    }

    /**
     * @param mixed $object
     *
     * @return string|null ACL group
     */
    protected function setObject($object)
    {
        if ($object instanceof FieldVote) {
            $object = $object->getDomainObject();
        }

        $identityObject = $object;
        if ($object instanceof DomainObjectWrapper) {
            $identityObject = $object->getObjectIdentity();
        }

        $group = null;
        if ($identityObject instanceof ObjectIdentity) {
            list($type, $group) = ObjectIdentityHelper::parseType($identityObject->getType());
            if (null !== $group) {
                $identityObject = new ObjectIdentity($identityObject->getIdentifier(), $type);
                $object = $object instanceof DomainObjectWrapper
                    ? new DomainObjectWrapper($object->getDomainObject(), $identityObject)
                    : $identityObject;
            }
            if (null === $group) {
                $group = AclGroupProviderInterface::DEFAULT_SECURITY_GROUP;
            }
        }

        $this->object = $object;

        return $group;
    }

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    protected function getObjectToVote($object)
    {
        return $object instanceof FieldVote
            // create new FieldVote with DomainObject that have no information about the group in it.
            ? new FieldVote($this->object, $object->getField())
            : $this->object;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function updateAttributes(array $attributes)
    {
        // replace empty permissions with default ones
        $count = count($attributes);
        for ($i = 0; $i < $count; $i++) {
            if (empty($attributes[$i])) {
                $attributes[$i] = $this->extension->getDefaultPermission();
            }
        }

        return $attributes;
    }

    /**
     * @param array  $attributes
     * @param string $group
     *
     * @return int
     */
    protected function checkAclGroup(array $attributes, $group)
    {
        if (null === $group || null === $this->groupProvider || !$this->object) {
            return self::ACCESS_ABSTAIN;
        }

        $result = self::ACCESS_DENIED;
        if ($group === $this->groupProvider->getGroup()) {
            $result = self::ACCESS_ABSTAIN;

            $permissions = $this->extension->getPermissions(null, false, true);
            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute)) {
                    continue;
                }

                if (!in_array($attribute, $permissions, true)) {
                    $result = self::ACCESS_DENIED;
                    break;
                }
            }
        }

        return $result;
    }
}
