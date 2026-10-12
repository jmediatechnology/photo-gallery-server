<?php

namespace App\Presentation\Resolver\Photograph;

use App\Presentation\DTO\Photograph\DownloadInputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DownloadInputDTOResolver implements ValueResolverInterface
{
    private const string SEPARATOR = ',';

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== DownloadInputDTO::class) {
            return [];
        }

        yield new DownloadInputDTO(
            uuids: $this->resolveUuids($request),
        );
    }

    /**
     * Turns "?uuids=B,a, ,A" into ['b', 'a']: trimmed, lowercased, without empty values or duplicates.
     *
     * @return array<string>
     */
    private function resolveUuids(Request $request): array
    {
        $uuids = array_map(
            callback: static fn (string $id): string => strtolower(trim($id)),
            array: explode(self::SEPARATOR, $request->query->getString('uuids')),
        );

        $nonEmptyUuids = array_filter(
            array: $uuids,
            callback: static fn (string $uuid): bool => $uuid !== '',
        );

        return array_values(array_unique($nonEmptyUuids));
    }
}
