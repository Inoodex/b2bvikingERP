<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <!-- laravel ajax csrf token -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <title>@yield('title')</title>
  {{-- <link rel="icon" type="image/png" href="{{ asset($logoSetting?->favicon ?? '') }}"> --}}
  <link rel="icon" type="image/png" href="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}">

  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/fontawesome/css/all.min.css') }}">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/jqvmap/dist/jqvmap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/weather-icon/css/weather-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/weather-icon/css/weather-icons-wind.min.css') }}">
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/summernote/summernote-bs4.css') }}">

  <!-- Select2 -->
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/select2/dist/css/select2.min.css') }}">

  <!-- datetimepicker CSS -->
  <link rel="stylesheet" href="{{ asset('backend/assets/modules/bootstrap-daterangepicker/daterangepicker.css') }}">

  <!-- iconpicker CSS -->
  <link rel="stylesheet" href="{{ asset('backend/assets/css/bootstrap-iconpicker.min.css') }}">


  <!-- Toastr css -->
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet"
    href="{{ asset('backend/assets/css/style.css') }}?v={{ filemtime(public_path('backend/assets/css/style.css')) }}">
  <link rel="stylesheet" href="{{ asset('backend/assets/css/components.css') }}">
  <!-- custom css -->
  <link rel="stylesheet" href="{{ asset('backend/assets/css/custom.css') }}">
  @stack('css')
  <style>
  @media (min-width: 1200px) {
    .main-wrapper.container {
      max-width: 100% !important;
      width: 100% !important;
      padding: 0 !important;
    }
  }

  /* Bell Shake Animation */
  @keyframes bell-shake {
    0% {
      transform: rotate(0);
    }

    15% {
      transform: rotate(5deg);
    }

    30% {
      transform: rotate(-5deg);
    }

    45% {
      transform: rotate(4deg);
    }

    60% {
      transform: rotate(-4deg);
    }

    75% {
      transform: rotate(2deg);
    }

    85% {
      transform: rotate(-2deg);
    }

    100% {
      transform: rotate(0);
    }
  }

  .shake {
    animation: bell-shake 0.5s cubic-bezier(.36, .07, .19, .97) both infinite;
    transform: translate3d(0, 0, 0);
    backface-visibility: hidden;
    perspective: 1000px;
  }

  /* Ensure SweetAlert2 is always on top of all modals, offcanvas drawers & backdrops */
  .swal2-container {
    z-index: 10000000 !important;
  }
  </style>
  <!-- DataTables CSS for Bootstrap 4 -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
</head>

<body class="layout-3">
  <div id="app">
    <div class="main-wrapper container">
      <div class="navbar-bg"></div>
      <!-- navbar Content -->
      @include('backend.layouts.navbar')
      {{-- sidebar is rendered inside navbar.blade.php (app-sidebar) --}}


      <!-- Main Content -->
      <div class="main-content">
        @yield('content')
      </div>
      <footer class="main-footer">
        <div class="footer-inner">
          <div class="footer-left">
            <span class="footer-copyright"> B2bviking &copy; {{ now()->year }} All rights reserved</span>
          </div>
          <div class="footer-right">
            Developed by <a target="_blank" href="https://inoodex.com/"><strong>Inoodex</strong></a>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="{{ asset('backend/assets/modules/jquery.min.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/popper.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/tooltip.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/moment.min.js') }}"></script>
  <script src="{{ asset('backend/assets/js/stisla.js') }}"></script>

  <!-- JS Libraies -->
  <script src="{{ asset('backend/assets/modules/simple-weather/jquery.simpleWeather.min.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/chart.min.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/jqvmap/dist/jquery.vmap.min.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/jqvmap/dist/maps/jquery.vmap.world.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/summernote/summernote-bs4.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/chocolat/dist/js/jquery.chocolat.min.js') }}"></script>
  <script src="{{ asset('backend/assets/modules/upload-preview/assets/js/jquery.uploadPreview.min.js') }}"></script>

  <!-- jq js bootstrap 5 -->
  {{-- <script src="https://cdn.datatables.net/2.0.7/js/dataTables.bootstrap5.js"></script> --}}

  <!-- Toastr css -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  <!-- Sweet Alert Js -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- iconpicker js -->
  {{-- <script src="{{asset('backend/assets/js/bootstrap-iconpicker.min.js')}}"></script> --}}
  <script src="{{ asset('backend/assets/js/bootstrap-iconpicker.bundle.min.js') }}"></script>

  <!-- datetimepicker js -->
  <script src="{{ asset('backend/assets/modules/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

  <!-- Select2  js -->
  <script src="{{ asset('backend/assets/modules/select2/dist/js/select2.full.min.js') }}"></script>

  <!-- Page Specific JS File -->
  {{-- <script src="{{asset('backend/assets/js/page/index-0.js')}}"></script> --}}
  <!-- Template JS File -->
  <script src="{{ asset('backend/assets/js/scripts.js') }}"></script>
  <script src="{{ asset('backend/assets/js/custom.js') }}"></script>
  {!! Toastr::message() !!}
  <script>
  @if($errors -> any())
  @foreach($errors -> all() as $error)
  toastr.error("{{ $error }}")
  @endforeach
  @endif
  </script>

  <!-- Datatables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
  <!-- Buttons JS -->
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>


  <!-- Dynamic Delete alert -->
  <script>
  $(document).ready(function() {
    $.fn.dataTable.ext.errMode = 'none';

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $('body').on('click', '.delete-item', function(event) {
      event.preventDefault();

      let deletUrl = $(this).attr('href');
      let bookingNo = $(this).data('booking-no');
      let message = bookingNo ? `This will delete the entire order group (${bookingNo})!` :
        "You won't be able to revert this!";

      Swal.fire({
        title: "Are you sure?",
        text: message,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        focusConfirm: false,
        focusCancel: false
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            type: 'DELETE',
            url: deletUrl,
            data: {
              _token: "{{ csrf_token() }}",
              booking_no: bookingNo
            },
            success: function(data) {
              if (data.status == 'success') {
                Swal.fire(
                  'Deleted',
                  data.message,
                  'success'
                ).then(() => {
                  window.location.reload();
                });
              } else if (data.status == 'error') {
                Swal.fire(
                  "Can't Delete!",
                  data.message,
                  'error'
                );
              }
            },
            error: function(xhr, status, error) {
              console.log(error);
            }
          });
        }
      });
    });

    // Status Change for grouped bookings
    $('body').on('change', '.change-booking-status', function() {
      let status = $(this).val();
      let id = $(this).data('id');
      let bookingNo = $(this).data('booking-no');

      $.ajax({
        method: 'PUT', // Changed to PUT to match route definition
        url: "{{ route('admin.bookings.status-update') }}", // Updated to new route
        data: {
          status: status,
          id: id,
          booking_no: bookingNo
        },
        success: function(response) {
          if (response.status === 'success') {
            toastr.success(response.message);
          } else {
            toastr.error(response.message || 'Error updating status');
          }
        },
        error: function(error) {
          console.log(error);
          toastr.error('Failed to update status');
        }
      });
    });
  });
  </script>

  {{-- Notification System --}}
  <script>
  function checkLowStock() {
    if ($('#low-stock-count-toggle').length === 0) return;

    $.ajax({
      url: '{{ route("admin.low-stock-check") }}',
      method: 'GET',
      cache: false,
      dataType: 'json',
      success: function(data) {
        if (data.count > 0) {
          $('#low-stock-count-badge').text(data.count).show();
          $('#low-stock-count-toggle').addClass('has-count');
          $('#low-stock-count-toggle i').addClass('shake');
        } else {
          $('#low-stock-count-badge').hide();
          $('#low-stock-count-toggle').removeClass('has-count');
          $('#low-stock-count-toggle i').removeClass('shake');
        }

        // Always update current items list even if count is 0 (to show read ones)
        let listHtml = '';
        if (data.notifications && data.notifications.length > 0) {
          data.notifications.forEach(function(item) {
            let cls = 'notif-item';
            if (item.is_unread) cls += ' unread';
            if (item.is_out_of_stock && item.is_unread) cls += ' out-of-stock';

            listHtml += `
              <a href="${item.url}" class="${cls}">
                <div class="notif-icon ${item.class}">
                  <i class="${item.icon}"></i>
                </div>
                <div class="notif-content">
                  <div class="notif-title">${item.title}</div>
                  <div class="notif-desc">${item.desc}</div>
                  <div class="notif-time">${item.time}</div>
                </div>
              </a>
            `;
          });
        } else {
          listHtml =
            '<div class="notif-empty"><i class="fas fa-inbox"></i>No new notifications</div>';
        }
        $('#low-stock-list').html(listHtml);
      },
      error: function() {
        console.log('Failed to check notifications');
      }
    });
  }

  function markAllAsRead() {
    $.ajax({
      url: '{{ route("admin.low-stock-mark-read") }}',
      method: 'POST',
      data: {
        _token: '{{ csrf_token() }}'
      },
      success: function() {
        $('#low-stock-count-badge').hide();
        $('#low-stock-count-toggle').removeClass('has-count');
        $('#low-stock-count-toggle i').removeClass('shake');
        // Refresh the list to show them as "read"
        checkLowStock();
      }
    });
  }

  $(document).ready(function() {
    // Initial check
    checkLowStock();
    // Auto-refresh every 5 seconds for quick updates
    setInterval(checkLowStock, 5000);
  });
  </script>

  @stack('scripts')

  {{-- Suppress third-party checkout popup errors --}}
  <script>
  window.addEventListener('unhandledrejection', function(event) {
    if (event.reason && event.reason.message && event.reason.message.includes('No checkout popup config found')) {
      event.preventDefault();
      // Silently ignore this error from unused payment library
    }
  });
  </script>
</body>

</html>