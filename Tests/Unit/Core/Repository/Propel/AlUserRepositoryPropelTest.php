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

namespace RedKiteLabs\RedKiteCmsBundle\Tests\Unit\Core\Repository\Propel;

use RedKiteLabs\RedKiteCmsBundle\Tests\TestCase;
use RedKiteLabs\RedKiteCmsBundle\Core\Repository\Propel\AlUserRepositoryPropel;

/**
 * AlUserRepositoryTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AlUserRepositoryTest extends TestCase
{
    private $userRepository;
    private $pdo;

    protected function setUp()
    {
        parent::setUp();

        $this->pdo = $this->getMock('RedKiteLabs\RedKiteCmsBundle\Tests\Unit\Core\Repository\Propel\Pdo\MockPDO');
        $this->userRepository = new AlUserRepositoryPropel($this->pdo);
    }

    public function testGetRepositoryObjectClassName()
    {
        $this->assertEquals('\RedKiteLabs\RedKiteCmsBundle\Model\AlUser', $this->userRepository->getRepositoryObjectClassName());
    }
    
    /**
     * @expectedException \RedKiteLabs\RedKiteCmsBundle\Core\Exception\Content\General\InvalidArgumentTypeException
     * @expectedExceptionMessage exception_only_propel_user_objects_are_accepted
     */
    public function testModelObjectInjectedBySettersIsInvalid()
    {
        $modelObject = $this->getMock('\RedKiteLabs\RedKiteCmsBundle\Model\AlBlock');
        $this->userRepository->setRepositoryObject($modelObject);
    }

    public function testModelObjectInjectedBySetters()
    {
        $modelObject = $this->getMock('\RedKiteLabs\RedKiteCmsBundle\Model\AlUser');
        $this->assertEquals($this->userRepository, $this->userRepository->setRepositoryObject($modelObject));
        $this->assertEquals($modelObject, $this->userRepository->getModelObject());
    }
}