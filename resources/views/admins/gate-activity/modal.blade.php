<form action="{{ route('gate-activity.update-reason') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" tabindex="-1" id="modal-update-reason">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Tambah Alasan Check In/Check Out Menggunakan Card/Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="gate_activity_id" id="gate_activity_id">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason/ Alasan</label>
                        <textarea name="reason" id="reason" cols="30" rows="10"
                            class="form-control form-control-lg mb-3 mb-lg-0"
                            placeholder="Alasan Anda"
                            required>{{ @$faq->reason ?? old('reason') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>