<?php 
namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderTotal;
use App\Models\Customer;
use App\Models\ChannelSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        $order_id = 'TEST' . $this->faker->unique()->numberBetween(1000, 9999);
        $companyId = session('company_id') ?? 0; 
        $channelSetting = ChannelSetting::where('company_id', $companyId)
            ->where('channel_code', 'custom')
            ->where('status', 1)
            ->first();
        $channelId = $channelSetting ? $channelSetting->channel_id : 0;
        $orderProduct = OrderProduct::factory()->make();
        // Calculate subtotal and total
        $shipping = 30;
        $discount = 20;
        // Calculate subtotal and total
        $subtotal = $orderProduct->unit_price * $orderProduct->quantity;
        $total = $subtotal+$shipping-$discount;

        // Haryana cities and pincodes
        $haryanaCities = [
            ['city' => 'Gurgaon', 'pincode' => '122001'],
            ['city' => 'Faridabad', 'pincode' => '121001'],
            ['city' => 'Panipat', 'pincode' => '132103'],
            ['city' => 'Ambala', 'pincode' => '134003'],
            ['city' => 'Rohtak', 'pincode' => '124001'],
            ['city' => 'Hisar', 'pincode' => '125001'],
            ['city' => 'Sonipat', 'pincode' => '131001'],
            ['city' => 'Kurukshetra', 'pincode' => '136118'],
            ['city' => 'Yamunanagar', 'pincode' => '135001']
        ];
        $cityData = $this->faker->randomElement($haryanaCities);
        $phone = $this->faker->numerify('9#########');
        return [
            'vendor_order_id' => $order_id,
            'vendor_order_number' => $order_id,
            'channel_id' => $channelId,
            'channel_order_date' => date('Y-m-d H:i:s'),
            'status_code' => 'N',
            'financial_status' => null,
            'customer_id' => Customer::factory(),
            'company_id' => $companyId,
            'fullname' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone_number' => $phone,
            's_fullname' => $this->faker->name,
            's_company' => $this->faker->company,
            's_complete_address' => $this->faker->address,
            's_landmark' => $this->faker->word,
            's_phone' => $phone,
            's_zipcode' => $cityData['pincode'],
            's_city' => $cityData['city'],
            's_state_code' => 'HR',
            's_country_code' => 'IN',
            'b_fullname' => $this->faker->name,
            'b_company' => $this->faker->company,
            'b_complete_address' => $this->faker->address,
            'b_landmark' => $this->faker->word,
            'b_phone' => $phone,
            'b_zipcode' => $cityData['pincode'],
            'b_city' => $cityData['city'],
            'b_state_code' => 'HR',
            'b_country_code' => 'IN',
            'invoice_prefix' => 'INV',
            'invoice_number' => $order_id,
            'package_length' => 10,
            'package_breadth' => 10,
            'package_height' => 10,
            'package_dead_weight' => 0.05,
            'notes' => $this->faker->sentence,
            'order_tags' => '',
            'payment_mode' => 'cod',
            'payment_method' => 'Cash on delivery',
            'currency_code' => 'INR',
            'sub_total' => $subtotal,
            'order_total' => $total,
        ];
    }


    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            // Create the order product
            $orderProduct = OrderProduct::factory()->create([
                'order_id' => $order->id 
            ]);
            $shipping = 30;
            $discount = 20;
            // Calculate subtotal and total
            $subtotal = $orderProduct->unit_price * $orderProduct->quantity;
            $total = $subtotal+$shipping-$discount; // If needed, you can add tax/shipping etc. here
            
            // Create discount entry
            OrderTotal::factory()->create([
                'order_id' => $order->id,
                'value' => $discount,
                'title' => 'Discount',
                'code' => 'discount',
                'sort_order' => 4,
            ]); 
            OrderTotal::factory()->create([
                'order_id' => $order->id,
                'value' => $shipping,
                'title' => 'Shipping',
                'code' => 'shipping',
                'sort_order' => 2,
            ]);           
            // Create subtotal entry
            OrderTotal::factory()->create([
                'order_id' => $order->id,
                'value' => $subtotal,
                'title' => 'Subtotal',
                'code' => 'sub_total',
                'sort_order' => 1,
            ]);
            
            // Create total entry
            OrderTotal::factory()->create([
                'order_id' => $order->id,
                'value' => $total,
                'title' => 'Total',
                'code' => 'total',
                'sort_order' => 9,
            ]);
        });
    }

}
