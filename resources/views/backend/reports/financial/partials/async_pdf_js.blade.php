<script>
$(document).ready(function() {
    $('#btn-generate-pdf').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalHtml = $btn.html();
        var generateUrl = $btn.data('url');
        var reportType = $btn.data('type');

        // Capture current form filter values if a form exists
        var $form = $('form[action*="reports"]').first();
        var formData = $form.length ? $form.serialize() : $('form').first().serialize();

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
                var checkUrl = "{{ route('admin.reports.financial.check-status') }}";
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
                                var $dlBtn = $('#btn-download-pdf');
                                $dlBtn.attr('href', statusRes.download_url);
                                $dlBtn.attr('title', statusRes.filename || 'Download PDF');
                                $dlBtn.html('<i class="fas fa-file-download mr-1"></i> Download PDF (Ready: ' + (statusRes.time || 'Now') + ')');
                                $dlBtn.fadeIn(300);

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
                    toastr.error('Failed to start PDF generation. Please try again.', 'Error');
                }
            }
        });
    });
});
</script>
