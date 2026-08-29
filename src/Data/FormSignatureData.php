<?php

namespace VenueFamily\Data;

class FormSignatureData
{
    public function __construct(
        public readonly int $id,
        public readonly string $token,
        public readonly string $status, // 'pending', 'signed', 'countersigned', 'cancelled', 'expired'
        public readonly string $mechanism = 'native', // 'native' or 'certified'
        public readonly ?string $signerName = null,
        public readonly ?string $signerEmail = null,
        public readonly ?string $signerType = null,
        public readonly ?string $signedAt = null,
        public readonly ?string $signingUrl = null,
        public readonly array $raw = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            token: $data['token'] ?? '',
            status: $data['status'] ?? 'pending',
            mechanism: $data['mechanism'] ?? 'native',
            signerName: $data['name'] ?? $data['signer_name'] ?? null,
            signerEmail: $data['email'] ?? $data['signer_email'] ?? null,
            signerType: $data['signer_type'] ?? null,
            signedAt: $data['signed_at'] ?? null,
            signingUrl: $data['signing_url'] ?? null,
            raw: $data
        );
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSigned(): bool
    {
        return in_array($this->status, ['signed', 'countersigned'], true);
    }

    public function isCertified(): bool
    {
        return $this->mechanism === 'certified';
    }

    public function isNative(): bool
    {
        return $this->mechanism === 'native';
    }

    public function toArray(): array
    {
        return $this->raw;
    }
}
