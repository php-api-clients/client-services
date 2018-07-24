<?php declare(strict_types=1);

namespace ApiClients\Tools\Services\Client;

use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Transport\ParsedContentsInterface;
use ApiClients\Foundation\Transport\Service\RequestService;
use Psr\Http\Message\ResponseInterface;
use React\Promise\CancellablePromiseInterface;
use RingCentral\Psr7\Request;
use function igorw\get_in;

class FetchAndHydrateService
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
     * @param Hydrator       $hydrator
     */
    public function __construct(RequestService $requestService, Hydrator $hydrator)
    {
        $this->requestService = $requestService;
        $this->hydrator = $hydrator;
    }

    /**
     * @param  string|null                 $path
     * @param  string|null                 $index
     * @param  string|null                 $hydrateClass
     * @param  array                       $options
     * @return CancellablePromiseInterface
     */
    public function fetch(
        string $path = null,
        string $index = null,
        string $hydrateClass = null,
        array $options = []
    ): CancellablePromiseInterface {
        return $this->requestService->request(
            new Request('GET', $path),
            $options
        )->then(function (ResponseInterface $response) use ($hydrateClass, $index) {
            $parsedContents = [];
            $body = $response->getBody();
            if ($body instanceof ParsedContentsInterface) {
                $parsedContents = $body->getParsedContents();
            }

            if ($index !== '') {
                $parsedContents = get_in($parsedContents, explode('.', $index), []);
            }

            return $this->hydrator->hydrate(
                $hydrateClass,
                $parsedContents
            );
        });
    }
}
