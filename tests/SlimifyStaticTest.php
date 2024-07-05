<?php
/**
 * District5 - GameLeaderboard
 *
 * @copyright District5
 *
 * @author District5
 * @link https://www.district5.co.uk
 *
 * @license This software and associated documentation (the "Software") may not be
 * used, copied, modified, distributed, published or licensed to any 3rd party
 * without the written permission of District5 or its author.
 *
 * The above copyright notice and this permission notice shall be included in
 * all licensed copies of the Software.
 *
 */

namespace SlimifyTests;

use Slim\Psr7\Factory\ResponseFactory;
use Slimify\SlimifyInstance;
use Slimify\SlimifyStatic;

/**
 * Class SlimifyStaticTest
 * @package SlimifyTests
 */
class SlimifyStaticTest extends TestAbstract
{
    public function testSlimifyStatic()
    {
        $instance = SlimifyStatic::retrieve(
            new SlimifyInstance(new ResponseFactory())
        );
        $this->assertInstanceOf(SlimifyStatic::class, $instance);
        $this->assertInstanceOf(SlimifyInstance::class, $instance->getApp());

        $instance->setErrorViewParams(['error' => 'this is an error']);
        $this->assertEquals('this is an error', $instance->getErrorViewParams()['error']);
    }
}
