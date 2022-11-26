<?php
// api/src/OpenApi/JwtDecorator.php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;

final class JwtDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
                'refreshToken' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ]);
        $schemas['RefreshToken'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'refresh_token' => [
                    'type' => 'string',
                    'example' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2Njk0NjM3ODEsImV4cCI6MTY2OTQ2NzM4MSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoiam9obmRvZSJ9.H4iVUDUJvpC3O-YgXWEte0BL_qLL4NvaJ6F9npRYW4ycIf2TGTvgOxeFIHqkTJhG172Wmz7-g9bPV9HfCwoAwqAPaWlaN4ljnmVO2WLMLUhy56cMWsEIk9QAkol0JAkatTCXU3HbcnKEbE4R-c2jwN7xaFQSFJq3Jh271gNuecBe1QqZ1pn0pbm-3T31f5_HAuBSjp5X300laU-hIXO6ajfL0ZmhdJrEvUtUo2Akh6aZ9V2DkW6p8WSrDceY-R_gAgG3Y_Ek8_RGMqt0QcHJt_tDLx6pBiKOJhp_rpgIjeHItav4p-ybmzDDmdOn594VraO83x0wVDXSeA6rowdY5A',
                ],
            ],
        ]);
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                    'example' => 'johndoe',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'apassword',
                ],
            ],
        ]);
        $schemas['registerCredentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                    'example' => 'johndoe',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'apassword',
                ],
                'email' => [
                    'type' => 'string',
                    'example' => 'johndoe@email.com',
                ],
            ],
        ]);

        $schemas = $openApi->getComponents()->getSecuritySchemes() ?? [];
        $schemas['JWT'] = new \ArrayObject([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ]);

        $login = new Model\PathItem(
            ref: 'JWT Token',
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['Authentication'],
                responses: [
                    '200' => [
                        'description' => 'Get JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token',
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Get JWT token to login.',
                requestBody: new Model\RequestBody(
                    description: 'Generate new JWT Token',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials',
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );

        $register =
            new Model\PathItem(
                ref: 'Register',
                post: new Model\Operation(
                    tags: ['Authentication'],
                    responses: [
                        '200' => [
                            'description' => 'Register new user',

                        ],
                    ],
                    summary: 'Register new user.',
                    requestBody: new Model\RequestBody(
                        description: 'Register new user',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/registerCredentials',
                                ],
                            ],
                        ]),
                    ),
                    security: [],
                ),
            );



        $refreshToken =
            new Model\PathItem(
                ref: 'Refresh',
                post: new Model\Operation(
                    tags: ['Authentication'],
                    responses: [
                        '200' => [
                            'description' => 'Refresh JWT token',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/Token',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    summary: 'Refresh JWT token.',
                    requestBody: new Model\RequestBody(
                        description: 'Refresh JWT token',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/RefreshToken',
                                ],
                            ],
                        ]),
                    ),
                    security: [],
                ),
            );

        $openApi->getPaths()->addPath('/api/login', $login);
        $openApi->getPaths()->addPath('/api/register', $register);
        $openApi->getPaths()->addPath('/api/auth/refresh', $refreshToken);

        return $openApi;
    }
}
