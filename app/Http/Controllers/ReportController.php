<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function store(Request $request)
    {
        try {
            $request->validate([
                'location' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'nullable|string|max:100',
                'severity' => 'nullable|integer|min:0|max:5',
                'probability' => 'nullable|integer|min:0|max:5',
                'status' => 'required|in:draft,submitted,reviewed,approved,closed',
                'attachments' => 'sometimes|array',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            ]);

            $report = $this->reportService->createReport($request->all());
            return $this->successResponse($report, 'Report created successfully', 201);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to create report', 500, $th->getMessage());
        }
    }

    public function update(int $id, Request $request)
    {
        try {
            $report = $this->reportService->getReportDetail($id);
            if (!$report) {
                return $this->errorResponse('Report not found', 404);
            }

            if ($report->status === 'draft') {
                $request->validate([
                    'location' => 'sometimes|required|string|max:255',
                    'description' => 'sometimes|required|string',
                    'category' => 'sometimes|nullable|string|max:100',
                    'severity' => 'nullable|integer|min:0|max:5',
                    'probability' => 'nullable|integer|min:0|max:5',
                    'status' => 'sometimes|required|in:draft,submitted,reviewed,approved,closed',
                    'attachments' => 'sometimes|array',
                    'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
                    'old_attachments' => 'sometimes',
                ]);
            } else {
                $request->validate([
                    'actions_taken' => 'sometimes|nullable|string',
                    'status' => 'required|in:draft,submitted,reviewed,approved,closed',
                ]);
            }

            $updatedReport = $this->reportService->updateReport($request->all(), $id);
            return $this->successResponse($updatedReport, 'Report updated successfully');
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to update report', 500, $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        $report = $this->reportService->getReportDetail($id);
        if (!$report) {
            return $this->errorResponse('Report not found', 404);
        }

        if ($report->status !== 'draft') {
            return $this->errorResponse('Only draft reports can be deleted', 403);
        }

        $this->reportService->deleteReport($id);
        return $this->successResponse(null, 'Report deleted successfully');
    }

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $search = $request->query('search');

        $reports = $this->reportService->getReport($page, $search);
        return $this->successResponse($reports, 'Reports retrieved successfully');
    }

    public function show(int $id)
    {
        $report = $this->reportService->getReportDetail($id);
        if (!$report) {
            return $this->errorResponse('Report not found', 404);
        }

        return $this->successResponse($report, 'Report retrieved successfully');
    }
}
