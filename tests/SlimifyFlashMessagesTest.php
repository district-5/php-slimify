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
use District5\SimpleSessionStore\Session;
use District5\SimpleSessionStore\SessionException;
use Slimify\SlimifyFactory;
use Slimify\SlimifyFlashMessages;

/**
 * Class SlimifyFlashMessagesTest
 * @package SlimifyTests
 */
class SlimifyFlashMessagesTest extends TestAbstract
{
    /**
     * @return void
     * @throws SessionException
     * @runInSeparateProcess
     */
    public function testBasicFlashMessages()
    {
        $flash = new SlimifyFlashMessages();
        $this->assertEmpty($flash->getMessages());
        $flash->addError('This is an error');
        $flash->addInfo('This is an info message');
        $flash->addSuccess('This is a success message');
        $flash->addWarning('This is a warning message');

        $messages = $flash->getMessages();
        $this->assertNotEmpty($messages);
        $this->assertCount(4, $messages);

        $i = SlimifyFactory::createSlimify(
            new Container(),
            false
        );
        $this->assertInstanceOf(SlimifyFlashMessages::class, $i->flash());
    }
    /**
     * @return void
     * @throws SessionException
     * @runInSeparateProcess
     */
    public function testForceInvalidSession()
    {
        $flash = new SlimifyFlashMessages();
        unset($_SESSION);
        $this->assertEmpty($flash->getMessages());
        $flash->addError('This is an error');
        $messages = $flash->getMessages();
        $this->assertEmpty($messages); // session is not set
    }
}
