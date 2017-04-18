<?php declare(strict_types=1);

namespace ApiClients\Tests\Client\Travis\Service;

use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Resource\ResourceInterface;
use ApiClients\Foundation\Transport\ClientInterface;
use ApiClients\Foundation\Transport\Service\RequestService;
use ApiClients\Middleware\Json\JsonStream;
use ApiClients\Tools\Services\Client\FetchAndHydrateService;
use ApiClients\Tools\TestUtilities\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use React\EventLoop\Factory;
use RingCentral\Psr7\Response;
use function Clue\React\Block\await;
use function React\Promise\resolve;

final class FetchAndHydrateServiceTest extends TestCase
{
    public function jsonProvider()
    {
        yield [
            [
                'repo' => [
                    'slug' => 'slak',
                ],
            ],
            [
                'repo' => [
                    'slug' => 'slak',
                ],
            ],
            '',
        ];

        yield [
            [
                'repo' => [
                    'slug' => 'slak',
                ],
            ],
            [
                'slug' => 'slak',
            ],
            'repo',
        ];

        yield [
            [
                'nested' => [
                    'repo' => [
                        'slug' => 'slak',
                    ],
                ],
            ],
            [
                'slug' => 'slak',
            ],
            'nested.repo',
        ];

        yield [
            [
                'repo' => [
                    'slug' => 'slak',
                ],
            ],
            [],
            'nested',
        ];
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testFetch(array $inputJson, array $expectedOutputJson, string $arrayPath)
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
        $hydrator->hydrate(
            Argument::exact('Resource'),
            Argument::exact($expectedOutputJson)
        )->shouldBeCalled()->willReturn($repositoryResource);

        $service = new FetchAndHydrateService($requestService, $hydrator->reveal());
        $resource = await(
            $service->fetch('repo', $arrayPath, 'Resource'),
            Factory::create()
        );

        self::assertSame($repositoryResource, $resource);
    }
}
