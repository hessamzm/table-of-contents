<?php
declare(strict_types=1);

namespace Hessamzm\TableOfContents\TOC;

defined('ABSPATH') || exit;

final class Heading
{
    public function __construct(
        private readonly int $level,
        private readonly string $text,
        private readonly string $html,
        private readonly ?string $existingId = null,
        private ?string $id = null,
    ) {
    }

    public function level(): int
    {
        return $this->level;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function html(): string
    {
        return $this->html;
    }

    public function existingId(): ?string
    {
        return $this->existingId;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function withId(string $id): self
    {
        $copy = clone $this;
        $copy->id = $id;

        return $copy;
    }
}
