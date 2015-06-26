<?php namespace Spatie\MediaLibrary\Traits;

interface HasMediaInterface
{
    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function media();

    /**
     * Add media to media collection from a given file.
     *
     * @param $file
     * @param $collectionName
     * @param bool $preserveOriginal
     * @param bool $addAsTemporary
     *
     * @return mixed
     */
    public function addMedia($file, $collectionName, $preserveOriginal = false, $addAsTemporary = false);

    /**
     * Get media collection by its collectionName.
     *
     * @param $collectionName
     * @param array $filters
     *
     * @return mixed
     */
    public function getMedia($collectionName, $filters = []);

    /**
     * Remove a media item by its id.
     *
     * @param $id
     */
    public function removeMedia($id);

    /**
     * Get an array with the properties of the derived files.
     *
     * @return array
     */
    public function getMediaProfileProperties();

    /**
     * Determine which profiles should be executed for the give $mediaCollection
     *
     * @param string $mediaCollectionName
     * @return array
     */
    public function getMediaProfileNames($mediaCollectionName);

    /**
     * Get an array with the properties of the derived files.
     *
     * @return array
     */
    public function get();

    /**
     * Remove all media in the given collection.
     *
     * @param $collectionName
     * @return void
     */
    public function emptyCollection($collectionName);
}
