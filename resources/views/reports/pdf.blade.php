<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ $month }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        .summary-item {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report</h1>
        <p>{{ $month }}</p>
    </div>

    <div class="info">
        <p><strong>Student Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Period:</strong> {{ $startDate->format('F j, Y') }} to {{ $endDate->format('F j, Y') }}</p>
    </div>

    @if($attendances->isEmpty())
    <p>No attendance records found for this period.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->date->format('M d, Y') }}</td>
                <td>{{ $attendance->subject->name }}</td>
                <td>{{ ucfirst($attendance->status) }}</td>
                <td>{{ $attendance->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h2>Summary</h2>
        @php
            $grouped = $attendances->groupBy('subject_id');
        @endphp
        @foreach($grouped as $subjectId => $subjectAttendances)
            @php
                $subject = $subjectAttendances->first()->subject;
                $total = $subjectAttendances->where('status', '!=', 'cancelled')->count();
                $present = $subjectAttendances->where('status', 'present')->count();
                $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
            @endphp
            <div class="summary-item">
                <strong>{{ $subject->name }}:</strong> 
                {{ $present }}/{{ $total }} ({{ $percentage }}%)
            </div>
        @endforeach
    </div>
    @endif

    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>

