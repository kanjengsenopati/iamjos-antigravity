<div class="card edit-profile mb-6">
  <div class="card-body">
    <div class="row justify-content-between">
      <div class="col-12 col-xl-8">
        <div class="d-flex flex-wrap gap-4 gap-md-12 gap-lg-6 gap-xxl-4 justify-content-start main-profile">
          <img src="{{asset($pt->thumbnail ?? 'assets/media/avatars/150-1.jpg')}}" class="img_avatar d-none d-md-block"
            alt="">
          <div class="d-flex flex-column justify-content-between">
            <div>
              <h6 class="name mb-6 d-none d-md-block mb-lg-4">{{ $pt->name ?? '' }}</h6>
              <div class="d-flex flex-column flex-xxl-row gap-5 mb-6 mb-xxl-0 align-items-start">
                {{-- <div class="d-flex gap-2 align-items-center">
                  <img src="{{ asset('assets/media/icons/email.svg') }}" alt="">
                  <p class="color-gray-5">loganweav</p>
                </div> --}}
                <div class="d-flex gap-2 align-items-center">
                  <img src="{{ asset('assets/media/icons/phone.svg') }}" class="icons_filter" alt="">
                  <p class="color-gray-5">{{ $pt->phone ?? 'Belum ditambahkan' }}</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                  <img src="{{ asset('assets/media/icons/mail.svg') }}" class="icons_filter" alt="">
                  <p class="color-gray-5">{{ $pt->email ?? '' }}</p>
                </div>
              </div>
            </div>
            <div class="d-flex flex-wrap gap-5">
              <div class="border border-gray-300 border-dashed rounded py-3 px-4 mb-3 box">
                <div class="d-flex gap-3 align-items-center">
                  <img src="{{ asset('assets/media/icons/experience.svg') }}" alt="">
                  <h6 class="mb-1">{{ $pt->experience_year ?? 0 }}</h6>
                </div>
                <p>Tahun Pengalaman</p>
              </div>
              <div class="border border-gray-300 border-dashed rounded py-3 px-4 mb-3 box">
                <div class="d-flex gap-3 align-items-center">
                  <img src="{{ asset('assets/media/icons/total_client.svg') }}" alt="">
                  <h6 class="mb-1">{{ $totalMemberFinish ?? 0 }}</h6>
                </div>
                <p>Total Client</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-4 order-first order-xl-last mb-6 mb-xl-0">
        <div class="text-end">
          <button data-bs-toggle="modal" data-bs-target="#kt_modal_id_card" class="btn btn-primary mb-6 ">
            <div class="d-flex gap-2 align-items-center">
              <img src="{{asset('assets/media/icons/idcard.svg')}}" alt="">
              <span>ID Card</span>
            </div>
          </button>
        </div>
        <div class="d-flex flex-column align-items-center gap-4 justify-content-center d-md-none mb-6">
          <img src="{{asset('assets/media/avatars/150-1.jpg')}}" class="img_avatar" alt="">
          <h6 class="name mb-6 mb-lg-0">Logan Weaver</h6>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <p class="color-gray-5">Kelengkapan profil</p>
          <p class="text-primary fw-bold">{{ $profileCompletion ?? 0 }}%</p>
        </div>
        <div class="progress h-7px bg-gray-8 mt-3">
          <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $profileCompletion ?? 0 }}%"
            aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
      </div>
    </div>
  </div>
</div>