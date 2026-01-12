<x-public-layout :journal="$journal">
    @php $title = 'About'; @endphp

    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">About the Journal</h1>

            <div class="prose prose-lg max-w-none">
                <h2>Journal Overview</h2>
                <p>{{ $journal->description ?? 'IAMJOS is an open-access academic journal.' }}</p>

                <h2>Peer Review Process</h2>
                <p>All manuscripts undergo double-blind peer review by at least two experts.</p>

                <h2>Open Access Policy</h2>
                <p>This journal provides immediate open access under Creative Commons licenses.</p>
            </div>

            <div class="mt-12 bg-gray-50 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Journal Information</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500">Publisher</dt>
                        <dd class="font-medium">{{ $journal->publisher ?? 'IAMJOS' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Access</dt>
                        <dd class="font-medium">Open Access</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</x-public-layout>
