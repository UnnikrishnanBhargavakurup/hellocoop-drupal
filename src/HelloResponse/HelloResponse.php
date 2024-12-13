<?php

namespace Drupal\hellocoop\HelloResponse;

use HelloCoop\HelloResponse\HelloResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class HelloResponse implements HelloResponseInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * Constructor.
     *
     * Initializes the response object.
     */
    public function __construct()
    {
        $this->response = new Response();
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader(string $name, $value): void
    {
        if (is_array($value)) {
            $value = implode(", ", $value);
        }
        $this->response->headers->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCookie(string $name, string $path = '/', string $domain = ''): void
    {
        $cookie = new Cookie($name, '', time() - 3600, $path, $domain, false, true, false, 'Lax');
        $this->response->headers->setCookie($cookie);
    }

    /**
     * {@inheritdoc}
     */
    public function setCookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = true
    ): void {
        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httponly, false, 'Lax');
        $this->response->headers->setCookie($cookie);
    }

    /**
     * {@inheritdoc}
     */
    public function redirect(string $url): void
    {
        $this->response->headers->set('Location', $url);
        $this->response->setStatusCode(Response::HTTP_FOUND);
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $content): string
    {
        $this->response->setContent($content);
        $this->response->headers->set('Content-Type', 'text/plain');
        return $this->response->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function json(array $data): string
    {
        $this->response->setContent(json_encode($data));
        $this->response->headers->set('Content-Type', 'application/json');
        return $this->response->getContent();
    }
}
