<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Resolver\Photograph;

use App\Presentation\DTO\Photograph\DownloadInputDTO;
use App\Presentation\DTO\Photograph\GetAllInputDTO;
use App\Presentation\Resolver\Photograph\DownloadInputDTOResolver;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class DownloadInputDTOResolverTest extends TestCase
{
    #[Test]
    public function ignoresArgumentsOfOtherTypes(): void
    {
        $request = Request::create(uri: '/');

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(null);

        $actual = new DownloadInputDTOResolver()->resolve($request, $argument);

        self::assertSame([], iterator_to_array($actual));
    }

    /**
     * @param array<string, string> $query
     * @param array<string> $expectedUuids
     */
    #[Test]
    #[DataProvider('provideUuids')]
    public function resolvesTheUuidsQueryParameter(array $query, array $expectedUuids): void
    {
        $request = Request::create(uri: '/photographs/download', parameters: $query);

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(DownloadInputDTO::class);

        /** @var $iterable Generator */
        $iterable = new DownloadInputDTOResolver()->resolve($request, $argument);
        $dto = $iterable->current();

        self::assertInstanceOf(DownloadInputDTO::class, $dto);
        self::assertSame($expectedUuids, $dto->uuids());
    }

    /**
     * @return array<string, array{array<string, string>, list<string>}>
     */
    public static function provideUuids(): array
    {
        return [
            'missing' => [[], []],
            'only separators and spaces' => [['uuids' => ' , ,'], []],
            'comma separated' => [['uuids' => 'a,b'], ['a', 'b']],
            'trimmed and lowercased' => [['uuids' => ' A , B '], ['a', 'b']],
            'duplicates removed, first occurrence kept' => [['uuids' => 'b,a,B'], ['b', 'a']],
        ];
    }
}
