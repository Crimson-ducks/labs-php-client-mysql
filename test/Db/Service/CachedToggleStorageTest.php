<?php
namespace Clearbooks\Labs\Db\Service;

use Clearbooks\Labs\Db\Entity\Toggle;
use Clearbooks\Labs\LabsTest;

class CachedToggleStorageTest extends LabsTest
{
    /**
     * @var CachedToggleStorage
     */
    private $cachedToggleStorage;

    /**
     * @var ToggleStorageMock
     */
    private $toggleStorageMock;

    public function setUp()
    {
        parent::setUp();
        $this->toggleStorageMock = new ToggleStorageMock();
        $this->cachedToggleStorage = new CachedToggleStorage( $this->toggleStorageMock );
    }

    /**
     * @test
     */
    public function WhenCallingCachedToggleStorageMethodsMultipleTimesWithSameArguments_SpyMethodsWillBeCalledOnceAndRightAfterTheFirstCall()
    {
        $toggleStorageSpy = new ToggleStorageSpy();
        $cachedToggleStorage = new CachedToggleStorage( $toggleStorageSpy );

        for ( $i = 0; $i < 3; ++$i ) {
            $cachedToggleStorage->getGroupPolicyOfToggle( "test toggle", "1" );
            $this->assertEquals( 1, $toggleStorageSpy->getGetGroupPolicyOfToggleCallCounter() );

            $cachedToggleStorage->getUserPolicyOfToggle( "test toggle", "1" );
            $this->assertEquals( 1, $toggleStorageSpy->getGetUserPolicyOfToggleCallCounter() );

            $cachedToggleStorage->getToggleByName( "test toggle" );
            $this->assertEquals( 1, $toggleStorageSpy->getGetToggleByNameCallCounter() );

            $cachedToggleStorage->getToggleById( 1 );
            $this->assertEquals( 1, $toggleStorageSpy->getGetToggleByIdCallCounter() );
        }
    }

    /**
     * @test
     */
    public function GivenEmptyToggleStorage_WhenCallingCachedToggleStorageMethods_DoNotReturnObjects()
    {
        $this->assertNull( $this->cachedToggleStorage->getGroupPolicyOfToggle( "test toggle", "1" ) );
        $this->assertNull( $this->cachedToggleStorage->getUserPolicyOfToggle( "test toggle", "1" ) );
        $this->assertNull( $this->cachedToggleStorage->getToggleByName( "test toggle" ) );
        $this->assertNull( $this->cachedToggleStorage->getToggleById( 1 ) );
    }

    /**
     * @test
     */
    public function GivenAToggleExists_WhenCallingGetToggleById_ReturnsToggle()
    {
        $toggle = new Toggle();
        $toggle->setId( 1 );

        $this->toggleStorageMock->setToggleIdToToggleMap( [ $toggle->getId() => $toggle ] );
        $retrievedToggle = $this->cachedToggleStorage->getToggleById( $toggle->getId() );

        $this->assertSame( $toggle, $retrievedToggle );
    }

    /**
     * @test
     */
    public function GivenAToggleExists_WhenCallingGetToggleByIdTwiceAndReplacingToggleBetweenTheseCalls_ReturnsOriginalToggle()
    {
        $toggle = new Toggle();
        $toggle->setId( 1 );

        $newToggle = clone $toggle;

        $this->toggleStorageMock->setToggleIdToToggleMap( [ $toggle->getId() => $toggle ] );
        $this->cachedToggleStorage->getToggleById( $toggle->getId() );

        $this->toggleStorageMock->setToggleIdToToggleMap( [ $newToggle->getId() => $newToggle ] );
        $retrievedToggle = $this->cachedToggleStorage->getToggleById( $toggle->getId() );

        $this->assertSame( $toggle, $retrievedToggle );
    }

