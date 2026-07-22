@extends('backend.layouts.master')

@section('title')
    Product Reviews & Ratings
@endsection

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product Reviews & Ratings</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Best Rated Products</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="best-rated-table">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th width="15%">Average Rating</th>
                                            <th width="10%">Total Reviews</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reviews-table-body">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <div class="spinner-border spinner-border-sm" role="status">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                                Loading products...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Reviews Modal -->
    <div class="modal fade" id="reviewsModal" tabindex="-1" role="dialog" aria-labelledby="reviewsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewsModalLabel">Product Reviews</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="reviewsModalBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load best rated products
    loadBestRatedProducts();

    function loadBestRatedProducts() {
        $.ajax({
            url: '{{ route("admin.reviews.best-rated") }}',
            method: 'GET',
            success: function(response) {
                if (response.status === 'success' && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(product) {
                        let stars = '';
                        for (let i = 1; i <= 5; i++) {
                            if (i <= Math.round(product.average_rating)) {
                                stars += '<i class="fas fa-star text-warning"></i>';
                            } else {
                                stars += '<i class="fas fa-star text-gray-300"></i>';
                            }
                        }

                        html += `
                            <tr>
                                <td><strong>${product.name}</strong></td>
                                <td>${product.sku}</td>
                                <td>${product.category}</td>
                                <td>
                                    <div class="badge badge-primary">${product.average_rating} / 5</div>
                                    <div>${stars}</div>
                                </td>
                                <td>
                                    <span class="badge badge-info">${product.total_reviews}</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info view-reviews" data-product-id="${product.id}" data-product-name="${product.name}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#reviews-table-body').html(html);
                } else {
                    $('#reviews-table-body').html(`
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No products with reviews yet.
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#reviews-table-body').html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            Error loading products. Please try again.
                        </td>
                    </tr>
                `);
            }
        });
    }

    // View reviews
    $(document).on('click', '.view-reviews', function() {
        let productId = $(this).data('product-id');
        let productName = $(this).data('product-name');

        $('#reviewsModalLabel').text('Reviews for: ' + productName);
        $('#reviewsModal').modal('show');

        $.ajax({
            url: '{{ route("admin.reviews.product", ":productId") }}'.replace(':productId', productId),
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    let html = `
                        <div class="mb-3">
                            <h6>Average Rating: <span class="badge badge-primary">${response.average_rating} / 5</span></h6>
                            <h6>Total Reviews: <span class="badge badge-info">${response.total_reviews}</span></h6>
                        </div>
                        <hr>
                    `;

                    if (response.reviews.length > 0) {
                        response.reviews.forEach(function(review) {
                            let stars = '';
                            for (let i = 1; i <= 5; i++) {
                                if (i <= review.rating) {
                                    stars += '<i class="fas fa-star text-warning"></i>';
                                } else {
                                    stars += '<i class="fas fa-star text-gray-300"></i>';
                                }
                            }

                            html += `
                                <div class="review-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between">
                                        <h6><strong>${review.user}</strong></h6>
                                        <small class="text-muted">${review.created_at}</small>
                                    </div>
                                    <div>${stars}</div>
                                    ${review.comment ? `<p class="mt-2 mb-0">${review.comment}</p>` : ''}
                                </div>
                            `;
                        });
                    } else {
                        html += '<p class="text-muted">No reviews yet.</p>';
                    }

                    $('#reviewsModalBody').html(html);
                }
            },
            error: function() {
                $('#reviewsModalBody').html('<p class="text-danger">Error loading reviews.</p>');
            }
        });
    });
});
</script>
@endpush
