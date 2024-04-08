<?php

declare(strict_types=1);

namespace Sparklink\EmailErrorsBundle\Twig;

class DataPanel
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_JSON = 'json';
    public const TYPE_STRING = 'string';

    public function __construct(
        protected string $title,
        protected mixed $data,
        protected string $type = self::TYPE_ARRAY
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