    /**
     * @test
     */
    public function GivenAToggleExists_WhenCallingGetToggleByName_ReturnsToggle()
    {
        $toggle = new Toggle();
        $toggle->setId( 1 );
        $toggle->setName( "test toggle" );

        $this->toggleStorageMock->setToggleNameToToggleMap( [ $toggle->getName() => $toggle ] );
        $retrievedToggle = $this->cachedToggleStorage->getToggleByName( $toggle->getName() );

        $this->assertSame( $toggle, $retrievedToggle );
    }

    /**
     * @test
     */
    public function GivenAToggleExists_WhenCallingGetToggleByNameTwiceAndReplacingToggleBetweenTheseCalls_ReturnsOriginalToggle()
    {
        $toggle = new Toggle();
        $toggle->setId( 1 );
        $toggle->setName( "test toggle" );

        $newToggle = clone $toggle;

        $this->toggleStorageMock->setToggleNameToToggleMap( [ $toggle->getName() => $toggle ] );
        $this->cachedToggleStorage->getToggleByName( $toggle->getName() );

        $this->toggleStorageMock->setToggleNameToToggleMap( [ $newToggle->getName() => $newToggle ] );
        $retrievedToggle = $this->cachedToggleStorage->getToggleByName( $toggle->getName() );

        $this->assertSame( $toggle, $retrievedToggle );
    }

    /**
     * @test
     */
    public function GivenAToggleExistsAndEnabledForGroup_WhenCallingGetGroupPolicyOfToggle_ReturnsEnabledState()
    {
        $toggleName = "test toggle";
        $groupId = "1";

        $this->toggleStorageMock->setToggleGroupPolicyMap( [ "test toggle" => [ $groupId => true ] ] );
        $toggleEnabledByGroup = $this->cachedToggleStorage->getGroupPolicyOfToggle( $toggleName, $groupId );

        $this->assertTrue( $toggleEnabledByGroup );
    }

    /**
     * @test
     */
    public function GivenAToggleExistsAndEnabledForGroup_WhenCallingGetGroupPolicyOfToggleTwiceAndChangingEnabledStateToFalseBetweenCalls_ReturnsEnabledState()
    {
        $toggleName = "test toggle";
        $groupId = "1";

        $this->toggleStorageMock->setToggleGroupPolicyMap( [ "test toggle" => [ $groupId => true ] ] );
        $this->cachedToggleStorage->getGroupPolicyOfToggle( $toggleName, $groupId );

        $this->toggleStorageMock->setToggleGroupPolicyMap( [ "test toggle" => [ $groupId => false ] ] );
        $toggleEnabledByGroup = $this->cachedToggleStorage->getGroupPolicyOfToggle( $toggleName, $groupId );

        $this->assertTrue( $toggleEnabledByGroup );
    }

    /**
     * @test
     */
    public function GivenAToggleExistsAndEnabledForUser_WhenCallingGetToggleByName_ReturnsEnabledState()
    {
        $toggleName = "test toggle";
        $userId = "1";

        $this->toggleStorageMock->setToggleUserPolicyMap( [ "test toggle" => [ $userId => true ] ] );
        $toggleEnabledByUser = $this->cachedToggleStorage->getUserPolicyOfToggle( $toggleName, $userId );

        $this->assertTrue( $toggleEnabledByUser );
    }

    /**
     * @test
     */
    public function GivenAToggleExistsAndEnabledForGroup_WhenCallingGetToggleByNameTwiceAndChangingEnabledStateToFalseBetweenCalls_ReturnsEnabledState()
    {
        $toggleName = "test toggle";
        $userId = "1";

        $this->toggleStorageMock->setToggleUserPolicyMap( [ "test toggle" => [ $userId => true ] ] );
        $this->cachedToggleStorage->getUserPolicyOfToggle( $toggleName, $userId );

        $this->toggleStorageMock->setToggleUserPolicyMap( [ "test toggle" => [ $userId => false ] ] );
        $toggleEnabledByUser = $this->cachedToggleStorage->getUserPolicyOfToggle( $toggleName, $userId );

        $this->assertTrue( $toggleEnabledByUser );
    }
}