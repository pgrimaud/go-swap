<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
class TypedClassConstantRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();
        $errors = [];

        // Only check classes in src/ directory
        $classFileName = $classReflection->getFileName();
        if ($classFileName === null || !str_contains($classFileName, '/src/')) {
            return [];
        }

        foreach ($classReflection->getNativeReflection()->getReflectionConstants() as $constant) {
            // Only check constants declared in the current class (not inherited)
            if ($constant->getDeclaringClass()->getName() !== $classReflection->getName()) {
                continue;
            }

            if (!$constant->hasType()) {
                $declaringClass = $constant->getDeclaringClass();
                $line = $declaringClass->getStartLine();

                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'Class constant %s::%s is missing a type declaration.',
                        $classReflection->getName(),
                        $constant->getName()
                    )
                )->identifier('class.missingConstantType')
                ->line($line)
                ->build();
            }
        }

        return $errors;
    }
}
