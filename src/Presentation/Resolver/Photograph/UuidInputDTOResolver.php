<?php

namespace App\Presentation\Resolver\Photograph;

use App\Presentation\DTO\Photograph\UuidInputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UuidInputDTOResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UuidInputDTO::class) {
            return [];
        }

        $uuid = $request->attributes->get('id');

        yield new UuidInputDTO(
            uuid: $uuid,
        );
    }
}
