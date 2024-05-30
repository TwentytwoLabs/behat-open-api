<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use TwentytwoLabs\ArrayComparator\Comparator\ComparatorChain;
use TwentytwoLabs\BehatOpenApiExtension\Context\JsonContext;

final class JsonInitializer implements ContextInitializer
{
    private ComparatorChain $comparatorChain;

    public function __construct(ComparatorChain $comparatorChain)
    {
        $this->comparatorChain = $comparatorChain;
    }

    public function initializeContext(Context $context): void
    {
        if ($context instanceof JsonContext) {
            $context->setComparatorChain($this->comparatorChain);
        }
    }
}
