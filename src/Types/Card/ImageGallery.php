<?php

namespace Alisa\Types\Card;

use Alisa\Stores\Asset;

/**
 * @see https://yandex.ru/dev/dialogs/alice/doc/ru/response-card-imagegallery
 * @see https://yandex.ru/dev/dialogs/alice/doc/ru/interface#images-list
 */
class ImageGallery extends AbstractCard
{
    protected array $card = [
        'type' => 'ImageGallery',
        'items' => [],
    ];

    public function add(string $imageId, string $title, Button|string|null $button = null): static
    {
        $this->card['items'][] = [
            'image_id' => Asset::get($imageId, $imageId),
            'title' => $title,
            'button' => $this->resolveButton($button),
        ];

        return $this;
    }
}