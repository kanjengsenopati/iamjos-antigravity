<div id="section_detail" class="card edit-profile mb-6">
  <div class="card-header">
    <!--begin::Card title-->
    <h3 class="card-title align-items-start flex-column">
      <span class="card-label fw-bold fs-3 mb-0">Detail Profil</span>
    </h3>
    <!--end::Card title-->
    <!--begin::Card toolbar-->
    <div class="card-toolbar">
      <!--begin::Button-->
      <button type="button" class="btn btn-primary" id="btn_edit_profile">
        Edit Profil
      </button>
      <!--end::Button-->
    </div>
    <!--end::Card toolbar-->
  </div>
  <div class="card-body">
    <div class="row mb-6">
      <!--begin::Label-->
      <p class="col-lg-3 fw-semibold labels">Level</p>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <p class="fw-bold"><span class="badge badge-status">{{ $pt->personal_trainer_level?->name ?? '' }}</span>
            </p>
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
    <div class="row mb-6">
      <!--begin::Label-->
      <p class="col-lg-3 fw-semibold labels">Bio</p>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <p class="fw-bold">{{ $pt->bio ?? '' }}</p>
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
    <div class="row mb-6">
      <!--begin::Label-->
      <p class="col-lg-3 fw-semibold labels">Nama Lengkap</p>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <p>{{ $pt->name ?? '' }}</p>
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
    <div class="row mb-6">
      <!--begin::Label-->
      <p class="col-lg-3 fw-semibold labels">Nomor Handphone</p>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12 d-flex gap-3">
            <p>{{ $pt->phone ?? 'Belum diisi' }}</p>
            {{-- <span class="badge badge-status">Terverfikasi</span> --}}
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
    <div class="row mb-6">
      <!--begin::Label-->
      <p class="col-lg-3 fw-semibold labels">Email</p>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <p>{{ $pt->email ?? '' }}</p>
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
  </div>
</div>