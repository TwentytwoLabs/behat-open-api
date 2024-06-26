<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use JsonSchema\Validator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Serializer\Encoder\ChainDecoder;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use TwentytwoLabs\ApiValidator\Decoder\Adapter\SymfonyDecoderAdapter;
use TwentytwoLabs\ApiValidator\Validator\MessageValidator;
use TwentytwoLabs\ArrayComparator\Comparator\ArrayComparator;
use TwentytwoLabs\ArrayComparator\Comparator\ComparatorChain;
use TwentytwoLabs\ArrayComparator\Comparator\DateComparator;
use TwentytwoLabs\ArrayComparator\Comparator\DateTimeComparator;
use TwentytwoLabs\ArrayComparator\Comparator\IntegerComparator;
use TwentytwoLabs\ArrayComparator\Comparator\SameComparator;
use TwentytwoLabs\ArrayComparator\Comparator\StringComparator;
use TwentytwoLabs\ArrayComparator\Comparator\UuidComparator;
use TwentytwoLabs\BehatOpenApiExtension\Initializer\JsonInitializer;
use TwentytwoLabs\BehatOpenApiExtension\Initializer\OpenApiInitializer;

final class BehatOpenApiExtension implements ExtensionInterface
{
    public function getConfigKey(): string
    {
        return 'open_api_extension';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('schemaFile')->defaultNull()->end()
            ->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $chainDecoderDefinition = new Definition(
            ChainDecoder::class,
            [
                '$decoders' => [
                    new Definition(JsonDecode::class),
                    new Definition(XmlEncoder::class),
                    new Definition(YamlEncoder::class),
                ],
            ]
        );
        $decoderDefinition = new Definition(SymfonyDecoderAdapter::class, ['$decoder' => $chainDecoderDefinition]);

        $validatorDefinition = new Definition(
            MessageValidator::class,
            ['$validator' => new Definition(Validator::class), '$decoder' => $decoderDefinition]
        );

        $openApiInitializerDefinition = new Definition(
            OpenApiInitializer::class,
            [
                '$validator' => $validatorDefinition,
                '$schemaFile' => null === $config['schemaFile'] ? null : $this->resolveFile($config['schemaFile']),
            ]
        );
        $openApiInitializerDefinition->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);

        $container->setDefinition('open-api.context_initializer', $openApiInitializerDefinition);

        $comparatorChainDefinition = new Definition(ComparatorChain::class);
        $comparatorChainDefinition
            ->addMethodCall('addComparators', [new Definition(IntegerComparator::class)])
            ->addMethodCall('addComparators', [new Definition(StringComparator::class)])
            ->addMethodCall('addComparators', [new Definition(DateTimeComparator::class)])
            ->addMethodCall('addComparators', [new Definition(DateComparator::class)])
            ->addMethodCall('addComparators', [new Definition(UuidComparator::class)])
            ->addMethodCall('addComparators', [new Definition(ArrayComparator::class)])
            ->addMethodCall('addComparators', [new Definition(SameComparator::class)])
        ;

        $jsonInitializerDefinition = new Definition(
            JsonInitializer::class,
            ['$comparatorChain' => $comparatorChainDefinition]
        );
        $jsonInitializerDefinition->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);

        $container->setDefinition('open-api.json-context_initializer', $jsonInitializerDefinition);
    }

    public function process(ContainerBuilder $container)
    {
    }

    private function resolveFile(string $file): string
    {
        if (str_starts_with($file, 'file://')) {
            return str_replace(
                [sprintf('.%s', DIRECTORY_SEPARATOR), sprintf('%%kernel.project%%%s', DIRECTORY_SEPARATOR)],
                sprintf('%s%s', realpath('.'), DIRECTORY_SEPARATOR),
                $file
            );
        }

        return $file;
    }
}
