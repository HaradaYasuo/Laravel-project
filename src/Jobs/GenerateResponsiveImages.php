<?php

namespace Spatie\MediaLibrary\Jobs;

use Illuminate\Bus\Queueable;
use Spatie\MediaLibrary\Media;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\MediaLibrary\FileManipulator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImageGenerator;

class GenerateResponsiveImages implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    /** @var \Spatie\MediaLibrary\Conversion\ConversionCollection */
    protected $conversions;

    /** @var \Spatie\MediaLibrary\Media */
    protected $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function handle(): bool
    {
        app(ResponsiveImageGenerator::class)->generateResponsiveImages($this->media);

        return true;
    }
}
