<?php

use Alisa\Alisa;
use Alisa\Component;
use Alisa\Context;

require __DIR__ . '/../vendor/autoload.php';

class VersionComponent extends Component
{
    public function register()
    {
        $this->alisa->onCommand('версия', function (Context $context) {
            dump('Версия навыка: ' . $this->args['version']);
        });
    }
}

$alisa = new Alisa([
    'request' => json_decode(file_get_contents(__DIR__ . '/requests/components.json'), true),

    'components' => [
        VersionComponent::class => [
            'version' => '1.0.0',
        ],
    ],
]);

$alisa->run();