<h3>Informasi Coach</h3>
<img src="{{ asset($personalTrainer->thumbnail) }}" class="img w-25 mb-2 img-thumbnail" alt="">
<div class="row mb-4">
    <div class="col-sm-3">
        <label class="text-muted">Nama</label>
        <p class="text-label">{{ $personalTrainer->name }}</p>
    </div>
    <div class="col-sm-3">
        <label class="text-muted">No.Telepon</label>
        <p class="text-label">{{ $personalTrainer->phone }}</p>
    </div>
    <div class="col-sm-3">
        <label class="text-muted">Email</label>
        <p class="text-label">{{ $personalTrainer->email }}</p>
    </div>
    <div class="col-sm-3">
        <label class="text-muted">Pengalaman Kerja</label>
        <p class="text-label">Sejak {{ $personalTrainer->start_experience_year }}
            ({{ $personalTrainer->experience_year }} Tahun)</p>
    </div>
</div>
<div class="row mb-4">
    <div class="col-sm-3">
        <label class="text-muted">Keahlian</label>
        @foreach ($personalTrainer->personal_trainer_skills ?? [] as $personalTrainerSkill)
            <p class="mb-0">&nbsp;<i class="fa fa-check"></i>{{ $personalTrainerSkill->name }}</p>
        @endforeach
    </div>
    <div class="col-sm-3">
        <label class="text-muted">Benefit</label>
        @foreach ($personalTrainer->personal_trainer_benefits ?? [] as $personalTrainerBenefit)
            <p class="mb-0">&nbsp;<i class="fa fa-check"></i>{{ $personalTrainerBenefit->name }}</p>
        @endforeach
    </div>
    <div class="col-sm-3">
        <label class="text-muted">Level</label>
        <p class="text-label">{{ $personalTrainer->personal_trainer_level?->name }}</p>
    </div>
</div>
<div class="row mb-4">
    <div class="col-sm-8">
        <label class="text-muted">Deskripsi</label>
        <p class="text-label">{!! $personalTrainer->description !!}</p>
    </div>
</div>

@push('js')
@endpush
