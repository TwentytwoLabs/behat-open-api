<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TwentytwoLabs\BehatOpenApiExtension\Handler\GuzzleHandler;
use TwentytwoLabs\BehatOpenApiExtension\Initializer\ClientAwareInitializer;

/**
 * class BehatOpenApiExtension.
 */
class BehatOpenApiExtension implements ExtensionInterface
{
    private const GUZZLE_CLIENT_ID = 'guzzle.client';

    public function getConfigKey()
    {
        return 'open_api_extension';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $container->setDefinition(
            self::GUZZLE_CLIENT_ID,
            new Definition(GuzzleHandler::class, ['$baseUri' => $config['base_uri']])
        );

        $definition = new Definition(
            ClientAwareInitializer::class,
            [new Reference(self::GUZZLE_CLIENT_ID), []]
        );
        $definition->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);

        $container->setDefinition('guzzle.context_initializer', $definition);
    }

    public function process(ContainerBuilder $container)
    {
    }
}
