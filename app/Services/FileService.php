<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    protected UploadedFile $file;

    /**
     * Store a file locally and update the model's file attribute using Laravel's store method.
     *
     * @param Model $model
     * @param string $file_attribute
     * @param UploadedFile $file
     * @param string $folder
     * @return bool
     */
    public function storeLocal(Model $model, string $file_attribute, UploadedFile $file, string $folder = null)
    {
        $modelName = strtolower(class_basename($model));
        $folderName = $modelName . '_' . $file_attribute;
        return $file->store($folder ?? $folderName, 'public');
    }

    /**
     * Update a file locally and update the model's file attribute using Laravel's store method.
     *
     * @param Model $model
     * @param string $file_attribute
     * @param UploadedFile $file
     * @param string $folder
     * @return bool
     */
    public function updateLocal(Model $model, string $file_attribute, UploadedFile $file, string $folder = null)
    {
        if (!empty($model->$file_attribute)) {
            Storage::disk('public')->delete($model->$file_attribute);
        }
        return $this->storeLocal($model, $file_attribute, $file, $folder);
    }

    public function deleteLocal(Model $model, $file_attribute)
    {
        if (!empty($model->$file_attribute)) {
            return Storage::disk('public')->delete($model->$file_attribute);
        }
        return false;
    }
    /**
     * Store a remote file (by URL) and update the model's attribute.
     * Deletes old file if exists.
     *
     * @param Model $model
     * @param string $file_attribute
     * @param string $url
     * @param string|null $folder
     * @return string|false Saved path or false on failure
     */
    public function storeRemote(Model $model, string $file_attribute, string $url, string $folder = null)
    {
        // Delete existing
        $this->deleteLocal($model, $file_attribute);
        // Fetch contents
        try {
            $contents = @file_get_contents($url);
            if ($contents === false) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
        // Determine extension
        $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
        // Build folder and filename
        $modelName = strtolower(class_basename($model));
        $baseFolder = $folder ?? ($modelName . '_' . $file_attribute);
        $filename = $baseFolder . '/' . $model->id . '_' . time() . '.' . $ext;
        // Store file
        Storage::disk('public')->put($filename, $contents);
        return $filename;
    }
}
