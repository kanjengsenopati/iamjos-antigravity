<x-public-layout :journal="$journal">
    @php $title = 'Editorial Team'; @endphp

    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Editorial Team</h1>

            <div class="space-y-8">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Editor-in-Chief</h2>
                    <div class="bg-gray-50 rounded-xl p-6">
                        <p class="font-medium text-gray-900">Chief Editor</p>
                        <p class="text-sm text-gray-500">IAMJOS Publishing</p>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Editorial Board</h2>
                    <p class="text-gray-500">Editorial board members will be listed here.</p>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Reviewers</h2>
                    <p class="text-gray-500">We thank our dedicated peer reviewers for their contributions.</p>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
