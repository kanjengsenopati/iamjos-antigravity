<div class="row gy-5 g-xl-10">
	<div class="col-sm-6 col-xl-3 mb-xl-10">
		<div class="card h-lg-150">
			<div class="card-body d-flex justify-content-between align-items-start flex-column">
				<div class="m-0">
					<i class="ki-duotone ki-chart-simple fs-2hx text-gray-600">
						<span class="path1"></span>
						<span class="path2"></span>
						<span class="path3"></span>
						<span class="path4"></span>
					</i>
				</div>
				<div class="flex-column mt-7 mb-2">
					<div class="m-0">
						<span class="fw-semibold fs-6 text-gray-700">Rating Membership</span>
					</div>
					<span class="badge badge-light-success fw-semibold fs-3x text-gray-800 lh-1 ls-n2 mt-2">{{ round($membership->avg('star'), 1) ?? "-" }}</span>
				</div>
				<p>Dari <span class="badge badge-light-warning fs-base">{{ $membership->count() }}</span> User</p>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-xl-3 mb-xl-10">
		<div class="card h-lg-150">
			<div class="card-body d-flex justify-content-between align-items-start flex-column">
				<div class="m-0">
					<i class="ki-duotone ki-chart-simple fs-2hx text-gray-600">
						<span class="path1"></span>
						<span class="path2"></span>
						<span class="path3"></span>
						<span class="path4"></span>
					</i>
				</div>
				<div class="flex-column mt-7 mb-2">
					<div class="m-0">
						<span class="fw-semibold fs-6 text-gray-700">Rating Kelas</span>
					</div>
					<span class="badge badge-light-success fw-semibold fs-3x text-gray-800 lh-1 ls-n2 mt-2">{{ round($class->avg('star'), 1) ?? "-" }}</span>
				</div>
				<p>Dari <span class="badge badge-light-warning fs-base">{{ $class->count() }}</span> User</p>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-xl-3 mb-xl-10">
		<div class="card h-lg-150">
			<div class="card-body d-flex justify-content-between align-items-start flex-column">
				<div class="m-0">
					<i class="ki-duotone ki-chart-simple fs-2hx text-gray-600">
						<span class="path1"></span>
						<span class="path2"></span>
						<span class="path3"></span>
						<span class="path4"></span>
					</i>
				</div>
				<div class="flex-column mt-7 mb-2">
					<div class="m-0">
						<span class="fw-semibold fs-6 text-gray-700">Rating Coach Plus</span>
					</div>
					<span class="badge badge-light-success fw-semibold fs-3x text-gray-800 lh-1 ls-n2 mt-2">{{ round($pt_plus->avg('star'), 1) ?? "-" }}</span>
				</div>
				<p>Dari <span class="badge badge-light-warning fs-base">{{ $pt_plus->count() }}</span> User</p>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-xl-3 mb-xl-10">
		<div class="card h-lg-150">
			<div class="card-body d-flex justify-content-between align-items-start flex-column">
				<div class="m-0">
					<i class="ki-duotone ki-chart-simple fs-2hx text-gray-600">
						<span class="path1"></span>
						<span class="path2"></span>
						<span class="path3"></span>
						<span class="path4"></span>
					</i>
				</div>
				<div class="flex-column mt-7 mb-2">
					<div class="m-0">
						<span class="fw-semibold fs-6 text-gray-700">Rating Coach</span>
					</div>
					<span class="badge badge-light-success fw-semibold fs-3x text-gray-800 lh-1 ls-n2 mt-2">{{ round($pt->avg('star'), 1) ?? "-" }}</span>
				</div>
				<p>Dari <span class="badge badge-light-warning fs-base">{{ $pt->count() }}</span> User</p>
			</div>
		</div>
	</div>
</div>
<div class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
    <h3 class="card-title align-items-start flex-column">
        <span class="card-label fw-bold fs-3 mb-1">Daftar Review</span>
    </h3>
</div>
<div class="table-responsive">
    <table id="datatable-all-review" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
        <thead>
            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th style="width: 20%">Nama Layanan</th>
                <th style="width: 20%">Nama User</th>
                <th style="width: 15%">Tanggal Review</th>
                <th style="width: 10%">Rating</th>
                <th style="width: 30%">Ulasan</th>
            </tr>
        </thead>
        <tbody class="text-dark fw-semibold"></tbody>
    </table>
</div>