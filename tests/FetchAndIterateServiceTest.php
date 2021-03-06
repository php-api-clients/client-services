<?php declare(strict_types=1);

namespace ApiClients\Tests\Client\Travis\Service;

use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Resource\ResourceInterface;
use ApiClients\Foundation\Transport\ClientInterface;
use ApiClients\Foundation\Transport\Service\RequestService;
use ApiClients\Middleware\Json\JsonStream;
use ApiClients\Tools\Services\Client\FetchAndIterateService;
use ApiClients\Tools\TestUtilities\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use RingCentral\Psr7\Response;
use function React\Promise\resolve;

final class FetchAndIterateServiceTest extends TestCase
{
    public function jsonProvider()
    {
        yield [
            [
                [
                    'slug' => 'slak',
                ],
                [
                    'slug' => 'vis',
                ],
                [
                    'slug' => 'beer',
                ],
            ],
            [
                [
                    'slug' => 'slak',
                ],
                [
                    'slug' => 'vis',
                ],
                [
                    'slug' => 'beer',
                ],
            ],
            '',
        ];

        yield [
            [
                'repos' => [
                    [
                        'slug' => 'slak',
                    ],
                    [
                        'slug' => 'vis',
                    ],
                    [
                        'slug' => 'beer',
                    ],
                ],
            ],
            [
                [
                    'slug' => 'slak',
                ],
                [
                    'slug' => 'vis',
                ],
                [
                    'slug' => 'beer',
                ],
            ],
            'repos',
        ];

        yield [
            [
                'nested' => [
                    'repos' => [
                        [
                            'slug' => 'slak',
                        ],
                        [
                            'slug' => 'vis',
                        ],
                        [
                            'slug' => 'beer',
                        ],
                    ],
                ],
            ],
            [
                [
                    'slug' => 'slak',
                ],
                [
                    'slug' => 'vis',
                ],
                [
                    'slug' => 'beer',
                ],
            ],
            'nested.repos',
        ];

        yield [
            [
                'repos' => [
                    [
                        'slug' => 'slak',
                    ],
                    [
                        'slug' => 'vis',
                    ],
                    [
                        'slug' => 'beer',
                    ],
                ],
            ],
            [],
            'nested',
            true,
        ];
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testIterate(array $inputJson, array $expectedOutputJsons, string $arrayPath, bool $subscribeCallbackCalled = false)
    {
        $repositoryResource = $this->prophesize(ResourceInterface::class)->reveal();

        $client = $this->prophesize(ClientInterface::class);
        $client->request(
            Argument::type(RequestInterface::class),
            Argument::type('array')
        )->shouldBeCalled()->willReturn(resolve(
            new Response(
                200,
                [],
                new JsonStream($inputJson)
            )
        ));

        $requestService = new RequestService($client->reveal());

        $hydrator = $this->prophesize(Hydrator::class);
        foreach ($expectedOutputJsons as $expectedOutputJson) {
            $hydrator->hydrate(
                Argument::exact('Resource'),
                Argument::exact($expectedOutputJson)
            )->shouldBeCalled()->willReturn($repositoryResource);
        }

        $service = new FetchAndIterateService($requestService, $hydrator->reveal());
        $service->iterate('repos', $arrayPath, 'Resource')->subscribeCallback(function ($resource) use ($repositoryResource, &$subscribeCallbackCalled) {
            self::assertSame($repositoryResource, $resource);
            $subscribeCallbackCalled = true;
        });

        self::assertTrue($subscribeCallbackCalled);
    }
}
