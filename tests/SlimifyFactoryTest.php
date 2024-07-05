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

use DI\Container;
use Slimify\SlimifyFactory;
use Slimify\SlimifyInstance;

/**
 * Class SlimifyInstanceTest
 * @package SlimifyTests
 */
class SlimifyFactoryTest extends TestAbstract
{
    public function testSlimifyFactory()
    {
        $container = new Container();
        $container->set('foo', 'bar');

        $instance = SlimifyFactory::createSlimify(
            $container,
            false
        );

        $this->assertInstanceOf(SlimifyInstance::class, $instance);
        $this->assertEquals('bar', $instance->getContainer()->get('foo'));
        $this->assertFalse($instance->isDevelopment());
        $this->assertTrue($instance->isProduction());
    }
}
