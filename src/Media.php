<?php namespace Spatie\MediaLibrary;

use Eloquent;
use GlideImage;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableInterface;
use Spatie\MediaLibrary\Utility\File;

class Media extends Eloquent implements SortableInterface
{
    use Sortable;

    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';
    const TYPE_PDF = 'pdf';

    public $imageProfileUrls = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'profile_properties' => 'array',
    ];

    public static function boot()
    {
        static::deleted(function(Media $media) {
            app('mediaLibraryFileSystem')->removeFiles($media);
        });

        parent::boot();
    }

    /**
     * Create the polymorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function content()
    {
        return $this->morphTo();
    }

    /**
     * Get the path for a media-file.
     *
     * @param string $profileName
     * @return string
     */
    public function getPath($profileName = '')
    {
        return config('laravel-medialibrary.publicPath').'/'.$this->id.'/'.$this->path;
    }

    /**
     * Get the original Url to a media-file.
     *
     * @param string $profileName
     * @return string
     */
    public function getUrl($profileName = '')
    {
        return MediaLibraryUrlGenerator::getUrl($this, $profileName, app('MediaLibraryFileSystem')->getDriverType());
    }


    /**
     * Determine the type of a file.
     *
     * @return string
     */
    public function getType()
    {
        switch ($this->extension) {
            case 'png';
            case 'jpg':
            case 'jpeg':
                $type = self::TYPE_IMAGE;
                break;
            case 'pdf':
                $type = self::TYPE_PDF;
                break;
            default:
                $type = self::TYPE_FILE;
                break;
        }

        return $type;
    }



    public function getHumanReadableFileSize()
    {
        return File::getHumanReadableFileSize($this->size);
    }
}
