<?php

namespace Symplify\EasyCodingStandard\ValueObject;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\ForLoopShouldBeWhileLoopSniff;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\Classes\PropertyDeclarationSniff;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\MethodDeclarationSniff;

final class WarningToError
{
    /**
     * Explicit list for classes that use only warnings. ECS only reports errors, so this one promotes them to error.
     *
     * @var array<class-string<Sniff>>
     */
    private const REPORT_WARNINGS_SNIFFS = [
        AssignmentInConditionSniff::class,
        PropertyDeclarationSniff::class,
        MethodDeclarationSniff::class,
        ForLoopShouldBeWhileLoopSniff::class,
    ];

    /**
     * Complete list (predefined + configured) of sniff classes to report warnings as errors.
     *
     * @var array<class-string<Sniff>>
     */
    private array $sniffClasses;

    /**
     * @param array<class-string<Sniff>> $reportWarnings
     */
    public function __construct(array $reportWarnings = [])
    {
        $this->sniffClasses = array_merge(self::REPORT_WARNINGS_SNIFFS, $reportWarnings);
    }

    public function hasSniffClass(string $sniffClass): bool
    {
        foreach ($this->sniffClasses as $current) {
            if (is_a($sniffClass, $current, true)) {
                return true;
            }
        }

        return false;
    }
}
