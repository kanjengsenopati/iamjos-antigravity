@php $title = __('Author Guidelines'); @endphp

<x-layouts.public :journal="$journal" :settings="$settings" :title="$title">

    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Author Guidelines') }}</h1>

            <div class="prose prose-lg max-w-none">
                <h2>{{ __('Submission Requirements') }}</h2>
                <ul>
                    <li>{{ __('Manuscripts must be original and not under consideration elsewhere') }}</li>
                    <li>{{ __('File format: Microsoft Word (.doc, .docx) or PDF') }}</li>
                    <li>{{ __('Maximum file size: 20MB') }}</li>
                    <li>{{ __('Language: English or Indonesian') }}</li>
                </ul>

                <h2>{{ __('Manuscript Structure') }}</h2>
                <ol>
                    <li><strong>{{ __('Title:') }}</strong> {{ __('Clear and concise') }}</li>
                    <li><strong>{{ __('Abstract:') }}</strong> {{ __('150-300 words') }}</li>
                    <li><strong>{{ __('Keywords:') }}</strong> {{ __('3-5 keywords') }}</li>
                    <li><strong>{{ __('Introduction') }}</strong></li>
                    <li><strong>{{ __('Methods') }}</strong></li>
                    <li><strong>{{ __('Results') }}</strong></li>
                    <li><strong>{{ __('Discussion') }}</strong></li>
                    <li><strong>{{ __('Conclusion') }}</strong></li>
                    <li><strong>{{ __('References') }}</strong></li>
                </ol>

                <h2>{{ __('References Style') }}</h2>
                <p>{{ __('Use APA 7th edition citation style.') }}</p>
            </div>

            <div class="mt-8">
                <a href="{{ route('login') }}"
                    class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700">
                    {{ __('Submit Manuscript') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
