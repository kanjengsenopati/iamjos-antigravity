<div class="modal fade" id="kt_modal_id_card" tabindex="-1" aria-hidden="true">
  <!--begin::Modal dialog-->
  <div class="modal-dialog modal-dialog-centered">
    <!--begin::Modal content-->
    <div class="modal-content rounded">
      <!--begin::Modal header-->
      <div class="modal-header pb-0 border-0 justify-content-end">
        <!--begin::Close-->
        <button class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
          <img src="{{asset('assets/media/icons/cancel.svg')}}" class="cancel-img" alt="">
        </button>
        <!--end::Close-->
      </div>
      <!--begin::Modal header-->
      <!--begin::Modal body-->
      <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
        <div class="d-flex justify-content-center">
          <div class="qr-code position-relative">
            <div class="p-4 bg-white">
              <img class="img_qr" src="data:image/png;base64, {!! base64_encode(QrCode::format('png')
              ->merge('assets/media/logos/logo.png', .5, true)
              ->errorCorrection('M')->size(256)
              ->generate($pt->qr_code)) !!} " alt="Qr Code Personal Trainer" />
            </div>
          </div>
        </div>
        <div class="text-center">
          <p class="badge badge-light-success gray-10">Scan me</p>
          <h4>{{ $pt->name ?? '' }}</h4>
          <p>{{ $pt->email ?? '' }}</p>
        </div>
      </div>
      <!--end::Modal body-->
    </div>
    <!--end::Modal content-->
  </div>
  <!--end::Modal dialog-->
</div>