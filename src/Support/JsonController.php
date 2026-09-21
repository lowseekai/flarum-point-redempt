<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Support;

use Flarum\Api\JsonApiResponse;
use Flarum\Http\RequestUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class JsonController implements RequestHandlerInterface
{
    protected function actor(ServerRequestInterface $request)
    {
        return RequestUtil::getActor($request);
    }

    protected function attributes(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();

        return is_array($body) ? ($body['data']['attributes'] ?? $body) : [];
    }

    protected function response(array $data, array $meta = [], int $status = 200): ResponseInterface
    {
        $document = ['data' => $data];
        if ($meta !== []) {
            $document['meta'] = $meta;
        }

        return new JsonApiResponse($document, $status);
    }

    protected function resource(string $type, int|string $id, array $attributes): array
    {
        return ['type' => $type, 'id' => (string) $id, 'attributes' => $attributes];
    }
}
