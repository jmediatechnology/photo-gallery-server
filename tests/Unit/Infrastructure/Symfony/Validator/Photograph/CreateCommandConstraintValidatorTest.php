<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Commands\Photograph\CreateCommand;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use App\Infrastructure\Symfony\Validator\Photograph\CreateCommandConstraintValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


final class CreateCommandConstraintValidatorTest extends TestCase
{
    #[Test]
    public function throwsUnexpectedTypeExceptionWhenValueIsNotInstanceOfCreatePhotograph(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $photographRepository = $this->createStub(PhotographRepository::class);

        $value = '';
        $constraint = $this->createStub(Constraint::class);
        new CreateCommandConstraintValidator($photographRepository)->validate($value, $constraint);
    }

    #[Test]
    public function throwsUnexpectedTypeExceptionWhenConstraintIsNotInstanceOfCreatePhotographConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $photographRepository = $this->createStub(PhotographRepository::class);

        $value = $this->createStub(CreateCommand::class);
        $constraint = $this->createStub(Constraint::class);
        new CreateCommandConstraintValidator($photographRepository)->validate($value, $constraint);
    }
}
