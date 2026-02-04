{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
{!! '<' . '?xml-stylesheet type="text/xsl" href="' . asset('oai.xsl') . '" ?' . '>' !!}
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate>{{ now()->format('Y-m-d\TH:i:s\Z') }}</responseDate>
    <request verb="Identify">{{ url()->current() }}</request>
    <Identify>
        <repositoryName>{{ $journal->name }}</repositoryName>
        <baseURL>{{ route('journal.oai', $journal->slug) }}</baseURL>
        <protocolVersion>2.0</protocolVersion>
        @php
            // 1. Normalisasi data (jika settings masih string JSON, decode dulu)
            $settings = is_string($journal->settings) ? json_decode($journal->settings, true) : $journal->settings;

            // 2. Ambil email menggunakan helper Laravel data_get agar aman dari error "undefined index"
            // Syntax: data_get(target, key, default_value)
            $adminEmail = data_get($settings, 'contact.email', 'admin@iamjos.id');
        @endphp
        <adminEmail>{{ $adminEmail }}</adminEmail>
        <earliestDatestamp>
            {{ \App\Models\Submission::min('updated_at') ? \Carbon\Carbon::parse(\App\Models\Submission::min('updated_at'))->format('Y-m-d\TH:i:s\Z') : now()->format('Y-m-d\TH:i:s\Z') }}
        </earliestDatestamp>
        <deletedRecord>persistent</deletedRecord>
        <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
        <description>
            <toolkit xmlns="http://oai.dlib.vt.edu/OAI/metadata/toolkit"
                xsi:schemaLocation="http://oai.dlib.vt.edu/OAI/metadata/toolkit http://oai.dlib.vt.edu/OAI/metadata/toolkit.xsd">
                <title>IAMJOS Journal System</title>
                <author>
                    <name>IAMJOS Development Team</name>
                    <email>dev@iamjos.test</email>
                </author>
                <version>1.0.0</version>
                <URL>{{ config('app.url') }}</URL>
            </toolkit>
        </description>
    </Identify>
</OAI-PMH>
