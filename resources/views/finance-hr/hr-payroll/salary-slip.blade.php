<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Slip - {{ $employee->name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <style>
        @media print { .no-print { display: none !important; } body { background: white; } }
        body { font-size: 14px; }
    </style>
</head>
<body class="p-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h4>Salary Slip Preview</h4>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-2"></i>Print / Save as PDF</button>
        </div>

        <div class="card p-4">
            <div class="row mb-4">
                <div class="col-6">
                    <h2 class="fw-bold text-primary">AlphaGrow</h2>
                    <p class="mb-0">123 Business Park, Tech City</p>
                    <p>GSTIN: 27AABCU9603R1ZM</p>
                </div>
                <div class="col-6 text-end">
                    <h3 class="fw-bold">SALARY SLIP</h3>
                    <p class="mb-0"><strong>Payroll #:</strong> {{ $payroll->payroll_no }}</p>
                    <p><strong>Month:</strong> {{ $payroll->month_formatted }}</p>
                </div>
            </div>

            <div class="row mb-4 p-3 bg-light rounded">
                <div class="col-md-6">
                    <h6 class="fw-bold text-muted text-uppercase">Employee Details</h6>
                    <p class="mb-1"><strong>Name:</strong> {{ $employee->name }}</p>
                    <p class="mb-1"><strong>Code:</strong> {{ $employee->emp_code }}</p>
                    <p class="mb-1"><strong>Designation:</strong> {{ $employee->designation }}</p>
                    <p class="mb-0"><strong>Department:</strong> {{ $employee->department->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold text-muted text-uppercase">Statutory Details</h6>
                    <p class="mb-1"><strong>UAN:</strong> {{ $employee->uan ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>ESI No:</strong> {{ $employee->esi_no ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>DOJ:</strong> {{ $employee->doj->format('d M Y') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-plus-circle me-2"></i>EARNINGS</h6>
                    <table class="table table-bordered">
                        <tr><td>Basic Salary</td><td class="text-end">₹{{ number_format($detail->basic, 2) }}</td></tr>
                        <tr><td>House Rent Allowance (HRA)</td><td class="text-end">₹{{ number_format($detail->hra, 2) }}</td></tr>
                        <tr><td>Other Allowances</td><td class="text-end">₹{{ number_format($detail->allowances, 2) }}</td></tr>
                        <tr><td>Overtime</td><td class="text-end">₹{{ number_format($detail->overtime, 2) }}</td></tr>
                        <tr class="table-success"><td class="fw-bold">GROSS EARNINGS</td><td class="text-end fw-bold">₹{{ number_format($detail->gross, 2) }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold text-danger mb-3"><i class="bi bi-dash-circle me-2"></i>DEDUCTIONS</h6>
                    <table class="table table-bordered">
                        <tr><td>Provident Fund (PF)</td><td class="text-end">₹{{ number_format($detail->pf, 2) }}</td></tr>
                        <tr><td>Employee State Insurance (ESI)</td><td class="text-end">₹{{ number_format($detail->esi, 2) }}</td></tr>
                        <tr><td>Tax Deducted at Source (TDS)</td><td class="text-end">₹{{ number_format($detail->tds, 2) }}</td></tr>
                        <tr><td>Loss of Pay Days</td><td class="text-end">{{ $detail->lop_days }} days</td></tr>
                        <tr class="table-danger"><td class="fw-bold">TOTAL DEDUCTIONS</td><td class="text-end fw-bold">₹{{ number_format($detail->total_deductions, 2) }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="mt-4 p-4 bg-primary text-white rounded text-center">
                <h5 class="mb-1">NET SALARY PAYABLE</h5>
                <h2 class="fw-bold mb-0">₹{{ number_format($detail->net_pay, 2) }}</h2>
                <small class="opacity-75">Working Days: {{ $detail->working_days }} | LOP: {{ $detail->lop_days }}</small>
            </div>

            <div class="mt-5 pt-4 border-top text-center text-muted">
                <p class="mb-0">This is a computer-generated salary slip.</p>
                <small>AlphaGrow HCM · Generated on {{ now()->format('d M Y, h:i A') }}</small>
            </div>
        </div>
    </div>
</body>
</html>