@props([
    'fileUrl' => '',
    'fileName' => 'Document',
    'height' => '750px',
    'downloadUrl' => null,
])

<div class="pdf-viewer-container" style="height: {{ $height }}; display: flex; flex-direction: column; overflow: hidden;">
    {{-- Toolbar --}}
    <div id="pdf-toolbar" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f9fafb; border-bottom: 1px solid #e5e7eb; flex-shrink: 0;">
        <button id="pdf-prev-page" onclick="pdfViewerPrevPage()" style="padding: 0.25rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">
            ← Prev
        </button>
        <span id="pdf-page-info" style="font-size: 0.875rem; color: #374151;">
            Page <span id="pdf-current-page">1</span> of <span id="pdf-total-pages">-</span>
        </span>
        <button id="pdf-next-page" onclick="pdfViewerNextPage()" style="padding: 0.25rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">
            Next →
        </button>
        <span style="margin: 0 0.25rem; color: #d1d5db;">|</span>
        <button id="pdf-zoom-out" onclick="pdfViewerZoom(-0.25)" style="padding: 0.25rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">
            −
        </button>
        <span id="pdf-zoom-level" style="font-size: 0.875rem; color: #374151; min-width: 3rem; text-align: center;">100%</span>
        <button id="pdf-zoom-in" onclick="pdfViewerZoom(0.25)" style="padding: 0.25rem 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">
            +
        </button>
        <div style="flex: 1;"></div>
        <span style="font-size: 0.75rem; color: #6b7280; margin-right: 0.5rem;">{{ $fileName }}</span>
        @if($downloadUrl)
            <a href="{{ $downloadUrl }}" download style="padding: 0.25rem 0.75rem; background: #2563eb; color: white; border-radius: 0.375rem; text-decoration: none; font-size: 0.875rem;">
                ⬇ Download
            </a>
        @endif
    </div>

    {{-- Canvas container --}}
    <div id="pdf-canvas-container" style="flex: 1; overflow: auto; background: #6b7280; display: flex; justify-content: center; padding: 1rem;">
        <div id="pdf-loading" style="display: flex; align-items: center; justify-content: center; width: 100%; color: white; font-size: 1rem;">
            <svg style="animation: spin 1s linear infinite; width: 1.5rem; height: 1.5rem; margin-right: 0.5rem;" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="white" stroke-width="3" stroke-dasharray="31.4 31.4" stroke-linecap="round"/>
            </svg>
            Loading PDF...
        </div>
        <canvas id="pdf-canvas" style="display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.3);"></canvas>
    </div>

    {{-- DOCX Container --}}
    <div id="docx-container" style="display: none; flex: 1; overflow: auto; background: #f1f5f9; position: relative;">
        <div id="docx-loading" style="display: flex; align-items: center; justify-content: center; height: 100%; width: 100%; color: #475569; font-size: 1rem; padding: 2rem 0;">
            <svg style="animation: spin 1s linear infinite; width: 1.5rem; height: 1.5rem; margin-right: 0.5rem;" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="#475569" stroke-width="3" stroke-dasharray="31.4 31.4" stroke-linecap="round"/>
            </svg>
            Loading Document...
        </div>
        <div id="docx-preview-body" style="display: none; margin: 0 auto; width: 100%; height: 100%;"></div>
    </div>

    {{-- Non-PDF fallback --}}
    <div id="pdf-fallback" style="display: none; flex: 1; padding: 2rem; text-align: center; background: #f9fafb; flex-direction: column; align-items: center; justify-content: center; gap: 1rem;">
        <p style="color: #475569; font-weight: 500; margin-bottom: 0.25rem;">This file type cannot be previewed in the browser.</p>
        <p id="pdf-error-details" style="color: #dc2626; font-size: 0.8125rem; background: #fef2f2; border: 1px solid #fee2e2; padding: 0.5rem 1.25rem; border-radius: 8px; max-width: 500px; display: none; word-break: break-word;"></p>
        @if($downloadUrl)
            <a href="{{ $downloadUrl }}" download style="display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1.5rem; background: #2563eb; color: white; border-radius: 0.375rem; text-decoration: none; max-width: max-content; font-size: 0.875rem;">
                Download File
            </a>
        @endif
    </div>
</div>

<style>
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Google Docs Style for docx-preview */
    #docx-container .docx-wrapper {
        background-color: #f1f5f9 !important; /* Light gray background like Google Docs */
        padding: 3rem 1rem !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        gap: 2rem !important;
        min-height: 100% !important;
        box-sizing: border-box !important;
    }

    #docx-container .docx {
        background-color: #ffffff !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.03) !important; /* Google Docs page shadow */
        border: 1px solid #e2e8f0 !important;
        border-radius: 4px !important;
        margin-bottom: 0 !important;
        padding: 3.5rem 3rem !important; /* Document page padding */
        box-sizing: border-box !important;
        transition: box-shadow 0.2s ease-in-out !important;
        max-width: 850px !important;
        width: 100% !important;
    }

    #docx-container .docx:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08), 0 2px 6px rgba(0, 0, 0, 0.04) !important;
    }

    /* Responsive table inside docx */
    #docx-container .docx table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 1rem 0 !important;
    }

    /* Responsive images */
    #docx-container .docx img {
        max-width: 100% !important;
        height: auto !important;
        object-fit: contain !important;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
