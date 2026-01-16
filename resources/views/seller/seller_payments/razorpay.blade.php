<!DOCTYPE html>
<html>
   <head>
      <title>Razorpay Payment</title>
      <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
   </head>
   <body>
      <form id="payment-form" style="display: none;">
         <input type="number" name="amount" id="amount" value="{{$amount}}" required>
         <input type="number" name="setup_fee" id="setup_fee" value="{{$setup_fee}}" >
         <input type="text" name="plan" id="plan" value="{{$plan}}" required>
         <input type="number" name="duration_months" id="duration_months" value="{{$duration_months}}" >
         <input type="number" name="plan_id" id="plan_id" value="{{$plan_id}}" required>
      </form>
      <script>
         window.onload = function () {
             const amount = document.getElementById('amount').value;
             const setupFee = document.getElementById('setup_fee').value;
             const plan = document.getElementById('plan').value;
             const duration_months = document.getElementById('duration_months').value;
             const planId = document.getElementById('plan_id').value;
             // Fetch the order details as soon as the page loads
             fetch('/admin/payment/order', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                     amount: amount,
                     duration_months:duration_months,
                     plan: plan,
                     setup_fee: setupFee,
                     plan_id: planId,
                 })
             })
             .then(response => response.json())
             .then(data => {
                 const options = {
                     key: '{{ config('razorpay.key_id') }}', // Razorpay Key ID from .env
                     amount: amount * 100, // Amount in paisa
                     currency: 'INR',
                     name: 'parcelmind',
                     description: 'Payment Description',
                     order_id: data.order_id,
                     handler: function (response) {
                         // Handle the payment succes
                         fetch('/admin/payment/callback', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                             },
                             body: JSON.stringify(response)
                         })
                         .then(callbackResponse => callbackResponse.json())
                         .then(data => {
                             window.location.href = "{{route('subscription_plans')}}";
                         })
                         .catch(error => {
                             window.location.href = "{{route('subscription_plans')}}";
                             
                         });
                     },
                     modal: {
                         ondismiss: function () {
                             window.location.href = "{{route('subscription_plans')}}";
                         }
                     },
                     prefill: {
                         name: "{{ $user->name }}",
                         email: "{{ $user->email }}"
                     },
                     theme: {
                         color: "#3399cc"
                     }
                 };
                 // Open Razorpay payment widget
                 const rzp = new Razorpay(options);
                 rzp.open();
             })
             .catch(error => console.error('Error creating Razorpay order:', error));
         };
      </script>
   </body>
</html>