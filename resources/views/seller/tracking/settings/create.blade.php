<x-layout>
   <x-slot name="title">Manage Tracking</x-slot>
   <x-slot name="breadcrumbs">Tracking / Manage</x-slot>
   <x-slot name="page_header_title">
      <h1 class="page-header-title">Manage Tracking Page</h1>
   </x-slot>
   <x-slot name="headerbuttons">
        <div class="col-sm-auto">
            <a href="javascript:history.back()" class="btn btn-light btn-sm"> <i class="bi bi-chevron-left"></i>{{__('message.back')}}</a>
        </div>
    </x-slot>   
   <x-slot name="main">      
      @if(session('success'))
        <div class="alert alert-soft-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <!-- Nav -->
      <ul class="nav nav-segment" role="tablist">
         <li class="nav-item">
            <a class="nav-link active" id="nav-one-eg1-tab" href="#nav-one-eg1" data-bs-toggle="pill" data-bs-target="#nav-one-eg1" role="tab" aria-controls="nav-one-eg1" aria-selected="true">Landing View</a>
         </li>
         <li class="nav-item">
            <a class="nav-link" id="nav-two-eg1-tab" href="#nav-two-eg1" data-bs-toggle="pill" data-bs-target="#nav-two-eg1" role="tab" aria-controls="nav-two-eg1" aria-selected="false">Tracking View</a>
         </li>
         <li class="nav-item">
            <a class="nav-link" id="nav-three-eg1-tab" href="#nav-three-eg1" data-bs-toggle="pill" data-bs-target="#nav-three-eg1" role="tab" aria-controls="nav-three-eg1" aria-selected="false">Settings</a>
         </li>
      </ul>
      <!-- End Nav -->
      <form action="{{ route('tracking_store') }}" method="POST" enctype="multipart/form-data" novalidate>
      @csrf
         <!-- Tab Content -->
         <div class="tab-content">
            <div class="tab-pane fade show active" id="nav-one-eg1" role="tabpanel" aria-labelledby="nav-one-eg1-tab">
               <div class="card">
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Set Domain Name</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="This domain help user on reliability"></i>
                           <input type="text" class="form-control" name="website_domain" id="website_domain" placeholder="Enter Domain Name" required  value="{{old('website_domain',$manageTrackingPage->website_domain??'')}}">
                           @error('website_domain')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Preview</label>
                           <input type="text" class="form-control" disabled value="{{ request()->root() }}/{{old('website_domain',$manageTrackingPage->website_domain??'{website_domain}')}}/track" id="website_domain_preview">
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6 border p-1 mb-4">
                           <!-- Media -->
                           <div class="d-flex align-items-center">
                              <!-- Avatar -->
                              <label class="avatar avatar-xl avatar-circle" for="avatarUploader">
                              <img id="avatarImg" class="avatar-img"  src="{{ old('website_logo', isset($jsonData['website_logo']) ? asset(env('PUBLIC_ASSETS').'/images/tracking/logos/' . basename($jsonData['website_logo'])) : asset(env('PUBLIC_ASSETS') . '/img/160x160/img2.jpg')) }}"  alt="Image Description">
                              </label>
                              <div class="d-flex gap-3 ms-4">
                                 <div class="form-attachment-btn btn btn-sm btn-primary">Add Your Website Logo
                                    <input type="file" name="website_logo" class="js-file-attach form-attachment-btn-label" id="avatarUploader"
                                       data-hs-file-attach-options='{
                                       "textTarget": "#avatarImg",
                                       "maxFileSize": 100,
                                       "mode": "image",
                                       "targetAttr": "src",
                                       "resetTarget": ".js-file-attach-reset-img",
                                       "resetImg": "{{ asset(env('PUBLIC_ASSETS') . '/img/160x160/img2.jpg') }}",
                                       "allowTypes": [".png", ".jpeg", ".jpg"],
                                       "errorMessage": "File is too big!, max allow 100KB"
                                       }' required>
                                 </div>
                                 <!-- End Avatar -->
                                 <button type="button" class="js-file-attach-reset-img btn btn-white btn-sm">Remove</button>
                              </div>
                           </div>
                           @session('error')
                              <div class="text-danger">{{ session('error') }}</div>
                           @enderror
                           <!-- End Media -->
                        </div>
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Set Your Website Targeted URL</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="Link your website by providing website URL"></i>
                           <input type="text" class="form-control" name="website_url" placeholder="Paste your website URL here"  value="{{old('website_url',$jsonData['website_url']??'')}}"> 
                           @error('website_url')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Set Page Heading Title</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="You can set your page title, by default it is 'Track Your Order'"></i>
                           <input type="text" class="form-control" name="heading_title" placeholder="Enter Title"  value="{{old('heading_title',$jsonData['heading_title']??'')}}">
                           @error('heading_title')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Set Page Heading Sub Title</label>
                           <input type="text" class="form-control" name="heading_sub_title" placeholder="Enter Sub Title"  value="{{old('heading_sub_title',$jsonData['heading_sub_title']??'')}}">
                           @error('heading_sub_title')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label"> Set Buyer Tracking Options</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="Your can select both option for your buyer."></i><br>
                           <!-- Form Check -->
                           <div class="form-check form-check-inline">
                              <input type="checkbox" id="formInlineCheck1" class="form-check-input" {{ in_array('order_number', old('tracking_type', $jsonData['tracking_type'] ?? [])) ? 'checked' : '' }} name="tracking_type[]" value="order_number">
                              <label class="form-check-label" for="formInlineCheck1">Order Number</label>
                           </div>
                           <!-- End Form Check -->
                           <!-- Form Check -->
                           <div class="form-check form-check-inline">
                              <input type="checkbox" id="formInlineCheck2" class="form-check-input indeterminate-checkbox" {{ in_array('tracking_number', old('tracking_type', $jsonData['tracking_type'] ?? [])) ? 'checked' : '' }} name="tracking_type[]"  value="tracking_number">                        
                              <label class="form-check-label" for="formInlineCheck2">Tracking Number</label>
                           </div>
                           <!-- End Form Check -->
                           @error('tracking_type')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                           <label for="theme" class="form-label">Set Theme Color</label>
                           <input type="text" class="form-control" name="theme_color" placeholder="Enter color code here"  value="{{old('theme_color',$jsonData['theme_color']??'')}}">
                           @error('theme_color')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Support Email Address</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="Add an email ID where customers can reach your customer service team."></i>
                           <input type="text" class="form-control" name="support_email_address" placeholder="Enter eMail"  value="{{old('support_email_address',$jsonData['support_email_address']??'')}}">
                           @error('support_email_address')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Support Contact Number</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="Add phone number for customers to reach your customer service team."></i>
                           <input type="text" class="form-control" name="support_contact_number" placeholder="Enter Number"  value="{{old('support_contact_number',$jsonData['support_contact_number']??'')}}">
                           @error('support_contact_number')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>                  
                  </div>
                  <a href="{{ request()->root() }}/{{old('website_domain',$manageTrackingPage->website_domain??'{website_domain}')}}/track" target="_blank" class="btn btn-ghost-secondary btn-sm" id="landing_page"><i class="bi bi-box-arrow-up-right"></i> Preview Landing Page</a>
               </div>
            </div>
            <div class="tab-pane fade" id="nav-two-eg1" role="tabpanel" aria-labelledby="nav-two-eg1-tab">
               <div class="card">
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Set Announcement/Offer</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="You can set any announcement and offer for your visitors"></i>
                           <input type="text" class="form-control" name="announcement" placeholder="Enter description"  value="{{old('announcement',$jsonData['announcement']??'')}}">
                           @error('announcement')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Set Your Announcement/Offer Targeted URL</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="Link your website by providing website URL"></i>
                           <input type="text" class="form-control" name="announcement_url" placeholder="Paste your website URL here"  value="{{old('announcement_url',$jsonData['announcement_url']??'')}}">
                           @error('announcement_url')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6 border p-1 mb-4">
                              <!-- Media -->
                              <div class="d-flex align-items-center">
                                 <!-- Avatar -->
                                 <label class="avatar avatar-xl avatar-circle" for="avatarUploader">                                    
                                 <img id="avatarImg2" class="avatar-img"  src="{{ old('promotional_banner', isset($jsonData['promotional_banner']) ? asset('assets/images/tracking/banner/' . basename($jsonData['promotional_banner'])) :asset(env('PUBLIC_ASSETS') . '/img/160x160/img2.jpg')) }}"  alt="Image Description">
                                 </label>
                                 <div class="d-flex gap-3 ms-4">
                                    <div class="form-attachment-btn btn btn-sm btn-primary">Add Promotional Banner
                                       <input type="file" name="promotional_banner" class="js-file-attach form-attachment-btn-label" id="avatarUploader"
                                          data-hs-file-attach-options='{
                                          "textTarget": "#avatarImg2",
                                          "maxFileSize": 2048,
                                          "mode": "image",
                                          "targetAttr": "src",
                                          "resetTarget": ".js-file-attach-reset-img",
                                          "resetImg": "{{asset(env('PUBLIC_ASSETS') . '/img/160x160/img2.jpg')}}",
                                          "allowTypes": [".png", ".jpeg", ".jpg"],
                                          "errorMessage": "File is too big!, max allow 2MB"
                                          }' required>
                                          @error('promotional_banner')
                                             <div class="text-danger">{{ $message }}</div>
                                          @enderror
                                    </div>
                                    <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="Maxmimum promotional banner file size is 2 MB"></i>
                                 </div>
                              </div>
                              <!-- End Media -->
                        </div>
                        
                        <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Set Your Promotional Targeted URL</label>
                           <input type="text" class="form-control" name="promotional_url" placeholder="Enter Your Promotional Targeted URL"  value="{{old('promotional_url',$jsonData['promotional_url']??'')}}">
                           @error('promotional_url')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>
                     <div class="row">
                     <div class="col-md-6 mb-4">
                           <label for="brand_name" class="form-label">Set Your YouTube Promotional Video</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="Your can set your any youtube promotional video for your visitors"></i>
                           <input type="text" class="form-control" name="youtube_video" placeholder="Enter Your YouTube Promotional Video"  value="{{old('youtube_video',$jsonData['youtube_video']??'')}}">
                           @error('location_type')
                              <div class="text-danger">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>
                  </div>
                  <a href="javascript:;" target="_blank" class="btn btn-ghost-secondary btn-sm"><i class="bi bi-box-arrow-up-right"></i> Preview Tracking Page</a>
               </div>
            </div>
            <div class="tab-pane fade" id="nav-three-eg1" role="tabpanel" aria-labelledby="nav-three-eg1-tab">
               <div class="card">
                  <div class="card-body">            
                     <div class="row">
                        <div class="col-md-12 mb-4">
                           <label class="form-label" for="exampleFormControlTextarea1">Custom Cascading Style Sheets(CSS)</label>
                           <i class="bi bi-info-circle text-body ms-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top" data-bs-original-title="Write your own css as per your brand desing language."></i>
                           <textarea id="exampleFormControlTextarea1" class="form-control" placeholder=".track_button_color{background-color:#000;color:fff;}" rows="4" name="custom_style_script">{{old('custom_style_script',$jsonData['custom_style_script']??'')}}</textarea>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-sm-6 mb-4">
                           <label class="form-label">{{__('message.status')}}</label>
                           <div class="input-group input-group-sm-vertical">
                               <label class="form-control" for="status_yes">
                                   <span class="form-check">
                                       <input type="radio" id="status_yes" name="status" value="1"
                                           class="form-check-input" {{ ((isset($manageTrackingPage) && $manageTrackingPage->status) || !isset($manageTrackingPage)) ? 'checked' : '' }}>
                                       <span class="form-check-label">{{__('message.active')}}</label>
                               </span>
                               </label>
                               <label class="form-control" for="status_no">
                                   <span class="form-check">
                                       <input type="radio" id="status_no" name="status" value="0"
                                           class="form-check-input" {{ isset($manageTrackingPage) && !$manageTrackingPage->status ? 'checked' : '' }}>
                                       <span class="form-check-label">{{__('message.inactive')}}</label>
                               </span>
                               </label>
                           </div>
                           @error('status')
                           <span class="text-danger">{{ $message }}</span>
                           @enderror
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- End Tab Content -->         
         <div class="modal-footer my-3">            
            <button type="submit" class="btn btn-primary btn-sm" id="importButton">Save</button>
         </div>   
      </form>
   </x-slot>
</x-layout> 
<script>
   //this is for preview
   document.addEventListener('DOMContentLoaded', function () {
      const input1 = document.getElementById('website_domain');
      const input2 = document.getElementById('website_domain_preview');
      const input3 = document.getElementById('landing_page');
      input1.addEventListener('input', function () {
          const domainValue = input1.value;
          const updatedValue = `{{request()->root()}}/${domainValue}/track`;
          input2.value = updatedValue;
          input3.href = updatedValue;
      });
   });

</script>