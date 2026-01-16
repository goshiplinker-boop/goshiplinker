<x-layout>
    <x-slot name="title">Manage Template</x-slot>
    @php
    $segment = request()->segment(1);
   @endphp
<x-slot name="breadcrumbs">
    @if($segment == 'admin')
        {{ Breadcrumbs::render('seller_notification_edit', $template) }} 
    @elseif($segment == 'admin')
        {{ Breadcrumbs::render('notification_edit', $template) }}
    @endif
</x-slot>
    <x-slot name="page_header_title">
        <h1 class="page-header-title">Manage Template</h1>
    </x-slot>
    <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i>{{ __('message.back') }}</a>
        </div>
    </x-slot>   

    <x-slot name="main">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{ route($update_route, ['id' => $template->id]) }}" method="POST" id="templateForm" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <div class="tab-content">
                <div class="tab-pane fade show active" id="nav-one-eg1" role="tabpanel" aria-labelledby="nav-one-eg1-tab">
                    <div class="card">
                        <div class="card-body">

                            <!-- Template Type Dropdown -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-1 mt-1">
                                        <label for="channel" class="form-label">Template Type</label>
                                        <select name="channel" id="channel" class="form-select" required disabled onchange="toggleFields()">
                                            <option value="{{$template->channel}}">{{ ucfirst($template->channel) }}</option>
                                            <input type="hidden" name="channel" value="{{ old('channel', $template->channel) }}">
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Template for Dropdown -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="mb-1 mt-1">
                                        <label class="form-label" for="user_type">Template for</label>
                                        <select name="user_type" class="form-select" required disabled>
                                            <option value="{{$template->user_type}}">{{ ucfirst($template->user_type) }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Select Status Dropdown -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="mb-1 mt-1">
                                        <label class="form-label" for="event_type">Select Status</label>
                                        <select class="form-select" id="event_type" name="event_name" required disabled>
                                            <option value="{{$template->event_type}}">{{ ucfirst($template->event_type ) }}</option>
                                        </select>
                                        <input type="hidden" name="event_type" value="{{ old('event_type', $template->event_type) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mt-2" id="sender-id-field" style="display: none;">
                                    <label for="sender_id" class="form-label">Sender ID</label>
                                    <input type="text" class="form-control" name="sender_id" id="sender_id" value="{{ old('sender_id', $template->sender_id ?? '') }}" placeholder="Enter sender ID">
                                    @error('sender_id')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Subject Field (Initially Hidden) -->
                            <div class="row">
                                <div class="col-sm-12" id="subject-field" style="display: none;">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" name="subject" id="subject" value="{{ old('subject', $template->subject ?? '') }}" placeholder="Your order is placed" required>
                                    @error('subject')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- Quill Editor Container -->
                         <div class="row">
                           <div class="col-sm-12">
                             <div class="mb-1 mt-1">
                            <label for="template" class="form-label">Template</label>
                            <div class="quill-custom">
                                <div class="js-quill" style="min-height: 15rem;" 
                                     data-hs-quill-options='{
                                       "placeholder": "Type your message...",
                                       "modules": {
                                         "toolbar": [
                                           ["bold", "italic", "underline", "strike","blockquote", "code"]
                                         ]
                                       }
                                     }'>
                                    {{ old('template', $template->body ?? '') }}
                                </div>
                            </div>
                        </div>
                            <!-- End Quill Editor -->

                            <!-- Hidden input to capture Quill content -->
                            <input type="hidden" id="template" name="template">

                            @error('template')
                            <div class="text-danger">{{ $message }}</div>
                                <div class="text-danger">{{ $message }}</div>
                               @enderror
                        </div>
                    </div>
                             <div class="modal-footer my-3">
                                <button type="submit" class="btn btn-primary btn-sm" id="importButton">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="accordion" id="accordionExample">
             <div class="accordion-item">
                <div class="accordion-header" id="headingTwo">
                    <a class="accordion-button collapsed" role="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        MORE VARIABLE
                    </a>
                </div>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Variable</th>
                                    <th>Description</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{customer_name}</td>
                                    <td>Customer Name</td>
                                </tr>
                                <tr>
                                    <td>{order_id}</td>
                                    <td>Order ID</td>
                                </tr>

                                <tr>
                                    <td>{product_name}</td>
                                    <td>Product Name</td>
                                </tr>
                                <tr>
                                    <td>{order_amount}</td>
                                    <td>Order Amount</td>
                                </tr>
                                <tr>
                                    <td>{phone_no}</td>
                                    <td>Phone Number</td>
                                </tr>
                                <tr>
                                    <td>{store_name}</td>
                                    <td>Store Name</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </x-slot>
</x-layout>

<script>
   function toggleFields() {
        const channel = document.getElementById('channel').value;
        const subjectField = document.getElementById('subject-field');
        const senderIdField = document.getElementById('sender-id-field');

        // Show or hide fields based on the selected channel
        if (channel === 'email') {
            subjectField.style.display = 'block';
            senderIdField.style.display = 'none';
        } else if (channel === 'whatsapp' || channel === 'sms' || channel === 'crs') {
            subjectField.style.display = 'none';
            senderIdField.style.display = 'block';
        } else {
            subjectField.style.display = 'none';
            senderIdField.style.display = 'none';
        }
    }

    // Call toggleFields when the page loads to set initial visibility
    document.addEventListener('DOMContentLoaded', function() {
        toggleFields(); 
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Quill editor only if it is not already initialized
        if (!window.quillEditor) {
            window.quillEditor = new Quill('.js-quill', {
                theme: 'snow', // Use the 'snow' theme (which includes the toolbar)
                placeholder: 'Type your message...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike', "blockquote", "code"] // Customize this toolbar as needed
                    ]
                }
            });
            
            // Sync Quill content to the hidden input field on form submit
            document.querySelector('form').onsubmit = function() {
                var templateContent = window.quillEditor.root.innerHTML;
                document.getElementById('template').value = templateContent;
            };
        }

        // Call toggleFields to handle the visibility of fields after the page loads
        toggleFields(); 
    });
</script>
