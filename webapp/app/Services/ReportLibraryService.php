<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Report;
use App\Models\ReportFolder;
use App\Models\ReportTag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReportLibraryService
{
    public function foldersQuery(User $user, ?Organization $organization = null): Builder
    {
        return ReportFolder::query()
            ->where(function (Builder $query) use ($user, $organization): void {
                $query->where('user_id', $user->id);

                if ($organization !== null) {
                    $query->orWhere(function (Builder $orgQuery) use ($organization, $user): void {
                        $orgQuery->where('organization_id', $organization->id)
                            ->where(function (Builder $visibility) use ($user): void {
                                $visibility->where('is_shared', true)
                                    ->orWhere('user_id', $user->id);
                            });
                    });
                }
            })
            ->orderBy('name');
    }

    public function tagsQuery(User $user, ?Organization $organization = null): Builder
    {
        return ReportTag::query()
            ->where(function (Builder $query) use ($user, $organization): void {
                $query->where('user_id', $user->id);

                if ($organization !== null) {
                    $query->orWhere('organization_id', $organization->id);
                }
            })
            ->orderBy('name');
    }

    public function createFolder(User $user, array $data, ?Organization $organization = null): ReportFolder
    {
        if ($organization !== null) {
            return ReportFolder::query()->create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'name' => $data['name'],
                'is_shared' => (bool) ($data['is_shared'] ?? false),
            ]);
        }

        return ReportFolder::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'is_shared' => false,
        ]);
    }

    public function updateFolder(ReportFolder $folder, array $data): ReportFolder
    {
        $folder->update($data);

        return $folder->fresh();
    }

    public function deleteFolder(ReportFolder $folder): void
    {
        Report::query()
            ->where('report_folder_id', $folder->id)
            ->update(['report_folder_id' => null]);

        $folder->delete();
    }

    public function createTag(User $user, array $data, ?Organization $organization = null): ReportTag
    {
        if ($organization !== null) {
            return ReportTag::query()->create([
                'organization_id' => $organization->id,
                'name' => $data['name'],
            ]);
        }

        return ReportTag::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
        ]);
    }

    public function updateTag(ReportTag $tag, array $data): ReportTag
    {
        $tag->update($data);

        return $tag->fresh();
    }

    public function deleteTag(ReportTag $tag): void
    {
        $tag->reports()->detach();
        $tag->delete();
    }

    /**
     * @param  int|null|false  $folderId
     * @param  list<int>|false  $tagIds
     */
    public function assignReportMetadata(Report $report, int|null|false $folderId = false, array|false $tagIds = false): Report
    {
        $report->loadMissing(['user', 'organization']);

        if ($folderId !== false) {
            if ($folderId === null) {
                $report->report_folder_id = null;
            } else {
                $folder = ReportFolder::query()->find($folderId);

                if ($folder === null) {
                    throw ValidationException::withMessages([
                        'report_folder_id' => ['The selected folder does not exist.'],
                    ]);
                }

                $this->assertFolderAccessible($report->user, $folder, $report->organization);
                $report->report_folder_id = $folder->id;
            }
        }

        if ($tagIds !== false) {
            $tags = $this->resolveTagsForReport($report, $tagIds);
            $report->save();
            $report->tags()->sync($tags->pluck('id'));

            return $report->fresh(['tags', 'folder', 'analysisJob']);
        }

        $report->save();

        return $report->fresh(['tags', 'folder', 'analysisJob']);
    }

    /**
     * @param  list<int>  $tagIds
     * @return Collection<int, ReportTag>
     */
    private function resolveTagsForReport(Report $report, array $tagIds): Collection
    {
        $tags = ReportTag::query()->whereIn('id', $tagIds)->get();

        if ($tags->count() !== count(array_unique($tagIds))) {
            throw ValidationException::withMessages([
                'tag_ids' => ['One or more tags are invalid.'],
            ]);
        }

        foreach ($tags as $tag) {
            $this->assertTagAccessible($report->user, $tag, $report->organization);
        }

        return $tags;
    }

    public function assertFolderAccessible(User $user, ReportFolder $folder, ?Organization $reportOrganization = null): void
    {
        if ($folder->user_id === $user->id) {
            return;
        }

        $folder->loadMissing('organization');

        if ($folder->organization !== null) {
            if ($reportOrganization !== null && $folder->organization_id !== $reportOrganization->id) {
                throw ValidationException::withMessages([
                    'report_folder_id' => ['This folder belongs to a different organization.'],
                ]);
            }

            if ($folder->is_shared && $user->isMemberOf($folder->organization)) {
                return;
            }
        }

        throw ValidationException::withMessages([
            'report_folder_id' => ['You do not have access to this folder.'],
        ]);
    }

    public function assertTagAccessible(User $user, ReportTag $tag, ?Organization $reportOrganization = null): void
    {
        if ($tag->user_id === $user->id) {
            return;
        }

        $tag->loadMissing('organization');

        if ($tag->organization !== null) {
            if ($reportOrganization !== null && $tag->organization_id !== $reportOrganization->id) {
                throw ValidationException::withMessages([
                    'tag_ids' => ['One or more tags belong to a different organization.'],
                ]);
            }

            if ($user->isMemberOf($tag->organization)) {
                return;
            }
        }

        throw ValidationException::withMessages([
            'tag_ids' => ['You do not have access to one or more tags.'],
        ]);
    }
}
