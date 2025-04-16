<?php

declare(strict_types=1);

namespace Drupal\hello_login\OpenIDProviderCommands;

use HelloCoop\Type\Command\CommandClaims;
use HelloCoop\Type\Command\Command as CommandEnum;
use HelloCoop\Utils\PackageMetadata;
use HelloCoop\Type\Command\MetadataResponse;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Config\ConfigInterface;

class CommandHandler implements CommandHandlerInterface
{
    private ConfigInterface $config;

    private HelloResponseInterface $helloResponse;

    /**
     * Constructs a CommandHandler object.
     *
     * @param HelloCoop\Config\ConfigInterface $config
     *   The HelloConfig.
     * @param HelloCoop\HelloResponse\HelloResponseInterface $helloResponse
     *   The http respose service
     */
    public function __construct(ConfigInterface $config, HelloResponseInterface $helloResponse) {
        $this->config = $config;
        $this->helloResponse = $helloResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function handleCommand(CommandClaims $commandClaims): string
    {
        if ($commandClaims->command === CommandEnum::METADATA) {
            return $this->handleMetadata($commandClaims);
        }
    }

    private function handleMetadata(CommandClaims $claims): string
    {
        $metadata = PackageMetadata::getMetadata();

        $metadataResponse = new MetadataResponse(
            context: [
                'package_name' => $metadata['name'],
                'package_version' => $metadata['version'],
                'iss' => $claims->iss,
                'tenant' => $claims->tenant ?? null,
            ],
            commands_uri: $this->config->getRedirectURI() ?? 'unknown',
            commands_supported: [CommandEnum::METADATA],
            commands_ttl: 0,
            client_id: $this->config->getClientId() ?? 'unknown'
        );

        return $this->helloResponse->json($metadataResponse->toArray());
    }
}
