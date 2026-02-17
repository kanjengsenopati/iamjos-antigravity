<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Correspondence Proof - {{ $submission->slug }}</title>
    <style>
        @page {
            margin: 2.5cm 2.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .metadata {
            margin-bottom: 20px;
        }

        .metadata-row {
            margin-bottom: 6px;
        }

        .label {
            width: 130px;
            float: left;
            font-weight: bold;
        }

        .value {
            margin-left: 140px;
        }

        .authors-list {
            font-style: italic;
        }

        .affiliations {
            margin-top: 5px;
            font-size: 10pt;
            color: #444;
        }

        .publication-info {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 10px 0;
            margin: 20px 0;
            font-size: 10pt;
        }

        .publication-info p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10pt;
        }

        th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 8px 5px;
            font-weight: bold;
        }

        td {
            padding: 8px 5px;
            vertical-align: top;
            border-bottom: 1px solid #ddd;
        }
        
        tr:last-child td {
            border-bottom: 1px solid #000;
        }

        .col-no { width: 5%; text-align: center; }
        .col-subject { width: 70%; }
        .col-date { width: 25%; text-align: right; }

        .footer {
            margin-top: 30px;
            font-size: 9pt;
            text-align: center;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        a { color: #000; text-decoration: none; }
    </style>
</head>

<body>

    <div class="header">
        BUKTI KORESPONDENSI ARTIKEL JURNAL INTERNASIONAL BEREPUTASI
    </div>

    <div class="metadata">
        <div class="metadata-row">
            <div class="label">Judul Artikel</div>
            <div class="value">: {{ $submission->title }}</div>
        </div>
        <div class="metadata-row">
            <div class="label">Jurnal</div>
            <div class="value">: {{ $journal->name }}</div>
        </div>
        <div class="metadata-row">
            <div class="label">Penulis</div>
            <div class="value authors-list">
                : 
                @foreach($authorsData as $index => $author)
                    {{ $author['name'] }}@foreach($author['indices'] as $idx)<sup>{{ $idx }}</sup>@endforeach{{ $loop->last ? '' : ', ' }}
                @endforeach
            </div>
            <div class="value affiliations">
                @foreach($affiliationsList as $id => $affiliation)
                    <div><sup>{{ $id }}</sup> {{ $affiliation }}</div>
                @endforeach
            </div>
        </div>
        <div class="metadata-row">
            <div class="label">Link Publikasi</div>
            <div class="value">: <a href="{{ $live_url }}" target="_blank">{{ $live_url }}</a></div>
        </div>
    </div>

    <div class="publication-info">
        <p>
            <strong>{{ $journal->name }}</strong> | Vol {{ $issue_vol ?? '-' }} | {{ $issue_no ? 'No '.$issue_no : '' }}
        </p>
        <p>
            DOI: <a href="{{ $doi ?? '#' }}">{{ $doi ?? 'Pending' }}</a> | 
            &copy; {{ $issue_year }} 
            @foreach($authorsData as $author){{ $author['name'] }}{{ $loop->last ? '' : ', ' }}@endforeach
        </p>
        <p>This work is licensed under CC Attribution 4.0</p>
        <p>Submitted: {{ $submitted_at }} | Published: {{ $published_at }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-subject">Perihal</th>
                <th class="col-date">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td class="col-no">{{ $loop->iteration }}</td>
                    <td class="col-subject">{{ $log['description'] }}</td>
                    <td class="col-date">{{ $log['date'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->isoFormat('D MMMM Y H:mm') }} by {{ $journal->name }} System
    </div>

</body>

</html>
