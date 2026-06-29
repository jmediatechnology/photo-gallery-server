<?php

namespace App\Infrastructure\Symfony\Validator\Photograph;

use Symfony\Component\Validator\Constraint;

class GenerateDescriptionForKnownPhotographUuidConstraint extends Constraint
{
    public string $message;

    public function __construct(
        ?array $options = null,
        ?string $message = null,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        if ($message !== null) {
            $this->message = $message;
        }
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
