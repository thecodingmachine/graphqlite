<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use function sprintf;

/**
 * An expression language provider that adds functions used in the context of the "Security" annotation.
 */
class SecurityExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('is_granted', static function (string $rightName, $object = 'null'): string {
                return sprintf('$authorizationService->isAllowed(%s, %s)', $rightName, $object);
            }, static function (array $variables, string $rightName, $object = null): bool {
                return $variables['authorizationService']->isAllowed($rightName, $object);
            }),

            new ExpressionFunction('is_logged', static function (): string {
                return sprintf('$authenticationService->isLogged()');
            }, static function (array $variables): bool {
                return $variables['authenticationService']->isLogged();
            }),
        ];
    }
}
