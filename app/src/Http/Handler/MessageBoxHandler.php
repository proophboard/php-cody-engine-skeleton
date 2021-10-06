<?php

declare(strict_types=1);

namespace Acme\Http\Handler;

use Acme\Exception\InvalidArgumentException;
use Acme\Exception\RuntimeException;
use EventEngine\Messaging\CommandDispatchResult;
use EventEngine\Messaging\MessageDispatcher;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Prooph\EventStore\Exception\ConcurrencyException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function is_array;
use function is_object;
use function method_exists;
use function var_export;

use const PHP_EOL;

/**
 * One middleware for all commands and events
 */
final class MessageBoxHandler implements RequestHandlerInterface
{
    /** @var MessageDispatcher */
    private $messageDispatcher;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(MessageDispatcher $messageDispatcher, LoggerInterface $logger)
    {
        $this->messageDispatcher = $messageDispatcher;
        $this->logger            = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $payload     = null;
        $messageName = 'UNKNOWN';
        $metadata    = [];

        try {
            $payload = $request->getParsedBody();

            $messageName = $request->getAttribute('message_name', $messageName);

            if (is_array($payload) && isset($payload['message_name'])) {
                $messageName = $payload['message_name'];
                $metadata    = $payload['metadata'] ?? [];
                $payload     = $payload['payload'] ?? [];
            }

            $result = $this->messageDispatcher->dispatch($messageName, $payload, $metadata);

            if ($result === null || $result instanceof CommandDispatchResult) {
                return new EmptyResponse(StatusCodeInterface::STATUS_ACCEPTED);
            }

            if (is_object($result) && method_exists($result, 'toArray')) {
                $result = $result->toArray();
            }

            return new JsonResponse($result);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(
                $e->getMessage(),
                StatusCodeInterface::STATUS_BAD_REQUEST,
                $e
            );
        } catch (Throwable $e) {
            return $this->handleError($e, ['message_name' => $messageName, 'payload' => $payload, 'metadata' => $metadata]);
        }
    }

    private function handleError(Throwable $e, array $messageData): ResponseInterface
    {
        $this->logger->error($e);
        $this->logger->error('Message Data was:' . PHP_EOL . var_export($messageData, true));

        $errorCode = 500;
        $msg       = "Internal Server Error";

        if ($e->getCode() >= 400 && $e->getCode() < 500) {
            $errorCode = $e->getCode();
            $msg       = $e->getMessage();
        }

        if ($e instanceof ConcurrencyException) {
            $e = $e->getPrevious();

            $errorCode = StatusCodeInterface::STATUS_CONFLICT;
            $msg       = 'Concurrency error. Please retry!';
        }

        throw new RuntimeException($msg, $errorCode, $e);
    }
}
