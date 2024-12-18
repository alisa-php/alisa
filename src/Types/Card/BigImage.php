<?php

namespace Alisa\Types\Card;

use Alisa\Stores\Asset;

/**
 * @see https://yandex.ru/dev/dialogs/alice/doc/ru/response-card-bigimage
 * @see https://yandex.ru/dev/dialogs/alice/doc/ru/interface#card
 */
class BigImage extends AbstractCard
{
    protected array $card = [
        'type' => 'BigImage',
        'image_id' => null,
        'title' => null,
        'description' => null,
        'button' => [],
    ];

    public function __construct(string $imageId, ?string $title = null, ?string $description = null, ?Button $button = null)
    {
        if ($button) {
            $this->button($button);
        }

        $this
            ->image($imageId)
            ->title($title)
            ->description($description);
    }

    public function image(string $imageId): static
    {
        $this->card['image_id'] = Asset::get($imageId) ?? $imageId;

        return $this;
    }

    public function title(?string $title = null): static
    {
        $this->card['title'] = $title;

        return $this;
    }

    public function description(?string $description = null): static
    {
        $this->card['description'] = $description;

        return $this;
    }

    public function button(Button $button): static
    {
        $this->card['button'] = $button->toArray();

        return $this;
    }
}