<?php

namespace VenueFamily\Data;

class MediaData
{
    /**
     * @param  MediaSlotData[]  $slots
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $url,
        public readonly string $type = 'image',
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $alt = null,
        public readonly ?string $mimeType = null,
        public readonly ?string $extension = null,
        public readonly ?int $size = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?float $aspectRatio = null,
        public readonly ?string $aspectRatioLabel = null,
        public readonly bool $isPrimary = false,
        public readonly ?string $slot = null,
        public readonly array $slots = [],
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        $slots = [];
        if (! empty($data['slots']) && is_array($data['slots'])) {
            foreach ($data['slots'] as $slotItem) {
                $slots[] = MediaSlotData::fromArray($slotItem);
            }
        } elseif (! empty($data['slot'])) {
            $slots[] = MediaSlotData::fromArray($data['slot']);
        }

        $primary = (bool) ($data['primary'] ?? $data['is_primary'] ?? false);
        $slot = $data['slot'] ?? (! empty($slots) ? $slots[0]->slot : null);

        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            url: (string) ($data['url'] ?? ''),
            type: (string) ($data['type'] ?? 'image'),
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            alt: $data['alt'] ?? null,
            mimeType: $data['mime_type'] ?? null,
            extension: $data['extension'] ?? null,
            size: isset($data['size']) ? (int) $data['size'] : null,
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            aspectRatio: isset($data['aspect_ratio']) ? (float) $data['aspect_ratio'] : null,
            aspectRatioLabel: $data['aspect_ratio_label'] ?? null,
            isPrimary: $primary,
            slot: $slot,
            slots: $slots,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            raw: $data
        );
    }

    /**
     * Get all slot names this media item belongs to.
     *
     * @return string[]
     */
    public function slotNames(): array
    {
        return array_map(fn (MediaSlotData $s) => $s->slot, $this->slots);
    }

    public function hasSlot(string $slot): bool
    {
        return in_array($slot, $this->slotNames(), true);
    }

    public function isBanner(): bool
    {
        return $this->isPrimary || $this->hasSlot('banner') || $this->slot === 'banner';
    }

    public function isPage(): bool
    {
        return $this->hasSlot('page') || $this->slot === 'page';
    }

    public function isList(): bool
    {
        return $this->hasSlot('list') || $this->slot === 'list';
    }

    public function isGallery(): bool
    {
        return $this->hasSlot('gallery') || $this->slot === 'gallery';
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
