<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Receipt - {{ $project->reference_code ?? $project->name }}</title>
    <style>
        @page {
            margin: 15px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.4;
            position: relative;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            padding: 15px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 8px;
            color: white;
        }
        .header-content {
            display: table-cell;
            vertical-align: middle;
        }
        .header-content h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-content h2 {
            font-size: 11px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .header-content h3 {
            font-size: 13px;
            font-weight: bold;
            margin-top: 5px;
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
        }
        .qr-section {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
            text-align: center;
            padding-left: 15px;
        }
        .qr-code {
            width: 85px;
            height: 85px;
            background: white;
            padding: 5px;
            border-radius: 6px;
            margin: 0 auto;
        }
        .qr-label {
            font-size: 8px;
            margin-top: 3px;
            opacity: 0.9;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        .info-section {
            display: table-cell;
            width: 50%;
            padding: 12px;
            background: #f8fafc;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
            vertical-align: top;
        }
        .info-section:not(:last-child) {
            margin-right: 10px;
        }
        .info-section h4 {
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dbeafe;
            padding-bottom: 4px;
        }
        .info-row {
            margin-bottom: 6px;
            padding: 3px 0;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            font-size: 9px;
            display: block;
            margin-bottom: 2px;
        }
        .info-value {
            color: #0f172a;
            font-size: 10px;
            display: block;
        }
        .notes-section {
            margin-bottom: 12px;
            background: linear-gradient(to right, #fef3c7, #fef9e7);
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .notes-section h4 {
            font-size: 11px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 6px;
            border-bottom: 2px solid #fbbf24;
            padding-bottom: 3px;
        }
        .notes-content {
            color: #78350f;
            white-space: pre-wrap;
            line-height: 1.6;
            font-size: 9.5px;
        }
        .doc-history {
            margin-bottom: 12px;
            background: linear-gradient(to right, #f0fdf4, #f7fef9);
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #22c55e;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .doc-history h4 {
            font-size: 11px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 8px;
            border-bottom: 2px solid #86efac;
            padding-bottom: 3px;
        }
        .doc-entry {
            margin-bottom: 7px;
            padding: 6px 8px;
            background: white;
            border-radius: 4px;
            border-left: 3px solid #86efac;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .doc-meta {
            font-size: 8.5px;
            color: #059669;
            margin-bottom: 3px;
            font-weight: bold;
        }
        .doc-content {
            font-size: 9.5px;
            color: #064e3b;
            line-height: 1.4;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            background: #dbeafe;
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 4px solid #1e40af;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 6px;
            overflow: hidden;
        }
        table thead {
            background: #1e40af;
            color: white;
        }
        table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
        }
        table td {
            padding: 7px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        table tbody tr:hover {
            background: #f1f5f9;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background: linear-gradient(to right, #dbeafe, #bfdbfe) !important;
            font-weight: bold;
            color: #1e40af;
            font-size: 10px;
        }
        .footer {
            margin-top: 15px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 6px;
            border-top: 3px solid #3b82f6;
            text-align: center;
            font-size: 8.5px;
            color: #475569;
        }
        .footer-security {
            margin-top: 8px;
            padding: 8px;
            background: #fef3c7;
            border-radius: 4px;
            font-size: 8px;
            color: #92400e;
            border: 1px dashed #f59e0b;
        }
        .verification-box {
            background: white;
            padding: 8px;
            border-radius: 4px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(30, 64, 175, 0.04);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-in-progress, .badge-in_progress {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .badge-completed {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #86efac;
        }
        .badge-on-hold, .badge-on_hold {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fbbf24;
        }
    </style>
</head>
<body>
    <div class="watermark">VERIFIED • AUTHENTIC</div>
    
    <div class="header">
        <div class="header-content">
            <h1>Alchy Enterprises Inc.</h1>
            <h2>Smart Inventory Management System</h2>
            <h3>📋 Official Project Receipt & Material Release Report</h3>
        </div>
        <div class="qr-section">
            <div class="qr-code">
                {!! $qrCodeSvg !!}
            </div>
            <div class="qr-label">Scan to Verify</div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-section">
            <h4>📊 Project Details</h4>
            <div class="info-row">
                <span class="info-label">Project Name:</span>
                <span class="info-value">{{ $project->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Reference Code:</span>
                <span class="info-value">{{ $project->reference_code ?: 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="badge badge-{{ $project->status }}">
                        {{ strtoupper(str_replace('_', ' ', $project->status)) }}
                    </span>
                </span>
            </div>
        </div>

        <div class="info-section">
            <h4>🏢 Client Information</h4>
            <div class="info-row">
                <span class="info-label">Company Name:</span>
                <span class="info-value">{{ $project->client->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Branch Location:</span>
                <span class="info-value">{{ $project->client->branch }}</span>
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-section">
            <h4>📅 Project Timeline</h4>
            <div class="info-row">
                <span class="info-label">Start Date:</span>
                <span class="info-value">{{ $project->start_date ? $project->start_date->format('F d, Y') : 'Not Set' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Target Completion:</span>
                <span class="info-value">{{ $project->target_date ? $project->target_date->format('F d, Y') : 'Not Set' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Warranty Until:</span>
                <span class="info-value">{{ $project->warranty_until ? $project->warranty_until->format('F d, Y') : 'Not Set' }}</span>
            </div>
        </div>

        <div class="info-section" style="border-left-color: #22c55e;">
            <h4 style="color: #166534; border-bottom-color: #86efac;">🔒 Security Verification</h4>
            <div class="info-row">
                <span class="info-label">Verification Hash:</span>
                <span class="info-value" style="font-family: monospace; font-size: 8px; word-break: break-all;">{{ substr($verificationHash, 0, 32) }}...</span>
            </div>
            <div class="info-row">
                <span class="info-label">Generated By:</span>
                <span class="info-value">{{ auth()->user()->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Verification URL:</span>
                <span class="info-value" style="font-size: 8px; word-break: break-all;">{{ $verificationUrl }}</span>
            </div>
        </div>
    </div>

    @if($project->notes)
    <div class="notes-section">
        <h4>Project Overview</h4>
        <div class="notes-content">{{ $project->notes }}</div>
    </div>
    @endif

    @if($project->projectNotes && $project->projectNotes->count() > 0)
    <div class="doc-history">
        <h4>Documentation History</h4>
        @foreach($project->projectNotes->sortBy('created_at') as $note)
        <div class="doc-entry">
            <div class="doc-meta">
                <strong>{{ $note->created_at->format('M d, Y h:i A') }}</strong> - Documented by: {{ $note->user->name ?? 'Unknown User' }}
            </div>
            <div class="doc-content">{{ $note->content }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="section-title">
        💰 Material Releases & Expenses Breakdown
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">RELEASED</th>
                <th style="width: 15%;">ITEM</th>
                <th style="width: 30%;"></th>
                <th style="width: 25%;" class="text-right">QTY × COST</th>
                <th style="width: 15%;" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmount = 0;
                $totalQuantity = 0;
            @endphp
            @foreach($project->expenses->sortBy('released_at') as $expense)
            @php
                $totalAmount += $expense->total_cost;
                $totalQuantity += $expense->quantity_used;
            @endphp
            <tr>
                <td>{{ $expense->released_at ? $expense->released_at->format('M d, Y h:i A') : 'Not Specified' }}</td>
                <td>{{ $expense->inventory->brand ?? 'Unknown' }}</td>
                <td>{{ $expense->inventory->description ?? 'No Description' }}</td>
                <td class="text-right">{{ number_format($expense->quantity_used, 2) }} × ₱{{ number_format($expense->cost_per_unit, 2) }}</td>
                <td class="text-right">₱{{ number_format($expense->total_cost, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right; font-weight: bold;">GRAND TOTAL</td>
                <td class="text-right">₱{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>🕐 Generated:</strong> {{ now()->format('F d, Y h:i A') }} (Philippine Time)</p>
        <p style="margin-top: 5px;"><strong>System:</strong> Alchy Smart Inventory Management System v2.0</p>
        
        <div class="footer-security">
            <p style="font-weight: bold; margin-bottom: 4px;">⚠️ SECURITY NOTICE</p>
            <p>This is an official computer-generated receipt with QR code verification.</p>
            <p style="margin-top: 3px;">Any modification to this document will be detected during verification.</p>
            <p style="margin-top: 3px; font-weight: bold;">To verify authenticity: Scan the QR code or visit the verification URL above.</p>
        </div>
        
        <div class="verification-box">
            <p style="font-size: 8px; color: #475569;">
                <strong>Verification Hash:</strong> {{ $verificationHash }}
            </p>
            <p style="font-size: 7px; color: #64748b; margin-top: 3px;">
                This receipt is cryptographically signed and stored in our database for tamper detection.
            </p>
        </div>
    </div>
</body>
</html>
