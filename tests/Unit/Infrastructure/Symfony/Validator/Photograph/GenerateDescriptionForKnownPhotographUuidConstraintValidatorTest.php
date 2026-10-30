<?php

namespace App\Tests\Unit\Infrastructure\Symfony\Validator\Photograph;

use App\Application\Query\Photograph\GenerateDescriptionForKnownPhotographUuidQuery;
use App\Domain\Entity\Photograph;
use App\Domain\Repository\PhotographRepositoryInterface;
use App\Domain\ValueObject\UUID;
use App\Infrastructure\Doctrine\Repository\PhotographRepository;
use App\Infrastructure\Symfony\Validator\Photograph\GenerateDescriptionForKnownPhotographUuidConstraint;
use App\Infrastructure\Symfony\Validator\Photograph\GenerateDescriptionForKnownPhotographUuidConstraintValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class GenerateDescriptionForKnownPhotographUuidConstraintValidatorTest extends ConstraintValidatorTestCase
{
    private PhotographRepository|MockObject $photographRepository;

    protected function createValidator(): GenerateDescriptionForKnownPhotographUuidConstraintValidator
    {
        $photographRepository = $this->createMock(PhotographRepositoryInterface::class);
        $this->photographRepository = $photographRepository;

        return new GenerateDescriptionForKnownPhotographUuidConstraintValidator($this->photographRepository);
    }

    #[Test]
    public function throwsUnexpectedTypeExceptionWhenValueIsNotTheExpectedQuery(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $photographRepository = $this->photographRepository;
        $photographRepository
            ->expects($this->never())
            ->method('findOneByUUID');

        $this->validator->validate('NOT the specified Query', new GenerateDescriptionForKnownPhotographUuidConstraint());
    }

    #[Test]
    public function throwsUnexpectedValueExceptionWhenConstraintIsNotTheExpectedType(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $photographRepository = $this->photographRepository;
        $photographRepository
            ->expects($this->never())
            ->method('findOneByUUID');

        $value = $this->createStub(GenerateDescriptionForKnownPhotographUuidQuery::class);
        $constraint = $this->createStub(Constraint::class);

        $this->validator->validate($value, $constraint);
    }

    #[Test]
    public function addsViolationWhenPhotographIsNotFound(): void
    {
        $uuid = '247a3d6f-e5ac-4239-bfe6-09e50c1444f9';

        $value = $this->createMock(GenerateDescriptionForKnownPhotographUuidQuery::class);
        $value
            ->expects($this->once())
            ->method('uuid')
            ->willReturn($uuid);

        $photographRepository = $this->photographRepository;
        $photographRepository
            ->expects($this->once())
            ->method('findOneByUUID')
            ->with($this->callback(
                static fn (UUID $valueObject): bool => $valueObject->__toString() === $uuid
            ))
            ->willReturn(null);

        $constraint = new GenerateDescriptionForKnownPhotographUuidConstraint(
            message: 'Uuid is not known.',
        );

        $this->validator->validate($value, $constraint);

        $this
            ->buildViolation($constraint->message)
            ->assertRaised();
    }

    #[Test]
    public function addsNoViolationWhenPhotographIsFound(): void
    {
        $uuid = 'b4375763-3456-4ffe-a1a0-e2069c332aa9';

        $value = $this->createMock(GenerateDescriptionForKnownPhotographUuidQuery::class);
        $value
            ->expects($this->once())
            ->method('uuid')
            ->willReturn($uuid);

        $photographRepository = $this->photographRepository;
        $photographRepository
            ->expects($this->once())
            ->method('findOneByUUID')
            ->with($this->callback(
                static fn (UUID $valueObject): bool => $valueObject->__toString() === $uuid
            ))
            ->willReturn($this->createStub(Photograph::class));

        $constraint = new GenerateDescriptionForKnownPhotographUuidConstraint(
            message: 'Uuid is not known.',
        );

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }
}
