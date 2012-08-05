<?php
/**
 *
 * License, TERMS and CONDITIONS
 *
 * This software is licensed under the GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * Please read the license here : http://www.gnu.org/licenses/lgpl-3.0.txt
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * ATTRIBUTION REQUIRED
 * 4. All web pages generated by the use of this software, or at least
 *       the page that lists the recent questions (usually home page) must include
 *    a link to the http://www.lampcms.com and text of the link must indicate that
 *    the website\'s Questions/Answers functionality is powered by lampcms.com
 *    An example of acceptable link would be "Powered by <a href="http://www.lampcms.com">LampCMS</a>"
 *    The location of the link is not important, it can be in the footer of the page
 *    but it must not be hidden by style attributes
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This product includes GeoLite data created by MaxMind,
 *  available from http://www.maxmind.com/
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2012 (or current year) Dmitri Snytkine
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * @link       http://www.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */


namespace Lampcms;
require_once 'bootstrap.php';

/**
 * Run after IniTest
 *
 */
class RegistryTest extends LampcmsUnitTestCase
{

    protected $Registry;

    /**
     * We want to override parent setUp
     * because we want to use new instance of Registry
     * and not use getInstance() here
     * (non-PHPdoc)
     * @see Lampcms.LampcmsUnitTestCase::setUp()
     */
    public function setUp()
    {
        $this->Registry = new Registry();
    }

    public function testSingleInstance()
    {
        $o1 = Registry::getInstance();
        $o2 = Registry::getInstance();
        $this->assertEquals($o1->hashCode(), $o2->hashCode(), 'Registry::getInstance returned 2 different objects. This is bad times!');
    }


    public function testIniAsShared()
    {
        $o1 = $this->Registry->Ini;
        $o2 = $this->Registry->Ini;
        $this->assertInstanceOf('\Lampcms\Ini', $o1);
        $this->assertSame($o1, $o2);
    }


    public function testMongoAsShared()
    {
        $o1 = $this->Registry->Mongo;
        $o2 = $this->Registry->Mongo;
        $this->assertInstanceOf('\Lampcms\Mongo\DB', $o1);
        $this->assertSame($o1, $o2);
    }

    public function testIncrementorAsShared()
    {
        $o1 = $this->Registry->Incrementor;
        $o2 = $this->Registry->Incrementor;
        $this->assertInstanceOf('\Lampcms\Mongo\Incrementor', $o1);
        $this->assertSame($o1, $o2);
    }


    public function testCacheAsShared()
    {
        $o1 = $this->Registry->Cache;
        $o2 = $this->Registry->Cache;
        $this->assertInstanceOf('\Lampcms\Cache\Cache', $o1);
        $this->assertSame($o1, $o2);
    }


    public function testAclAsShared()
    {
        $o1 = $this->Registry->Acl;
        $o2 = $this->Registry->Acl;
        $this->assertInstanceOf('\Lampcms\Acl\Acl', $o1);
        $this->assertSame($o1, $o2);
    }


    public function testRequestAsShared()
    {
        $o1 = $this->Registry->Request;
        $o2 = $this->Registry->Request;
        $this->assertInstanceOf('\Lampcms\Request', $o1);
        $this->assertSame($o1, $o2);
    }


    public function testDispatcherAsShared()
    {
        $o1 = $this->Registry->Dispatcher;
        $o2 = $this->Registry->Dispatcher;
        $this->assertInstanceOf('\Lampcms\Event\Dispatcher', $o1);
        $this->assertSame($o1, $o2);
    }


    public function testMongoDoc()
    {
        $o1 = $this->Registry->MongoDoc;
        $o2 = $this->Registry->MongoDoc;
        $this->assertInstanceOf('\Lampcms\Mongo\Doc', $o1);
        $this->assertInstanceOf('\Lampcms\Mongo\Doc', $o2);
        $this->assertNotSame($o1, $o2);
    }


    public function testResource()
    {
        $o1 = $this->Registry->Resource;
        $o2 = $this->Registry->Resource;
        $this->assertInstanceOf('\Lampcms\Resource', $o1);
        $this->assertInstanceOf('\Lampcms\Resource', $o2);
        $this->assertNotSame($o1, $o2);
    }


    public function testNonExistant()
    {
        $o1 = $this->Registry->Abcdefg;
        $this->assertTrue(null === $o1);
    }


    public function testSetThenGet()
    {
        $this->Registry->Zxcvb = new \stdClass();
        $o1 = $this->Registry->Zxcvb;
        $this->assertInstanceOf('stdClass', $o1);
    }


    public function testSetGetUnset()
    {
        $this->Registry->qwertyu = new \stdClass();
        $o1 = $this->Registry->qwertyu;
        $this->assertInstanceOf('stdClass', $o1);
        unset($this->Registry->qwertyu);
        $o2 = $this->Registry->qwertyu;
        $this->assertEmpty($o2);
    }


    public function testCustomMongoDoc()
    {
        $o1 = $this->Registry->MongoMyTest;
        $this->assertInstanceOf('\Lampcms\Mongo\Doc', $o1);
        $this->assertEquals('MYTEST', $o1->getCollectionName());
    }


    public function testEmpty()
    {
        $this->assertTrue(empty($this->Registry->some_fake_poiuyt));
    }


    public function testNotEmpty()
    {
        $this->assertFalse(empty($this->Registry->Ini));
    }

}