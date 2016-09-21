<?php

namespace Spatie\MediaLibrary\ImageGenerators;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\ImageGenerators\ImageGenerator;

abstract class BaseGenerator implements ImageGenerator
{
    public function canConvert(string $path): bool
    {
        if (! file_exists($path)) {
            return false;
        }

        if (! $this->requirementsAreInstalled()) {
            return false;
        }

        if ($this->supportedExtensions()->contains(pathinfo($path, PATHINFO_EXTENSION))) {
            return true;
        }

        if ($this->supportedMimetypes()->contains(File::getMimetype($path))) {
            return true;
        }

        return false;
    }

    public function canHandleMime(string $mime = ''): bool
    {
        return $this->supportedMimetypes()->contains($mime);
    }

    public function canHandleExtension(string $extension = ''): bool
    {
        return $this->supportedExtensions()->contains($extension);
    }

    public function getType(): string
    {
        return strtolower(class_basename(static::class));
    }

    public abstract function requirementsAreInstalled(): bool;

    public abstract function supportedExtensions(): Collection;

    public abstract function supportedMimetypes(): Collection;
}