(function() {
    const fileUrl = @json($fileUrl);
    const fileName = @json($fileName);
    const ext = fileName.split('.').pop().toLowerCase();

    // Protocol sync helper to prevent Mixed Content Block on HTTPS
    let targetUrl = fileUrl;
    if (window.location.protocol === 'https:' && targetUrl.startsWith('http:')) {
        targetUrl = 'https:' + targetUrl.substring(5);
    }

    function showFallback(err) {
        document.getElementById('pdf-toolbar').style.display = 'none';
        document.getElementById('pdf-canvas-container').style.display = 'none';
        document.getElementById('pdf-loading').style.display = 'none';
        document.getElementById('docx-container').style.display = 'none';
        
        const fallback = document.getElementById('pdf-fallback');
        fallback.style.display = 'flex';
        
        if (err) {
            const errDetails = document.getElementById('pdf-error-details');
            errDetails.textContent = "Error: " + (err.message || String(err));
            errDetails.style.display = 'block';
            console.error('Document preview error:', err);
        }
    }

    // Dynamic script loading helper with deduplication
    function loadScript(src, checkGlobal) {
        if (checkGlobal && window[checkGlobal]) {
            return Promise.resolve();
        }
        const existing = document.querySelector(`script[src="${src}"]`);
        if (existing) {
            if (checkGlobal && window[checkGlobal]) return Promise.resolve();
            return new Promise((resolve) => {
                existing.addEventListener('load', resolve);
                existing.addEventListener('error', resolve);
            });
        }
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.crossOrigin = 'anonymous';
            script.onload = resolve;
            script.onerror = () => reject(new Error('Failed to load script: ' + src));
            document.head.appendChild(script);
        });
    }

    // Dynamic style loading helper
    function loadStyle(href) {
        const existing = document.querySelector(`link[href="${href}"]`);
        if (existing) return Promise.resolve();
        return new Promise((resolve, reject) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.crossOrigin = 'anonymous';
            link.onload = resolve;
            link.onerror = () => reject(new Error('Failed to load style: ' + href));
            document.head.appendChild(link);
        });
    }

    // DOCX Render Logic
    if (ext === 'docx') {
        // Hide PDF elements
        document.getElementById('pdf-toolbar').style.display = 'none';
        document.getElementById('pdf-canvas-container').style.display = 'none';
        document.getElementById('pdf-fallback').style.display = 'none';

        // Show DOCX container
        const docxContainer = document.getElementById('docx-container');
        docxContainer.style.display = 'block';

        // Load dependencies sequentially using Promise chain
        loadScript('https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js', 'JSZip')
            .then(() => {
                return loadStyle('https://cdn.jsdelivr.net/npm/docx-preview@0.4.1/dist/docx-preview.css');
            })
            .then(() => {
                return loadScript('https://cdn.jsdelivr.net/npm/docx-preview@0.4.1/dist/docx-preview.min.js', 'docx');
            })
            .then(() => {
                if (typeof window.docx === 'undefined') {
                    throw new Error('docx-preview library failed to initialize.');
                }
                // Fetch the signed docx URL as arrayBuffer
                return fetch(targetUrl);
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server returned HTTP status ' + response.status + ' for document download.');
                }
                return response.arrayBuffer();
            })
            .then(arrayBuffer => {
                const docxPreviewBody = document.getElementById('docx-preview-body');
                docxPreviewBody.style.display = 'block';

                // Render using docx-preview
                return window.docx.renderAsync(arrayBuffer, docxPreviewBody, null, {
                    className: "docx",
                    inWrapper: true,
                    ignoreWidth: false,
                    ignoreHeight: false,
                    experimental: true
                });
            })
            .then(() => {
                document.getElementById('docx-loading').style.display = 'none';
            })
            .catch(error => {
                showFallback(error);
            });
        return;
    }

    // PDF Render Logic
    if (ext !== 'pdf') {
        showFallback(new Error('Unsupported file extension: ' + ext));
        return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let pdfDoc = null;
    let currentPage = 1;
    let scale = 1.0;
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');

    function renderPage(num) {
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            page.render(renderContext);
            document.getElementById('pdf-current-page').textContent = num;
            document.getElementById('pdf-zoom-level').textContent = Math.round(scale * 100) + '%';
        });
    }

    // Load the PDF
    pdfjsLib.getDocument(targetUrl).promise.then(function(pdf) {
        pdfDoc = pdf;
        document.getElementById('pdf-total-pages').textContent = pdf.numPages;
        document.getElementById('pdf-loading').style.display = 'none';
        canvas.style.display = 'block';
        renderPage(1);
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        showFallback(error);
    });

    // Expose navigation functions globally
    window.pdfViewerPrevPage = function() {
        if (currentPage <= 1) return;
        currentPage--;
        renderPage(currentPage);
    };

    window.pdfViewerNextPage = function() {
        if (pdfDoc && currentPage >= pdfDoc.numPages) return;
        currentPage++;
        renderPage(currentPage);
    };

    window.pdfViewerZoom = function(delta) {
        const newScale = scale + delta;
        if (newScale < 0.5 || newScale > 3.0) return;
        scale = newScale;
        renderPage(currentPage);
    };
})();
</script>
