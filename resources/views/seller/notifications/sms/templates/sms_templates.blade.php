<x-layout>
   <x-slot name="title">{{__('message.sms_template.tittle')}}</x-slot>
   <x-slot name="breadcrumbs"> Notifications</x-slot>
   <x-slot name="page_header_title">
      <h1 class="page-header-title">{{__('message.sms_template.tittle')}}</h1>
   </x-slot>
   <x-slot name="headerbuttons">
      <div class="col-sm-auto">
         <a href="{{ route('sms_template_create') }}" class="btn btn-primary btn-sm" ><i class="bi bi-plus-circle me-1"></i>{{__('message.add_new')}}</a>
      </div>
      <div class="col-sm-auto">
         <div class="d-flex gap-2">
            <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
         </div>
      </div>
   </x-slot>
   <x-slot name="main">
      @if(session('success'))
      <div class="alert alert-soft-success alert-dismissible" role="alert">
         {!! session('success') !!}
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif
      @if(session('error'))
      <div class="alert alert-soft-danger alert-dismissible" role="alert">
         {!! session('error') !!}
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif
      {{-- DLT Settings Table --}}
      <div class="card overflow-hidden">
         <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
               <thead class="thead-light">
                  <tr>
                     <th>{{__('message.sms_template.shipment_status')}}</th>
                     <th>{{__('message.sms_template.reg_id')}}</th>
                     <th>{{__('message.sms_template.telecom_content')}}</th>
                     <th>{{__('message.status')}}</th>
                     <th  class="text-end">{{__('message.action')}}</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($template_settings as $index => $template)
                  <tr>
                     <td>{{ $template->order_status }}</td>
                     <td>{{ $template->template_registration_id }}</td>
                     <td>{{ $template->message_content }}</td>
                     <td>
                        <span class="badge bg-{{ $template->status ? 'success' : 'secondary' }}">
                        {{ $template->status ? 'Active' : 'Inactive' }}
                        </span>
                     </td>
                     <td class="text-end">
                        <a href="{{ route('sms_template_edit',['id' => $template->id]) }}" class="btn btn-white btn-sm"><i class="bi-pencil-fill me-1"></i>{{__('message.edit')}}</a>
                         <a class="btn btn-white" data-bs-toggle="modal" data-bs-target="#smsModal"   onclick="sentSMS({{ $template->id }})"><i class="bi bi-arrow-repeat"></i>Test</a>
                     </td>
                  </tr>
                  @empty
                  <tr>
                     <td colspan="6" class="text-center">{{__('message.sms_template.not_found')}}</td>
                  </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
      <div class="modal fade" id="smsModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-lg">
            <div class="modal-content">
               <form method="POST" action="{{ route('Test_sms') }}" id="dlt-setting-form">
                  @csrf
                  <input type="hidden" name="id" id="template-id">
                  <!-- <input type="hidden" name="id" id="setting-id"> -->
                  <div class="modal-header">
                     <h5 class="modal-title">Add Phone Number</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                     <div class="row g-6">
                        <div class="col-md-13">
                           <label class="form-label">Phone Number</label>
                           <input type="number" class="form-control" name="phone" id="phone" required  maxlength="10">
                        </div>
                        <div class="modal-footer">
                           <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                           <button type="submit" class="btn btn-primary btn-sm">Send Message</button>
                        </div>
                     </div>
                  </div>
               </form>     
            </div>
         </div>
      </div>
      <script>
         var myModal = new bootstrap.Modal(document.getElementById('smsModal'));
         myModal.show(); 
         
         function sentSMS(id) {
             document.getElementById('template-id').value = id;
         }
         
      </script>
   </x-slot>
</x-layout>