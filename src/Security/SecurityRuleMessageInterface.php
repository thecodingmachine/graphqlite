<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

/**
 * A #[Security] rule that states the message it is refused with.
 *
 * A rule that knows why it refuses is the only place that reason has to be written. Without this,
 * every field guarded by the same rule repeats `message:` in its attribute, and the reason drifts
 * from the check as soon as one of them is edited and the others are not.
 *
 * Implementing this is entirely optional: a rule that does not is denied with the message written
 * in the attribute, or with "Access denied." when the attribute wrote none, exactly as before. A
 * `message:` on the attribute always wins, so a single field can still say something the shared
 * rule cannot know.
 *
 * Only a rule that is an object can carry a message: an array callable names a static method and a
 * first-class callable is a Closure, and neither has an instance to ask. Write the rule as an
 * invokable object, which is also the form that lets its message quote the parameter the rule was
 * constructed with:
 *
 *     final class PageSizeWithin implements SecurityRuleMessageInterface
 *     {
 *         public function __construct(private readonly int $max)
 *         {
 *         }
 *
 *         public function __invoke(SecurityRuleContextInterface $context): bool
 *         {
 *             return $context->argument('first') <= $this->max;
 *         }
 *
 *         public function getRefusalMessage(): string
 *         {
 *             return 'Page size must be at most ' . $this->max . '.';
 *         }
 *     }
 */
interface SecurityRuleMessageInterface
{
    /**
     * The message the field is denied with when this rule refuses.
     *
     * Read only on refusal, and only when the attribute gave no message of its own. It reaches the
     * client, so it should say what a caller is allowed to know and nothing more.
     */
    public function getRefusalMessage(): string;
}
