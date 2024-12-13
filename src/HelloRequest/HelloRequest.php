<?php

namespace Drupal\hellocoop\HelloRequest;

use HelloCoop\HelloRequest\HelloRequestInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class HelloRequest implements HelloRequestInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    protected $currentRequest;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     *   The request stack service.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->currentRequest = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $key, $default = null): ?string
    {
        return $this->currentRequest->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchMultiple(array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->currentRequest->get($key, null);
        }
        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchHeader(string $key, $default = null): ?string
    {
        return $this->currentRequest->headers->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie(string $name): ?string
    {
        return $this->currentRequest->cookies->get($name, null);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestUri(): string
    {
        return $this->currentRequest->getRequestUri();
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->currentRequest->getMethod();
    }
}
