<?php


namespace App\Services\Storage;

use Storage;
use Str;

/**
 * Class StorageService
 */
class StorageService
{
    /**
     * @param string $content
     *
     * @return string
     */
    public static function storeContent(string $content): string
    {
        $fileName = Str::random(20);
        $disk     = Storage::disk('uploads');
        $disk->put($fileName, $content);

        return $fileName;
    }

}
