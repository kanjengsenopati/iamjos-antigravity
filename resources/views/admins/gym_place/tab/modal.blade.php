<form action="{{ route('membership.import') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-import-membership" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Import Tambah / Update Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="gym_place_id" value="{{ $gymPlace->id }}" hidden>
                    <input type="file" class="form-control" name="file" accept=".xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a class="btn btn-sm btn-primary"
                        href="{{ route('membership.export-excel', $gymPlace->id . '?is_template_import=true') }}">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Download Template
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ki-duotone ki-exit-down fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Import Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<form action="{{ route('gym-class-bundling.import') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-import-gym-class-bundling" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Import Tambah / Update Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="gym_place_id" value="{{ $gymPlace->id }}" hidden>
                    <input type="file" class="form-control" name="file" accept=".xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a class="btn btn-sm d-flex gap-2 align-items-center btn-primary"
                        href="{{ route('gym-class-bundling.export-excel', $gymPlace->id . '?is_template_import=true') }}">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Download Template
                    </a>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-down fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Import Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</form>


<form action="{{ route('gym-class.import') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-import-gym-class" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Import Tambah / Update Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="gym_place_id" value="{{ $gymPlace->id }}" hidden>
                    <input type="file" class="form-control" name="file" accept=".xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal">Batal</button>
                    <a class="btn btn-sm d-flex gap-2 align-items-center btn-download"
                        href="{{ route('gym-class.export-excel', $gymPlace->id . '?is_template_import=true') }}">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Download Template
                    </a>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-import">
                        <i class="ki-duotone ki-exit-down fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Import Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</form>


<div class="modal fade" id="modal-import-personal-trainer" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title-import-personal-trainer">Import Tambah / Update Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="loading-import-pt" class="d-none">Menunggu proses import...</p>
                <input type="text" name="gym_place_id" value="{{ $gymPlace->id }}" hidden>
                <input type="file" class="form-control" id="file_import_personal_trainer" name="file"
                    accept=".xlsx" required>
            </div>
            <input type="text" name="is_force_import" id="is_force_import" hidden>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a class="btn btn-sm d-flex gap-2 align-items-center btn-primary"
                    href="{{ route('personal-trainer.export-excel', $gymPlace->id . '?is_template_import=true') }}">
                    <i class="ki-duotone ki-exit-up fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Download Template
                </a>
                <button type="button" onclick="doImportPersonalTrainer()"
                    class="btn btn-sm d-flex gap-2 align-items-center btn-primary" id="btn-import-pt">
                    <i class="ki-duotone ki-exit-down fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Import Sekarang</button>
            </div>
        </div>
    </div>
</div>





@push('js')
    <script>
        const body = document.body;

        function importMembership() {
            $("#modal-import-membership").modal("show")
            if (body.classList.contains('modal-open')) {
                body.style.zoom = 1
            }
        }

        function importGymClassBundling() {
            $("#modal-import-gym-class-bundling").modal("show")
            if (body.classList.contains('modal-open')) {
                body.style.zoom = 1
            }
        }

        function importGymClass() {
            $("#modal-import-gym-class").modal("show")
            if (body.classList.contains('modal-open')) {
                body.style.zoom = 1
            }
        }

        function importPersonalTrainer() {
            $("#modal-import-personal-trainer").modal("show")
            if (body.classList.contains('modal-open')) {
                body.style.zoom = 1
            }
        }


        $('.btn-close').on('click', function() {
            setTimeout(() => {

            }, 500);
        })
    </script>
@endpush
