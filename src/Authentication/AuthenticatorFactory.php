<?php

namespace Keboola\AzureKeyVaultClient\Authentication;

use Keboola\AzureKeyVaultClient\Exception\ClientException;
use Keboola\AzureKeyVaultClient\GuzzleClientFactory;
use Psr\Log\LoggerInterface;

class AuthenticatorFactory
{
    /**
     * @param LoggerInterface $logger
     * @param GuzzleClientFactory $clientFactory
     * @return AuthenticatorInterface
     */
    public function getAuthenticator(GuzzleClientFactory $clientFactory, $resource)
    {
        $authenticators = [
            ClientCredentialsEnvironmentAuthenticator::class,
            ManagedCredentialsAuthenticator::class,
        ];
        foreach ($authenticators as $authenticatorClass) {
            /** @var AuthenticatorInterface $authenticator */
            $authenticator = new $authenticatorClass($clientFactory, $resource);
            try {
                $authenticator->checkUsability();
                return $authenticator;
            } catch (ClientException $e) {
                $clientFactory->getLogger()->debug($authenticatorClass . ' is not usable: ' . $e->getMessage());
            }
        }
        throw new ClientException('No suitable authentication method found.');
    }
}
