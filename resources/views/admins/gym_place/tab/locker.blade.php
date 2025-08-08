@push('css')
<style>
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        background-color: transparent !important;
        color: var(--bs-primary) !important;
        font-weight: 500;
        border-bottom: 2px solid var(--bs-primary);
        border-radius: 0;
    }

    .nav-pills .nav-link {
        border-radius: 0;
        color: var(--bs-gray-600) !important;
        padding-bottom: 1rem !important;
        width: 100% !important;
        font-size: 1.25rem !important;
    }

    .nav-pills .nav-item {
        width: 25% !important;
        min-width: 200px !important;
        max-width: 250px !important;
    }


    /* box loker */
    .box_loker {
        width: 100%;
        height: 100%;
        border: 1px solid var(--bs-gray-300);
        border-radius: 8px;
        padding: 2rem 1rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .box_loker .action {
        display: flex;
        gap: 1.5rem;
        justify-content: flex-end !important;
        align-items: center !important;
        width: 100% !important;
        margin-top: 1rem !important;
    }

    @media only screen and (min-width: 992px) {
        .nav-pills .nav-link {
            font-size: 1.125rem !important;
        }

        .box_loker h4 {
            font-size: 1.125rem !important;
        }

        .nav-pills .nav-item {
            width: 25% !important;
            min-width: 200px !important;
            max-width: 250px !important;
        }
    }

    @media only screen and (max-width: 991.98px) {
        .box_loker h4 {
            font-size: 1rem !important;
        }

        .box_loker {
            padding: 1.5rem !important;
        }

        .box_loker .action {
            margin-top: .5rem !important;
        }
    }
</style>
@endpush
<div class="border-0 pt-6 d-flex mb-3 gap-4 flex-wrap justify-content-between align-items-center">
    <h4>List Loker</h4>
    <div class="d-flex flex-wrap gap-2">
        <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
            href="{{ route('locker.create', ['gym_place_id' => $gymPlace->id]) }}">
            <i class="ki-duotone ki-plus fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>Loker
        </a>
        {{-- <a type="button" class="btn btn-sm btn-primary text-nowrap" onclick="importMembership()">
            <i class="ki-duotone ki-exit-down fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            Import
        </a>
        <a href="{{route('membership.export-excel', $gymPlace->id)}}" class="btn btn-primary btn-sm text-nowrap">
            <i class="ki-duotone ki-exit-up fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            Export Excel</a> --}}
    </div>
</div>
<div>
    <div class="nav nav-pills mb-3 w-100 d-flex justify-content-center" id="pills-tab">
        <div class="nav-item">
            <button class="nav-link active" id="pills-male-tab" data-bs-toggle="pill" data-bs-target="#pills-male"
                type="button" role="tab" aria-controls="pills-male" aria-selected="true">Loker Laki-Laki</button>
        </div>
        <div class="nav-item">
            <button class="nav-link" id="pills-female-tab" data-bs-toggle="pill" data-bs-target="#pills-female"
                type="button" role="tab" aria-controls="pills-female" aria-selected="false">Loker Perempuan</button>
        </div>
    </div>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-male" aria-labelledby="pills-male-tab">
            <div id="locker" class="row mt-5 pt-5">
                <div id="loading-animation-male" class="d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-primary">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-female" aria-labelledby="pills-female-tab">
            <div id="locker-list-female" class="row mt-5 pt-5">
                <div id="loading-animation-female" class="d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-primary">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function showLoading() {
            $('#loading-animation').show();
        }

        // Define function to hide loading animation
        function hideLoading() {
            // Hide loading animation here, for example:
            $('#loading-animation').hide();
        }

        $(document).ready(function() {
        // Add event listener for checkbox change
        $(document).on('change', 'input[type="checkbox"]', function() {
        var lockerId = $(this).attr('id').replace('flexSwitchCheckDefault', ''); // Extract locker ID
        var isChecked = $(this).prop('checked'); // Get whether the checkbox is checked or not
        changeLockerStatus(lockerId, isChecked); // Call function to send AJAX request to change locker status
        });
        
        // Function to send AJAX request to change locker status
        function changeLockerStatus(lockerId, isChecked) {
        $.ajax({
        url: "{{ route('locker.change-status') }}",
        type: 'POST',
        data: {
        locker_id: lockerId,
        is_checked: isChecked,
        },
        success: function(response) {
        // Handle success response if needed
        // For example, update UI to reflect the new locker status
        },
        error: function(xhr, status, error) {
        // Handle error response if needed
        }
        });
        }
        
        // Function to load locker data
        function loadLockerData() {
        showLoading(); // Show loading animation before sending Ajax request
        $.ajax({
        url: "{{ route('locker.index') }}",
        type: 'GET',
        data: {
        gym_place_id: '{{$gymPlace->id}}',
        },
        success: function(response) {
        $('#locker').empty(); // Clear previous locker list
        $('#locker-list-female').empty(); // Clear previous locker list
        
        response.data.forEach(function(item) {
        const checkedAttribute = item.is_available ? 'checked' : '';
        var lockerHTML = `
        <div class="col-lg-3 mb-5">
            <div class="box_loker">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault${item.id}"
                        ${checkedAttribute}>
                </div>
                <h4 class="mt-3" id="loker_name">${item.name}</h4>
                <div class="action">
                    
                    <a href="/locker/${item.id}/edit" type="button" class="d-block">
                        <i class="ki-duotone ki-notepad-edit fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </a>
                    <a type="button" class="d-block" data-id="formDelete${item.id}" id="btnDelete${item.id}">
                        <i class="ki-duotone ki-basket fs-2" data-id="formDelete${item.id}">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3" data-id="formDelete${item.id}"></span>
                        </i>
                    </a>
                    <form class="d-none" id="formDelete${item.id}" action="/locker/${item.id}" method="post">
                        @csrf
                        @method('delete')
                    </form>
                </div>
            </div>
        </div>
        `;

        document.querySelectorAll('[data-id^="formDelete"]').forEach(function(button) {
        button.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default action (e.g., following the href)
        var formId = this.getAttribute('data-id');
        document.getElementById(formId).submit(); // Submit the associated form
        });
        });
        
        // Append locker HTML to the appropriate locker list based on gender
        if (item.gender === 'MALE') {
        $('#locker').append(lockerHTML);
        } else if (item.gender === 'FEMALE') {
        $('#locker-list-female').append(lockerHTML);
        }
        });
        },
        complete: function() {
        hideLoading(); // Hide loading animation after receiving response
        }
        });
        }
        
        // Call function to load locker data when the document is ready
        loadLockerData();
        });
</script>
@endpush