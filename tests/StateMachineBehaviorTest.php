<?php
namespace StateMachineBehavior\tests;

use Propel\Generator\Util\QuickBuilder;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class StateMachineBehaviorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('TableWithStateMachineBehavior')) {
            $schema = <<<EOF
<database name="state_machine_behavior" defaultIdMethod="native">
    <table name="table_with_state_machine_behavior">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />

        <behavior name="state_machine">
            <parameter name="states" value="draft, unpublished, published" />

            <parameter name="initial_state" value="draft" />

            <parameter name="transition" value="draft to published with publish" />
            <parameter name="transition" value="published to unpublished with unpublish" />
            <parameter name="transition" value="unpublished to published with publish" />

            <parameter name="state_column" value="state" />
        </behavior>
    </table>
    <table name="table_with_state_machine_behavior_with_custom_column">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />

        <behavior name="state_machine">
            <parameter name="states" value="draft, published, not_yEt_published, flagged" />

            <parameter name="initial_state" value="draft" />

            <parameter name="transition" value="draft to published with publish" />
            <parameter name="transition" value="published to not_yet_published with unpublish" />
            <parameter name="transition" value="not_yEt_published to published with publish" />
            <parameter name="transition" value="not_yEt_published to flagged with flag_for_publish" />
            <parameter name="transition" value="flagged to published with publish" />

            <parameter name="state_column" value="my_state" />
        </behavior>
    </table>
</database>
EOF;
            $builder = new QuickBuilder();
            $config  = $builder->getConfig();
            $builder->setConfig($config);
            $builder->setSchema($schema);

            $builder->build();
        }
    }

    public function testObjectMethods()
    {

        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'isDraft'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'isUnpublished'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'isPublished'));

        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'canPublish'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'canUnpublish'));

        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'publish'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'unpublish'));

        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'prePublish'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'onPublish'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'postPublish'));

        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'preUnpublish'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'onUnpublish'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'postUnpublish'));

        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'getAvailableStates'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'getState'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'getHumanizedState'));
        $this->assertTrue(method_exists('TableWithStateMachineBehavior', 'getHumanizedStates'));

        $this->assertTrue(defined('TableWithStateMachineBehavior::STATE_DRAFT'));
        $this->assertTrue(defined('TableWithStateMachineBehavior::STATE_PUBLISHED'));
        $this->assertTrue(defined('TableWithStateMachineBehavior::STATE_UNPUBLISHED'));

        $this->assertTrue(defined('TableWithStateMachineBehavior::STATE_NORMALIZED_DRAFT'));
        $this->assertEquals('draft', \TableWithStateMachineBehavior::STATE_NORMALIZED_DRAFT);
    }

    public function testInitialState()
    {
        $post = new \TableWithStateMachineBehavior();
        $this->assertTrue($post->isDraft());
    }

    public function testGetState()
    {
        $post = new \TableWithStateMachineBehavior();
        $this->assertEquals(\TableWithStateMachineBehavior::STATE_DRAFT, $post->getState());
    }

    public function testGetNormalizedState()
    {
        $post = new \TableWithStateMachineBehavior();
        $this->assertEquals(\TableWithStateMachineBehavior::STATE_NORMALIZED_DRAFT, $post->getNormalizedState());
    }

    public function testGetNormalizedStates()
    {
        $expected = array(
            \TableWithStateMachineBehavior::STATE_NORMALIZED_DRAFT,
            \TableWithStateMachineBehavior::STATE_NORMALIZED_UNPUBLISHED,
            \TableWithStateMachineBehavior::STATE_NORMALIZED_PUBLISHED,
        );

        $this->assertCount(3, \TableWithStateMachineBehavior::getNormalizedStates());
        $this->assertEquals($expected, \TableWithStateMachineBehavior::getNormalizedStates());
    }

    public function testGetAvailableStates()
    {
        $post = new \TableWithStateMachineBehavior();
        $expected = array(
            \TableWithStateMachineBehavior::STATE_DRAFT,
            \TableWithStateMachineBehavior::STATE_UNPUBLISHED,
            \TableWithStateMachineBehavior::STATE_PUBLISHED,
        );

        $this->assertEquals($expected, $post->getAvailableStates());
    }

    public function testIssersDefaultValues()
    {
        $post = new \TableWithStateMachineBehavior();

        $this->assertTrue($post->isDraft());
        $this->assertFalse($post->isPublished());
        $this->assertFalse($post->isUnpublished());
    }

    public function testCannersDefaultValues()
    {
        $post = new \TableWithStateMachineBehavior();

        $this->assertTrue($post->canPublish());
        $this->assertFalse($post->canUnpublish());
    }

    public function testPublish()
    {
        $post = new \TableWithStateMachineBehavior();

        $this->assertTrue($post->isDraft());
        $this->assertFalse($post->isPublished());
        $this->assertFalse($post->isUnpublished());
        $this->assertTrue($post->canPublish());

        try {
            $post->publish();
        } catch (\Exception $e) {
            $this->fail('Unexpected exception caught: ' . $e->getMessage());
        }

        $this->assertFalse($post->canPublish());
        $this->assertTrue($post->canUnpublish());
        $this->assertFalse($post->isDraft());
        $this->assertTrue($post->isPublished());
        $this->assertFalse($post->isUnpublished());
    }

    public function testSymbolMethodShouldThrowAnExceptionOnInvalidCall()
    {
        $post = new \TableWithStateMachineBehavior();

        $this->assertFalse($post->canUnpublish());

        try {
            $post->unpublish();
            $this->fail('Expected exception not thrown') ;
        } catch (\Exception $e) {
            $this->assertTrue(true);
            $this->assertInstanceOf('LogicException', $e);
        }

        try {
            $post->publish();
        } catch (\Exception $e) {
            $this->fail('Unexpected exception caught: ' . $e->getMessage());
        }

        $this->assertFalse($post->canPublish());
        $this->assertTrue($post->canUnpublish());
        $this->assertFalse($post->isDraft());
        $this->assertTrue($post->isPublished());
        $this->assertFalse($post->isUnpublished());

        try {
            $post->publish();
            $this->fail('Expected exception not thrown') ;
        } catch (\Exception $e) {
            $this->assertTrue(true);
            $this->assertInstanceOf('LogicException', $e);
        }
    }

    public function testGenerateGetStateIfCustomStateColumn()
    {
        $this->assertTrue(method_exists('TableWithStateMachineBehaviorWithCustomColumn', 'getState'));
        $this->assertTrue(method_exists('TableWithStateMachineBehaviorWithCustomColumn', 'getMyState'));
        $this->assertTrue(method_exists('TableWithStateMachineBehaviorWithCustomColumn', 'isNotYetPublished'));
        $this->assertTrue(method_exists('TableWithStateMachineBehaviorWithCustomColumn', 'flagForPublish'));

        $this->assertTrue(defined('TableWithStateMachineBehaviorWithCustomColumn::STATE_NOT_YET_PUBLISHED'));
        $this->assertTrue(defined('TableWithStateMachineBehaviorWithCustomColumn::STATE_NORMALIZED_NOT_YET_PUBLISHED'));
        $this->assertEquals('not_yet_published', \TableWithStateMachineBehaviorWithCustomColumn::STATE_NORMALIZED_NOT_YET_PUBLISHED);
    }

    public function testIssersDefaultValuesWithCustomStateColumn()
    {
        $post = new \TableWithStateMachineBehaviorWithCustomColumn();

        $this->assertTrue($post->isDraft());
        $this->assertFalse($post->isPublished());
        $this->assertFalse($post->isNotYetPublished());
    }

    public function testCannersDefaultValuesWithCustomStateColumn()
    {
        $post = new \TableWithStateMachineBehaviorWithCustomColumn();

        $this->assertTrue($post->canPublish());
        $this->assertFalse($post->canUnpublish());
    }

    public function testGetHumanizedState()
    {
        $post = new \TableWithStateMachineBehaviorWithCustomColumn();
        $this->assertEquals('Draft', $post->getHumanizedState());

        $refl = new \ReflectionClass($post);
        $prop = $refl->getProperty('my_state');
        $prop->setAccessible(true);
        $prop->setValue($post, \TableWithStateMachineBehaviorWithCustomColumn::STATE_NOT_YET_PUBLISHED);

        $this->assertEquals('Not Yet Published', $post->getHumanizedState());
    }

    public function testGetAvailableStatesStatic()
    {
        $post = new \TableWithStateMachineBehaviorWithCustomColumn();

        $this->assertEquals($post->getAvailableStates(), \TableWithStateMachineBehaviorWithCustomColumn::getAvailableStates());
    }

    public function testGetHumanizedStates()
    {
        $expected = array(
            \TableWithStateMachineBehaviorWithCustomColumn::STATE_DRAFT       => 'Draft',
            \TableWithStateMachineBehaviorWithCustomColumn::STATE_NOT_YET_PUBLISHED => 'Not Yet Published',
            \TableWithStateMachineBehaviorWithCustomColumn::STATE_PUBLISHED   => 'Published',
            \TableWithStateMachineBehaviorWithCustomColumn::STATE_FLAGGED   => 'Flagged',
        );
        $this->assertTrue(is_array(\TableWithStateMachineBehaviorWithCustomColumn::getHumanizedStates()));
        $this->assertEquals($expected, \TableWithStateMachineBehaviorWithCustomColumn::getHumanizedStates());
    }
}
