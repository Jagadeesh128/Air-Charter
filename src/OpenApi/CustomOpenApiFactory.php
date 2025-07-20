<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Contact;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\License;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;

final class CustomOpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        // ℹ️ Update API metadata
        $info = new Info(
            title: '✈️ Air Charter Booking API',
            version: '1.0.0',
            description: 'Flight Booking Back-End',
            contact: new Contact('Support', 'https://yourcompany.com', 'support@yourcompany.com'),
            license: new License('MIT', 'https://opensource.org/licenses/MIT')
        );

        $openApi = $openApi->withInfo($info);
        $paths = $openApi->getPaths();

        // 🔁 Refresh Token Endpoint (no bearer auth needed)
        $refreshTokenOperation = (new Operation(
            operationId: 'postRefreshToken',
            tags: ['Authentication'],
            summary: 'Refresh JWT access token using a refresh token',
            requestBody: new RequestBody(
                description: 'Send your refresh token to receive a new access token',
                required: true,
                content: new ArrayObject([
                    'application/json' => new ArrayObject([
                        'schema' => new ArrayObject([
                            'type' => 'object',
                            'properties' => new ArrayObject([
                                'refresh_token' => ['type' => 'string'],
                            ]),
                            'required' => ['refresh_token'],
                        ]),
                    ]),
                ])
            ),
            responses: [
                '200' => new Response(description: 'Returns new access token'),
                '400' => new Response(description: 'Invalid or missing refresh token'),
                '401' => new Response(description: 'Unauthorized or expired refresh token'),
            ]
        ))->withSecurity([]); // Disable bearer auth

        $paths->addPath('/api/token/refresh', new PathItem(post: $refreshTokenOperation));

        // 🔒 Logout Endpoint (no bearer auth needed)
        $logoutOperation = (new Operation(
            operationId: 'postLogout',
            tags: ['Authentication'],
            summary: 'Logout and invalidate refresh token',
            requestBody: new RequestBody(
                description: 'Send your refresh token to revoke access',
                required: true,
                content: new ArrayObject([
                    'application/json' => new ArrayObject([
                        'schema' => new ArrayObject([
                            'type' => 'object',
                            'properties' => new ArrayObject([
                                'refresh_token' => ['type' => 'string'],
                            ]),
                            'required' => ['refresh_token'],
                        ]),
                    ]),
                ])
            ),
            responses: [
                '200' => new Response(description: 'Successfully logged out'),
                '400' => new Response(description: 'Invalid or missing refresh token'),
            ]
        ))->withSecurity([]); // Disable bearer auth

        $paths->addPath('/api/logout', new PathItem(post: $logoutOperation));

        return $openApi->withPaths($paths);
    }
}
