<html>
<table class="table table-bordered" border="1">
    <thead>
        <tr>
            <th width=30 valign="middle" align="center"><b>Nama Fisio</b></th>
            @foreach ($sessions as $session)
            <th width=30 valign="middle" align="center">Session<br><b>{{ $session->name }}</b></th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($physiotherapyData as $data)
        <tr>
            <td valign="middle"><b>{{ $data['name'] }}</b></td>
            @foreach ($sessions as $session)
            <td valign="middle">
                {!! isset($data['sessions'][$session->name]) ? implode('<br>', $data['sessions'][$session->name]) : "-"
                !!}
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>


</html>