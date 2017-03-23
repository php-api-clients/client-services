<?php declare(strict_types=1);

namespace ApiClients\Tools\Services\Client;

use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Transport\Service\RequestService;
use React\Promise\CancellablePromiseInterface;
use RingCentral\Psr7\Request;
use Rx\Observable;
use Rx\React\Promise;
use function igorw\get_in;

class FetchAndIterateService
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
    public function iterate(
        string $path = null,
        string $index = null,
        string $hydrateClass = null,
        array $options = []
    ): Observable {
        return Promise::toObservable(
            $this->requestService->handle(
                new Request('GET', $path),
                $options
            )
        )->flatMap(function ($response) use ($index) {
            $json = $response->getBody()->getJson();

            if ($index === '') {
                return Observable::fromArray($json);
            }

            return Observable::fromArray(get_in($json, explode('.', $index), []));
        })->map(function ($json) use ($hydrateClass) {
            return $this->hydrator->hydrate(
                $hydrateClass,
                $json
            );
        });
    }
}
