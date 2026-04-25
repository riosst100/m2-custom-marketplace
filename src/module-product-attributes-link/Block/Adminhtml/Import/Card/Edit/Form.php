<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Import\Card\Edit;

use Magento\Framework\View\Asset\Repository; 
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_assetRepo;
    protected $helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        MappingHelper $helper,
        array $data = []
    ) {   
        $this->helper = $helper;
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
                ['legend' => __('Import Card'), 'class' => 'fieldset-wide']
            );
            
            $entity = $this->getRequest()->getParam('entity') ?: '';

            $fieldset->addField(
                'entity', 
                'select',
                array(
                    'label' => __("Entity"),
                    'class' => 'requifed-entry',
                    'required' => 'true',
                    'name' => 'entity',        
                    'value' => $entity,
                    'values'    => [
                        [
                            'value' => '',
                            'label' => __('Select...')
                        ],
                        ['label' => 'Card Games', 'value' => 'card_game'],
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

            // // SPORT CARD
            // $sportCardItems = [];
            // $sportCardItems[] = [
            //     'value' => '',
            //     'label' => __('--Please select--')
            // ];
            // $sportCardOptions = $this->helper->getAttributeOptions('sc_categories');
            // if ($sportCardOptions) {
            //     foreach($sportCardOptions as $option) {
            //         if ($option->getLabel() && $option->getValue()) {
            //             $sportCardItems[] = [
            //                 'value' => $option->getValue(),
            //                 'label' => $option->getLabel()
            //             ];
            //         }
            //     }
            // }

            // $sportCard = $this->getRequest()->getParam('sport_card') ?: null;

            // $fieldset->addField(
            //     'sport_card', 
            //     'select',
            //     array(
            //         'display' => 'none',
            //         'label' => __("Sport Card"),
            //         'class' => 'required-entry',
            //         'required' => 'true',
            //         'name' => 'sport_card',
            //         'value' => $sportCard,
            //         'values'    => $sportCardItems
            //     )
            // );

            // // NON SPORT CARD
            // $nonSportCardItems = [];
            // $nonSportCardItems[] = [
            //     'value' => '',
            //     'label' => __('--Please select--')
            // ];
            // $nonSportCardOptions = $this->helper->getAttributeOptions('nsc_categories');
            // if ($nonSportCardOptions) {
            //     foreach($nonSportCardOptions as $option) {
            //         if ($option->getLabel() && $option->getValue()) {
            //             $nonSportCardItems[] = [
            //                 'value' => $option->getValue(),
            //                 'label' => $option->getLabel()
            //             ];
            //         }
            //     }
            // }

            // $nonSportCard = $this->getRequest()->getParam('non_sport_card') ?: null;

            // $fieldset->addField(
            //     'non_sport_card', 
            //     'select',
            //     array(
            //         'display' => 'none',
            //         'label' => __("Non Sport Card"),
            //         'class' => 'required-entry',
            //         'required' => 'true',
            //         'name' => 'non_sport_card',
            //         'value' => $nonSportCard,
            //         'values'    => $nonSportCardItems
            //     )
            // );

            // // CARD GAME
            // $items = [];
            // $items[] = [
            //     'value' => '',
            //     'label' => __('--Please select--')
            // ];
            // $cardGameOptions = $this->helper->getAttributeOptions('card_game');
            // if ($cardGameOptions) {
            //     foreach($cardGameOptions as $option) {
            //         if ($option->getLabel() && $option->getValue()) {
            //             $items[] = [
            //                 'value' => $option->getValue(),
            //                 'label' => $option->getLabel()
            //             ];
            //         }
            //     }
            // }

            // $cardGame = $this->getRequest()->getParam('card_game') ?: null;

            // $fieldset->addField(
            //     'card_game', 
            //     'select',
            //     array(
            //         'display' => 'none',
            //         'label' => __("Card Game"),
            //         'class' => 'required-entry',
            //         'required' => 'true',
            //         'name' => 'card_game',
            //         'value' => $cardGame,
            //         'values'    => $items
            //     )
            // );
            
            $fieldset->addField(
                'import_type', 
                'hidden',
                array(
                    'label' => '',
                    'name' => 'import_type',        
                    'value' => 'card'
                )
            );

       $importdata_script =  $fieldset->addField(
            'importdata',
            'file', 
                array(
                        'label'     => 'Upload File',
                        'required'  => true,
                        'name'      => 'importdata',  
                        'note' => 'Allow File type: .csv', 
                     )
        );

        // <span id='sample-file-span' ><a id='sample-file-link' href='".$path."'  >Download Sample File</a></span>

        $importdata_script->setAfterElementHtml(" 
        

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

        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}card_game",
                'card_game'
            )->addFieldMap(
                "{$htmlIdPrefix}entity",
                'entity'
            )->addFieldMap(
                "{$htmlIdPrefix}non_sport_card",
                'non_sport_card'
            )->addFieldMap(
                "{$htmlIdPrefix}sport_card",
                'sport_card'
            )->addFieldDependence(
            'card_game',
            'entity',
            'card_game'
            )->addFieldDependence(
                'non_sport_card',
                'entity',
                'non_sport_card'
                )->addFieldDependence(
                    'sport_card',
                    'entity',
                    'sport_card'
                    )
        );


        // $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
  
}