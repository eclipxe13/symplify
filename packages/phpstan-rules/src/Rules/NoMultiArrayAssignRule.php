<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use Symplify\Astral\ValueObject\AttributeKey;
use Symplify\PHPStanRules\Printer\NodeComparator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\NoMultiArrayAssignRule\NoMultiArrayAssignRuleTest
 */
final class NoMultiArrayAssignRule extends AbstractSymplifyRule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Use value object over multi array assign';

    public function __construct(
        private NodeComparator $nodeComparator
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     * @return string[]
     */
    public function process(Node $node, Scope $scope): array
    {
        if (! $node->var instanceof ArrayDimFetch) {
            return [];
        }

        // is previous array dim assign too? - print the exprt conteont
        $previousArrayDimFetch = $this->matchParentArrayDimFetch($node);
        if (! $previousArrayDimFetch instanceof ArrayDimFetch) {
            return [];
        }

        if (! $this->haveSameArrayDimFetchNonEmptyRoot($node->var, $previousArrayDimFetch)) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
$values = [];
$values['person']['name'] = 'Tom';
$values['person']['surname'] = 'Dev';
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$values = [];
$values[] = new Person('Tom', 'Dev');
CODE_SAMPLE
            ),
        ]);
    }

    private function haveSameArrayDimFetchNonEmptyRoot(
        ArrayDimFetch $firstArrayDimFetch,
        ArrayDimFetch $secondArrayDimFetch
    ): bool {
        $singleNestedFirstArrayDimFetch = $this->resolveSingleNestedArrayDimFetch($firstArrayDimFetch);

        if ($singleNestedFirstArrayDimFetch->dim === null) {
            return false;
        }
        $singleNestedSecondArrayDimFetch = $this->resolveSingleNestedArrayDimFetch($secondArrayDimFetch);

        return $this->nodeComparator->areNodesEqual($singleNestedFirstArrayDimFetch, $singleNestedSecondArrayDimFetch);
    }

    private function resolveSingleNestedArrayDimFetch(ArrayDimFetch $arrayDimFetch): ArrayDimFetch
    {
        while ($arrayDimFetch->var instanceof ArrayDimFetch) {
            $arrayDimFetch = $arrayDimFetch->var;
        }

        return $arrayDimFetch;
    }

    private function matchParentArrayDimFetch(Assign $assign): ?Expr
    {
        $parent = $assign->getAttribute(AttributeKey::PARENT);
        if (! $parent instanceof Expression) {
            return null;
        }

        $previous = $parent->getAttribute(AttributeKey::PREVIOUS);
        if (! $previous instanceof Expression) {
            return null;
        }
        if (! $previous->expr instanceof Assign) {
            return null;
        }

        $previousAssign = $previous->expr;
        if (! $previousAssign->var instanceof ArrayDimFetch) {
            return null;
        }

        return $previousAssign->var;
    }
}
