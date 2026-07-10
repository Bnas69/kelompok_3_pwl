<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsDataService $analytics)
    {
    }

    public function summary(): JsonResponse
    {
        return response()->json($this->analytics->summary());
    }

    public function trendData(Request $request): JsonResponse
    {
        return response()->json($this->analytics->trendData($this->filters($request)));
    }

    public function syncMonthly(Request $request): JsonResponse
    {
        return response()->json($this->analytics->syncMonthly($this->filters($request)));
    }

    public function departmentRisk(Request $request): JsonResponse
    {
        return response()->json($this->analytics->departmentRisk($this->filters($request)));
    }

    public function riskComposition(Request $request): JsonResponse
    {
        return response()->json($this->analytics->riskComposition($this->filters($request)));
    }

    public function topJobRoleRisk(Request $request): JsonResponse
    {
        return response()->json($this->analytics->topJobRoleRisk($this->filters($request)));
    }

    public function jobSatisfaction(Request $request): JsonResponse
    {
        return response()->json($this->analytics->jobSatisfaction($this->filters($request)));
    }

    private function filters(Request $request): array
    {
        $data = $request->validate([
            'filter' => ['nullable', Rule::in(['daily', 'monthly', 'yearly', 'date_range'])],
            'start_date' => ['nullable', 'required_if:filter,date_range', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'required_if:filter,date_range', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'year' => ['nullable', 'integer', 'min:2005', 'max:'.now()->year],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ], [
            'filter.in' => 'Filter tidak tersedia.',
            'start_date.required_if' => 'Tanggal awal wajib diisi.',
            'start_date.date_format' => 'Format tanggal awal harus YYYY-MM-DD.',
            'end_date.required_if' => 'Tanggal akhir wajib diisi.',
            'end_date.date_format' => 'Format tanggal akhir harus YYYY-MM-DD.',
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
            'year.integer' => 'Tahun harus berupa angka.',
            'year.min' => 'Tahun tidak boleh kurang dari 2005.',
            'year.max' => 'Tahun tidak boleh lebih dari tahun berjalan.',
            'month.integer' => 'Bulan harus berupa angka.',
            'month.min' => 'Bulan tidak valid.',
            'month.max' => 'Bulan tidak valid.',
        ]);

        $data['filter'] ??= 'monthly';
        if (
            isset($data['year'], $data['month'])
            && (int) $data['year'] === now()->year
            && (int) $data['month'] > now()->month
        ) {
            throw ValidationException::withMessages([
                'month' => 'Bulan belum tersedia.',
            ]);
        }

        return $data;
    }
}
