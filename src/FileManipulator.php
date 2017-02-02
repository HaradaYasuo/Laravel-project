<?php

namespace Spatie\MediaLibrary;

use Illuminate\Contracts\Bus\Dispatcher;
use Spatie\Image\Image;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Jobs\PerformConversions;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\Events\ConversionHasBeenCompleted;
use Spatie\MediaLibrary\Helpers\File as MediaLibraryFileHelper;

class FileManipulator
{
    /**
     * Create all derived files for the given media.
     *
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function createDerivedFiles(Media $media)
    {
        $profileCollection = ConversionCollection::createForMedia($media);

        $this->performConversions(
            $profileCollection->getNonQueuedConversions($media->collection_name),
            $media
        );

        $queuedConversions = $profileCollection->getQueuedConversions($media->collection_name);

        if ($queuedConversions->isNotEmpty()) {
            $this->dispatchQueuedConversions($media, $queuedConversions);
        }
    }

    /**
     * Perform the given conversions for the given media.
     *
     * @param \Spatie\MediaLibrary\Conversion\ConversionCollection $conversions
     * @param \Spatie\MediaLibrary\Media $media
     */
    public function performConversions(ConversionCollection $conversions, Media $media)
    {
        $imageGenerator = $this->determineImageGenerator($media);

        if (! $imageGenerator || $conversions->isEmpty()) {
            return;
        }

        $temporaryDirectory = new TemporaryDirectory(storage_path('medialibrary/temp/'));

        $copiedOriginalFile = app(FilesystemInterface::class)->copyFromMediaLibrary(
            $media,
            $temporaryDirectory->path(str_random(16).'.'.$media->extension)
        );

        foreach ($conversions as $conversion) {
            $copiedOriginalFile = $imageGenerator->convert($copiedOriginalFile, $conversion);

            $conversionResult = $this->performConversion($media, $conversion, $copiedOriginalFile);

            $newFileName = $conversion->getName()
                .'.'
                .$conversion->getResultExtension(pathinfo($copiedOriginalFile, PATHINFO_EXTENSION));

            $renamedFile = MediaLibraryFileHelper::renameInDirectory($conversionResult, $newFileName);

            app(FilesystemInterface::class)->copyToMediaLibrary($renamedFile, $media, true);

            event(new ConversionHasBeenCompleted($media, $conversion));
        }

        $temporaryDirectory->delete();
    }

    public function performConversion(Media $media, Conversion $conversion, string $imageFile): string
    {
        $conversionTempFile = pathinfo($imageFile, PATHINFO_DIRNAME).'/'.string()->random(16)
            .$conversion->getName()
            .'.'
            .$media->extension;

        File::copy($imageFile, $conversionTempFile);

        Image::load($conversionTempFile)
            ->manipulate($conversion->getManipulations())
            ->save();

        return $conversionTempFile;
    }

    protected function dispatchQueuedConversions(Media $media, ConversionCollection $queuedConversions)
    {
        $job = new PerformConversions($queuedConversions, $media);

        if ($customQueue = config('medialibrary.queue_name')) {
            $job->onQueue($customQueue);
        }

        app(Dispatcher::class)->dispatch($job);
    }

    /**
     * @param \Spatie\MediaLibrary\Media $media
     *
     * @return \Spatie\MediaLibrary\ImageGenerators\ImageGenerator|null
     */
    public function determineImageGenerator(Media $media)
    {
        return $media->getImageGenerators()
            ->map(function (string $imageGeneratorClassName) {
                return app($imageGeneratorClassName);
            })
            ->first->canConvert($media);
    }
}
