<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CateringOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-03');
        $this->seed(\Database\Seeders\MenuSeeder::class);
    }

    public function test_home_page_shows_the_noshmark_account_flow(): void
    {
        $this->get('/')->assertOk()->assertSee('Create Your Account')->assertSee('NOSHMARK');
    }

    public function test_guests_are_sent_to_login_before_ordering(): void
    {
        $this->get(route('order.menu'))->assertRedirect(route('login'));
    }

    public function test_checkout_prices_the_menu_persons_packing_and_delivery_days(): void
    {
        $user = User::factory()->create(['phone' => '+923001234567']);
        $biryani = MenuItem::query()->where('slug', 'chicken-biryani')->firstOrFail();
        $seekh = MenuItem::query()->where('slug', 'chicken-seekh-kebab')->firstOrFail();

        $this->actingAs($user)
            ->post(route('order.menu.store'), [
                'serving_size' => 4,
                'qty' => [$biryani->id => 1, $seekh->id => 2],
            ])->assertRedirect(route('order.schedule'));

        $this->post(route('order.schedule.store'), [
            'schedule' => 'weekly',
            'start_date' => '2026-11-05',
            'menus_vary' => '1',
        ])->assertRedirect(route('order.packing'));

        $this->post(route('order.packing.store'), [
            'packing' => 'separate',
        ])->assertRedirect(route('order.delivery'));

        $this->post(route('order.delivery.store'), [
            'days_per_week' => 2,
            'per_person_schedule' => 'same',
            'address' => 'House 12, Street 4, Karachi',
            'delivery_payload' => json_encode([
                'deliveries' => [
                    '2026-11-04' => [
                        ['menu_item_id' => $biryani->id, 'quantity' => 1, 'persons' => null],
                    ],
                ],
            ]),
        ])->assertSessionHasErrors('delivery');

        $this->post(route('order.delivery.store'), [
            'days_per_week' => 2,
            'per_person_schedule' => 'same',
            'address' => 'House 12, Street 4, Karachi',
            'delivery_payload' => json_encode([
                'deliveries' => [
                    '2026-11-05' => [
                        ['menu_item_id' => $biryani->id, 'quantity' => 1, 'persons' => null],
                    ],
                    '2026-11-07' => [
                        ['menu_item_id' => $seekh->id, 'quantity' => 2, 'persons' => null],
                    ],
                ],
            ]),
        ])->assertRedirect(route('order.review'));

        $this->get(route('order.review'))
            ->assertOk()
            ->assertSee('Weekly')
            ->assertSee('Separate Packing')
            ->assertSee('100% payment')
            ->assertSee('PKR 16,040');

        $this->post(route('order.pay'), ['payment_method' => 'easypaisa'])
            ->assertRedirect();

        $order = Order::query()->with('deliveries.items')->firstOrFail();
        $this->assertSame(16040, $order->grand_total);
        $this->assertSame(16040, $order->amount_paid);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('weekly', $order->schedule);
        $this->assertCount(2, $order->deliveries);
        $this->assertTrue($order->deliveries->every(fn ($day) => $day->paid));
    }

    public function test_whole_pateela_is_priced_for_thirty_persons(): void
    {
        $user = User::factory()->create();
        $biryani = MenuItem::query()->where('slug', 'chicken-biryani')->firstOrFail();

        $this->actingAs($user)->post(route('order.menu.store'), [
            'serving_size' => 4,
            'qty' => [$biryani->id => 1],
        ]);
        $this->post(route('order.schedule.store'), [
            'schedule' => 'monthly',
            'start_date' => '2026-11-06',
            'menus_vary' => '1',
        ]);
        $this->post(route('order.packing.store'), ['packing' => 'pateela']);
        $this->post(route('order.delivery.store'), [
            'days_per_week' => 1,
            'per_person_schedule' => 'personalized',
            'address' => 'House 4, Clifton, Karachi',
            'delivery_payload' => json_encode([
                'deliveries' => [
                    '2026-11-06' => [
                        ['menu_item_id' => $biryani->id, 'quantity' => 3, 'persons' => 4],
                    ],
                ],
            ]),
        ])->assertRedirect(route('order.review'));

        $this->post(route('order.pay'), ['payment_method' => 'jazzcash']);

        $order = Order::query()->with('deliveries.items')->firstOrFail();
        $this->assertSame(30, $order->serving_size);
        $this->assertSame('pateela', $order->packing);
        $this->assertSame('same', $order->per_person_schedule);
        $this->assertSame(28500, $order->dishes_total);
        $this->assertSame(18000, $order->packing_total);
        $this->assertSame(120, $order->delivery_total);
        $this->assertSame(46620, $order->grand_total);
        $this->assertSame(1, $order->deliveries->first()->items->first()->quantity);
    }

    public function test_personalized_portions_must_cover_every_person(): void
    {
        $user = User::factory()->create();
        $biryani = MenuItem::query()->where('slug', 'chicken-biryani')->firstOrFail();
        $karahi = MenuItem::query()->where('slug', 'chicken-karahi')->firstOrFail();

        $this->actingAs($user)->post(route('order.menu.store'), [
            'serving_size' => 4,
            'qty' => [$biryani->id => 1],
        ]);
        $this->post(route('order.schedule.store'), [
            'schedule' => 'every_15_days',
            'start_date' => '2026-11-05',
            'menus_vary' => '1',
        ]);
        $this->post(route('order.packing.store'), ['packing' => 'separate']);

        $this->post(route('order.delivery.store'), [
            'days_per_week' => 1,
            'per_person_schedule' => 'personalized',
            'address' => 'Office 9, Shahrah-e-Faisal',
            'delivery_payload' => json_encode([
                'deliveries' => [
                    '2026-11-05' => [
                        ['menu_item_id' => $biryani->id, 'quantity' => 1, 'persons' => 3],
                        ['menu_item_id' => $karahi->id, 'quantity' => 1, 'persons' => 2],
                    ],
                ],
            ]),
        ])->assertSessionHasErrors('delivery');

        $this->post(route('order.delivery.store'), [
            'days_per_week' => 1,
            'per_person_schedule' => 'personalized',
            'address' => 'Office 9, Shahrah-e-Faisal',
            'delivery_payload' => json_encode([
                'deliveries' => [
                    '2026-11-05' => [
                        ['menu_item_id' => $biryani->id, 'quantity' => 1, 'persons' => 2],
                        ['menu_item_id' => $karahi->id, 'quantity' => 1, 'persons' => 2],
                    ],
                ],
            ]),
        ])->assertRedirect(route('order.review'));

        $this->get(route('order.review'))->assertSee('Every 15 Days')->assertSee('PKR 6,720');
    }

    public function test_a_future_delivery_menu_can_be_changed_when_the_new_total_is_paid(): void
    {
        $user = User::factory()->create();
        $biryani = MenuItem::query()->where('slug', 'chicken-biryani')->firstOrFail();
        $karahi = MenuItem::query()->where('slug', 'chicken-karahi')->firstOrFail();

        $this->actingAs($user)->post(route('order.menu.store'), [
            'serving_size' => 2,
            'qty' => [$biryani->id => 1],
        ]);
        $this->post(route('order.schedule.store'), [
            'schedule' => 'weekly',
            'start_date' => '2026-11-05',
            'menus_vary' => '1',
        ]);
        $this->post(route('order.packing.store'), ['packing' => 'separate']);
        $this->post(route('order.delivery.store'), [
            'days_per_week' => 1,
            'per_person_schedule' => 'same',
            'address' => 'House 2, Lahore',
            'delivery_payload' => json_encode([
                'deliveries' => [
                    '2026-11-10' => [
                        ['menu_item_id' => $biryani->id, 'quantity' => 1, 'persons' => null],
                    ],
                ],
            ]),
        ]);
        $this->post(route('order.pay'), ['payment_method' => 'bank']);

        $order = Order::query()->with('deliveries')->firstOrFail();
        $delivery = $order->deliveries->first();

        $this->post(route('orders.delivery.update', [$order, $delivery]), [
            'lines' => [
                ['menu_item_id' => $karahi->id, 'quantity' => 1],
            ],
        ])->assertSessionHasErrors('confirm_payment');

        $this->assertSame(950 * 2, $order->fresh()->dishes_total);

        $this->post(route('orders.delivery.update', [$order, $delivery]), [
            'lines' => [
                ['menu_item_id' => $karahi->id, 'quantity' => 1],
            ],
            'confirm_payment' => '1',
        ])->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame(1050 * 2, $order->dishes_total);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame($order->grand_total, $order->amount_paid);
        $this->assertSame('Chicken Karahi', $delivery->fresh()->items()->first()->name);
    }
}
