<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\NodeAnalyzer;

use Nette\Application\UI\Template;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\PHPStanRules\TypeAnalyzer\ObjectTypeAnalyzer;

final class NetteTypeAnalyzer
{
    /**
     * @var array<class-string<Template>>
     */
    private const TEMPLATE_TYPES = [
        'Nette\Application\UI\Template',
        'Nette\Bridges\ApplicationLatte\Template',
        'Nette\Bridges\ApplicationLatte\DefaultTemplate',
    ];

    public function __construct(
        private ObjectTypeAnalyzer $objectTypeAnalyzer,
        private SimpleNameResolver $simpleNameResolver
    ) {
    }

    /**
     * E.g. $this->template->key
     */
    public function isTemplateMagicPropertyType(Expr $expr, Scope $scope): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        if (! $expr->var instanceof PropertyFetch) {
            return false;
        }

        return $this->isTemplateType($expr->var, $scope);
    }

    /**
     * E.g. $this->template
     */
    public function isTemplateType(Expr $expr, Scope $scope): bool
    {
        $callerType = $scope->getType($expr);
        return $this->objectTypeAnalyzer->isObjectOrUnionOfObjectTypes($callerType, self::TEMPLATE_TYPES);
    }

    /**
     * This type has getComponent() method
     */
    public function isInsideComponentContainer(Scope $scope): bool
    {
        $className = $this->simpleNameResolver->getClassNameFromScope($scope);
        if ($className === null) {
            return false;
        }

        // this type has getComponent() method
        return is_a($className, 'Nette\ComponentModel\Container', true);
    }

    public function isInsideControl(Scope $scope): bool
    {
        $className = $this->simpleNameResolver->getClassNameFromScope($scope);
        if ($className === null) {
            return false;
        }

        return is_a($className, 'Nette\Application\UI\Control', true);
    }
}
