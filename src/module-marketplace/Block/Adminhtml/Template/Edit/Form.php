<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Template\Edit;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\System\Store;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Form extends \Magento\Email\Block\Adminhtml\Template\Edit\Form
{
    protected Store $systemStore;

    /**
     * @var \Magento\Variable\Model\Source\Variables
     */
    protected $_variables;

    /**
     * @var \Magento\Variable\Model\VariableFactory
     */
    protected $_variableFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    private AdminWebsite $adminWebsite;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Variable\Model\VariableFactory $variableFactory
     * @param \Magento\Variable\Model\Source\Variables $variables
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param SecureHtmlRenderer|null $secureRenderer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Variable\Model\VariableFactory $variableFactory,
        \Magento\Variable\Model\Source\Variables $variables,
        Store $systemStore,
        AdminWebsite $adminWebsite,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->systemStore = $systemStore;
        $this->adminWebsite = $adminWebsite;
        parent::__construct($context, $registry, $formFactory, $variableFactory, $variables, $data, $serializer, $secureRenderer);
    }

    /**
     * Add Email Receiver field
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->getForm();
        if (!$form) {
            return $this;
        }

        $fieldset = $form->getElement('base_fieldset');
        if (!$fieldset) {
            return $this;
        }

        $fieldset->addField(
            'email_receiver',
            'select',
            [
                'name'     => 'email_receiver',
                'label'    => __('Sender'),
                'title'    => __('Sender'),
                'required' => true,
                'options'  => [
                    'admin'  => __('Admin'),
                    'seller' => __('Seller'),
                ],
                'note'     => __('Who should send this email template'),
            ]
        );                            
        
        $websites = $this->systemStore->getWebsiteOptionHash(true);
        if (isset($websites[0])) {
            unset($websites[0]);
        }

        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            $fieldset->addField(
                'website_id',
                'select',
                [
                    'name'   => 'website_id',
                    'label'  => __('Countries/Regions'),
                    'title'  => __('Countries/Regions'),
                    'values' => $websites,
                    'value'  => 0,
                    'note'   => __('Website scope for this email template'),
                ]
            );
        }

        $template = $this->getEmailTemplate();
        if ($template && $template->getId()) {
            $form->getElement('email_receiver')
            ->setValue($template->getData('email_receiver'));
            if ($this->adminWebsite->isAllWebsitesAllowed()) {
                $form->getElement('website_id')
                ->setValue($template->getData('website_id'));
            }
        }

        return $this;
    }
}
