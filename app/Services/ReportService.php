<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Eloquent\ReportRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Event\Code\Throwable;
use TheSeer\Tokenizer\Exception;

class ReportService
{
    protected FirebaseStorageService $firebase;
    protected ReportRepository $reportRepository;
    protected Authenticatable|User|null $authUser;

    public function __construct() {
        $this->firebase = new FirebaseStorageService();
        $this->reportRepository = new ReportRepository();
        $this->authUser = auth()->user();
    }

    public function createReport(array $data): Model
    {
        $data['created_by'] = $this->authUser->id;
        $data['updated_by'] = $this->authUser->id;
        $data['risk_score'] = $this->getRiskScore($data['severity'] ?? 0, $data['probability'] ?? 0);
        $data['risk_level'] = $this->getRiskLevel($data['severity'] ?? 0, $data['probability'] ?? 0);

        if (!empty($data['attachments'])) {
            $uploadedFiles = $this->firebase->uploadMultiple(
                $data['attachments'],
                'reports/' . $this->authUser->id
            );

            $data['attachments'] = $uploadedFiles;
        }

        return $this->reportRepository->create($data);
    }

    public function updateReport(array $data, int $id): ?Model
    {
        try {
            $report = $this->reportRepository->find($id);
            $data['updated_by'] = $this->authUser->id;

            // Check status if draft can edit all fields
            if ($report->status === 'draft') {
                $data['risk_score'] = $this->getRiskScore($data['severity'], $data['probability']);
                $data['risk_level'] = $this->getRiskLevel($data['severity'], $data['probability']);

                $existingAttachments = $report->attachments ?? [];
                $oldAttachments = json_decode($data['old_attachments'], true) ?? [];
                $newFiles = [];
                foreach ($data['attachments'] ?? [] as $attachment) {
                    if ($attachment instanceof UploadedFile) {
                        $newFiles[] = $attachment;
                    }
                }

                // Delete removed attachments
                $deletedAttachments = array_filter($oldAttachments, function ($old) use ($existingAttachments) {
                    foreach ($existingAttachments as $existing) {
                        if ($old['path'] === $existing['path']) return false;
                    }
                    return true;
                });
                if (empty($oldAttachments)) {
                    $deletedAttachments = $existingAttachments;
                }

                foreach ($deletedAttachments as $deleted) {
                    $this->firebase->delete($deleted['path']);
                }

                // Upload new files
                $uploadedFiles = [];
                if (!empty($newFiles)) {
                    $uploadedFiles = $this->firebase->uploadMultiple($newFiles, 'reports/' . $report->id);
                }

                $data['attachments'] = array_merge($oldAttachments, $uploadedFiles);

            } else {
                // Only can update status field
                $data = [
                    'status' => $data['status'],
                    'action_taken' => $data['action_taken'],
                ];
            }

            $report->update($data);
            return $this->reportRepository->find($id);
        } catch (\Exception $exception) {
            logger($exception);
            throw new \Exception($exception->getMessage());
        }
    }

    public function deleteReport(int $id): int
    {
        try {
            $report = $this->reportRepository->find($id);
            // Only draft report can be deleted
            if ($report->status === 'draft') {
                // delete attachments
                $attachments = $report->attachments ?? [];
                foreach ($attachments as $attachment) {
                    $this->firebase->delete($attachment['path']);
                }

                return $this->reportRepository->destroy($id);
            } else {
                throw new \Exception('Only draft report can be deleted');
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function getReport(int $page = 1, string|null $search = null): LengthAwarePaginator
    {
        $model = $this->reportRepository->model;
        $model = $model->newQuery()
            ->with('creator');
        if (!empty($search)) {
            $model = $model
                ->where(function ($query) use ($search) {
                    $query->where('location', 'LIKE', '%' . $search . '%')
                        ->orWhere('category', 'LIKE', '%' . $search . '%')
                        ->orWhere('risk_level', 'LIKE', '%' . $search . '%')
                        ->orWhere('risk_score', 'LIKE', '%' . $search . '%')
                        ->orWhere('status', 'LIKE', '%' . $search . '%')
                        ->orWhere('created_at', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('creator', function ($query) use ($search) {
                            $query->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
        }

        // for admin
        $isAdmin = $this->authUser->hasAnyRole(['SPV', 'Admin']);
        if ($isAdmin) {
            $model = $model
                ->where(function ($query) {
                    $query->where('created_by', '=', $this->authUser->id)
                        ->orWhere('status', '!=', 'draft');
                });
        } else {
            $model = $model
                ->where('created_by', '=', $this->authUser->id);
        }

        $model = $model->orderBy('created_at', 'DESC');

        $this->reportRepository->model = $model;
        return $this->reportRepository->paginate($page);
    }

    public function getReportDetail(int $id): ?Model
    {
        return $this->reportRepository->find($id);
    }

    private function getRiskScore(int $severity, int $probability): int
    {
        return $severity * $probability;
    }

    private function getRiskLevel(int $severity, int $probability): string
    {
        $riskScore = $this->getRiskScore($severity, $probability);
        return match (true) {
            $riskScore <= 5 => 'Low',
            $riskScore <= 10 => 'Medium',
            $riskScore <= 15 => 'High',
            default => 'Critical',
        };
    }
}
