@props([
    'id' => 'orderShipModal',       // default modal ID
    'title' => 'Extra Large Modal', // default title
])

<!-- Modal -->
<div class="modal fade bd-example-modal-xl" id="{{ $id }}" tabindex="-1" role="dialog"
     aria-labelledby="{{ $id }}Label" aria-hidden="true">

    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title h4" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="{{ $id }}Body">
                <!-- Dynamic AJAX content loads here -->
                Loading...
            </div>

        </div>
    </div>
</div>
<!-- End Modal -->
