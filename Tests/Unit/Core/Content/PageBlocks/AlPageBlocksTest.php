<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteLabs\RedKiteCmsBundle\Tests\Unit\Core\Content\PageBlocks;

use RedKiteLabs\RedKiteCmsBundle\Tests\TestCase;
use RedKiteLabs\RedKiteCmsBundle\Core\Content\PageBlocks\AlPageBlocks;
use RedKiteLabs\RedKiteCmsBundle\Core\Exception\Content\General;

/**
 * AlPageBlocksTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlPageBlocksTest extends TestCase
{
    private $blockRepository;
    private $pageContentsContainer;

    protected function setUp()
    {
        parent::setUp();

        $this->blockRepository = $this->getMockBuilder('RedKiteLabs\RedKiteCmsBundle\Core\Repository\Propel\AlBlockRepositoryPropel')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->factoryRepository = $this->getMock('RedKiteLabs\RedKiteCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface');
        $this->factoryRepository->expects($this->any())
            ->method('createRepository')
            ->will($this->returnValue($this->blockRepository));

        $this->pageContentsContainer = new AlPageBlocks($this->factoryRepository);
    }

    /**
     * @expectedException RedKiteLabs\RedKiteCmsBundle\Core\Exception\Content\General\ArgumentIsEmptyException
     */
    public function testRefreshThrownAnExceptionWhenPageAndLanguageHaveNotBeenSet()
    {
        $this->pageContentsContainer->refresh();
    }

    /**
     * @expectedException RedKiteLabs\RedKiteCmsBundle\Core\Exception\Content\General\InvalidArgumentTypeException
     * @expectedException The language id must be a numeric value
     */
    public function testLanguageIdMustBeAnInteger()
    {
        $this->pageContentsContainer->setIdLanguage('fake');
    }

    /**
     * @expectedException RedKiteLabs\RedKiteCmsBundle\Core\Exception\Content\General\InvalidArgumentTypeException
     * @expectedException The page id must be a numeric value
     */
    public function testPageIdMustBeAnInteger()
    {
        $this->pageContentsContainer->setIdPage('fake');
    }

    /**
     * @expectedException RedKiteLabs\RedKiteCmsBundle\Core\Exception\Content\General\ArgumentIsEmptyException
     */
    public function testRefreshThrownAnExceptionWhenPageHaveNotBeenSet()
    {
        $this->pageContentsContainer
                ->setIdLanguage(2)
                ->refresh();
    }

    /**
     * @expectedException RedKiteLabs\RedKiteCmsBundle\Core\Exception\Content\General\ArgumentIsEmptyException
     */
    public function testRefreshThrownAnExceptionWhenLanguageHaveNotBeenSet()
    {
        $this->pageContentsContainer
                ->setIdPage(2)
                ->refresh();
    }

    public function testAnEmptyArrayIsRetrievedWhenAnyBlockExists()
    {
        $this->blockRepository->expects($this->once())
            ->method('retrieveContents')
            ->will($this->returnValue(array()));

        $this->pageContentsContainer
                ->setIdLanguage(2)
                ->setIdPage(2)
                ->refresh();

        $this->assertEquals(0, count($this->pageContentsContainer->getBlocks()));
        $this->assertEquals(2, $this->pageContentsContainer->getIdLanguage());
        $this->assertEquals(2, $this->pageContentsContainer->getIdPage());
    }

    public function testContentsAreRetrieved()
    {
        $blocks = array(
            $this->setUpBlock('logo', 'Text'),
            $this->setUpBlock('logo', 'Text'),
            $this->setUpBlock('menu', 'Menu'),
        );

        $this->blockRepository->expects($this->once())
            ->method('retrieveContents')
            ->will($this->returnValue($blocks));

        $this->pageContentsContainer
                ->setIdLanguage(2)
                ->setIdPage(2)
                ->refresh();

        $this->assertEquals(2, count($this->pageContentsContainer->getBlocks()));
        $this->assertEquals(2, count($this->pageContentsContainer->getSlotBlocks('logo')));
        $this->assertEquals(1, count($this->pageContentsContainer->getSlotBlocks('menu')));
        $this->assertEquals(array('Text', 'Menu'), $this->pageContentsContainer->getBlockTypes());
    }
    
    public function testBlockIsAdded()
    {
        $this->assertEquals($this->pageContentsContainer, $this->pageContentsContainer->add("logo", array('Content' => 'My value')));

        $this->assertCount(1, $this->pageContentsContainer->getBlocks());
        $this->checkOneBlock('logo', 'My value');
    }

    public function testBlockIsEdited()
    {
        $this->pageContentsContainer->add("logo", array('Content' => 'My value'));
        $this->pageContentsContainer->add("logo", array('Content' => 'My new value'), 0);

        $this->assertCount(1, $this->pageContentsContainer->getBlocks());
        $this->checkOneBlock('logo', 'My new value');
    }

    public function testBlockIsAddedWhenAnInvalidPositionNumberIsGiven()
    {
        $this->pageContentsContainer->add("logo", array('Content' => 'My value'));
        $this->pageContentsContainer->add("logo", array('Content' => 'My new value'), 5);

        $this->assertCount(1, $this->pageContentsContainer->getBlocks());
        $block = $this->pageContentsContainer->getSlotBlocks('logo');
        $this->assertCount(2, $block);
    }
    
    public function testNullContents()
    {
        $this->pageContentsContainer->addRange(array("logo" => null));

        $this->assertCount(1, $this->pageContentsContainer->getBlocks());
        $block = $this->pageContentsContainer->getSlotBlocks('logo');
        $this->assertNull($block);
    }

    public function testARangeOfBlocksIsAdded()
    {
        $this->pageContentsContainer->addRange(array("logo" => array(array('Content' => 'My value'), array('Content' => 'My new value'))));

        $this->assertCount(1, $this->pageContentsContainer->getBlocks());
        $block = $this->pageContentsContainer->getSlotBlocks('logo');
        $this->assertCount(2, $block);
        $this->assertEquals('My value', $block[0]['Content']);
        $this->assertEquals('My new value', $block[1]['Content']);
    }
    
    public function testARangeOfBlocksIsOverriden()
    {
        $this->pageContentsContainer->addRange(array("logo" => array(array('Content' => 'My value'), array('Content' => 'My new value'))));
        $this->pageContentsContainer->addRange(array("logo" => array(array('Content' => 'Overrided value'))), true);

        $this->assertCount(1, $this->pageContentsContainer->getBlocks());
        $block = $this->pageContentsContainer->getSlotBlocks('logo');
        $this->assertCount(1, $block);
        $this->assertEquals('Overrided value', $block[0]['Content']);
    }

    public function testARangeOfBlocksIsAddedOnMoreSlots()
    {
        $this->pageContentsContainer->addRange(array("logo" => array(array('Content' => 'My value'), array('Content' => 'My new value')),
            "nav_menu" => array(array('Content' => 'My value'))));

        $this->assertCount(2, $this->pageContentsContainer->getBlocks());
    }

    /**
     * @expectedException RedKiteLabs\RedKiteCmsBundle\Core\Exception\General\InvalidArgumentException
     */
    public function testAnExeptionIsThrowsWhenTryingToClearANonExistentSlot()
    {
        $this->assertEquals($this->pageContentsContainer, $this->pageContentsContainer->clearSlotBlocks('logo'));
    }

    public function testASlotIsCleared()
    {
        $this->pageContentsContainer->addRange(array("logo" => array(array('Content' => 'My value'))));
        $this->assertCount(1, $this->pageContentsContainer->getSlotBlocks('logo'));

        $this->assertEquals($this->pageContentsContainer, $this->pageContentsContainer->clearSlotBlocks('logo'));
        $this->assertCount(0, $this->pageContentsContainer->getSlotBlocks('logo'));
    }

    public function testAllSlotsAreCleared()
    {
        $this->pageContentsContainer->addRange(array("logo" => array(array('Content' => 'My value')), "nav-menu" => array(array('Content' => 'My value'))));
        $this->assertCount(2, $this->pageContentsContainer->getBlocks());
        $this->assertCount(1, $this->pageContentsContainer->getSlotBlocks('logo'));
        $this->assertCount(1, $this->pageContentsContainer->getSlotBlocks('nav-menu'));

        $this->assertEquals($this->pageContentsContainer, $this->pageContentsContainer->clearSlots());
        $this->assertCount(2, $this->pageContentsContainer->getBlocks());
        $this->assertCount(0, $this->pageContentsContainer->getSlotBlocks('logo'));
        $this->assertCount(0, $this->pageContentsContainer->getSlotBlocks('nav-menu'));
    }

    /**
     * @expectedException RedKiteLabs\RedKiteCmsBundle\Core\Exception\General\InvalidArgumentException
     */
    public function testAnExeptionIsThrowsWhenTryingToRemoveANonExistentSlot()
    {
        $this->assertEquals($this->pageContentsContainer, $this->pageContentsContainer->removeSlot('logo'));
    }

    public function testASlotIsRemoved()
    {
        $this->pageContentsContainer->addRange(array("logo" => array(array('Content' => 'My value'))));
        $this->assertCount(1, $this->pageContentsContainer->getBlocks());

        $this->assertEquals($this->pageContentsContainer, $this->pageContentsContainer->removeSlot('logo'));
        $this->assertCount(0, $this->pageContentsContainer->getBlocks());
    }

    public function testAllSlotsAreRemoved()
    {
        $this->pageContentsContainer->addRange(array("logo" => array(array('Content' => 'My value')), "nav-menu" => array(array('Content' => 'My value'))));
        $this->assertCount(2, $this->pageContentsContainer->getBlocks());

        $this->assertEquals($this->pageContentsContainer, $this->pageContentsContainer->removeSlots());
        $this->assertCount(0, $this->pageContentsContainer->getBlocks());
    }

    private function checkOneBlock($slotName, $expectedContent)
    {
        $block = $this->pageContentsContainer->getSlotBlocks($slotName);
        $this->assertTrue(count($block) == 1);
        $this->assertEquals($expectedContent, $block[0]['Content']);
    }

    private function setUpBlock($slotName, $type = null)
    {
        $block = $this->getMock('RedKiteLabs\RedKiteCmsBundle\Model\AlBlock');
        $block->expects($this->once())
            ->method('getSlotName')
            ->will($this->returnValue($slotName));

        if (null !== $type) {
            $block->expects($this->once())
                ->method('getType')
                ->will($this->returnValue($type));
        }

        return $block;
    }
}
