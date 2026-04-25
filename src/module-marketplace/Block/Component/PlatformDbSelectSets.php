<?php

namespace TCGCollective\MarketPlace\Block\Component;

/**
 * Multi steps wizard block
 *
 * @api
 * @since 100.0.2
 */
class PlatformDbSelectSets extends \Magento\Framework\View\Element\Template
{
    /**
     * Wizard step template
     *
     * @var string
     */
    protected $_template = 'TCGCollective_MarketPlace::sportcard-stepswizard.phtml';

    /**
     * @var array
     */
    protected $initData = [];

    /**
     * @var null|\Magento\Ui\Block\Component\StepsWizard\StepInterface[]
     */
    private $steps;

    /**
     * @return \Magento\Ui\Block\Component\StepsWizard\StepInterface[]
     */
    public function getSteps()
    {
        if ($this->steps === null) {
            foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $step) {
                // dd($step);
                // if ($step instanceof StepsWizard\StepInterface) {
                    $this->steps[$step->getComponentName()] = $step;
                // }
            }
        }
        return $this->steps;
    }

    // @codeCoverageIgnoreStart

    /**
     * @return array
     */
    public function getStepComponents()
    {
        return array_keys($this->getSteps());
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return $this->getNameInLayout();
    }

    /**
     * @return array
     */
    public function getInitData()
    {
        return $this->initData;
    }

    /**
     * @param array $initData
     * @return $this
     */
    public function setInitData($initData)
    {
        $this->initData = $initData;

        return $this;
    }
}
