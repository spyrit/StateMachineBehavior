<?php
namespace smolowik\Propel\Behavior\StateMachine;

class StateMachineBehaviorQueryBuilderModifier
{
    /**
     * @var StateMachineBehavior
     */
    private $behavior;

    public function __construct(\Propel\Generator\Model\Behavior $behavior)
    {
        $this->behavior = $behavior;
    }

    protected function getColumnFilter($columnName)
    {
        return 'filterBy' . $this->behavior->getColumnForParameter($columnName)->getPhpName();
    }

    protected function getQueryClassName($builder)
    {
        return $builder->getStubQueryBuilder()->getClassname();
    }
}
