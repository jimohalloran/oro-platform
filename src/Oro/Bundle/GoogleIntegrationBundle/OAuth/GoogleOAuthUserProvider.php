<?php

namespace Oro\Bundle\GoogleIntegrationBundle\OAuth;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SSOBundle\Security\Core\User\OAuthUserProviderInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;

/**
 * The OAuth single sign-on authentication user provider for Google.
 */
class GoogleOAuthUserProvider implements OAuthUserProviderInterface
{
    /** @var ConfigManager */
    private $configManager;

    /** @var UserManager */
    private $userManager;

    /**
     * Constructor
     */
    public function __construct(UserManager $userManager, ConfigManager $configManager)
    {
        $this->userManager = $userManager;
        $this->configManager = $configManager;
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return (bool)$this->configManager->get('oro_google_integration.enable_sso');
    }

    #[\Override]
    public function getAllowedDomains(): array
    {
        return (array)$this->configManager->get('oro_google_integration.sso_domains');
    }

    #[\Override]
    public function findUser(UserResponseInterface $response): ?UserInterface
    {
        $username = $response->getUserIdentifier();
        $user = $this->userManager->findUserBy(['googleId' => $username]);
        if (null === $user) {
            $email = $response->getEmail();
            if ($email) {
                $user = $this->userManager->findUserByEmail($email);
                if (null !== $user) {
                    $user->setGoogleId($username);
                    $this->userManager->updateUser($user);
                }
            }
        }

        return $user;
    }
}
