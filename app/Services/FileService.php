<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FileService
{
    protected UploadedFile $file;

    public function storeLocal(Model $model, string $file_attribute, UploadedFile $file, string $folder = null)
    {
        $modelName = Str::lower(class_basename($model));
        $folderName = $modelName . '_' . $file_attribute;
        return $file->store($folder ?? $folderName, 'public');
    }

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
    public function storeRemote(Model $model, string $file_attribute, string $url, string $folder = null)
    {
        $this->deleteLocal($model, $file_attribute);

        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                return false;
            }
            $contents = $response->body();
        } catch (\Throwable $e) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
        $modelName = Str::lower(class_basename($model));
        $baseFolder = $folder ?? ($modelName . '_' . $file_attribute);
        $filename = $baseFolder . '/' . $model->id . '_' . now()->timestamp . '.' . $ext;
        Storage::disk('public')->put($filename, $contents);
        return $filename;
    }
}
