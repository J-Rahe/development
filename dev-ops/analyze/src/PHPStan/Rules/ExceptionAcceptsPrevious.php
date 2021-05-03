<?php declare(strict_types=1);

namespace Shopware\Development\Analyze\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class ExceptionAcceptsPrevious implements Rule
{
    private const expectedConstructorParameterName = 'previous';

    public function getNodeType(): string
    {
        return Node\Stmt\Class_::class;
    }

    /**
     * @param Node\Stmt\Class_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $currentClass = $scope->getNamespace() . '\\' . $node->name;

        try {
            $reflectionClass = new \ReflectionClass($currentClass);
        } catch (\ReflectionException $e) {
            return [
            ];
        }

        if ($reflectionClass->isAbstract()) {
            return [];
        }
        $classObj = $reflectionClass->newInstanceWithoutConstructor();

        // Isn't an exception
        if (!$classObj instanceof \Throwable) {
            return [];
        }

        //getConstructor will return the parents if the class has none
        if (!$reflectionClass->getConstructor()) {
            return [
                sprintf(
                    "Exception '%s' has no construction parameters, expected '%s''.",
                    $node->name,
                    self::expectedConstructorParameterName
                ),
            ];
        }

        //If the constructor is private we dont care of you cant give him an throwable
        if($reflectionClass->getConstructor()->isPrivate()){
            return [];
        }

        $parameters = $reflectionClass->getConstructor()->getParameters();

        $parameterTypeString = 'none';
        /**
         * @param $parameter \ReflectionParameter
         */
        foreach ($parameters as $parameter) {
            if ($parameter->getName() === self::expectedConstructorParameterName) {
                if ($parameter->hasType()) {
                    if ((string) $parameter->getType() === 'Throwable') {
                        //everything is fine, return no error
                        return [];
                    }
                    if ($parameter->getType() instanceof \Throwable){
                        //is an subset of throwable
                        return [];
                    }
                    $parameterTypeString = $parameter->getType();
                }

                return [
                    sprintf(
                        "Exception '%s' has parameter '%s' of wrong type, expected Throwable, got %s.",
                        $node->name,
                        self::expectedConstructorParameterName,
                        $parameterTypeString,
                    ),
                ];
            }
        }

        return [
            sprintf(
                "Exception '%s' is not accepting parameter '%s'.",
                $node->name,
                self::expectedConstructorParameterName
            ),
        ];
    }
}
