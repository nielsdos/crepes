<?php

namespace Tests\Unit;

use App\Http\Controllers\CourseController;
use PHPUnit\Framework\TestCase;

class CourseControllerStreetAddressHeuristicTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testStreetAddressHeuristic($input, $result)
    {
        $this->assertEquals($result, CourseController::couldBeAValidStreetAddress($input));
    }

    public function dataProvider(): array
    {
        return [
            ['', false],
            ['a', false],
            ['City', false],
            ['on-line', false],
            ['Virtual Event City, abc', false],
            ['City Center, 1000 City', true],
            ['Street 123, 1000 City', true],
            ['Street 123, City 1000', true],
            ['3000 Random Road, Chicago AA 123456', true],
        ];
    }
}
