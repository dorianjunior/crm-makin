<?php

namespace App\Services\CMS;

use App\Models\CMS\ContentVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class VersioningService
{
    public function createVersion(Model $content, int $userId, ?string $changeSummary = null): ContentVersion
    {
        $latestVersion = $this->getLatestVersion($content);
        $versionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        return ContentVersion::create([
            'versionable_type' => get_class($content),
            'versionable_id' => $content->id,
            'created_by' => $userId,
            'version_number' => $versionNumber,
            'content_data' => $content->toArray(),
            'change_summary' => $changeSummary ?? ($versionNumber === 1 ? 'Initial version' : 'Content updated'),
        ]);
    }

    public function getHistory(Model $content): Collection
    {
        return ContentVersion::forContent(get_class($content), $content->id)
            ->latest()
            ->with('creator')
            ->get();
    }

    public function getLatestVersion(Model $content): ?ContentVersion
    {
        return ContentVersion::forContent(get_class($content), $content->id)
            ->latest()
            ->first();
    }

    public function getVersion(Model $content, int $versionNumber): ?ContentVersion
    {
        return ContentVersion::forContent(get_class($content), $content->id)
            ->where('version_number', $versionNumber)
            ->first();
    }

    public function rollback(Model $content, int $versionNumber, int $userId): bool
    {
        $version = $this->getVersion($content, $versionNumber);

        if (! $version) {
            return false;
        }

        $contentData = $version->content_data;

        // Remove fields that shouldn't be restored
        unset($contentData['id'], $contentData['created_at'], $contentData['updated_at'], $contentData['deleted_at']);

        // Update content with version data
        $content->update($contentData);

        // Create new version to record the rollback
        $this->createVersion(
            $content,
            $userId,
            "Rolled back to version {$versionNumber}"
        );

        return true;
    }

    public function compareVersions(Model $content, int $version1Number, int $version2Number): array
    {
        $version1 = $this->getVersion($content, $version1Number);
        $version2 = $this->getVersion($content, $version2Number);

        if (! $version1 || ! $version2) {
            return [];
        }

        $data1 = $version1->content_data;
        $data2 = $version2->content_data;

        $differences = [];

        foreach ($data1 as $key => $value) {
            if (! isset($data2[$key]) || $data2[$key] !== $value) {
                $differences[$key] = [
                    'version_'.$version1Number => $value,
                    'version_'.$version2Number => $data2[$key] ?? null,
                ];
            }
        }

        return $differences;
    }

    public function deleteOldVersions(Model $content, int $keepLast = 10): int
    {
        $versions = $this->getHistory($content);

        if ($versions->count() <= $keepLast) {
            return 0;
        }

        $versionsToDelete = $versions->skip($keepLast);

        return ContentVersion::whereIn('id', $versionsToDelete->pluck('id'))->delete();
    }
}
