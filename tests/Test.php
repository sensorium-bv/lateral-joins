<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Sensorium\LateralJoins\DatabaseServiceProvider;
use Tests\Models\Order;
use Tests\Models\User;

class Test extends TestCase 
{
	public function setUp(): void
	{
		parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->timestamp('ordered_at');
            $table->timestamps();
        });

        Model::unguard();

        User::create(['name' => 'a']);
        User::create(['name' => 'b']);

        Order::create(['user_id' => 1, 'ordered_at' => Carbon::now()->subDay()]);
        Order::create(['user_id' => 1, 'ordered_at' => Carbon::now()->subHours(12)]);
        Order::create(['user_id' => 2, 'ordered_at' => Carbon::now()]);

        Model::reguard();
	}

	public function tearDown(): void
    {
    	parent::tearDown();
    }

	protected function getPackageProviders($app)
    {
        return [
            DatabaseServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $config = require __DIR__.'/config/database.php';

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', $config['pgsql']);
    }

    public function test_can_perform_lateral_left_join()
    {
        $sub1 = DB::table('orders')
                    ->select('user_id', DB::raw('min(ordered_at) AS ordered_at'))
                    ->groupBy('user_id');

        $sub2 = DB::table('orders')
                    ->select('user_id', 'ordered_at')
                    ->whereRaw('user_id = first_order.user_id')
                    ->whereRaw('ordered_at > first_order.ordered_at')
                    ->orderBy('ordered_at', 'DESC')
                    ->limit(1);

        $query = DB::table('users')
            ->select('users.name', 'first_order.ordered_at AS first_order', 'next_order.ordered_at AS next_order')
            ->joinSub($sub1, 'first_order', function($join) {
                $join->on('users.id', '=', 'first_order.user_id');
            })
            ->leftJoinLateral($sub2, 'next_order');

        $result = $query->get();

        //dd($query->toSql());

        $this->assertCount(2, $result);
        $this->assertEquals(Carbon::now()->subHours(12)->toDateTimeString(), $result->firstWhere('name', 'a')->next_order);
        $this->assertNull($result->firstWhere('name', 'b')->next_order);
    }

    public function test_can_perform_lateral_cross_join()
    {
        $sub1 = DB::table('orders')
                    ->select('user_id', DB::raw('min(ordered_at) AS ordered_at'))
                    ->groupBy('user_id');

        $sub2 = DB::table('orders')
                    ->select('user_id', 'ordered_at')
                    ->whereRaw('user_id = first_order.user_id')
                    ->whereRaw('ordered_at > first_order.ordered_at')
                    ->orderBy('ordered_at', 'DESC')
                    ->limit(1);

        $query = DB::table('users')
            ->select('users.name', 'first_order.ordered_at AS first_order', 'next_order.ordered_at AS next_order')
            ->joinSub($sub1, 'first_order', function($join) {
                $join->on('users.id', '=', 'first_order.user_id');
            })
            ->joinLateral($sub2, 'next_order');

        $result = $query->get();

        $this->assertCount(1, $result);
        $this->assertEquals(Carbon::now()->subHours(12)->toDateTimeString(), $result->firstWhere('name', 'a')->next_order);
    }

    /*public function test_lateral_with_cte()
    {
        $sub1 = DB::table('orders')
                    ->select('user_id', DB::raw('min(ordered_at) AS ordered_at'))
                    ->groupBy('user_id');

        $sub2 = DB::table('today_orders')
                    ->select('user_id', 'ordered_at')
                    ->whereRaw('user_id = first_order.user_id')
                    ->whereRaw('ordered_at > first_order.ordered_at')
                    ->orderBy('ordered_at', 'DESC')
                    ->limit(1);

        $query = DB::table('users')
            ->select('users.name', 'first_order.ordered_at AS first_order', 'next_order.ordered_at AS next_order')
            ->withExpression('today_orders', DB::table('orders')->whereDate('ordered_at', '>=', Carbon::today()->toDateString()))
            ->joinSub($sub1, 'first_order', function($join) {
                $join->on('users.id', '=', 'first_order.user_id');
            })
            ->leftJoinLateral($sub2, 'next_order');

        $result = $query->get();

        $this->assertCount(2, $result);
        $this->assertEquals(Carbon::now()->subHours(12)->toDateTimeString(), $result->firstWhere('name', 'a')->next_order);
        $this->assertNull($result->firstWhere('name', 'b')->next_order);
    }*/

}
