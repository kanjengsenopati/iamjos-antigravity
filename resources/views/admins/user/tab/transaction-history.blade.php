<form action="{{ route('transaction.export.pdf') }}" method="GET" enctype="multipart/form-data">
    @method('GET')
    <input type="text" name="user_id" hidden value="{{ $user->id }}">
    <div class="d-flex gap2 align-items-center justify-content-end">
        <div>
            <label for="dateRangeTransaction"
                class="btn btn-sm btn-light text-dark fw-600 d-flex align-items-center px-4">
                <input placeholder="Pick date rage" class="bg-transparent text-dark fw-600 cursor-pointer"
                    id="dateRangeTransaction" />
                <i class="ki-duotone ki-calendar fs-1 ms-0 me-0">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                    <span class="path5"></span>
                    <span class="path6"></span>
                </i>
            </label>
            <input type="text" id="transaction_start_date" name="start_date" hidden>
            <input type="text" id="transaction_end_date" name="end_date" hidden>
        </div>
        <div class="ms-2">
            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                <i class="ki-duotone ki-exit-up fs-3">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Export PDF
            </button>
        </div>
    </div>
</form>
<div class="table-responsive">
    <table id="datatable-transaction" class="table table-striped border rounded gy-5 gs-7">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th>Kode Pembayaran</th>
                <th>Metode Pembayaran</th>
                <th>Program</th>
                <th>Pembayaran</th>
                <th>Batas Pembayaran</th>
                <th>Tanggal Pembayaran</th>
                <th>Status</th>
                <th>Note</th>
                <th class="text-center min-w-100px">Aksi</th>
            </tr>
        </thead>
    </table>
</div>
<form id="form-change-amount" method="post" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <div class="modal fade" id="modal-change-amount" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Edit Nominal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mt-3">
                        <!-- Kolom Kiri: Total Pembayaran -->
                        <div class="col-lg-6">
                            <div class="form-group mb-3 row">
                                <label for="pay_amount" class="col-lg-5 col-form-label required fw-semibold fs-6">
                                    Harga Produk
                                </label>
                                <div class="col-lg-7">
                                    <input type="text" name="pay_amount" class="form-control input-money"
                                        id="pay_amount" required>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label for="promo_type" class="col-lg-5 col-form-label fw-semibold fs-6">
                                    Jenis Promo
                                </label>
                                <div class="col-lg-7">
                                    <select name="promo_type" id="promo_type" class="form-select">
                                        <option value="">-- Pilih Jenis Promo --</option>
                                        <option value="nominal">Nominal (Rp)</option>
                                        <option value="percent">Persen (%)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3 row" id="discount_promo_group" style="display: none;">
                                <label for="discount_promo" class="col-lg-5 col-form-label fw-semibold fs-6">
                                    Potongan Promo
                                </label>
                                <div class="col-lg-7">
                                    <input type="number" min="0" name="discount_promo" class="form-control" id="discount_promo">
                                    <small id="discount_promo_info" class="text-muted"></small>
                                </div>
                            </div>
                            <div class="form-group mb-3 row" id="after_discount_group" style="display: none;">
                                <label for="after_discount" class="col-lg-5 col-form-label fw-semibold fs-6">
                                    Total Bayar Setelah Diskon
                                </label>
                                <div class="col-lg-7">
                                    <input type="text" readonly class="form-control" id="after_discount" name="pay_amount_after_discount">
                                </div>
                            </div>
                            <div class="form-group mb-3 row" style="display: none;" id="offline_payment_method_group">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="tag">
                                    <span class="required">Pilih Metode Pembayaran di Tempat</span>
                                </label>
                                <div class="p-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="QRIS" id="QRIS"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="QRIS">
                                            QRIS
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="DEBIT BNI" id="DEBITBNI"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="DEBITBNI">
                                            DEBIT BNI
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="CREDIT BNI" id="CREDITBNI"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="CREDITBNI">
                                            CREDIT BNI
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="QRIS BNI" id="QRISBNI"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="QRISBNI">
                                            QRIS BNI
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="DEBIT OCBC" id="DEBITOCBC"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="DEBITOCBC">
                                            DEBIT OCBC
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="CREDIT OCBC"
                                            id="CREDITOCBC" name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="CREDITOCBC">
                                            CREDIT OCBC
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="QRIS OCBC" id="QRISOCBC"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="QRISOCBC">
                                            QRIS OCBC
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="TRANSFER BCA"
                                            id="TRANSFERBCA" name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="TRANSFERBCA">
                                            TRANSFER BCA
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden input untuk total potongan promo -->
                            <input type="hidden" name="total_discount_promo" id="total_discount_promo">
                        </div>
                        <!-- Kolom Kanan: Lainnya -->
                        <div class="col-lg-6">
                            <div class="form-group mb-3 row">
                                <label for="paid_at" class="col-lg-5 col-form-label required fw-semibold fs-6">
                                    Tanggal Pembayaran
                                </label>
                                <div class="col-lg-7">
                                    <input type="datetime-local" name="paid_at" class="form-control" id="paid_at">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label for="created_at" class="col-lg-5 col-form-label required fw-semibold fs-6">
                                    Tanggal Transaksi
                                </label>
                                <div class="col-lg-7">
                                    <input type="datetime-local" name="created_at" class="form-control" id="created_at">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label for="note" class="col-lg-5 col-form-label required fw-semibold fs-6">
                                    Catatan
                                </label>
                                <div class="col-lg-7">
                                    <textarea type="text" name="note" class="form-control" id="note"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-save fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>
