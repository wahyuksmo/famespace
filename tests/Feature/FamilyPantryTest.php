<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\PantryItem;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FamilyPantryTest extends TestCase
{
    use DatabaseTransactions;

    protected Family $family;

    protected User $parent;

    protected User $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Keluarga Pantry Test',
            'invite_code' => 'PAN-'.rand(1000, 9999),
            'monthly_budget' => 5000000,
            'currency' => 'IDR',
        ]);

        $proPlan = Plan::firstOrCreate(['code' => 'pro_monthly'], [
            'name' => 'FamKoji Pro Monthly',
            'price' => 20000,
            'billing_cycle' => 'monthly',
            'features' => ['pantry', 'timeline', 'events', 'unlimited_members'],
        ]);

        Subscription::create([
            'family_id' => $this->family->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->parent = User::create([
            'family_id' => $this->family->id,
            'name' => 'Ayah Pantry',
            'email' => 'ayah.pantry.'.rand(1000, 9999).'@test.com',
            'password' => bcrypt('password123'),
            'role' => 'Ayah',
        ]);

        $this->child = User::create([
            'family_id' => $this->family->id,
            'name' => 'Anak Pantry',
            'email' => 'anak.pantry.'.rand(1000, 9999).'@test.com',
            'password' => bcrypt('password123'),
            'role' => 'Anak',
        ]);
    }

    public function test_authenticated_user_can_view_pantry_index(): void
    {
        $response = $this->actingAs($this->parent)->get(route('pantry.index'));

        $response->assertOk()
            ->assertViewIs('modules.pantry.index')
            ->assertSee('pantry-datatable', false)
            ->assertSee('Inventaris Stok');
    }

    public function test_any_family_member_can_create_pantry_item(): void
    {
        $payload = [
            'name' => 'Beras Pandan Wangi',
            'category' => 'dapur',
            'quantity' => 10,
            'unit' => 'kg',
            'min_quantity' => 2,
            'expired_at' => now()->addMonths(6)->toDateString(),
            'notes' => 'Beli di pasar induk',
        ];

        $response = $this->actingAs($this->child)->post(route('pantry.store'), $payload);

        $response->assertRedirect(route('pantry.index'));

        $this->assertDatabaseHas('t_pantry_items', [
            'family_id' => $this->family->id,
            'name' => 'Beras Pandan Wangi',
            'category' => 'dapur',
            'created_by' => $this->child->id,
        ]);
    }

    public function test_family_member_can_adjust_stock_quantity(): void
    {
        $item = PantryItem::create([
            'family_id' => $this->family->id,
            'name' => 'Susu UHT Full Cream',
            'category' => 'kulkas',
            'quantity' => 5,
            'unit' => 'kotak',
            'min_quantity' => 2,
            'created_by' => $this->parent->id,
        ]);

        // Child drinks 1 milk -> decrement
        $this->actingAs($this->child)->patch(route('pantry.adjust', $item->id), [
            'delta' => -1,
        ]);

        $item->refresh();
        $this->assertEquals(4, (float) $item->quantity);

        // Child rests 2 boxes -> increment
        $this->actingAs($this->child)->patch(route('pantry.adjust', $item->id), [
            'delta' => 2,
        ]);

        $item->refresh();
        $this->assertEquals(6, (float) $item->quantity);
    }

    public function test_child_cannot_delete_item_but_parent_can(): void
    {
        $item = PantryItem::create([
            'family_id' => $this->family->id,
            'name' => 'Paracetamol 500mg',
            'category' => 'obat',
            'quantity' => 2,
            'unit' => 'strip',
            'min_quantity' => 1,
            'created_by' => $this->parent->id,
        ]);

        // Child attempts to delete -> fails with validation exception / back
        $response = $this->actingAs($this->child)->delete(route('pantry.destroy', $item->id));
        $response->assertSessionHasErrors(['permission']);
        $this->assertDatabaseHas('t_pantry_items', ['id' => $item->id]);

        // Parent deletes -> succeeds
        $response = $this->actingAs($this->parent)->delete(route('pantry.destroy', $item->id));
        $response->assertRedirect(route('pantry.index'));
        $this->assertDatabaseMissing('t_pantry_items', ['id' => $item->id]);
    }

    public function test_pantry_items_are_isolated_between_families(): void
    {
        $otherFamily = Family::create([
            'name' => 'Keluarga Tetangga',
            'invite_code' => 'PAN-'.rand(1000, 9999),
        ]);

        $otherItem = PantryItem::create([
            'family_id' => $otherFamily->id,
            'name' => 'Minyak Goreng 2L',
            'category' => 'dapur',
            'quantity' => 3,
            'unit' => 'liter',
            'min_quantity' => 1,
        ]);

        $response = $this->actingAs($this->parent)->getJson(route('pantry.paginate', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));
        $response->assertOk();
        $items = collect($response->json('data'));
        $this->assertFalse($items->contains('name', 'Minyak Goreng 2L'));
    }

    public function test_pantry_datatables_server_side_paginate_search_and_filters(): void
    {
        PantryItem::create([
            'family_id' => $this->family->id,
            'name' => 'Kecap Manis Sedaap',
            'category' => 'dapur',
            'quantity' => 1,
            'unit' => 'botol',
            'min_quantity' => 2,
            'notes' => 'Di lemari atas',
            'created_by' => $this->parent->id,
        ]);

        PantryItem::create([
            'family_id' => $this->family->id,
            'name' => 'Saus Tomat ABC',
            'category' => 'dapur',
            'quantity' => 5,
            'unit' => 'botol',
            'min_quantity' => 1,
            'created_by' => $this->parent->id,
        ]);

        // 1. DataTables standard load
        $dtRes = $this->actingAs($this->parent)->getJson(route('pantry.paginate', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));
        $dtRes->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data', 'summary']);
        $this->assertEquals(1, $dtRes->json('draw'));

        // 2. DataTables Search by keyword 'Kecap'
        $searchRes = $this->actingAs($this->parent)->getJson(route('pantry.paginate', [
            'draw' => 2,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'Kecap'],
        ]));
        $searchRes->assertOk();
        $this->assertEquals(1, $searchRes->json('recordsFiltered'));
        $this->assertEquals('Kecap Manis Sedaap', $searchRes->json('data.0.name'));

        // 3. DataTables filter low_stock
        $lowStockRes = $this->actingAs($this->parent)->getJson(route('pantry.paginate', [
            'draw' => 3,
            'start' => 0,
            'length' => 10,
            'filter' => 'low_stock',
        ]));
        $lowStockRes->assertOk();
        $data = collect($lowStockRes->json('data'));
        $this->assertTrue($data->contains('name', 'Kecap Manis Sedaap'));
        $this->assertFalse($data->contains('name', 'Saus Tomat ABC'));

        // 4. DataTables filter category
        $catRes = $this->actingAs($this->parent)->getJson(route('pantry.paginate', [
            'draw' => 4,
            'start' => 0,
            'length' => 10,
            'category' => 'obat',
        ]));
        $catRes->assertOk();
        $this->assertEmpty($catRes->json('data'));
    }
}
