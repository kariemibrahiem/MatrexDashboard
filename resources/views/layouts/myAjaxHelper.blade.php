<script>
    var loader = `
			<div id="skeletonLoader" class="skeleton-loader">
    <div class="loader-header">
        <div class="skeleton skeleton-text"></div>
    </div>
    <div class="loader-body">
        <div class="skeleton skeleton-textarea"></div>
    </div>

</div>
        `;


    // Show Data Using YAJRA
    async function showData(routeOfShow, columns) {
        $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: routeOfShow,
            columns: columns,
            order: [
                [0, "DESC"]
            ],
            createdRow: function (row, data, dataIndex) {
                $(row).attr('data-id', data.id);
                $(row).addClass('clickable-row');
            },
            "language": {
                // your language settings...
            },
            buttons: [
                // your buttons config...
            ]
        });

        // $('#dataTable tbody').on('mouseenter', 'tr', function (e) {
        //     $(this).css('cursor', 'pointer');
        // });
        // Row click event
        {{--$('#dataTable tbody').on('click', 'td', function (e) {--}}

        {{--    let colIndex = $(this).index();--}}

        {{--    if (colIndex >= 0 && colIndex <= 4) {--}}
        {{--        if ($(e.target).is('input, button, a, .delete-checkbox, .editBtn, .statusBtn')) {--}}
        {{--            return;--}}
        {{--        }--}}

        {{--        // Get the row ID and redirect--}}
        {{--        let row = $(this).closest('tr');--}}
        {{--        let id = row.data('id');--}}
        {{--        if (id) {--}}
        {{--            window.location.href = `{{ route($route.'.show', ':id') }}`.replace(':id', id);--}}
        {{--        }--}}
        {{--    }--}}
        {{--});--}}
    }

    function deleteScript(routeTemplate) {
        $(document).ready(function() {
            // Configure modal event listeners
            $('#delete_modal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var title = button.data('title');
                var modal = $(this);
                modal.find('.modal-body #delete_id').val(id);
                modal.find('.modal-body #title').text(title);
            });

            $(document).on('click', '#delete_btn', function() {
                var id = $("#delete_id").val();
                var routeOfDelete = routeTemplate.replace(':id', id);

                $.ajax({
                    type: 'DELETE',
                    url: routeOfDelete,
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'id': id
                    },
                    success: function(data) {
                        $("#dismiss_delete_modal")[0].click();
                        if (data.status === 200) {
                            $('#dataTable').DataTable().ajax.reload();
                            toastr.success(data.message);
                        } else {
                            toastr.error(data.message);
                        }
                    }
                });
            });
        });
    }

    // show Add Modal
    function showAddModal(routeOfShow) {
        $(document).on('click', '.addBtn', function() {
            $('#modal-body').html(loader)
            $('#editOrCreate').modal('show')
            setTimeout(function() {
                $('#modal-body').load(routeOfShow)
            }, 250)
        });
    }

    function addScript() {
        $(document).on('submit', 'Form#addForm', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var url = $('#addForm').attr('action');
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#addButton').html('<span class="spinner-border spinner-border-sm mr-2" ' +
                        ' ></span> <span style="margin-left: 4px;">{{ __('loading...') }}</span>'
                    ).attr('disabled', true);
                },
                success: function(data) {
                    if (data.status == 200) {

                        window.location.reload();
                        console.log('test');
                        $('#editOrCreate').modal('hide');
                        $('#dataTable').DataTable().ajax.reload();
                        toastr.success('{{ __('added_successfully') }}');
                    } else if (data.status == 405) {
                        toastr.error(data.mymessage);
                    } else
                        toastr.error('{{ __('something_went_wrong') }}');
                    $('#addButton').html(`{{ __('add') }}`).attr('disabled', false);
                    $('#editOrCreate').modal('hide')
                },
                error: function(data) {
                    if (data.status === 500) {
                        toastr.error('');
                    } else if (data.status === 422) {
                        var errors = $.parseJSON(data.responseText);
                        $.each(errors, function(key, value) {
                            if ($.isPlainObject(value)) {
                                $.each(value, function(key, value) {
                                    toastr.error(value, '{{ __('error') }}');
                                });
                            }
                        });
                    } else
                        toastr.error('{{ __('something_went_wrong') }}');
                    $('#addButton').html(`اضافة`).attr('disabled', false);
                }, //end error method

                cache: false,
                contentType: false,
                processData: false
            });
        });
    }

    function showEditModal(routeOfEdit) {
        $(document).on('click', '.editBtn', function() {
            var id = $(this).data('id')
            var url = routeOfEdit;
            url = url.replace(':id', id)
            $('#modal-body').html(loader)
            $('#editOrCreate').modal('show')

            setTimeout(function() {
                $('#modal-body').load(url)
            }, 500)
        })
    }

    function showUpdateProfileImage(routeOfEdit) {
        $(document).on('click', '.updateProfileImageBtn', function() {
            var id = $(this).data('id')
            var url = routeOfEdit;
            url = url.replace(':id', id)
            $('#modal-body').html(loader)
            $('#updateProfileImage').modal('show')

            setTimeout(function() {
                $('#modal-body').load(url)
            }, 500)
        })
    }



    function editScript() {
        $(document).on('submit', 'Form#updateForm', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var url = $('#updateForm').attr('action');
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#updateButton').html('<span class="spinner-border spinner-border-sm mr-2" ' +
                        ' ></span> <span style="margin-left: 4px;">{{ __('loading...') }}</span>'
                    ).attr('disabled', true);
                },
                success: function(data) {
                    $('#updateButton').html(`{{ __('update') }}`).attr('disabled', false);
                    if (data.status == 200) {
                        window.location.reload();
                        $('#editOrCreate').modal('hide')
                        $('#editOrCreate').on('hidden.bs.modal', function () {
                            $('#modal-body').html('');
                        });
                        toastr.success('{{ __('updated_successfully') }}');
                        $('#editOrCreate').on('hidden.bs.modal', function () {
                            $('#modal-body').html(''); // Clear the modal content
                        });
                    } else
                        toastr.error('{{ __('something_went_wrong') }}');


                    $('#editOrCreate').modal('hide');
                    if (data.redirect){
                        setTimeout(function() {
                            window.location.href = data.redirect;
                        }, 1000);
                    }else{
                        $('#dataTable').DataTable().ajax.reload();
                    }
                },
                error: function(data) {
                    if (data.status === 500) {
                        toastr.error('{{ __('something_went_wrong') }}');
                    } else if (data.status === 422) {
                        var errors = $.parseJSON(data.responseText);
                        $.each(errors, function(key, value) {
                            if ($.isPlainObject(value)) {
                                $.each(value, function(key, value) {
                                    toastr.error(value, '{{ __('error') }}');
                                });
                            }
                        });
                    } else
                        toastr.error('{{ __('something_went_wrong') }}');
                    $('#updateButton').html(`{{ __('update') }}`).attr('disabled', false);
                }, //end error method

                cache: false,
                contentType: false,
                processData: false
            });
        });
    }


    function editOwners() {
        $(document).on('submit', 'Form#editOwnersForm', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var url = $('#updateForm').attr('action');
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#updateButton').html('<span class="spinner-border spinner-border-sm mr-2" ' +
                        ' ></span> <span style="margin-left: 4px;">{{ __('loading...') }}</span>'
                    ).attr('disabled', true);
                },
                success: function(data) {
                    $('#updateButton').html(`{{ __('update') }}`).attr('disabled', false);
                    if (data.status == 200) {
                        $('#editOrCreate').modal('hide')
                        $('#editOrCreate').on('hidden.bs.modal', function () {
                            $('#modal-body').html('');
                        });
                        toastr.success('{{ __('updated_successfully') }}');
                        $('#editOrCreate').on('hidden.bs.modal', function () {
                            $('#modal-body').html(''); // Clear the modal content
                        });
                    } else
                        toastr.error('{{ __('something_went_wrong') }}');


                    $('#editOrCreate').modal('hide');
                    if (data.redirect){
                        setTimeout(function() {
                            window.location.href = data.redirect;
                        }, 1000);
                    }else{
                        $('#dataTable').DataTable().ajax.reload();
                    }
                },
                error: function(data) {
                    if (data.status === 500) {
                        toastr.error('{{ __('something_went_wrong') }}');
                    } else if (data.status === 422) {
                        var errors = $.parseJSON(data.responseText);
                        $.each(errors, function(key, value) {
                            if ($.isPlainObject(value)) {
                                $.each(value, function(key, value) {
                                    toastr.error(value, '{{ __('error') }}');
                                });
                            }
                        });
                    } else
                        toastr.error('{{ __('something_went_wrong') }}');
                    $('#updateButton').html(`{{ __('update') }}`).attr('disabled', false);
                }, //end error method

                cache: false,
                contentType: false,
                processData: false
            });
        });
    }

    function deleteSelected(route) {
        $(document).ready(function() {
            $('#bulk-delete').prop('disabled', true);

            $('#select-all').on('click', function() {
                const isChecked = $(this).is(':checked');
                $('.delete-checkbox').prop('checked', isChecked);
                toggleBulkDeleteButton();
            });

            $(document).on('change', '.delete-checkbox', function() {
                toggleBulkDeleteButton();
            });

            $('#bulk-delete').on('click', function() {
                const selected = $('.delete-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selected.length > 0) {
                    $('#deleteConfirmModal').modal('show');

                    $('#confirm-delete-btn').off('click').on('click', function() {
                        $.ajax({
                            url: route,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: selected
                            },
                            success: function(response) {
                                if (response.status === 200) {
                                    toastr.success(
                                        '{{ __('deleted_successfully') }}');
                                    $('#select-all').prop('checked', false);
                                    $('.delete-checkbox').prop('checked', false);
                                    $('#dataTable').DataTable().ajax.reload();
                                } else {
                                    toastr.error(
                                        '{{ __('something_went_wrong') }}');
                                }
                                $('#deleteConfirmModal').modal('hide');
                                toggleBulkDeleteButton();
                            },
                            error: function() {
                                toastr.error('{{ __('something_went_wrong') }}');
                                $('#deleteConfirmModal').modal('hide');
                                toggleBulkDeleteButton();
                            }
                        });
                    });
                }
            });

            function toggleBulkDeleteButton() {
                const anyChecked = $('.delete-checkbox:checked').length > 0;
                $('#bulk-delete').prop('disabled', !anyChecked);
            }
        });
    }

    function updateColumnSelected(route) {
        $(document).ready(function() {
            $('#bulk-update').prop('disabled', true);

            $('#select-all').on('click', function() {
                const isChecked = $(this).is(':checked');
                $('.delete-checkbox').prop('checked', isChecked);
                toggleBulkUpdateButton();
            });

            $(document).on('change', '.delete-checkbox', function() {
                toggleBulkUpdateButton();
            });

            $('#bulk-update').on('click', function() {
                const selected = $('.delete-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selected.length > 0) {
                    $('#updateConfirmModal').modal('show');

                    // Handle update confirmation
                    $('#confirm-update-btn').off('click').on('click', function() {
                        $.ajax({
                            url: route,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: selected
                            },
                            success: function(data) {
                                if (data.status === 200) {
                                    toastr.success(
                                        '{{ __('updated_successfully') }}');
                                    $('#select-all').prop('checked', false);
                                    $('.delete-checkbox').prop('checked', false);
                                    $('#dataTable').DataTable().ajax.reload();
                                } else {
                                    toastr.error(
                                        '{{ __('something_went_wrong') }}');
                                }
                                $('#updateConfirmModal').modal('hide');
                                toggleBulkUpdateButton();
                            },
                            error: function(xhr) {
                                toastr.error('{{ __('something_went_wrong') }}');
                                $('#updateConfirmModal').modal('hide');
                                toggleBulkUpdateButton();
                            }
                        });
                    });
                } else {
                    toastr.error('{{ __('please_select_first') }}');
                }
            });

            function toggleBulkUpdateButton() {
                const anyChecked = $('.delete-checkbox:checked').length > 0;
                $('#bulk-update').prop('disabled', !anyChecked);
            }
        });
    }

    function updateStatus(route) {

        $(document).on('click', '.statusBtnOne', function() {
            let ids = [];
            ids.push($(this).data('id'));
            var val = $(this).is(':checked') ? 'active' : 'inactive';

            $.ajax({
                type: 'POST',
                url: route,
                data: {
                    "_token": "{{ csrf_token() }}",
                    'ids': ids,
                },
                success: function(data) {
                    if (data.status === 200) {
                        // window.location.reload();
                        $('#dataTable').DataTable().ajax.reload();
                        if (val === 'active') {
                            toastr.success('Success', "{{ __('active') }}");
                        } else {
                            toastr.warning('Success', "{{ __('inactive') }}");
                        }
                    } else {
                        toastr.error('Error', "{{ __('something_went_wrong') }}");
                    }
                },
                error: function() {
                    toastr.error('Error', "{{ __('something_went_wrong') }}");
                }
            });
        });
    }
</script>

<script>
    function openModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').style.display = "block";
    }

    function closeModal() {
        document.getElementById('imageModal').style.display = "none";
    }

    // Also close when clicking outside the image
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
{{--<script>--}}
{{--    document.addEventListener('click', function (e) {--}}
{{--        if (e.target.classList.contains('copy-btn')) {--}}
{{--            const text = e.target.getAttribute('data-copy');--}}

{{--            navigator.clipboard.writeText(text).then(() => {--}}
{{--                toastr.success('Copied successfully!' , text);--}}
{{--            }).catch(err => {--}}
{{--                console.error('Copy failed:', err);--}}
{{--            });--}}
{{--        }--}}
{{--    });--}}
{{--</script>--}}

<script>
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('copy-btn')) {
            const text = e.target.getAttribute('data-copy');

            navigator.clipboard.writeText(text).then(() => {
                toastr.success('Copied successfully!' , text);
            }).catch(err => {
                console.error('Copy failed:', err);
            });
        }
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