@push('js')
<script>
    $(function() {
            var start = moment().startOf('year');
            var end = moment().endOf('year');

            function cbTransaction(start, end) {
                $('#dateRangeTransaction span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
                var start = start.format('YYYY-MM-DD');
                var end = end.format('YYYY-MM-DD');
                $('#transaction_start_date').val(start);
                $('#transaction_end_date').val(end);
                tableTransaction()
            }

            $('#dateRangeTransaction').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                    'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1,
                        'year').endOf('year')],
                }
            }, cbTransaction);
            cbTransaction(start, end);
        });

        function tableTransaction() {
            var tableTransaction = $('#datatable-transaction').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('transaction.index') }}",
                    type: 'GET',
                    data: {
                        user_id: "{{ $user->id }}",
                        start_date: $("#transaction_start_date").val(),
                        end_date: $("#transaction_end_date").val(),
                    }
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'payment_code',
                        name: 'payment_code'
                    },
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let offline_payment = row.offline_payment_method ? " (" + row.offline_payment_method + ")" : "";
                            let data_payment_method = row.payment_method.type == "AUTO" ? row.payment_method.name : row.payment_method.name + offline_payment;
                            return data_payment_method;
                        }
                    },
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            if (row?.transaction_details && row?.transaction_details.length > 0) {
                                let items = '';
                                for (item of row?.transaction_details ?? []) {
                                    if (item.parentable_type == "App\\Models\\Event") {
                                        items += `<p>${item.parent?.name} (Tiket Event)</p>`;
                                        if (row?.event_ticket_order?.event_ticket_order_detail_groups) {
                                            for (eventTicketOrderDetail of row?.event_ticket_order?.event_ticket_order_detail_groups) {
                                                items += `<li><i>${eventTicketOrderDetail.event_ticket?.name} (${eventTicketOrderDetail.total_quantity}Tiket)</i></li>`;
                                            }
                                        }
                                    } else {
                                        items += `<li><i>${item.parent?.name}</i></li>`;
                                    }
                                }
                                return `<ul>${items}</ul>`;
                            } else if (row?.user_timeoff_history) {
                                let month = Math.round(row?.user_timeoff_history?.duration / 30);
                                return `<ul><li><i>Cuti Membership ${month} Bulan</i></li></ul>`;
                            } else {
                                let name = row?.annual_payment_history?.name;
                                return `<ul><li><i>${name}</i></li></ul>`;
                            }
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return '<small>Diskon Produk: Rp' + row.discount_product.toString()
                                .replace(
                                    /\B(?=(\d{3})+(?!\d))/g, ",") +
                                '<br>Diskon Promo: Rp' + row.discount_promo.toString().replace(
                                    /\B(?=(\d{3})+(?!\d))/g, ",") +
                                '<br>Total Bayar: Rp' + row.pay_amount.toString().replace(
                                    /\B(?=(\d{3})+(?!\d))/g, ",") +
                                '</small></i>' +
                                '<br>' +
                                '<a class="btn-superadmin" onclick=changeAmount("' + row.id +
                                '")><i class="ki-duotone ki-notepad-edit fs-2">' +
                                '<span class="path1"> </span> <span class="path2" > </span><span class="path3"> </span></i></a>'
                        }
                    },
                    {
                        data: 'expiry_time',
                        name: 'expiry_time'
                    },
                    {
                        data: 'paid_at',
                        name: 'paid_at'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            if (data == 'PAID') {
                                return `<span class="badge badge-light-success">LUNAS</span>`;
                            } else if (data == 'PENDING') {
                                return `<span class="badge badge-light-warning">${data}</span>`;
                            } else {
                                return `<span class="badge badge-light-danger">${data}</span>`;
                            }
                        },
                    },
                    {
                        data: 'note',
                        name: 'note'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: -2
                    },
                ]
            });
        }

        $(".input-money").on('keyup', function() {
            var n = parseInt($(this).val().replace(/\D/g, ''), 10) || 0
            if (n > 0) {
                // var value = n.toLocaleString()
                // $(this).val(value);
                var value = n.toLocaleString('en-US')
                $(this).val(value.replace(/\./g, ','));
            } else {
                $(this).val(0);
            }
        });

        function changeAmount(id) {
            $.ajax({
                url: "{{ route('transaction.show', ':id') }}".replace(':id', id),
                success(data) {
                    $('#form-change-amount').attr('action', "{{ route('transaction.change-amount', ':id') }}"
                        .replace(
                            ':id', id))
                    $('#modal-change-amount').modal('show')
                    // Reset total bayar setelah diskon saat modal dibuka
                    $('#after_discount').val('');
                    $('#after_discount_group').css('display', 'none');
                    $('#total_discount_promo').val('');
                    $('#discount_promo_info').text('');
                    $('#promo_type').val('');
                    if (data.data.transaction.discount_promo !== null && data.data.transaction.discount_promo !== undefined && data.data.transaction.discount_promo > 0) {
                        var subTotal = data.data.transaction.pay_amount + data.data.transaction.discount_promo;
                        $('#pay_amount').val(subTotal.toString().replace(
                            /\B(?=(\d{3})+(?!\d))/g, ","))
                        $('#discount_promo_group').css('display', '');
                        $('#discount_promo').val(data.data.transaction.discount_promo);
                    } else {
                        $('#pay_amount').val(data.data.transaction.pay_amount.toString().replace(
                            /\B(?=(\d{3})+(?!\d))/g, ","))
                        $('#discount_promo_group').css('display', 'none');
                        $('#discount_promo').val('');
                    }
                    $('#note').val(data.data.transaction.note);
                    // if (data.data.transaction.paid_at) {
                    // $('#paid_at').val(new Date(data.data.transaction.paid_at).toISOString().slice(0, 16));
                    // }

                    if (data.data.transaction.discount_promo !== null && data.data.transaction.discount_promo !== undefined && data.data.transaction.discount_promo > 0) {
                        var subTotal = data.data.transaction.pay_amount + data.data.transaction.discount_promo;
                        $('#pay_amount').val(subTotal.toString().replace(
                            /\B(?=(\d{3})+(?!\d))/g, ","))
                        $('#discount_promo_group').css('display', '');
                        $('#discount_promo').val(data.data.transaction.discount_promo);
                    } else {
                        $('#pay_amount').val(data.data.transaction.pay_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))
                        $('#discount_promo_group').css('display', 'none');
                        $('#discount_promo').val('');
                    }
                    $('#note').val(data.data.transaction.note);
                    if (data.data.transaction.payment_method.type === "AUTO") {
                        $('#offline_payment_method_group').css('display', 'none');
                        $('input[name="offline_payment_method"]').prop('checked', false);
                    } else {
                        $('#offline_payment_method_group').css('display', '');
                        
                        // Hapus pilihan sebelumnya
                        $('input[name="offline_payment_method"]').prop('checked', false);
                        
                        // Pilih metode pembayaran offline yang sesuai
                        $('input[name="offline_payment_method"]').each(function() {
                            if ($(this).val() === data.data.transaction.offline_payment_method) {
                                $(this).prop('checked', true);
                            }
                        });
                    }
                    
                    function formatDate(date) {
                        return date.getFullYear() + '-' +
                            ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                            ('0' + date.getDate()).slice(-2) + 'T' +
                            ('0' + date.getHours()).slice(-2) + ':' +
                            ('0' + date.getMinutes()).slice(-2);
                    }
                
                    if (data.data.transaction.paid_at) {
                        let paidDate = new Date(data.data.transaction.paid_at);
                        $('#paid_at').val(formatDate(paidDate));
                    }
                    
                    if (data.data.transaction.created_at) {
                        let createdDate;
                        if (typeof data.data.transaction.created_at === 'string') {
                            // Parsing the format "DD-MM-YYYY HH:MM"
                            let parts = data.data.transaction.created_at.split(' ');
                            let dateParts = parts[0].split('-');
                            let timeParts = parts[1].split(':');
                            
                            createdDate = new Date(
                            dateParts[2], // Year
                            dateParts[1] - 1, // Month (0-based)
                            dateParts[0], // Day
                            timeParts[0], // Hour
                            timeParts[1] // Minute
                            );
                        } else {
                            // Assuming it's a timestamp
                            createdDate = new Date(data.data.transaction.created_at * 1000);
                        }

                        $('#created_at').val(formatDate(createdDate));

                    }
                    // if (data.data.transaction.created_at) {
                    // $('#created_at').val(new Date(data.data.transaction.created_at).toISOString().slice(0, 16));
                    // }
                },
                error(err) {

                }
            })
        }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function calculateDiscountPromo() {
            const promoType = document.getElementById('promo_type').value;
            const payAmountInput = document.getElementById('pay_amount');
            const discountPromoInput = document.getElementById('discount_promo');
            const info = document.getElementById('discount_promo_info');
            const afterDiscountInput = document.getElementById('after_discount');
            const totalDiscountPromoInput = document.getElementById('total_discount_promo');
            let payAmount = payAmountInput.value.replace(/[^0-9]/g, '');
            payAmount = payAmount ? parseInt(payAmount) : 0;
            let discount = discountPromoInput.value ? parseFloat(discountPromoInput.value) : 0;
            let afterDiscount = payAmount;
            let totalPotongan = 0;
            // Ubah format Rp menjadi 200,000 (tanpa titik, pakai koma)
            function formatRupiahTanpaRp(angka) {
                // Pastikan angka adalah integer/float
                angka = angka || 0;
                return angka.toLocaleString('en-US');
            }

            if (promoType === 'percent') {
                if (discount > 100) discount = 100;
                let calculated = Math.round((discount / 100) * payAmount);
                info.innerText = 'Diskon: ' + formatRupiahTanpaRp(calculated);
                afterDiscount = payAmount - calculated;
                totalPotongan = calculated;
            } else if (promoType === 'nominal') {
                info.innerText = '';
                afterDiscount = payAmount - discount;
                totalPotongan = discount;
            } else {
                info.innerText = '';
                afterDiscount = payAmount;
                totalPotongan = 0;
            }
            if (promoType !== '') {
                if (afterDiscount < 0) afterDiscount = 0;
                afterDiscountInput.value = formatRupiahTanpaRp(afterDiscount);
            } else {
                afterDiscountInput.value = '';
            }
            // Set nilai total potongan ke input hidden
            totalDiscountPromoInput.value = totalPotongan;
        }
        function togglePromoFields() {
            const promoType = document.getElementById('promo_type').value;
            const discountPromoGroup = document.getElementById('discount_promo_group');
            const afterDiscountGroup = document.getElementById('after_discount_group');
            const discountPromoInput = document.getElementById('discount_promo');
            const afterDiscountInput = document.getElementById('after_discount');
            if (promoType === '') {
                discountPromoGroup.style.display = 'none';
                afterDiscountGroup.style.display = 'none';
                discountPromoInput.value = '';
                afterDiscountInput.value = '';
            } else {
                discountPromoGroup.style.display = '';
                afterDiscountGroup.style.display = '';
            }
        }
        document.getElementById('promo_type').addEventListener('change', function() {
            const promoType = this.value;
            const discountPromoInput = document.getElementById('discount_promo');
            discountPromoInput.value = '';
            if (promoType === 'percent') {
                discountPromoInput.setAttribute('max', '100');
                discountPromoInput.setAttribute('step', '0.01');
                discountPromoInput.placeholder = 'Masukkan persen promo (0-100)';
            } else if (promoType === 'nominal') {
                discountPromoInput.removeAttribute('max');
                discountPromoInput.setAttribute('step', '1');
                discountPromoInput.placeholder = 'Masukkan nominal promo';
            } else {
                discountPromoInput.placeholder = '';
            }
            togglePromoFields();
            calculateDiscountPromo();
        });
        document.getElementById('discount_promo').addEventListener('input', function() {
            calculateDiscountPromo();
        });
        document.getElementById('pay_amount').addEventListener('input', function() {
            calculateDiscountPromo();
        });
        // Inisialisasi tampilan saat pertama kali
        togglePromoFields();
        calculateDiscountPromo();
    });
</script>

@endpush