@extends('layouts.master')
@section('title', 'Reports')
@section('page-title', 'Financial & Operational Reports')

@section('content')
    <style>
        .report-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.04);
            border-radius: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        .report-card:hover, .report-card.active {
            border-color: var(--c-primary, #6366f1);
            background: rgba(99, 102, 241, 0.03);
            transform: translateY(-2px);
        }
        .report-card.active {
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }
        .report-card .icon-box {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(99, 102, 241, 0.1); color: #6366f1;
        }
        .report-card.active .icon-box {
            background: #6366f1; color: white;
        }
        
        /* Table Styling */
        .report-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        .report-table td {
            font-size: 0.88rem;
            color: #334155;
            vertical-align: middle;
        }
        .report-table tr:hover td {
            background: #f8fafc;
        }
    </style>

    <div class="row g-4">
        {{-- ==================== LEFT: REPORT SELECTOR ==================== --}}
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3 px-2 text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Report Templates</h6>
                <div class="d-flex flex-column gap-2">
                    @foreach($reportTypes as $key => $report)
                        <a href="{{ route('intelligence.reports.index', ['type' => $key, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                           class="report-card p-3 text-decoration-none text-dark {{ $reportType == $key ? 'active' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box">
                                    <i class="bi {{ $report['icon'] }} fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $report['name'] }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $report['desc'] }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ==================== RIGHT: REPORT DATA ==================== --}}
        <div class="col-lg-9">
            {{-- Filter Bar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('intelligence.reports.index') }}" class="row g-3 align-items-end">
                        <input type="hidden" name="type" value="{{ $reportType }}">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-funnel me-1"></i> Generate Report
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('intelligence.reports.export', ['type' => $reportType, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                               class="btn btn-outline-success btn-sm w-100">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Report Title & Data --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>{{ $title }}
                    </h6>
                    <span class="badge bg-light text-dark border">{{ count($data) }} Records</span>
                </div>
                <div class="card-body p-0">
                    @if(count($data) > 0)
                        <div class="table-responsive">
                            <table class="table report-table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        @foreach($columns as $col)
                                            <th class="ps-4">{{ $col }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $row)
                                        <tr>
                                            @foreach($row as $key => $value)
                                                <td class="ps-4">
                                                    @if($key === 'total' || $key === 'amount' || $key === 'value' || $key === 'current' || $key === 'd30' || $key === 'd60' || $key === 'd60_plus')
                                                        <span class="fw-semibold">₹{{ number_format($value, 2) }}</span>
                                                    @elseif($key === 'status')
                                                        @php
                                                            $statusColors = ['paid' => 'success', 'completed' => 'success', 'sent' => 'warning', 'overdue' => 'danger', 'pending' => 'warning', 'approved' => 'info', 'active' => 'success'];
                                                            $color = $statusColors[strtolower($value)] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">{{ $value }}</span>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                                {{-- Optional: Footer with totals for financial reports --}}
                                @if(in_array($reportType, ['sales', 'expenses']))
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="{{ count($columns) - 2 }}" class="text-end fw-bold ps-4">Grand Total:</td>
                                            <td class="text-end fw-bold text-primary">
                                                ₹{{ number_format($data->sum('amount'), 2) }}
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                ₹{{ number_format($data->sum('total'), 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <p class="mb-0">No data found for the selected date range.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection