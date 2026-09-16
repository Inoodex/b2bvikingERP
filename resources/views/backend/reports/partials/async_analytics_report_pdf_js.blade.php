<script>
$(document).ready(function() {
    $(document).on('click', '#btn-generate-pdf, .btn-generate-pdf', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalHtml = $btn.html();
        var generateUrl = $btn.data('url') || $btn.attr('href');
        var reportType = $btn.data('type') || 'order_sales_report';

        if (!generateUrl) {
            console.error('No generateUrl found on generate button');
            return;
        }

        // Defensive Guard: Prevent full inventory crash on Stock Valuation PDF
        if (reportType === 'stock_valuation_report') {
            var catVal = $('select[name="category_id"]').val();
            var brandVal = $('select[name="brand_id"]').val();
            if (!catVal && !brandVal) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Please select a Category or Brand filter before exporting PDF. For full inventory, please use Excel export.', 'Filter Required');
                }
                return;
            }
        }

        // Capture current form filter values if a form exists
        var $form = $('form#stock-filter-form, form#filter-form-user, form#filter-form-global, form[action*="reports"]').first();
        var formData = '';

        if ($form.length) {
            formData = $form.serialize();
        } else {
            formData = window.location.search.replace(/^\?/, '');
        }

        // Disable button and show spinner
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating in Background...');

        if (typeof toastr !== 'undefined') {
            toastr.info('Generating PDF report in the background. The Download button will appear when ready.', 'Processing PDF');
        }

        // Trigger Queue Job dispatch
        $.ajax({
            url: generateUrl,
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                var checkUrl = $btn.data('check-url') || "{{ route('admin.reports.orders.check-status') }}";
                var pollAttempts = 0;
                var maxAttempts = 35; // 35 * 2s = 70s
                var dispatchedAt = response.dispatched_at || (Math.floor(Date.now() / 1000) - 1);

                var pollInterval = setInterval(function() {
                    pollAttempts++;
                    $.ajax({
                        url: checkUrl,
                        type: 'GET',
                        data: {
                            type: reportType,
                            after: dispatchedAt
                        },
                        success: function(statusRes) {
                            if (statusRes.ready && statusRes.download_url) {
                                clearInterval(pollInterval);
                                $btn.prop('disabled', false).html(originalHtml);

                                // Update and reveal the download button
                                var $dlBtn = $('#btn-download-pdf, .btn-download-pdf');
                                if ($dlBtn.length) {
                                    $dlBtn.attr('href', statusRes.download_url);
                                    $dlBtn.attr('title', statusRes.filename || 'Download PDF');
                                    $dlBtn.html('<i class="fas fa-file-download mr-1"></i> Download PDF (Ready: ' + (statusRes.time || 'Now') + ')');
                                    $dlBtn.fadeIn(300);
                                }

                                if (typeof toastr !== 'undefined') {
                                    toastr.success('Your report PDF is ready! Click "Download PDF" anytime to download.', 'Report Ready');
                                }
                            } else if (pollAttempts >= maxAttempts) {
                                clearInterval(pollInterval);
                                $btn.prop('disabled', false).html(originalHtml);
                                if (typeof toastr !== 'undefined') {
                                    toastr.info('PDF is still generating in the background. You can download it from the top notification bell once ready.', 'Still Processing');
                                }
                            }
                        },
                        error: function() {
                            if (pollAttempts >= maxAttempts) {
                                clearInterval(pollInterval);
                                $btn.prop('disabled', false).html(originalHtml);
                            }
                        }
                    });
                }, 2000);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalHtml);
                if (typeof toastr !== 'undefined') {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.warning(xhr.responseJSON.message, 'Filter Required');
                    } else {
                        toastr.error('Failed to start PDF generation. Please try again.', 'Error');
                    }
                }
            }
        });
    });
});
</script>
