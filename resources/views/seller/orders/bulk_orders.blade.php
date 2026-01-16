<x-layout>
   <x-slot name="title">{{__('message.bulk.title')}}</x-slot>
   <x-slot name="breadcrumbs"> {{ Breadcrumbs::render('add_orders') }}</x-slot>
   <x-slot name="page_header_title"><h1 class="page-header-title">{{__('message.bulk.import_orders')}}</h1></x-slot>
   <x-slot name="headerbuttons">
      <div class="col-sm-auto">
         <a href="javascript:history.back()" class="btn btn-light btn-sm"><i class="bi bi-chevron-left"></i> {{__('message.back')}}</a>
      </div>
   </x-slot>
   <x-slot name="main">
      @if (session('success'))
      <div class="alert alert-soft-success alert-dismissible" role="alert">
         {!! session('success') !!}
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif
      @if (session('error'))
      <div class="alert alert-soft-danger alert-dismissible" role="alert">
         {!! session('error') !!}
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif
      <div class="card">
         <div class="card-body">
            <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data" id="importForm">
               @csrf
               <label for="basicFormFile" class="js-file-attach form-label"data-hs-file-attach-options='{"textTarget": "[for=\"customFile\"]"}'>Upload file from computer</label>
               <input class="form-control" type="file" name="importfile" >
               <div class="modal-footer my-3">
                  <a href="{{ asset(env('PUBLIC_ASSETS') . '/templates/Bulk_orders_import_template.csv') }}" class="btn btn-ghost-secondary btn-sm"><i class="bi bi-download"></i>{{__('message.bulk.download_template')}}</a>   
                  <div class="ms-auto">
                  <button type="submit" class="btn btn-primary btn-sm" id="importButton">
                   {{ __('message.bulk.import_file') }}
                   </button>
                  </div>
               </div>
            </form>
            <!-- Accordion -->
            <div class="accordion" id="accordionExample">
            <div class="accordion-item">
               <div class="accordion-header" id="headingOne">
                  <a class="accordion-button" role="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  {{__('message.bulk.instructions')}}
                  </a>
               </div>
               <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                  <div class="accordion-body">
                     <div class="alert alert-soft-secondary mb-2 mb-lg-2" role="alert">
                        <div class="d-flex align-items-center">
                           <div class="flex-shrink-0">
                              <img class="avatar avatar-xl" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations/oc-project-development.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                              <img class="avatar avatar-xl" src="{{ asset(env('PUBLIC_ASSETS') . '/svg/illustrations-light/oc-project-development.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                           </div>
                           <div class="flex-grow-1 ms-3">
                              <ul>{!!__('message.bulk.instructions_description')!!}</ul>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="accordion-item">
               <div class="accordion-header" id="headingTwo">
                  <a class="accordion-button collapsed" role="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Indian States Name
                  </a>
               </div>
               <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                  <div class="accordion-body">
                     <table class="table">
                        <thead class="thead-light"><tr><th>State Names</th><th>State Code</th></tr></thead>
                          <tbody>
                           @foreach ($states as $state)
                               <tr><td>{{$state->name}}</td><td>{{$state->state_code}}</td></tr>
                           @endforeach
                          </tbody>
                           
                     </table>
                  </div>
               </div>
            </div>
            </div>
            <!-- End Accordion -->
         </div>
      </div>
   </x-slot>
</x-layout>
<script>
document.getElementById('importForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const button = document.getElementById('importButton');
    const spinnerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

    button.innerHTML = spinnerHTML;
    button.disabled = true;

    this.submit();
});
</script>