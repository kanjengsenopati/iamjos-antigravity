<form method="POST" {{-- novalidate="novalidate" --}} class="card edit-profile mb-6" id="section_edit" style="display: none"
  enctype="multipart/form-data" action="{{ route('personal-trainer.profile.update', $pt->id) }}">
  @csrf
  @method('PUT')
  <div class="card-header">
    <!--begin::Card title-->
    <h3 class="card-title align-items-start flex-column">
      <span class="card-label fw-bold fs-3 mb-0">Detail Profil</span>
    </h3>
    <!--end::Card title-->
  </div>
  <div class="card-body">
    <div class="row mb-6">
      <!--begin::Label-->
      <label for="bio" class="col-lg-3 fw-semibold labels mb-2 mb-lg-0">Avatar</label>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <div class="avatar_content">
              <div class="avatar position-relative">
                <img id="img-avatar" src="{{asset($pt->avatar ?? 'assets/media/avatars/150-1.jpg')}}" class="avatar_img"
                  alt="">
                <input type="file" id="edit-avatar" class="d-none" accept="image/*" name="avatar">
                <label for="edit-avatar" class="position-absolute btn-edit">
                  <img src="{{asset('assets/media/icons/pencil.svg')}}" class="icons_filter" alt="">
                </label>
                {{-- <button class="position-absolute btn-hapus">
                  <img src="{{asset('assets/media/icons/icon-trash.svg')}}" alt="">
                </button> --}}
              </div>
              <p>Maksimal ukuran file 10mb menggunakan format png, jpg, atau jpeg</p>
            </div>
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
    <div class="row mb-6">
      <!--begin::Label-->
      <label for="bio" class="col-lg-3 fw-semibold labels mb-2 mb-lg-0">Bio</label>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <textarea id="bio" class="form-control" rows="4" name="bio"
              placeholder="Deskripsikan dirimu secara singkat">{{ $pt->bio ?? ''  }}</textarea>
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
    <div class="row mb-6">
      <!--begin::Label-->
      <label for="name" class="col-lg-3 fw-semibold labels mb-2 mb-lg-0">Nama Lengkap</label>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <input type="text" class="form-control" placeholder="Nama Lengkap" id="name" name="name"
              value="{{ $pt->name ?? '' }}" required />
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
    <div class="row mb-6">
      <!--begin::Label-->
      <label for="phone" class="col-lg-3 fw-semibold labels mb-2 mb-lg-0">Nomor Handphone</label>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <input type="phone" class="form-control" placeholder="+6285xxxxxxxxx" id="phone" name="phone" 
              value="{{ @$pt->phone ?? '' }}" required />
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
    <div class="row mb-6">
      <!--begin::Label-->
      <label for="email" class="col-lg-3 fw-semibold labels mb-2 mb-lg-0">Email</label>
      <!--end::Label-->
      <!--begin::Col-->
      <div class="col-lg-9">
        <!--begin::Row-->
        <div class="row">
          <!--begin::Col-->
          <div class="col-lg-12">
            <input type="email" class="form-control" placeholder="name@mail.com" id="email" name="email"
              value="{{ $pt->email ?? '' }}" required />
          </div>
          <!--end::Col-->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Col-->
    </div>
  </div>
  <div class="card-footer">
    <div class="d-flex gap-2 align-items-center justify-content-end">
      <button type="button" id="cancel-edit" class="btn btn-secondary">Batal</button>
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
  </div>
</form>