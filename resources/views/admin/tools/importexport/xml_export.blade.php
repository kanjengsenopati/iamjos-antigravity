<?xml version="1.0"?>
<articles xmlns="http://pkp.sfu.ca" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://pkp.sfu.ca native.xsd">
    @foreach ($submissions as $sub)
        <article locale="en_US"
            date_submitted="{{ $sub->submitted_at ? $sub->submitted_at->format('Y-m-d') : now()->format('Y-m-d') }}"
            status="3" stage="production" current_publication_id="{{ $sub->id }}">

            <id type="internal" advice="ignore">{{ $sub->id }}</id>

            {{-- 1. SUBMISSION FILES (Base64 Embedding) --}}
            @foreach ($sub->files as $file)
                <submission_file xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" stage="submission"
                    id="{{ $file->id }}" created_at="{{ $file->created_at->format('Y-m-d') }}"
                    updated_at="{{ $file->updated_at->format('Y-m-d') }}" viewable="false" genre="Article Text"
                    uploader="admin" xsi:schemaLocation="http://pkp.sfu.ca native.xsd">
                    <name locale="en_US">{{ $file->file_name ?? $file->name }}</name>
                    <file id="{{ $file->id }}" filesize="{{ $file->file_size ?? 0 }}" extension="pdf">
                        {{-- Convert physical file to Base64 --}}
                        @php
                            $content = '';
                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file->file_path)) {
                                $fileData = \Illuminate\Support\Facades\Storage::disk('public')->get($file->file_path);
                                $content = base64_encode($fileData);
                            }
                        @endphp
                        <embed encoding="base64">{{ $content }}</embed>
                    </file>
                </submission_file>
            @endforeach

            {{-- 2. PUBLICATION METADATA (OJS 3.3 Structure) --}}
            <publication xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" locale="en_US" version="1"
                status="3" primary_contact_id="{{ $sub->authors->where('is_primary', true)->first()->id ?? 0 }}"
                url_path="" seq="0" access_status="0" xsi:schemaLocation="http://pkp.sfu.ca native.xsd">

                <id type="internal" advice="ignore">{{ $sub->id }}</id>
                <title locale="en_US">{{ $sub->title }}</title>
                @if ($sub->subtitle)
                    <subtitle locale="en_US">{{ $sub->subtitle }}</subtitle>
                @endif

                <abstract locale="en_US">{{ strip_tags($sub->abstract) }}</abstract>

                {{-- Keywords --}}
                @if ($sub->keywords)
                    <keywords locale="en_US">
                        @foreach (explode(',', $sub->keywords) as $keyword)
                            <keyword>{{ trim($keyword) }}</keyword>
                        @endforeach
                    </keywords>
                @endif

                {{-- Authors --}}
                <authors xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xsi:schemaLocation="http://pkp.sfu.ca native.xsd">
                    @foreach ($sub->authors as $author)
                        <author primary_contact="{{ $author->is_primary ? 'true' : 'false' }}" include_in_browse="true"
                            user_group_ref="Author">
                            <givenname locale="en_US">{{ $author->first_name ?? $author->given_name }}</givenname>
                            <familyname locale="en_US">
                                {{ $author->last_name ?? ($author->family_name ?? $author->first_name) }}</familyname>
                            <email>{{ $author->email }}</email>
                            @if ($author->affiliation)
                                <affiliation locale="en_US">{{ $author->affiliation }}</affiliation>
                            @endif
                            <country>{{ $author->country ?? 'ID' }}</country>
                        </author>
                    @endforeach
                </authors>

                <article_galley xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" approved="false"
                    xsi:schemaLocation="http://pkp.sfu.ca native.xsd">
                    <id type="internal" advice="ignore">1</id>
                    <name locale="en_US">PDF</name>
                    <seq>0</seq>
                    {{-- Link Galley to File ID --}}
                    @if ($sub->files->first())
                        <submission_file_ref id="{{ $sub->files->first()->id }}" revision="1" />
                    @endif
                </article_galley>

                <issue_identification>
                    <volume>{{ $sub->issue->volume ?? 0 }}</volume>
                    <number>{{ $sub->issue->number ?? 0 }}</number>
                    <year>{{ $sub->issue->year ?? date('Y') }}</year>
                    <title locale="en_US">{{ $sub->issue->title ?? '' }}</title>
                </issue_identification>

            </publication>
        </article>
    @endforeach
</articles>
