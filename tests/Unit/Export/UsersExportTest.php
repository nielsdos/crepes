<?php

namespace Tests\Unit\Export;

use App\Models\User;
use App\Services\Exports\UsersExportable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class UsersExportTest extends TestCase
{
    use RefreshDatabase;

    private Collection $users;

    protected function afterRefreshingDatabase()
    {
        $this->users = User::factory()->count(10)->create();
    }

    public function testHeading(): void
    {
        $exportable = new UsersExportable(User::withTrashed());
        $this->assertCount(8, $exportable->heading());
    }

    public function testEveryUserIsInTheCollection(): void
    {
        $exportable = new UsersExportable(User::withTrashed());
        $collection = $exportable->collection();
        $this->assertCount($this->users->count(), $collection);
        foreach ($this->users as $user) {
            $this->assertTrue($collection->where('id', '=', $user->id)->isNotEmpty());
        }
    }

    public function testMapping(): void
    {
        $exportable = new UsersExportable(User::withTrashed());
        $collection = $exportable->collection();
        foreach ($collection as $user) {
            $mapped = $exportable->map($user);
            $this->assertCount(count($exportable->heading()), $mapped);
        }
    }
}
