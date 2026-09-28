<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Commands\Photograph\UpdateCommand;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use App\Infrastructure\Symfony\Validator\Photograph\UpdateCommandConstraintValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


final class UpdateCommandConstraintValidatorTest extends TestCase
{
    #[Test]
    public function throwsUnexpectedTypeExceptionWhenValueIsNotInstanceOfCreatePhotograph(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $photographRepository = $this->createStub(PhotographRepository::class);

        $value = '';
        $constraint = $this->createStub(Constraint::class);
        new UpdateCommandConstraintValidator($photographRepository)->validate($value, $constraint);
    }

    #[Test]
    public function throwsUnexpectedTypeExceptionWhenConstraintIsNotInstanceOfCreatePhotographConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $photographRepository = $this->createStub(PhotographRepository::class);

        $value = $this->createStub(UpdateCommand::class);
        $constraint = $this->createStub(Constraint::class);
        new UpdateCommandConstraintValidator($photographRepository)->validate($value, $constraint);
    }
}
