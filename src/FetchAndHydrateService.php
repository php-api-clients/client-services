<?php declare(strict_types=1);

namespace ApiClients\Tools\Services\Client;

use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Service\ServiceInterface;
use ApiClients\Foundation\Transport\Service\RequestService;
use Psr\Http\Message\ResponseInterface;
use React\Promise\CancellablePromiseInterface;
use RingCentral\Psr7\Request;
use Rx\Observable;
use function igorw\get_in;
use function React\Promise\resolve;
use function WyriHaximus\React\futureFunctionPromise;

class FetchAndHydrateService implements ServiceInterface
{
    /**
     * @var RequestService
     */
    private $requestService;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @param RequestService $requestService
     * @param Hydrator $hydrator
     */
    public function __construct(RequestService $requestService, Hydrator $hydrator)
    {
        $this->requestService = $requestService;
        $this->hydrator = $hydrator;
    }

    /**
     * @param string|null $path
     * @param string|null $index
     * @param string|null $hydrateClass
     * @param array $options
     * @return CancellablePromiseInterface
     */
    public function handle(
        string $path = null,
        string $index = null,
        string $hydrateClass = null,
        array $options = []
    ): CancellablePromiseInterface {
        return $this->requestService->handle(
            new Request('GET', $path),
            $options
        )->then(function (ResponseInterface $response) use ($hydrateClass, $index) {
            $json = $response->getBody()->getJson();

            if ($index !== '') {
                $json = get_in($json, explode('.', $index), []);
            }

            return $this->hydrator->hydrate(
                $hydrateClass,
                $json
            );
        });
    }
}
