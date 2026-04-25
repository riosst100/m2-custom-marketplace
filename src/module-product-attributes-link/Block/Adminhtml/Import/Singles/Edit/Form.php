<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Import\Singles\Edit;

use Magento\Framework\View\Asset\Repository; 

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_assetRepo;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $data = []
    ) {   
        parent::__construct($context, $registry, $formFactory, $data);
    }


    protected function _prepareForm()
    {   
        $path = $this->_assetRepo->getUrl("TCGCollective_ProductAttributesLink::sample/attribute_set_mapping_sample.csv");

        $model = $this->_coreRegistry->registry('row_data');

        $form = $this->_formFactory->create(
            ['data' => [
                    'id' => 'edit_form',
                    'enctype' => 'multipart/form-data',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]);

        $form->setHtmlIdPrefix('datalocation_');

           $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => '', 'class' => 'fieldset-wide']
            );
            
            // $entity = $this->getRequest()->getParam('entity') ?: '';

            $fieldset->addField(
                'entity', 
                'select',
                array(
                    'label' => __("Categories"),
                    'class' => 'required-entry',
                    'required' => 'true',
                    'name' => 'entity',        
                    'value' => '',
                    'values'    => [
                        [
                            'value' => '',
                            'label' => __('Select...')
                        ],
                        ['label' => 'Card Games', 'value' => 'card_game'],
                        ['label' => 'Bricks', 'value' => 'bricks_brand'],
                        ['label' => 'Miniature Games', 'value' => 'miniature_game'],
                        ['label' => 'Living Card Games', 'value' => 'living_card_game'],
                        ['label' => 'Non Sport Card', 'value' => 'non_sport_card'],
                        ['label' => 'Sport Card', 'value' => 'sport_card']
                    ]
                )
            );

            $fieldset->addField(
                'categories', 
                'select',
                array(
                    'label' => "",
                    'class' => 'requifed-entry',
                    'required' => 'true',
                    'name' => 'categories',        
                    'value' => '',
                    'values'    => [
                        [
                            'value' => '',
                            'label' => __('Select...')
                        ]
                    ]
                )
            );

            $fieldset->addField(
                'sets', 
                'select',
                array(
                    'label' => "Sets",
                    'class' => 'requifed-entry',
                    'required' => 'true',
                    'name' => 'sets',        
                    'value' => '',
                    'values'    => [
                        [
                            'value' => '',
                            'label' => __('Select...')
                        ]
                    ]
                )
            );
            
            $fieldset->addField(
                'import_type', 
                'hidden',
                array(
                    'label' => '',
                    'name' => 'import_type',        
                    'value' => 'singles'
                )
            );

       $importdata_script =  $fieldset->addField(
            'importdata',
            'file', 
                array(
                        'label'     => 'Select File',
                        'required'  => true,
                        'name'      => 'importdata',  
                        'note' => 'Allow File type: .csv', 
                     )
        );

        $importdata_script->setAfterElementHtml("   
        
<style>
        .field-categories {
        display:none;
    }
        .field-sets {
        display:none;
    }
        </style>
            <script type=\"text/javascript\">

            document.getElementById('attributeset_importdata').onchange = function () { 

                var fileInput = document.getElementById('attributeset_importdata');

                var filePath = fileInput.value;

                var allowedExtensions = /(\.csv)$/i; 

                if(!allowedExtensions.exec(filePath))
                {
                    alert('Please upload file having extensions .csv only.');
                    fileInput.value = '';
                }

            };

            </script>"
        );


        // $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
  
}